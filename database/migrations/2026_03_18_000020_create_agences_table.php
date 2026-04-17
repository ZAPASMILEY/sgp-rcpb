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
        Schema::create('agences', function (Blueprint $table): void {
            $table->id();
            $table->string('nom');
            
            // 1. Bloc Chef d'Agence
            $table->string('chef_nom');
            $table->string('chef_email');
            $table->string('chef_telephone', 30);

            // 2. Bloc Secrétariat
            $table->string('secretaire_nom');
            $table->string('secretaire_email');
            $table->string('secretaire_telephone', 30);

            // 3. Rattachements hiérarchiques
            $table->foreignId('delegation_technique_id')->constrained('delegation_techniques')->cascadeOnDelete();
            $table->foreignId('superviseur_caisse_id')->constrained('caisses')->cascadeOnDelete();
            
            $table->timestamps();

            // 4. Index d'unicité (Fusion du add)
            // Empêche les doublons de noms d'agence au sein d'une même délégation
            $table->unique(['delegation_technique_id', 'nom'], 'agences_delegation_nom_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agences');
    }
};