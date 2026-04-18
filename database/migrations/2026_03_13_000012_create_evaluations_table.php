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
        Schema::disableForeignKeyConstraints();
        Schema::create('evaluations', function (Blueprint $table): void {
            $table->id();

            // 1. Système Polymorphique (Flexibilité totale)
            $table->morphs('evaluable'); 
            $table->string('evaluable_role')->default('entity');
            
            // L'utilisateur qui effectue l'évaluation
            $table->foreignId('evaluateur_id')->constrained('users')->cascadeOnDelete();

            // 2. Période et Exercice (Fusion des besoins annuels)
            $table->foreignId('annee_id')->nullable()->constrained('annees')->nullOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');

            // 3. Calculs et Moyennes (Précision décimale SGP)
            $table->decimal('moyenne_subjectifs', 8, 2)->nullable();
            $table->decimal('note_criteres_subjectifs', 8, 2)->nullable();
            $table->decimal('moyenne_objectifs', 8, 2)->nullable();
            $table->decimal('note_criteres_objectifs', 8, 2)->nullable();
            
            // Notes de synthèse
            $table->unsignedTinyInteger('note_objectifs')->default(0)->comment('Moyenne avancement objectifs');
            $table->unsignedTinyInteger('note_manuelle')->nullable()->comment('Ajustement évaluateur');
            $table->unsignedTinyInteger('note_finale')->default(0)->comment('Note résultante calculée');

            // 4. Feedback et Développement
            $table->text('commentaire')->nullable();
            $table->text('points_a_ameliorer')->nullable();
            $table->text('strategies_amelioration')->nullable();
            $table->text('commentaires_evalue')->nullable();

            // 5. Workflow et Statut
            $table->enum('statut', ['brouillon', 'soumis', 'valide'])->default('brouillon');

            // 6. Bloc des Signatures (Validation réseau RCPB)
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};