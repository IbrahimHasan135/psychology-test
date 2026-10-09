<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Core\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

final class Audit
{
    public static function record(string $action, ?Model $target = null, array $metadata = []): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        AuditLog::query()->create([
            'actor_user_id' => auth()->id(),
            'tenant_id' => app(TenantContext::class)->isScopedRequest() ? app(TenantContext::class)->id() : null,
            'action' => $action,
            'target_type' => $target ? $target->getMorphClass() : null,
            'target_id' => $target?->getKey(),
            'metadata_json' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
