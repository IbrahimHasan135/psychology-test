<?php

use Addons\Demo\Dashboard\DemoOverviewCard;

return [
    'slug' => 'demo',
    'name' => 'Demo Addon',
    'description' => 'Contoh addon NovaBase untuk validasi sidebar, dashboard, route, view, dan permission.',
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
