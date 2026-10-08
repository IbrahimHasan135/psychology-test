<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_pages') && ! Schema::hasColumn('site_pages', 'builder_initialized')) {
            Schema::table('site_pages', function (Blueprint $table): void {
                $table->boolean('builder_initialized')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('site_pages') && Schema::hasColumn('site_pages', 'builder_initialized')) {
            Schema::table('site_pages', function (Blueprint $table): void {
                $table->dropColumn('builder_initialized');
            });
        }
    }
};
