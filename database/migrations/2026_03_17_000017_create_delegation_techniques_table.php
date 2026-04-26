<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delegation_techniques', function (Blueprint $table): void {
            $table->id();

            // Rattachement à la faîtière
            $table->foreignId('entite_id')->nullable()->constrained('entites')->nullOnDelete();

            // Localisation
            $table->string('region');
            $table->string('ville');
            $table->string('secretariat_telephone', 30)->nullable();

            // Responsables : FK vers agents
            $table->foreignId('directeur_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('secretaire_agent_id')->nullable()->constrained('agents')->nullOnDelete();

            $table->unique(['region', 'ville']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delegation_techniques');
    }
};
