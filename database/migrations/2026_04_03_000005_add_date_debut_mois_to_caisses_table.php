<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caisses', function (Blueprint $table) {
            $table->string('directeur_date_debut_mois', 7)->nullable()->after('directeur_telephone');
            $table->string('secretaire_date_debut_mois', 7)->nullable()->after('secretaire_telephone');
        });
    }

    public function down(): void
    {
        Schema::table('caisses', function (Blueprint $table) {
            $table->dropColumn(['directeur_date_debut_mois', 'secretaire_date_debut_mois']);
        });
    }
};
