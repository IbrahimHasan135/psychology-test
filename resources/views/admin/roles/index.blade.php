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
            <form class="admin-card" method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <h2>Create Role</h2>
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
                <h2>Roles</h2>
                <div class="table-wrap embedded-table">
                    <table>
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
                                    <td>
                                        <strong>{{ $role->name }}</strong>
                                        @if ($role->is_system)
                                            <span class="pill">system</span>
                                        @endif
                                    </td>
                                    <td>{{ $role->slug }}</td>
                                    <td>{{ $role->is_admin ? 'Admin panel' : 'User portal' }}</td>
                                    <td>{{ $rolePermissions->get($role->slug)?->pluck('permission')->implode(', ') ?: 'No addon access' }}</td>
                                    <td>{{ $creatableRoles->get($role->slug)?->pluck('creatable_role_slug')->implode(', ') ?: 'None' }}</td>
                                    <td>
                                        <a class="button button-soft" href="{{ route('admin.roles.edit', $role) }}">Edit</a>
                                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="inline-form" onsubmit="return confirm('Delete this role?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="button danger-button" type="submit">Delete</button>
                                        </form>
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
