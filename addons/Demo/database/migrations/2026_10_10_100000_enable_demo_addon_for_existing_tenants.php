<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenant_addons') || ! Schema::hasTable('tenants')) {
            return;
        }

        $now = now();
        DB::table('tenants')
            ->where('slug', '!=', 'default')
            ->pluck('id')
            ->each(function (int $tenantId) use ($now): void {
                DB::table('tenant_addons')->updateOrInsert(
                    ['tenant_id' => $tenantId, 'addon_slug' => 'demo'],
                    ['status' => 'active', 'created_at' => $now, 'updated_at' => $now]
                );
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('tenant_addons')) {
            DB::table('tenant_addons')->where('addon_slug', 'demo')->delete();
        }
    }
};
