<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agences', function (Blueprint $table): void {
            $table->id();
            $table->string('nom');

            // Rattachements hiérarchiques
            $table->foreignId('delegation_technique_id')->constrained('delegation_techniques')->cascadeOnDelete();
            $table->foreignId('superviseur_caisse_id')->constrained('caisses')->cascadeOnDelete();

            // Responsables : FK vers agents
            $table->foreignId('chef_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('secretaire_agent_id')->nullable()->constrained('agents')->nullOnDelete();

            $table->unique(['delegation_technique_id', 'nom'], 'agences_delegation_nom_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agences');
    }
};
