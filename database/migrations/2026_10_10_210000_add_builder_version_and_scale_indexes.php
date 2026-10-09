<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('site_pages', 'builder_version')) {
            Schema::table('site_pages', function (Blueprint $table): void {
                $table->unsignedBigInteger('builder_version')->default(1)->after('builder_initialized');
                $table->index(['tenant_id', 'is_published', 'id'], 'site_pages_public_lookup_index');
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->index(['name', 'id'], 'users_name_id_index');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('site_pages', 'builder_version')) {
            Schema::table('site_pages', function (Blueprint $table): void {
                $table->dropIndex('site_pages_public_lookup_index');
                $table->dropColumn('builder_version');
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_name_id_index');
        });
    }
};
