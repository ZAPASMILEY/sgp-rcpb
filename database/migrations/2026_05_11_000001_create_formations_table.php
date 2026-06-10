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
            $table->string('theme', 255)->comment('Thème de la formation');

            $table->string('domaine', 60)->comment('Domaine : management, informatique, finance…');

            // Période
            $table->date('date_debut')->comment('Début de la formation');
            $table->date('date_fin')->comment('Fin de la formation');

            $table->unsignedSmallInteger('duree_heures')
                  ->default(0)
                  ->comment('Durée totale en heures');

            // Type de formation
            $table->string('type', 20)->default('interne')
                  ->comment('interne ou externe');

            // Pièce justificative (attestation uploadée par l'agent)
            $table->string('attestation_path', 500)->nullable()
                  ->comment('Chemin vers l\'attestation (PDF/image) soumise par l\'agent');

            // Workflow de validation RH
            $table->string('statut', 20)->default('validee')
                  ->comment('en_attente | validee | refusee — defaut validee pour saisies RH directes');
            $table->text('motif_refus')->nullable()
                  ->comment('Motif du refus saisi par le RH');

            // Formateur (agent de la Faitière)
            $table->foreignId('formateur_id')
                  ->nullable()
                  ->constrained('agents')
                  ->nullOnDelete()
                  ->comment('Agent de la Faitière ayant animé la formation');

            // Traçabilité RH
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('Utilisateur ayant saisi la formation (RH ou agent lui-même)');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
