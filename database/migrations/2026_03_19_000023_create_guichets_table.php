<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guichets', function (Blueprint $table): void {
            $table->id();
            $table->string('nom');
            $table->string('chef_nom');
            $table->string('chef_email');
            $table->string('chef_telephone', 30);
            $table->foreignId('agence_id')->constrained('agences')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guichets');
    }
};
