<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_addon_permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('role')->index();
            $table->string('addon_slug')->index();
            $table->string('permission')->index();
            $table->timestamps();

            $table->unique(['role', 'addon_slug', 'permission'], 'role_addon_permission_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_addon_permissions');
    }
};
