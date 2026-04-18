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
        Schema::create('directions', function (Blueprint $table): void {
            $table->id();
            $table->string('nom');
            
            // Rattachements hiérarchiques
            $table->foreignId('entite_id')->constrained('entites')->cascadeOnDelete();
            $table->foreignId('delegation_technique_id')->nullable()->constrained('delegation_techniques')->nullOnDelete();
            
            // 1. Bloc Directeur (Fusion des infos personnelles et compte user)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('directeur_nom');
            $table->string('directeur_prenom')->nullable();
            $table->string('directeur_email');
            $table->string('directeur_numero', 30)->nullable();
            $table->string('directeur_region')->nullable();
            $table->string('directeur_sexe', 20)->nullable();
            $table->string('directeur_date_prise_fonction', 7)->nullable();

            // 2. Bloc Secrétariat (Fusion de la gestion complète du secrétariat)
            $table->foreignId('secretaire_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('secretaire_nom')->nullable();
            $table->string('secretaire_prenom')->nullable();
            $table->string('secretaire_email')->nullable();
            $table->string('secretaire_telephone', 30)->nullable();
            $table->string('secretaire_sexe', 20)->nullable();
            $table->string('secretaire_date_prise_fonction', 7)->nullable();
            
            // Ancien champ rendu nullable pour compatibilité
            $table->string('secretariat_telephone', 30)->nullable();

            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directions');
    }
};