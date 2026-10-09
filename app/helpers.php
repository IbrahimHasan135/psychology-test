<?php

use App\Core\Tenancy\TenantContext;

if (! function_exists('nova_route')) {
    function nova_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        if (app(TenantContext::class)->isScopedRequest()) {
            $tenantName = 'tenant.'.$name;
            if (app('router')->has($tenantName)) {
                return route($tenantName, $parameters, $absolute);
            }
        }

        return route($name, $parameters, $absolute);
    }
}
