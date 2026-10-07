<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_user_login_redirects_to_user_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::USER,
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('user.dashboard'));
    }

    public function test_user_cannot_open_admin_panel(): void
    {
        $user = User::factory()->create(['role' => UserRole::USER]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}