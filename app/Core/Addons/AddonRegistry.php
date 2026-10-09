<?php

namespace App\Core\Addons;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class AddonRegistry
{
    /** @var Collection<string, AddonMeta>|null */
    private ?Collection $addons = null;

    public function all(): Collection
    {
        if ($this->addons !== null) {
            return $this->addons;
        }

        $this->addons = collect(config('addons', []))
            ->map(function (array|string $config, string $slug): ?AddonMeta {
                $path = is_array($config) ? ($config['path'] ?? null) : $config;
                $enabled = is_array($config) ? (bool) ($config['enabled'] ?? true) : true;

                if (! $path || ! $enabled) {
                    return null;
                }

                $manifest = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'addon.php';
                if (! is_file($manifest)) {
                    return null;
                }

                $data = require $manifest;

                return is_array($data) ? AddonMeta::fromArray($slug, $path, $data, $enabled) : null;
            })
            ->filter()
            ->keyBy(fn (AddonMeta $addon) => $addon->slug);

        return $this->addons;
    }

    public function enabled(): Collection
    {
        return $this->all()->filter(fn (AddonMeta $addon) => $addon->enabled);
    }

    public function find(string $slug): ?AddonMeta
    {
        return $this->enabled()->get($slug);
    }

    public function adminMenuFor(?User $user): Collection
    {
        return $this->enabled()
            ->filter(fn (AddonMeta $addon) => $user?->canAccessAddon($addon->slug))
            ->map(function (AddonMeta $addon) use ($user) {
                return [
                    'addon' => $addon,
                    'items' => collect($addon->adminMenu)
                        ->filter(fn (array $item) => empty($item['permission']) || $user?->hasPermission($item['permission']))
                        ->values()
                        ->all(),
                ];
            })
            ->filter(fn (array $group) => ! empty($group['items']));
    }

    public function dashboardCardsFor(?User $user): Collection
    {
        return $this->enabled()
            ->filter(fn (AddonMeta $addon) => $user?->canAccessAddon($addon->slug))
            ->flatMap(function (AddonMeta $addon) use ($user) {
                return collect($addon->dashboardCards)
                    ->map(fn (array|string $card) => $this->resolveCard($addon, $card))
                    ->filter()
                    ->filter(function (array $card) use ($user) {
                        return empty($card['permission']) || $user?->hasPermission($card['permission']);
                    });
            })
            ->sortBy('order')
            ->values();
    }

    public function permissions(): Collection
    {
        return $this->enabled()
            ->flatMap(fn (AddonMeta $addon) => collect($addon->permissions)->map(fn (string $permission) => [
                'addon' => $addon->slug,
                'addon_name' => $addon->name,
                'permission' => $permission,
            ]))
            ->values();
    }

    public function pageBuilderDefinitions(?User $user = null): array
    {
        $definitions = [];

        foreach ($this->enabled() as $addon) {
            foreach ($addon->webEditorBlocks as $registration) {
                $class = $registration["definition"] ?? null;
                $permission = $registration["permission"] ?? null;

                if (! is_string($class) || ! class_exists($class)) {
                    continue;
                }
                if ($permission && $user && ! $user->hasPermission($permission)) {
                    continue;
                }

                $definition = app($class)->definition();
                $type = $definition["type"] ?? ($registration["type"] ?? null);
                if (! is_string($type) || $type === "" || isset($definitions[$type])) {
                    continue;
                }

                $definitions[$type] = array_merge($definition, [
                    "addon" => $addon->slug,
                    "permission" => $permission,
                ]);
            }
        }

        return $definitions;
    }

    public function routeFiles(): Collection
    {
        return $this->enabled()
            ->map(fn (AddonMeta $addon) => $addon->path.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php')
            ->filter(fn (string $path) => is_file($path));
    }

    public function migrationPaths(): array
    {
        return $this->enabled()
            ->map(fn (AddonMeta $addon) => $addon->path.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations')
            ->filter(fn (string $path) => is_dir($path))
            ->values()
            ->all();
    }

    public function viewNamespaces(): Collection
    {
        return $this->enabled()
            ->mapWithKeys(fn (AddonMeta $addon) => [
                $addon->slug => $addon->path.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views',
            ])
            ->filter(fn (string $path) => is_dir($path));
    }

    private function resolveCard(AddonMeta $addon, array|string $card): ?array
    {
        if (is_array($card)) {
            return array_merge(['addon' => $addon], $card);
        }

        if (! class_exists($card)) {
            return null;
        }

        $instance = app($card);
        $rendered = method_exists($instance, 'render') ? $instance->render() : '';

        if ($rendered instanceof View) {
            $rendered = $rendered->render();
        }

        return [
            'addon' => $addon,
            'id' => method_exists($instance, 'id') ? $instance->id() : class_basename($card),
            'title' => method_exists($instance, 'title') ? $instance->title() : $addon->name,
            'icon' => method_exists($instance, 'icon') ? $instance->icon() : $addon->icon,
            'order' => method_exists($instance, 'order') ? $instance->order() : 999,
            'permission' => method_exists($instance, 'permission') ? $instance->permission() : null,
            'content' => $rendered,
        ];
    }
}
