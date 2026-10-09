<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\SitePage;
use App\Models\Role;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => UserRole::SUPER_ADMIN, 'is_system' => true, 'is_admin' => true],
            ['name' => 'Admin', 'slug' => UserRole::ADMIN, 'is_system' => true, 'is_admin' => true],
            ['name' => 'User', 'slug' => UserRole::USER, 'is_system' => true, 'is_admin' => false],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }

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
            $user = User::query()->firstOrNew(['username' => $account['username']]);

            $plainPassword = $account['password'];
            unset($account['password']);

            $user->fill($account);
            if (! $user->exists || ! Hash::check($plainPassword, (string) $user->password)) {
                $user->password = $plainPassword;
            }
            $user->save();

            $defaultTenant = Tenant::query()->where('slug', 'default')->first();
            if ($defaultTenant) {
                $roleId = Role::query()->where('slug', $user->role)->value('id');
                DB::table('tenant_memberships')->updateOrInsert(
                    ['tenant_id' => $defaultTenant->id, 'user_id' => $user->id],
                    ['role' => $user->role, 'role_id' => $roleId, 'status' => 'active', 'joined_at' => now(), 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        $defaultTenant = Tenant::query()->where('slug', 'default')->first();
        SitePage::query()->withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $defaultTenant?->id, 'slug' => 'home'],
            [
                'name' => 'Home',
                'display_mode' => 'sections',
                'is_published' => true,
                'tenant_id' => $defaultTenant?->id,
            ]
        );

        if (Schema::hasTable('role_addon_permissions')) {
            foreach ([
                UserRole::ADMIN => ['demo.view'],
            ] as $role => $permissions) {
                foreach ($permissions as $permission) {
                    $attributes = [
                        'role' => $role,
                        'role_id' => Role::query()->where('slug', $role)->value('id'),
                        'addon_slug' => str($permission)->before('.')->toString(),
                        'permission' => $permission,
                    ];

                    if (! DB::table('role_addon_permissions')->where($attributes)->exists()) {
                        DB::table('role_addon_permissions')->insert($attributes + [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        if (Schema::hasTable('role_creatable_roles')) {
            foreach ([UserRole::USER] as $creatableRole) {
                $attributes = [
                    'role_slug' => UserRole::ADMIN,
                    'creatable_role_slug' => $creatableRole,
                ];

                if (! DB::table('role_creatable_roles')->where($attributes)->exists()) {
                    DB::table('role_creatable_roles')->insert($attributes + [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
