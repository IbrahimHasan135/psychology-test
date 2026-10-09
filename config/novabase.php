<?php

return [
    'tenancy' => [
        'enabled' => (bool) env('NOVABASE_TENANCY_ENABLED', false),
        'driver' => env('NOVABASE_TENANCY_DRIVER', 'single'),
        'default_slug' => env('NOVABASE_DEFAULT_TENANT_SLUG', 'default'),
    ],
];
