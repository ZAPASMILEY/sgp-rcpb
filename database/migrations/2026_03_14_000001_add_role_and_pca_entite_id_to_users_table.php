<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->enum('role', ['admin', 'pca'])->default('admin')->after('email');
            $table->foreignId('pca_entite_id')->nullable()->constrained('entites')->nullOnDelete()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['pca_entite_id']);
            $table->dropColumn(['role', 'pca_entite_id']);
        });
    }
};
