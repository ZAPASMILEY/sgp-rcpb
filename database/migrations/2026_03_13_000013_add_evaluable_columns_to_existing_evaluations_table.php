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

        Schema::table('evaluations', function (Blueprint $table): void {
            if (! Schema::hasColumn('evaluations', 'evaluable_type')) {
                $table->string('evaluable_type')->nullable()->after('id');
            }

            if (! Schema::hasColumn('evaluations', 'evaluable_id')) {
                $table->unsignedBigInteger('evaluable_id')->nullable()->after('evaluable_type');
            }
        });

        if (
            Schema::hasColumn('evaluations', 'agent_id')
            && Schema::hasColumn('evaluations', 'evaluable_type')
            && Schema::hasColumn('evaluations', 'evaluable_id')
        ) {
            DB::table('evaluations')
                ->whereNull('evaluable_type')
                ->update([
                    'evaluable_type' => Agent::class,
                    'evaluable_id' => DB::raw('agent_id'),
                ]);
        }

        if (Schema::hasColumn('evaluations', 'evaluable_type') && Schema::hasColumn('evaluations', 'evaluable_id')) {
            Schema::table('evaluations', function (Blueprint $table): void {
                $table->index(['evaluable_type', 'evaluable_id'], 'evaluations_evaluable_lookup_index');
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
                $table->dropIndex('evaluations_evaluable_lookup_index');
                $table->dropColumn(['evaluable_type', 'evaluable_id']);
            });
        }
    }
};
