<div class="admin-topbar">
    <div>
        <div class="eyebrow">{{ $breadcrumb ?? 'Admin Panel' }}</div>
        @isset($description)
            <p>{{ $description }}</p>
        @endisset
    </div>
    <div class="topbar-account">
        <span class="pill">{{ \App\Enums\UserRole::label(auth()->user()->role) }}</span>
        <strong>{{ auth()->user()->name }}</strong>
    </div>
</div>
