<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('directions', function (Blueprint $table): void {
            $table->string('directeur_prenom')->nullable()->after('directeur_nom');
            $table->string('directeur_numero', 30)->nullable()->after('directeur_email');
            $table->string('directeur_region')->nullable()->after('directeur_numero');

            $table->unsignedBigInteger('secretaire_user_id')->nullable()->after('directeur_region');
            $table->foreign('secretaire_user_id')->references('id')->on('users')->nullOnDelete();

            $table->string('secretaire_prenom')->nullable()->after('secretaire_user_id');
            $table->string('secretaire_nom')->nullable()->after('secretaire_prenom');
            $table->string('secretaire_email')->nullable()->after('secretaire_nom');
            $table->string('secretaire_telephone', 30)->nullable()->after('secretaire_email');

            // make the old secretariat_telephone nullable so it is no longer required
            $table->string('secretariat_telephone', 30)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('directions', function (Blueprint $table): void {
            $table->dropForeign(['secretaire_user_id']);
            $table->dropColumn([
                'directeur_prenom',
                'directeur_numero',
                'directeur_region',
                'secretaire_user_id',
                'secretaire_prenom',
                'secretaire_nom',
                'secretaire_email',
                'secretaire_telephone',
            ]);
            $table->string('secretariat_telephone', 30)->nullable(false)->change();
        });
    }
};
