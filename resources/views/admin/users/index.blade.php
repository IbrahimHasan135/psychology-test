@extends('layouts.app', ['title' => 'User Management'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        @include('admin.partials.topbar', [
            'breadcrumb' => 'User Management',
            'title' => 'User Management',
            'description' => 'Create and maintain user accounts. Available roles are controlled by Super Admin.',
        ])

        @if (session('status'))
            <div class="notice admin-status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="notice error-notice admin-status">{{ $errors->first() }}</div>
        @endif

        <section class="admin-grid">
            <form class="admin-card form-card" method="POST" action="{{ nova_route('admin.users.store') }}">
                @csrf
                <div class="admin-card-header">
                    <div>
                        <span class="card-kicker">Account directory</span>
                        <h2>Create Account</h2>
                        <p>Add a user and assign one of the roles available to you.</p>
                    </div>
                </div>

                <label for="name">Full Name</label>
                <input id="name" name="name" required value="{{ old('name') }}" placeholder="Example: John Doe">

                <label for="username">Username</label>
                <input id="username" name="username" required value="{{ old('username') }}" placeholder="Example: john.doe">

                <label for="email">Email</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}" placeholder="john@example.com">

                <label for="password">Password</label>
                <input id="password" name="password" type="password" required placeholder="Minimum 6 characters">

                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="">Select role...</option>
                    @foreach ($creatableRoles as $role)
                        <option value="{{ $role->slug }}" @selected(old('role') === $role->slug)>{{ $role->name }}</option>
                    @endforeach
                </select>

                <button class="button button-primary full-button" type="submit">Create Account</button>
            </form>

            <div class="admin-card wide-card">
                <div class="admin-card-header">
                    <div>
                        <span class="card-kicker">Directory</span>
                        <h2>Accounts</h2>
                        <p>Manage account identity and access assignments.</p>
                    </div>
                    <span class="pill">{{ $users->total() }} accounts</span>
                </div>
                <div class="table-wrap embedded-table">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td data-label="Name">{{ $user->name }}</td>
                                    <td data-label="Username">{{ $user->username ?? '-' }}</td>
                                    <td data-label="Email">{{ $user->email }}</td>
                                    <td data-label="Role"><span class="pill">{{ \App\Enums\UserRole::label($user->role) }}</span></td>
                                    <td data-label="Created">{{ $user->created_at?->format('d M Y H:i') }}</td>
                                    <td data-label="Actions">
                                        <div class="action-group">
                                        <a class="button button-soft button-sm" href="{{ nova_route('admin.users.edit', $user) }}">Edit</a>
                                        @if (auth()->id() !== $user->id)
                                            <form class="inline-form" method="POST" action="{{ nova_route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this account?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="button danger-button button-sm" type="submit">Delete</button>
                                            </form>
                                        @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No accounts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 18px;">
                    {{ $users->links() }}
                </div>
            </div>
        </section>

        <section class="admin-panel permission-overview">
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
                            @php($roleSlug = $role->slug)
                            <div class="permission-row">
                                <span>{{ $role->name }}</span>
                                <small>
                                    @if ($roleSlug === \App\Enums\UserRole::SUPER_ADMIN)
                                        All permissions
                                    @else
                                        {{ $rolePermissions->get($roleSlug)?->pluck('permission')->intersect($permissions->pluck('permission'))->implode(', ') ?: 'No access' }}
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

    </main>
</div>
@endsection
