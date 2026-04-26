<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('objectifs', function (Blueprint $table): void {
            $table->id();
            
            // 1. Liaison polyvalente (Permet de fixer des objectifs à une Direction ou Caisse)
            $table->morphs('assignable'); 
            
            // On force la liaison avec l'année pour le reporting
            $table->foreignId('annee_id')->constrained('annees')->cascadeOnDelete();

            // 2. Dates et échéances
            $table->date('date');
            $table->date('date_echeance');

            // 3. Contenu et Progression
            $table->string('titre'); // Ajouté : pour nommer l'objectif stratégique
            $table->text('commentaire')->nullable();
            $table->unsignedTinyInteger('avancement_percentage')->default(0);
            
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('objectifs');
    }
};