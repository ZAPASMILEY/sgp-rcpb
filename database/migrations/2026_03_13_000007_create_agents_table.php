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
        Schema::create('agents', function (Blueprint $table): void {
            $table->id();

            // 1. Liaison Compte Utilisateur (Fusion du add personnel)
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();

            // 2. Rattachements structurels (Flexibilité SGP-RCPB)
            $table->foreignId('delegation_technique_id')->nullable()->constrained('delegation_techniques')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('agence_id')->nullable()->constrained('agences')->nullOnDelete();
            
            // 3. Identité
            $table->string('nom');
            $table->string('prenom');
            $table->string('sexe', 20)->nullable();

            // 4. Poste et Carrière
            $table->string('fonction');
            $table->date('date_debut_fonction')->nullable();
            
            // 5. Contact et Médias
            $table->string('numero_telephone', 30);
            $table->string('email');
            $table->string('photo_path')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};