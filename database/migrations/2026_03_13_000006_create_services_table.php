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
        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->string('nom');

            // 1. Rattachements (Flexibilité Direction ou Délégation)
            // direction_id est maintenant nullable pour permettre un rattachement technique
            $table->foreignId('direction_id')->nullable()->constrained('directions')->nullOnDelete();
            $table->foreignId('delegation_technique_id')->nullable()->constrained('delegation_techniques')->nullOnDelete();

            // 2. Bloc Chef de Service (Fusionné avec les nouveaux champs)
            $table->string('chef_prenom');
            $table->string('chef_nom');
            $table->string('chef_sexe')->nullable();
            $table->string('chef_email');
            $table->string('chef_telephone', 30);
            $table->string('chef_date_debut_mois')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};