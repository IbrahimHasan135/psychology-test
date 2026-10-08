<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\SitePage;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Novalynk Super Admin',
                'username' => 'novalynk.superadmin',
                'email' => 'superadmin@novalynk.local',
                'role' => UserRole::SUPER_ADMIN,
                'password' => 'N0v4.lynk',
            ],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'role' => UserRole::ADMIN,
                'password' => 'password',
            ],
            [
                'name' => 'User',
                'username' => 'user',
                'email' => 'user@example.com',
                'role' => UserRole::USER,
                'password' => 'password',
            ],
        ];

        foreach ($accounts as $account) {
            User::query()->updateOrCreate(
                ['username' => $account['username']],
                $account
            );
        }

        SitePage::query()->firstOrCreate(
            ['slug' => 'home'],
            [
                'name' => 'Home',
                'display_mode' => 'sections',
                'is_published' => true,
            ]
        );

        if (Schema::hasTable('role_addon_permissions')) {
            foreach ([
                UserRole::ADMIN => ['demo.view'],
            ] as $role => $permissions) {
                foreach ($permissions as $permission) {
                    DB::table('role_addon_permissions')->updateOrInsert(
                        [
                            'role' => $role,
                            'addon_slug' => str($permission)->before('.')->toString(),
                            'permission' => $permission,
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
