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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            
            // 1. Rôles et Autorisations (Fusion des adds)
            // On utilise string au lieu d'enum pour plus de souplesse (SGP-RCPB)
            $table->string('role', 30)->default('admin');
            $table->unsignedBigInteger('pca_entite_id')->nullable();

            // Identité personnelle
            $table->string('sexe', 20)->nullable();
            $table->string('date_prise_fonction', 7)->nullable(); // format Y-m

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // 2. Préférences Interface (Fusion du add theme)
            $table->string('theme_preference')->default('reference');

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};