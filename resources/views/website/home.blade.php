@extends('layouts.app', ['title' => 'Psychology Test'])

@section('content')
<main class="page">
    <section class="hero">
        <div>
            <div class="eyebrow">Laravel base app</div>
            <h1>Psychology Test</h1>
            <p>Fondasi website dan admin panel untuk pengembangan bertahap: akun, role, dashboard, dan struktur modul yang siap ditambah fitur tes psikologi, laporan, atau integrasi IoT.</p>
            <div class="hero-actions">
                <a class="button button-primary" href="{{ route('login') }}">Masuk</a>
                @auth
                    <a class="button button-soft" href="{{ route(auth()->user()->dashboardRoute()) }}">Buka Dashboard</a>
                @endauth
            </div>
        </div>
        <img class="hero-art" src="{{ asset('images/base-dashboard.svg') }}" alt="Dashboard preview">
    </section>

    <section class="section">
        <div class="grid-3">
            <article class="card">
                <h3>Website utama</h3>
                <p>Halaman publik sebagai pintu masuk awal sebelum user login dan diarahkan sesuai rolenya.</p>
            </article>
            <article class="card">
                <h3>Admin panel</h3>
                <p>Area kerja Super Admin dan Admin untuk mengelola akun, konfigurasi, dan fitur internal.</p>
            </article>
            <article class="card">
                <h3>Role access</h3>
                <p>Middleware role memisahkan akses Super Admin, Admin, dan User sejak awal.</p>
            </article>
        </div>
    </section>
</main>
@endsection