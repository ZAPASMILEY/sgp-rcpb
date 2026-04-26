<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles_has_permissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('roles_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permissions_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['roles_id', 'permissions_id'], 'roles_permissions_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles_has_permissions');
    }
};
