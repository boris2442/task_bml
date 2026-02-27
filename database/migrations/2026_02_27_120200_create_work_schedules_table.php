<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table des horaires de travail (heures/jour par employé)
     * Permet de gérer des employés avec des contrats différents
     */
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Heures attendues par jour (4, 6, 8, etc.)
            $table->decimal('heures_par_jour', 5, 2)->default(4.00);

            // Lundi = 1, Dimanche = 0 (bitwise: lun=1, mar=2, mer=4, jeu=8, ven=16, sam=32, dim=64)
            // Pour simplifier: jours travaillés (JSON array ou string)
            $table->string('jours_travailles')->default('[1, 2, 3, 4, 5]')->comment('JSON array: 1=Lun, 2=Mar, 3=Mer, 4=Jeu, 5=Ven, 6=Sam, 0=Dim');

            // Date de début et fin du contrat
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
