<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Caisses: unique on nom, emails, and phones
        Schema::table('caisses', function (Blueprint $table) {
            $table->unique('nom');
            $table->unique('directeur_email');
            $table->unique('directeur_telephone');
            $table->unique('secretaire_email');
            $table->unique('secretaire_telephone');
        });

        // Guichets: unique on nom, chef email, chef phone
        Schema::table('guichets', function (Blueprint $table) {
            $table->unique('nom');
            $table->unique('chef_email');
            $table->unique('chef_telephone');
        });

        // Agences: unique on chef/secretaire emails and phones
        Schema::table('agences', function (Blueprint $table) {
            $table->unique('chef_email');
            $table->unique('chef_telephone');
            $table->unique('secretaire_email');
            $table->unique('secretaire_telephone');
        });

        // Services: unique on nom and chef phone
        Schema::table('services', function (Blueprint $table) {
            $table->unique('nom');
            $table->unique('chef_telephone');
        });

        // Agents: unique on email and phone
        Schema::table('agents', function (Blueprint $table) {
            $table->unique('email');
            $table->unique('numero_telephone');
        });
    }

    public function down(): void
    {
        Schema::table('caisses', function (Blueprint $table) {
            $table->dropUnique(['nom']);
            $table->dropUnique(['directeur_email']);
            $table->dropUnique(['directeur_telephone']);
            $table->dropUnique(['secretaire_email']);
            $table->dropUnique(['secretaire_telephone']);
        });

        Schema::table('guichets', function (Blueprint $table) {
            $table->dropUnique(['nom']);
            $table->dropUnique(['chef_email']);
            $table->dropUnique(['chef_telephone']);
        });

        Schema::table('agences', function (Blueprint $table) {
            $table->dropUnique(['chef_email']);
            $table->dropUnique(['chef_telephone']);
            $table->dropUnique(['secretaire_email']);
            $table->dropUnique(['secretaire_telephone']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique(['nom']);
            $table->dropUnique(['chef_telephone']);
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropUnique(['numero_telephone']);
        });
    }
};
