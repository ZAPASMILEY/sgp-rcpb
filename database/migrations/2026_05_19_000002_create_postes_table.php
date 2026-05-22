<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Table des postes prédéfinis par fonction ──────────────────────────
        Schema::create('postes', function (Blueprint $table): void {
            $table->id();
            $table->string('fonction', 100)->index()->comment('Rôle de l\'agent (ex: Agent, Conseiller DG)');
            $table->string('libelle', 150)->comment('Intitulé du poste affiché (ex: Caissier, Chargé de crédit)');
            $table->timestamps();

            $table->unique(['fonction', 'libelle']);
        });

        // ── Colonne poste sur agents (nullable) ───────────────────────────────
        if (! Schema::hasColumn('agents', 'fonction')) {
            Schema::table('agents', function (Blueprint $table): void {
                $table->string('fonction', 100)->nullable()->after('role')
                      ->comment('Fonction dans laquelle l\'agent exerce (ex: Agent, Conseiller DG)');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('postes');

        if (Schema::hasColumn('agents', 'fonction')) {
            Schema::table('agents', function (Blueprint $table): void {
                $table->dropColumn('fonction');
            });
        }
    }
};
