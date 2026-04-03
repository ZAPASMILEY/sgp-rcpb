<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delegation_techniques', function (Blueprint $table): void {
            $table->string('directeur_prenom')->nullable()->after('secretariat_telephone');
            $table->string('directeur_nom')->nullable()->after('directeur_prenom');
            $table->string('directeur_sexe', 20)->nullable()->after('directeur_nom');
            $table->string('directeur_email')->nullable()->after('directeur_sexe');
            $table->string('directeur_telephone', 30)->nullable()->after('directeur_email');
            $table->string('directeur_date_debut_mois', 7)->nullable()->after('directeur_telephone');
        });
    }

    public function down(): void
    {
        Schema::table('delegation_techniques', function (Blueprint $table): void {
            $table->dropColumn([
                'directeur_prenom',
                'directeur_nom',
                'directeur_sexe',
                'directeur_email',
                'directeur_telephone',
                'directeur_date_debut_mois',
            ]);
        });
    }
};
