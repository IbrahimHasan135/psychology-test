<?php

use Addons\Demo\Dashboard\DemoOverviewCard;

return [
    'slug' => 'demo',
    'name' => 'Demo Addon',
    'description' => 'NovaBase sample addon for validating sidebar, dashboard, routes, views, and permissions.',
    'icon' => 'package',
    'admin_menu' => [
        [
            'label' => 'Overview',
            'route' => 'admin.addons.demo.index',
            'icon' => 'layout-dashboard',
            'permission' => 'demo.view',
        ],
    ],
    'permissions' => [
        'demo.view',
    ],
    'dashboard_cards' => [
        DemoOverviewCard::class,
    ],
];
