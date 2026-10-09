<?php

namespace App\Http\Middleware;

use App\Core\Tenancy\TenantContext;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(TenantContext::class);
        if (! Schema::hasTable('tenants')) {
            $context->clear();

            return $next($request);
        }

        $enabled = (bool) config('novabase.tenancy.enabled');
        $slug = $request->route('tenant');

        if ($slug instanceof Tenant) {
            $slug = $slug->slug;
        }

        if ($slug !== null) {
            abort_unless($enabled, 404);
            $tenant = Tenant::query()->where('slug', $slug)->where('status', 'active')->firstOrFail();
            $context->set($tenant, true);
            URL::defaults(['tenant' => $tenant->slug]);

            return $next($request);
        }

        $default = Tenant::query()->where('slug', config('novabase.tenancy.default_slug', 'default'))->first();
        $context->set($default, false);

        return $next($request);
    }
}
