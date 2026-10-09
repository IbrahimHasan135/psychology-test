<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Tenant;
use App\Models\TenantMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddonAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_with_seeded_permission_can_open_demo_addon(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.addons.demo.index'))
            ->assertOk()
            ->assertSee('Demo Addon');
    }

    public function test_user_without_permission_cannot_open_demo_addon(): void
    {
        $this->seed();
        $user = User::factory()->create(['role' => UserRole::USER]);

        $this->actingAs($user)
            ->get(route('admin.addons.demo.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_see_demo_dashboard_card(): void
    {
        $this->seed();
        $superAdmin = User::query()->where('username', 'novalynk.superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Demo Addon Ready');
    }

    public function test_tenant_owner_cannot_see_or_open_platform_demo_addon(): void
    {
        $this->seed();
        $tenant = Tenant::query()->create([
            'slug' => 'addon-tenant',
            'name' => 'Addon Tenant',
            'status' => 'active',
        ]);
        $owner = User::factory()->create([
            'username' => 'addon_owner',
            'email' => 'addon-owner@example.test',
            'role' => UserRole::USER,
        ]);
        TenantMembership::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'role' => UserRole::SUPER_ADMIN,
            'status' => 'active',
        ]);

        $this->actingAs($owner)->withSession(['novabase.active_tenant_slug' => 'addon-tenant'])
            ->get(route('tenant.admin.dashboard', ['tenant' => 'addon-tenant']))
            ->assertOk()
            ->assertDontSee('Demo Addon')
            ->assertDontSee('Demo Addon Ready');

        $this->get('/addon-tenant/admin/addons/demo')->assertNotFound();
    }
}
