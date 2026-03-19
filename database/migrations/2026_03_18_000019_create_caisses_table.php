<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caisses', function (Blueprint $table): void {
            $table->id();
            $table->string('nom');
            $table->string('directeur_nom');
            $table->string('directeur_email');
            $table->string('directeur_telephone', 30);
            $table->string('secretariat_telephone', 30);
            $table->foreignId('superviseur_direction_id')->constrained('directions')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caisses');
    }
};
