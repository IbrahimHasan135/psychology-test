<aside class="sidebar">
    <div class="stack">
        <div>
            <strong>{{ auth()->user()->name }}</strong>
            <div class="muted">{{ auth()->user()->email }}</div>
        </div>
        <nav>
            <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Akun & Role</a>
        </nav>
    </div>
</aside>