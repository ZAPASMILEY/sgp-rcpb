<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entites', function (Blueprint $table) {
            $table->string('directrice_generale_photo_path')->nullable()->after('directrice_generale_email');
            $table->string('dga_photo_path')->nullable()->after('dga_email');
            $table->string('pca_photo_path')->nullable()->after('pca_email');
        });
    }

    public function down(): void
    {
        Schema::table('entites', function (Blueprint $table) {
            $table->dropColumn([
                'directrice_generale_photo_path',
                'dga_photo_path',
                'pca_photo_path',
            ]);
        });
    }
};
