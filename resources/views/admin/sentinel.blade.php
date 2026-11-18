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
        .sentinel-health-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.7rem}
        .sentinel-health-card{display:grid;gap:.35rem;padding:.85rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:12px;min-width:0}
        .sentinel-health-card strong{font-size:.9rem}
        .sentinel-health-card p{margin:0;color:var(--zazu-muted,#687386);font-size:.82rem}
        .sentinel-health-card small{color:var(--zazu-muted,#687386);font-size:.72rem}
        .sentinel-health-healthy{border-left:4px solid #23865b}
        .sentinel-health-attention{border-left:4px solid #b7791f}
        .sentinel-health-failed{border-left:4px solid #c04444}
        .sentinel-health-unknown,.sentinel-health-not_checked{border-left:4px solid var(--zazu-border,#d9dee7)}
        .sentinel-health-overall{display:flex;justify-content:space-between;gap:1rem;align-items:center;margin-bottom:.9rem}
        .sentinel-health-overall strong{font-size:1rem}
        .sentinel-health-facts{display:flex;gap:.8rem;flex-wrap:wrap;font-size:.75rem;color:var(--zazu-muted,#687386)}
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
        .sentinel-download{display:inline-block;margin-top:.35rem;font-size:.78rem;font-weight:650;color:var(--zazu-accent,#263d58)!important;text-decoration:underline}
        .sentinel-muted{color:var(--zazu-muted,#687386)}
        .sentinel-pill{display:inline-block;border:1px solid var(--zazu-border,#d9dee7);border-radius:999px;padding:.18rem .55rem;font-size:.75rem}
        .sentinel-timeline{display:grid;gap:.65rem}
        .sentinel-event{display:grid;grid-template-columns:minmax(10rem, .8fr) minmax(0,2fr);gap:1rem;padding:.8rem;border:1px solid var(--zazu-border,#e3e7ed);border-radius:12px}
        .sentinel-event time{font-size:.82rem;font-variant-numeric:tabular-nums}
        .sentinel-event dl{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.5rem 1rem;margin:0}
        .sentinel-event dt{font-size:.7rem;text-transform:uppercase;color:var(--zazu-muted,#687386)}
        .sentinel-event dd{margin:0;font-size:.85rem;overflow-wrap:anywhere}
        .sentinel-empty{padding:1.2rem;text-align:center;color:var(--zazu-muted,#687386)}
        .sentinel-retention-note{font-size:.78rem}
        .sentinel-license-grid{display:grid;gap:.8rem}
        .sentinel-license-row{display:grid;grid-template-columns:minmax(0,1fr) minmax(18rem,1.2fr);gap:1rem;padding:1rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:12px}
        .sentinel-license-actions{display:grid;gap:.55rem}
        .sentinel-license-form{display:flex;gap:.5rem;flex-wrap:wrap;align-items:end}
        .sentinel-license-form label{display:grid;gap:.25rem;font-size:.76rem;color:var(--zazu-muted,#687386)}
        .sentinel-license-form input,.sentinel-license-form select{min-width:9rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:8px;padding:.55rem;background:var(--zazu-background,#fff);color:inherit}
        .sentinel-license-form .sentinel-button{font-size:.82rem}
        .sentinel-license-form input[type=checkbox]{min-width:auto;padding:0}
        .sentinel-token{width:100%;min-height:6rem;overflow-wrap:anywhere;border:1px solid var(--zazu-border,#d9dee7);border-radius:10px;padding:.7rem;background:var(--zazu-background,#fff);color:inherit}
        .sentinel-alert{padding:.85rem 1rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:12px}
        .sentinel-alert-error{border-color:#c04444}
        .sentinel-license-search{margin:.8rem 0}
        .sentinel-license-facts{margin-top:.45rem}
        .sentinel-license-meta{font-size:.75rem}
        .sentinel-license-pagination{margin-top:1rem}
        @media(max-width:800px){.sentinel-license-row{grid-template-columns:1fr}}
        @media(max-width:700px){.sentinel-stats{grid-template-columns:1fr}.sentinel-health-grid{grid-template-columns:1fr}.sentinel-intro{display:block}.sentinel-event{grid-template-columns:1fr}.sentinel-event dl{grid-template-columns:1fr 1fr}}
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

        <section class="sentinel-panel" id="licensing">
            <div class="sentinel-health-overall">
                <div>
                    <h3>Licensing &amp; entitlements</h3>
                    <p class="sentinel-muted">Founder-only business lookup, signed licence issue/renewal and revocation. Customer activation remains in Business Settings.</p>
                </div>
                <span class="sentinel-pill">{{ $licenceSigningConfigured ? 'Signing configured' : 'Signing not configured' }}</span>
            </div>

            @if(session('error'))
                <div class="sentinel-alert sentinel-alert-error" role="alert">{{ session('error') }}</div>
            @endif

            @if($issuedLicenseToken)
                <div class="sentinel-alert" role="status">
                    <strong>Signed licence {{ $issuedLicenseAction }} for {{ $issuedLicenseBusiness }}.</strong>
                    <p class="sentinel-muted">Copy this token and deliver it only to the matching business owner. It is shown once in this founder session; it is not written to the audit log.</p>
                    <textarea class="sentinel-token" readonly rows="4" aria-label="Signed licence token">{{ $issuedLicenseToken }}</textarea>
                </div>
            @endif

            @if(!$licenceSigningConfigured)
                <p class="sentinel-muted">To enable issuance, set <code>ZAZU_LICENSE_PRIVATE_KEY_FILE</code> to a base64 Ed25519 secret-key file outside the Zazu project. The browser never receives the key. The configured public key must match it.</p>
            @endif

            <form method="GET" action="{{ route('admin.sentinel') }}" class="sentinel-search sentinel-license-search">
                <input type="search" name="business_q" value="{{ $businessQuery }}" maxlength="120" placeholder="Find business by name or ID" aria-label="Find business">
                <button class="sentinel-button" type="submit">Find business</button>
                <a class="sentinel-clear" href="{{ route('admin.sentinel', ['section' => 'licensing']) }}">Clear</a>
            </form>

            <div class="sentinel-license-grid">
                @forelse($businesses as $business)
                    <article class="sentinel-license-row">
                        <div>
                            <strong>{{ $business->name }}</strong>
                            <div class="sentinel-muted">Business ID #{{ $business->id }}</div>
                            @if($business->license)
                                <div class="sentinel-health-facts sentinel-license-facts">
                                    <span class="sentinel-pill">{{ strtoupper($business->license->plan) }}</span>
                                    <span class="sentinel-pill">{{ $business->license->statusLabel() }}</span>
                                    <span>Expires {{ $business->license->expires_at?->format('d M Y H:i') ?? 'Never' }}</span>
                                </div>
                                <div class="sentinel-muted sentinel-license-meta">Licence {{ $business->license->license_id }}</div>
                            @else
                                <div class="sentinel-muted">No signed licence installed · Basic Free / trial rules apply</div>
                            @endif
                        </div>
                        <div class="sentinel-license-actions">
                            <form method="POST" action="{{ route('admin.sentinel.licences.issue') }}" class="sentinel-license-form">
                                @csrf
                                <input type="hidden" name="business_id" value="{{ $business->id }}">
                                <label>Plan
                                    <select name="plan" required>
                                        <option value="basic" @selected(($business->license?->plan ?? 'basic') === 'basic')>Basic</option>
                                        <option value="intermediate" @selected(($business->license?->plan ?? '') === 'intermediate')>Intermediate</option>
                                        <option value="advanced" @selected(in_array($business->license?->plan, ['advanced', 'solo'], true))>Advanced</option>
                                    </select>
                                </label>
                                <label>Expires at
                                    <input type="datetime-local" name="expires_at">
                                </label>
                                <label><span> </span><span><input type="checkbox" name="never_expires" value="1"> Never expires</span></label>
                                <button class="sentinel-button" type="submit" {{ !$licenceSigningConfigured ? 'disabled' : '' }}>{{ $business->license ? 'Issue renewal' : 'Issue licence' }}</button>
                            </form>
                            @if($business->license && $business->license->status !== 'revoked')
                                <form method="POST" action="{{ route('admin.sentinel.licences.revoke', $business) }}" class="sentinel-license-form">
                                    @csrf
                                    <span class="sentinel-muted">Revoke current licence</span>
                                    <button class="sentinel-button" type="submit" {{ !$licenceSigningConfigured ? 'disabled' : '' }}>Revoke</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="sentinel-empty">No businesses match this search.</div>
                @endforelse
            </div>
            <div class="sentinel-license-pagination">{{ $businesses->links() }}</div>
            <p class="sentinel-muted sentinel-retention-note">Issuance and revocation actions are recorded in the application log without storing signed tokens or private-key material. Issuing a renewal replaces the currently installed business entitlement when the owner imports the newer token; revocation takes effect when the owner imports the revocation token.</p>
        </section>

        <section class="sentinel-stats" aria-label="Sentinel overview">
            <div class="sentinel-stat"><strong>{{ number_format($totalFingerprints) }}</strong><span>Failure fingerprints indexed</span></div>
            <div class="sentinel-stat"><strong>{{ number_format($totalOccurrences) }}</strong><span>Recorded occurrences in retained ledger</span></div>
            <div class="sentinel-stat"><strong>{{ $latestIncident ?? '—' }}</strong><span>Most recent recorded incident</span></div>
        </section>

        <section class="sentinel-panel" aria-label="Sentinel diagnostic health">
            <div class="sentinel-health-overall">
                <div>
                    <h3>Sentinel's own health</h3>
                    <p class="sentinel-muted">Checks the local incident evidence store, not the health of every Zazu subsystem.</p>
                </div>
                <span class="sentinel-pill">{{ strtoupper(str_replace('_', ' ', $health['status'])) }}</span>
            </div>
            <div class="sentinel-muted sentinel-retention-note">Checked {{ $health['checked_at'] }}. This check does not query or modify business records.</div>
            <div class="sentinel-health-grid">
                @foreach($health['checks'] as $check)
                    <article class="sentinel-health-card sentinel-health-{{ $check['status'] }}">
                        <strong>{{ $check['label'] }}</strong>
                        <span class="sentinel-pill">{{ strtoupper(str_replace('_', ' ', $check['status'])) }}</span>
                        <p>{{ $check['message'] }}</p>
                        @if(isset($check['records']))
                            <div class="sentinel-health-facts">
                                <span>{{ number_format($check['records']) }} valid records</span>
                                <span>{{ number_format($check['malformed_lines']) }} unreadable lines</span>
                                <span>{{ number_format($check['size_bytes']) }} bytes</span>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
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
                                    @if($event['error_code'] === 'DB-001')
                                        <a class="sentinel-download" href="{{ URL::temporarySignedRoute('support.incidents.show', now()->addMinutes(15), ['requestId' => $event['request_id']]) }}">Download safe DB report</a>
                                    @endif
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

        <p class="sentinel-muted sentinel-retention-note">Retention is limited to the active and previous ledger segments. Incident messages, SQL statements, bindings, customer data and stack traces are intentionally excluded.</p>
    </div>
</x-app-layout>
