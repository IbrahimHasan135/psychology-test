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
            <form class="admin-card" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <h2>Create Account</h2>

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
                <h2>Accounts</h2>
                <div class="table-wrap embedded-table">
                    <table>
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
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->username ?? '-' }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="pill">{{ \App\Enums\UserRole::label($user->role) }}</span></td>
                                    <td>{{ $user->created_at?->format('d M Y H:i') }}</td>
                                    <td>
                                        <a class="button button-soft" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                                        @if (auth()->id() !== $user->id)
                                            <form class="inline-form" method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this account?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="button danger-button" type="submit">Delete</button>
                                            </form>
                                        @endif
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
