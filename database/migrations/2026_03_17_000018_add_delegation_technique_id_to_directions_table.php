<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('directions', function (Blueprint $table): void {
            $table->foreignId('delegation_technique_id')
                ->nullable()
                ->after('entite_id')
                ->constrained('delegation_techniques')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('directions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('delegation_technique_id');
        });
    }
};
