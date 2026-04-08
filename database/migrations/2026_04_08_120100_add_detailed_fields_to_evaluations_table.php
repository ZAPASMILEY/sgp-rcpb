<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table): void {
            if (! Schema::hasColumn('evaluations', 'moyenne_subjectifs')) {
                $table->decimal('moyenne_subjectifs', 8, 2)->nullable()->after('date_fin');
            }
            if (! Schema::hasColumn('evaluations', 'note_criteres_subjectifs')) {
                $table->decimal('note_criteres_subjectifs', 8, 2)->nullable()->after('moyenne_subjectifs');
            }
            if (! Schema::hasColumn('evaluations', 'moyenne_objectifs')) {
                $table->decimal('moyenne_objectifs', 8, 2)->nullable()->after('note_criteres_subjectifs');
            }
            if (! Schema::hasColumn('evaluations', 'note_criteres_objectifs')) {
                $table->decimal('note_criteres_objectifs', 8, 2)->nullable()->after('moyenne_objectifs');
            }
            if (! Schema::hasColumn('evaluations', 'points_a_ameliorer')) {
                $table->text('points_a_ameliorer')->nullable()->after('commentaire');
            }
            if (! Schema::hasColumn('evaluations', 'strategies_amelioration')) {
                $table->text('strategies_amelioration')->nullable()->after('points_a_ameliorer');
            }
            if (! Schema::hasColumn('evaluations', 'commentaires_evalue')) {
                $table->text('commentaires_evalue')->nullable()->after('strategies_amelioration');
            }
            if (! Schema::hasColumn('evaluations', 'signature_evalue_nom')) {
                $table->string('signature_evalue_nom')->nullable()->after('commentaires_evalue');
            }
            if (! Schema::hasColumn('evaluations', 'signature_directeur_nom')) {
                $table->string('signature_directeur_nom')->nullable()->after('signature_evalue_nom');
            }
            if (! Schema::hasColumn('evaluations', 'signature_evaluateur_nom')) {
                $table->string('signature_evaluateur_nom')->nullable()->after('signature_directeur_nom');
            }
            if (! Schema::hasColumn('evaluations', 'date_signature_evalue')) {
                $table->date('date_signature_evalue')->nullable()->after('signature_evaluateur_nom');
            }
            if (! Schema::hasColumn('evaluations', 'date_signature_directeur')) {
                $table->date('date_signature_directeur')->nullable()->after('date_signature_evalue');
            }
            if (! Schema::hasColumn('evaluations', 'date_signature_evaluateur')) {
                $table->date('date_signature_evaluateur')->nullable()->after('date_signature_directeur');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table): void {
            $columns = [
                'moyenne_subjectifs',
                'note_criteres_subjectifs',
                'moyenne_objectifs',
                'note_criteres_objectifs',
                'points_a_ameliorer',
                'strategies_amelioration',
                'commentaires_evalue',
                'signature_evalue_nom',
                'signature_directeur_nom',
                'signature_evaluateur_nom',
                'date_signature_evalue',
                'date_signature_directeur',
                'date_signature_evaluateur',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('evaluations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
