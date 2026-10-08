@extends('layouts.app', ['title' => 'Admin Dashboard'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        @include('admin.partials.topbar', [
            'breadcrumb' => 'Admin Dashboard',
            'description' => 'Ringkasan core NovaBase dan addon yang aktif untuk role Anda.',
        ])

        <div class="split">
            <div>
                <h2>Core Overview</h2>
                <p>Statistik akun dan role bawaan NovaBase.</p>
            </div>
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

        @forelse ($addonCards as $addonSlug => $cards)
            @php($addon = $cards->first()['addon'])
            <section class="addon-dashboard-section">
                <div class="split">
                    <div>
                        <div class="eyebrow">Addon</div>
                        <h2>{{ $addon->name }}</h2>
                        <p>{{ $addon->description }}</p>
                    </div>
                </div>
                <div class="grid-3">
                    @foreach ($cards as $card)
                        <article class="card addon-dashboard-card">
                            <div class="card-kicker">{{ $card['icon'] ?? 'box' }}</div>
                            <h3>{{ $card['title'] }}</h3>
                            {!! $card['content'] ?? '' !!}
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <section class="empty-state addon-empty-state">
                Belum ada dashboard card addon untuk role ini.
            </section>
        @endforelse
    </main>
</div>
@endsection
