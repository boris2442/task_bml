<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    /**
     * Initialiser les horaires de travail par défaut pour tous les utilisateurs
     */
    public function run(): void
    {
        // Pour tous les employés existants, créer un WorkSchedule par défaut
        $employes = User::where('role', 'employe')->get();

        foreach ($employes as $employe) {
            WorkSchedule::firstOrCreate(
                ['user_id' => $employe->id],
                [
                    'heures_par_jour' => 4.0,
                    'jours_travailles' => json_encode([1, 2, 3, 4, 5]), // Lun-Ven
                    'date_debut' => $employe->date_inscription,
                    'date_fin' => null, // En cours
                ]
            );
        }
    }
}
