<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0B0E14">
    <meta name="description" content="Zazu EMP — event operations management for jobs, catering, equipment, quotes and finance.">
    <title>Zazu EMP · Event Operations Management</title>
    <style>
        :root {
            --canvas:#0B0E14; --surface:#11161E; --surface-2:#171D27; --ink:#F2F2ED;
            --muted:#9BA5B4; --line:#2A323E; --orange:#FF4500; --green:#00E676;
            --amber:#FFB300; --red:#FF1744; --blue:#69A7FF;
        }
        * { box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body { margin:0; background:var(--canvas); color:var(--ink); font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; }
        a { color:inherit; text-decoration:none; }
        button { font:inherit; }
        .mono { font-family:"JetBrains Mono",ui-monospace,SFMono-Regular,Menlo,monospace; }
        .page { min-height:100vh; background:
            linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),
            linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);
            background-size:48px 48px;
        }
        .shell { width:min(1180px,calc(100% - 40px)); margin:auto; }
        .topbar { height:76px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--line); }
        .brand { display:flex; align-items:center; gap:12px; font-weight:900; letter-spacing:-.04em; }
        .mark { width:34px;height:34px;display:grid;place-items:center;background:var(--orange);color:#fff;font-weight:950; }
        .brand small { display:block;color:var(--muted);font:600 10px/1 "JetBrains Mono",monospace;letter-spacing:.12em;text-transform:uppercase;margin-top:4px; }
        .top-actions { display:flex;gap:10px;align-items:center; }
        .btn { min-height:42px;padding:0 17px;border:1px solid var(--line);display:inline-flex;align-items:center;justify-content:center;gap:9px;font-weight:800; }
        .btn-primary { background:var(--orange);border-color:var(--orange);color:#fff; }
        .btn-primary:hover { background:#e83e00; }
        .btn-ghost:hover { border-color:#5A6677;background:var(--surface); }
        .hero { padding:82px 0 70px; display:grid;grid-template-columns:minmax(0,1fr) minmax(430px,.95fr);gap:60px;align-items:center; }
        .eyebrow { color:var(--orange);font:800 11px/1 "JetBrains Mono",monospace;letter-spacing:.16em;text-transform:uppercase;display:flex;align-items:center;gap:9px; }
        .eyebrow:before { content:"";width:28px;height:2px;background:var(--orange); }
        h1 { margin:18px 0 20px;font-size:clamp(44px,6vw,76px);line-height:.94;letter-spacing:-.065em;max-width:720px; }
        .hero-copy { color:#B8C0CC;font-size:18px;line-height:1.65;max-width:650px; }
        .hero-actions { display:flex;flex-wrap:wrap;gap:10px;margin-top:30px; }
        .hero-note { margin-top:18px;color:#778292;font:500 11px/1.5 "JetBrains Mono",monospace; }
        .telemetry { border:1px solid #36404E;background:rgba(17,22,30,.92);box-shadow:0 24px 70px rgba(0,0,0,.35); }
        .telemetry-head { padding:12px 14px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;gap:15px;font:700 10px/1.2 "JetBrains Mono",monospace;letter-spacing:.08em; }
        .online { color:var(--green);display:flex;gap:7px;align-items:center; }
        .online i { width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 12px var(--green); }
        .metrics { display:grid;grid-template-columns:repeat(5,1fr); }
        .metric { padding:16px 12px;border-right:1px solid var(--line); }
        .metric:last-child { border-right:0; }
        .metric-label { color:#758092;font:700 8px/1.2 "JetBrains Mono",monospace;text-transform:uppercase; }
        .metric-value { font:800 26px/1 "JetBrains Mono",monospace;margin-top:8px; }
        .record { border-top:1px solid var(--line);padding:18px;display:grid;grid-template-columns:1.5fr 1fr .8fr;gap:15px; }
        .record-label { color:#6F7A89;font:700 8px/1.2 "JetBrains Mono",monospace;text-transform:uppercase; }
        .record-main { font-weight:850;margin-top:7px; }
        .record-meta { color:#9BA5B4;font-size:12px;margin-top:4px; }
        .badge { display:inline-flex;align-items:center;border:1px solid var(--amber);color:var(--amber);padding:4px 7px;font:800 9px/1 "JetBrains Mono",monospace;margin-top:7px; }
        .matrix-foot { border-top:1px solid var(--line);padding:10px 14px;color:#6F7A89;font:600 9px/1.4 "JetBrains Mono",monospace; }
        .section { padding:70px 0;border-top:1px solid var(--line); }
        .section-head { display:flex;justify-content:space-between;gap:30px;align-items:end;margin-bottom:28px; }
        .section h2 { margin:8px 0 0;font-size:36px;letter-spacing:-.045em; }
        .section-intro { max-width:600px;color:var(--muted);line-height:1.6; }
        .pillars { display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--line);border-left:1px solid var(--line); }
        .pillar { min-height:220px;padding:24px;border-right:1px solid var(--line);border-bottom:1px solid var(--line);background:rgba(17,22,30,.65); }
        .pillar-num { color:var(--orange);font:800 11px "JetBrains Mono",monospace; }
        .pillar h3 { margin:48px 0 9px;font-size:21px; }
        .pillar p { margin:0;color:var(--muted);line-height:1.6;font-size:14px; }
        .compare { display:grid;grid-template-columns:1fr 1fr;border:1px solid var(--line); }
        .compare > div { padding:28px;min-height:260px; }
        .compare > div + div { border-left:1px solid var(--line);background:#0F141B; }
        .compare h3 { margin:0 0 18px;font-size:13px;text-transform:uppercase;letter-spacing:.1em;font-family:"JetBrains Mono",monospace; }
        .compare ul { margin:0;padding:0;list-style:none;display:grid;gap:12px;color:#B6BFCC;font-size:14px;line-height:1.5; }
        .compare li:before { content:"/";color:var(--orange);font-family:monospace;font-weight:900;margin-right:9px; }
        .inspector { display:grid;grid-template-columns:1.1fr .9fr;border:1px solid var(--line); }
        .inspector-main,.inspector-side { padding:26px; }
        .inspector-side { border-left:1px solid var(--line);background:var(--surface); }
        .ledger-row { display:flex;justify-content:space-between;gap:20px;padding:13px 0;border-bottom:1px solid var(--line);font-size:13px; }
        .ledger-row:last-child { border-bottom:0; }
        .status { font:800 10px "JetBrains Mono",monospace; }
        .status.ok { color:var(--green); }.status.warn { color:var(--amber); }.status.alert { color:var(--red); }
        .cta { padding:70px 0 90px; }
        .cta-box { border:1px solid var(--line);padding:36px;background:linear-gradient(110deg,#151A22,#0E1218);display:flex;justify-content:space-between;gap:30px;align-items:center; }
        .cta-box h2 { margin:0 0 8px;font-size:32px;letter-spacing:-.04em; }
        .cta-box p { margin:0;color:var(--muted); }
        footer { border-top:1px solid var(--line);padding:22px 0;color:#667181;font:600 10px "JetBrains Mono",monospace;display:flex;justify-content:space-between;gap:20px; }
        @media (max-width:900px) { .hero{grid-template-columns:1fr;padding-top:55px}.pillars{grid-template-columns:1fr}.inspector{grid-template-columns:1fr}.inspector-side{border-left:0;border-top:1px solid var(--line)} }
        @media (max-width:650px) { .shell{width:min(100% - 24px,1180px)}.topbar{height:auto;padding:16px 0;gap:15px}.top-actions .btn-ghost{display:none}.hero{padding:45px 0}.metrics{grid-template-columns:repeat(2,1fr)}.metric{border-bottom:1px solid var(--line)}.record{grid-template-columns:1fr}.compare{grid-template-columns:1fr}.compare>div+div{border-left:0;border-top:1px solid var(--line)}.section{padding:52px 0}.cta-box{display:block}.cta-box .btn{margin-top:20px}footer{display:block}.footer-right{margin-top:8px} }
        @media (prefers-reduced-motion:no-preference) { .telemetry{animation:rise .7s ease both}.online i{animation:pulse 1.8s infinite}@keyframes rise{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}@keyframes pulse{50%{opacity:.45;box-shadow:0 0 4px var(--green)}} }
    </style>
</head>
<body>
<div class="page">
    <div class="shell">
        <header class="topbar">
            <a class="brand" href="{{ url('/') }}" aria-label="Zazu EMP home">
                <span class="mark">Z</span>
                <span>ZAZU <small>Event Management Platform</small></span>
            </a>
            <div class="top-actions">
                <a class="btn btn-ghost" href="#capabilities">Capabilities</a>
                <a class="btn btn-primary" href="{{ route('login') }}">Launch Workspace →</a>
            </div>
        </header>

        <main>
            <section class="hero">
                <div>
                    <div class="eyebrow">Operational command system</div>
                    <h1>Zero-Friction Staging.<br>From Draft Quote to Teardown.</h1>
                    <p class="hero-copy">The operational workspace for event managers, caterers and equipment-hire teams. Keep jobs, customers, quotes, resources, preparation and financial activity connected in one system.</p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="{{ route('login') }}">Enter Rosco ICT Workspace →</a>
                        <a class="btn btn-ghost" href="#live-preview">Simulate Dispatch Load</a>
                    </div>
                    <div class="hero-note mono">LOCAL WORKSPACE :: OPERATIONS / RESOURCES / FINANCE / REPORTING</div>
                </div>

                <div class="telemetry" id="live-preview" aria-label="Live operational preview">
                    <div class="telemetry-head">
                        <span>LIVE TELEMETRY :: ROSCO ICT WORKSPACE</span>
                        <span class="online"><i></i> STATUS: ONLINE</span>
                    </div>
                    <div class="metrics">
                        <div class="metric"><div class="metric-label">All work</div><div class="metric-value">01</div></div>
                        <div class="metric"><div class="metric-label">Today</div><div class="metric-value">00</div></div>
                        <div class="metric"><div class="metric-label">Next 7d</div><div class="metric-value">01</div></div>
                        <div class="metric"><div class="metric-label">In progress</div><div class="metric-value">00</div></div>
                        <div class="metric"><div class="metric-label">Drafts</div><div class="metric-value">01</div></div>
                    </div>
                    <div class="record">
                        <div><div class="record-label">Record</div><div class="record-main">Rosscore Labs (Pty) Ltd</div><div class="record-meta">Isaac Junior Lehlogonolo Maluleka</div><span class="badge">DRAFT</span></div>
                        <div><div class="record-label">Schedule</div><div class="record-main">30 Sep 2026</div><div class="record-meta">Birthday event</div></div>
                        <div><div class="record-label">Reference</div><div class="record-main mono">ZAZU-PIEXWGKF</div><div class="record-meta">Deposit pending</div></div>
                    </div>
                    <div class="matrix-foot">WORKLOAD PREVIEW // QUOTES → PREPARATION → RESOURCES → EXECUTION → FINANCE</div>
                </div>
            </section>

            <section class="section" id="capabilities">
                <div class="section-head">
                    <div><div class="eyebrow">Built for execution</div><h2>One operational record. Every moving part.</h2></div>
                    <p class="section-intro">Zazu is designed around the work itself — not a collection of disconnected admin screens. Operational, commercial and resource information can stay tied to the same job lifecycle.</p>
                </div>
                <div class="pillars">
                    <article class="pillar"><div class="pillar-num">01 / OPERATIONS</div><h3>Centralized Job Operations</h3><p>Manage active jobs, drafts and completed work with clear status, scheduling, preparation and action context.</p></article>
                    <article class="pillar"><div class="pillar-num">02 / RESOURCES</div><h3>Resources & Equipment</h3><p>Track physical assets, inventory, purchasing and service capabilities against the operational work that needs them.</p></article>
                    <article class="pillar"><div class="pillar-num">03 / CONTROL</div><h3>Workload Visibility</h3><p>See upcoming execution load, preparation pressure, commercial activity and financial movement without hunting across systems.</p></article>
                </div>
            </section>

            <section class="section">
                <div class="section-head"><div><div class="eyebrow">Operational discipline</div><h2>Spreadsheet vs. command centre.</h2></div></div>
                <div class="compare">
                    <div><h3>Old way</h3><ul><li>WhatsApp threads and voice notes become the unofficial job system.</li><li>Quotes, return sheets and preparation status drift apart.</li><li>Equipment availability is checked manually against competing jobs.</li><li>Costs and purchasing activity arrive in finance after the fact.</li></ul></div>
                    <div><h3>Zazu EMP</h3><ul><li>One operational record anchors the job lifecycle.</li><li>Quotes, requirements, preparation and costs stay connected.</li><li>Resources can be inspected in the context of operational work.</li><li>Commercial and financial activity has a clearer operational trail.</li></ul></div>
                </div>
            </section>

            <section class="section">
                <div class="section-head"><div><div class="eyebrow">Resource load inspector</div><h2>Know what is committed before dispatch.</h2></div></div>
                <div class="inspector">
                    <div class="inspector-main">
                        <div class="ledger-row"><span>LED Cans × 12</span><span class="status ok">AVAILABLE</span></div>
                        <div class="ledger-row"><span>Subwoofers × 2</span><span class="status warn">CHECK LOAD</span></div>
                        <div class="ledger-row"><span>Catering Package B</span><span class="status ok">ALLOCATABLE</span></div>
                        <div class="ledger-row"><span>30 Sep · competing booking</span><span class="status alert">CONFLICT</span></div>
                    </div>
                    <aside class="inspector-side">
                        <div class="record-label">Operational principle</div>
                        <h3>Resource decisions belong in the workflow.</h3>
                        <p class="section-intro">The aim is not another inventory spreadsheet. It is operational visibility: what the job needs, what is available, what is committed, and where attention is required.</p>
                    </aside>
                </div>
            </section>

            <section class="cta">
                <div class="cta-box">
                    <div><div class="eyebrow">Ready room</div><h2>Open the workspace when the work starts.</h2><p>Sign in to continue into the Zazu EMP operational workspace.</p></div>
                    <a class="btn btn-primary" href="{{ route('login') }}">Launch Workspace →</a>
                </div>
            </section>
        </main>

        <footer><span>ZAZU EMP · ROSCO ICT</span><span class="footer-right">EVENT OPERATIONS / CATERING / EQUIPMENT HIRE</span></footer>
    </div>
</div>
</body>
</html>
