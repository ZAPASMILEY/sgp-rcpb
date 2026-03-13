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
        Schema::create('entites', function (Blueprint $table): void {
            $table->id();
            $table->string('nom');
            $table->string('ville');
            $table->string('directeur_general_nom');
            $table->text('directeur_general_informations')->nullable();
            $table->string('pca_nom');
            $table->text('pca_informations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entites');
    }
};
