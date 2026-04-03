<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caisses', function (Blueprint $table) {
            $table->foreignId('delegation_technique_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('annee_ouverture', 4)->nullable()->after('nom');
            $table->string('quartier')->nullable()->after('annee_ouverture');
            $table->string('directeur_prenom')->nullable()->after('quartier');
            $table->string('directeur_sexe', 20)->nullable()->after('directeur_nom');
            $table->string('secretaire_prenom')->nullable()->after('secretariat_telephone');
            $table->string('secretaire_nom')->nullable()->after('secretaire_prenom');
            $table->string('secretaire_sexe', 20)->nullable()->after('secretaire_nom');
            $table->string('secretaire_email')->nullable()->after('secretaire_sexe');
            $table->string('secretaire_telephone', 30)->nullable()->after('secretaire_email');
        });
    }

    public function down(): void
    {
        Schema::table('caisses', function (Blueprint $table) {
            $table->dropForeign(['delegation_technique_id']);
            $table->dropColumn([
                'delegation_technique_id',
                'annee_ouverture',
                'quartier',
                'directeur_prenom',
                'directeur_sexe',
                'secretaire_prenom',
                'secretaire_nom',
                'secretaire_sexe',
                'secretaire_email',
                'secretaire_telephone',
            ]);
        });
    }
};
