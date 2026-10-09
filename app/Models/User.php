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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Core\Tenancy\TenantContext;
use App\Core\Addons\AddonRegistry;
use App\Models\Tenant;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

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
        'status',
        'last_login_at',
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
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->effectiveRole(), $roles, true);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(TenantMembership::class);
    }

    public function currentMembership(): ?TenantMembership
    {
        $context = app(TenantContext::class);
        if (! $context->isScopedRequest()) {
            return null;
        }

        return $this->memberships()
            ->where('tenant_id', $context->id())
            ->where('status', 'active')
            ->with('roleDefinition')
            ->first();
    }

    public function ownedTenant(): ?Tenant
    {
        if (! Schema::hasTable('tenant_memberships')) {
            return null;
        }

        return $this->memberships()
            ->where('role', UserRole::SUPER_ADMIN)
            ->where('status', 'active')
            ->with('tenant')
            ->get()
            ->pluck('tenant')
            ->filter()
            ->first();
    }

    public function dashboardUrl(): string
    {
        $context = app(TenantContext::class);
        if (! $context->isScopedRequest()) {
            $tenant = $this->ownedTenant();
            if ($tenant) {
                return route('tenant.admin.dashboard', ['tenant' => $tenant->slug]);
            }
        }

        return nova_route($this->dashboardRoute());
    }

    public function isAuthenticatedInCurrentContext(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $context = app(TenantContext::class);
        if (! $context->isScopedRequest()) {
            return true;
        }

        return $context->isActiveSession()
            && $this->memberships()
                ->where('tenant_id', $context->id())
                ->where('status', 'active')
                ->exists();
    }

    public function effectiveRole(): string
    {
        return app(TenantContext::class)->roleFor($this) ?: (string) $this->role;
    }

    public function isPlatformSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN && ! app(TenantContext::class)->isScopedRequest();
    }

    public function roleRecord(): ?Role
    {
        if (! Schema::hasTable('roles')) {
            return null;
        }

        return Role::query()->where('slug', $this->effectiveRole())->first();
    }

    public function isAdminLike(): bool
    {
        if ($this->effectiveRole() === UserRole::SUPER_ADMIN) {
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
        return $this->effectiveRole() === UserRole::SUPER_ADMIN;
    }

    public function canCreateRole(string $roleSlug): bool
    {
        if ($this->effectiveRole() === UserRole::SUPER_ADMIN) {
            return true;
        }

        if (! Schema::hasTable('role_creatable_roles')) {
            return false;
        }

        $query = DB::table('role_creatable_roles')
            ->where('role_slug', $this->effectiveRole())
            ->where('creatable_role_slug', $roleSlug)
            ;
        if (app(TenantContext::class)->isScopedRequest()) {
            $query->where(function ($builder): void {
                $builder->whereNull('tenant_id')->orWhere('tenant_id', app(TenantContext::class)->id());
            });
        }

        return $query->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isPlatformSuperAdmin()) {
            return true;
        }

        $addonSlug = str($permission)->before('.')->toString();
        $addon = app(AddonRegistry::class)->find($addonSlug);
        if ($addon?->scope === 'platform' && app(TenantContext::class)->isScopedRequest()) {
            return false;
        }

        if (! Schema::hasTable('role_addon_permissions')) {
            return false;
        }

        if (app(TenantContext::class)->isScopedRequest() && $this->effectiveRole() === UserRole::SUPER_ADMIN) {
            return DB::table('tenant_addons')
                ->where('tenant_id', app(TenantContext::class)->id())
                ->where('addon_slug', $addonSlug)
                ->where('status', 'active')
                ->exists();
        }

        $roleId = $this->currentMembership()?->role_id;

        $query = DB::table('role_addon_permissions')
            ->where('permission', $permission)
            ->where(function ($query) use ($roleId): void {
                $query->where('role_id', $roleId)->orWhere('role', $this->effectiveRole());
            });
        if (app(TenantContext::class)->isScopedRequest()) {
            $query->where(function ($builder): void {
                $builder->whereNull('tenant_id')->orWhere('tenant_id', app(TenantContext::class)->id());
            });
        }

        return $query->exists();
    }

    public function canAccessAddon(string $addonSlug): bool
    {
        $addon = app(AddonRegistry::class)->find($addonSlug);
        if ($addon?->scope === 'platform' && app(TenantContext::class)->isScopedRequest()) {
            return false;
        }

        if ($this->isPlatformSuperAdmin()) {
            return true;
        }

        $context = app(TenantContext::class);
        if ($context->isScopedRequest()) {
            return DB::table('tenant_addons')
                ->where('tenant_id', $context->id())
                ->where('addon_slug', $addonSlug)
                ->where('status', 'active')
                ->exists();
        }

        if (! Schema::hasTable('role_addon_permissions')) {
            return false;
        }

        return DB::table('role_addon_permissions')
            ->where('role', $this->effectiveRole())
            ->where('addon_slug', $addonSlug)
            ->exists();
    }

    public function dashboardRoute(): string
    {
        if (app(TenantContext::class)->isScopedRequest()) {
            return $this->isAdminLike() ? 'admin.dashboard' : 'user.dashboard';
        }

        return $this->isAdminLike() ? 'admin.dashboard' : 'user.dashboard';
    }
}
