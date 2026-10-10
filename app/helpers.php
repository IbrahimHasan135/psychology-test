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

if (! function_exists('addon_public_url')) {
    function addon_public_url(string $addonSlug, string $routeName, mixed $parameters = [], bool $absolute = true): string
    {
        $name = 'addon.'.$addonSlug.'.'.$routeName;
        $route = app('router')->getRoutes()->getByName($name);
        $parameters = is_array($parameters) ? $parameters : [$parameters];

        if ($route && in_array('tenant', $route->parameterNames(), true)
            && ! array_key_exists('tenant', $parameters)
            && app(TenantContext::class)->isScopedRequest()) {
            $parameters['tenant'] = app(TenantContext::class)->tenant()?->slug;
        }

        return route($name, $parameters, $absolute);
    }
}
