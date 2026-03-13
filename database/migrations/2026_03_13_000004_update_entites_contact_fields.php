<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('entites', function (Blueprint $table): void {
            $table->string('directrice_generale_prenom')->nullable()->after('ville');
            $table->string('directrice_generale_nom')->nullable()->after('directrice_generale_prenom');
            $table->string('directrice_generale_email')->nullable()->after('directrice_generale_nom');
            $table->string('pca_prenom')->nullable()->after('directrice_generale_email');
            $table->string('pca_email')->nullable()->after('pca_nom');
            $table->string('secretariat_telephone', 30)->nullable()->after('pca_email');
        });

        Schema::table('entites', function (Blueprint $table): void {
            $table->dropColumn(['directeur_general_nom', 'directeur_general_informations', 'pca_informations']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entites', function (Blueprint $table): void {
            $table->string('directeur_general_nom')->nullable()->after('ville');
            $table->text('directeur_general_informations')->nullable()->after('directeur_general_nom');
            $table->text('pca_informations')->nullable()->after('pca_nom');
        });

        Schema::table('entites', function (Blueprint $table): void {
            $table->dropColumn([
                'directrice_generale_prenom',
                'directrice_generale_nom',
                'directrice_generale_email',
                'pca_prenom',
                'pca_email',
                'secretariat_telephone',
            ]);
        });
    }
};
