<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table d'alertes pour tracer les employés en retard sur heures attendues
     */
    public function up(): void
    {
        Schema::create('work_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Type d'alerte: 'retard', 'depassement', etc.
            $table->string('type')->default('retard');

            // Message d'alerte
            $table->text('message');

            // Données pour le rapport
            $table->decimal('heures_attendues', 8, 2)->nullable();
            $table->decimal('heures_actuelles', 8, 2)->nullable();
            $table->decimal('pourcentage', 5, 2)->nullable()->comment('% d\'accomplissement');

            // Période
            $table->date('periode_debut');
            $table->date('periode_fin');

            // Statut (si alertée à l'admin ou pas)
            $table->boolean('notifiee')->default(false);
            $table->timestamp('notifiee_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_alerts');
    }
};
