<?php

namespace App\Http\Controllers\Employe;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardUserConttoller extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        // Récupérer la présence d'aujourd'hui
        $presenceToday = Presence::where('user_id', $user->id)
            ->whereDate('date_presence', Carbon::today())
            ->first();

        // Calculer les heures du mois
        $presencesMois = Presence::where('user_id', $user->id)
            ->whereMonth('date_presence', Carbon::now()->month)
            ->whereNotNull('heure_depart')
            ->get();

        $heuresMois = 0;
        $joursPresence = 0;
        foreach ($presencesMois as $p) {
            $heuresMois += $p->heure_depart->diffInHours($p->heure_arrivee);
            $joursPresence++;
        }



        // Calculer les heures par jour de la semaine
        $heuresSemaine = [];
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::now()->startOfWeek()->addDays($i);
            $presence = Presence::where('user_id', $user->id)
                ->whereDate('date_presence', $date)
                ->whereNotNull('heure_depart')
                ->first();

            if ($presence) {
                $heures = $presence->heure_depart->diffInHours($presence->heure_arrivee);
                $heuresSemaine[] = round($heures, 1);
            } else {
                $heuresSemaine[] = 0;
            }
        }





        // Récupérer les 5 dernières présences
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
                    'heures' => $p->heure_depart
                        ? round($p->heure_depart->diffInHours($p->heure_arrivee), 1)
                        : 0,
                ];
            });

        return Inertia::render('employe/DashboardEmploye', [
            'user' => [
                'role' => $user->role,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
            ],
            'ma_presence' => $presenceToday ? [
                'heure_arrivee' => $presenceToday->heure_arrivee,
                'heure_depart' => $presenceToday->heure_depart,
                'statut' => $presenceToday->statut,
            ] : null,
            'stats' => [
                'heures_mois' => round($heuresMois, 1),
                'jours_presence' => $joursPresence,
                'heures_semaine' => $heuresSemaine,
                'objectif_mois' => 80, // Objectif mensuel
            ],
            'recentes' => $recentes,
        ]);
    }
}
