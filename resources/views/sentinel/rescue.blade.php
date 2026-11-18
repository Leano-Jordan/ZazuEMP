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
        .topbar{position:sticky;top:8px;z-index:5;display:flex;justify-content:space-between;align-items:center;gap:14px;min-height:58px;padding:9px 14px;margin-bottom:18px;border:1px solid #aebdcb;border-radius:15px;background:rgba(255,255,255,.97);box-shadow:0 6px 22px rgba(25,42,60,.08);backdrop-filter:blur(12px)}
        .brand{display:flex;align-items:center;gap:10px;min-width:0;color:#203b57;text-decoration:none}
        .mark{display:grid;place-items:center;flex:0 0 34px;height:34px;border-radius:10px;background:#203b57;color:#fff;font-weight:850}
        .brand-lockup{display:grid;gap:1px;line-height:1}
        .brand-lockup strong{font-size:.9rem;letter-spacing:.08em}
        .brand-lockup span{font-size:.67rem;color:#64798e;letter-spacing:.14em;font-weight:750}
        .top-actions{display:flex;align-items:center;gap:12px}
        .context{color:#64778a;font-size:.72rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
        .workspace-link,.refresh-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;padding:0 14px;border:1px solid #9aafc2;border-radius:10px;background:#f5f9fc;color:#1d527a;font-size:.84rem;font-weight:750;text-decoration:none;white-space:nowrap;cursor:pointer}
        .workspace-link:hover,.refresh-button:hover{background:#e7f0f7;border-color:#6689a8}
        .page-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin:0 2px 14px}
        .eyebrow{color:#536b82;font-size:.68rem;font-weight:850;letter-spacing:.14em;text-transform:uppercase}
        h1{margin:.2rem 0 0;color:#18324b;font-size:clamp(1.45rem,2.4vw,2rem);line-height:1.08;letter-spacing:-.04em}
        .page-copy{margin:.35rem 0 0;color:#52677c;font-size:.84rem;line-height:1.45}
        .overview{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 15px;margin-bottom:12px;border:1px solid #aebdcb;border-radius:14px;background:#fff;box-shadow:0 3px 12px rgba(25,42,60,.035)}
        .overview-copy{min-width:0}
        .overview-copy strong{display:block;font-size:.91rem;color:#21384f}
        .overview-copy span{display:block;margin-top:3px;color:#52677c;font-size:.76rem}
        .overview-meta{display:flex;align-items:center;justify-content:flex-end;gap:7px 13px;flex-wrap:wrap;color:#52677c;font-size:.72rem;text-align:right}
        .status{display:inline-flex;align-items:center;justify-content:center;border:1px solid #aebdcb;border-radius:999px;padding:5px 10px;font-size:.68rem;font-weight:850;letter-spacing:.055em;white-space:nowrap}
        .status-healthy{color:#145b3b;background:#eaf6ef;border-color:#8fc5a5}
        .status-attention{color:#754b08;background:#fff5dd;border-color:#d9b85f}
        .status-failed{color:#8d2525;background:#fff0f0;border-color:#d99a9a}
        .status-unknown,.status-not_checked{color:#40576e;background:#eef2f6;border-color:#b4c1ce}
        .toolbar[hidden]{display:none}.toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;padding:10px 11px;margin-bottom:11px;border:1px solid #aebdcb;border-radius:13px;background:#f9fbfd}
        .filters{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
        .filter-button,.expand-button{min-height:34px;padding:0 11px;border:1px solid #aab9c8;border-radius:9px;background:#fff;color:#354d64;font:inherit;font-size:.75rem;font-weight:750;cursor:pointer}
        .filter-button:hover,.expand-button:hover{border-color:#6986a1;background:#edf4f9}
        .filter-button[aria-pressed="true"]{border-color:#365f80;background:#e3edf5;color:#173e5e;box-shadow:inset 0 0 0 1px #365f80}
        .toolbar-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
        .search{width:min(260px,55vw);height:35px;padding:0 11px;border:1px solid #aab9c8;border-radius:9px;background:#fff;color:#24394d;font:inherit;font-size:.78rem}
        .search:focus,.filter-button:focus-visible,.expand-button:focus-visible,.refresh-button:focus-visible,.workspace-link:focus-visible{outline:3px solid #80a9cc;outline-offset:2px}
        .result-count{min-width:72px;color:#52677c;font-size:.71rem;text-align:right}
        .checks{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,270px),1fr));gap:11px}
        .check{display:flex;flex-direction:column;gap:8px;min-width:0;padding:14px 15px;border:1px solid #aebdcb;border-radius:13px;background:#fff;box-shadow:0 4px 14px rgba(25,42,60,.045);transition:border-color .15s ease,box-shadow .15s ease}
        .check:hover{border-color:#8299ae;box-shadow:0 7px 18px rgba(25,42,60,.08)}
        .check[hidden]{display:none}
        .check-top{display:flex;align-items:center;justify-content:space-between;gap:8px}
        .check-group{overflow:hidden;color:#536b82;font-size:.63rem;font-weight:850;letter-spacing:.1em;text-transform:uppercase;text-overflow:ellipsis;white-space:nowrap}
        .check h2{margin:0;color:#21384f;font-size:.9rem;font-weight:760;line-height:1.25}
        .check p{margin:0;color:#52677c;font-size:.79rem;line-height:1.45;overflow-wrap:anywhere}
        .facts{display:flex;gap:6px 11px;flex-wrap:wrap;color:#52677c;font-size:.69rem}
        .facts strong{color:#2e455c;font-weight:800}
        .details{margin-top:1px;color:#344e66;font-size:.74rem}
        .details summary{cursor:pointer;font-weight:750}
        .details ul{padding-left:17px;max-height:120px;overflow:auto}
        .notice{padding:8px 10px;border:1px solid #b1c3d2;border-left:3px solid #527b9e;border-radius:7px;background:#eef4f8;color:#294761!important;font-size:.74rem!important}
        .guidance{display:grid;gap:4px;padding:10px 11px;border:1px solid #c2ced9;border-left:3px solid #557b9c;border-radius:8px;background:#f5f8fb}
        .guidance strong{color:#294761;font-size:.72rem;letter-spacing:.025em}
        .guidance p{color:#405970;font-size:.75rem}
        .guidance a{width:max-content;color:#1d527a;font-size:.74rem;font-weight:800;text-decoration:underline;text-underline-offset:2px}
        .empty-state{display:none;padding:22px;border:1px dashed #8298ac;border-radius:13px;background:#f8fafc;color:#52677c;text-align:center;font-size:.84rem}
        .empty-state.is-visible{display:block}
        .foot{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;padding:12px 3px 2px;color:#596f84;font-size:.69rem}
        @media(max-width:650px){
            body{padding:9px}
            .topbar{top:5px;min-height:54px;padding:8px 10px;margin-bottom:14px}
            .context{display:none}
            .workspace-link,.refresh-button{min-height:38px;padding:0 10px;font-size:.78rem}
            .page-head{display:block;margin-bottom:11px}
            .page-copy{max-width:42rem}
            .overview{align-items:flex-start}
            .overview-meta{max-width:48%;font-size:.67rem}
            .toolbar{align-items:stretch}
            .filters{width:100%}
            .toolbar-actions{width:100%;justify-content:space-between}
            .search{flex:1;width:auto}
            .checks{grid-template-columns:1fr}
        }
        @media(prefers-reduced-motion:reduce){.check{transition:none}}
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
            <p class="page-copy">A local health snapshot with safe next steps. Business records are not changed by these checks.</p>
        </div>
    </header>

    @php
        $checkCounts = collect($health['checks'])->countBy('status');
        $totalChecks = count($health['checks']);
    @endphp
    <section class="overview" aria-labelledby="health-heading">
        <div class="overview-copy">
            <strong id="health-heading">Installation health</strong>
            <span>{{ $checkCounts->get('healthy', 0) }} healthy · {{ $checkCounts->get('attention', 0) }} need attention · {{ $checkCounts->get('failed', 0) }} failed</span>
        </div>
        <div class="overview-meta">
            <span class="status status-{{ $health['status'] }}">{{ strtoupper(str_replace('_', ' ', $health['status'])) }}</span>
            <span>Checked {{ $health['checked_at'] }}</span>
            <span>PHP {{ $phpVersion }} · Laravel {{ $laravelVersion }}</span>
            <button class="refresh-button" type="button" data-refresh><span aria-hidden="true">↻</span> Recheck</button>
        </div>
    </section>

    <section class="toolbar" data-rescue-controls aria-label="Filter diagnostic checks" hidden>
        <div class="filters" role="group" aria-label="Filter by status">
            <button class="filter-button" type="button" data-filter="all" aria-pressed="true">All <span>{{ $totalChecks }}</span></button>
            <button class="filter-button" type="button" data-filter="failed" aria-pressed="false">Failed <span>{{ $checkCounts->get('failed', 0) }}</span></button>
            <button class="filter-button" type="button" data-filter="attention" aria-pressed="false">Attention <span>{{ $checkCounts->get('attention', 0) }}</span></button>
            <button class="filter-button" type="button" data-filter="healthy" aria-pressed="false">Healthy <span>{{ $checkCounts->get('healthy', 0) }}</span></button>
        </div>
        <div class="toolbar-actions">
            <input class="search" type="search" data-check-search placeholder="Find a check…" aria-label="Search diagnostic checks">
            <button class="expand-button" type="button" data-expand-details>Expand details</button>
            <span class="result-count" data-result-count aria-live="polite">{{ $totalChecks }} checks</span>
        </div>
    </section>

    <section class="checks" data-checks aria-label="Installation health checks">
        @foreach($health['checks'] as $check)
            <article class="check" data-check-card data-status="{{ $check['status'] }}" data-search="{{ strtolower(($check['group'] ?? 'Sentinel').' '.$check['label'].' '.$check['message'].' '.($check['error_code'] ?? '').' '.($check['failure_class'] ?? '')) }}">
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
                @if(in_array($check['status'], ['failed', 'attention'], true))
                    @php
                        $safeGuidance = $check['next_step'] ?? match ($check['key'] ?? '') {
                            'database_connection' => 'Check that the configured local database service is running and that the installation connection settings are correct. Do not change credentials or replace the database from this screen.',
                            'migration_history' => 'Stop normal data entry until the schema is reviewed. Verify a complete database and private-files backup, then use the documented Zazu upgrade procedure. Do not run migrations from this screen.',
                            'private_storage' => 'Check that the private storage folder exists and that the Windows account running Zazu can read and write it. Do not move, rename or delete existing attachment files.',
                            'offline_assets' => 'Repair the installation using the matching Zazu release package, then recheck. Do not mix files from different releases or clear phone data as a first response.',
                            'diagnostic_directory' => 'Check that the private diagnostics folder exists and that the Zazu process can read it. Preserve any existing diagnostic files while correcting folder access.',
                            'current_ledger', 'previous_ledger' => 'Preserve the original diagnostic file. Do not edit, truncate or delete ledger lines; use the available reference-specific report when an incident reference is shown.',
                            'write_probe' => 'Check available disk space and the diagnostics folder permissions. Sentinel uses a temporary file for this probe and attempts to remove it automatically; do not delete unrelated files.',
                            default => 'Keep the installation data intact. Record the check name and reference, if present, before attempting a repair.',
                        };
                    @endphp
                    <div class="guidance" role="note">
                        <strong>Recommended next step</strong>
                        <p>{{ $safeGuidance }}</p>
                        @if(!empty($check['reference']) && \Illuminate\Support\Str::isUuid($check['reference']) && ($check['error_code'] ?? null) === 'DB-001')
                            <a href="{{ route('support.incidents.show', ['requestId' => $check['reference']]) }}">Open safe incident report →</a>
                        @endif
                    </div>
                @elseif(!empty($check['next_step']))
                    <p class="notice">{{ $check['next_step'] }}</p>
                @endif
            </article>
        @endforeach
    </section>
    <div class="empty-state" data-empty-state>No checks match that filter. Try a different status or search term.</div>

    <footer class="foot">
        <span>Owner access · active business only · non-destructive diagnostics</span>
        <span>Database checks are read-only · temporary diagnostic probe is removed after use</span>
    </footer>
</main>
<script nonce="{{ request()->attributes->get('cspNonce') }}">
document.addEventListener('DOMContentLoaded', () => {
    const controls = document.querySelector('[data-rescue-controls]');
    const cards = [...document.querySelectorAll('[data-check-card]')];
    const filters = [...document.querySelectorAll('[data-filter]')];
    const search = document.querySelector('[data-check-search]');
    const count = document.querySelector('[data-result-count]');
    const empty = document.querySelector('[data-empty-state]');
    const expand = document.querySelector('[data-expand-details]');
    let activeFilter = 'all';
    if (!controls || !cards.length) return;
    controls.hidden = false;

    const applyFilters = () => {
        const query = (search?.value || '').trim().toLowerCase();
        let visible = 0;
        cards.forEach((card) => {
            const statusMatch = activeFilter === 'all' || card.dataset.status === activeFilter;
            const searchMatch = !query || (card.dataset.search || '').includes(query);
            const show = statusMatch && searchMatch;
            card.hidden = !show;
            if (show) visible += 1;
        });
        if (count) count.textContent = visible + (visible === 1 ? ' check' : ' checks');
        empty?.classList.toggle('is-visible', visible === 0);
    };

    filters.forEach((button) => button.addEventListener('click', () => {
        activeFilter = button.dataset.filter || 'all';
        filters.forEach((item) => item.setAttribute('aria-pressed', item === button ? 'true' : 'false'));
        applyFilters();
    }));
    search?.addEventListener('input', applyFilters);
    expand?.addEventListener('click', () => {
        const details = cards.flatMap((card) => [...card.querySelectorAll('details')]);
        const shouldOpen = details.some((item) => !item.open);
        details.forEach((item) => { item.open = shouldOpen; });
        expand.textContent = shouldOpen ? 'Collapse details' : 'Expand details';
    });
    document.querySelector('[data-refresh]')?.addEventListener('click', (event) => {
        const button = event.currentTarget;
        button.disabled = true;
        button.textContent = 'Checking…';
        window.location.reload();
    });
    applyFilters();
});
</script>
</body>
</html>
