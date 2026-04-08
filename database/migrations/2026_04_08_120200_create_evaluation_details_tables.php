<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_identifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('evaluations')->cascadeOnDelete();
            $table->string('nom_prenom')->nullable();
            $table->date('date_recrutement')->nullable();
            $table->date('date_titularisation')->nullable();
            $table->string('poste')->nullable();
            $table->string('niveau')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('direction')->nullable();
            $table->date('date_confirmation')->nullable();
            $table->string('categorie')->nullable();
            $table->string('anciennete')->nullable();
            $table->string('sexe', 1)->nullable();
            $table->date('date_affectation')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluation_criteres', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('evaluations')->cascadeOnDelete();
            $table->string('type', 20);
            $table->unsignedInteger('ordre')->default(0);
            $table->string('titre');
            $table->text('description')->nullable();
            $table->decimal('note_globale', 8, 2)->default(0);
            $table->text('observation')->nullable();
            $table->unsignedBigInteger('source_template_id')->nullable();
            $table->foreignId('source_fiche_objectif_id')->nullable()->constrained('fiche_objectifs')->nullOnDelete();
            $table->foreignId('source_fiche_objectif_objectif_id')->nullable()->constrained('fiche_objectif_objectifs')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('evaluation_sous_criteres', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('evaluation_critere_id')->constrained('evaluation_criteres')->cascadeOnDelete();
            $table->unsignedInteger('ordre')->default(0);
            $table->string('libelle');
            $table->decimal('note', 8, 2)->default(0);
            $table->text('observation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_sous_criteres');
        Schema::dropIfExists('evaluation_criteres');
        Schema::dropIfExists('evaluation_identifications');
    }
};
