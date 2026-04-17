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
        Schema::create('objectifs', function (Blueprint $table): void {
            $table->id();
            
            // 1. Liaison polyvalente (Fusion du add 'annee_id')
            $table->morphs('assignable'); 
            $table->foreignId('annee_id')->nullable()->constrained('annees')->nullOnDelete();

            // 2. Dates et échéances
            $table->date('date');
            $table->date('date_echeance');

            // 3. Contenu et Progression (Fusion du add 'avancement_percentage')
            $table->text('commentaire');
            $table->unsignedTinyInteger('avancement_percentage')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objectifs');
    }
};