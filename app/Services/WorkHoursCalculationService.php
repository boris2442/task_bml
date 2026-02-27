<?php

namespace App\Services;

use App\Models\User;
use App\Models\Holiday;
use App\Models\Presence;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WorkHoursCalculationService
{
    const HEURES_PAR_JOUR_DEFAULT = 4.0;
    const JOURS_TRAVAILLES_DEFAULT = [1, 2, 3, 4, 5]; // Lun-Ven (0=Dim, 1=Lun, ..., 6=Sam)
    const JOUR_FERIE_DIMANCHE = 0; // Dimanche

    /**
     * Calculer les heures attendues TOTALES depuis l'inscription jusqu'à aujourd'hui
     * 
     * @param User $user
     * @return float Heures attendues
     */
    public function calculerHeuresAttenduesTotales(User $user): float
    {
        $dateDebut = Carbon::parse($user->date_inscription);
        $dateAujourdhui = Carbon::today();

        return $this->calculerHeuresAttendusPeriode($user, $dateDebut, $dateAujourdhui);
    }

    /**
     * Calculer les heures attendues pour une période donnée
     * 
     * @param User $user
     * @param Carbon $dateDebut
     * @param Carbon $dateFin
     * @return float Heures attendues
     */
    public function calculerHeuresAttendusPeriode(User $user, Carbon $dateDebut, Carbon $dateFin): float
    {
        // Récupérer le scénario de travail de l'utilisateur (sans parenthèses pour éviter la relation HasOne)
        $workSchedule = $user->workSchedule;

        if ($workSchedule) {
            $heuresParJour = $workSchedule->heures_par_jour ?? self::HEURES_PAR_JOUR_DEFAULT;
            $joursTravailles = is_array($workSchedule->jours_travailles)
                ? $workSchedule->jours_travailles
                : json_decode($workSchedule->jours_travailles, true) ?? self::JOURS_TRAVAILLES_DEFAULT;
        } else {
            $heuresParJour = self::HEURES_PAR_JOUR_DEFAULT;
            $joursTravailles = self::JOURS_TRAVAILLES_DEFAULT;
        }

        $heuresAttendues = 0;
        $current = $dateDebut->copy();

        // Iterérer chaque jour entre début et fin
        while ($current <= $dateFin) {
            $jourSemaine = $current->dayOfWeek; // 0=Dim, 1=Lun, ..., 6=Sam

            // Vérifier si c'est un jour travaillé
            if ($this->isjourTravaille($current, $jourSemaine, $joursTravailles)) {
                $heuresAttendues += $heuresParJour;
            }

            $current->addDay();
        }

        return (float) $heuresAttendues;
    }

    /**
     * Calculer les heures RÉELLES travaillées par un utilisateur pour une période
     * Seulement les présences validées
     * 
     * @param User $user
     * @param Carbon $dateDebut
     * @param Carbon $dateFin
     * @return float
     */
    public function calculerHeuresReellesPeriode(User $user, Carbon $dateDebut, Carbon $dateFin): float
    {
        $presences = Presence::where('user_id', $user->id)
            ->where('statut', 'validee')
            ->whereBetween('date_presence', [$dateDebut, $dateFin])
            ->get();

        $heuresReelles = 0;
        foreach ($presences as $presence) {
            $heuresReelles += $presence->heures_travaillees ?? 0;
        }

        return (float) $heuresReelles;
    }

    /**
     * Calculer le pourcentage d'accomplissement
     * 
     * @param float $heuresReelles
     * @param float $heuresAttendues
     * @return float Pourcentage (0-100)
     */
    public function calculerPourcentageAccomplissement(float $heuresReelles, float $heuresAttendues): float
    {
        if ($heuresAttendues == 0) {
            return 0;
        }
        return round(($heuresReelles / $heuresAttendues) * 100, 2);
    }

    /**
     * Vérifier si un utilisateur est en retard (< 80%)
     * 
     * @param User $user
     * @param float $pourcentage
     * @return bool
     */
    public function estEnRetard(User $user, float $pourcentage): bool
    {
        return $pourcentage < 80;
    }

    /**
     * Calculer les heures supplémentaires pour une présence
     * Si heure_depart - heure_arrivee > 4h, alors supp = totalHeures - 4
     * 
     * @param float $totalHeures (différence entre départ et arrivée)
     * @return array ['travaillees' => X, 'supplementaires' => Y]
     */
    public function calculerHeuresTravailleesEtSupp(float $totalHeures): array
    {
        $heuresTravaillees = min($totalHeures, 4.0);
        $heuresSupp = max(0, $totalHeures - 4.0);

        return [
            'heures_travaillees' => round($heuresTravaillees, 2),
            'heures_supplementaires' => round($heuresSupp, 2),
        ];
    }

    /**
     * Récupérer les stats complètes pour un utilisateur
     * 
     * @param User $user
     * @param Carbon|null $dateDebut
     * @param Carbon|null $dateFin
     * @return array
     */
    public function obtenirStatsUtilisateur(User $user, ?Carbon $dateDebut = null, ?Carbon $dateFin = null): array
    {
        if (!$dateDebut) {
            $dateDebut = Carbon::parse($user->date_inscription);
        }
        if (!$dateFin) {
            $dateFin = Carbon::today();
        }

        $heuresAttendues = $this->calculerHeuresAttendusPeriode($user, $dateDebut, $dateFin);
        $heuresReelles = $this->calculerHeuresReellesPeriode($user, $dateDebut, $dateFin);
        $pourcentage = $this->calculerPourcentageAccomplissement($heuresReelles, $heuresAttendues);

        return [
            'heures_attendues' => $heuresAttendues,
            'heures_reelles' => $heuresReelles,
            'heures_manquantes' => max(0, $heuresAttendues - $heuresReelles),
            'pourcentage' => $pourcentage,
            'en_retard' => $this->estEnRetard($user, $pourcentage),
            'periode' => [
                'debut' => $dateDebut->format('Y-m-d'),
                'fin' => $dateFin->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Récupérer les stats pour TOUS les employés
     * 
     * @return Collection
     */
    public function obtenirStatsAllUtilisateurs(): Collection
    {
        $employes = User::where('role', 'employe')->get();

        return $employes->map(function ($user) {
            $stats = $this->obtenirStatsUtilisateur($user);
            $stats['user'] = $user;
            return $stats;
        });
    }

    /**
     * Obtenir les utilisateurs EN RETARD (< 80%)
     * 
     * @return Collection
     */
    public function obtenirUtilisateurEnRetard(): Collection
    {
        return $this->obtenirStatsAllUtilisateurs()
            ->filter(fn($stat) => $stat['en_retard']);
    }

    /**
     * Vérifier si un jour est travaillé (pas dimanche ni jour férié)
     * 
     * @param Carbon $date
     * @param int $jourSemaine (0=Dim, 1=Lun, ..., 6=Sam)
     * @param array $joursTravailles
     * @return bool
     */
    private function isjourTravaille(Carbon $date, int $jourSemaine, array $joursTravailles): bool
    {
        // Vérifier que c'est un jour travaillé (pas dimanche)
        if (!in_array($jourSemaine, $joursTravailles)) {
            return false;
        }

        // Vérifier que ce n'est pas un jour férié
        $isHoliday = Holiday::whereDate('date', $date)->exists();
        if ($isHoliday) {
            return false;
        }

        return true;
    }

    /**
     * Récupérer le schedule par défaut d'un utilisateur
     * 
     * @param User $user
     * @return array
     */
    private function getDefaultSchedule(User $user): array
    {
        return [
            'heures_par_jour' => self::HEURES_PAR_JOUR_DEFAULT,
            'jours_travailles' => self::JOURS_TRAVAILLES_DEFAULT,
        ];
    }

    /**
     * Stats par semaine pour un utilisateur
     * 
     * @param User $user
     * @return array
     */
    public function obtenirStatsParSemaine(User $user): array
    {
        $dateDebut = Carbon::parse($user->date_inscription);
        $dateAujourdhui = Carbon::today();

        $stats = [];
        $current = $dateDebut->copy()->startOfWeek();

        while ($current <= $dateAujourdhui) {
            $finSemaine = $current->copy()->endOfWeek();
            if ($finSemaine > $dateAujourdhui) {
                $finSemaine = $dateAujourdhui;
            }

            $stat = $this->obtenirStatsUtilisateur($user, $current, $finSemaine);
            $stat['semaine'] = $current->format('W');
            $stat['annee'] = $current->format('Y');
            $stats[] = $stat;

            $current->addWeek();
        }

        return $stats;
    }

    /**
     * Stats par mois pour un utilisateur
     * 
     * @param User $user
     * @return array
     */
    public function obtenirStatsParMois(User $user): array
    {
        $dateDebut = Carbon::parse($user->date_inscription);
        $dateAujourdhui = Carbon::today();

        $stats = [];
        $current = $dateDebut->copy()->startOfMonth();

        while ($current <= $dateAujourdhui) {
            $finMois = $current->copy()->endOfMonth();
            if ($finMois > $dateAujourdhui) {
                $finMois = $dateAujourdhui;
            }

            $stat = $this->obtenirStatsUtilisateur($user, $current, $finMois);
            $stat['mois'] = $current->format('m');
            $stat['annee'] = $current->format('Y');
            $stat['mois_label'] = $current->format('F Y');
            $stats[] = $stat;

            $current->addMonth();
        }

        return $stats;
    }
}
