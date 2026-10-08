@extends('layouts.app', ['title' => $page?->name ?? 'Home'])

@section('content')
<main class="website-page">
    @if (!empty($builderState))
        @php($novabaseAuth = auth()->check() ? [
            'authenticated' => true,
            'label' => auth()->user()->isAdminLike() ? 'Admin Panel' : 'User Portal',
            'url' => route(auth()->user()->dashboardRoute()),
        ] : [
            'authenticated' => false,
            'label' => 'Login',
            'url' => route('login'),
        ])
        <div id="publicSiteCanvas"></div>
        <script>
            window.NOVABASE_BUILDER_STATE = @json($builderState, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
            window.NOVABASE_DESIGN_TEMPLATES = @json($designTemplates, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
            window.NOVABASE_BASE_URL = @json(url('/'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
            window.NOVABASE_AUTH = @json($novabaseAuth, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        </script>
        <script type="module" src="{{ asset('js/page-builder/editor/public-renderer.js') }}"></script>
    @elseif ($page && $page->blocks->isNotEmpty())
        @if ($page->blocks->contains('nav_enabled', true))
            <nav class="{{ $page->display_mode === 'tabs' ? 'website-tabs' : 'nb-section-nav' }} page" aria-label="Website sections">
                @foreach ($page->blocks->where('nav_enabled', true) as $block)
                    <a href="#{{ $block->block_uid }}">{{ $block->nav_label ?: ($block->data_json['title'] ?? 'Section') }}</a>
                @endforeach
            </nav>
        @endif
        @foreach ($page->blocks as $block)
            <section class="nb-block nb-block-{{ $block->type }}" id="{{ $block->block_uid }}">
                @includeIf('website.blocks.'.$block->type, ['data' => $block->data_json])
            </section>
        @endforeach
    @elseif ($page && ! $page->builder_initialized && $page->activeSections->isNotEmpty())
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
                        <a class="button button-primary" href="{{ route('login') }}">Login</a>
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
