@extends('layouts.app', ['title' => 'Admin Dashboard'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        @include('admin.partials.topbar', [
            'breadcrumb' => 'Admin Dashboard',
            'title' => 'Welcome, '.auth()->user()->name,
            'description' => 'Overview of active modules available for your role.',
        ])

        @forelse ($addonCards as $addonSlug => $cards)
            @php($addon = $cards->first()['addon'])
            <section class="admin-section addon-dashboard-section">
                <div class="module-section-header">
                    <div class="module-section-title">
                        <span class="module-section-icon">{{ strtoupper(substr($addon->name, 0, 1)) }}</span>
                        <span>{{ $addon->name }}</span>
                    </div>
                </div>
                <div class="dash-cards-grid">
                    @foreach ($cards as $card)
                        <article class="card dash-card addon-dashboard-card" data-card-id="{{ $card['id'] }}">
                            <div class="dash-card-hdr">
                                <span class="dash-card-hdr-title">{{ $card['title'] }}</span>
                                <span class="badge-module">{{ $addon->name }}</span>
                            </div>
                            <div class="dash-card-body">
                                {!! $card['content'] ?? '' !!}
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <section class="empty-state admin-section addon-empty-state">
                No addon dashboard cards are available for this role.
            </section>
        @endforelse
    </main>
</div>
@endsection
