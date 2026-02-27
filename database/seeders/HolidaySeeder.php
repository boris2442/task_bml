<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Ensemencer les jours fériés (dimanches)
     */
    public function run(): void
    {
        // Initialiser les dimanches comme jours non-travaillés
        // Pour 2026, créer une entrée pour le premier dimanche
        Holiday::firstOrCreate(
            ['date' => Carbon::parse('2026-02-01')->startOfWeek(0)], // Premier dimanche de février
            [
                'nom' => 'Dimanche',
                'description' => 'Jour de repos hebdomadaire',
            ]
        );

        // Note: En production, créer une commande cron ou un job pour générer dynamiquement tous les dimanches
        // Pour l'instant, cette seeder initialise le système
    }
}
