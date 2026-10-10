<?php

namespace App\Http\Middleware;

use App\Core\Addons\AddonRegistry;
use App\Core\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsurePublicAddonAccess
{
    public function handle(Request $request, Closure $next, string $addonSlug): Response
    {
        $addon = app(AddonRegistry::class)->find($addonSlug);
        abort_unless($addon, 404);

        $context = app(TenantContext::class);
        if ($addon->scope === 'platform' && $context->isScopedRequest()) {
            abort(404);
        }

        if (in_array($addon->scope, ['tenant', 'all'], true) && ($addon->scope === 'tenant' || $context->isScopedRequest())) {
            if (config('novabase.tenancy.enabled') && ! $context->isScopedRequest()) {
                abort(404);
            }

            if ($context->id() !== null && Schema::hasTable('tenant_addons')) {
                $enabled = DB::table('tenant_addons')
                    ->where('tenant_id', $context->id())
                    ->where('addon_slug', $addonSlug)
                    ->where('status', 'active')
                    ->exists();
                abort_unless($enabled, 404);
            }
        }

        return $next($request);
    }
}
