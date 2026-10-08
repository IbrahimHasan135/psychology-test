@extends('layouts.app', ['title' => $page?->name ?? 'Home'])

@section('content')
<main class="website-page">
    @if ($page && $page->activeSections->isNotEmpty())
        @if ($page->display_mode === 'tabs')
            <nav class="website-tabs page" aria-label="Website tabs">
                @foreach ($page->activeSections as $section)
                    <a href="#{{ $section->anchor ?: 'section-'.$section->id }}">{{ $section->title }}</a>
                @endforeach
            </nav>
        @endif

        @foreach ($page->activeSections as $section)
            <section class="website-section" id="{{ $section->anchor ?: 'section-'.$section->id }}">
                <div class="page">
                    <div class="section-heading">
                        <h2>{{ $section->title }}</h2>
                        @if ($section->description)
                            <p>{{ $section->description }}</p>
                        @endif
                    </div>
                    <div class="website-card-grid">
                        @foreach ($section->activeCards as $card)
                            @includeIf('website.card-templates.'.$card->template, ['card' => $card])
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach
    @else
        <section class="landing-hero page">
            <div>
                <div class="eyebrow">Novalynk Modular Base</div>
                <h1>NovaBase</h1>
                <p>A green Laravel foundation for modular products, admin panels, landing pages, addon features, roles, and future client deployments.</p>
                <div class="hero-actions">
                    @auth
                        <a class="button button-primary" href="{{ route(auth()->user()->dashboardRoute()) }}">{{ auth()->user()->isAdminLike() ? 'Admin Panel' : 'User Portal' }}</a>
                    @else
                        <a class="button button-primary" href="{{ route('login') }}">Admin Panel</a>
                    @endauth
                </div>
            </div>
            <div class="landing-panel">
                <span>Core</span>
                <strong>Auth, roles, pages, addons</strong>
                <p>Start with a clean base, then mount product features from the addon folder.</p>
            </div>
        </section>
    @endif
</main>
@endsection
