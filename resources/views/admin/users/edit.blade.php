@extends('layouts.app', ['title' => 'Edit Account'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        @include('admin.partials.topbar', [
            'breadcrumb' => 'Edit Account',
            'title' => 'Edit '.$editUser->name,
            'description' => 'Update account identity. Role changes are controlled from Role Management rules.',
        ])

        @if ($errors->any())
            <div class="notice error-notice admin-status">{{ $errors->first() }}</div>
        @endif

        <form class="admin-card role-edit-card" method="POST" action="{{ route('admin.users.update', $editUser) }}">
            @csrf
            @method('PUT')

            <a class="button button-soft" href="{{ route('admin.users.index') }}">Back</a>
            <p class="muted">Current role: <strong>{{ $editUser->role }}</strong></p>

            <label for="name">Full Name</label>
            <input id="name" name="name" required value="{{ old('name', $editUser->name) }}">

            <label for="username">Username</label>
            <input id="username" name="username" required value="{{ old('username', $editUser->username) }}">

            <label for="email">Email</label>
            <input id="email" name="email" type="email" required value="{{ old('email', $editUser->email) }}">

            <label for="password">New Password</label>
            <input id="password" name="password" type="password" placeholder="Leave empty to keep the current password">

            <button class="button button-primary" type="submit">Save Account</button>
        </form>
    </main>
</div>
@endsection
