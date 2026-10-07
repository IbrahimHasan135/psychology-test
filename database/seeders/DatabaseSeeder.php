<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
                'password' => 'N0v4.lynk.',
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
            User::query()->firstOrCreate(
                ['username' => $account['username']],
                $account
            );
        }
    }
}