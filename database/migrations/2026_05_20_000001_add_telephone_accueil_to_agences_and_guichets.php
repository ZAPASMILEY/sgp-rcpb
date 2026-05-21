<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agences', function (Blueprint $table): void {
            $table->string('telephone_accueil', 30)->nullable()->after('secretaire_agent_id');
        });

        Schema::table('guichets', function (Blueprint $table): void {
            $table->string('telephone_accueil', 30)->nullable()->after('chef_agent_id');
        });
    }

    public function down(): void
    {
        Schema::table('agences', function (Blueprint $table): void {
            $table->dropColumn('telephone_accueil');
        });

        Schema::table('guichets', function (Blueprint $table): void {
            $table->dropColumn('telephone_accueil');
        });
    }
};
