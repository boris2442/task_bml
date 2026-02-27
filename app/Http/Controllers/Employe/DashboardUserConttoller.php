<?php

namespace App\Http\Controllers\Employe;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use App\Services\WorkHoursCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardUserConttoller extends Controller
{
    protected WorkHoursCalculationService $hoursService;

    public function __construct(WorkHoursCalculationService $hoursService)
    {
        $this->hoursService = $hoursService;
    }

    public function dashboard()
    {
        $user = Auth::user();

        // Récupérer la présence d'aujourd'hui
        $presenceToday = Presence::where('user_id', $user->id)
            ->whereDate('date_presence', Carbon::today())
            ->first();

        // ===== STATS TOTALES (depuis inscription) =====
        $statsTotales = $this->hoursService->obtenirStatsUtilisateur($user);

        // ===== STATS CE MOIS =====
        $statsThisMonth = $this->hoursService->obtenirStatsUtilisateur(
            $user,
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        // ===== STATS CETTE SEMAINE =====
        $statsThisWeek = $this->hoursService->obtenirStatsUtilisateur(
            $user,
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        );

        // ===== HEURES PAR JOUR CETTE SEMAINE =====
        $heuresSemaine = [];
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::now()->startOfWeek()->addDays($i);
            $presence = Presence::where('user_id', $user->id)
                ->where('statut', 'validee')
                ->whereDate('date_presence', $date)
                ->first();

            if ($presence) {
                $heuresSemaine[] = round($presence->heures_travaillees, 1);
            } else {
                $heuresSemaine[] = 0;
            }
        }

        // ===== 5 DERNIÈRES PRÉSENCES =====
        $recentes = Presence::where('user_id', $user->id)
            ->orderBy('date_presence', 'desc')
            ->orderBy('heure_arrivee', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'date' => Carbon::parse($p->date_presence)->format('d/m/Y'),
                    'statut' => $p->statut,
                    'heures' => $p->heures_travaillees ?? 0,
                    'heures_supp' => $p->heures_supplementaires ?? 0,
                ];
            });

        // ===== HEURES SUPP TOTALES =====
        $heuresSupp = Presence::where('user_id', $user->id)
            ->where('statut', 'validee')
            ->sum('heures_supplementaires');

        return Inertia::render('employe/DashboardEmploye', [
            'user' => [
                'role' => $user->role,
                'name' => $user->name,
                'matricule' => $user->matricule,
            ],
            'ma_presence' => $presenceToday ? [
                'heure_arrivee' => $presenceToday->heure_arrivee,
                'heure_depart' => $presenceToday->heure_depart,
                'statut' => $presenceToday->statut,
                'heures_travaillees' => $presenceToday->heures_travaillees ?? 0,
                'heures_supplementaires' => $presenceToday->heures_supplementaires ?? 0,
            ] : null,
            'stats' => [
                // Total depuis inscription
                'heures_total' => round($statsTotales['heures_reelles'], 1),
                'heures_total_attendues' => round($statsTotales['heures_attendues'], 1),
                'pourcentage_total' => round($statsTotales['pourcentage'], 2),
                'en_retard_total' => $statsTotales['en_retard'],

                // Ce mois
                'heures_mois' => round($statsThisMonth['heures_reelles'], 1),
                'heures_mois_attendues' => round($statsThisMonth['heures_attendues'], 1),
                'pourcentage_mois' => round($statsThisMonth['pourcentage'], 2),
                'objectif_mois' => 80, // Seuil d'alerte

                // Cette semaine
                'heures_semaine_total' => round($statsThisWeek['heures_reelles'], 1),
                'heures_semaine_attendues' => round($statsThisWeek['heures_attendues'], 1),

                // Jour par jour
                'heures_semaine' => $heuresSemaine,

                // Supplémentaires
                'heures_supplementaires_total' => round($heuresSupp, 1),
            ],
            'recentes' => $recentes,
        ]);
    }
}
