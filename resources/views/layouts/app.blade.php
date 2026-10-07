<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Psychology Test') }}</title>
    <style>
        :root {
            --green-950: #052e24;
            --green-900: #064434;
            --green-800: #0c6048;
            --green-700: #0f7a55;
            --green-500: #27b36a;
            --green-300: #8fe3a7;
            --mint: #effbf2;
            --ink: #10231d;
            --muted: #5f746b;
            --line: #dbe9df;
            --white: #ffffff;
            --amber: #f7c948;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--ink);
            background: #f5fbf7;
            letter-spacing: 0;
        }
        a { color: inherit; text-decoration: none; }
        .site-shell { min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 0 clamp(20px, 5vw, 72px);
            border-bottom: 1px solid rgba(12, 96, 72, .12);
            background: rgba(245, 251, 247, .86);
            backdrop-filter: blur(18px);
            position: sticky;
            top: 0;
            z-index: 20;
        }
        .brand { display: flex; align-items: center; gap: 12px; font-weight: 800; color: var(--green-950); }
        .brand-mark { width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; background: var(--green-800); color: white; font-weight: 900; }
        .nav { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
        .nav a, .nav button, .button {
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 10px 14px;
            font-weight: 700;
            font-size: 14px;
            background: transparent;
            cursor: pointer;
        }
        .nav a:hover, .nav button:hover { background: var(--mint); border-color: var(--line); }
        .button-primary { background: var(--green-800); color: white; box-shadow: 0 12px 30px rgba(12, 96, 72, .2); }
        .button-primary:hover { background: var(--green-700); }
        .button-soft { background: #e7f7ec; color: var(--green-900); border-color: #cfe9d7; }
        .page { width: min(1180px, calc(100% - 40px)); margin: 0 auto; }
        .hero { display: grid; grid-template-columns: minmax(0, 1fr) minmax(320px, 500px); gap: clamp(28px, 5vw, 76px); align-items: center; padding: clamp(48px, 8vw, 94px) 0 42px; }
        .eyebrow { color: var(--green-700); font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0; }
        h1, h2, h3, p { margin-top: 0; }
        h1 { font-size: clamp(42px, 7vw, 76px); line-height: .98; margin-bottom: 24px; color: var(--green-950); letter-spacing: 0; }
        h2 { font-size: clamp(26px, 4vw, 40px); line-height: 1.08; margin-bottom: 14px; color: var(--green-950); letter-spacing: 0; }
        h3 { font-size: 18px; margin-bottom: 8px; color: var(--green-950); letter-spacing: 0; }
        p { color: var(--muted); line-height: 1.7; }
        .hero-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 28px; }
        .hero-art { width: 100%; border-radius: 26px; box-shadow: 0 34px 80px rgba(5, 46, 36, .22); border: 1px solid rgba(12, 96, 72, .12); }
        .section { padding: 28px 0 64px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .card { background: white; border: 1px solid var(--line); border-radius: 8px; padding: 22px; box-shadow: 0 16px 40px rgba(5, 46, 36, .06); }
        .metric { font-size: 34px; font-weight: 900; color: var(--green-800); margin-bottom: 4px; }
        .auth-wrap { min-height: calc(100vh - 72px); display: grid; place-items: center; padding: 42px 20px; }
        .auth-card { width: min(100%, 440px); background: white; border: 1px solid var(--line); border-radius: 8px; padding: 28px; box-shadow: 0 24px 70px rgba(5, 46, 36, .14); }
        label { display: block; font-size: 13px; font-weight: 800; margin: 18px 0 8px; color: var(--green-950); }
        input { width: 100%; border: 1px solid var(--line); border-radius: 8px; padding: 13px 14px; font: inherit; color: var(--ink); background: white; }
        input:focus { outline: 3px solid rgba(39, 179, 106, .18); border-color: var(--green-500); }
        .password-field { position: relative; }
        .password-field input { padding-right: 86px; }
        .password-toggle { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); border: 1px solid var(--line); border-radius: 8px; padding: 7px 10px; background: var(--mint); color: var(--green-900); font-weight: 800; cursor: pointer; }
        .password-toggle:hover { border-color: var(--green-500); }
        .field-error { color: #b42318; font-size: 13px; margin-top: 8px; }
        .checkbox-row { display: flex; align-items: center; gap: 10px; margin: 16px 0 22px; color: var(--muted); font-size: 14px; }
        .checkbox-row input { width: 16px; height: 16px; }
        .app-layout { display: grid; grid-template-columns: 250px minmax(0, 1fr); min-height: calc(100vh - 72px); }
        .sidebar { border-right: 1px solid var(--line); background: #ffffff; padding: 24px; }
        .sidebar a { display: block; padding: 11px 12px; border-radius: 8px; color: var(--muted); font-weight: 700; margin-bottom: 6px; }
        .sidebar a.active, .sidebar a:hover { background: var(--mint); color: var(--green-900); }
        .content { padding: clamp(22px, 4vw, 42px); min-width: 0; }
        .table-wrap { overflow-x: auto; background: white; border: 1px solid var(--line); border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; min-width: 720px; }
        th, td { text-align: left; padding: 15px 18px; border-bottom: 1px solid var(--line); font-size: 14px; }
        th { color: var(--green-950); background: #f4fbf6; font-size: 12px; text-transform: uppercase; letter-spacing: 0; }
        tr:last-child td { border-bottom: 0; }
        .pill { display: inline-flex; align-items: center; border-radius: 999px; padding: 6px 10px; background: #e7f7ec; color: var(--green-900); font-weight: 800; font-size: 12px; }
        .muted { color: var(--muted); }
        .stack { display: grid; gap: 16px; }
        .split { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
        .full-button { width: 100%; }
        @media (max-width: 860px) {
            .hero { grid-template-columns: 1fr; padding-top: 38px; }
            .grid-3 { grid-template-columns: 1fr; }
            .app-layout { grid-template-columns: 1fr; }
            .sidebar { border-right: 0; border-bottom: 1px solid var(--line); }
            .topbar { height: auto; min-height: 72px; padding-top: 14px; padding-bottom: 14px; align-items: flex-start; }
        }
    </style>
</head>
<body>
<div class="site-shell">
    <header class="topbar">
        <a class="brand" href="{{ route('home') }}">
            <span class="brand-mark">PT</span>
            <span>{{ config('app.name', 'Psychology Test') }}</span>
        </a>
        <nav class="nav">
            <a href="{{ route('home') }}">Website</a>
            @auth
                <a href="{{ route(auth()->user()->dashboardRoute()) }}">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            @else
                <a class="button-primary" href="{{ route('login') }}">Login</a>
            @endauth
        </nav>
    </header>
    {{ $slot ?? '' }}
    @yield('content')
</div>
</body>
</html>