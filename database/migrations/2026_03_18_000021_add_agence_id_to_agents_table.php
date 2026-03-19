<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table): void {
            $table->dropForeign(['service_id']);
            $table->unsignedBigInteger('service_id')->nullable()->change();
            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();

            $table->foreignId('agence_id')->nullable()->after('service_id')->constrained('agences')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table): void {
            $table->dropForeign(['agence_id']);
            $table->dropColumn('agence_id');

            $table->dropForeign(['service_id']);
            $table->unsignedBigInteger('service_id')->nullable(false)->change();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
        });
    }
};
