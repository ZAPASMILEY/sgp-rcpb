<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formations', function (Blueprint $table): void {
            $table->id();

            // Agent formé
            $table->foreignId('agent_id')
                  ->constrained('agents')
                  ->cascadeOnDelete()
                  ->comment('Agent ayant suivi la formation');

            // Contenu de la formation
            $table->string('titre', 255)->comment('Intitulé de la formation');

            $table->string('domaine', 60)->comment('Domaine : management, informatique, finance…');

            // Période
            $table->date('date_debut')->comment('Début de la formation');
            $table->date('date_fin')->comment('Fin de la formation');

            $table->unsignedSmallInteger('duree_heures')
                  ->default(0)
                  ->comment('Durée totale en heures');

            // Traçabilité RH
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('Utilisateur RH ayant saisi la formation');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
