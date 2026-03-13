<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table): void {
            $table->id();
            $table->string('evaluable_type');
            $table->unsignedBigInteger('evaluable_id');
            $table->index(['evaluable_type', 'evaluable_id']);
            $table->foreignId('evaluateur_id')->constrained('users')->cascadeOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->unsignedTinyInteger('note_objectifs')->default(0)->comment('Moyenne avancement objectifs (0-100)');
            $table->unsignedTinyInteger('note_manuelle')->nullable()->comment('Ajustement evaluateur (0-100)');
            $table->unsignedTinyInteger('note_finale')->default(0)->comment('Note resultante calculee (0-100)');
            $table->text('commentaire')->nullable();
            $table->enum('statut', ['brouillon', 'soumis', 'valide'])->default('brouillon');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
