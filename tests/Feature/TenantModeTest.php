<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\SitePage;
use App\Models\User;
use App\Core\PageBuilder\BlockRegistry;
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

    public function test_regular_tenant_member_cannot_login_from_platform_login(): void
    {
        $this->seed();
        $tenant = Tenant::query()->create([
            'slug' => 'member-tenant',
            'name' => 'Member Tenant',
            'status' => 'active',
        ]);
        $member = User::query()->where('username', 'user')->firstOrFail();
        $member->memberships()->create([
            'tenant_id' => $tenant->id,
            'role' => UserRole::USER,
            'status' => 'active',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'login' => $member->username,
                'password' => 'password',
            ])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
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
                'owner_password_confirmation' => 'owner-password',
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

        $this->post(route('tenant.logout', ['tenant' => 'gbi']));

        $this->post(route('login.store'), [
            'login' => 'gbi_owner',
            'password' => 'owner-password',
        ])->assertRedirect(route('tenant.admin.dashboard', ['tenant' => 'gbi']));

        $page = SitePage::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('slug', 'home')->firstOrFail();
        $this->postJson(route('tenant.admin.pages.builder.site-save', ['tenant' => 'gbi']), [
            'template' => 'template-studio',
            'activePageId' => (string) $page->id,
            'pages' => [[
                'id' => (string) $page->id,
                'label' => 'GBI Home Updated',
                'path' => '/gbi',
                'blocks' => [[
                    'id' => 'gbi-hero',
                    'type' => 'hero',
                    'navEnabled' => true,
                    'navLabel' => 'Home',
                    'data' => BlockRegistry::definitions()['hero']['initial'],
                ]],
            ]],
        ])->assertOk()->assertJson(['saved' => true]);

        $this->assertDatabaseHas('site_pages', [
            'id' => $page->id,
            'tenant_id' => $tenant->id,
            'slug' => 'home',
            'name' => 'GBI Home Updated',
        ]);

        $this->assertDatabaseHas('tenant_addons', [
            'tenant_id' => $tenant->id,
            'addon_slug' => 'demo',
            'status' => 'active',
        ]);

        $this->get(route('tenant.admin.addons.demo.index', ['tenant' => 'gbi']))->assertOk();
    }

    public function test_public_tenant_signup_creates_owner_and_redirects_to_tenant_login(): void
    {
        $this->seed();

        $this->post(route('demo.tenant-accounts.store'), [
            'tenant_name' => 'Public Demo',
            'tenant_slug' => 'public-demo',
            'owner_name' => 'Public Owner',
            'owner_username' => 'public_owner',
            'owner_email' => 'public@example.test',
            'owner_password' => 'owner-password',
            'owner_password_confirmation' => 'owner-password',
        ])->assertRedirect(route('tenant.login', ['tenant' => 'public-demo']));

        $tenant = Tenant::query()->where('slug', 'public-demo')->firstOrFail();
        $owner = User::query()->where('username', 'public_owner')->firstOrFail();

        $this->assertDatabaseHas('tenant_memberships', [
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'role' => UserRole::SUPER_ADMIN,
            'status' => 'active',
        ]);

        $this->post(route('tenant.login.store', ['tenant' => 'public-demo']), [
            'login' => 'public_owner',
            'password' => 'owner-password',
        ])->assertRedirect(route('tenant.admin.dashboard', ['tenant' => 'public-demo']));
    }

    public function test_platform_super_admin_can_submit_signup_card_while_logged_in(): void
    {
        $this->seed();
        $platformAdmin = User::query()->where('username', 'novalynk.superadmin')->firstOrFail();

        $this->actingAs($platformAdmin)
            ->post(route('demo.tenant-accounts.store'), [
                'tenant_name' => 'Admin Card Demo',
                'tenant_slug' => 'admin-card-demo',
                'owner_name' => 'Admin Card Owner',
                'owner_username' => 'admin_card_owner',
                'owner_email' => 'admin-card@example.test',
                'owner_password' => 'owner-password',
                'owner_password_confirmation' => 'owner-password',
            ])
            ->assertRedirect(route('tenant.login', ['tenant' => 'admin-card-demo']));

        $this->assertDatabaseHas('tenants', ['slug' => 'admin-card-demo']);
        $this->assertDatabaseHas('users', ['username' => 'admin_card_owner']);
    }

    public function test_default_tenant_urls_redirect_to_the_main_application(): void
    {
        $this->seed();

        $this->get('/default/login')->assertRedirect(route('login'));
        $this->get('/default')->assertRedirect(route('home'));
    }

    public function test_active_tenant_session_is_not_presented_as_logged_in_on_another_tenant(): void
    {
        $this->seed();
        $owner = User::query()->where('username', 'user')->firstOrFail();
        $tenantA = Tenant::query()->create(['slug' => 'tenant-a', 'name' => 'Tenant A', 'status' => 'active']);
        $tenantB = Tenant::query()->create(['slug' => 'tenant-b', 'name' => 'Tenant B', 'status' => 'active']);

        foreach ([$tenantA, $tenantB] as $tenant) {
            $owner->memberships()->create([
                'tenant_id' => $tenant->id,
                'role' => UserRole::SUPER_ADMIN,
                'status' => 'active',
            ]);
            SitePage::query()->withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => 'Home',
                'slug' => 'home',
                'display_mode' => 'sections',
                'is_published' => true,
            ]);
        }

        $this->actingAs($owner)->withSession(['novabase.active_tenant_slug' => 'tenant-a'])
            ->get(route('tenant.home', ['tenant' => 'tenant-b']))
            ->assertOk()
            ->assertSee('Login')
            ->assertDontSee('User Portal');

        $this->withSession(['novabase.active_tenant_slug' => 'tenant-a'])
            ->get(route('tenant.admin.dashboard', ['tenant' => 'tenant-b']))
            ->assertForbidden();
    }

    public function test_login_can_switch_from_one_tenant_to_another_without_guest_redirect(): void
    {
        $this->seed();
        $tenantA = Tenant::query()->create(['slug' => 'switch-a', 'name' => 'Switch A', 'status' => 'active']);
        $tenantB = Tenant::query()->create(['slug' => 'switch-b', 'name' => 'Switch B', 'status' => 'active']);
        $ownerA = User::factory()->create(['username' => 'switch_owner_a', 'email' => 'switch-a@example.test']);
        $ownerB = User::factory()->create(['username' => 'switch_owner_b', 'email' => 'switch-b@example.test']);

        foreach ([[$tenantA, $ownerA], [$tenantB, $ownerB]] as [$tenant, $owner]) {
            $owner->memberships()->create([
                'tenant_id' => $tenant->id,
                'role' => UserRole::SUPER_ADMIN,
                'status' => 'active',
            ]);
            SitePage::query()->withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => 'Home',
                'slug' => 'home',
                'display_mode' => 'sections',
                'is_published' => true,
            ]);
        }

        $this->post(route('tenant.login.store', ['tenant' => 'switch-a']), [
            'login' => 'switch_owner_a',
            'password' => 'password',
        ])->assertRedirect(route('tenant.admin.dashboard', ['tenant' => 'switch-a']));

        $this->get(route('tenant.login', ['tenant' => 'switch-b']))->assertOk();

        $this->post(route('tenant.login.store', ['tenant' => 'switch-b']), [
            'login' => 'switch_owner_b',
            'password' => 'password',
        ])->assertRedirect(route('tenant.admin.dashboard', ['tenant' => 'switch-b']));

        $this->get(route('tenant.admin.dashboard', ['tenant' => 'switch-a']))->assertForbidden();
        $this->get(route('tenant.admin.dashboard', ['tenant' => 'switch-b']))->assertOk();
    }
}
