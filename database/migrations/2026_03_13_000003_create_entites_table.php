<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('entites', function (Blueprint $table): void {
            $table->id();
            $table->tinyInteger('singleton')->unsigned()->default(1)
                  ->comment('Valeur toujours 1. Contrainte UNIQUE garantit une seule ligne.');
            $table->string('nom')->unique();
            $table->string('ville');
            $table->string('region')->nullable();
            $table->string('secretariat_telephone', 30)->nullable();

            // Responsables : FK vers agents (sélectionnés, pas dénormalisés)
            $table->foreignId('dg_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('dga_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('dga_secretaire_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('pca_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('assistante_agent_id')->nullable()->constrained('agents')->nullOnDelete();

            $table->timestamps();

            $table->unique('singleton');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('entites');
    }
};
