@extends('layouts.app', ['title' => 'Accounts & Roles'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        @include('admin.partials.topbar', [
            'breadcrumb' => 'Accounts & Roles',
            'title' => 'Accounts & Roles',
            'description' => 'Manage user visibility and addon permissions from the NovaBase admin workspace.',
        ])

        <section class="admin-panel">
            <div class="split compact-split">
                <div>
                    <h3>Addon Permissions</h3>
                    <p>Super Admin has full access. Admin and User roles follow addon permissions stored in the database.</p>
                </div>
            </div>
            <div class="permission-grid">
                @forelse ($addonPermissions as $addonName => $permissions)
                    <article class="permission-card">
                        <strong>{{ $addonName }}</strong>
                        @foreach ($roles as $role)
                            <div class="permission-row">
                                <span>{{ \App\Enums\UserRole::label($role) }}</span>
                                <small>
                                    @if ($role === \App\Enums\UserRole::SUPER_ADMIN)
                                        All permissions
                                    @else
                                        {{ $rolePermissions->get($role)?->pluck('permission')->intersect($permissions->pluck('permission'))->implode(', ') ?: 'No access' }}
                                    @endif
                                </small>
                            </div>
                        @endforeach
                    </article>
                @empty
                    <div class="empty-state">No addon permissions are registered.</div>
                @endforelse
            </div>
        </section>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->username ?? '-' }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="pill">{{ \App\Enums\UserRole::label($user->role) }}</span></td>
                            <td>{{ $user->created_at?->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 18px;">
            {{ $users->links() }}
        </div>
    </main>
</div>
@endsection
