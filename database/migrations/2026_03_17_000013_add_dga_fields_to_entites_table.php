<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entites', function (Blueprint $table): void {
            $table->string('dga_prenom')->nullable()->after('directrice_generale_email');
            $table->string('dga_nom')->nullable()->after('dga_prenom');
            $table->string('dga_email')->nullable()->after('dga_nom');
        });
    }

    public function down(): void
    {
        Schema::table('entites', function (Blueprint $table): void {
            $table->dropColumn(['dga_prenom', 'dga_nom', 'dga_email']);
        });
    }
};