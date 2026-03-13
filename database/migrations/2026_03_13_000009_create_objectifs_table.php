<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objectifs', function (Blueprint $table): void {
            $table->id();
            $table->morphs('assignable');
            $table->date('date');
            $table->date('date_echeance');
            $table->text('commentaire');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objectifs');
    }
};