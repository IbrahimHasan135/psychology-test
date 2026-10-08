<?php

namespace App\Enums;

use App\Models\Role;
use Illuminate\Support\Facades\Schema;

final class UserRole
{
    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN = 'admin';
    public const USER = 'user';

    private static ?array $labels = null;

    public static function all(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::USER,
        ];
    }

    public static function label(string $role): string
    {
        if (class_exists(Role::class) && Schema::hasTable('roles')) {
            self::$labels ??= Role::query()->pluck('name', 'slug')->all();

            if (isset(self::$labels[$role])) {
                return self::$labels[$role];
            }
        }

        return match ($role) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::USER => 'User',
            default => 'Unknown',
        };
    }
}
