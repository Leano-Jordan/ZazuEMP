<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F2F7FF">
    <meta name="description" content="Zazu EMP helps South African event, catering and equipment-hire businesses keep customers, jobs, quotes, suppliers, costs and finance connected.">
    <title>Zazu EMP · Run the work. Know the numbers.</title>
    @vite(['resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Manrope:wght@500;600;700;800&display=swap');
        :root{--ink:#0F172A;--body:#334155;--muted:#64748B;--blue:#1E40AF;--sapphire:#2563EB;--glow:#3B82F6;--soft:#EFF6FF;--canvas:#F8FAFC;--line:#E2E8F0;--card:#fff}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;color:var(--ink);background:var(--canvas);font-family:Manrope,ui-sans-serif,system-ui,sans-serif}
        body:before{content:"";position:fixed;inset:0;z-index:-2;background:linear-gradient(180deg,rgba(248,250,252,.84),rgba(248,250,252,.96)),url('/images/Background-ZAZU.jpg') center/cover no-repeat}
        body:after{content:"";position:fixed;inset:0;z-index:-1;pointer-events:none;background:radial-gradient(circle at 78% 12%,rgba(59,130,246,.12),transparent 28%),linear-gradient(90deg,transparent 0 70%,rgba(255,255,255,.22))}
        a{text-decoration:none;color:inherit}button{font:inherit}.mono{font-family:"DM Mono",ui-monospace,monospace}.site{width:min(1380px,calc(100% - 40px));margin:auto}
        .top{height:76px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(226,232,240,.82);backdrop-filter:blur(16px)}
        .brand{display:flex;align-items:center;gap:11px}.mark{width:36px;height:36px;display:grid;place-items:center;border-radius:9px;background:var(--blue);color:#fff;font-weight:800;box-shadow:0 8px 20px rgba(30,64,175,.2)}.brand strong{display:block;letter-spacing:-.04em}.brand span:last-child{display:block;color:var(--muted);font:500 9px "DM Mono";letter-spacing:.08em;text-transform:uppercase;margin-top:2px}
        .topnav{display:flex;align-items:center;gap:6px}.topnav a,.topnav button{padding:9px 11px;border-radius:7px;color:var(--muted);font-size:11px;font-weight:700}.topnav button{border:0;background:transparent;cursor:pointer}.topnav a:hover,.topnav button:hover{background:#fff;color:var(--ink)}.topnav .launch{padding:11px 15px;background:var(--blue);color:#fff;box-shadow:0 7px 16px rgba(30,64,175,.16)}
        .hero{display:grid;grid-template-columns:minmax(0,1.08fr) minmax(460px,.92fr);gap:clamp(40px,7vw,100px);align-items:center;min-height:calc(100vh - 76px);padding:72px 0 88px}
        .eyebrow{display:flex;align-items:center;gap:8px;color:var(--blue);font:500 10px "DM Mono";letter-spacing:.13em;text-transform:uppercase}.eyebrow:before{content:"";width:24px;height:1px;background:currentColor}
        h1{max-width:780px;margin:18px 0;font-size:clamp(48px,6.4vw,88px);line-height:.93;letter-spacing:-.065em}h1 em{font-style:normal;color:var(--blue)}.hero-copy{max-width:650px;color:var(--body);font-size:17px;line-height:1.7}.actions{display:flex;flex-wrap:wrap;gap:9px;margin-top:28px}.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:44px;padding:0 16px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--ink);font-size:11px;font-weight:800}.btn.primary{border-color:var(--blue);background:var(--blue);color:#fff;box-shadow:0 9px 20px rgba(30,64,175,.17)}.btn:hover{transform:translateY(-1px)}.hero-note{margin-top:18px;color:var(--muted);font:500 9px "DM Mono";letter-spacing:.05em}
        .topnav .register{padding:10px 13px;background:#E8F1FF;color:var(--blue);border:1px solid #B7D0F1}.topnav .login{padding:10px 13px;background:#fff;color:var(--ink);border:1px solid var(--line)}.hero-photo{height:180px;background-position:center;background-size:cover;position:relative}.hero-photo:after,.feature-image:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(15,23,42,.02),rgba(15,23,42,.40))}.hero-photo span,.feature-image span{position:absolute;z-index:1;left:14px;bottom:12px;color:#fff;font:600 8px "DM Mono";letter-spacing:.08em;text-transform:uppercase;background:rgba(15,23,42,.58);padding:6px 8px;border-radius:5px}.feature-image{height:160px;position:relative;margin:-2px -2px 26px;background-size:cover;background-position:center;border-radius:8px;overflow:hidden}.feature.large .feature-image{height:185px}.feature-image.compact{height:92px;margin-bottom:20px}.closing-box .actions{margin-top:0}.closing-box .btn{margin-top:0}        .control-card{overflow:hidden;border:1px solid rgba(203,213,225,.95);border-radius:14px;background:rgba(255,255,255,.91);box-shadow:0 25px 70px rgba(15,23,42,.12);backdrop-filter:blur(18px)}.control-head{display:flex;justify-content:space-between;padding:13px 15px;border-bottom:1px solid var(--line);font:500 9px "DM Mono";letter-spacing:.07em}.online{display:flex;gap:6px;align-items:center;color:#166534}.online i{width:6px;height:6px;border-radius:50%;background:#16A34A;box-shadow:0 0 0 4px rgba(22,163,74,.1)}
        .telemetry{display:grid;grid-template-columns:repeat(5,1fr);border-bottom:1px solid var(--line)}.telemetry div{padding:16px 12px;border-right:1px solid var(--line)}.telemetry div:last-child{border:0}.telemetry small{display:block;color:var(--muted);font:500 8px "DM Mono";text-transform:uppercase}.telemetry strong{display:block;margin-top:7px;font:500 22px "DM Mono"}
        .record{display:grid;grid-template-columns:1.35fr .8fr .9fr;gap:20px;padding:20px}.record small,.record-label{display:block;color:var(--muted);font:500 8px "DM Mono";text-transform:uppercase}.record strong{display:block;margin-top:7px;font-size:13px}.record .ref{font:500 11px "DM Mono";word-break:break-all}.record-meta{margin-top:5px;color:var(--muted);font-size:10px}.tag{display:inline-flex;margin-top:7px;padding:4px 6px;border-radius:999px;background:#FEF3C7;color:#92400E;font:500 8px "DM Mono"}
        .flow{display:flex;align-items:center;gap:6px;padding:11px 15px;background:#0F172A;color:#CBD5E1;font:500 8px "DM Mono";overflow:auto;white-space:nowrap}.flow b{color:#93C5FD;font-weight:500}
        .section{padding:105px 0;border-top:1px solid rgba(226,232,240,.8)}.section-head{display:flex;justify-content:space-between;align-items:end;gap:40px;margin-bottom:30px}.section h2{max-width:720px;margin:10px 0 0;font-size:clamp(31px,4vw,54px);line-height:.98;letter-spacing:-.055em}.intro{max-width:480px;color:var(--muted);font-size:13px;line-height:1.7}
        .bento{display:grid;grid-template-columns:1.35fr .65fr;grid-template-rows:auto auto;gap:12px}.feature{padding:28px;border:1px solid var(--line);border-radius:12px;background:rgba(255,255,255,.88);box-shadow:0 10px 28px rgba(15,23,42,.045)}.feature.large{grid-row:span 2;min-height:410px;background:linear-gradient(145deg,#fff,#EFF6FF)}.num{font:500 9px "DM Mono";color:var(--blue)}.feature h3{margin:54px 0 10px;font-size:24px;letter-spacing:-.035em}.feature p{margin:0;color:var(--muted);font-size:12px;line-height:1.7}.mini{min-height:195px}.mini h3{margin-top:32px}
        .compare{display:grid;grid-template-columns:1fr 1fr;border:1px solid var(--line);border-radius:12px;overflow:hidden;background:#fff}.compare>article{padding:32px}.compare>article+article{border-left:1px solid var(--line);background:var(--ink);color:#fff}.compare h3{margin:0 0 20px;font:500 10px "DM Mono";letter-spacing:.12em;text-transform:uppercase}.compare ul{list-style:none;padding:0;margin:0;display:grid;gap:13px}.compare li{color:var(--body);font-size:13px;line-height:1.55}.compare li:before{content:"—";margin-right:9px;color:var(--sapphire)}.compare>article+article li{color:#CBD5E1}
        .closing{padding:90px 0}.closing-box{display:flex;align-items:center;justify-content:space-between;gap:30px;padding:38px;border-radius:14px;background:var(--blue);color:#fff;box-shadow:0 20px 50px rgba(30,64,175,.2)}.closing-box h2{margin:8px 0;font-size:34px;letter-spacing:-.045em}.closing-box p{margin:0;color:#DBEAFE;font-size:12px}.closing-box .btn{border-color:#fff;background:#fff;color:var(--blue)}
        footer{display:flex;justify-content:space-between;padding:22px 0;border-top:1px solid var(--line);color:var(--muted);font:500 8px "DM Mono";letter-spacing:.06em}
        @media(max-width:920px){.hero{grid-template-columns:1fr;min-height:auto;padding-top:60px}.bento{grid-template-columns:1fr}.feature.large{grid-row:auto}.section-head{display:block}.intro{margin-top:15px}.closing-box{display:block}.closing-box .btn{margin-top:20px}}
        @media(max-width:640px){.site{width:min(100% - 24px,1380px)}.top{height:auto;padding:14px 0}.topnav a:not(.launch){display:none}.topnav .account-state{display:none}.hero{padding:48px 0 65px}h1{font-size:48px}.telemetry{grid-template-columns:repeat(2,1fr)}.telemetry div:nth-child(2n){border-right:0}.record{grid-template-columns:1fr}.compare{grid-template-columns:1fr}.compare>article+article{border-left:0;border-top:1px solid #334155}footer{display:block}.footer-right{margin-top:8px}}

        /* Public authentication modal. */
        .auth-modal{width:min(520px,calc(100% - 24px));max-width:none;padding:0;border:0;border-radius:16px;background:transparent;box-shadow:0 30px 90px rgba(15,23,42,.28);color:var(--ink)}.auth-modal::backdrop{background:rgba(15,23,42,.58);backdrop-filter:blur(5px)}.auth-modal-panel{position:relative;padding:30px;background:#fff;border:1px solid rgba(226,232,240,.95);border-radius:16px;max-height:calc(100vh - 24px);overflow-y:auto}.auth-modal-access{display:grid;grid-template-columns:1fr 1fr;gap:4px;margin:0 0 24px;padding:4px;border:1px solid #D8E2F0;border-radius:11px;background:#F3F7FC}.auth-modal-access button{min-height:40px;border:0;border-radius:8px;background:transparent;color:#64748B;font:800 10px Manrope,ui-sans-serif,system-ui,sans-serif;letter-spacing:.01em;cursor:pointer;transition:background-color 160ms ease,color 160ms ease,box-shadow 160ms ease,transform 160ms ease}.auth-modal-access button[aria-selected="true"]{background:#fff;color:#173B8F;box-shadow:0 2px 7px rgba(15,23,42,.09)}.auth-modal-access button:hover:not([aria-selected="true"]){color:var(--ink);background:rgba(255,255,255,.55)}.auth-modal-access button:active{transform:scale(.985)}.auth-modal-access button:focus-visible{outline:3px solid rgba(59,130,246,.18);outline-offset:1px}.auth-modal-close{position:absolute;top:14px;right:14px;width:36px;height:36px;border:1px solid var(--line);border-radius:9px;background:#fff;color:var(--muted);cursor:pointer;font-size:18px;line-height:1}.auth-modal-close:hover{color:var(--ink);background:var(--canvas)}.auth-modal-kicker{color:var(--blue);font:500 9px "DM Mono";letter-spacing:.13em;text-transform:uppercase}.auth-modal h2{margin:8px 45px 7px 0;font-size:30px;line-height:1.05;letter-spacing:-.05em}.auth-modal-copy{max-width:390px;margin:0 40px 24px 0;color:#5B6B82;font-size:12px;line-height:1.65}.auth-modal-form{display:grid;gap:15px}.auth-modal-field label{color:#1E293B;letter-spacing:-.01em}.auth-modal-field{display:grid;gap:6px}.auth-modal-field label{font-size:11px;font-weight:800}.auth-modal-field input{display:block;box-sizing:border-box;width:100%;min-height:46px;height:46px;margin:0;padding:0 13px;border:1px solid #D8E2F0;border-radius:9px;background:#FBFDFF;color:var(--ink);font:600 13px Manrope,ui-sans-serif,system-ui,sans-serif;line-height:1.2;transition:border-color 160ms ease,box-shadow 160ms ease,background-color 160ms ease}.auth-modal-field input:hover{border-color:#B9C8DC;background:#fff}.auth-modal-field input:focus{outline:none;border-color:var(--sapphire);background:#fff;box-shadow:0 0 0 3px rgba(59,130,246,.13)}.auth-modal-password{position:relative;width:100%}.auth-modal-password input{padding-right:68px}.auth-modal-password button{position:absolute;right:6px;top:7px;height:32px;padding:0 9px;border:0;border-radius:6px;background:var(--soft);color:var(--blue);font:700 10px Manrope;cursor:pointer}.auth-modal-error{margin:0;color:#B91C1C;font-size:10px;line-height:1.45}.auth-modal-help{margin-top:-5px;color:var(--muted);font-size:10px;line-height:1.45}.auth-modal-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.auth-modal-submit{width:100%;border:0;cursor:pointer}.auth-modal-switch{margin-top:20px;padding-top:17px;border-top:1px solid #E5EAF1;text-align:center;color:#64748B;font-size:11px}.auth-modal-switch button{transition:color 140ms ease}.auth-modal-switch button:hover{color:#173B8F}.auth-modal-switch button,.auth-modal-forgot{border:0;background:none;padding:0;color:var(--blue);font:800 11px Manrope;cursor:pointer}.auth-modal-forgot{display:block;margin-top:9px;text-align:right}.auth-modal[open]{animation:authModalIn 160ms ease-out}@keyframes authModalIn{from{opacity:0;transform:translateY(8px) scale(.985)}to{opacity:1;transform:none}}[data-auth-panel]{transition:opacity 170ms ease,transform 190ms ease}[data-auth-panel][hidden]{display:none}[data-auth-panel].auth-panel-enter{animation:authPanelEnter 190ms ease-out}@keyframes authPanelEnter{from{opacity:0;transform:translateY(7px)}to{opacity:1;transform:none}}@media(max-width:640px){.auth-modal{width:calc(100% - 16px);margin:auto}.auth-modal-panel{padding:24px 18px 20px;border-radius:14px}.auth-modal-grid{grid-template-columns:1fr}.auth-modal h2{font-size:25px}.auth-modal-copy{margin-right:30px}.auth-modal-close{top:10px;right:10px}.auth-modal::backdrop{backdrop-filter:none}}@media(prefers-reduced-motion:reduce){.auth-modal[open]{animation:none}}
    </style>
</head>
<body>
<div class="site">
<header class="top">
    <a href="{{ url('/') }}" class="brand"><span class="mark">Z</span><span><strong>ZAZU</strong><span>Event Management Platform</span></span></a>
    <nav class="topnav" aria-label="Public navigation"><a href="#capabilities">How it works</a><a href="#difference">Why Zazu</a>@if(!$isAuthenticated)<button type="button" class="register" data-auth-modal-open="register">Register</button><button type="button" class="login" data-auth-modal-open="login">Log in</button>@elseif($hasActiveWorkspace)<span class="account-state">You’re signed in</span><a class="launch" href="{{ route('dashboard') }}">Open workspace <span>↗</span></a><form method="POST" action="{{ route('logout') }}" class="topnav-form">@csrf<button type="submit" class="login">Sign out</button></form>@else<span class="account-state">You’re signed in</span><a class="launch" href="{{ route('workspace.recovery') }}">Set up workspace <span>↗</span></a><form method="POST" action="{{ route('logout') }}" class="topnav-form">@csrf<button type="submit" class="login">Sign out</button></form>@endif
</nav>
</header>
<main>
<section class="hero">
<div><div class="eyebrow">Built for South African event businesses</div><h1>Run the work. <em>Know the numbers.</em></h1><p class="hero-copy">From a one-person catering business to a growing events company, keep customers, bookings, quotes, suppliers, equipment, preparation, costs and payments connected to the job—without stitching the business together across WhatsApp, notebooks and spreadsheets.</p><div class="actions">@if(!$isAuthenticated)<button type="button" class="btn primary" data-auth-modal-open="register">Create your workspace <span>→</span></button><button type="button" class="btn" data-auth-modal-open="login">Log in</button>@elseif($hasActiveWorkspace)<span class="account-state">You’re signed in · {{ $business->name }}</span><a class="btn primary" href="{{ route('dashboard') }}">Open workspace <span>→</span></a><form method="POST" action="{{ route('logout') }}" class="topnav-form">@csrf<button type="submit" class="btn">Sign out</button></form>@else<span class="account-state">You’re signed in</span><a class="btn primary" href="{{ route('workspace.recovery') }}">Set up workspace <span>→</span></a><form method="POST" action="{{ route('logout') }}" class="topnav-form">@csrf<button type="submit" class="btn">Sign out</button></form>@endif
<a class="btn" href="#capabilities">See how it works</a></div><div class="hero-note">SOUTH AFRICAN BUSINESS · EVENTS · CATERING · HIRE · COMMERCIAL · CONTROL</div></div>
<div class="control-card" aria-label="Live workspace preview">
<div class="hero-photo" role="img" aria-label="Catering event table placeholder image" style="background-image:url('https://images.unsplash.com/photo-1576842546422-60562b9242ae?auto=format&fit=crop&w=1600&q=85');"><span>Replace with your event photography</span></div>
<div class="control-head"><span>ROSCO ICT / LIVE WORKSPACE</span><span class="online"><i></i>ONLINE</span></div>
<div class="telemetry">
<div><small>All work</small><strong>{{ str_pad((string)$telemetry['all_work'],2,'0',STR_PAD_LEFT) }}</strong></div><div><small>Today</small><strong>{{ str_pad((string)$telemetry['today'],2,'0',STR_PAD_LEFT) }}</strong></div><div><small>Next 7d</small><strong>{{ str_pad((string)$telemetry['next_7_days'],2,'0',STR_PAD_LEFT) }}</strong></div><div><small>In progress</small><strong>{{ str_pad((string)$telemetry['in_progress'],2,'0',STR_PAD_LEFT) }}</strong></div><div><small>Drafts</small><strong>{{ str_pad((string)$telemetry['drafts'],2,'0',STR_PAD_LEFT) }}</strong></div>
</div>
<div class="record"><div><span class="record-label">Operational record</span><strong>{{ $telemetry['record_name'] }}</strong><span class="record-meta">{{ $telemetry['record_meta'] }}</span><span class="tag">{{ $telemetry['status'] }}</span></div><div><span class="record-label">Schedule</span><strong>{{ $telemetry['date'] }}</strong><span class="record-meta">{{ $telemetry['event_type'] }}</span></div><div><span class="record-label">Reference</span><strong class="ref">{{ $telemetry['reference'] }}</strong><span class="record-meta">{{ $telemetry['reference_meta'] }}</span></div></div>
<div class="flow"><b>JOB</b> → CUSTOMER → CAPABILITY → QUOTE → PREP → RESOURCES → FINANCE</div>
</div>
</section>
<section class="section" id="capabilities"><div class="section-head"><div><div class="eyebrow">Made for the work</div><h2>One job. A clear view from booking to payment.</h2></div><p class="intro">Start with the basics, then bring more of the operation together as the business grows—from customer and quote through preparation, purchasing, costs and finance.</p></div>
<div class="bento"><article class="feature large"><div class="feature-image" style="background-image:url('https://images.unsplash.com/photo-1773409258502-a9e77a8d80fe?auto=format&fit=crop&w=1200&q=85');"><span>Placeholder</span></div><span class="num">01 / OPERATIONS</span><h3>Keep the booking together.</h3><p>Customer details, event dates, requirements, quotes, preparation and costs stay connected to the work they belong to.</p></article><article class="feature mini"><div class="feature-image compact" style="background-image:url('https://images.unsplash.com/photo-1768508948334-541161d1cb5c?auto=format&fit=crop&w=900&q=85');"><span>Placeholder</span></div><span class="num">02 / RESOURCES</span><h3>Know what you need before the event.</h3><p>Track services, equipment, stock, suppliers and purchasing in the same operational context.</p></article><article class="feature mini"><span class="num">03 / CONTROL</span><h3>Keep money tied to the work.</h3><p>See invoices, payments, expenses and costs with a clearer line back to the work that created them.</p></article></div></section>
<section class="section" id="difference"><div class="section-head"><div><div class="eyebrow">A better working record</div><h2>Replace scattered admin with one working record.</h2></div></div><div class="compare"><article><h3>Without a central record</h3><ul><li>WhatsApp messages, notebooks and spreadsheets each hold part of the job.</li><li>Quotes and preparation details can drift away from execution.</li><li>Equipment and supplier decisions are easy to lose in the admin.</li><li>Costs can reach finance long after the operational decision.</li></ul></article><article><h3>With Zazu</h3><ul><li>One job record becomes the reference point for the work.</li><li>Operational and commercial context stays connected.</li><li>Purchasing and costs can be traced back to the work.</li><li>Finance has a clearer line back to what happened on the job.</li></ul></article></div></section>
<section class="closing"><div class="closing-box"><div><div class="eyebrow closing-eyebrow">Ready for the next booking?</div><h2>{{ $hasActiveWorkspace ? 'Open your workspace.' : 'Restore your workspace access.' }}</h2><p>{{ $hasActiveWorkspace ? 'You are signed in. Open your workspace when you are ready.' : 'Your account is signed in. Finish workspace setup to continue.' }}</p></div><div class="actions">@if(!$isAuthenticated)<button type="button" class="btn" data-auth-modal-open="register">Register →</button><button type="button" class="btn" data-auth-modal-open="login">Log in →</button>@else
@if($hasActiveWorkspace)<a class="btn" href="{{ route('dashboard') }}">Open workspace →</a>@else<a class="btn" href="{{ route('workspace.recovery') }}">Set up workspace →</a>@endif<form method="POST" action="{{ route('logout') }}" class="topnav-form">@csrf<button type="submit" class="btn">Sign out</button></form>@endif</div></div></section>

<dialog class="auth-modal" data-auth-modal aria-modal="true" aria-labelledby="auth-login-title" aria-describedby="auth-login-copy">
    <div class="auth-modal-panel">
        <button type="button" class="auth-modal-close" data-auth-modal-close aria-label="Close authentication window">×</button>

        <div class="auth-modal-access" role="tablist" aria-label="Zazu access type">
            <button type="button" role="tab" aria-selected="true" data-auth-access-switch="login">Workspace access</button>
            <button type="button" role="tab" aria-selected="false" data-auth-access-switch="owner">Owner / Administrator</button>
        </div>

        <section data-auth-panel="login">
            <div class="auth-modal-kicker" data-auth-kicker>Workspace access</div>
            <h2 id="auth-login-title" data-auth-title>Welcome back.</h2>
            <p class="auth-modal-copy" id="auth-login-copy" data-auth-copy>Use your username or email address to continue to Zazu.</p>
            <form method="POST" action="{{ route('login.store') }}" class="auth-modal-form" data-auth-form="login">
                @csrf
                <input type="hidden" name="owner_access" value="0" data-auth-owner-access>
                <div class="auth-modal-field">
                    <label for="auth_identifier">Username or email</label>
                    <input id="auth_identifier" name="identifier" type="text" value="{{ old('identifier') }}" autocomplete="username" inputmode="text" spellcheck="false" autocapitalize="none" required>
                    @error('identifier')<p class="auth-modal-error">{{ $message }}</p>@enderror
                </div>
                <div class="auth-modal-field">
                    <label for="auth_password">Password</label>
                    <div class="auth-modal-password">
                        <input id="auth_password" name="password" type="password" autocomplete="current-password" required>
                        <button type="button" data-auth-password-toggle data-target="auth_password" aria-label="Show password">Show</button>
                    </div>
                    @error('password')<p class="auth-modal-error">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn primary auth-modal-submit" data-auth-submit>Log in <span>→</span></button>
                <a class="auth-modal-forgot" href="{{ route('password.request') }}">Forgot your password?</a>
            </form>
            <div class="auth-modal-switch" data-auth-login-switch>New to Zazu? <button type="button" data-auth-modal-switch="register">Create your workspace</button></div>
        </section>

        <section data-auth-panel="register" hidden>
            <div class="auth-modal-kicker">Create owner workspace</div>
            <h2 id="auth-register-title">Set up your Zazu workspace.</h2>
            <p class="auth-modal-copy" id="auth-register-copy">Create the owner account for your Zazu workspace and choose the username you will use to sign in.</p>
            <form method="POST" action="{{ route('register.store') }}" class="auth-modal-form" data-auth-form="register">
                @csrf
                <div class="auth-modal-grid">
                    <div class="auth-modal-field">
                        <label for="auth_name">Your name</label>
                        <input id="auth_name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required>
                        @error('name')<p class="auth-modal-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="auth-modal-field">
                        <label for="auth_username">Username</label>
                        <input id="auth_username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" autocapitalize="none" spellcheck="false" maxlength="32" pattern="[A-Za-z0-9][A-Za-z0-9._-]{2,31}" required>
                        <div class="auth-modal-help">3–32 characters. Letters, numbers, dots, underscores and hyphens.</div>
                        @error('username')<p class="auth-modal-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="auth-modal-field">
                        <label for="auth_business_name">Business name</label>
                        <input id="auth_business_name" name="business_name" type="text" value="{{ old('business_name') }}" autocomplete="organization" required>
                        @error('business_name')<p class="auth-modal-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="auth-modal-field">
                        <label for="auth_email">Email address</label>
                        <input id="auth_email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                        @error('email')<p class="auth-modal-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="auth-modal-field">
                        <label for="auth_register_password">Password</label>
                        <div class="auth-modal-password">
                            <input id="auth_register_password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                            <button type="button" data-auth-password-toggle data-target="auth_register_password" aria-label="Show password">Show</button>
                        </div>
                        @error('password')<p class="auth-modal-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="auth-modal-field">
                        <label for="auth_password_confirmation">Confirm password</label>
                        <div class="auth-modal-password">
                            <input id="auth_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                            <button type="button" data-auth-password-toggle data-target="auth_password_confirmation" aria-label="Show password">Show</button>
                        </div>
                        @error('password_confirmation')<p class="auth-modal-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <button type="submit" class="btn primary auth-modal-submit">Create workspace <span>→</span></button>
            </form>
            <div class="auth-modal-switch">Already have an account? <button type="button" data-auth-modal-switch="login">Log in</button></div>
        </section>
    </div>
</dialog>

<footer><span>ZAZU EMP · EVENT OPERATIONS PLATFORM</span><span class="footer-right">SOUTH AFRICAN BUSINESS · EVENTS / CATERING / HIRE</span></footer>
</div>

<script>
(() => {
    const modal = document.querySelector('[data-auth-modal]');
    if (!modal) return;
    const openers = document.querySelectorAll('[data-auth-modal-open]');
    const closeButton = modal.querySelector('[data-auth-modal-close]');
    const panels = modal.querySelectorAll('[data-auth-panel]');
    const lastFocus = { element: null };
    const requestedPanel = @json(request()->query('auth'));
    const requestedAuthPanel = ['login', 'register', 'owner'].includes(requestedPanel) ? requestedPanel : null;
    const validationPanel = @json(session('auth_modal', $errors->any() ? 'login' : null));
    const initialPanel = requestedAuthPanel || validationPanel;
    const accessTabs = modal.querySelectorAll('[data-auth-access-switch]');
    const ownerAccess = modal.querySelector('[data-auth-owner-access]');
    const loginTitle = modal.querySelector('[data-auth-title]');
    const loginCopy = modal.querySelector('[data-auth-copy]');
    const loginKicker = modal.querySelector('[data-auth-kicker]');
    const loginSubmit = modal.querySelector('[data-auth-submit]');
    const loginSwitch = modal.querySelector('[data-auth-login-switch]');

    const setAccessMode = (mode) => {
        const isOwner = mode === 'owner';
        accessTabs.forEach((tab) => tab.setAttribute('aria-selected', tab.dataset.authAccessSwitch === mode ? 'true' : 'false'));
        if (ownerAccess) ownerAccess.value = isOwner ? '1' : '0';
        if (loginKicker) loginKicker.textContent = isOwner ? 'Owner access' : 'Workspace access';
        if (loginTitle) loginTitle.textContent = isOwner ? 'Welcome back, owner.' : 'Welcome back.';
        if (loginCopy) loginCopy.textContent = isOwner ? 'Use your owner account to enter protected administration.' : 'Use your username or email address to continue to Zazu.';
        if (loginSubmit) loginSubmit.innerHTML = isOwner ? 'Open owner area <span>→</span>' : 'Log in <span>→</span>';
        if (loginSwitch) loginSwitch.hidden = isOwner;
    };

    const showPanel = (name) => {
        const panel = name === 'owner' ? 'login' : name;
        const active = modal.querySelector('[data-auth-panel]:not([hidden])');
        const next = modal.querySelector('[data-auth-panel="' + panel + '"]');
        if (!next) return;

        if (active === next) {
            if (panel === 'login') setAccessMode(name === 'owner' ? 'owner' : 'login');
            const currentFirst = next.querySelector('input:not([type="hidden"])');
            window.setTimeout(() => currentFirst?.focus(), 0);
            return;
        }

        modal.querySelector('.auth-modal-panel')?.classList.add('auth-switching');
        if (active) {
            active.hidden = true;
            active.classList.remove('auth-panel-enter');
        }
        next.hidden = false;
        void next.offsetWidth;
        next.classList.add('auth-panel-enter');

        modal.setAttribute('aria-labelledby', panel === 'register' ? 'auth-register-title' : 'auth-login-title');
        modal.setAttribute('aria-describedby', panel === 'register' ? 'auth-register-copy' : 'auth-login-copy');
        if (panel === 'login') setAccessMode(name === 'owner' ? 'owner' : 'login');

        const first = next.querySelector('input:not([type="hidden"])');
        window.setTimeout(() => {
            modal.querySelector('.auth-modal-panel')?.classList.remove('auth-switching');
            next.classList.remove('auth-panel-enter');
            first?.focus();
        }, 170);
    };

    const open = (name, opener = null) => {
        lastFocus.element = opener || document.activeElement;
        showPanel(name);
        if (typeof modal.showModal === 'function') modal.showModal();
        else modal.setAttribute('open', '');
    };

    const close = () => {
        if (typeof modal.close === 'function' && modal.open) modal.close();
        else modal.removeAttribute('open');
        lastFocus.element?.focus?.();
        lastFocus.element = null;
    };

    openers.forEach((button) => button.addEventListener('click', () => open(button.dataset.authModalOpen, button)));
    accessTabs.forEach((button) => button.addEventListener('click', () => showPanel(button.dataset.authAccessSwitch)));
    modal.querySelectorAll('[data-auth-modal-switch]').forEach((button) => button.addEventListener('click', () => showPanel(button.dataset.authModalSwitch)));
    closeButton?.addEventListener('click', close);
    modal.addEventListener('cancel', (event) => { event.preventDefault(); close(); });
    modal.addEventListener('click', (event) => { if (event.target === modal) close(); });
    modal.querySelectorAll('[data-auth-password-toggle]').forEach((button) => button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.target);
        if (!input) return;
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.textContent = visible ? 'Show' : 'Hide';
        button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
    }));

    if (initialPanel) open(initialPanel);
})();
</script>
</body>
</html>