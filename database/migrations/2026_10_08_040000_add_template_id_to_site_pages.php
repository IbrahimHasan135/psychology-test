<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_pages') && ! Schema::hasColumn('site_pages', 'template_id')) {
            Schema::table('site_pages', function (Blueprint $table): void {
                $table->string('template_id', 60)->default('template-studio');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('site_pages') && Schema::hasColumn('site_pages', 'template_id')) {
            Schema::table('site_pages', function (Blueprint $table): void {
                $table->dropColumn('template_id');
            });
        }
    }
};
