@extends('layouts.app', ['title' => 'Admin Dashboard'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        <div class="split">
            <div>
                <div class="eyebrow">Admin panel</div>
                <h2>Dashboard</h2>
                <p>Ringkasan awal sistem dan akses role.</p>
            </div>
            <span class="pill">{{ \App\Enums\UserRole::label(auth()->user()->role) }}</span>
        </div>

        <section class="grid-3">
            <article class="card">
                <div class="metric">{{ $totalUsers }}</div>
                <p>Total akun</p>
            </article>
            @foreach ($roleCounts as $role => $count)
                <article class="card">
                    <div class="metric">{{ $count }}</div>
                    <p>{{ \App\Enums\UserRole::label($role) }}</p>
                </article>
            @endforeach
        </section>
    </main>
</div>
@endsection