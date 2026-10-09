<?php

namespace App\Http\Controllers\Admin;

use App\Core\Addons\AddonRegistry;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\TenantMembership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Core\Tenancy\TenantContext;

class RoleController extends Controller
{
    public function index(AddonRegistry $addons): View
    {
        $tenantId = app(TenantContext::class)->isScopedRequest() ? app(TenantContext::class)->id() : null;
        $permissionQuery = DB::table('role_addon_permissions');
        $creatableQuery = DB::table('role_creatable_roles');
        if ($tenantId) {
            $permissionQuery->where(function ($query) use ($tenantId): void {
                $query->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
            });
            $creatableQuery->where(function ($query) use ($tenantId): void {
                $query->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
            });
        }

        return view('admin.roles.index', [
            'roles' => Role::query()->orderByDesc('is_system')->orderBy('name')->get(),
            'addons' => $addons->enabled(),
            'addonPermissions' => $addons->permissions()->groupBy('addon_name'),
            'rolePermissions' => $permissionQuery->get()->groupBy('role'),
            'creatableRoles' => $creatableQuery->get()->groupBy('role_slug'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedRole($request);
        $slug = Str::slug($data['name'], '_');

        $role = Role::query()->create([
            'name' => $data['name'],
            'slug' => $slug,
            'is_system' => false,
            'is_admin' => $request->boolean('is_admin'),
        ]);

        $this->syncRoleAccess($role, $request);

        return redirect()->to(nova_route('admin.roles.index'))->with('status', 'Role created.');
    }

    public function edit(Role $role, AddonRegistry $addons): View
    {
        $this->guardSystemRole($role);

        return view('admin.roles.edit', [
            'role' => $role,
            'roles' => Role::query()->orderByDesc('is_system')->orderBy('name')->get(),
            'addonPermissions' => $addons->permissions()->groupBy('addon_name'),
            'enabledPermissions' => DB::table('role_addon_permissions')->where('role', $role->slug)->where('tenant_id', $role->tenant_id)->pluck('permission')->all(),
            'enabledCreatableRoles' => DB::table('role_creatable_roles')->where('role_slug', $role->slug)->where('tenant_id', $role->tenant_id)->pluck('creatable_role_slug')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->guardSystemRole($role);
        $data = $this->validatedRole($request);

        $role->update([
            'name' => $data['name'],
            'slug' => $role->is_system ? $role->slug : Str::slug($data['name'], '_'),
            'is_admin' => $role->slug === UserRole::SUPER_ADMIN || $request->boolean('is_admin'),
        ]);

        $this->syncRoleAccess($role->refresh(), $request);

        return redirect()->to(nova_route('admin.roles.index'))->with('status', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $context = app(TenantContext::class);
        if ($role->is_system || ($context->isScopedRequest() && $role->tenant_id !== $context->id()) || User::query()->where('role', $role->slug)->exists() || TenantMembership::query()->where('tenant_id', $role->tenant_id)->where('role', $role->slug)->exists()) {
            return redirect()->to(nova_route('admin.roles.index'))->withErrors('System roles or roles currently assigned to users cannot be deleted.');
        }

        DB::table('role_addon_permissions')->where('role', $role->slug)->where('tenant_id', $role->tenant_id)->delete();
        DB::table('role_creatable_roles')->where('role_slug', $role->slug)->where('tenant_id', $role->tenant_id)->delete();
        $role->delete();

        return redirect()->to(nova_route('admin.roles.index'))->with('status', 'Role deleted.');
    }

    private function validatedRole(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'max:160'],
            'creatable_roles' => ['array'],
            'creatable_roles.*' => ['string', 'max:120'],
        ]);
    }

    private function syncRoleAccess(Role $role, Request $request): void
    {
        $tenantId = app(TenantContext::class)->isScopedRequest() ? app(TenantContext::class)->id() : null;
        $permissions = collect($request->input('permissions', []))->unique()->values();
        DB::table('role_addon_permissions')->where('role', $role->slug)->where('tenant_id', $tenantId)->delete();

        foreach ($permissions as $permission) {
            DB::table('role_addon_permissions')->insert([
                'role' => $role->slug,
                'tenant_id' => $tenantId,
                'addon_slug' => Str::before($permission, '.'),
                'permission' => $permission,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('role_creatable_roles')->where('role_slug', $role->slug)->where('tenant_id', $tenantId)->delete();
        foreach (collect($request->input('creatable_roles', []))->unique() as $creatableRole) {
            DB::table('role_creatable_roles')->insert([
                'role_slug' => $role->slug,
                'creatable_role_slug' => $creatableRole,
                'tenant_id' => $tenantId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function guardSystemRole(Role $role): void
    {
        if (app(TenantContext::class)->isScopedRequest() && $role->is_system) {
            abort(403, 'System roles are managed by the platform.');
        }
    }
}
