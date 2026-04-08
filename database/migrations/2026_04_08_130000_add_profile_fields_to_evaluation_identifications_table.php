<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_identifications', function (Blueprint $table): void {
            if (! Schema::hasColumn('evaluation_identifications', 'semestre')) {
                $table->string('semestre', 20)->nullable()->after('nom_prenom');
            }
            if (! Schema::hasColumn('evaluation_identifications', 'date_evaluation')) {
                $table->date('date_evaluation')->nullable()->after('date_recrutement');
            }
            if (! Schema::hasColumn('evaluation_identifications', 'matricule')) {
                $table->string('matricule')->nullable()->after('date_titularisation');
            }
            if (! Schema::hasColumn('evaluation_identifications', 'emploi')) {
                $table->string('emploi')->nullable()->after('poste');
            }
            if (! Schema::hasColumn('evaluation_identifications', 'direction_service')) {
                $table->string('direction_service')->nullable()->after('direction');
            }
            if (! Schema::hasColumn('evaluation_identifications', 'formations')) {
                $table->json('formations')->nullable()->after('date_affectation');
            }
            if (! Schema::hasColumn('evaluation_identifications', 'experiences')) {
                $table->json('experiences')->nullable()->after('formations');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_identifications', function (Blueprint $table): void {
            foreach (['semestre', 'date_evaluation', 'matricule', 'emploi', 'direction_service', 'formations', 'experiences'] as $column) {
                if (Schema::hasColumn('evaluation_identifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
