<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Core\Tenancy\TenantContext;

class Role extends Model
{
    protected $fillable = [
        'name',
        'tenant_id',
        'slug',
        'is_system',
        'is_admin',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $context = app(TenantContext::class);
            if ($context->isScopedRequest()) {
                $builder->where(function (Builder $query) use ($context): void {
                    $query->whereNull('tenant_id')->orWhere('tenant_id', $context->id());
                });
            }
        });

        static::creating(function (self $role): void {
            if (! $role->is_system && app(TenantContext::class)->isScopedRequest()) {
                $role->tenant_id ??= app(TenantContext::class)->id();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creatableRoles(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'role_creatable_roles', 'role_slug', 'creatable_role_slug', 'slug', 'slug');
    }
}
