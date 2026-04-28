<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('agents', function (Blueprint $table): void {
            $table->id();

            /**
             * Rattachements hiérarchiques.
             * Un agent est rattaché à UNE SEULE unité à la fois ;
             * les autres colonnes restent NULL.
             *
             * Hiérarchie descendante :
             *   Entité
             *   └─ Direction           (agents des services centraux)
             *      └─ Service
             *   └─ Délégation Technique
             *      ├─ Service de DT
             *      └─ Caisse
             *         ├─ Service de Caisse
             *         └─ Agence
             *            └─ Guichet
             */
            $table->foreignId('entite_id')
                  ->nullable()
                  ->constrained('entites')
                  ->nullOnDelete()
                  ->comment('Direction Générale — DG, DGA, PCA, Assistante_Dg, Conseillers_Dg, Secrétariat DG');

            $table->foreignId('direction_id')
                  ->nullable()
                  ->constrained('directions')
                  ->nullOnDelete()
                  ->comment('Direction fonctionnelle — agents des services centraux (DRH, DAF, DTIC…)');

            $table->foreignId('delegation_technique_id')
                  ->nullable()
                  ->constrained('delegation_techniques')
                  ->nullOnDelete()
                  ->comment('Délégation Technique de rattachement');

            $table->foreignId('caisse_id')
                  ->nullable()
                  ->constrained('caisses')
                  ->nullOnDelete()
                  ->comment('Caisse de rattachement');

            $table->foreignId('agence_id')
                  ->nullable()
                  ->constrained('agences')
                  ->nullOnDelete()
                  ->comment('Agence de rattachement');

            $table->foreignId('guichet_id')
                  ->nullable()
                  ->constrained('guichets')
                  ->nullOnDelete()
                  ->comment('Guichet de rattachement');

            $table->foreignId('service_id')
                  ->nullable()
                  ->constrained('services')
                  ->nullOnDelete()
                  ->comment('Service de rattachement (direction, DT ou caisse)');

            // ── Données personnelles ──────────────────────────────────────────
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('sexe', 20)->nullable();

            // Email professionnel : sert d'identifiant unique dans les selects
            $table->string('email', 191)->unique()
                  ->comment('Email professionnel — peut différer de users.email');

            $table->string('numero_telephone', 30)->nullable();
            $table->string('photo_path')->nullable();

            // ── Données professionnelles ──────────────────────────────────────
            $table->string('fonction', 100)
                  ->comment('Intitulé du poste : Directeur Régional, Caissier, Chef Service RH …');
            $table->date('date_debut_fonction')->nullable();

            $table->timestamps();
        });

        /**
         * Pose la FK users.agent_id → agents.id maintenant que la table agents existe.
         * Un compte User ne peut être lié qu'à UN SEUL Agent (unique).
         */
        Schema::table('users', function (Blueprint $table): void {
            $table->foreign('agent_id')
                  ->references('id')
                  ->on('agents')
                  ->nullOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Supprimer d'abord la FK sur users avant de dropper agents
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['agent_id']);
        });

        Schema::dropIfExists('agents');
    }
};
