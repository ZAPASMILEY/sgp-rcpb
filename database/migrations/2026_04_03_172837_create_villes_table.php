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
        Schema::create('villes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegation_technique_id')->constrained('delegation_techniques')->cascadeOnDelete();
            $table->string('nom');
            $table->timestamps();

            $table->unique(['delegation_technique_id', 'nom'], 'villes_dt_nom_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('villes');
    }
};
