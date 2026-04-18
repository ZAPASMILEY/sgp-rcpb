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
        Schema::create('caisses', function (Blueprint $table): void {
            $table->id();
            
            // 1. Structure et Localisation
            $table->foreignId('delegation_technique_id')->nullable()->constrained('delegation_techniques')->nullOnDelete();
            $table->foreignId('ville_id')->nullable()->constrained('villes')->nullOnDelete();
            $table->string('nom')->unique(); // Sécurisé par unique()
            $table->string('annee_ouverture', 4)->nullable();
            $table->string('quartier')->nullable();

            // 2. Bloc Directeur
            $table->string('directeur_nom');
            $table->string('directeur_prenom')->nullable();
            $table->string('directeur_sexe', 20)->nullable();
            $table->string('directeur_email')->unique(); // Sécurisé par unique()
            $table->string('directeur_telephone', 30)->unique(); // Sécurisé par unique()
            $table->string('directeur_date_debut_mois', 7)->nullable();

            // 3. Bloc Secrétariat
            $table->string('secretaire_nom')->nullable();
            $table->string('secretaire_prenom')->nullable();
            $table->string('secretaire_sexe', 20)->nullable();
            $table->string('secretaire_email')->nullable()->unique(); // Sécurisé par unique()
            $table->string('secretaire_telephone', 30)->nullable()->unique(); // Sécurisé par unique()
            $table->string('secretaire_date_debut_mois', 7)->nullable();
            
            // Contact générique de la structure
            $table->string('secretariat_telephone', 30);
            
            // 4. Supervision
            $table->foreignId('superviseur_direction_id')->nullable()->constrained('directions')->nullOnDelete();
            
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caisses');
    }
};