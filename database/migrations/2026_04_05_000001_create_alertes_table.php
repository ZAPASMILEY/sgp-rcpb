<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertes', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('personnalisee'); // 'securite' or 'personnalisee'
            $table->string('priorite')->default('moyenne');     // 'basse', 'moyenne', 'haute', 'critique'
            $table->string('titre');
            $table->text('message')->nullable();
            $table->string('statut')->default('active');        // 'active', 'resolue', 'ignoree'
            $table->string('ip_address', 45)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertes');
    }
};
