@extends('layouts.app', ['title' => 'Demo Addon'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        @include('admin.partials.topbar', [
            'breadcrumb' => 'Demo Addon',
            'title' => 'Demo Addon',
            'description' => 'Sample addon page loaded from the addons/Demo folder.',
        ])

        <section class="admin-panel">
            <div class="eyebrow">NovaBase addon</div>
            <h2>Demo Addon</h2>
            <p>This page comes from the <code>demo::index</code> view namespace. If this addon appears, routes, views, sidebar, dashboard cards, and base permissions are connected.</p>
            @if (auth()->user()?->isPlatformSuperAdmin())
                <a class="button button-primary" href="{{ route('admin.addons.demo.tenants.index') }}">Manage Demo Tenants</a>
            @endif
        </section>
    </main>
</div>
@endsection
