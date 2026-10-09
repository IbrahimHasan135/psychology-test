<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_addons')) {
            DB::table('tenant_addons')->where('addon_slug', 'demo')->delete();
        }
    }

    public function down(): void
    {
        // Platform-only addons must not be reattached to tenant workspaces.
    }
};
