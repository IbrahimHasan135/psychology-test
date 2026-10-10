<?php

namespace App\Core\Addons;

class AddonMeta
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly string $path,
        public readonly string $description = '',
        public readonly string $icon = 'box',
        public readonly string $scope = 'all',
        public readonly bool $enabled = true,
        public readonly array $adminMenu = [],
        public readonly array $permissions = [],
        public readonly array $dashboardCards = [],
        public readonly array $webEditorBlocks = [],
        public readonly array $reports = [],
        public readonly array $listeners = [],
        public readonly bool $publicRoutes = false,
        public readonly string $publicRouteFile = 'routes/public.php',
    ) {}

    public static function fromArray(string $slug, string $path, array $data, bool $enabled = true): self
    {
        $publicRoutes = $data['public_routes'] ?? false;

        return new self(
            slug: $data['slug'] ?? $slug,
            name: $data['name'] ?? str($slug)->headline()->toString(),
            path: rtrim($path, DIRECTORY_SEPARATOR),
            description: $data['description'] ?? '',
            icon: $data['icon'] ?? 'box',
            scope: $data['scope'] ?? 'all',
            enabled: (bool) ($data['enabled'] ?? $enabled),
            adminMenu: $data['admin_menu'] ?? [],
            permissions: $data['permissions'] ?? [],
            dashboardCards: $data['dashboard_cards'] ?? [],
            webEditorBlocks: $data['web_editor']['blocks'] ?? [],
            reports: $data['reports'] ?? [],
            listeners: $data['listeners'] ?? [],
            publicRoutes: is_array($publicRoutes) ? (bool) ($publicRoutes['enabled'] ?? true) : (bool) $publicRoutes,
            publicRouteFile: is_array($publicRoutes) ? (string) ($publicRoutes['route_file'] ?? 'routes/public.php') : 'routes/public.php',
        );
    }
}
