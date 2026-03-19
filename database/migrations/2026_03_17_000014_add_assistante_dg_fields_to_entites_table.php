<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entites', function (Blueprint $table): void {
            $table->string('assistante_dg_prenom')->nullable()->after('dga_email');
            $table->string('assistante_dg_nom')->nullable()->after('assistante_dg_prenom');
            $table->string('assistante_dg_email')->nullable()->after('assistante_dg_nom');
        });
    }

    public function down(): void
    {
        Schema::table('entites', function (Blueprint $table): void {
            $table->dropColumn(['assistante_dg_prenom', 'assistante_dg_nom', 'assistante_dg_email']);
        });
    }
};