<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('caisses', function (Blueprint $table): void {
            $table->id();

            // Rattachements hiérarchiques
            $table->foreignId('delegation_technique_id')->nullable()->constrained('delegation_techniques')->nullOnDelete();
            $table->foreignId('ville_id')->nullable()->constrained('villes')->nullOnDelete();
            $table->foreignId('superviseur_direction_id')->nullable()->constrained('directions')->nullOnDelete();

            $table->string('nom')->unique();
            $table->string('annee_ouverture', 4)->nullable();
            $table->string('quartier')->nullable();
            $table->string('secretariat_telephone', 30)->nullable();

            // Responsables : FK vers agents
            $table->foreignId('directeur_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('secretaire_agent_id')->nullable()->constrained('agents')->nullOnDelete();

            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('caisses');
    }
};
