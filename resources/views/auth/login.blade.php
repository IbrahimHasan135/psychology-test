@extends('layouts.app', ['title' => 'Login'])

@section('content')
<main class="auth-wrap">
    <section class="auth-card">
        <div class="eyebrow">Novalynk base access</div>
        <h2>Login</h2>
        <p>Masuk ke admin panel NovaBase untuk mengatur akun, halaman, dan addon produk.</p>

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label for="login">Username atau Email</label>
            <input id="login" type="text" name="login" value="{{ old('login') }}" autocomplete="username" required autofocus>
            @error('login')
                <div class="field-error">{{ $message }}</div>
            @enderror

            <label for="password">Password</label>
            <div class="password-field">
                <input id="password" type="password" name="password" autocomplete="current-password" required>
                <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Lihat password">Lihat</button>
            </div>
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
<script>
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            button.textContent = isHidden ? 'Tutup' : 'Lihat';
            button.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Lihat password');
        });
    });
</script>
@endsection
