<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date_presence');
            $table->datetime('heure_arrivee');
            $table->datetime('heure_depart')->nullable();
            $table->enum('statut', [
                'arrivee_en_attente',
                'arrivee_approuvee',
                'depart_en_attente',
                'validee',
                'rejetee'
            ])->default('arrivee_en_attente');
            $table->text('description');


            // Un employé ne peut avoir qu'une présence non terminée par jour
            $table->unique(['user_id', 'date_presence', 'statut'], 'unique_presence_active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
