<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delegation_techniques', function (Blueprint $table): void {
            $table->string('secretaire_prenom')->nullable()->after('directeur_date_debut_mois');
            $table->string('secretaire_nom')->nullable()->after('secretaire_prenom');
            $table->string('secretaire_sexe', 20)->nullable()->after('secretaire_nom');
            $table->string('secretaire_email')->nullable()->after('secretaire_sexe');
            $table->string('secretaire_telephone', 30)->nullable()->after('secretaire_email');
            $table->string('secretaire_date_debut_mois', 7)->nullable()->after('secretaire_telephone');
            $table->string('directeur_photo_path')->nullable()->after('directeur_date_debut_mois');
        });
    }

    public function down(): void
    {
        Schema::table('delegation_techniques', function (Blueprint $table): void {
            $table->dropColumn([
                'secretaire_prenom',
                'secretaire_nom',
                'secretaire_sexe',
                'secretaire_email',
                'secretaire_telephone',
                'secretaire_date_debut_mois',
                'directeur_photo_path',
            ]);
        });
    }
};
