<?php

namespace Addons\Demo\PageBuilder;

class TenantSignupBlock
{
    public function definition(): array
    {
        return [
            'type' => 'demo.tenant-signup',
            'label' => 'Tenant Account Signup',
            'category' => 'Demo Addon',
            'icon' => 'bi-person-plus',
            'renderer' => 'tenant-signup',
            'fields' => [
                ['path' => 'badge', 'label' => 'Badge', 'type' => 'text'],
                ['path' => 'title', 'label' => 'Title', 'type' => 'text'],
                ['path' => 'text', 'label' => 'Text', 'type' => 'textarea'],
            ],
            'defaults' => [
                'badge' => 'Create your workspace',
                'title' => 'Create a tenant account',
                'text' => 'Register your workspace and get a private website and admin panel at your own URL.',
            ],
        ];
    }
}
