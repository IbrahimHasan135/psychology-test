<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
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
}
