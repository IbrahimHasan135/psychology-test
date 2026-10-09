<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tenants')
            ->where('slug', '!=', 'default')
            ->orderBy('id')
            ->eachById(function (object $tenant): void {
                $hasHome = DB::table('site_pages')
                    ->where('tenant_id', $tenant->id)
                    ->where('slug', 'home')
                    ->exists();

                if ($hasHome) {
                    return;
                }

                DB::table('site_pages')
                    ->where('tenant_id', $tenant->id)
                    ->where('slug', $tenant->slug)
                    ->update(['slug' => 'home', 'updated_at' => now()]);
            });
    }

    public function down(): void
    {
        // The repair is intentionally irreversible: home is the canonical tenant page slug.
    }
};
