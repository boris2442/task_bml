<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Presence;
use App\Models\Justificatif;
use App\Models\Holiday;
use App\Services\WorkHoursCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected WorkHoursCalculationService $hoursService;

    public function __construct(WorkHoursCalculationService $hoursService)
    {
        $this->hoursService = $hoursService;
    }

    /**
     * Afficher le dashboard admin avec KPIs et alertes
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();

        // ===== KPIs GLOBAUX =====
        // Total employés
        $totalEmployes = User::where('role', 'employe')->count();

        // Présences aujourd'hui
        $presencesToday = Presence::whereDate('date_presence', $today)->count();

        // En attente d'approbation
        $enAtte = Presence::whereIn('statut', ['arrivee_en_attente', 'depart_en_attente'])->count();

        // Heures totales hoje
        $heuresAujourdhui = Presence::where('statut', 'validee')
            ->whereDate('date_presence', $today)
            ->sum('heures_travaillees');

        // ===== STATS EMPLOYÉS =====
        $statsAllEmployes = $this->hoursService->obtenirStatsAllUtilisateurs();

        // Employés en retard (< 80%)
        $emploiesEnRetard = $statsAllEmployes->filter(fn($stat) => $stat['en_retard'])
            ->map(function ($stat) {
                return [
                    'user' => $stat['user'],
                    'heures_attendues' => $stat['heures_attendues'],
                    'heures_reelles' => $stat['heures_reelles'],
                    'pourcentage' => $stat['pourcentage'],
                    'heures_manquantes' => $stat['heures_manquantes'],
                ];
            })
            ->values();

        // Top 5 employés les plus productifs
        $topProductifs = $statsAllEmployes
            ->sortByDesc('heures_reelles')
            ->take(5)
            ->map(function ($stat) {
                return [
                    'user' => $stat['user'],
                    'heures_reelles' => $stat['heures_reelles'],
                    'pourcentage' => $stat['pourcentage'],
                ];
            })
            ->values();

        // ===== STATS CETTE SEMAINE =====
        $statsThisWeek = [];
        $current = $thisWeekStart->copy();
        while ($current <= $thisWeekEnd) {
            $presencesDay = Presence::where('statut', 'validee')
                ->whereDate('date_presence', $current)
                ->count();

            $statsThisWeek[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('A'),
                'presences' => $presencesDay,
            ];

            $current->addDay();
        }

        // ===== HEURES SUPP DÉTECTÉES =====
        $heuresSupp = Presence::where('statut', 'validee')
            ->where('heures_supplementaires', '>', 0)
            ->with('user')
            ->latest('date_presence')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'utilisateur' => $p->user->name,
                'date' => $p->date_presence->format('d/m/Y'),
                'heures_supp' => $p->heures_supplementaires,
            ]);

        // ===== TAUX D'APPROBATION =====
        $totalPresences = Presence::count();
        $approvees = Presence::whereIn('statut', ['validee', 'arrivee_approuvee'])->count();
        $rejetees = Presence::where('statut', 'rejetee')->count();

        $tauxApprobation = [
            'approvees' => $approvees,
            'rejetees' => $rejetees,
            'en_attente' => $enAtte,
            'pourcentage_approvees' => $totalPresences > 0 ? round(($approvees / $totalPresences) * 100, 2) : 0,
        ];

        // ===== JOUR FÉRIÉ POUR INFO =====
        $dimanches = Holiday::count();

        return Inertia::render('admin/Dashboard', [
            'kpis' => [
                'total_employes' => $totalEmployes,
                'presences_today' => $presencesToday,
                'en_attente_approbation' => $enAtte,
                'heures_ajourhui' => round($heuresAujourdhui, 2),
            ],
            'emploies_en_retard' => $emploiesEnRetard,
            'top_productifs' => $topProductifs,
            'stats_this_week' => $statsThisWeek,
            'heures_supplementaires' => $heuresSupp,
            'taux_approbation' => $tauxApprobation,
            'nb_jours_feries' => $dimanches,
        ]);
    }

    /**
     * Page de rapports détaillés par employé
     */
    public function rapportsEmployes(Request $request)
    {
        $employes = User::where('role', 'employe')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                $stats = $this->hoursService->obtenirStatsUtilisateur($user);
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'matricule' => $user->matricule,
                    'total_heures' => $stats['heures_reelles'],
                    'heures_attendues' => $stats['heures_attendues'],
                    'pourcentage' => $stats['pourcentage'],
                    'en_retard' => $stats['en_retard'],
                ];
            })
            ->values();

        return Inertia::render('admin/Rapports', [
            'employes' => $employes,
        ]);
    }

    /**
     * Details d'un employé avec stats par semaine/mois + historique complet
     */
    public function detailEmploye(User $user, Request $request)
    {
        $statsParMois = $this->hoursService->obtenirStatsParMois($user);
        $statsParSemaine = $this->hoursService->obtenirStatsParSemaine($user);
        $statsTotales = $this->hoursService->obtenirStatsUtilisateur($user);

        // Heures supp pour cet employé
        $heuresSupp = Presence::where('user_id', $user->id)
            ->where('heures_supplementaires', '>', 0)
            ->where('statut', 'validee')
            ->sum('heures_supplementaires');

        // Historique complet des presences (avec pagination)
        $presencesQuery = Presence::where('user_id', $user->id)
            ->with(['justificatifs', 'approbations.admin'])
            ->orderBy('date_presence', 'desc')
            ->orderBy('heure_arrivee', 'desc');

        // Filtrer par statut si demandé
        if ($request->statut) {
            $presencesQuery->where('statut', $request->statut);
        }

        $presences = $presencesQuery->paginate(20);

        // Stats rapides pour l'historique
        $historique_stats = [
            'total_presences' => Presence::where('user_id', $user->id)->count(),
            'validees' => Presence::where('user_id', $user->id)->where('statut', 'validee')->count(),
            'en_attente_approbation' => Presence::where('user_id', $user->id)
                ->whereIn('statut', ['arrivee_en_attente', 'depart_en_attente'])->count(),
            'rejetees' => Presence::where('user_id', $user->id)->where('statut', 'rejetee')->count(),
            'total_justificatifs' => Justificatif::whereIn(
                'presence_id',
                Presence::where('user_id', $user->id)->pluck('id')
            )->count(),
        ];

        return Inertia::render('admin/DetailEmploye', [
            'user' => $user,
            'stats_totales' => $statsTotales,
            'stats_par_mois' => $statsParMois,
            'stats_par_semaine' => $statsParSemaine,
            'heures_supplementaires_total' => round($heuresSupp, 2),
            'presences' => $presences,
            'historique_stats' => $historique_stats,
            'filters' => $request->only(['statut']),
        ]);
    }
}
