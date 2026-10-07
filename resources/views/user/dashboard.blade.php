@extends('layouts.app', ['title' => 'User Dashboard'])

@section('content')
<main class="page section">
    <div class="split">
        <div>
            <div class="eyebrow">User area</div>
            <h2>Halo, {{ $user->name }}</h2>
            <p>Ini area awal untuk fitur user, misalnya mengerjakan tes, melihat hasil, atau memperbarui profil.</p>
        </div>
        <span class="pill">{{ \App\Enums\UserRole::label($user->role) }}</span>
    </div>

    <div class="grid-3">
        <article class="card">
            <h3>Tes</h3>
            <p>Tempat fitur tes psikologi nanti dipasang.</p>
        </article>
        <article class="card">
            <h3>Hasil</h3>
            <p>Tempat ringkasan hasil dan histori user.</p>
        </article>
        <article class="card">
            <h3>Profil</h3>
            <p>Tempat pengaturan akun personal.</p>
        </article>
    </div>
</main>
@endsection