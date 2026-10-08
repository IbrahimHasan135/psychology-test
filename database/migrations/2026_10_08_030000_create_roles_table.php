<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
        });

        Schema::create('role_creatable_roles', function (Blueprint $table): void {
            $table->id();
            $table->string('role_slug')->index();
            $table->string('creatable_role_slug')->index();
            $table->timestamps();

            $table->unique(['role_slug', 'creatable_role_slug'], 'role_creatable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_creatable_roles');
        Schema::dropIfExists('roles');
    }
};
