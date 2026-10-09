<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'NovaBase') }}</title>
    <style>
        :root {
            --green-950: #04251d;
            --green-900: #063b2f;
            --green-800: #07553f;
            --green-700: #08724f;
            --green-500: #16a05d;
            --green-300: #89e2a1;
            --mint: #edf9f1;
            --ink: #1f2937;
            --muted: #6b7280;
            --line: #e6ece8;
            --white: #ffffff;
            --amber: #f7c948;
            --nv-font: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --nv-page-bg: #f6f8f7;
            --nv-card-bg: rgba(255, 255, 255, .92);
            --nv-primary: #08724f;
            --nv-accent: #16a05d;
            --nv-sidebar-bg: linear-gradient(180deg, #052e24 0%, #063b2f 48%, #07553f 100%);
            --nv-radius: 18px;
            --nv-card-radius: 20px;
            --nv-shadow: 0 18px 46px rgba(5, 46, 36, .10);
            --sb-w: 286px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: var(--nv-font);
            color: var(--ink);
            background: var(--nv-page-bg);
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
            background: rgba(246, 251, 247, .92);
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
        .form-feedback { margin-top: 18px; padding: 13px 16px; border-radius: 8px; font-size: 14px; font-weight: 700; }
        .form-feedback-error { color: #8b1e1e; background: #fff0f0; border: 1px solid #f1c2c2; }
        .form-feedback-success { color: var(--green-900); background: var(--mint); border: 1px solid #cfe9d7; }
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
        .admin-mode .site-shell > .topbar { display: none; }
        .admin-mode .app-layout { display: block; min-height: 100vh; }
        .sidebar-toggle { position: fixed; top: 14px; left: 14px; z-index: 1060; width: 40px; height: 40px; display: none; align-items: center; justify-content: center; border: 0; border-radius: 10px; background: var(--green-800); color: white; font-weight: 900; box-shadow: 0 14px 30px rgba(5, 46, 36, .28); cursor: pointer; }
        .sidebar-overlay { display: none; position: fixed; inset: 0; z-index: 1040; background: rgba(4, 37, 29, .58); backdrop-filter: blur(6px); }
        body.sidebar-open .sidebar-overlay { display: block; }
        .sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: var(--sb-w); z-index: 1050; display: flex; flex-direction: column; background: var(--nv-sidebar-bg); box-shadow: 16px 0 46px rgba(5, 46, 36, .25); border-right: 1px solid rgba(237, 249, 241, .18); overflow: hidden; }
        .sidebar::before { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px), linear-gradient(180deg, rgba(255,255,255,.035) 1px, transparent 1px); background-size: 22px 22px; mask-image: linear-gradient(180deg, rgba(0,0,0,.75), transparent 82%); pointer-events: none; }
        .sidebar > * { position: relative; z-index: 1; }
        .sidebar-brand { display: flex; align-items: center; gap: 12px; padding: 24px 20px 18px; background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,0)); }
        .sidebar-brand-mark { width: 44px; height: 44px; display: grid; place-items: center; border-radius: 13px; background: linear-gradient(135deg, rgba(22,160,93,.95), rgba(137,226,161,.55)); color: white; font-weight: 900; box-shadow: 0 14px 34px rgba(22,160,93,.24); border: 1px solid rgba(237,249,241,.42); }
        .sidebar-brand-name { display: block; color: white; font-size: 18px; font-weight: 900; line-height: 1.2; }
        .sidebar-brand-tag { color: rgba(237,249,241,.72); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }
        .sidebar-search { padding: 0 16px 14px; border-bottom: 1px solid rgba(237,249,241,.14); }
        .sidebar-search-input { width: 100%; padding: 9px 12px; border-radius: 12px; border: 1px solid rgba(237,249,241,.18); background: rgba(255,255,255,.08); color: #edf9f1; font: 12.5px var(--nv-font); outline: none; }
        .sidebar-search-input::placeholder { color: rgba(237,249,241,.46); }
        .sidebar-search-input:focus { border-color: rgba(137,226,161,.58); background: rgba(255,255,255,.12); }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 8px 0; }
        .sidebar-group-label { margin: 0; padding: 16px 20px 6px; color: rgba(237,249,241,.62); font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; }
        .sidebar a, .sidebar-addon summary { position: relative; display: flex; align-items: center; gap: 10px; margin: 2px 12px; padding: 10px 14px; border: 1px solid transparent; border-radius: 12px; color: rgba(237,249,241,.82); font-size: 13.5px; font-weight: 650; text-decoration: none; cursor: pointer; transition: background .15s, color .15s, border-color .15s, transform .15s; }
        .sidebar a:hover, .sidebar-addon summary:hover { color: white; background: rgba(255,255,255,.095); border-color: rgba(237,249,241,.14); transform: translateX(2px); }
        .sidebar a.active { color: white; background: linear-gradient(135deg, rgba(22,160,93,.38), rgba(137,226,161,.18)); border-color: rgba(137,226,161,.34); font-weight: 800; box-shadow: inset 0 1px 0 rgba(255,255,255,.12), 0 10px 28px rgba(4,37,29,.18); }
        .sidebar a.active::before { content: ""; position: absolute; left: -12px; top: 9px; bottom: 9px; width: 3px; background: #89e2a1; border-radius: 99px; }
        .sidebar-addon { margin-bottom: 2px; }
        .sidebar-addon a { margin-left: 24px; font-size: 13px; }
        .sidebar-footer { padding: 12px 14px 16px; border-top: 1px solid rgba(237,249,241,.14); }
        .sidebar-footer button { width: 100%; display: flex; align-items: center; gap: 10px; padding: 11px 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,.14); background: rgba(255,255,255,.08); color: #ffd0d0; font: 700 13px var(--nv-font); cursor: pointer; }
        .sidebar-footer button:hover { background: rgba(180,35,24,.24); color: white; }
        .content { margin-left: var(--sb-w); min-width: 0; min-height: 100vh; padding: 0 0 34px; background: linear-gradient(90deg, rgba(8,114,79,.055) 1px, transparent 1px), linear-gradient(180deg, rgba(8,114,79,.045) 1px, transparent 1px), radial-gradient(circle at 78% 8%, rgba(22,160,93,.18), transparent 34%), var(--nv-page-bg); background-size: 34px 34px, 34px 34px, auto, auto; }
        .admin-topbar { position: sticky; top: 0; z-index: 100; display: flex; align-items: center; gap: 16px; padding: 12px 28px; min-height: 64px; background: rgba(246,248,247,.78); backdrop-filter: blur(18px); box-shadow: 0 18px 42px rgba(5,46,36,.10); border-bottom: 1px solid rgba(8,114,79,.12); }
        .topbar-breadcrumb { color: #60766d; font-size: 12.5px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .topbar-search { flex: 1; max-width: 420px; }
        .topbar-search-input { width: 100%; padding: 9px 14px; border-radius: 12px; border: 1px solid rgba(8,114,79,.14); background: rgba(255,255,255,.72); color: var(--ink); font: 13px var(--nv-font); outline: none; }
        .topbar-search-input:focus { border-color: rgba(22,160,93,.46); background: white; box-shadow: 0 0 0 4px rgba(22,160,93,.10); }
        .admin-web-link { display: inline-flex; align-items: center; justify-content: center; gap: 7px; flex: 0 0 auto; min-height: 38px; padding: 0 13px; border: 1px solid rgba(8,114,79,.16); border-radius: 10px; background: #edf9f1; color: var(--green-900); font-size: 12px; font-weight: 900; white-space: nowrap; }
        .admin-web-link:hover { border-color: rgba(8,114,79,.34); background: #dff3e5; color: var(--green-950); }
        .topbar-right { display: flex; align-items: center; gap: 10px; margin-left: auto; color: #60766d; font-size: 13px; }
        .topbar-avatar { width: 34px; height: 34px; border: 0; border-radius: 10px; display: grid; place-items: center; background: linear-gradient(135deg, var(--green-800), var(--green-500)); color: white; font-weight: 900; cursor: pointer; box-shadow: 0 10px 24px rgba(22,160,93,.22); }
        .admin-hero { margin: 22px 28px 0; padding: 24px 26px; border: 1px solid rgba(8,114,79,.14); border-radius: var(--nv-card-radius); background: linear-gradient(135deg, rgba(255,255,255,.96), rgba(237,249,241,.78)), linear-gradient(90deg, rgba(8,114,79,.08) 1px, transparent 1px), linear-gradient(180deg, rgba(22,160,93,.07) 1px, transparent 1px); background-size: auto, 28px 28px, 28px 28px; box-shadow: var(--nv-shadow); position: relative; overflow: hidden; }
        .admin-hero::after { content: ""; position: absolute; left: 0; right: 0; top: 0; height: 3px; background: linear-gradient(90deg, var(--green-800), var(--green-500), var(--amber)); }
        .admin-hero h1 { margin: 0 0 4px; color: var(--ink); font-size: 28px; font-weight: 900; }
        .admin-hero p { margin: 0; font-size: 13.5px; color: var(--muted); }
        .admin-section { padding: 24px 28px; }
        .module-section-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
        .module-section-title { display: flex; align-items: center; gap: 10px; font-size: 15px; font-weight: 900; color: var(--ink); }
        .module-section-icon { width: 30px; height: 30px; border-radius: 10px; display: grid; place-items: center; color: var(--green-700); background: linear-gradient(135deg, #dbeee3, #edf9f1); }
        .dash-cards-grid { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 16px; }
        .dash-card { grid-column: span 6; padding: 0; overflow: hidden; }
        .dash-card-hdr { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 16px 20px 12px; border-bottom: 1px solid rgba(8,114,79,.10); background: linear-gradient(135deg, rgba(247,251,248,.96), rgba(237,249,241,.78)); }
        .dash-card-hdr-title { color: var(--ink); font-weight: 900; font-size: 14px; }
        .badge-module { background: linear-gradient(135deg, #dbeee3, #edf9f1); color: var(--green-800); font-size: 10px; font-weight: 900; border-radius: 20px; padding: 4px 10px; }
        .dash-card-body { padding: 18px 20px 20px; }
        .table-wrap { overflow-x: auto; background: white; border: 1px solid var(--line); border-radius: 8px; }
        .content > .table-wrap { margin: 22px 28px; box-shadow: var(--nv-shadow); border-color: rgba(8,114,79,.14); border-radius: var(--nv-card-radius); }
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
        .admin-panel { background: var(--nv-card-bg); border: 1px solid rgba(8,114,79,.14); border-radius: var(--nv-card-radius); padding: 22px; margin: 22px 28px; box-shadow: var(--nv-shadow); }
        .admin-status { margin: 18px 28px 0; }
        .admin-grid { display: grid; grid-template-columns: minmax(280px, 380px) minmax(0, 1fr); gap: 18px; align-items: start; margin: 22px 28px; }
        .admin-card { background: var(--nv-card-bg); border: 1px solid rgba(8,114,79,.14); border-radius: var(--nv-card-radius); padding: 22px; box-shadow: var(--nv-shadow); }
        .admin-card h2 { margin-bottom: 16px; }
        .admin-card h3 { margin: 18px 0 10px; }
        .admin-card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 18px; }
        .admin-card-header h2 { margin: 0 0 4px; }
        .admin-card-header p { margin: 0; color: var(--muted); font-size: 13px; line-height: 1.55; }
        .card-kicker { display: block; margin-bottom: 6px; color: var(--green-700); font-size: 11px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; }
        .form-card { display: flex; flex-direction: column; gap: 0; }
        .form-card label:not(.checkbox-row) { margin-top: 12px; }
        .form-card .button { margin-top: 18px; }
        .wide-card { min-width: 0; }
        .role-edit-card { margin: 22px 28px; max-width: 820px; display: grid; gap: 12px; }
        .embedded-table { border-radius: 14px; box-shadow: none; }
        .admin-table { min-width: 760px; }
        .admin-table th { white-space: nowrap; }
        .admin-table td { vertical-align: top; }
        .admin-table td:first-child { min-width: 150px; }
        .admin-table td:last-child { width: 1%; white-space: nowrap; }
        .inline-form { display: inline-flex; margin: 0; }
        .action-group { display: inline-flex; align-items: center; gap: 7px; flex-wrap: wrap; }
        .button-sm { padding: 8px 11px; font-size: 12px; line-height: 1.2; }
        .summary-text { display: flex; flex-wrap: wrap; gap: 5px; max-width: 270px; }
        .summary-chip { display: inline-flex; align-items: center; max-width: 100%; padding: 4px 8px; border-radius: 7px; background: #f0f8f2; color: var(--green-900); font-size: 11px; font-weight: 700; line-height: 1.3; overflow-wrap: anywhere; }
        .summary-muted { color: var(--muted); font-size: 12px; }
        .check-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(155px, 1fr)); gap: 8px; }
        .check-grid label { display: flex; align-items: center; gap: 8px; margin: 0; padding: 9px 11px; border: 1px solid var(--line); border-radius: 10px; background: #f7fbf8; color: var(--ink); font-size: 13px; text-transform: none; }
        .check-grid input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--green-700); }
        .compact-permission-card { margin: 10px 0; }
        .permission-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .permission-card { border: 1px solid var(--line); border-radius: 8px; padding: 16px; background: #fbfefc; }
        .permission-row { display: flex; justify-content: space-between; gap: 14px; padding: 10px 0; border-top: 1px solid var(--line); }
        .permission-row:first-of-type { margin-top: 10px; }
        .permission-row small { color: var(--muted); text-align: right; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; align-items: end; }
        .form-wide { grid-column: 1 / -1; }
        .form-actions { display: flex; align-items: center; gap: 10px; justify-content: flex-end; }
        .compact-check { margin: 0; }
        .compact-split { margin-bottom: 12px; }
        .permission-overview { margin-top: 22px; }
        .builder-list { display: grid; gap: 14px; margin: 16px 0; }
        .builder-card { border: 1px solid var(--line); border-radius: 8px; padding: 16px; background: #fbfefc; }
        .new-card { background: #f4fbf6; }
        .delete-card-form { display: flex; justify-content: flex-end; margin-top: 10px; }
        .danger-button { background: #fff1f0; color: #b42318; border-color: #ffd2cc; }
        .empty-state { padding: 28px; border: 1px dashed var(--line); border-radius: 8px; color: var(--muted); background: white; }
        .website-page { min-height: calc(100vh - 72px); background: #f8fcf9; }
        .blank-home { min-height: calc(100vh - 72px); display: grid; place-items: center; }
        .landing-hero { min-height: calc(100vh - 72px); display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(280px, .85fr); gap: clamp(28px, 6vw, 80px); align-items: center; padding: clamp(46px, 8vw, 92px) 0; }
        .landing-hero h1 { margin-bottom: 18px; }
        .landing-hero p { max-width: 660px; font-size: 18px; }
        .landing-panel { min-height: 300px; display: grid; align-content: end; gap: 10px; padding: 28px; border-radius: 24px; border: 1px solid rgba(8,114,79,.16); background: linear-gradient(135deg, rgba(255,255,255,.96), rgba(237,249,241,.8)), linear-gradient(90deg, rgba(8,114,79,.08) 1px, transparent 1px), linear-gradient(180deg, rgba(22,160,93,.07) 1px, transparent 1px); background-size: auto, 28px 28px, 28px 28px; box-shadow: 0 34px 80px rgba(5, 46, 36, .16); }
        .landing-panel span { color: var(--green-700); font-size: 12px; font-weight: 900; text-transform: uppercase; }
        .landing-panel strong { color: var(--green-950); font-size: 28px; line-height: 1.1; }
        .landing-panel p { font-size: 14px; margin: 0; }
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
        .studio-topbar { display: flex; justify-content: space-between; gap: 18px; align-items: flex-start; margin: 22px 28px 18px; padding: 24px 26px; border: 1px solid rgba(8,114,79,.14); border-radius: var(--nv-card-radius); background: var(--nv-card-bg); box-shadow: var(--nv-shadow); }
        .studio-topbar h2 { margin-bottom: 8px; }
        .studio-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
        .error-notice { border-color: #ffd2cc; background: #fff1f0; color: #b42318; }
        .studio-board { display: grid; grid-template-columns: 320px minmax(0, 1fr); gap: 18px; align-items: start; margin: 0 28px 28px; }
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
        .nb-block { position: relative; }
        .nb-section-nav { position: sticky; top: 72px; z-index: 10; display: flex; gap: 10px; flex-wrap: wrap; padding-top: 12px; padding-bottom: 12px; background: rgba(248,252,249,.92); backdrop-filter: blur(12px); }
        .nb-section-nav a { padding: 8px 10px; border: 1px solid var(--line); border-radius: 8px; background: white; color: var(--green-900); font-size: 13px; font-weight: 800; }
        .nb-block > .page, .nb-section-inner { width: min(1180px, calc(100% - 40px)); margin: 0 auto; }
        .nb-block h1, .nb-block h2, .nb-block h3 { letter-spacing: 0; }
        .nb-hero { min-height: 520px; display: grid; grid-template-columns: minmax(0, 1fr) minmax(300px, .85fr); gap: 54px; align-items: center; padding-top: 72px; padding-bottom: 72px; }
        .nb-hero h1 { margin: 12px 0 18px; }
        .nb-hero p, .nb-split p, .nb-section-inner header p, .nb-video p, .nb-cta p { max-width: 700px; }
        .nb-hero > img, .nb-hero-placeholder { width: 100%; min-height: 320px; max-height: 460px; object-fit: cover; border-radius: 8px; background: #e3f1e6; }
        .nb-hero-placeholder, .nb-image-placeholder { display: grid; place-items: center; color: var(--green-700); font-size: 22px; font-weight: 800; }
        .nb-section-inner { padding-top: 68px; padding-bottom: 68px; }
        .nb-section-inner header { max-width: 760px; margin-bottom: 25px; }
        .nb-card-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .nb-content-card, .nb-testimonial, .nb-price-card { min-width: 0; padding: 22px; border: 1px solid var(--line); border-radius: 8px; background: white; }
        .nb-content-card img { width: 100%; max-height: 220px; object-fit: cover; border-radius: 6px; margin-top: 10px; }
        .nb-card-number { color: var(--green-700); font-weight: 900; font-size: 12px; }
        .nb-split { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 54px; align-items: center; padding-top: 68px; padding-bottom: 68px; }
        .nb-split.image-right > img { order: 2; }
        .nb-split > img, .nb-image-placeholder { width: 100%; height: 350px; object-fit: cover; border-radius: 8px; background: #e3f1e6; }
        .nb-gallery { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .nb-gallery > div { min-height: 170px; background: #e3f1e6; border-radius: 8px; overflow: hidden; }
        .nb-gallery img { width: 100%; height: 100%; min-height: 170px; object-fit: cover; }
        .nb-video { display: grid; grid-template-columns: minmax(220px, .7fr) minmax(0, 1.3fr); gap: 32px; align-items: center; padding-top: 64px; padding-bottom: 64px; }
        .nb-video-frame { aspect-ratio: 16 / 9; background: #082e22; border-radius: 8px; overflow: hidden; }
        .nb-video-frame iframe { width: 100%; height: 100%; border: 0; }
        .nb-logo-grid { display: flex; justify-content: center; align-items: center; gap: 16px; flex-wrap: wrap; }
        .nb-logo-grid a { min-width: 150px; min-height: 76px; display: grid; place-items: center; padding: 12px; border: 1px solid var(--line); border-radius: 8px; color: var(--green-900); }
        .nb-logo-grid img { max-width: 140px; max-height: 48px; object-fit: contain; }
        .nb-testimonial > span { color: var(--green-500); font-size: 42px; line-height: 1; }
        .nb-testimonial blockquote { margin: 8px 0 20px; color: var(--green-950); font-size: 17px; line-height: 1.55; }
        .nb-testimonial small { display: block; color: var(--muted); margin-top: 4px; }
        .nb-price-card > strong { display: block; color: var(--green-800); font-size: 30px; margin: 14px 0; }
        .nb-cta { display: flex; align-items: center; justify-content: space-between; gap: 22px; padding: 54px; margin-top: 35px; margin-bottom: 35px; border-radius: 8px; background: var(--green-900); }
        .nb-cta h2, .nb-cta p { color: white; }
        .nb-cta-button { flex: none; color: var(--green-950); background: white; }
        @media (max-width: 860px) { .nb-hero, .nb-split, .nb-video { grid-template-columns: 1fr; gap: 24px; } .nb-hero { min-height: 0; } .nb-card-grid { grid-template-columns: 1fr; } .nb-gallery { grid-template-columns: repeat(2, minmax(0, 1fr)); } .nb-cta { align-items: flex-start; flex-direction: column; padding: 30px; } .nb-split.image-right > img { order: 0; } }
        .addon-dashboard-section { margin-top: 0; }
        .addon-dashboard-card .card-kicker { color: var(--green-700); font-size: 12px; font-weight: 900; text-transform: uppercase; margin-bottom: 8px; }
        .addon-card-content { display: grid; gap: 12px; }
        .addon-card-content p { margin-bottom: 0; }
        .addon-empty-state { margin-top: 24px; }
        @media (max-width: 860px) {
            .hero { grid-template-columns: 1fr; padding-top: 38px; }
            .landing-hero { grid-template-columns: 1fr; }
            .grid-3 { grid-template-columns: 1fr; }
            .sidebar { transform: translateX(-100%); }
            .sidebar-toggle { display: flex; }
            body.sidebar-open .sidebar { transform: translateX(0); }
            .content { margin-left: 0; }
            .topbar { height: auto; min-height: 72px; padding-top: 14px; padding-bottom: 14px; align-items: flex-start; }
            .form-grid { grid-template-columns: 1fr; }
            .permission-grid { grid-template-columns: 1fr; }
            .admin-grid { grid-template-columns: 1fr; margin-left: 16px; margin-right: 16px; }
            .role-edit-card { margin-left: 16px; margin-right: 16px; }
            .studio-board { grid-template-columns: 1fr; }
            .studio-settings { position: static; }
            .studio-topbar { display: block; }
            .studio-actions { justify-content: flex-start; margin-top: 12px; }
            .section-quick-edit { grid-template-columns: 1fr; }
            .section-quick-edit textarea { grid-column: auto; }
            .site-card-feature, .site-card-media, .site-card-hero, .site-card-feature.image-right, .site-card-media.image-right, .site-card-hero.image-right { grid-template-columns: 1fr; }
            .site-card-feature.image-right img, .site-card-media.image-right img, .site-card-hero.image-left img { order: 0; }
            .site-card-cta { display: grid; }
            .admin-topbar { display: grid; }
            .admin-web-link { justify-self: start; }
            .topbar-account { align-items: flex-start; flex-direction: column; }
            .dash-card { grid-column: span 12; }
            .admin-hero, .admin-section, .admin-panel { margin-left: 16px; margin-right: 16px; padding-left: 18px; padding-right: 18px; }
        }
        @media (max-width: 700px) {
            .admin-card { padding: 18px; }
            .admin-card-header { display: block; }
            .admin-card-header .pill { margin-top: 10px; }
            .table-wrap.embedded-table { overflow: visible; border: 0; background: transparent; }
            .admin-table { min-width: 0; display: block; }
            .admin-table thead { display: none; }
            .admin-table tbody, .admin-table tr, .admin-table td { display: block; }
            .admin-table tr { margin-bottom: 12px; padding: 13px; border: 1px solid var(--line); border-radius: 12px; background: #fff; box-shadow: 0 8px 22px rgba(5,46,36,.05); }
            .admin-table tr:last-child { margin-bottom: 0; }
            .admin-table td { display: grid; grid-template-columns: 94px minmax(0, 1fr); gap: 10px; width: auto !important; min-width: 0 !important; padding: 9px 0; border-bottom: 1px solid #eef3ef; white-space: normal !important; }
            .admin-table td::before { content: attr(data-label); color: var(--muted); font-size: 11px; font-weight: 800; text-transform: uppercase; }
            .admin-table td:last-child { display: block; padding-bottom: 0; border-bottom: 0; }
            .admin-table td:last-child::before { display: block; margin-bottom: 8px; }
            .action-group { display: flex; width: 100%; }
            .action-group .button, .action-group .inline-form { flex: 1 1 0; }
            .action-group .button { width: 100%; text-align: center; }
            .summary-text { max-width: none; }
            .permission-row { align-items: flex-start; flex-direction: column; gap: 5px; }
            .permission-row small { text-align: left; }
        }
    </style>
    @if (request()->routeIs('admin.pages.*') || !empty($builderState))
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="{{ asset('css/page-builder/editor.css') }}">
        <link rel="stylesheet" href="{{ asset('css/page-builder/templates.css') }}">
    @endif
</head>
@php($isAdminArea = request()->routeIs('admin.*'))
<body class="{{ $isAdminArea ? 'admin-mode app-page' : '' }} {{ request()->routeIs('admin.pages.*') ? 'page-builder-mode' : '' }} {{ !empty($builderState) ? 'public-builder-mode' : '' }}">
<div class="site-shell">
    <header class="topbar">
        <a class="brand" href="{{ nova_route('home') }}">
            <span class="brand-mark">NB</span>
            <span>{{ config('app.name', 'NovaBase') }}</span>
        </a>
        <nav class="nav">
            @auth
                <a href="{{ auth()->user()->dashboardUrl() }}">{{ auth()->user()->isAdminLike() ? 'Admin Panel' : 'User Portal' }}</a>
                <form method="POST" action="{{ nova_route('logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            @else
                <a class="button-primary" href="{{ nova_route('login') }}">Login</a>
            @endauth
        </nav>
    </header>
    {{ $slot ?? '' }}
    @yield('content')
</div>
</body>
</html>
