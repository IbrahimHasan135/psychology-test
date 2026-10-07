@extends('layouts.app', ['title' => 'Akun & Role'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        <div class="split">
            <div>
                <div class="eyebrow">Admin panel</div>
                <h2>Akun & Role</h2>
                <p>Base listing untuk pengelolaan akun. Form create/update bisa ditambahkan pada modul ini.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Dibuat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="pill">{{ \App\Enums\UserRole::label($user->role) }}</span></td>
                            <td>{{ $user->created_at?->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Belum ada akun.</td>
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