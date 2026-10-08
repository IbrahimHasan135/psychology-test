@extends('layouts.app', ['title' => 'Demo Addon'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        @include('admin.partials.topbar', [
            'breadcrumb' => 'Demo Addon',
            'description' => 'Contoh halaman addon yang dimuat dari folder addons/Demo.',
        ])

        <section class="admin-panel">
            <div class="eyebrow">NovaBase addon</div>
            <h2>Demo Addon</h2>
            <p>Halaman ini berasal dari namespace view <code>demo::index</code>. Kalau addon ini muncul, berarti route, view, sidebar, dashboard card, dan permission dasar sudah tersambung.</p>
        </section>
    </main>
</div>
@endsection
