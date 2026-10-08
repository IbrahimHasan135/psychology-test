<?php

namespace App\Providers;

use App\Core\Addons\AddonRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AddonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        spl_autoload_register(function (string $class): void {
            if (! str_starts_with($class, 'Addons\\')) {
                return;
            }

            $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen('Addons\\')));
            $segments = explode(DIRECTORY_SEPARATOR, $relative, 2);

            if (count($segments) !== 2) {
                return;
            }

            [$addon, $classPath] = $segments;
            $file = base_path('addons'.DIRECTORY_SEPARATOR.$addon.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$classPath.'.php');

            if (is_file($file)) {
                require_once $file;
            }
        });

        $this->app->singleton(AddonRegistry::class);
    }

    public function boot(AddonRegistry $addons): void
    {
        foreach ($addons->viewNamespaces() as $namespace => $path) {
            $this->loadViewsFrom($path, $namespace);
        }

        $this->loadMigrationsFrom($addons->migrationPaths());

        foreach ($addons->routeFiles() as $routeFile) {
            Route::middleware(['web', 'auth'])
                ->group($routeFile);
        }
    }
}
