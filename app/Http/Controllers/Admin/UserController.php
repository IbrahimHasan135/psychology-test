<?php

namespace App\Http\Controllers\Admin;

use App\Core\Addons\AddonRegistry;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Core\Tenancy\TenantContext;
use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(AddonRegistry $addons): View
    {
        $rolePermissions = DB::table('role_addon_permissions')
            ->get()
            ->groupBy('role');
        $currentUser = auth()->user();
        $roles = Role::query()->orderByDesc('is_system')->orderBy('name')->get();

        $users = User::query();
        $context = app(TenantContext::class);
        if ($context->isScopedRequest()) {
            $users->whereHas('memberships', fn ($query) => $query
                ->where('tenant_id', $context->id())
                ->where('status', 'active'));
        }

        return view('admin.users.index', [
            'users' => $users->latest()->paginate(10),
            'roles' => $roles,
            'creatableRoles' => $roles->filter(fn (Role $role) => $currentUser?->canCreateRole($role->slug)),
            'addonPermissions' => $addons->permissions()->groupBy('addon_name'),
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'exists:roles,slug'],
        ]);

        if (! $request->user()?->canCreateRole($data['role'])) {
            return back()->withErrors('You are not allowed to create accounts with that role.')->withInput();
        }

        $role = $data['role'];
        unset($data['role']);
        $user = User::query()->create($data + [
            'role' => app(TenantContext::class)->isScopedRequest() ? UserRole::USER : $role,
        ]);

        if (app(TenantContext::class)->isScopedRequest()) {
            $user->memberships()->create([
                'tenant_id' => app(TenantContext::class)->id(),
                'role' => $role,
                'status' => 'active',
                'display_name' => $user->name,
                'joined_at' => now(),
            ]);
        }

        return redirect()->to(nova_route('admin.users.index'))->with('status', 'Account created.');
    }

    public function edit(User $user): View
    {
        abort_unless($this->canManageUser($user), 403);

        return view('admin.users.edit', [
            'editUser' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($this->canManageUser($user), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->to(nova_route('admin.users.index'))->with('status', 'Account updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($this->canManageUser($user), 403);

        if (auth()->id() === $user->id || $user->role === 'super_admin') {
            return back()->withErrors('You cannot delete this account.');
        }

        $user->delete();

        return redirect()->to(nova_route('admin.users.index'))->with('status', 'Account deleted.');
    }

    private function canManageUser(User $user): bool
    {
        $currentUser = auth()->user();

        if (! $currentUser?->canManageUsers()) {
            return false;
        }

        $context = app(TenantContext::class);
        if ($context->isScopedRequest() && ! $user->memberships()
            ->where('tenant_id', $context->id())
            ->where('status', 'active')
            ->exists()) {
            return false;
        }

        if ($currentUser->role === 'super_admin') {
            return true;
        }

        return $user->role !== 'super_admin' && ($currentUser->canCreateRole($user->role) || $user->id === $currentUser->id);
    }
}
