@extends('layouts.app', ['title' => 'Edit Role'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        @include('admin.partials.topbar', [
            'breadcrumb' => 'Edit Role',
            'title' => 'Edit '.$role->name,
            'description' => 'Update role mode, addon permissions, and account creation rules.',
        ])

        @if ($errors->any())
            <div class="notice error-notice admin-status">{{ $errors->first() }}</div>
        @endif

        <form class="admin-card role-edit-card" method="POST" action="{{ route('admin.roles.update', $role) }}">
            @csrf
            @method('PUT')

            <a class="button button-soft" href="{{ route('admin.roles.index') }}">Back</a>

            <label for="role_name">Role Name</label>
            <input id="role_name" name="name" required value="{{ old('name', $role->name) }}">

            <label class="checkbox-row compact-check">
                <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin', $role->is_admin))>
                <span>Admin role: can open the admin panel.</span>
            </label>

            <h3>Addon Permissions</h3>
            @foreach ($addonPermissions as $addonName => $permissions)
                <div class="permission-card compact-permission-card">
                    <strong>{{ $addonName }}</strong>
                    <div class="check-grid">
                        @foreach ($permissions as $permission)
                            <label>
                                <input type="checkbox" name="permissions[]" value="{{ $permission['permission'] }}" @checked(in_array($permission['permission'], old('permissions', $enabledPermissions), true))>
                                {{ $permission['permission'] }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <h3>Can Create Account Roles</h3>
            <div class="check-grid">
                @foreach ($roles as $availableRole)
                    <label>
                        <input type="checkbox" name="creatable_roles[]" value="{{ $availableRole->slug }}" @checked(in_array($availableRole->slug, old('creatable_roles', $enabledCreatableRoles), true))>
                        {{ $availableRole->name }}
                    </label>
                @endforeach
            </div>

            <button class="button button-primary" type="submit">Save Role</button>
        </form>
    </main>
</div>
@endsection
