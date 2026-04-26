<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annees', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('annee')->unique();
            // AJOUT : Permet de bloquer les modifications sur les fiches d'une année clôturée
            $table->enum('statut', ['ouvert', 'cloture'])->default('ouvert');
            $table->timestamps();
        });
        
        // Ton script d'insertion automatique reste excellent, garde-le ici...
    }

    public function down(): void
    {
        Schema::dropIfExists('annees');
    }
};