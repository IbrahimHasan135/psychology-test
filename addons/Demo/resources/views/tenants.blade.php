@extends('layouts.app', ['title' => 'Tenant Management'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        @include('admin.partials.topbar', [
            'breadcrumb' => 'Demo Addon / Tenants',
            'title' => 'Tenant Management',
            'description' => 'Create and inspect tenant workspaces from the platform context.',
        ])

        <section class="admin-section">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            <div class="admin-card form-card">
                <h3>Create Tenant</h3>
                <form method="POST" action="{{ nova_route('admin.addons.demo.tenants.store') }}" class="admin-form-grid">
                    @csrf
                    <label>Tenant name<input name="tenant_name" value="{{ old('tenant_name') }}" required></label>
                    <label>Tenant slug<input name="tenant_slug" value="{{ old('tenant_slug') }}" placeholder="gbi" required></label>
                    <label>Owner name<input name="owner_name" value="{{ old('owner_name') }}" required></label>
                    <label>Owner username<input name="owner_username" value="{{ old('owner_username') }}" required></label>
                    <label>Owner email<input type="email" name="owner_email" value="{{ old('owner_email') }}" required></label>
                    <label>Owner password<input type="password" name="owner_password" minlength="8" required></label>
                    <label>Confirm password<input type="password" name="owner_password_confirmation" minlength="8" required></label>
                    <div><button class="button button-primary" type="submit">Create Tenant Account</button></div>
                </form>
            </div>
        </section>

        <section class="admin-section">
            <div class="admin-card">
                <h3>Registered Tenants</h3>
                <div class="table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Name</th><th>Slug</th><th>Owner account</th><th>Members</th><th>Status</th><th>Website</th></tr></thead>
                        <tbody>
                        @forelse ($tenants as $tenant)
                            <tr>
                                <td>{{ $tenant->name }}</td>
                                <td><code>{{ $tenant->slug }}</code></td>
                                <td>{{ $tenant->owner?->username ?? 'Not assigned' }}<br><small>{{ $tenant->owner?->email }}</small></td>
                                <td>{{ $tenant->memberships_count }}</td>
                                <td>{{ ucfirst($tenant->status) }}</td>
                                <td><a href="{{ url('/'.$tenant->slug) }}" target="_blank" rel="noreferrer">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No tenants registered yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $tenants->links() }}
            </div>
        </section>
    </main>
</div>
@endsection
