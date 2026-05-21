<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Table des postes prédéfinis par fonction ──────────────────────────
        Schema::create('fonction', function (Blueprint $table): void {
            $table->id();
            $table->string('fonction', 100)->index()->comment('Clé de Agent::FONCTIONS (ex: Agent, Conseiller DG)');
            $table->string('libelle', 150)->comment('Intitulé affiché (ex: Caissier prestataire)');
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
        Schema::dropIfExists('fonction');

        if (Schema::hasColumn('agents', 'fonction')) {
            Schema::table('agents', function (Blueprint $table): void {
                $table->dropColumn('fonction');
            });
        }
    }
};
