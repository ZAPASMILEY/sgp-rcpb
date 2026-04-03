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
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('delegation_technique_id')->nullable()->constrained('delegation_techniques')->nullOnDelete();
            $table->string('chef_sexe')->nullable();
            $table->string('chef_date_debut_mois')->nullable();
            $table->string('direction_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delegation_technique_id');
            $table->dropColumn(['chef_sexe', 'chef_date_debut_mois']);
        });
    }
};
