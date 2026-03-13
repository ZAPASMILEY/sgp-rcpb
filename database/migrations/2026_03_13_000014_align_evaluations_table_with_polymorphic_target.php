<?php

use App\Models\Agent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('evaluations')) {
            return;
        }

        if (! Schema::hasColumn('evaluations', 'evaluable_type')) {
            Schema::table('evaluations', function (Blueprint $table): void {
                $table->string('evaluable_type')->nullable()->after('id');
            });
        }

        if (! Schema::hasColumn('evaluations', 'evaluable_id')) {
            Schema::table('evaluations', function (Blueprint $table): void {
                $table->unsignedBigInteger('evaluable_id')->nullable()->after('evaluable_type');
            });
        }

        if (! Schema::hasColumn('evaluations', 'agent_id') && Schema::hasColumn('evaluations', 'evaluable_type') && Schema::hasColumn('evaluations', 'evaluable_id')) {
            DB::table('evaluations')
                ->whereNull('evaluable_type')
                ->orWhereNull('evaluable_id')
                ->update([
                    'evaluable_type' => Agent::class,
                ]);
        }

        if (Schema::hasColumn('evaluations', 'agent_id')) {
            DB::table('evaluations')
                ->whereNull('evaluable_type')
                ->whereNotNull('agent_id')
                ->update([
                    'evaluable_type' => Agent::class,
                ]);

            DB::table('evaluations')
                ->whereNull('evaluable_id')
                ->whereNotNull('agent_id')
                ->update([
                    'evaluable_id' => DB::raw('agent_id'),
                ]);

            if (DB::getDriverName() === 'mysql') {
                // Keep legacy column for rollback compatibility, but stop forcing it on inserts.
                DB::statement('ALTER TABLE evaluations MODIFY agent_id BIGINT UNSIGNED NULL');
            }
        }

        if (Schema::hasColumn('evaluations', 'evaluable_type') && Schema::hasColumn('evaluations', 'evaluable_id')) {
            Schema::table('evaluations', function (Blueprint $table): void {
                $table->index(['evaluable_type', 'evaluable_id'], 'evaluations_evaluable_type_id_idx');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('evaluations')) {
            return;
        }

        if (Schema::hasColumn('evaluations', 'evaluable_type') && Schema::hasColumn('evaluations', 'evaluable_id')) {
            Schema::table('evaluations', function (Blueprint $table): void {
                $table->dropIndex('evaluations_evaluable_type_id_idx');
                $table->dropColumn(['evaluable_type', 'evaluable_id']);
            });
        }
    }
};
