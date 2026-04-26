<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiche_objectif_objectifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiche_objectif_id')->constrained('fiche_objectifs')->onDelete('cascade');
            
            // AJOUT : Lien vers l'objectif stratégique parent
            $table->foreignId('objectif_id')->nullable()->constrained('objectifs')->nullOnDelete();
            
            $table->string('libelle');
            $table->text('indicateur_performance')->nullable(); 
            $table->unsignedInteger('poids')->default(0); 
            $table->decimal('note_obtenue', 5, 2)->default(0); 
            
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiche_objectif_objectifs');
    }
};