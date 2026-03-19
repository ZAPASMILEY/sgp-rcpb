<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entites', function (Blueprint $table): void {
            $table->string('region')->nullable()->after('ville');
        });
    }

    public function down(): void
    {
        Schema::table('entites', function (Blueprint $table): void {
            $table->dropColumn('region');
        });
    }
};