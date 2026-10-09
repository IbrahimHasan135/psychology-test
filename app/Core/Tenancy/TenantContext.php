<?php

namespace App\Core\Tenancy;

use App\Models\Tenant;
use App\Models\User;

class TenantContext
{
    private ?Tenant $tenant = null;
    private bool $scopedRequest = false;

    public function set(?Tenant $tenant, bool $scopedRequest = false): void
    {
        $this->tenant = $tenant;
        $this->scopedRequest = $scopedRequest;
    }

    public function clear(): void
    {
        $this->tenant = null;
        $this->scopedRequest = false;
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

    public function roleFor(User $user): string
    {
        if (! $this->scopedRequest) {
            return (string) $user->role;
        }

        return (string) $user->memberships()
            ->where('tenant_id', $this->id())
            ->where('status', 'active')
            ->value('role');
    }
}
