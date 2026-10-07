@extends('layouts.app', ['title' => 'Login'])

@section('content')
<main class="auth-wrap">
    <section class="auth-card">
        <div class="eyebrow">Account gateway</div>
        <h2>Login</h2>
        <p>Role akun menentukan apakah masuk ke admin panel atau dashboard user.</p>

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label for="login">Username atau Email</label>
            <input id="login" type="text" name="login" value="{{ old('login') }}" autocomplete="username" required autofocus>
            @error('login')
                <div class="field-error">{{ $message }}</div>
            @enderror

            <label for="password">Password</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>
            @error('password')
                <div class="field-error">{{ $message }}</div>
            @enderror

            <label class="checkbox-row" for="remember">
                <input id="remember" type="checkbox" name="remember" value="1">
                <span>Ingat sesi login</span>
            </label>

            <button class="button button-primary full-button" type="submit">Masuk</button>
        </form>
    </section>
</main>
@endsection