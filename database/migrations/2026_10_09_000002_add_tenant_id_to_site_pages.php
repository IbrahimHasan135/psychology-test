<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('site_pages', 'tenant_id')) {
            Schema::table('site_pages', function (Blueprint $table): void {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
                $table->index(['tenant_id', 'slug']);
            });
        }

        $defaultTenantId = DB::table('tenants')->where('slug', 'default')->value('id');
        if ($defaultTenantId) {
            DB::table('site_pages')->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('site_pages', 'tenant_id')) {
            Schema::table('site_pages', function (Blueprint $table): void {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex(['tenant_id', 'slug']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
