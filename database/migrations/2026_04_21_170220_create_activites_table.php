<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activites', function (Blueprint $table) {
            $table->id();
            // L'utilisateur qui a fait l'action
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('action'); // ex: 'CHANGEMENT_ROLE', 'VALIDATION_EVALUATION'
            $table->text('description'); // ex: 'A passé l\'agent Alizeta de Secrétaire à Directeur'
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable(); // Pour savoir si c'est fait via PC ou Mobile
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activites');
    }
};