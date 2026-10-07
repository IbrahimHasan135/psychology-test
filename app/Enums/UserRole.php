<?php

namespace App\Enums;

final class UserRole
{
    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN = 'admin';
    public const USER = 'user';

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
        return match ($role) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::USER => 'User',
            default => 'Unknown',
        };
    }
}