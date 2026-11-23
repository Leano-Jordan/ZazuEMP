<x-app-layout>
    <x-slot:title>Owner Admin</x-slot:title>
    <x-slot:heading>Owner Admin</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-ghost">Business workspace</a>
    </x-slot:headerAction>

    <style nonce="{{ request()->attributes->get('cspNonce') }}">
        .owner-admin-stack{display:grid;gap:1rem}
        .owner-health-panel{padding:1rem;border:1px solid var(--zazu-border);border-radius:16px;background:var(--zazu-surface)}
        .owner-health-head{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:1rem}
        .owner-health-kicker{font-size:.7rem;font-weight:750;letter-spacing:.1em;text-transform:uppercase;color:var(--zazu-muted)}
        .owner-health-title{font-size:1.15rem;font-weight:750;margin:.2rem 0}
        .owner-health-copy{color:var(--zazu-muted);font-size:.86rem;max-width:54rem}
        .owner-health-actions{display:flex;align-items:center;gap:.7rem;flex-wrap:wrap}
        .owner-health-actions a{font-size:.82rem;font-weight:700;text-decoration:underline;text-underline-offset:3px}
        .owner-health-status{display:inline-flex;align-items:center;border:1px solid var(--zazu-border);border-radius:999px;padding:.3rem .65rem;font-size:.7rem;font-weight:800;letter-spacing:.04em;white-space:nowrap}
        .owner-health-healthy{border-left:4px solid #23865b}
        .owner-health-attention{border-left:4px solid #b7791f}
        .owner-health-failed{border-left:4px solid #c04444}
        .owner-health-unknown,.owner-health-not_checked{border-left:4px solid var(--zazu-border)}
        .owner-health-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,230px),1fr));gap:.7rem}
        .owner-health-card{display:grid;align-content:start;gap:.55rem;min-width:0;padding:.9rem;border:1px solid var(--zazu-border);border-radius:12px;background:var(--zazu-background)}
        .owner-health-card-head{display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem}
        .owner-health-card strong{font-size:.88rem}
        .owner-health-card p{margin:0;color:var(--zazu-muted);font-size:.8rem;line-height:1.45}
        .owner-health-next{padding-top:.55rem;border-top:1px solid var(--zazu-border);color:var(--zazu-text)!important}
        .owner-health-note{margin-top:.85rem;padding:.7rem .8rem;border-radius:9px;background:var(--zazu-background);color:var(--zazu-muted);font-size:.76rem;line-height:1.5}
        .owner-admin-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,210px),1fr));gap:.7rem}
        .owner-admin-links .zazu-route-card{height:100%}
        @media(max-width:650px){.owner-health-head{display:grid}.owner-health-actions{justify-content:flex-start}}
    </style>

    <div class="owner-admin-stack">
        <section class="zazu-command-band">
            <div>
                <div class="zazu-eyebrow">Business administration</div>
                <h2 class="zazu-command-title">{{ $business->name }}</h2>
                <p class="zazu-command-copy">Manage this business workspace, check its data access and find safe next steps when something needs attention.</p>
            </div>
            <div class="zazu-command-meta"><div class="zazu-command-meta-label">Access</div><div class="zazu-command-meta-value">Business owner</div></div>
        </section>

        <section class="owner-health-panel" id="business-health" aria-labelledby="business-health-title">
            <div class="owner-health-head">
                <div>
                    <div class="owner-health-kicker">Sentinel · this business only</div>
                    <h2 class="owner-health-title" id="business-health-title">Business health</h2>
                    <p class="owner-health-copy">A read-only check of database access, your owner membership and this business's customer, event and service data paths.</p>
                </div>
                <div class="owner-health-actions">
                    <span class="owner-health-status owner-health-{{ $health['status'] }}">{{ strtoupper(str_replace('_', ' ', $health['status'])) }}</span>
                    <a href="{{ route('owner.dashboard') }}#business-health">Recheck</a>
                </div>
            </div>

            <div class="owner-health-grid">
                @foreach($health['checks'] as $check)
                    <article class="owner-health-card owner-health-{{ $check['status'] }}">
                        <div class="owner-health-card-head">
                            <strong>{{ $check['label'] }}</strong>
                            <span class="owner-health-status">{{ strtoupper(str_replace('_', ' ', $check['status'])) }}</span>
                        </div>
                        <p>{{ $check['message'] }}</p>
                        @if(!empty($check['next_step']))
                            <p class="owner-health-next"><strong>Recommended next step:</strong> {{ $check['next_step'] }}</p>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="owner-health-note">
                Checked {{ !empty($health['checked_at']) ? \Illuminate\Support\Carbon::parse($health['checked_at'])->timezone(config('app.timezone'))->format('j M Y, H:i') : 'Unknown' }}. These checks are scoped to the active business and do not expose platform incident records or other businesses' information. They do not certify phone offline operation or synchronisation.
                If you need help, <a href="mailto:rosscorelabs@outlook.com?subject=Zazu%20business%20health%20support">contact Zazu support</a>.
            </div>
        </section>

        <section class="zazu-panel">
            <div class="zazu-panel-head">
                <div>
                    <div class="zazu-panel-title">Owner tools</div>
                    <div class="zazu-panel-copy">The controls for managing this business and protecting its records.</div>
                </div>
                <span class="zazu-chip zazu-chip-accent">Owner only</span>
            </div>
            <div class="owner-admin-links mt-4">
                <a href="{{ route('settings.index') }}" class="zazu-route-card">
                    <div>
                        <div class="zazu-route-card-title">Business settings</div>
                        <div class="zazu-route-card-copy">Manage business identity, currency, licence and workspace configuration.</div>
                    </div>
                    <span class="zazu-route-card-arrow">→</span>
                </a>
                <a href="{{ route('settings.audit') }}" class="zazu-route-card">
                    <div>
                        <div class="zazu-route-card-title">Activity &amp; security</div>
                        <div class="zazu-route-card-copy">Review the business audit history and important account activity.</div>
                    </div>
                    <span class="zazu-route-card-arrow">→</span>
                </a>
                <a href="{{ route('exports.index') }}" class="zazu-route-card">
                    <div>
                        <div class="zazu-route-card-title">Data export &amp; recovery</div>
                        <div class="zazu-route-card-copy">Export this business's records and access its available recovery tools.</div>
                    </div>
                    <span class="zazu-route-card-arrow">→</span>
                </a>
                <a href="{{ route('settings.compliance') }}" class="zazu-route-card">
                    <div>
                        <div class="zazu-route-card-title">Compliance documents</div>
                        <div class="zazu-route-card-copy">Manage the documents maintained for this business.</div>
                    </div>
                    <span class="zazu-route-card-arrow">→</span>
                </a>
                <a href="{{ route('capabilities.index') }}" class="zazu-route-card">
                    <div>
                        <div class="zazu-route-card-title">Services &amp; prices</div>
                        <div class="zazu-route-card-copy">Maintain the services and pricing available to this business.</div>
                    </div>
                    <span class="zazu-route-card-arrow">→</span>
                </a>
            </div>
        </section>

        <section class="zazu-detail-grid">
            <section class="zazu-panel">
                <div class="zazu-panel-title">Current workspace</div>
                <div class="zazu-detail-rows">
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Business</div><div class="zazu-detail-value">{{ $business->name }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Currency</div><div class="zazu-detail-value">{{ $business->currency }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Status</div><div class="zazu-detail-value">{{ ucfirst($business->status) }}</div></div>
                </div>
            </section>
            <aside class="zazu-panel">
                <div class="zazu-panel-title">Your data boundary</div>
                <p class="zazu-panel-copy">This area is limited to the active business you own. Platform-wide incidents, other businesses' records and installation-wide recovery controls are not available here.</p>
            </aside>
        </section>
    </div>
</x-app-layout>
