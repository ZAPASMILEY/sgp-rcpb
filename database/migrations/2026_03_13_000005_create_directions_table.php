<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('directions', function (Blueprint $table): void {
            $table->id();
            $table->string('nom');

            // Rattachement : toutes les directions appartiennent à la faîtière
            $table->foreignId('entite_id')->nullable()->constrained('entites')->nullOnDelete();

            // Responsables : FK vers agents
            $table->foreignId('directeur_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('secretaire_agent_id')->nullable()->constrained('agents')->nullOnDelete();

            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('directions');
    }
};
