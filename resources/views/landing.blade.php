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
        .topnav{display:flex;align-items:center;gap:6px}.topnav a{padding:9px 11px;border-radius:7px;color:var(--muted);font-size:11px;font-weight:700}.topnav a:hover{background:#fff;color:var(--ink)}.topnav .launch{padding:11px 15px;background:var(--blue);color:#fff;box-shadow:0 7px 16px rgba(30,64,175,.16)}
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
        @media(max-width:640px){.site{width:min(100% - 24px,1380px)}.top{height:auto;padding:14px 0}.topnav a:not(.register):not(.login):not(.launch){display:none}.topnav .account-state{display:none}.hero{padding:48px 0 65px}h1{font-size:48px}.telemetry{grid-template-columns:repeat(2,1fr)}.telemetry div:nth-child(2n){border-right:0}.record{grid-template-columns:1fr}.compare{grid-template-columns:1fr}.compare>article+article{border-left:0;border-top:1px solid #334155}footer{display:block}.footer-right{margin-top:8px}}

        /* Public authentication: one reusable dialog, no standalone public auth page UI. */
        .topnav .register,.topnav .login{font:inherit;cursor:pointer}.topnav button{border:0}.topnav .register{padding:10px 13px;background:#E8F1FF;color:var(--blue);border:1px solid #B7D0F1}.topnav .login{padding:10px 13px;background:#fff;color:var(--ink);border:1px solid var(--line)}
        .zazu-public-auth{width:min(94vw,620px);max-width:620px;max-height:calc(100vh - 28px);padding:0;border:1px solid #CBD5E1;border-radius:16px;background:#fff;color:var(--ink);box-shadow:0 28px 90px rgba(15,23,42,.24);overflow:hidden}
        .zazu-public-auth::backdrop{background:rgba(15,23,42,.48);backdrop-filter:blur(3px)}
        .zazu-public-auth-panel{display:flex;flex-direction:column;max-height:calc(100vh - 28px)}
        .zazu-public-auth-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;padding:24px 24px 18px;border-bottom:1px solid var(--line)}
        .zazu-public-auth-kicker{display:block;color:var(--blue);font:500 9px "DM Mono";letter-spacing:.12em;text-transform:uppercase}
        .zazu-public-auth-head h2{margin:8px 0 5px;font-size:25px;letter-spacing:-.04em}.zazu-public-auth-head p{margin:0;color:var(--muted);font-size:11px;line-height:1.6}
        .zazu-public-auth-close{width:34px;height:34px;flex:0 0 34px;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--muted);font-size:22px;line-height:1;cursor:pointer}.zazu-public-auth-close:hover{color:var(--ink);border-color:#94A3B8;background:#F8FAFC}
        .zazu-public-auth-tabs{display:grid;grid-template-columns:1fr 1fr;gap:6px;padding:10px 24px;border-bottom:1px solid var(--line);background:#F8FAFC}
        .zazu-public-auth-tab{min-height:40px;border:1px solid transparent;border-radius:7px;background:transparent;color:var(--muted);font-size:11px;font-weight:800;cursor:pointer}.zazu-public-auth-tab[aria-selected="true"]{border-color:#B7D0F1;background:#fff;color:var(--blue);box-shadow:0 2px 8px rgba(15,23,42,.06)}
        .zazu-public-auth-body{overflow:auto;padding:24px}
        .zazu-public-auth-body section[hidden]{display:none}
        .zazu-public-auth-state{display:flex;align-items:center;gap:7px;color:var(--blue);font:500 9px "DM Mono";letter-spacing:.06em;text-transform:uppercase}.zazu-public-auth-state span{width:6px;height:6px;border-radius:50%;background:#16A34A;box-shadow:0 0 0 4px rgba(22,163,74,.10)}
        .zazu-public-auth-body h3{margin:10px 0 7px;font-size:27px;letter-spacing:-.045em}.zazu-public-auth-copy{margin:0 0 18px;color:var(--muted);font-size:11px;line-height:1.6}
        .zazu-public-auth-form{display:grid;gap:14px}.zazu-public-auth-field{display:grid;gap:6px}.zazu-public-auth-field label{font-size:10px;font-weight:800;color:var(--ink)}.zazu-public-auth-field input{width:100%;min-height:42px;padding:0 12px;border:1px solid #9EA7BC;border-radius:7px;background:#fff;color:var(--ink);font:inherit;font-size:12px}.zazu-public-auth-field input:focus{outline:2px solid rgba(37,99,235,.24);outline-offset:1px;border-color:#2563EB}.zazu-public-auth-field small{color:var(--muted);font-size:9px;line-height:1.45}
        .zazu-public-auth-password{display:grid;grid-template-columns:1fr auto}.zazu-public-auth-password input{border-radius:7px 0 0 7px}.zazu-public-auth-password button{min-width:58px;border:1px solid #9EA7BC;border-left:0;border-radius:0 7px 7px 0;background:#F8FAFC;color:var(--blue);font-size:10px;font-weight:800;cursor:pointer}
        .zazu-public-auth-options{display:flex;justify-content:space-between;align-items:center;gap:12px;color:var(--muted);font-size:9px}.zazu-public-auth-options label{display:flex;align-items:center;gap:6px}.zazu-public-auth-options a,.zazu-public-auth-switch button{border:0;padding:0;background:none;color:var(--blue);font:inherit;font-weight:800;cursor:pointer;text-decoration:none}.zazu-public-auth-options a:hover,.zazu-public-auth-switch button:hover{text-decoration:underline}
        .zazu-public-auth-submit{min-height:44px;width:100%;display:flex;align-items:center;justify-content:center;gap:10px;border:1px solid var(--blue);border-radius:7px;background:var(--blue);color:#fff;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 9px 20px rgba(30,64,175,.17)}.zazu-public-auth-submit:hover{background:#1747A5}.zazu-public-auth-submit span{font-size:14px}
        .zazu-public-auth-grid{display:grid;grid-template-columns:1fr 1fr;gap:13px}.zazu-public-auth-alert{margin:0 0 14px;padding:10px 11px;border:1px solid #E7A2A9;border-radius:7px;background:#FDEBEC;color:#8E3847;font-size:10px;line-height:1.5}.zazu-public-auth-error{color:#A12B37;font-size:9px;line-height:1.35}.zazu-public-auth-switch{margin:16px 0 0;color:var(--muted);text-align:center;font-size:10px}
        @media(max-width:640px){.zazu-public-auth{width:calc(100vw - 16px);max-height:calc(100vh - 16px);border-radius:14px}.zazu-public-auth-panel{max-height:calc(100vh - 16px)}.zazu-public-auth-head{padding:19px 17px 15px}.zazu-public-auth-head h2{font-size:22px}.zazu-public-auth-tabs{padding:8px 17px}.zazu-public-auth-body{padding:18px 17px}.zazu-public-auth-grid{grid-template-columns:1fr}.zazu-public-auth-body h3{font-size:24px}.zazu-public-auth-options{align-items:flex-start;flex-direction:column}}

    </style>
</head>
<body>
<div class="site">
<header class="top">
    <a href="{{ url('/') }}" class="brand"><span class="mark">Z</span><span><strong>ZAZU</strong><span>Event Management Platform</span></span></a>
    <nav class="topnav" aria-label="Public navigation"><a href="#capabilities">How it works</a><a href="#difference">Why Zazu</a>@if(!$isAuthenticated)<button type="button" class="register" data-auth-open="register">Register</button><button type="button" class="login" data-auth-open="login">Log in</button>@elseif($hasActiveWorkspace)<span class="account-state">You’re signed in</span><a class="launch" href="{{ route('dashboard') }}">Open workspace <span>↗</span></a><form method="POST" action="{{ route('logout') }}" class="topnav-form">@csrf<button type="submit" class="login">Sign out</button></form>@else<span class="account-state">You’re signed in</span><a class="launch" href="{{ route('workspace.recovery') }}">Set up workspace <span>↗</span></a><form method="POST" action="{{ route('logout') }}" class="topnav-form">@csrf<button type="submit" class="login">Sign out</button></form>@endif
</nav>
</header>
<main>
<section class="hero">
<div><div class="eyebrow">Built for South African event businesses</div><h1>Run the work. <em>Know the numbers.</em></h1><p class="hero-copy">From a one-person catering business to a growing events company, keep customers, bookings, quotes, suppliers, equipment, preparation, costs and payments connected to the job—without stitching the business together across WhatsApp, notebooks and spreadsheets.</p><div class="actions">@if(!$isAuthenticated)<button type="button" class="btn primary" data-auth-open="register">Create your workspace <span>→</span></button><button type="button" class="btn" data-auth-open="login">Log in</button>@elseif($hasActiveWorkspace)<span class="account-state">You’re signed in · {{ $business->name }}</span><a class="btn primary" href="{{ route('dashboard') }}">Open workspace <span>→</span></a><form method="POST" action="{{ route('logout') }}" class="topnav-form">@csrf<button type="submit" class="btn">Sign out</button></form>@else<span class="account-state">You’re signed in</span><a class="btn primary" href="{{ route('workspace.recovery') }}">Set up workspace <span>→</span></a><form method="POST" action="{{ route('logout') }}" class="topnav-form">@csrf<button type="submit" class="btn">Sign out</button></form>@endif
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
<section class="closing"><div class="closing-box"><div><div class="eyebrow closing-eyebrow">Ready for the next booking?</div><h2>{{ $hasActiveWorkspace ? 'Open your workspace.' : 'Restore your workspace access.' }}</h2><p>{{ $hasActiveWorkspace ? 'You are signed in. Open your workspace when you are ready.' : 'Your account is signed in. Finish workspace setup to continue.' }}</p></div><div class="actions">@if(!$isAuthenticated)<button type="button" class="btn" data-auth-open="register">Register →</button><button type="button" class="btn" data-auth-open="login">Log in →</button>@else
@if($hasActiveWorkspace)<a class="btn" href="{{ route('dashboard') }}">Open workspace →</a>@else<a class="btn" href="{{ route('workspace.recovery') }}">Set up workspace →</a>@endif<form method="POST" action="{{ route('logout') }}" class="topnav-form">@csrf<button type="submit" class="btn">Sign out</button></form>@endif</div></div></section>

<dialog class="zazu-public-auth" data-public-auth data-auth-initial="{{ request('auth') ?: session('auth_modal') }}" aria-labelledby="zazu-auth-dialog-title">
    <div class="zazu-public-auth-panel">
        <header class="zazu-public-auth-head">
            <div>
                <span class="zazu-public-auth-kicker">ZAZU ACCESS</span>
                <h2 id="zazu-auth-dialog-title">Keep the business moving.</h2>
                <p>Sign in to your workspace or create a new one without leaving the Zazu site.</p>
            </div>
            <button type="button" class="zazu-public-auth-close" data-auth-close aria-label="Close sign in or registration window">×</button>
        </header>

        <div class="zazu-public-auth-tabs" role="tablist" aria-label="Zazu account access">
            <button type="button" class="zazu-public-auth-tab" data-auth-tab="login" role="tab" aria-selected="true" aria-controls="zazu-auth-login">Log in</button>
            <button type="button" class="zazu-public-auth-tab" data-auth-tab="register" role="tab" aria-selected="false" aria-controls="zazu-auth-register">Create workspace</button>
        </div>

        <div class="zazu-public-auth-body">
            <section id="zazu-auth-login" data-auth-panel="login" role="tabpanel" aria-labelledby="zazu-auth-login-tab">
                <div class="zazu-public-auth-state"><span></span> Workspace access</div>
                <h3>Welcome back.</h3>
                <p class="zazu-public-auth-copy">Use your Zazu username or email address to continue.</p>

                @if(session('auth_modal') === 'login' && $errors->any())
                    <div class="zazu-public-auth-alert" role="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="zazu-public-auth-form">
                    @csrf
                    <div class="zazu-public-auth-field">
                        <label for="public-identifier">Username or email</label>
                        <input id="public-identifier" name="identifier" type="text" value="{{ old('identifier') }}" autocomplete="username" inputmode="text" spellcheck="false" autocapitalize="none" required>
                    </div>

                    <div class="zazu-public-auth-field">
                        <label for="public-login-password">Password</label>
                        <div class="zazu-public-auth-password">
                            <input id="public-login-password" name="password" type="password" autocomplete="current-password" required>
                            <button type="button" data-auth-password-toggle="public-login-password" aria-label="Show password">Show</button>
                        </div>
                    </div>

                    <div class="zazu-public-auth-options">
                        <label><input type="checkbox" name="remember" value="1"> <span>Keep me signed in</span></label>
                        <a href="{{ route('password.request') }}">Forgot password?</a>
                    </div>

                    <button type="submit" class="zazu-public-auth-submit">Sign in <span aria-hidden="true">→</span></button>
                </form>

                <p class="zazu-public-auth-switch">New to Zazu? <button type="button" data-auth-tab="register">Create your workspace</button></p>
            </section>

            <section id="zazu-auth-register" data-auth-panel="register" role="tabpanel" aria-labelledby="zazu-auth-register-tab" hidden>
                <div class="zazu-public-auth-state"><span></span> Step 1 · Create workspace</div>
                <h3>Set up your Zazu workspace.</h3>
                <p class="zazu-public-auth-copy">Create your owner account and choose the username you will use to sign in.</p>

                @if(session('auth_modal') === 'register' && $errors->any())
                    <div class="zazu-public-auth-alert" role="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" class="zazu-public-auth-form">
                    @csrf
                    <div class="zazu-public-auth-grid">
                        <div class="zazu-public-auth-field">
                            <label for="public-name">Your name</label>
                            <input id="public-name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required>
                            @error('name') <div class="zazu-public-auth-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="zazu-public-auth-field">
                            <label for="public-username">Username</label>
                            <input id="public-username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" autocapitalize="none" spellcheck="false" maxlength="32" pattern="[A-Za-z0-9][A-Za-z0-9._-]{2,31}" required>
                            <small>3–32 characters. Letters, numbers, dots, underscores and hyphens.</small>
                            @error('username') <div class="zazu-public-auth-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="zazu-public-auth-field">
                            <label for="public-business-name">Business name</label>
                            <input id="public-business-name" name="business_name" type="text" value="{{ old('business_name') }}" autocomplete="organization" required>
                            @error('business_name') <div class="zazu-public-auth-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="zazu-public-auth-field">
                            <label for="public-email">Email address</label>
                            <input id="public-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                            @error('email') <div class="zazu-public-auth-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="zazu-public-auth-field">
                            <label for="public-register-password">Password</label>
                            <div class="zazu-public-auth-password">
                                <input id="public-register-password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                                <button type="button" data-auth-password-toggle="public-register-password" aria-label="Show password">Show</button>
                            </div>
                            @error('password') <div class="zazu-public-auth-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="zazu-public-auth-field">
                            <label for="public-password-confirmation">Confirm password</label>
                            <div class="zazu-public-auth-password">
                                <input id="public-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                                <button type="button" data-auth-password-toggle="public-password-confirmation" aria-label="Show password">Show</button>
                            </div>
                            @error('password_confirmation') <div class="zazu-public-auth-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <button type="submit" class="zazu-public-auth-submit">Create workspace <span aria-hidden="true">→</span></button>
                </form>

                <p class="zazu-public-auth-switch">Already have an account? <button type="button" data-auth-tab="login">Sign in</button></p>
            </section>
        </div>
    </div>
</dialog>

</main>
<footer><span>ZAZU EMP · EVENT OPERATIONS PLATFORM</span><span class="footer-right">SOUTH AFRICAN BUSINESS · EVENTS / CATERING / HIRE</span></footer>
</div>
</body>
</html>