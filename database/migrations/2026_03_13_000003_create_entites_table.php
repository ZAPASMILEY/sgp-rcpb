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
        Schema::create('entites', function (Blueprint $table): void {
            $table->id();
            $table->string('nom')->unique();
            
            // 1. Localisation
            $table->string('ville');
            $table->string('region')->nullable();

            // 2. Direction Générale (DG)
            $table->string('directrice_generale_prenom')->nullable();
            $table->string('directrice_generale_nom')->nullable();
            $table->string('directrice_generale_email')->nullable()->unique();
            $table->string('directrice_generale_photo_path')->nullable();
            $table->string('directrice_generale_sexe', 20)->nullable();
            $table->string('directrice_generale_date_prise_fonction', 7)->nullable();

            // 3. Direction Générale Adjointe (DGA)
            $table->string('dga_nom')->nullable();
            $table->string('dga_prenom')->nullable();
            $table->string('dga_email')->nullable()->unique();
            $table->string('dga_photo_path')->nullable();
            $table->string('dga_sexe', 20)->nullable();
            $table->string('dga_date_prise_fonction', 7)->nullable();

            // 4. Conseil d'Administration (PCA)
            $table->string('pca_prenom')->nullable();
            $table->string('pca_nom')->nullable();
            $table->string('pca_email')->nullable()->unique();
            $table->string('pca_photo_path')->nullable();
            $table->string('pca_sexe', 20)->nullable();
            $table->string('pca_date_prise_fonction', 7)->nullable();

            // 5. Secrétariat / Assistance
            $table->string('assistante_dg_nom')->nullable();
            $table->string('assistante_dg_prenom')->nullable();
            $table->string('assistante_dg_email')->nullable()->unique();
            $table->string('assistante_dg_sexe', 20)->nullable();
            $table->string('assistante_dg_date_prise_fonction', 7)->nullable();
            $table->string('secretariat_telephone', 30)->nullable()->unique();

            $table->timestamps();
        });

        // Ajouter la FK users.pca_entite_id → entites.id (maintenant que entites existe)
        Schema::table('users', function (Blueprint $table): void {
            $table->foreign('pca_entite_id')->references('id')->on('entites')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['pca_entite_id']);
        });
        Schema::dropIfExists('entites');
    }
};