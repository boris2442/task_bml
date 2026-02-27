<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajouter les colonnes heures_travaillees et heures_supplementaires à presences
     * heures_travaillees: min(heure_depart - heure_arrivee, 4)
     * heures_supplementaires: max(0, heure_depart - heure_arrivee - 4)
     */
    public function up(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            // Heures effectivement travaillées (capped à 4h)
            $table->decimal('heures_travaillees', 5, 2)->default(0)->after('description');

            // Heures supplémentaires (au-delà de 4h)
            $table->decimal('heures_supplementaires', 5, 2)->default(0)->after('heures_travaillees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            $table->dropColumn(['heures_travaillees', 'heures_supplementaires']);
        });
    }
};
