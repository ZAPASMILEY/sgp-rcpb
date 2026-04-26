<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();

            /**
             * Lien vers la personne physique.
             * La FK (→ agents) est ajoutée dans la migration create_agents_table,
             * car la table agents n'existe pas encore à ce stade.
             */
            $table->unsignedBigInteger('agent_id')->nullable()->unique()
                  ->comment('Lien vers la fiche Agent de cet utilisateur');

            // ── Authentification ──────────────────────────────────────────────
            $table->string('name', 191)
                  ->comment('Nom complet affiché (sync depuis agent.nom + agent.prenom)');
            $table->string('email', 191)->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->boolean('must_change_password')->default(true)
                  ->comment('Force le changement de mot de passe à la première connexion');

            // ── Profil système ────────────────────────────────────────────────
            $table->string('role', 50)->default('Agent')
                  ->comment('Rôle système : DG | DGA | Directeur_Caisse | Chef_Service | Agent …');
            $table->string('theme_preference', 50)->default('reference');

            // ── Hiérarchie de validation N+1 ──────────────────────────────────
            $table->foreignId('manager_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('Supérieur direct pour la chaîne de validation des évaluations');

            // ── Rattachement faîtière (héritage – sera dérivé via agent→structure) ──
            $table->unsignedBigInteger('pca_entite_id')->nullable()
                  ->comment('Entité de rattachement (legacy – à migrer vers agent→entite)');

            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
