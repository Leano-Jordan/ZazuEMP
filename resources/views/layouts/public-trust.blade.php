<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#172b3b">
    <meta name="description" content="@yield('description', 'Zazu EMP — event management and business operations.')">
    <title>@yield('title', 'Zazu EMP') · Zazu EMP</title>
    <style nonce="{{ request()->attributes->get('cspNonce') }}">
        :root {
            color-scheme: light;
            --page: #f4f7f8;
            --surface: #ffffff;
            --ink: #182b3a;
            --muted: #536878;
            --line: #d8e2e8;
            --navy: #172b3b;
            --navy-soft: #213b4e;
            --teal: #168b91;
            --teal-soft: #e6f4f3;
            --warning: #fff5df;
            --warning-line: #e7c77c;
            --warning-ink: #634916;
            --radius: 16px;
        }
        * { box-sizing: border-box; }
        html { min-width: 320px; background: var(--page); }
        body { margin: 0; color: var(--ink); background: var(--page); font: 16px/1.65 Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        a { color: #087b83; text-underline-offset: 3px; }
        a:hover { color: #075c64; }
        a:focus-visible { outline: 3px solid #159da2; outline-offset: 3px; border-radius: 4px; }
        .site-header { background: var(--navy); color: #edf5f7; border-bottom: 1px solid #30495b; }
        .header-inner, .footer-inner, .page-wrap { width: min(1080px, calc(100% - 40px)); margin-inline: auto; }
        .header-inner { min-height: 76px; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
        .brand { display: inline-flex; align-items: center; gap: 11px; color: #f3f8fa; text-decoration: none; font-weight: 800; letter-spacing: .11em; }
        .brand-mark { display: grid; place-items: center; width: 38px; height: 38px; border: 1px solid #5b7d8f; border-radius: 11px; color: #8ed8d5; font-size: 20px; letter-spacing: 0; }
        .brand small { display: block; color: #a9bfcb; font-size: 10px; font-weight: 650; letter-spacing: .19em; }
        .top-links { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 7px 20px; }
        .top-links a { color: #d4e1e7; text-decoration: none; font-size: 13px; font-weight: 650; }
        .top-links a[aria-current="page"], .top-links a:hover { color: #8ed8d5; }
        .top-links .sign-in { padding: 8px 13px; border: 1px solid #597184; border-radius: 8px; }
        .page-wrap { padding-block: 54px 72px; min-height: calc(100vh - 180px); }
        .eyebrow { margin: 0 0 12px; color: var(--teal); font-size: 12px; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }
        h1 { max-width: 850px; margin: 0; color: var(--navy); font-size: clamp(34px, 5vw, 54px); line-height: 1.08; letter-spacing: -.045em; }
        .lede { max-width: 760px; margin: 20px 0 0; color: var(--muted); font-size: clamp(17px, 2vw, 20px); line-height: 1.6; }
        .content-grid { display: grid; grid-template-columns: minmax(0, 1fr) 270px; gap: 42px; align-items: start; margin-top: 38px; }
        .prose { min-width: 0; }
        .prose h2 { margin: 36px 0 10px; color: var(--navy); font-size: 23px; line-height: 1.25; letter-spacing: -.025em; }
        .prose h2:first-child { margin-top: 0; }
        .prose h3 { margin: 24px 0 7px; color: var(--navy); font-size: 17px; }
        .prose p, .prose li { color: #425969; }
        .prose ul { padding-left: 22px; }
        .prose li + li { margin-top: 7px; }
        .side-card, .content-card { padding: 22px; border: 1px solid var(--line); border-radius: var(--radius); background: var(--surface); box-shadow: 0 8px 26px rgba(20, 43, 58, .045); }
        .side-card + .side-card { margin-top: 14px; }
        .side-card h2 { margin: 0 0 9px; color: var(--navy); font-size: 15px; }
        .side-card p, .side-card li { color: var(--muted); font-size: 14px; }
        .side-card ul { padding-left: 18px; margin-bottom: 0; }
        .notice { margin-top: 28px; padding: 15px 18px; border: 1px solid var(--warning-line); border-radius: 12px; background: var(--warning); color: var(--warning-ink); }
        .notice strong { display: block; margin-bottom: 3px; }
        .notice p { margin: 0; color: var(--warning-ink); font-size: 14px; }
        .status-line { display: inline-flex; align-items: center; gap: 8px; color: #425969; font-size: 13px; font-weight: 700; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; background: #c18b32; }
        .update { padding: 22px 0; border-top: 1px solid var(--line); }
        .update:first-child { border-top: 0; padding-top: 0; }
        .update time { color: var(--teal); font-size: 12px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        .update h2 { margin: 7px 0; font-size: 20px; }
        .update p { margin: 6px 0; }
        .site-footer { background: #e9eff2; border-top: 1px solid var(--line); }
        .footer-inner { padding-block: 25px 30px; display: flex; align-items: flex-start; justify-content: space-between; gap: 22px; }
        .footer-copy { color: #536878; font-size: 12px; }
        .footer-links { display: flex; flex-wrap: wrap; gap: 8px 18px; justify-content: flex-end; }
        .footer-links a { color: #425969; font-size: 13px; font-weight: 650; text-decoration: none; }
        .footer-links a:hover { color: #087b83; text-decoration: underline; }
        @media (max-width: 760px) {
            .header-inner { padding-block: 14px; align-items: flex-start; flex-direction: column; gap: 12px; }
            .top-links { justify-content: flex-start; gap: 8px 15px; }
            .page-wrap { padding-block: 38px 52px; }
            .content-grid { grid-template-columns: 1fr; gap: 22px; margin-top: 28px; }
            .footer-inner { flex-direction: column; }
            .footer-links { justify-content: flex-start; }
        }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; } }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('landing') }}" aria-label="Zazu EMP home">
                <span class="brand-mark" aria-hidden="true">Z</span>
                <span>ZAZU <small>EVENT MANAGEMENT</small></span>
            </a>
            <nav class="top-links" aria-label="Main navigation">
                <a href="{{ route('public.about') }}" @if(request()->routeIs('public.about')) aria-current="page" @endif>About</a>
                <a href="{{ route('public.security') }}" @if(request()->routeIs('public.security')) aria-current="page" @endif>Security</a>
                <a href="{{ route('public.whats-new') }}" @if(request()->routeIs('public.whats-new')) aria-current="page" @endif>What's new</a>
                <a href="{{ route('public.contact') }}" @if(request()->routeIs('public.contact')) aria-current="page" @endif>Contact</a>
                <a class="sign-in" href="{{ route('landing', ['auth' => 'login']) }}">Sign in</a>
            </nav>
        </div>
    </header>
    <main class="page-wrap">
        @yield('content')
    </main>
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-copy">
                <strong>Zazu EMP</strong><br>
                Event management and business operations.<br>
                Built and currently operated independently in South Africa.
            </div>
            <nav class="footer-links" aria-label="Legal and company information">
                <a href="{{ route('public.privacy') }}">Privacy review</a>
                <a href="{{ route('public.terms') }}">Terms review</a>
                <a href="{{ route('public.security') }}">Security</a>
                <a href="{{ route('public.contact') }}">Contact</a>
            </nav>
        </div>
    </footer>
</body>
</html>
