<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('evaluations', 'evaluable_role')) {
            Schema::table('evaluations', function (Blueprint $table): void {
                $table->string('evaluable_role')->default('entity')->after('evaluable_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('evaluations', 'evaluable_role')) {
            Schema::table('evaluations', function (Blueprint $table): void {
                $table->dropColumn('evaluable_role');
            });
        }
    }
};
