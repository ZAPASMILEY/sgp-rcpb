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
            
            // Lien avec l'année budgétaire
            $table->foreignId('annee_id')->constrained('annees')->cascadeOnDelete();

            // Cible polymorphique (User, Direction, etc.)
            $table->unsignedBigInteger('assignable_id');
            $table->string('assignable_type');
            $table->index(['assignable_type', 'assignable_id'], 'fiche_objectifs_assignable_index');

            $table->date('date');
            $table->date('date_echeance');
            $table->unsignedTinyInteger('avancement_percentage')->default(0);
            $table->enum('statut', ['brouillon', 'en_attente', 'acceptee', 'refusee'])->default('brouillon');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiche_objectifs');
    }
};