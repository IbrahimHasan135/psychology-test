@extends('layouts.app', ['title' => 'Login'])

@section('content')
<main class="auth-wrap">
    <section class="auth-card">
        <div class="eyebrow">Novalynk base access</div>
        <h2>Login</h2>
        <p>Sign in to NovaBase to manage accounts, pages, and product addons.</p>
        @if (session('status'))
            <div class="notice admin-status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ nova_route('login.store') }}">
            @csrf
            <label for="login">Username or Email</label>
            <input id="login" type="text" name="login" value="{{ old('login') }}" autocomplete="username" required autofocus>
            @error('login')
                <div class="field-error">{{ $message }}</div>
            @enderror

            <label for="password">Password</label>
            <div class="password-field">
                <input id="password" type="password" name="password" autocomplete="current-password" required>
                <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show password">Show</button>
            </div>
            @error('password')
                <div class="field-error">{{ $message }}</div>
            @enderror

            <label class="checkbox-row" for="remember">
                <input id="remember" type="checkbox" name="remember" value="1">
                <span>Remember this session</span>
            </label>

            <button class="button button-primary full-button" type="submit">Sign In</button>
        </form>
    </section>
</main>
<script>
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            button.textContent = isHidden ? 'Hide' : 'Show';
            button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });
    });
</script>
@endsection
