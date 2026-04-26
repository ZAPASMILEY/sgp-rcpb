<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('evaluations', function (Blueprint $table): void {
            $table->id();

            // 1. Destinataire et Évaluateur (Cardinalités 17, 19, 20)
            // On ajoute agent_id pour la clarté des stats
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->foreignId('evaluateur_id')->constrained('users')->cascadeOnDelete();
            
            // On garde ta flexibilité morphique si tu veux évaluer des "entités"
            $table->morphs('evaluable'); 
            $table->string('evaluable_role')->default('agent');
            
            // 2. Lien avec le travail effectué (Indispensable pour le calcul)
            $table->foreignId('fiche_objectif_id')->nullable()->constrained('fiche_objectifs')->nullOnDelete();

            // 3. Période et Exercice
            $table->foreignId('annee_id')->nullable()->constrained('annees')->nullOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');

            // 4. Calculs et Moyennes (Précision SGP)
            $table->decimal('moyenne_subjectifs', 8, 2)->nullable();
            $table->decimal('moyenne_objectifs', 8, 2)->nullable();
            $table->decimal('note_criteres_subjectifs', 8, 2)->default(0);
            $table->decimal('note_criteres_objectifs', 8, 2)->default(0);
            $table->unsignedTinyInteger('note_objectifs')->default(0);
            $table->unsignedTinyInteger('note_manuelle')->nullable();
            $table->decimal('note_finale', 8, 2)->default(0);

            // 5. Feedback
            $table->text('commentaire')->nullable();
            $table->text('points_a_ameliorer')->nullable();
            $table->text('strategies_amelioration')->nullable();
            $table->text('commentaires_evalue')->nullable();

            $table->enum('statut', ['brouillon', 'soumis', 'valide'])->default('brouillon');

            // 6. Bloc des Signatures
            $table->string('signature_evalue_nom')->nullable();
            $table->date('date_signature_evalue')->nullable();
            $table->string('signature_evaluateur_nom')->nullable();
            $table->date('date_signature_evaluateur')->nullable();
            $table->string('signature_directeur_nom')->nullable();
            $table->date('date_signature_directeur')->nullable();

            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};