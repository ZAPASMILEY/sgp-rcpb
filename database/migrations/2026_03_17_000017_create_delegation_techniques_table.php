<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delegation_techniques', function (Blueprint $table): void {
            $table->id();
            $table->string('region');
            $table->string('ville');
            $table->string('secretariat_telephone', 30);
            $table->timestamps();

            $table->unique(['region', 'ville']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delegation_techniques');
    }
};
