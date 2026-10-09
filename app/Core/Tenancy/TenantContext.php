<?php

namespace App\Core\Tenancy;

use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;

class TenantContext
{
    private ?Tenant $tenant = null;
    private bool $scopedRequest = false;
    /** @var array<int, TenantMembership|null> */
    private array $membershipCache = [];

    public function set(?Tenant $tenant, bool $scopedRequest = false): void
    {
        $this->tenant = $tenant;
        $this->scopedRequest = $scopedRequest;
        $this->membershipCache = [];
    }

    public function clear(): void
    {
        $this->tenant = null;
        $this->scopedRequest = false;
        $this->membershipCache = [];
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function isScopedRequest(): bool
    {
        return $this->scopedRequest;
    }

    public function isActiveSession(): bool
    {
        if (! $this->scopedRequest || $this->tenant === null) {
            return true;
        }

        return session()->get('novabase.active_tenant_slug') === $this->tenant->slug;
    }

    public function roleFor(User $user): string
    {
        if (! $this->scopedRequest) {
            return (string) $user->role;
        }

        $membership = $this->membershipFor($user);

        return (string) ($membership?->roleDefinition?->slug ?: $membership?->role);
    }

    public function membershipFor(User $user): ?TenantMembership
    {
        if (! $this->scopedRequest || $this->id() === null) {
            return null;
        }

        if (array_key_exists($user->getKey(), $this->membershipCache)) {
            return $this->membershipCache[$user->getKey()];
        }

        return $this->membershipCache[$user->getKey()] = $user->memberships()
            ->where('tenant_id', $this->id())
            ->where('status', 'active')
            ->with('roleDefinition')
            ->first();
    }
}
