@php
    $addonMenu = app(\App\Core\Addons\AddonRegistry::class)->adminMenuFor(auth()->user());
@endphp

<aside class="sidebar">
    <div class="stack">
        <div>
            <strong>{{ auth()->user()->name }}</strong>
            <div class="muted">{{ auth()->user()->email }}</div>
        </div>
        <nav>
            <div class="sidebar-group-label">Admin Panel</div>
            <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Akun & Role</a>
            <a class="{{ request()->routeIs('admin.pages.*') || request()->routeIs('admin.sections.*') || request()->routeIs('admin.cards.*') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}">Page Management</a>

            @if ($addonMenu->isNotEmpty())
                <div class="sidebar-group-label">Addons</div>
                @foreach ($addonMenu as $group)
                    <details class="sidebar-addon" open>
                        <summary>{{ $group['addon']->name }}</summary>
                        @foreach ($group['items'] as $item)
                            <a class="{{ request()->routeIs($item['route']) ? 'active' : '' }}" href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                        @endforeach
                    </details>
                @endforeach
            @endif
        </nav>
    </div>
</aside>
