<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiche_objectifs', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->year('annee');
            $table->foreignId('assignable_id'); // DG ou autre cible
            $table->string('assignable_type');
            $table->date('date');
            $table->date('date_echeance');
            $table->unsignedTinyInteger('avancement_percentage')->default(0);
            $table->enum('statut', ['en_attente', 'acceptee', 'refusee'])->default('en_attente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiche_objectifs');
    }
};
