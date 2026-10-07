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
            $this->runMigrations();
        }

        return $next($request);
    }

    private function runMigrations(): void
    {
        $lock = Cache::lock('database-auto-migrate', 30);

        if (! $lock->get()) {
            return;
        }

        try {
            Artisan::call('migrate', [
                '--force' => true,
            ]);
        } catch (Throwable $exception) {
            report($exception);
        } finally {
            $lock->release();
        }
    }
}