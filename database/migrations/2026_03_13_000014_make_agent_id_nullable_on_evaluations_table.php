<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('evaluations') || ! Schema::hasColumn('evaluations', 'agent_id')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE evaluations MODIFY agent_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('evaluations') || ! Schema::hasColumn('evaluations', 'agent_id')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE evaluations MODIFY agent_id BIGINT UNSIGNED NOT NULL');
        }
    }
};
