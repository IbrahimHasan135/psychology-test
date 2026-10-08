<div class="admin-topbar">
    <div class="topbar-breadcrumb">{{ $breadcrumb ?? 'Admin Panel' }}</div>
    <div class="topbar-search">
        <input class="topbar-search-input" type="text" placeholder="Search workspace...">
    </div>
    <div class="topbar-right">
        <span>Hello, <strong>{{ auth()->user()->name }}</strong></span>
        <button class="topbar-avatar" type="button" title="{{ \App\Enums\UserRole::label(auth()->user()->role) }}">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </button>
    </div>
</div>

@isset($description)
    <section class="admin-hero">
        <div class="eyebrow">{{ $breadcrumb ?? 'Admin Panel' }}</div>
        <h1>{{ $title ?? $breadcrumb ?? 'Dashboard' }}</h1>
        <p>{{ $description }}</p>
    </section>
@endisset
