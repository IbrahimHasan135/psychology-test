<?php

namespace App\Http\Middleware;

use App\Core\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantMembership
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(TenantContext::class);
        $user = $request->user();

        if ($context->isScopedRequest()) {
            abort_unless(
                $user
                && $context->isActiveSession()
                && $user->memberships()->where('tenant_id', $context->id())->where('status', 'active')->exists(),
                403
            );
        }

        return $next($request);
    }
}
