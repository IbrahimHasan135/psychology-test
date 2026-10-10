<?php

namespace App\Providers;

use App\Core\Tenancy\TenantContext;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $login = Str::lower(trim((string) $request->input('login')));

            return Limit::perMinute(10)->by($login.'|'.$request->ip());
        });

        RateLimiter::for('public-addon', function (Request $request): Limit {
            $tenant = app(\App\Core\Tenancy\TenantContext::class)->tenant()?->slug ?? 'platform';

            return Limit::perMinute(60)->by($tenant.'|'.$request->ip().'|'.$request->route()?->getName());
        });
    }
}
