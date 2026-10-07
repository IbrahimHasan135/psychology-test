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
            ['name' => 'Super Admin', 'email' => 'superadmin@example.com', 'role' => UserRole::SUPER_ADMIN],
            ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => UserRole::ADMIN],
            ['name' => 'User', 'email' => 'user@example.com', 'role' => UserRole::USER],
        ];

        foreach ($accounts as $account) {
            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [...$account, 'password' => 'password']
            );
        }
    }
}