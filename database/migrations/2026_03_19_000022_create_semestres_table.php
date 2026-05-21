<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semestres', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('annee_id')->constrained('annees')->cascadeOnDelete();
            $table->unsignedTinyInteger('numero'); // 1 ou 2
            $table->enum('statut', ['ouvert', 'cloture'])->default('cloture');
            $table->timestamps();

            $table->unique(['annee_id', 'numero']);
        });

        // Pose la contrainte FK sur evaluations.semestre_id maintenant que semestres existe
        Schema::table('evaluations', function (Blueprint $table): void {
            $table->foreign('semestre_id')->references('id')->on('semestres')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table): void {
            $table->dropForeign(['semestre_id']);
        });
        Schema::dropIfExists('semestres');
    }
};
