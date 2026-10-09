<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RunDatabaseMigrations
{
    public function handle($request, Closure $next)
    {
        if (config('database.auto_migrate') && app()->environment(['local', 'testing'])) {
            $this->runMigrationsAndSeeders();
        }

        return $next($request);
    }

    private function runMigrationsAndSeeders(): void
    {
        $signature = $this->databaseSignature();
        $cacheKey = 'database-auto-migrate-signature:'.md5((string) config('database.default').':'.(string) config('database.connections.'.config('database.default').'.database'));

        if (Schema::hasTable('migrations') && Cache::get($cacheKey) === $signature) {
            return;
        }

        $lock = Cache::lock('database-auto-migrate', 30);

        if (! $lock->get()) {
            return;
        }

        try {
            Artisan::call('migrate', [
                '--force' => true,
            ]);

            if (config('database.auto_seed')) {
                Artisan::call('db:seed', [
                    '--force' => true,
                ]);
            }

            Cache::put($cacheKey, $signature);
        } catch (Throwable $exception) {
            report($exception);
        } finally {
            $lock->release();
        }
    }

    private function databaseSignature(): string
    {
        $files = array_merge(
            glob(database_path('migrations/*.php')) ?: [],
            glob(database_path('seeders/*.php')) ?: [],
            glob(base_path('addons/*/database/migrations/*.php')) ?: [],
            [config_path('addons.php')]
        );

        $fingerprint = collect($files)
            ->filter(fn (string $file) => is_file($file))
            ->map(fn (string $file) => $file.':'.filemtime($file).':'.filesize($file))
            ->implode('|');

        return sha1($fingerprint);
    }
}
