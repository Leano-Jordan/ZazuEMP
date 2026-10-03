<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F1F5FB">
    <meta name="description" content="Zazu EMP helps South African event, catering and equipment-hire businesses keep customers, jobs, quotes, suppliers, costs and finance connected.">
    <title>Zazu EMP · Run the work. Know the numbers.</title>
    @vite(['resources/js/app.js'])
    <style>
        :root{--ink:#0F172A;--body:#334155;--muted:#64748B;--blue:#1E40AF;--sapphire:#2563EB;--glow:#3B82F6;--soft:#EFF6FF;--canvas:#F8FAFC;--line:#E2E8F0;--card:#fff}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;color:var(--ink);background:var(--canvas);font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}
        body:before{content:"";position:fixed;inset:0;z-index:-2;background:linear-gradient(180deg,#F5F8FD 0%,#EEF4FB 48%,#F8FAFC 100%)}
        body:after{content:"";position:fixed;inset:0;z-index:-1;pointer-events:none;background:radial-gradient(circle at 78% 12%,rgba(59,130,246,.12),transparent 28%),linear-gradient(90deg,transparent 0 70%,rgba(255,255,255,.22))}
        a{text-decoration:none;color:inherit}button{font:inherit}.mono{font-family:"DM Mono",ui-monospace,monospace}.site{width:min(1380px,calc(100% - 40px));margin:auto}
        .top{height:76px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(226,232,240,.82);backdrop-filter:blur(16px)}
        .brand{display:flex;align-items:center;gap:11px}.mark{width:36px;height:36px;display:grid;place-items:center;border-radius:9px;background:var(--blue);color:#fff;font-weight:800;box-shadow:0 8px 20px rgba(30,64,175,.2)}.brand strong{display:block;letter-spacing:-.04em}.brand span:last-child{display:block;color:var(--muted);font:500 9px ui-monospace,SFMono-Regular,Menlo,monospace;letter-spacing:.08em;text-transform:uppercase;margin-top:2px}
        .topnav{display:flex;align-items:center;gap:5px}.topnav a,.topnav button{padding:9px 11px;border-radius:7px;color:var(--muted);font-size:11px;font-weight:700}.topnav button{border:0;background:transparent;cursor:pointer}.topnav a:hover,.topnav button:hover{background:#fff;color:var(--ink)}.topnav .launch{padding:11px 15px;background:var(--blue);color:#fff;box-shadow:0 7px 16px rgba(30,64,175,.16)}.topnav-form{display:flex;margin:0}.mobile-nav-toggle{display:none;position:relative;z-index:30;pointer-events:auto}.mobile-menu{display:flex;align-items:center;gap:5px}
        .hero{display:grid;grid-template-columns:minmax(0,1fr) minmax(520px,.95fr);gap:clamp(36px,5vw,72px);align-items:center;min-height:auto;padding:56px 0 42px}
        .eyebrow{display:flex;align-items:center;gap:8px;color:var(--blue);font:500 10px ui-monospace,SFMono-Regular,Menlo,monospace;letter-spacing:.13em;text-transform:uppercase}.eyebrow:before{content:"";width:24px;height:1px;background:currentColor}
        h1{max-width:780px;margin:18px 0;font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;font-size:clamp(48px,6.4vw,88px);font-weight:700;line-height:.92;letter-spacing:-.055em}h1 em{font-style:normal;color:var(--blue)}.hero-copy{max-width:650px;color:var(--body);font-size:17px;line-height:1.7}.actions{display:flex;flex-wrap:wrap;gap:9px;margin-top:28px}.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:44px;padding:0 16px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--ink);font-size:11px;font-weight:800}.btn.primary{border-color:var(--blue);background:var(--blue);color:#fff;box-shadow:0 9px 20px rgba(30,64,175,.17)}.btn:hover{transform:translateY(-1px)}.hero-note{margin-top:18px;color:var(--muted);font:500 9px "DM Mono";letter-spacing:.05em}
        .topnav .register{padding:10px 13px;background:#E8F1FF;color:var(--blue);border:1px solid #B7D0F1}.topnav .login{padding:10px 13px;background:#fff;color:var(--ink);border:1px solid var(--line)}.hero-photo{height:clamp(360px,38vw,500px);aspect-ratio:4/3;background-position:center 35%;background-size:cover;position:relative;background-color:#E2E8F0}.hero-photo:after,.feature-image:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(15,23,42,.02),rgba(15,23,42,.40))}.hero-photo span,.feature-image span{position:absolute;z-index:1;left:14px;bottom:12px;color:#fff;font:600 8px ui-monospace,SFMono-Regular,Menlo,monospace;letter-spacing:.08em;text-transform:uppercase;background:rgba(15,23,42,.58);padding:6px 8px;border-radius:5px}.feature-image{height:160px;position:relative;margin:-2px -2px 20px;background-size:cover;background-position:center;border-radius:8px;overflow:hidden;background-color:#E2E8F0}.feature.large .feature-image{height:270px;background-position:center 42%}.feature-image.compact{height:150px;margin-bottom:20px;background-position:center 38%}.closing-box .actions{margin-top:0}.closing-box .btn{margin-top:0}        .control-card{overflow:hidden;border:1px solid rgba(203,213,225,.95);border-radius:14px;background:rgba(255,255,255,.91);box-shadow:0 25px 70px rgba(15,23,42,.12);backdrop-filter:blur(18px)}.control-head{display:flex;justify-content:space-between;padding:13px 15px;border-bottom:1px solid var(--line);font:500 9px "DM Mono";letter-spacing:.07em}.online{display:flex;gap:6px;align-items:center;color:#166534}.online i{width:6px;height:6px;border-radius:50%;background:#16A34A;box-shadow:0 0 0 4px rgba(22,163,74,.1)}
        .telemetry{display:grid;grid-template-columns:repeat(5,1fr);border-bottom:1px solid var(--line)}.telemetry div{padding:16px 12px;border-right:1px solid var(--line)}.telemetry div:last-child{border:0}.telemetry small{display:block;color:var(--muted);font:500 8px ui-monospace,SFMono-Regular,Menlo,monospace;text-transform:uppercase}.telemetry strong{display:block;margin-top:7px;font:500 22px ui-monospace,SFMono-Regular,Menlo,monospace}
        .record{display:grid;grid-template-columns:1.35fr .8fr .9fr;gap:20px;padding:20px}.record small,.record-label{display:block;color:var(--muted);font:500 8px "DM Mono";text-transform:uppercase}.record strong{display:block;margin-top:7px;font-size:13px}.record .ref{font:500 11px ui-monospace,SFMono-Regular,Menlo,monospace;word-break:break-all}.record-meta{margin-top:5px;color:var(--muted);font-size:10px}.tag{display:inline-flex;margin-top:7px;padding:4px 6px;border-radius:999px;background:#FEF3C7;color:#92400E;font:500 8px "DM Mono"}
        .flow{display:flex;align-items:center;gap:6px;padding:11px 15px;background:#0F172A;color:#CBD5E1;font:500 8px "DM Mono";overflow:auto;white-space:nowrap}.flow b{color:#93C5FD;font-weight:500}
        .section{padding:74px 0;border-top:1px solid rgba(226,232,240,.8)}.section-head{display:flex;justify-content:space-between;align-items:end;gap:40px;margin-bottom:30px}.section h2{max-width:720px;margin:10px 0 0;font-family:"Space Grotesk",Manrope,ui-sans-serif,sans-serif;font-weight:600;font-size:clamp(31px,4vw,54px);line-height:.98;letter-spacing:-.045em}.intro{max-width:480px;color:var(--muted);font-size:13px;line-height:1.7}
        .bento{display:grid;grid-template-columns:1.35fr .65fr;grid-template-rows:auto auto;gap:12px}.feature{padding:28px;border:1px solid var(--line);border-radius:12px;background:rgba(255,255,255,.88);box-shadow:0 10px 28px rgba(15,23,42,.045)}.feature.large{grid-row:span 2;min-height:410px;background:linear-gradient(145deg,#fff,#EFF6FF)}.num{font:500 9px "DM Mono";color:var(--blue)}.feature h3{margin:24px 0 9px;font-family:"Space Grotesk",Manrope,ui-sans-serif,sans-serif;font-weight:600;font-size:24px;line-height:1.08;letter-spacing:-.035em}.feature p{margin:0;color:var(--muted);font-size:12px;line-height:1.7}.mini{min-height:195px}.mini h3{margin-top:22px}
        .compare{display:grid;grid-template-columns:1fr 1fr;border:1px solid var(--line);border-radius:12px;overflow:hidden;background:#fff}.compare>article{padding:32px}.compare>article+article{border-left:1px solid var(--line);background:var(--ink);color:#fff}.compare h3{margin:0 0 20px;font:500 10px "DM Mono";letter-spacing:.12em;text-transform:uppercase}.compare ul{list-style:none;padding:0;margin:0;display:grid;gap:13px}.compare li{color:var(--body);font-size:13px;line-height:1.55}.compare li:before{content:"—";margin-right:9px;color:var(--sapphire)}.compare>article+article li{color:#CBD5E1}
        .closing{padding:90px 0}.closing-box{display:flex;align-items:center;justify-content:space-between;gap:30px;padding:38px;border-radius:14px;background:var(--blue);color:#fff;box-shadow:0 20px 50px rgba(30,64,175,.2)}.closing-box h2{margin:8px 0;font-family:"Space Grotesk",Manrope,ui-sans-serif,sans-serif;font-weight:600;font-size:34px;line-height:1.05;letter-spacing:-.04em}.closing-box p{margin:0;color:#DBEAFE;font-size:12px}.closing-box .btn{border-color:#fff;background:#fff;color:var(--blue)}
        footer{display:flex;justify-content:space-between;padding:22px 0;border-top:1px solid var(--line);color:var(--muted);font:500 8px "DM Mono";letter-spacing:.06em}
        @media(max-width:920px){.hero{grid-template-columns:1fr;min-height:auto;padding-top:60px}.hero-photo{height:clamp(220px,48vw,340px);aspect-ratio:16/8.5;background-position:center 34%}.bento{grid-template-columns:1fr}.feature.large{grid-row:auto}.feature.large .feature-image{height:clamp(190px,42vw,280px);background-position:center 42%}.section-head{display:block}.intro{margin-top:15px}.closing-box{display:block}.closing-box .btn{margin-top:20px}}
        @media(max-width:640px){.site{width:min(100% - 24px,1380px)}.top{height:auto;padding:12px 0}.topnav{position:relative}.topnav a:not(.launch),.topnav-form,.topnav .account-state{display:none}.topnav .launch{display:inline-flex}.mobile-nav-toggle{display:inline-flex;align-items:center;justify-content:center;position:relative;z-index:30;pointer-events:auto;width:42px;height:42px;padding:0;border:1px solid var(--line);border-radius:9px;background:#fff;color:var(--ink);font-size:0}.mobile-nav-toggle span,.mobile-nav-toggle span:before,.mobile-nav-toggle span:after{display:block;width:17px;height:2px;background:currentColor;content:"";position:relative}.mobile-nav-toggle span:before{position:absolute;top:-6px}.mobile-nav-toggle span:after{position:absolute;top:6px}.topnav.mobile-open .mobile-menu{display:grid}.mobile-menu{display:none;position:absolute;z-index:20;top:50px;right:0;width:min(260px,calc(100vw - 24px));padding:8px;border:1px solid var(--line);border-radius:12px;background:#fff;box-shadow:0 18px 40px rgba(15,23,42,.14)}.mobile-menu a,.mobile-menu button{display:flex!important;width:100%;box-sizing:border-box;align-items:center;justify-content:flex-start;padding:11px 12px;margin:0;border:0;background:transparent;color:var(--ink);border-radius:7px;text-align:left}.mobile-menu a:hover,.mobile-menu button:hover{background:#F3F7FC}.hero{padding:48px 0 65px}.hero-photo{height:230px;aspect-ratio:4/3;background-position:center 31%}.feature.large .feature-image{height:210px;background-position:center 40%}.feature-image.compact{height:120px;background-position:center 34%}h1{font-size:48px}.telemetry{grid-template-columns:repeat(2,1fr)}.telemetry div:nth-child(2n){border-right:0}.record{grid-template-columns:1fr}.compare{grid-template-columns:1fr}.compare>article+article{border-left:0;border-top:1px solid #334155}footer{display:block}.footer-right{margin-top:8px}}

        /* Public authentication modal. */
        .auth-modal{width:min(520px,calc(100% - 24px));max-width:none;padding:0;border:0;border-radius:16px;background:transparent;box-shadow:0 30px 90px rgba(15,23,42,.28);color:var(--ink)}.auth-modal::backdrop{background:rgba(15,23,42,.58);backdrop-filter:blur(5px)}.auth-modal-panel{position:relative;padding:30px;background:#fff;border:1px solid rgba(226,232,240,.95);border-radius:16px;max-height:calc(100vh - 24px);overflow-y:auto}.auth-modal-access{display:grid;grid-template-columns:1fr 1fr;gap:4px;margin:0 0 24px;padding:4px;border:1px solid #D8E2F0;border-radius:11px;background:#F3F7FC}.auth-modal-access button{min-height:40px;border:0;border-radius:8px;background:transparent;color:#64748B;font:800 10px Manrope,ui-sans-serif,system-ui,sans-serif;letter-spacing:.01em;cursor:pointer;transition:background-color 160ms ease,color 160ms ease,box-shadow 160ms ease,transform 160ms ease}.auth-modal-access button[aria-selected="true"]{background:#fff;color:#173B8F;box-shadow:0 2px 7px rgba(15,23,42,.09)}.auth-modal-access button:hover:not([aria-selected="true"]){color:var(--ink);background:rgba(255,255,255,.55)}.auth-modal-access button:active{transform:scale(.985)}.auth-modal-access button:focus-visible{outline:3px solid rgba(59,130,246,.18);outline-offset:1px}.auth-modal-close{position:absolute;top:14px;right:14px;width:36px;height:36px;border:1px solid var(--line);border-radius:9px;background:#fff;color:var(--muted);cursor:pointer;font-size:18px;line-height:1}.auth-modal-close:hover{color:var(--ink);background:var(--canvas)}.auth-modal-kicker{color:var(--blue);font:500 9px "DM Mono";letter-spacing:.13em;text-transform:uppercase}.auth-modal h2{margin:8px 45px 7px 0;font-family:"Space Grotesk",Manrope,ui-sans-serif,sans-serif;font-weight:600;font-size:30px;line-height:1.05;letter-spacing:-.045em}.auth-modal-copy{max-width:390px;margin:0 40px 24px 0;color:#5B6B82;font-size:12px;line-height:1.65;letter-spacing:-.005em}.auth-modal-form{display:grid;gap:15px}.auth-modal-field label{color:#1E293B;letter-spacing:-.01em}.auth-modal-field{display:grid;gap:6px}.auth-modal-field label{font-size:11px;font-weight:800}.auth-modal-field input{display:block;box-sizing:border-box;width:100%;min-height:46px;height:46px;margin:0;padding:0 13px;border:1px solid #D8E2F0;border-radius:9px;background:#FBFDFF;color:var(--ink);font:600 13px Manrope,ui-sans-serif,system-ui,sans-serif;line-height:1.2;transition:border-color 160ms ease,box-shadow 160ms ease,background-color 160ms ease}.auth-modal-field input:hover{border-color:#B9C8DC;background:#fff}.auth-modal-field input:focus{outline:none;border-color:var(--sapphire);background:#fff;box-shadow:0 0 0 3px rgba(59,130,246,.13)}.auth-modal-password{position:relative;width:100%}.auth-modal-password input{padding-right:68px}.auth-modal-password button{position:absolute;right:6px;top:7px;height:32px;padding:0 9px;border:0;border-radius:6px;background:var(--soft);color:var(--blue);font:700 10px Manrope;cursor:pointer}.auth-modal-error{margin:0;color:#B91C1C;font-size:10px;line-height:1.45}.auth-modal-help{margin-top:-5px;color:var(--muted);font-size:10px;line-height:1.45}.auth-modal-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.auth-modal-submit{width:100%;border:0;cursor:pointer}.auth-modal-switch{margin-top:20px;padding-top:17px;border-top:1px solid #E5EAF1;text-align:center;color:#64748B;font-size:11px}.auth-modal-switch button{transition:color 140ms ease}.auth-modal-switch button:hover{color:#173B8F}.auth-modal-switch button,.auth-modal-forgot{border:0;background:none;padding:0;color:var(--blue);font:800 11px Manrope;cursor:pointer}.auth-modal-forgot{display:block;margin-top:9px;text-align:right}.auth-modal[open]{animation:authModalIn 160ms ease-out}@keyframes authModalIn{from{opacity:0;transform:translateY(8px) scale(.985)}to{opacity:1;transform:none}}[data-auth-panel]{transition:opacity 170ms ease,transform 190ms ease}[data-auth-panel][hidden]{display:none}[data-auth-panel].auth-panel-enter{animation:authPanelEnter 190ms ease-out}@keyframes authPanelEnter{from{opacity:0;transform:translateY(7px)}to{opacity:1;transform:none}}@media(max-width:640px){.auth-modal{width:calc(100% - 16px);margin:auto}.auth-modal-panel{padding:24px 18px 20px;border-radius:14px}.auth-modal-grid{grid-template-columns:1fr}.auth-modal h2{font-size:25px}.auth-modal-copy{margin-right:30px}.auth-modal-close{top:10px;right:10px}.auth-modal::backdrop{backdrop-filter:none}}@media(prefers-reduced-motion:reduce){.auth-modal[open]{animation:none}}
    
        /* Director landing composition — real stock photography, stronger rhythm, compact public nav. */
        body:before{background:linear-gradient(180deg,#F4F8FD 0%,#EAF2FA 45%,#F8FAFC 100%)}
        .top{position:sticky;top:0;z-index:80;background:rgba(244,248,253,.94);box-shadow:0 1px 0 rgba(190,205,222,.72);backdrop-filter:blur(16px)}
        .hero{grid-template-columns:minmax(0,1fr) minmax(540px,.96fr);gap:clamp(32px,4.5vw,66px);min-height:auto;padding:52px 0 34px}
        .hero-photo{height:clamp(390px,38vw,520px);aspect-ratio:16/12}
        .hero .control-card{box-shadow:0 24px 60px rgba(30,63,105,.15)}
        .section{padding:70px 0}
        .service-strip{display:flex;align-items:center;justify-content:center;flex-wrap:wrap;gap:7px;padding:15px 0 9px;border-top:1px solid #D2DEEA;border-bottom:1px solid #D2DEEA}
        .service-strip span{display:inline-flex;align-items:center;min-height:30px;padding:0 11px;border:1px solid #C4D2E1;border-radius:999px;background:#F5F8FC;color:#3F607C;font:700 9px ui-monospace,SFMono-Regular,Menlo,monospace;letter-spacing:.08em}
        .nav-solutions{position:relative}
        .nav-solutions>summary{list-style:none;cursor:pointer;padding:9px 11px;border-radius:7px;color:var(--muted);font-size:11px;font-weight:700}
        .nav-solutions>summary::-webkit-details-marker{display:none}
        .nav-solutions>summary span{margin-left:4px;font-size:9px;transition:transform 140ms ease}
        .nav-solutions[open]>summary{background:#E9F0FF;color:#173F7E}
        .nav-solutions[open]>summary span{display:inline-block;transform:rotate(180deg)}
        .nav-solutions-panel{position:absolute;top:42px;left:0;z-index:50;width:230px;display:grid;gap:2px;padding:7px;border:1px solid #C4D3E3;border-radius:10px;background:#fff;box-shadow:0 18px 40px rgba(15,23,42,.14)}
        .nav-solutions-panel a{padding:10px 11px!important;border-radius:7px;color:#355570!important;background:transparent!important;font-size:10px!important}
        .nav-solutions-panel a:hover{background:#EAF2FB!important;color:#173F6E!important}
        .bento{grid-template-columns:repeat(12,minmax(0,1fr));grid-template-rows:auto auto;gap:14px}
        .feature.large{grid-column:span 7;grid-row:span 2;min-height:0}
        .feature.mini{grid-column:span 5;min-height:0}
        .feature.large .feature-image{height:300px}
        .feature.mini .feature-image{height:175px}
        .feature{padding:22px;background:#fff}
        .feature h3{margin-top:17px}
        .visual-band{display:grid;grid-template-columns:minmax(0,1.08fr) minmax(0,.92fr);margin-top:14px;border:1px solid #C4D2E1;border-radius:12px;overflow:hidden;background:#fff;box-shadow:0 12px 30px rgba(31,76,132,.06)}
        .visual-band-image{min-height:300px;background-size:cover;background-position:center}
        .visual-band-copy{display:flex;flex-direction:column;justify-content:center;padding:36px}
        .visual-band-copy h3{margin:10px 0;font-family:"Space Grotesk",Manrope,sans-serif;font-size:28px;line-height:1.06;letter-spacing:-.035em}
        .visual-band-copy p{margin:0;color:var(--muted);font-size:12px;line-height:1.7}
        .visual-band-points{display:flex;flex-wrap:wrap;gap:6px;margin-top:20px}
        .visual-band-points span{padding:6px 8px;border:1px solid #CBD8E6;border-radius:999px;background:#F5F8FC;color:#476783;font:700 8px ui-monospace,SFMono-Regular,Menlo,monospace;letter-spacing:.04em}
        .closing{padding:62px 0}
        @media(max-width:920px){
            .hero{grid-template-columns:1fr;padding-top:42px}
            .hero-photo{height:360px;aspect-ratio:16/10}
            .feature.large,.feature.mini{grid-column:1/-1;grid-row:auto}
            .visual-band{grid-template-columns:1fr}
            .visual-band-image{min-height:260px}
        }
        @media(max-width:640px){
            .service-strip{justify-content:flex-start;overflow:auto;flex-wrap:nowrap;padding:13px 0 8px}
            .service-strip span{flex:0 0 auto}
            .nav-solutions{width:100%}
            .nav-solutions>summary{display:flex!important;width:100%;box-sizing:border-box;align-items:center;justify-content:space-between;padding:11px 12px;margin:0;border-radius:7px;background:transparent;color:var(--ink)}
            .nav-solutions-panel{position:static;width:auto;margin:2px 0 2px 10px;padding:4px 0 4px 8px;border:0;border-left:2px solid #B6CCE5;border-radius:0;box-shadow:none;background:#F4F8FD}
            .nav-solutions-panel a{min-height:40px;padding:0 10px!important;display:flex!important;align-items:center}
            .hero{padding:32px 0 36px}
            .hero-photo{height:300px;aspect-ratio:4/3}
            .section{padding:56px 0}
            .visual-band-copy{padding:26px 20px}
        }
        @media(prefers-reduced-motion:reduce){.nav-solutions-panel a,.nav-solutions>summary{transition:none!important}}
</style>
</head>
<body>
<div class="site">
<header class="top">
    <a href="{{ url('/') }}" class="brand"><span class="mark">Z</span><span><strong>ZAZU</strong><span>Event Management Platform</span></span></a>
    <nav class="topnav" aria-label="Public navigation">
        <div id="mobile-public-menu" class="mobile-menu" data-mobile-menu>
            <a href="#capabilities">Product</a>
            <details class="nav-solutions">
                <summary>Solutions <span aria-hidden="true">⌄</span></summary>
                <div class="nav-solutions-panel">
                    <a href="#capabilities">Catering &amp; baking</a>
                    <a href="#capabilities">Rentals &amp; equipment</a>
                    <a href="#capabilities">Sound &amp; DJ</a>
                    <a href="#capabilities">Decor &amp; photography</a>
                    <a href="#capabilities">Events &amp; venues</a>
                </div>
            </details>
            <a href="#difference">Why Zazu</a>
            @if(!$isAuthenticated)
                <button type="button" class="register" data-auth-modal-open="register">Get started</button>
                <button type="button" class="login" data-auth-modal-open="login">Log in</button>
            @elseif($hasActiveWorkspace)
                <span class="account-state">You’re signed in</span>
                <a class="launch" href="{{ route('dashboard') }}">Open workspace <span>↗</span></a>
                <form method="POST" action="{{ route('logout') }}" class="topnav-form">@csrf<button type="submit" class="login">Sign out</button></form>
            @else
                <span class="account-state">You’re signed in</span>
                <a class="launch" href="{{ route('workspace.recovery') }}">Set up workspace <span>↗</span></a>
                <form method="POST" action="{{ route('logout') }}" class="topnav-form">@csrf<button type="submit" class="login">Sign out</button></form>
            @endif
        </div>
        <button type="button" class="mobile-nav-toggle" aria-label="Open navigation" aria-expanded="false" aria-controls="mobile-public-menu"><span></span></button>
    </nav>
</header>
<main>
<section class="hero">
<div><div class="eyebrow">Built for event-service businesses</div><h1>Run every event. <em>Stay ahead of the business.</em></h1><p class="hero-copy">From the first enquiry to the final payment, Zazu keeps customers, bookings, quotes, suppliers, equipment, preparation, costs and finance connected to the work—so your team can spend less time chasing details and more time delivering great events.</p><div class="actions">@if(!$isAuthenticated)<button type="button" class="btn primary" data-auth-modal-open="register">Create your workspace <span>→</span></button><button type="button" class="btn" data-auth-modal-open="login">Log in</button>@elseif($hasActiveWorkspace)<span class="account-state">You’re signed in · {{ $business->name }}</span><a class="btn primary" href="{{ route('dashboard') }}">Open workspace <span>→</span></a><form method="POST" action="{{ route('logout') }}" class="topnav-form">@csrf<button type="submit" class="btn">Sign out</button></form>@else<span class="account-state">You’re signed in</span><a class="btn primary" href="{{ route('workspace.recovery') }}">Set up workspace <span>→</span></a><form method="POST" action="{{ route('logout') }}" class="topnav-form">@csrf<button type="submit" class="btn">Sign out</button></form>@endif
<a class="btn" href="#capabilities">See how it works</a></div><div class="hero-note">SOUTH AFRICAN BUSINESS · EVENTS · CATERING · HIRE · COMMERCIAL · CONTROL</div></div>
<div class="control-card" aria-label="Live workspace preview">
<div class="hero-photo hero-photo--hero" role="img" aria-label="South African outdoor catered event with guests and service team" style="background-image:url('{{ $landingImages['hero'] }}');"><span>Real event operations</span></div>
<div class="control-head"><span>ZAZU EMP / WORKSPACE VIEW</span><span class="online"><i></i>WORKSPACE</span></div>
<div class="telemetry">
<div><small>All work</small><strong>{{ str_pad((string)$telemetry['all_work'],2,'0',STR_PAD_LEFT) }}</strong></div><div><small>Today</small><strong>{{ str_pad((string)$telemetry['today'],2,'0',STR_PAD_LEFT) }}</strong></div><div><small>Next 7d</small><strong>{{ str_pad((string)$telemetry['next_7_days'],2,'0',STR_PAD_LEFT) }}</strong></div><div><small>In progress</small><strong>{{ str_pad((string)$telemetry['in_progress'],2,'0',STR_PAD_LEFT) }}</strong></div><div><small>Drafts</small><strong>{{ str_pad((string)$telemetry['drafts'],2,'0',STR_PAD_LEFT) }}</strong></div>
</div>
<div class="record"><div><span class="record-label">Operational record</span><strong>{{ $telemetry['record_name'] }}</strong><span class="record-meta">{{ $telemetry['record_meta'] }}</span><span class="tag">{{ $telemetry['status'] }}</span></div><div><span class="record-label">Schedule</span><strong>{{ $telemetry['date'] }}</strong><span class="record-meta">{{ $telemetry['event_type'] }}</span></div><div><span class="record-label">Reference</span><strong class="ref">{{ $telemetry['reference'] }}</strong><span class="record-meta">{{ $telemetry['reference_meta'] }}</span></div></div>
<div class="flow"><b>JOB</b> → CUSTOMER → QUOTE → PREP → RESOURCES → COSTS → FINANCE</div>
</div>
</section>
<div class="service-strip" aria-label="Zazu business coverage"><span>EVENTS</span><span>CATERING</span><span>RENTALS</span><span>SOUND &amp; DJ</span><span>DECOR</span><span>PHOTOGRAPHY</span><span>BAKING</span></div>
<section class="section" id="capabilities"><div class="section-head"><div><div class="eyebrow">Made for the work</div><h2>One event. A clear view from enquiry to payment.</h2></div><p class="intro">Keep the moving parts together as the business grows—from customer and quote through preparation, purchasing, costs and finance. Everyone works from the same operational record.</p></div>
<div class="bento">
<article class="feature large">
    <div class="feature-image feature-image--operations" style="background-image:url('{{ $landingImages['operations'] }}');"><span>Catering &amp; service</span></div>
    <span class="num">01 / OPERATIONS</span>
    <h3>Keep the whole booking together.</h3>
    <p>Customer details, event dates, requirements, quotes, preparation and costs stay connected to the job from the first conversation through delivery.</p>
</article>
<article class="feature mini">
    <div class="feature-image feature-image--resources compact" style="background-image:url('{{ $landingImages['resources'] }}');"><span>Sound &amp; event setup</span></div>
    <span class="num">02 / RESOURCES</span>
    <h3>Know what the event needs.</h3>
    <p>Keep services, equipment, suppliers and purchasing tied to the event, whether you are arranging catering, sound, staging or hire.</p>
</article>
<article class="feature mini">
    <div class="feature-image feature-image--control compact" style="background-image:url('{{ $landingImages['control'] }}');"><span>Event venue</span></div>
    <span class="num">03 / CONTROL</span>
    <h3>Keep the commercial picture clear.</h3>
    <p>See invoices, payments, expenses and costs with a direct line back to the event decisions that created them.</p>
</article>
<article class="feature mini">
    <div class="feature-image feature-image--venue compact" style="background-image:url('{{ $landingImages['venue'] }}');"><span>Outdoor event setup</span></div>
    <span class="num">04 / PLAN</span>
    <h3>Give every event a visible plan.</h3>
    <p>Keep dates, spaces, setup needs and the next actions visible without making the operator hunt across separate admin tools.</p>
</article>
<article class="feature mini">
    <div class="feature-image feature-image--decor compact" style="background-image:url('{{ $landingImages['decor'] }}');"><span>Decor &amp; presentation</span></div>
    <span class="num">05 / DELIVERY</span>
    <h3>Make the final setup part of the record.</h3>
    <p>Keep presentation, setup details and service decisions close to the same job instead of splitting them across disconnected notes.</p>
</article>
</div></section>
<section class="section" id="difference"><div class="section-head"><div><div class="eyebrow">A better working record</div><h2>Replace scattered admin with one working record.</h2></div></div><div class="compare"><article><h3>Without a central record</h3><ul><li>WhatsApp messages, notebooks and spreadsheets each hold a piece of the event.</li><li>Quotes, preparation and delivery details can drift apart.</li><li>Supplier, equipment and hire decisions can disappear into the admin.</li><li>Costs can reach finance long after the decision was made.</li></ul></article><article><h3>With Zazu</h3><ul><li>One event record becomes the reference point for the team.</li><li>Operational and commercial context stays connected.</li><li>Purchasing and costs can be traced back to the event.</li><li>Finance can see the story behind the numbers.</li></ul></article></div></section>
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
    const nav = document.querySelector('.topnav');
    const toggle = document.querySelector('.mobile-nav-toggle');
    const menu = document.querySelector('[data-mobile-menu]');
    if (nav && toggle && menu) {
        const setOpen = (open) => {
            nav.classList.toggle('mobile-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
        };
        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setOpen(!nav.classList.contains('mobile-open'));
        });
        menu.addEventListener('click', (event) => {
            const item = event.target.closest('a,button,summary');
            if (item && !item.closest('.nav-solutions')) setOpen(false);
        });
        document.addEventListener('click', (event) => {
            if (!nav.contains(event.target)) setOpen(false);
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') setOpen(false);
        });
    }
})();
</script>
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