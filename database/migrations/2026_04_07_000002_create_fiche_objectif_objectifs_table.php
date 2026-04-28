<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_fiche_objectif', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiche_objectif_id')->constrained('fiche_objectifs')->cascadeOnDelete();
            $table->text('description');
            $table->decimal('note_obtenue', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_fiche_objectif');
    }
};