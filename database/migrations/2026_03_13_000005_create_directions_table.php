<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('directions', function (Blueprint $table): void {
            $table->id();
            $table->string('nom');
            $table->foreignId('entite_id')->constrained('entites')->cascadeOnDelete();
            $table->string('directeur_nom');
            $table->string('directeur_email');
            $table->string('secretariat_telephone', 30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directions');
    }
};
