<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiche_objectifs', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            
            // 1. LIEN AVEC L'ANNÉE (Indispensable pour ton diagramme)
            // On remplace year('annee') par une clé étrangère vers ta table 'annees'
            $table->foreignId('annee_id')->constrained('annees')->cascadeOnDelete();
            
            // 2. L'AGENT ÉVALUÉ (L'employé qui va recevoir la note)
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();

            // 3. LA CIBLE (Ton système polymorphique DG, Direction, etc.)
            $table->foreignId('assignable_id'); 
            $table->string('assignable_type');
            
            $table->date('date');
            $table->date('date_echeance');
            
            $table->unsignedTinyInteger('avancement_percentage')->default(0);
            
            // 4. STATUTS (Ajout du statut 'brouillon' pour la préparation)
            $table->enum('statut', ['brouillon', 'en_attente', 'acceptee', 'refusee'])->default('brouillon');
            
            // 5. SÉCURITÉ : Un agent ne peut pas avoir deux fiches pour la même année
            $table->unique(['agent_id', 'annee_id'], 'unique_fiche_par_agent_et_an');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiche_objectifs');
    }
};