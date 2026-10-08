@php
    $addonMenu = app(\App\Core\Addons\AddonRegistry::class)->adminMenuFor(auth()->user());
@endphp

<button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Toggle menu">☰</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="sidebar-brand-mark">NB</span>
        <div>
            <span class="sidebar-brand-name">NovaBase</span>
            <span class="sidebar-brand-tag">Modular Core</span>
        </div>
    </div>

    <div class="sidebar-search">
        <input class="sidebar-search-input" id="sidebarSearch" type="text" placeholder="Search menu...">
    </div>

    <nav class="sidebar-nav" id="sidebarNav">
            <div class="sidebar-group-label">Admin Panel</div>
            <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span>Dashboard</span></a>
            <a href="{{ route('home') }}"><span>Back To Website</span></a>
            @if (auth()->user()?->canManageRoles())
                <a class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}"><span>Role Management</span></a>
            @endif
            @if (auth()->user()?->canManageUsers())
                <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><span>User Management</span></a>
            @endif
            <a class="{{ request()->routeIs('admin.pages.*') || request()->routeIs('admin.sections.*') || request()->routeIs('admin.cards.*') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}"><span>Page Management</span></a>

            @if ($addonMenu->isNotEmpty())
                <div class="sidebar-group-label">Addons</div>
                @foreach ($addonMenu as $group)
                    <details class="sidebar-addon" open>
                        <summary>{{ $group['addon']->name }}</summary>
                        @foreach ($group['items'] as $item)
                            <a class="{{ request()->routeIs($item['route']) ? 'active' : '' }}" href="{{ route($item['route']) }}"><span>{{ $item['label'] }}</span></a>
                        @endforeach
                    </details>
                @endforeach
            @endif
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>
</aside>

<script>
    (() => {
        const toggle = document.getElementById('sidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');
        const search = document.getElementById('sidebarSearch');

        toggle?.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
        overlay?.addEventListener('click', () => document.body.classList.remove('sidebar-open'));

        search?.addEventListener('input', () => {
            const query = search.value.toLowerCase();
            document.querySelectorAll('#sidebarNav a').forEach((link) => {
                const label = link.textContent.toLowerCase();
                link.style.display = label.includes(query) ? '' : 'none';
            });
        });
    })();
</script>
