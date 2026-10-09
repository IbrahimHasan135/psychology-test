<?php

namespace Addons\Demo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Addons\Demo\Services\TenantProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        return view('demo::tenants', [
            'tenants' => Tenant::query()->with('owner')->withCount('memberships')->latest()->paginate(10),
        ]);
    }

    public function store(Request $request, TenantProvisioner $provisioner): RedirectResponse
    {
        $tenant = $provisioner->create($this->validatedData($request));

        return redirect()->route('admin.addons.demo.tenants.index')
            ->with('status', "Tenant {$tenant->name} created successfully.");
    }

    public function storePublic(Request $request, TenantProvisioner $provisioner): RedirectResponse
    {
        $tenant = $provisioner->create($this->validatedData($request));

        return redirect()->route('tenant.login', ['tenant' => $tenant->slug])
            ->with('status', 'Tenant account created. You can sign in with the owner credentials.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'tenant_name' => ['required', 'string', 'max:120'],
            'tenant_slug' => ['required', 'alpha_dash', 'max:60', 'unique:tenants,slug'],
            'owner_name' => ['required', 'string', 'max:120'],
            'owner_username' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'owner_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'owner_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }
}
