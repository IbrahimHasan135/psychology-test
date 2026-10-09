<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_pages', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->unique(['tenant_id', 'slug'], 'site_pages_tenant_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('site_pages', function (Blueprint $table): void {
            $table->dropUnique('site_pages_tenant_slug_unique');
            $table->unique('slug');
        });
    }
};
