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
            $table->string('nom')->unique();
            $table->string('ville');
            $table->string('region')->nullable();
            $table->string('secretariat_telephone', 30)->nullable();

            // Responsables : FK vers agents (sélectionnés, pas dénormalisés)
            $table->foreignId('dg_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('dga_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('pca_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('assistante_agent_id')->nullable()->constrained('agents')->nullOnDelete();

            $table->timestamps();
        });

        // FK users.pca_entite_id → entites.id
        Schema::table('users', function (Blueprint $table): void {
            $table->foreign('pca_entite_id')->references('id')->on('entites')->nullOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['pca_entite_id']);
        });
        Schema::dropIfExists('entites');
    }
};
