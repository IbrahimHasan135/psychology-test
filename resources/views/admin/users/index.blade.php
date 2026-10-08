@extends('layouts.app', ['title' => 'Akun & Role'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        <div class="split">
            <div>
                <div class="eyebrow">Admin panel</div>
                <h2>Akun & Role</h2>
                <p>Base listing untuk pengelolaan akun dan visibility permission addon.</p>
            </div>
        </div>

        <section class="admin-panel">
            <div class="split compact-split">
                <div>
                    <h3>Addon Permissions</h3>
                    <p>Super Admin otomatis punya semua akses. Admin/User mengikuti permission addon yang tercatat di database.</p>
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
                    <div class="empty-state">Belum ada addon permission.</div>
                @endforelse
            </div>
        </section>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Dibuat</th>
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
                            <td colspan="5">Belum ada akun.</td>
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
