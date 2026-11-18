<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="referrer" content="no-referrer">
    <title>Sentinel Rescue · Zazu EMP</title>
    <style nonce="{{ request()->attributes->get('cspNonce') }}">
        :root{font-family:Inter,"Segoe UI",system-ui,sans-serif;color:#172638;background:#f2f5f8;font-synthesis:none;text-rendering:optimizeLegibility}
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;padding:clamp(18px,4vw,44px)}
        main{width:min(100%,920px);margin:0 auto}
        header,.panel{border:1px solid #d8e0e8;border-radius:18px;background:#fff;box-shadow:0 10px 32px rgba(25,42,60,.05)}
        header{padding:clamp(22px,4vw,34px);margin-bottom:16px}
        .brand{display:flex;align-items:center;gap:10px;font-weight:800;letter-spacing:.04em}
        .mark{display:grid;place-items:center;width:34px;height:34px;border-radius:10px;background:#203b57;color:#fff}
        .eyebrow{margin-top:26px;color:#596d82;font-size:.72rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
        h1{margin:.45rem 0 .7rem;font-size:clamp(2rem,6vw,3rem);line-height:1.02;letter-spacing:-.045em}
        h2{margin:0;font-size:1.05rem}
        p{line-height:1.6;color:#5c6c7d}
        .panel{padding:clamp(18px,3vw,26px);margin-bottom:16px}
        .status-head{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap}
        .status{display:inline-flex;align-items:center;border:1px solid #cdd7e1;border-radius:999px;padding:5px 11px;font-size:.75rem;font-weight:800;letter-spacing:.04em}
        .status-healthy{color:#176342;background:#eaf6ef;border-color:#badfc9}
        .status-attention{color:#85580e;background:#fff5dd;border-color:#eed79d}
        .status-failed{color:#9a3030;background:#fff0f0;border-color:#ecc0c0}
        .status-unknown,.status-not_checked{color:#596d82;background:#f3f6f8}
        .checks{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:18px}
        .check{padding:14px;border:1px solid #e0e6ec;border-radius:12px;min-width:0}
        .check h3{margin:0;font-size:.9rem}
        .check p{margin:.45rem 0 0;font-size:.82rem}
        .check-group{margin-bottom:6px;color:#6b7c8e;font-size:.68rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
        .facts{display:flex;gap:12px;flex-wrap:wrap;margin-top:10px;color:#66788a;font-size:.74rem}
        .facts a{color:#1c5c9a;font-weight:700}
        .details{margin-top:10px;color:#53687c;font-size:.78rem;overflow-wrap:anywhere}
        .details summary{cursor:pointer;font-weight:700}
        .details ul{padding-left:18px;max-height:160px;overflow:auto}
        .meta{display:flex;gap:12px;flex-wrap:wrap;color:#6b7c8e;font-size:.76rem}
        .notice{padding:14px 16px;border-left:3px solid #607b96;background:#f4f7fa;border-radius:8px;font-size:.86rem}
        .foot{padding:8px 2px;color:#788797;font-size:.75rem}
        @media(max-width:620px){.checks{grid-template-columns:1fr}.status-head{align-items:flex-start}}
    </style>
</head>
<body>
<main>
    <header>
        <div class="brand"><span class="mark" aria-hidden="true">Z</span><span>ZAZU EMP · SENTINEL</span></div>
        <div class="eyebrow">Private installation access</div>
        <h1>Sentinel Rescue</h1>
        <p>This is a separate diagnostic entry point. It does not require your normal Zazu sign-in or a database-backed session, and it does not need internet access.</p>
        <div class="meta">
            <span>PHP {{ $phpVersion }}</span>
            <span>Laravel {{ $laravelVersion }}</span>
            <span>Checked {{ $health['checked_at'] }}</span>
        </div>
    </header>

    <section class="panel" aria-labelledby="health-heading">
        <div class="status-head">
            <div>
                <h2 id="health-heading">Installation health snapshot</h2>
                <p>Read-only checks for database access and migration history, private storage, installed-app files and Sentinel's own evidence store.</p>
            </div>
            <span class="status status-{{ $health['status'] }}">{{ strtoupper(str_replace('_', ' ', $health['status'])) }}</span>
        </div>

        <div class="checks">
            @foreach($health['checks'] as $check)
                <article class="check">
                    <div class="check-group">{{ $check['group'] ?? 'Sentinel' }}</div>
                    <div class="status-head">
                        <h3>{{ $check['label'] }}</h3>
                        <span class="status status-{{ $check['status'] }}">{{ strtoupper(str_replace('_', ' ', $check['status'])) }}</span>
                    </div>
                    <p>{{ $check['message'] }}</p>
                    @if(isset($check['driver']) && $check['driver'])
                        <div class="facts"><span>Database driver: {{ $check['driver'] }}</span></div>
                    @endif
                    @if(isset($check['records']))
                        <div class="facts">
                            <span>{{ number_format($check['records']) }} readable records</span>
                            <span>{{ number_format($check['malformed_lines']) }} malformed lines</span>
                            <span>{{ number_format($check['size_bytes']) }} bytes</span>
                        </div>
                    @endif
                    @if(isset($check['pending_count']) && $check['pending_count'] > 0)
                        <div class="facts"><span>{{ number_format($check['pending_count']) }} pending migration(s)</span></div>
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
                        <div class="facts"><span>Error code: {{ $check['error_code'] }}</span></div>
                    @endif
                    @if(!empty($check['failure_class']))
                        <div class="facts"><span>Safe failure category: {{ $check['failure_class'] }}</span></div>
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
        </div>
    </section>

    <section class="panel">
        <h2>How to use this page</h2>
        <p class="notice">Bookmark this private link while Zazu is working normally. It can remain available when normal sign-in or the business database is unavailable, provided the local Zazu web process and application bootstrap are still running.</p>
        <p>The page uses the installation's signed link as a private access key. Anyone who receives that link can view this installation-level health snapshot. Keep it private and renew it from the System menu when it expires.</p>
        <p>Sentinel does not apply migrations, restore backups, reset the database or modify business records. Pending migrations are evidence to investigate, not an instruction to repair automatically.</p>
        <p>A present service worker or manifest does not prove that a phone's browser cache, local records or sync queue are healthy. Those require separate device-side checks.</p>
    </section>

    <div class="foot">Zazu EMP · Sentinel Rescue · No external services contacted</div>
</main>
</body>
</html>
