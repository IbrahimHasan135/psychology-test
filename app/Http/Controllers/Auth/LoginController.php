<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Core\Tenancy\TenantContext;
use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Support\Audit;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('status', 'active')
            ->where(function ($query) use ($credentials): void {
                $query->where('username', $credentials['login'])
                    ->orWhere('email', $credentials['login']);
            })
            ->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'Username/email atau password belum cocok.',
            ]);
        }

        $context = app(TenantContext::class);
        $ownedTenant = null;
        if (! $context->isScopedRequest() && config('novabase.tenancy.enabled') && $user->role === UserRole::USER) {
            $tenantMemberships = $user->memberships()
                ->where('status', 'active')
                ->whereHas('tenant', fn ($query) => $query->where('slug', '!=', config('novabase.tenancy.default_slug', 'default')));
            $ownedTenant = (clone $tenantMemberships)
                ->where('role', UserRole::SUPER_ADMIN)
                ->with('tenant')
                ->first()?->tenant;
            if (! $ownedTenant && $tenantMemberships->exists()) {
                throw ValidationException::withMessages([
                    'login' => 'This account must use its tenant login page.',
                ]);
            }
        }

        if ($context->isScopedRequest() && ! $user->memberships()
            ->where('tenant_id', $context->id())
            ->where('status', 'active')
            ->exists()) {
            throw ValidationException::withMessages([
                'login' => 'This account does not belong to the selected tenant.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        if (Schema::hasColumn('users', 'last_login_at')) {
            $user->forceFill(['last_login_at' => now()])->saveQuietly();
        }
        Audit::record('auth.login', $user, ['scoped' => $context->isScopedRequest()]);

        if ($context->isScopedRequest()) {
            $request->session()->put('novabase.active_tenant_slug', $context->tenant()?->slug);
        } elseif ($ownedTenant) {
            $request->session()->put('novabase.active_tenant_slug', $ownedTenant->slug);
        } else {
            $request->session()->forget('novabase.active_tenant_slug');
        }

        if ($ownedTenant) {
            return redirect()->route('tenant.admin.dashboard', ['tenant' => $ownedTenant->slug]);
        }

        return redirect()->intended(nova_route($request->user()->dashboardRoute()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $context = app(TenantContext::class);
        if ($context->isScopedRequest() && ! $context->isActiveSession()) {
            return redirect()->route('tenant.home', ['tenant' => $context->tenant()->slug]);
        }

        $user = $request->user();
        Auth::logout();
        Audit::record('auth.logout', $user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to(nova_route('home'));
    }
}
