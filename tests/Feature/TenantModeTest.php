<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_role_cannot_manage_tenants(): void
    {
        $this->seed();
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.addons.demo.tenants.index'))
            ->assertForbidden();
    }

    public function test_platform_super_admin_can_provision_tenant_and_owner_is_scoped(): void
    {
        $this->seed();
        $platformAdmin = User::query()->where('username', 'novalynk.superadmin')->firstOrFail();

        $this->actingAs($platformAdmin)
            ->post(route('admin.addons.demo.tenants.store'), [
                'tenant_name' => 'GBI Demo',
                'tenant_slug' => 'gbi',
                'owner_name' => 'GBI Owner',
                'owner_username' => 'gbi_owner',
                'owner_email' => 'owner@gbi.test',
                'owner_password' => 'owner-password',
            ])
            ->assertRedirect(route('admin.addons.demo.tenants.index'));

        $tenant = Tenant::query()->where('slug', 'gbi')->firstOrFail();
        $owner = User::query()->where('username', 'gbi_owner')->firstOrFail();

        $this->assertDatabaseHas('tenant_memberships', [
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'role' => UserRole::SUPER_ADMIN,
        ]);

        $this->post(route('logout'));

        $this->post(route('tenant.login.store', ['tenant' => 'gbi']), [
            'login' => 'gbi_owner',
            'password' => 'owner-password',
        ])->assertRedirect(route('tenant.admin.dashboard', ['tenant' => 'gbi']));

        $this->get(route('tenant.home', ['tenant' => 'gbi']))->assertOk();
        $this->get(route('tenant.admin.dashboard', ['tenant' => 'gbi']))->assertOk();
        $this->get(route('admin.addons.demo.index'))->assertForbidden();
    }
}
