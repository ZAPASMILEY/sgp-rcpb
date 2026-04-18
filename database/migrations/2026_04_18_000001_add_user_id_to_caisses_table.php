<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caisses', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('secretaire_user_id')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('caisses', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['secretaire_user_id']);
            $table->dropColumn(['user_id', 'secretaire_user_id']);
        });
    }
};
