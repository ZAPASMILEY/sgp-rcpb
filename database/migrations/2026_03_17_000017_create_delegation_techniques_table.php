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
        Schema::create('delegation_techniques', function (Blueprint $table): void {
            $table->id();
            
            // 1. Localisation
            $table->string('region');
            $table->string('ville');
            
            // 2. Bloc Directeur (Fusion des deux adds)
            $table->string('directeur_nom')->nullable();
            $table->string('directeur_prenom')->nullable();
            $table->string('directeur_sexe', 20)->nullable();
            $table->string('directeur_email')->nullable();
            $table->string('directeur_telephone', 30)->nullable();
            $table->string('directeur_date_debut_mois', 7)->nullable();
            $table->string('directeur_photo_path')->nullable();

            // 3. Bloc Secrétariat (Fusion du dernier add + champ original)
            $table->string('secretaire_nom')->nullable();
            $table->string('secretaire_prenom')->nullable();
            $table->string('secretaire_sexe', 20)->nullable();
            $table->string('secretaire_email')->nullable();
            $table->string('secretaire_telephone', 30)->nullable();
            $table->string('secretaire_date_debut_mois', 7)->nullable();
            
            // Champ de contact générique original
            $table->string('secretariat_telephone', 30);

            $table->timestamps();

            // Contrainte d'unicité originale
            $table->unique(['region', 'ville']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegation_techniques');
    }
};