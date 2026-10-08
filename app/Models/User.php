<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use App\Enums\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function roleRecord(): ?Role
    {
        if (! Schema::hasTable('roles')) {
            return null;
        }

        return Role::query()->where('slug', $this->role)->first();
    }

    public function isAdminLike(): bool
    {
        if ($this->role === UserRole::SUPER_ADMIN) {
            return true;
        }

        return (bool) $this->roleRecord()?->is_admin;
    }

    public function canManageUsers(): bool
    {
        return $this->isAdminLike();
    }

    public function canManageRoles(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }

    public function canCreateRole(string $roleSlug): bool
    {
        if ($this->role === UserRole::SUPER_ADMIN) {
            return true;
        }

        if (! Schema::hasTable('role_creatable_roles')) {
            return false;
        }

        return DB::table('role_creatable_roles')
            ->where('role_slug', $this->role)
            ->where('creatable_role_slug', $roleSlug)
            ->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === UserRole::SUPER_ADMIN) {
            return true;
        }

        if (! Schema::hasTable('role_addon_permissions')) {
            return false;
        }

        return DB::table('role_addon_permissions')
            ->where('role', $this->role)
            ->where('permission', $permission)
            ->exists();
    }

    public function canAccessAddon(string $addonSlug): bool
    {
        if ($this->role === UserRole::SUPER_ADMIN) {
            return true;
        }

        if (! Schema::hasTable('role_addon_permissions')) {
            return false;
        }

        return DB::table('role_addon_permissions')
            ->where('role', $this->role)
            ->where('addon_slug', $addonSlug)
            ->exists();
    }

    public function dashboardRoute(): string
    {
        return $this->isAdminLike() ? 'admin.dashboard' : 'user.dashboard';
    }
}
