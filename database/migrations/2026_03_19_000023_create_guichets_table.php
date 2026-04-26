<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guichets', function (Blueprint $table): void {
            $table->id();
            $table->string('nom');

            // Rattachement hiérarchique
            $table->foreignId('agence_id')->constrained('agences')->cascadeOnDelete();

            // Responsable : FK vers agent
            $table->foreignId('chef_agent_id')->nullable()->constrained('agents')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guichets');
    }
};
