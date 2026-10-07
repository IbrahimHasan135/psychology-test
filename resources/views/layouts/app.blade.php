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
        input, select, textarea { width: 100%; border: 1px solid var(--line); border-radius: 8px; padding: 13px 14px; font: inherit; color: var(--ink); background: white; }
        textarea { resize: vertical; }
        input:focus, select:focus, textarea:focus { outline: 3px solid rgba(39, 179, 106, .18); border-color: var(--green-500); }
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
        .notice { margin-bottom: 18px; padding: 12px 14px; border: 1px solid #bfeccb; border-radius: 8px; background: #effbf2; color: var(--green-900); font-weight: 700; }
        .admin-panel { background: white; border: 1px solid var(--line); border-radius: 8px; padding: 22px; margin-bottom: 20px; box-shadow: 0 16px 40px rgba(5, 46, 36, .05); }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; align-items: end; }
        .form-wide { grid-column: 1 / -1; }
        .form-actions { display: flex; align-items: center; gap: 10px; justify-content: flex-end; }
        .compact-check { margin: 0; }
        .compact-split { margin-bottom: 12px; }
        .builder-list { display: grid; gap: 14px; margin: 16px 0; }
        .builder-card { border: 1px solid var(--line); border-radius: 8px; padding: 16px; background: #fbfefc; }
        .new-card { background: #f4fbf6; }
        .delete-card-form { display: flex; justify-content: flex-end; margin-top: 10px; }
        .danger-button { background: #fff1f0; color: #b42318; border-color: #ffd2cc; }
        .empty-state { padding: 28px; border: 1px dashed var(--line); border-radius: 8px; color: var(--muted); background: white; }
        .website-page { min-height: calc(100vh - 72px); background: #f8fcf9; }
        .blank-home { min-height: calc(100vh - 72px); display: grid; place-items: center; }
        .website-tabs { position: sticky; top: 72px; z-index: 10; display: flex; gap: 10px; flex-wrap: wrap; padding: 14px 0; background: rgba(248, 252, 249, .92); backdrop-filter: blur(12px); }
        .website-tabs a { border: 1px solid var(--line); border-radius: 8px; padding: 9px 12px; background: white; color: var(--green-900); font-weight: 800; }
        .website-section { padding: 56px 0; scroll-margin-top: 110px; }
        .section-heading { max-width: 720px; margin-bottom: 22px; }
        .website-card-grid { display: grid; gap: 16px; }
        .site-card { border: 1px solid var(--line); border-radius: 8px; background: white; padding: 22px; box-shadow: 0 16px 40px rgba(5, 46, 36, .06); }
        .site-card img { width: 100%; border-radius: 8px; object-fit: cover; max-height: 320px; background: var(--mint); }
        .site-card-feature, .site-card-media, .site-card-hero { display: grid; grid-template-columns: minmax(220px, .8fr) minmax(0, 1.2fr); gap: 22px; align-items: center; }
        .site-card-feature.image-right, .site-card-media.image-right, .site-card-hero.image-right { grid-template-columns: minmax(0, 1.2fr) minmax(220px, .8fr); }
        .site-card-feature.image-right img, .site-card-media.image-right img { order: 2; }
        .site-card-hero.image-left img { order: -1; }
        .site-card-feature.image-top, .site-card-media.image-top, .site-card-hero.image-top { grid-template-columns: 1fr; }
        .site-card-compact { max-width: 760px; }
        .site-card-hero { background: #063f31; color: white; padding: 30px; }
        .site-card-hero h3, .site-card-hero p { color: white; }
        .site-card-stat strong { display: block; color: var(--green-800); font-size: clamp(30px, 6vw, 64px); line-height: 1; margin-bottom: 10px; }
        .site-card-quote { border-left: 6px solid var(--green-500); }
        .site-card-quote p { color: var(--green-950); font-size: 22px; line-height: 1.45; }
        .site-card-cta { display: flex; align-items: center; justify-content: space-between; gap: 18px; background: #effbf2; }
        .page-studio { background: #f7fbf8; }
        .studio-topbar { display: flex; justify-content: space-between; gap: 18px; align-items: flex-start; margin-bottom: 18px; }
        .studio-topbar h2 { margin-bottom: 8px; }
        .studio-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
        .error-notice { border-color: #ffd2cc; background: #fff1f0; color: #b42318; }
        .studio-board { display: grid; grid-template-columns: 320px minmax(0, 1fr); gap: 18px; align-items: start; }
        .studio-settings { position: sticky; top: 94px; background: white; border: 1px solid var(--line); border-radius: 8px; padding: 18px; box-shadow: 0 12px 34px rgba(5, 46, 36, .06); }
        .studio-settings h3 { font-size: 16px; margin-bottom: 12px; }
        .studio-form { display: grid; gap: 10px; }
        .studio-form label { margin: 0; }
        .studio-divider { height: 1px; background: var(--line); margin: 18px 0; }
        .studio-canvas { display: grid; gap: 18px; }
        .studio-section { background: white; border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 14px 36px rgba(5, 46, 36, .06); overflow: hidden; }
        .studio-section-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; padding: 18px 20px; border-bottom: 1px solid var(--line); background: #fbfefc; }
        .studio-section-head h3 { margin: 4px 0 4px; }
        .studio-section-head p { margin: 0; font-size: 13px; }
        .section-index { display: inline-flex; color: var(--green-700); font-size: 12px; font-weight: 900; }
        .section-quick-edit { display: grid; grid-template-columns: minmax(160px, 1.2fr) minmax(120px, .8fr) 90px 110px; gap: 10px; padding: 16px 20px; border-bottom: 1px solid var(--line); align-items: center; }
        .section-quick-edit textarea { grid-column: 1 / -2; }
        .section-quick-edit button { align-self: stretch; }
        .card-stack { display: grid; gap: 12px; padding: 18px 20px 0; }
        .visual-card-editor { border: 1px solid var(--line); border-radius: 8px; background: white; overflow: hidden; }
        .visual-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--line); background: #fbfefc; }
        .visual-card-head h4 { margin: 4px 0; color: var(--green-950); }
        .visual-card-head p { margin: 0; font-size: 13px; }
        .card-type { color: var(--green-700); font-size: 12px; font-weight: 900; text-transform: uppercase; }
        .card-delete-hint { color: #b42318; font-size: 12px; font-weight: 900; text-transform: uppercase; white-space: nowrap; }
        .card-live-preview { padding: 16px; background: #f7fbf8; }
        .card-live-preview .site-card { box-shadow: none; }
        .always-delete-card { display: flex; justify-content: flex-end; padding: 0 16px 16px; background: #f7fbf8; }
        .delete-card-button { font-weight: 900; border-color: #ffb4a8; }
        .card-edit-panel { border-top: 1px solid var(--line); }
        .card-edit-panel summary { padding: 12px 16px; cursor: pointer; color: var(--green-900); font-weight: 900; background: white; }
        .studio-card { border: 1px solid var(--line); border-radius: 8px; background: white; overflow: hidden; }
        .studio-card summary { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px 16px; cursor: pointer; background: #f7fbf8; }
        .studio-card summary strong { display: block; color: var(--green-950); }
        .studio-card summary small { display: block; color: var(--muted); margin-top: 3px; }
        .card-editor-form { padding: 16px; border-top: 1px solid var(--line); }
        .card-actions { display: flex; justify-content: flex-end; margin-top: 12px; }
        .card-delete-form { display: flex; justify-content: flex-end; padding: 0 16px 16px; }
        .new-studio-card { margin: 18px 20px 20px; border-style: dashed; background: #fbfefc; }
        .new-studio-card summary { background: #effbf2; }
        @media (max-width: 860px) {
            .hero { grid-template-columns: 1fr; padding-top: 38px; }
            .grid-3 { grid-template-columns: 1fr; }
            .app-layout { grid-template-columns: 1fr; }
            .sidebar { border-right: 0; border-bottom: 1px solid var(--line); }
            .topbar { height: auto; min-height: 72px; padding-top: 14px; padding-bottom: 14px; align-items: flex-start; }
            .form-grid { grid-template-columns: 1fr; }
            .studio-board { grid-template-columns: 1fr; }
            .studio-settings { position: static; }
            .studio-topbar { display: block; }
            .studio-actions { justify-content: flex-start; margin-top: 12px; }
            .section-quick-edit { grid-template-columns: 1fr; }
            .section-quick-edit textarea { grid-column: auto; }
            .site-card-feature, .site-card-media, .site-card-hero, .site-card-feature.image-right, .site-card-media.image-right, .site-card-hero.image-right { grid-template-columns: 1fr; }
            .site-card-feature.image-right img, .site-card-media.image-right img, .site-card-hero.image-left img { order: 0; }
            .site-card-cta { display: grid; }
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
