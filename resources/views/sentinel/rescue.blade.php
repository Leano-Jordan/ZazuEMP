<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="referrer" content="no-referrer">
    <title>Sentinel Rescue · Zazu EMP</title>
    <style nonce="{{ request()->attributes->get('cspNonce') }}">
        :root{font-family:Inter,"Segoe UI",system-ui,sans-serif;color:#172638;background:#edf2f6;font-synthesis:none;text-rendering:optimizeLegibility}
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;padding:clamp(10px,1.8vw,24px)}
        main{width:min(100%,1480px);margin:0 auto}
        .topbar{position:sticky;top:8px;z-index:5;display:flex;justify-content:space-between;align-items:center;gap:14px;min-height:58px;padding:9px 14px;margin-bottom:18px;border:1px solid #d6e0e9;border-radius:15px;background:rgba(255,255,255,.96);box-shadow:0 6px 22px rgba(25,42,60,.06);backdrop-filter:blur(12px)}
        .brand{display:flex;align-items:center;gap:10px;min-width:0;color:#203b57;text-decoration:none}
        .mark{display:grid;place-items:center;flex:0 0 34px;height:34px;border-radius:10px;background:#203b57;color:#fff;font-weight:850}
        .brand-lockup{display:grid;gap:1px;line-height:1}
        .brand-lockup strong{font-size:.9rem;letter-spacing:.08em}
        .brand-lockup span{font-size:.67rem;color:#64798e;letter-spacing:.14em;font-weight:750}
        .top-actions{display:flex;align-items:center;gap:12px}
        .context{color:#64778a;font-size:.72rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
        .workspace-link{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;padding:0 14px;border:1px solid #cbd9e5;border-radius:10px;background:#f5f9fc;color:#1d5c88;font-size:.84rem;font-weight:750;text-decoration:none;white-space:nowrap}
        .workspace-link:hover{background:#eaf3fa;border-color:#9fb9ce}
        .page-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin:0 2px 14px}
        .eyebrow{color:#60768c;font-size:.68rem;font-weight:850;letter-spacing:.14em;text-transform:uppercase}
        h1{margin:.2rem 0 0;color:#18324b;font-size:clamp(1.45rem,2.4vw,2rem);line-height:1.08;letter-spacing:-.04em}
        .page-copy{margin:.35rem 0 0;color:#607184;font-size:.84rem;line-height:1.45}
        .overview{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 15px;margin-bottom:12px;border:1px solid #d6e0e9;border-radius:14px;background:#fff}
        .overview-copy{min-width:0}
        .overview-copy strong{display:block;font-size:.91rem;color:#21384f}
        .overview-copy span{display:block;margin-top:3px;color:#66798c;font-size:.76rem}
        .overview-meta{display:flex;align-items:center;justify-content:flex-end;gap:7px 13px;flex-wrap:wrap;color:#6b7c8e;font-size:.72rem;text-align:right}
        .status{display:inline-flex;align-items:center;justify-content:center;border:1px solid #cdd7e1;border-radius:999px;padding:5px 10px;font-size:.68rem;font-weight:850;letter-spacing:.055em;white-space:nowrap}
        .status-healthy{color:#176342;background:#eaf6ef;border-color:#badfc9}
        .status-attention{color:#85580e;background:#fff5dd;border-color:#eed79d}
        .status-failed{color:#9a3030;background:#fff0f0;border-color:#ecc0c0}
        .status-unknown,.status-not_checked{color:#596d82;background:#f3f6f8}
        .checks{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,270px),1fr));gap:10px}
        .check{display:flex;flex-direction:column;gap:7px;min-width:0;padding:13px 14px;border:1px solid #dbe3eb;border-radius:13px;background:#fff;box-shadow:0 4px 14px rgba(25,42,60,.035)}
        .check-top{display:flex;align-items:center;justify-content:space-between;gap:8px}
        .check-group{overflow:hidden;color:#718297;font-size:.63rem;font-weight:850;letter-spacing:.1em;text-transform:uppercase;text-overflow:ellipsis;white-space:nowrap}
        .check h2{margin:0;color:#21384f;font-size:.9rem;font-weight:760;line-height:1.25}
        .check p{margin:0;color:#5e7082;font-size:.79rem;line-height:1.45;overflow-wrap:anywhere}
        .facts{display:flex;gap:6px 11px;flex-wrap:wrap;color:#687b8e;font-size:.69rem}
        .facts strong{color:#344b61;font-weight:750}
        .details{margin-top:1px;color:#405a70;font-size:.74rem}
        .details summary{cursor:pointer;font-weight:750}
        .details ul{padding-left:17px;max-height:120px;overflow:auto}
        .notice{padding:8px 10px;border-left:3px solid #7798b3;border-radius:6px;background:#f2f7fb;color:#38536c!important;font-size:.74rem!important}
        .foot{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;padding:12px 3px 2px;color:#718194;font-size:.69rem}
        @media(max-width:650px){
            body{padding:9px}
            .topbar{top:5px;min-height:54px;padding:8px 10px;margin-bottom:14px}
            .context{display:none}
            .workspace-link{min-height:38px;padding:0 10px;font-size:.78rem}
            .page-head{display:block;margin-bottom:11px}
            .page-copy{max-width:42rem}
            .overview{align-items:flex-start}
            .overview-meta{max-width:45%;font-size:.67rem}
            .checks{grid-template-columns:1fr}
        }
        @media(prefers-reduced-motion:no-preference){.workspace-link{transition:background .15s ease,border-color .15s ease}}
    </style>
</head>
<body>
<main>
    <nav class="topbar" aria-label="Workspace navigation">
        <a class="brand" href="{{ route('dashboard') }}" aria-label="Return to Zazu workspace dashboard">
            <span class="mark" aria-hidden="true">Z</span>
            <span class="brand-lockup"><strong>ZAZU EMP</strong><span>SENTINEL RESCUE</span></span>
        </a>
        <div class="top-actions">
            <span class="context">Private owner view</span>
            <a class="workspace-link" href="{{ route('dashboard') }}"><span aria-hidden="true">←</span> Back to workspace</a>
        </div>
    </nav>

    <header class="page-head">
        <div>
            <div class="eyebrow">Owner diagnostics</div>
            <h1>Sentinel Rescue</h1>
            <p class="page-copy">A read-only health snapshot for this installation. Available locally without an internet connection.</p>
        </div>
    </header>

    <section class="overview" aria-labelledby="health-heading">
        <div class="overview-copy">
            <strong id="health-heading">Installation health</strong>
            <span>Database · local files · diagnostic evidence</span>
        </div>
        <div class="overview-meta">
            <span class="status status-{{ $health['status'] }}">{{ strtoupper(str_replace('_', ' ', $health['status'])) }}</span>
            <span>Checked {{ $health['checked_at'] }}</span>
            <span>PHP {{ $phpVersion }} · Laravel {{ $laravelVersion }}</span>
        </div>
    </section>

    <section class="checks" aria-label="Installation health checks">
        @foreach($health['checks'] as $check)
            <article class="check">
                <div class="check-top">
                    <span class="check-group">{{ $check['group'] ?? 'Sentinel' }}</span>
                    <span class="status status-{{ $check['status'] }}">{{ strtoupper(str_replace('_', ' ', $check['status'])) }}</span>
                </div>
                <h2>{{ $check['label'] }}</h2>
                <p>{{ $check['message'] }}</p>
                @if(isset($check['driver']) && $check['driver'])
                    <div class="facts"><span>Database driver: <strong>{{ $check['driver'] }}</strong></span></div>
                @endif
                @if(isset($check['records']))
                    <div class="facts">
                        <span><strong>{{ number_format($check['records']) }}</strong> readable records</span>
                        <span><strong>{{ number_format($check['malformed_lines']) }}</strong> malformed lines</span>
                        <span><strong>{{ number_format($check['size_bytes']) }}</strong> bytes</span>
                    </div>
                @endif
                @if(isset($check['pending_count']) && $check['pending_count'] > 0)
                    <div class="facts"><span><strong>{{ number_format($check['pending_count']) }}</strong> pending migration(s)</span></div>
                    @if(!empty($check['pending_migrations']))
                        <details class="details">
                            <summary>View migration names ({{ count($check['pending_migrations']) }} shown)</summary>
                            <ul>
                                @foreach($check['pending_migrations'] as $migration)
                                    <li><code>{{ $migration }}</code></li>
                                @endforeach
                            </ul>
                        </details>
                    @endif
                @endif
                @if(!empty($check['error_code']))
                    <div class="facts"><span>Error code: <strong>{{ $check['error_code'] }}</strong></span></div>
                @endif
                @if(!empty($check['failure_class']))
                    <div class="facts"><span>Failure category: <strong>{{ $check['failure_class'] }}</strong></span></div>
                @endif
                @if(!empty($check['reference']) && \Illuminate\Support\Str::isUuid($check['reference']))
                    <div class="facts">
                        <span>Reference: <code>{{ $check['reference'] }}</code></span>
                        @if(($check['error_code'] ?? null) === 'DB-001')
                            <a href="{{ route('support.incidents.show', ['requestId' => $check['reference']]) }}">Download safe DB report</a>
                        @endif
                    </div>
                @endif
                @if(!empty($check['next_step']))
                    <p class="notice">{{ $check['next_step'] }}</p>
                @endif
            </article>
        @endforeach
    </section>

    <footer class="foot">
        <span>Owner access · active business only · read-only checks</span>
        <span>Sentinel Rescue · No external services contacted</span>
    </footer>
</main>
</body>
</html>
