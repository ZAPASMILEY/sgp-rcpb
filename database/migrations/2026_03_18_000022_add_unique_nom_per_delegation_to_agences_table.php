<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Consolidate existing duplicates before adding the unique index.
        $duplicateGroups = DB::table('agences')
            ->select('delegation_technique_id', 'nom', DB::raw('COUNT(*) as total'))
            ->groupBy('delegation_technique_id', 'nom')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $agenceIds = DB::table('agences')
                ->where('delegation_technique_id', $group->delegation_technique_id)
                ->where('nom', $group->nom)
                ->orderBy('id')
                ->pluck('id')
                ->all();

            if (count($agenceIds) < 2) {
                continue;
            }

            $keepId = (int) array_shift($agenceIds);
            $duplicateIds = array_map(static fn ($id): int => (int) $id, $agenceIds);

            DB::table('agents')
                ->whereIn('agence_id', $duplicateIds)
                ->update(['agence_id' => $keepId]);

            DB::table('agences')
                ->whereIn('id', $duplicateIds)
                ->delete();
        }

        Schema::table('agences', function (Blueprint $table): void {
            $table->unique(['delegation_technique_id', 'nom'], 'agences_delegation_nom_unique');
        });
    }

    public function down(): void
    {
        Schema::table('agences', function (Blueprint $table): void {
            $table->dropUnique('agences_delegation_nom_unique');
        });
    }
};
