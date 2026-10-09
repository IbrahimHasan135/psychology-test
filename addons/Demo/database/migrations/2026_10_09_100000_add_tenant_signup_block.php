<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaultTenantId = DB::table('tenants')->where('slug', 'default')->value('id');
        $page = DB::table('site_pages')->where('slug', 'home')->where('tenant_id', $defaultTenantId)->first();
        if (! $page || DB::table('site_blocks')->where('site_page_id', $page->id)->where('type', 'demo.tenant-signup')->exists()) {
            return;
        }

        $maxOrder = DB::table('site_blocks')->where('site_page_id', $page->id)->max('sort_order');
        DB::table('site_blocks')->insert([
            'site_page_id' => $page->id,
            'block_uid' => 'demo-tenant-signup',
            'type' => 'demo.tenant-signup',
            'nav_enabled' => false,
            'nav_label' => null,
            'sort_order' => ((int) $maxOrder) + 1,
            'data_json' => json_encode([
                'badge' => 'Create your workspace',
                'title' => 'Create a tenant account',
                'text' => 'Register your workspace and get a private website and admin panel at your own URL.',
            ], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('site_blocks')->where('type', 'demo.tenant-signup')->delete();
    }
};
