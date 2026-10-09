<?php

namespace Addons\Demo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        return view('demo::tenants', [
            'tenants' => Tenant::query()->with('owner')->latest()->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tenant_name' => ['required', 'string', 'max:120'],
            'tenant_slug' => ['required', 'alpha_dash', 'max:60', 'unique:tenants,slug'],
            'owner_name' => ['required', 'string', 'max:120'],
            'owner_username' => ['required', 'alpha_dash', 'max:120', 'unique:users,username'],
            'owner_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'owner_password' => ['required', 'string', 'min:8'],
        ]);

        $tenant = DB::transaction(function () use ($data): Tenant {
            $owner = User::query()->create([
                'name' => $data['owner_name'],
                'username' => $data['owner_username'],
                'email' => $data['owner_email'],
                'role' => 'user',
                'password' => $data['owner_password'],
            ]);

            $tenant = Tenant::query()->create([
                'name' => $data['tenant_name'],
                'slug' => strtolower($data['tenant_slug']),
                'status' => 'active',
                'owner_user_id' => $owner->id,
            ]);

            $tenant->memberships()->create([
                'user_id' => $owner->id,
                'role' => 'super_admin',
                'status' => 'active',
                'display_name' => $owner->name,
                'joined_at' => now(),
            ]);

            SitePage::query()->withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => 'Home',
                'slug' => 'home',
                'display_mode' => 'sections',
                'is_published' => true,
            ]);

            return $tenant;
        });

        return redirect()->route('admin.addons.demo.tenants.index')
            ->with('status', "Tenant {$tenant->name} created successfully.");
    }
}
