<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Throwable;

class RunDatabaseMigrations
{
    public function handle($request, Closure $next)
    {
        if (config('database.auto_migrate')) {
            $this->runMigrationsAndSeeders();
        }

        return $next($request);
    }

    private function runMigrationsAndSeeders(): void
    {
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
        } catch (Throwable $exception) {
            report($exception);
        } finally {
            $lock->release();
        }
    }
}