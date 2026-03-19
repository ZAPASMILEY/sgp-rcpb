<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agences', function (Blueprint $table): void {
            $table->id();
            $table->string('nom');
            $table->string('chef_nom');
            $table->string('chef_email');
            $table->string('chef_telephone', 30);
            $table->string('secretaire_nom');
            $table->string('secretaire_email');
            $table->string('secretaire_telephone', 30);
            $table->foreignId('delegation_technique_id')->constrained('delegation_techniques')->cascadeOnDelete();
            $table->foreignId('superviseur_caisse_id')->constrained('caisses')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agences');
    }
};
