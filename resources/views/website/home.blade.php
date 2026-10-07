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
        <section class="blank-home page">
            @auth
                <a class="button button-soft" href="{{ route(auth()->user()->dashboardRoute()) }}">Dashboard</a>
            @else
                <a class="button button-primary" href="{{ route('login') }}">Login</a>
            @endauth
        </section>
    @endif
</main>
@endsection