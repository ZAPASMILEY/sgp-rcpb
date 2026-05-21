<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_roles', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 100)->unique()->comment('Valeur stockée dans users.role');
            $table->string('label', 150)->comment('Libellé affiché dans l\'interface');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_roles');
    }
};
