<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('objectifs', function (Blueprint $table): void {
            $table->foreignId('annee_id')->nullable()->after('assignable_id')->constrained('annees')->nullOnDelete();
        });

        Schema::table('evaluations', function (Blueprint $table): void {
            $table->foreignId('annee_id')->nullable()->after('evaluable_role')->constrained('annees')->nullOnDelete();
        });

        $anneeIds = DB::table('annees')->pluck('id', 'annee');

        foreach (DB::table('objectifs')->select(['id', 'date'])->get() as $objectif) {
            if (! $objectif->date) {
                continue;
            }

            $year = (int) Carbon::parse($objectif->date)->year;
            $anneeId = $anneeIds[$year] ?? null;

            if ($anneeId !== null) {
                DB::table('objectifs')->where('id', $objectif->id)->update(['annee_id' => $anneeId]);
            }
        }

        foreach (DB::table('evaluations')->select(['id', 'date_debut'])->get() as $evaluation) {
            if (! $evaluation->date_debut) {
                continue;
            }

            $year = (int) Carbon::parse($evaluation->date_debut)->year;
            $anneeId = $anneeIds[$year] ?? null;

            if ($anneeId !== null) {
                DB::table('evaluations')->where('id', $evaluation->id)->update(['annee_id' => $anneeId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('annee_id');
        });

        Schema::table('objectifs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('annee_id');
        });
    }
};