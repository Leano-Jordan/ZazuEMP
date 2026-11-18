<x-app-layout>
    <x-slot:title>Sentinel diagnostics</x-slot:title>
    <x-slot:heading>Sentinel diagnostics</x-slot:heading>

    <style nonce="{{ request()->attributes->get('cspNonce') }}">
        .sentinel-wrap{display:grid;gap:1.25rem}
        .sentinel-intro{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;padding:1.25rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:18px;background:var(--zazu-surface,#fff)}
        .sentinel-kicker{font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;font-weight:700;color:var(--zazu-muted,#687386)}
        .sentinel-title{font-size:1.45rem;font-weight:750;margin:.3rem 0}
        .sentinel-copy{color:var(--zazu-muted,#687386);max-width:58rem}
        .sentinel-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.8rem}
        .sentinel-stat{padding:1rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:14px;background:var(--zazu-surface,#fff)}
        .sentinel-stat strong{display:block;font-size:1.55rem;font-variant-numeric:tabular-nums}
        .sentinel-stat span{font-size:.8rem;color:var(--zazu-muted,#687386)}
        .sentinel-panel{padding:1rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:16px;background:var(--zazu-surface,#fff);min-width:0}
        .sentinel-panel h3{font-size:1rem;font-weight:700;margin:0 0 .8rem}
        .sentinel-search{display:flex;gap:.6rem;flex-wrap:wrap}
        .sentinel-search input{flex:1 1 16rem;min-width:0;border:1px solid var(--zazu-border,#d9dee7);border-radius:10px;padding:.7rem .85rem;background:var(--zazu-background,#fff);color:inherit}
        .sentinel-button{border:0;border-radius:10px;padding:.7rem 1rem;background:var(--zazu-accent,#263d58);color:#fff;font-weight:650;text-decoration:none;cursor:pointer}
        .sentinel-clear{align-self:center;color:inherit;text-decoration:underline;font-size:.88rem}
        .sentinel-table-wrap{overflow-x:auto}
        .sentinel-table{width:100%;border-collapse:collapse;font-size:.88rem}
        .sentinel-table th,.sentinel-table td{text-align:left;padding:.75rem .65rem;border-bottom:1px solid var(--zazu-border,#e3e7ed);vertical-align:top}
        .sentinel-table th{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--zazu-muted,#687386)}
        .sentinel-table code,.sentinel-code{font-size:.8rem;overflow-wrap:anywhere}
        .sentinel-table a{font-weight:700;color:inherit}
        .sentinel-muted{color:var(--zazu-muted,#687386)}
        .sentinel-pill{display:inline-block;border:1px solid var(--zazu-border,#d9dee7);border-radius:999px;padding:.18rem .55rem;font-size:.75rem}
        .sentinel-timeline{display:grid;gap:.65rem}
        .sentinel-event{display:grid;grid-template-columns:minmax(10rem, .8fr) minmax(0,2fr);gap:1rem;padding:.8rem;border:1px solid var(--zazu-border,#e3e7ed);border-radius:12px}
        .sentinel-event time{font-size:.82rem;font-variant-numeric:tabular-nums}
        .sentinel-event dl{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.5rem 1rem;margin:0}
        .sentinel-event dt{font-size:.7rem;text-transform:uppercase;color:var(--zazu-muted,#687386)}
        .sentinel-event dd{margin:0;font-size:.85rem;overflow-wrap:anywhere}
        .sentinel-empty{padding:1.2rem;text-align:center;color:var(--zazu-muted,#687386)}
        @media(max-width:700px){.sentinel-stats{grid-template-columns:1fr}.sentinel-intro{display:block}.sentinel-event{grid-template-columns:1fr}.sentinel-event dl{grid-template-columns:1fr 1fr}}
    </style>

    <div class="sentinel-wrap">
        <section class="sentinel-intro">
            <div>
                <div class="sentinel-kicker">Director control · private diagnostics</div>
                <h2 class="sentinel-title">Failure intelligence, not guesswork.</h2>
                <p class="sentinel-copy">Sentinel reads the independent, privacy-filtered incident ledger. It groups repeat failures, exposes their technical fingerprints and preserves an occurrence timeline without querying business records.</p>
            </div>
            <span class="sentinel-pill">Platform admin only</span>
        </section>

        <section class="sentinel-stats" aria-label="Sentinel overview">
            <div class="sentinel-stat"><strong>{{ number_format($totalFingerprints) }}</strong><span>Failure fingerprints indexed</span></div>
            <div class="sentinel-stat"><strong>{{ number_format($totalOccurrences) }}</strong><span>Recorded occurrences in retained ledger</span></div>
            <div class="sentinel-stat"><strong>{{ $latestIncident ?? '—' }}</strong><span>Most recent recorded incident</span></div>
        </section>

        <section class="sentinel-panel">
            <h3>Search failure patterns</h3>
            <form method="GET" action="{{ route('admin.sentinel') }}" class="sentinel-search">
                <input type="search" name="q" value="{{ $searchQuery }}" maxlength="120" placeholder="Fingerprint, error code, exception, failure class or source file" aria-label="Search incidents">
                <button class="sentinel-button" type="submit">Search</button>
                @if($searchQuery !== '' || $selectedFingerprint)
                    <a class="sentinel-clear" href="{{ route('admin.sentinel') }}">Clear filters</a>
                @endif
            </form>
        </section>

        <section class="sentinel-panel">
            <h3>Recurring failure patterns <span class="sentinel-muted">({{ count($summaries) }})</span></h3>
            @if(count($summaries))
                <div class="sentinel-table-wrap">
                    <table class="sentinel-table">
                        <thead><tr><th>Fingerprint</th><th>Error</th><th>Occurrences</th><th>Last seen</th><th>Failure / exception</th><th>Source</th></tr></thead>
                        <tbody>
                        @foreach($summaries as $item)
                            <tr>
                                <td><a href="{{ route('admin.sentinel', array_filter(['q' => $searchQuery, 'fingerprint' => $item['fingerprint']])) }}"><code>{{ $item['fingerprint'] }}</code></a></td>
                                <td><span class="sentinel-pill">{{ $item['error_code'] }}</span></td>
                                <td><strong>{{ number_format($item['occurrences']) }}</strong></td>
                                <td>{{ $item['last_seen'] }}</td>
                                <td>{{ $item['failure_class'] !== 'unknown' ? $item['failure_class'] : $item['exception'] }}</td>
                                <td>{{ $item['source_file'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="sentinel-empty">No incidents match this search. The console will populate as the ledger records failures.</div>
            @endif
        </section>

        @if($selectedFingerprint)
            <section class="sentinel-panel" id="incident-timeline">
                <h3>Occurrence timeline <span class="sentinel-muted">· {{ $selectedFingerprint }}</span></h3>
                @if(count($timeline))
                    <div class="sentinel-timeline">
                        @foreach($timeline as $event)
                            <article class="sentinel-event">
                                <div>
                                    <time>{{ $event['timestamp'] }}</time>
                                    <div><code class="sentinel-code">{{ $event['request_id'] }}</code></div>
                                </div>
                                <dl>
                                    <div><dt>Error</dt><dd>{{ $event['error_code'] }}</dd></div>
                                    <div><dt>Source</dt><dd>{{ $event['source_file'] }}</dd></div>
                                    <div><dt>Component</dt><dd>{{ $event['component'] }}</dd></div>
                                    <div><dt>Exception</dt><dd>{{ $event['exception'] }}</dd></div>
                                    <div><dt>Database driver / state</dt><dd>{{ $event['database_driver'] }} / {{ $event['database_sqlstate'] }}</dd></div>
                                    <div><dt>DB operation / class</dt><dd>{{ $event['database_operation'] }} / {{ $event['database_failure_class'] }}</dd></div>
                                </dl>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="sentinel-empty">No retained occurrences were found for this fingerprint.</div>
                @endif
            </section>
        @endif

        <p class="sentinel-muted" style="font-size:.78rem">Retention is limited to the active and previous ledger segments. Incident messages, SQL statements, bindings, customer data and stack traces are intentionally excluded.</p>
    </div>
</x-app-layout>
