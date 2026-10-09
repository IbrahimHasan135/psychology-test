@extends('layouts.app', ['title' => 'Role Management'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        @include('admin.partials.topbar', [
            'breadcrumb' => 'Role Management',
            'title' => 'Role Management',
            'description' => 'Create roles, assign addon access, and decide which account roles each role can create.',
        ])

        @if (session('status'))
            <div class="notice admin-status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="notice error-notice admin-status">{{ $errors->first() }}</div>
        @endif

        <section class="admin-grid">
            <form class="admin-card form-card" method="POST" action="{{ nova_route('admin.roles.store') }}">
                @csrf
                <div class="admin-card-header">
                    <div>
                        <span class="card-kicker">Access control</span>
                        <h2>Create Role</h2>
                        <p>Define what this role can access and which accounts it can create.</p>
                    </div>
                </div>
                <label for="role_name">Role Name</label>
                <input id="role_name" name="name" required placeholder="Example: Sales Admin" value="{{ old('name') }}">

                <label class="checkbox-row compact-check">
                    <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin'))>
                    <span>Admin role: can open the admin panel.</span>
                </label>

                <h3>Addon Permissions</h3>
                @foreach ($addonPermissions as $addonName => $permissions)
                    <div class="permission-card compact-permission-card">
                        <strong>{{ $addonName }}</strong>
                        <div class="check-grid">
                            @foreach ($permissions as $permission)
                                <label><input type="checkbox" name="permissions[]" value="{{ $permission['permission'] }}"> {{ $permission['permission'] }}</label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <h3>Can Create Account Roles</h3>
                <div class="check-grid">
                    @foreach ($roles as $role)
                        <label><input type="checkbox" name="creatable_roles[]" value="{{ $role->slug }}"> {{ $role->name }}</label>
                    @endforeach
                </div>

                <button class="button button-primary full-button" type="submit">Create Role</button>
            </form>

            <div class="admin-card wide-card">
                <div class="admin-card-header">
                    <div>
                        <span class="card-kicker">Directory</span>
                        <h2>Roles</h2>
                        <p>Review system roles and their current access rules.</p>
                    </div>
                    <span class="pill">{{ $roles->count() }} roles</span>
                </div>
                <div class="table-wrap embedded-table">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Slug</th>
                                <th>Mode</th>
                                <th>Addon Permissions</th>
                                <th>Can Create</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr>
                                    <td data-label="Role">
                                        <strong>{{ $role->name }}</strong>
                                        @if ($role->is_system)
                                            <span class="pill">system</span>
                                        @endif
                                    </td>
                                    <td data-label="Slug">{{ $role->slug }}</td>
                                    <td data-label="Mode">{{ $role->is_admin ? 'Admin panel' : 'User portal' }}</td>
                                    <td data-label="Addon permissions">
                                        @php($permissions = $rolePermissions->get($role->slug)?->pluck('permission')->filter()->values())
                                        @if ($permissions?->isNotEmpty())
                                            <div class="summary-text">
                                                @foreach ($permissions as $permission)<span class="summary-chip">{{ $permission }}</span>@endforeach
                                            </div>
                                        @else
                                            <span class="summary-muted">No addon access</span>
                                        @endif
                                    </td>
                                    <td data-label="Can create">
                                        @php($creatable = $creatableRoles->get($role->slug)?->pluck('creatable_role_slug')->filter()->values())
                                        @if ($creatable?->isNotEmpty())
                                            <div class="summary-text">
                                                @foreach ($creatable as $createdRole)<span class="summary-chip">{{ $createdRole }}</span>@endforeach
                                            </div>
                                        @else
                                            <span class="summary-muted">None</span>
                                        @endif
                                    </td>
                                    <td data-label="Actions">
                                        <div class="action-group">
                                        <a class="button button-soft button-sm" href="{{ nova_route('admin.roles.edit', $role) }}">Edit</a>
                                        <form method="POST" action="{{ nova_route('admin.roles.destroy', $role) }}" class="inline-form" onsubmit="return confirm('Delete this role?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="button danger-button button-sm" type="submit">Delete</button>
                                        </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
