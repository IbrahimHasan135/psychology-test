<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_open_role_management(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $superAdmin = User::query()->where('username', 'novalynk.superadmin')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('Role Management');
    }

    public function test_super_admin_can_create_role_with_addon_permission_and_creatable_role(): void
    {
        $this->seed();
        $superAdmin = User::query()->where('username', 'novalynk.superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->post(route('admin.roles.store'), [
                'name' => 'Client Operator',
                'permissions' => ['demo.view'],
                'creatable_roles' => [UserRole::USER],
            ])
            ->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseHas('roles', ['slug' => 'client_operator']);
        $this->assertDatabaseHas('role_addon_permissions', [
            'role' => 'client_operator',
            'permission' => 'demo.view',
        ]);
        $this->assertDatabaseHas('role_creatable_roles', [
            'role_slug' => 'client_operator',
            'creatable_role_slug' => UserRole::USER,
        ]);
    }

    public function test_admin_can_create_only_allowed_user_roles(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('User Management');

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Client User',
                'username' => 'client.user',
                'email' => 'client.user@example.com',
                'password' => 'password',
                'role' => UserRole::USER,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'username' => 'client.user',
            'role' => UserRole::USER,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Blocked Super',
                'username' => 'blocked.super',
                'email' => 'blocked.super@example.com',
                'password' => 'password',
                'role' => UserRole::SUPER_ADMIN,
            ])
            ->assertSessionHasErrors();

        $this->assertDatabaseMissing('users', ['username' => 'blocked.super']);
    }

    public function test_custom_admin_role_can_open_admin_panel_when_marked_admin(): void
    {
        $this->seed();
        Role::query()->create([
            'name' => 'Support Admin',
            'slug' => 'support_admin',
            'is_admin' => true,
        ]);

        $support = User::factory()->create(['role' => 'support_admin']);

        $this->actingAs($support)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }
}
