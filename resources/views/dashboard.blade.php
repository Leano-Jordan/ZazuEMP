<x-app-layout>
    <x-slot:title>Dashboard</x-slot:title>
    <x-slot:heading>Dashboard</x-slot:heading>

    @if ($isOwner && (! $business?->catalogue_setup_completed_at || ! $business?->business_setup_completed_at))
        <section class="zazu-dashboard-setup" aria-label="Workspace setup">
            <div>
                <div class="zazu-eyebrow">Setup centre</div>
                <h2>Finish the workspace foundation when you are ready.</h2>
                <p>Services, business identity and optional compliance settings can be completed without losing existing work.</p>
            </div>
            <a href="{{ route('onboarding.index') }}" class="zazu-btn zazu-btn-primary">Continue setup <span aria-hidden="true">→</span></a>
        </section>
    @endif

    <section class="zazu-dashboard-command" aria-labelledby="zazu-dashboard-title">
        <div>
            <div class="zazu-dashboard-kicker"><span class="zazu-dashboard-live-dot" aria-hidden="true"></span>Live workspace telemetry</div>
            <h2 id="zazu-dashboard-title">Run the business from one operational surface.</h2>
            <p>Jobs, commercial activity and planning stay connected to the workspace records that already exist.</p>
        </div>
        @if($workspaceTools['work'] && app(\App\Support\PermissionService::class)->allows('work.create', auth()->user(), $business))
            <a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary">Create work <span aria-hidden="true">+</span></a>
        @elseif($workspaceTools['services'])
            <a href="{{ route('capabilities.index') }}" class="zazu-btn zazu-btn-primary">Open catalogue <span aria-hidden="true">→</span></a>
        @endif
    </section>

    <section class="zazu-dashboard-telemetry" aria-label="Operational telemetry">
        @if($workspaceTools['work'])
            <a href="{{ route('work.index') }}" class="zazu-dashboard-kpi">
                <span class="zazu-dashboard-kpi-label">Active events</span>
                <strong>{{ number_format($metrics['active_work']) }}</strong>
                <span class="zazu-dashboard-kpi-meta">Open operational records</span>
            </a>
        @endif
        @if($workspaceTools['calendar'])
            <a href="{{ route('calendar.index') }}" class="zazu-dashboard-kpi">
                <span class="zazu-dashboard-kpi-label">Next 14 days</span>
                <strong>{{ number_format($metrics['upcoming_work']) }}</strong>
                <span class="zazu-dashboard-kpi-meta">Scheduled work ahead</span>
            </a>
        @endif
        @if($workspaceTools['quotes'])
            <a href="{{ route('quotes.index') }}" class="zazu-dashboard-kpi">
                <span class="zazu-dashboard-kpi-label">Draft quotes</span>
                <strong>{{ number_format($metrics['draft_quotes']) }}</strong>
                <span class="zazu-dashboard-kpi-meta">Commercial records awaiting action</span>
            </a>
        @endif
        @if($workspaceTools['customers'])
            <a href="{{ route('customers.index') }}" class="zazu-dashboard-kpi">
                <span class="zazu-dashboard-kpi-label">Customers</span>
                <strong>{{ number_format($metrics['customers']) }}</strong>
                <span class="zazu-dashboard-kpi-meta">People and organisations</span>
            </a>
        @endif
    </section>

    <section class="zazu-dashboard-bento" aria-label="Operational workbench">
        <section class="zazu-dashboard-workbench">
            <div class="zazu-dashboard-panel-head">
                <div>
                    <div class="zazu-eyebrow">Run-sheet</div>
                    <h3>Upcoming dispatch &amp; work</h3>
                    <p>Real workspace events, ordered by execution date.</p>
                </div>
                @if($workspaceTools['calendar'])
                    <a href="{{ route('calendar.index') }}" class="zazu-btn zazu-btn-secondary">Calendar <span aria-hidden="true">→</span></a>
                @endif
            </div>

            <div class="zazu-run-sheet">
                @forelse($upcoming as $event)
                    <a href="{{ $workspaceTools['work'] ? route('work.show', $event) : route('calendar.index') }}" class="zazu-run-item">
                        <div class="zazu-run-time">
                            <strong>{{ $event->event_date?->format('H:i') ?: '—' }}</strong>
                            <span>{{ $event->event_date?->format('d M Y') ?: 'Unscheduled' }}</span>
                        </div>
                        <div class="zazu-run-marker" aria-hidden="true"></div>
                        <div class="zazu-run-main">
                            <strong>{{ $event->name }}</strong>
                            <span>{{ $event->customer?->name ?? 'No customer' }} · {{ $event->reference }}</span>
                        </div>
                        <div class="zazu-run-status">{{ strtoupper(str_replace('_', ' ', $event->status ?? 'OPEN')) }}</div>
                    </a>
                @empty
                    <div class="zazu-dashboard-empty">
                        <strong>No scheduled work yet.</strong>
                        <span>Create a job with an event date and it will appear here.</span>
                        @if($workspaceTools['work'])
                            <a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary">Create job</a>
                        @endif
                    </div>
                @endforelse
            </div>

            <div class="zazu-run-sheet-footer">
                <span>Operational source: workspace event register</span>
                @if($workspaceTools['work'])
                    <a href="{{ route('work.index') }}">Open all work →</a>
                @endif
            </div>
        </section>

        <aside class="zazu-dashboard-commercial">
            <div class="zazu-dashboard-panel-head">
                <div>
                    <div class="zazu-eyebrow">Commercial queue</div>
                    <h3>Quotes awaiting action</h3>
                    <p>Keep draft commercial records visible without inventing financial state.</p>
                </div>
            </div>

            <div class="zazu-commercial-stat">
                <span>Draft quotes</span>
                <strong>{{ number_format($metrics['draft_quotes']) }}</strong>
                <small>Current workspace count</small>
            </div>

            <div class="zazu-commercial-actions">
                @if($workspaceTools['quotes'])
                    <a href="{{ route('quotes.index') }}" class="zazu-btn zazu-btn-primary">Inspect quote queue</a>
                @endif
                @if($workspaceTools['finance'])
                    <a href="{{ route('finance.index') }}" class="zazu-btn zazu-btn-secondary">Open finance</a>
                @endif
            </div>

            <div class="zazu-dashboard-note">
                <span class="zazu-dashboard-note-mark" aria-hidden="true">i</span>
                <p>Revenue, deposits and payment balances are intentionally not fabricated here. They should be surfaced from the finance ledger once the dashboard has an authoritative aggregate.</p>
            </div>
        </aside>

        <section class="zazu-dashboard-assets">
            <div class="zazu-dashboard-panel-head">
                <div>
                    <div class="zazu-eyebrow">Asset readiness</div>
                    <h3>Equipment &amp; stock manifest</h3>
                    <p>The asset register remains the authoritative source for availability, allocation and release state.</p>
                </div>
                @if($workspaceTools['assets'])
                    <a href="{{ route('assets.index') }}" class="zazu-btn zazu-btn-secondary">Open asset register <span aria-hidden="true">→</span></a>
                @endif
            </div>

            <div class="zazu-asset-manifest">
                <div><span class="zazu-asset-signal"></span><strong>Live allocation state</strong><small>Tracked in Assets</small></div>
                <div><span class="zazu-asset-signal"></span><strong>Hire availability</strong><small>Tracked per asset</small></div>
                <div><span class="zazu-asset-signal"></span><strong>Warehouse readiness</strong><small>Tracked in asset status</small></div>
            </div>
        </section>
    </section>

    <section class="zazu-dashboard-quickbar" aria-label="Workspace shortcuts">
        <div>
            <div class="zazu-eyebrow">Workspace surfaces</div>
            <strong>Move directly into the record you need.</strong>
        </div>
        <nav>
            @if($workspaceTools['services'])<a href="{{ route('capabilities.index') }}">Services &amp; prices</a>@endif
            @if($workspaceTools['work'])<a href="{{ route('work.index') }}">Jobs</a>@endif
            @if($workspaceTools['customers'])<a href="{{ route('customers.index') }}">Customers</a>@endif
            @if($workspaceTools['quotes'])<a href="{{ route('quotes.index') }}">Quotes</a>@endif
            @if($workspaceTools['calendar'])<a href="{{ route('calendar.index') }}">Calendar</a>@endif
            @if($workspaceTools['finance'])<a href="{{ route('finance.index') }}">Finance</a>@endif
            @if($workspaceTools['reports'])<a href="{{ route('reports.index') }}">Reports</a>@endif
        </nav>
    </section>

    <style>
        .zazu-dashboard-setup,.zazu-dashboard-command,.zazu-dashboard-kpi,.zazu-dashboard-workbench,.zazu-dashboard-commercial,.zazu-dashboard-assets,.zazu-dashboard-quickbar{border:1px solid var(--zazu-surface-border);background:var(--zazu-surface-card);border-radius:6px;box-shadow:0 1px 2px rgba(15,23,42,.04)}
        .zazu-dashboard-setup{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:16px;margin-bottom:14px}
        .zazu-dashboard-setup h2{margin-top:4px;font:800 15px/1.2 'Cabinet Grotesk','Segoe UI',sans-serif;letter-spacing:-.02em;color:var(--zazu-text-main)}
        .zazu-dashboard-setup p,.zazu-dashboard-command p,.zazu-dashboard-panel-head p{margin-top:5px;color:var(--zazu-text-muted);font-size:11px;line-height:1.55}
        .zazu-dashboard-command{display:flex;align-items:center;justify-content:space-between;gap:24px;padding:22px;border-left:3px solid var(--zazu-blue-primary)}
        .zazu-dashboard-kicker{display:flex;align-items:center;gap:7px;color:var(--zazu-blue-primary);font:700 9px/1.2 'JetBrains Mono',ui-monospace,monospace;letter-spacing:.08em;text-transform:uppercase}
        .zazu-dashboard-live-dot{width:6px;height:6px;border-radius:50%;background:#2FA36B;box-shadow:0 0 0 3px color-mix(in srgb,#2FA36B 13%,transparent)}
        .zazu-dashboard-command h2{margin-top:7px;color:var(--zazu-text-main);font:800 clamp(22px,3vw,31px)/1.05 'Cabinet Grotesk','Segoe UI',sans-serif;letter-spacing:-.03em}
        .zazu-dashboard-telemetry{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:10px}
        .zazu-dashboard-kpi{display:flex;flex-direction:column;min-height:118px;padding:15px;text-decoration:none;transition:box-shadow 140ms ease,border-color 140ms ease,transform 140ms ease}
        .zazu-dashboard-kpi:hover{border-color:var(--zazu-blue-soft);box-shadow:0 4px 12px rgba(15,23,42,.07);transform:translateY(-1px)}
        .zazu-dashboard-kpi-label{color:var(--zazu-text-muted);font-size:10px;font-weight:750;text-transform:uppercase;letter-spacing:.08em}
        .zazu-dashboard-kpi strong{margin-top:14px;color:var(--zazu-text-main);font:800 25px/1 'JetBrains Mono',ui-monospace,monospace;letter-spacing:-.03em}
        .zazu-dashboard-kpi-meta{margin-top:auto;color:var(--zazu-text-muted);font-size:10px}
        .zazu-dashboard-bento{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(300px,1fr);gap:10px;margin-top:10px}
        .zazu-dashboard-workbench,.zazu-dashboard-commercial,.zazu-dashboard-assets{min-width:0;padding:18px}
        .zazu-dashboard-workbench{grid-row:span 2}
        .zazu-dashboard-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
        .zazu-dashboard-panel-head h3{margin-top:4px;color:var(--zazu-text-main);font:800 16px/1.15 'Cabinet Grotesk','Segoe UI',sans-serif;letter-spacing:-.02em}
        .zazu-run-sheet{margin-top:16px;border-top:1px solid var(--zazu-surface-border)}
        .zazu-run-item{display:grid;grid-template-columns:82px 10px minmax(0,1fr) auto;align-items:center;gap:12px;min-height:72px;padding:10px 0;border-bottom:1px solid var(--zazu-surface-border);text-decoration:none}
        .zazu-run-item:hover .zazu-run-main strong{color:var(--zazu-blue-primary)}
        .zazu-run-time{display:flex;flex-direction:column;gap:3px}
        .zazu-run-time strong{color:var(--zazu-text-main);font:700 11px/1 'JetBrains Mono',ui-monospace,monospace}
        .zazu-run-time span,.zazu-run-main span{color:var(--zazu-text-muted);font-size:9px}
        .zazu-run-marker{width:8px;height:8px;border-radius:50%;background:var(--zazu-blue-primary);box-shadow:0 0 0 3px color-mix(in srgb,var(--zazu-blue-primary) 12%,transparent)}
        .zazu-run-main{min-width:0;display:flex;flex-direction:column;gap:4px}
        .zazu-run-main strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--zazu-text-main);font-size:12px}
        .zazu-run-status{padding:5px 7px;border:1px solid var(--zazu-surface-border);border-radius:4px;color:var(--zazu-text-muted);font:700 8px/1 'JetBrains Mono',ui-monospace,monospace}
        .zazu-run-sheet-footer{display:flex;justify-content:space-between;gap:12px;padding-top:13px;color:var(--zazu-text-muted);font-size:9px}
        .zazu-run-sheet-footer a{color:var(--zazu-blue-primary);font-weight:750;text-decoration:none}
        .zazu-dashboard-empty{display:grid;gap:7px;padding:28px 0;color:var(--zazu-text-muted);font-size:11px}
        .zazu-dashboard-empty strong{color:var(--zazu-text-main)}
        .zazu-commercial-stat{margin-top:18px;padding:16px;border:1px solid var(--zazu-surface-border);border-radius:4px;background:var(--zazu-blue-canvas)}
        .zazu-commercial-stat span,.zazu-commercial-stat small{display:block;color:var(--zazu-text-muted);font-size:10px}
        .zazu-commercial-stat strong{display:block;margin:8px 0;color:var(--zazu-text-main);font:800 28px/1 'JetBrains Mono',ui-monospace,monospace}
        .zazu-commercial-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
        .zazu-dashboard-note{display:flex;gap:9px;margin-top:16px;padding:11px;border-left:2px solid var(--zazu-blue-primary);background:var(--zazu-surface-2)}
        .zazu-dashboard-note-mark{display:grid;place-items:center;width:17px;height:17px;border-radius:50%;background:var(--zazu-blue-primary);color:#fff;font:700 9px/1 'JetBrains Mono',monospace;flex:0 0 17px}
        .zazu-dashboard-note p{color:var(--zazu-text-muted);font-size:9px;line-height:1.55}
        .zazu-dashboard-assets{grid-column:2}
        .zazu-asset-manifest{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin-top:15px}
        .zazu-asset-manifest>div{display:flex;flex-direction:column;gap:5px;padding:12px;border:1px solid var(--zazu-surface-border);border-radius:4px;background:var(--zazu-surface-2)}
        .zazu-asset-manifest strong{font-size:10px;color:var(--zazu-text-main)}
        .zazu-asset-manifest small{color:var(--zazu-text-muted);font-size:9px}
        .zazu-asset-signal{width:7px;height:7px;border-radius:50%;background:#2FA36B;box-shadow:0 0 0 3px color-mix(in srgb,#2FA36B 12%,transparent)}
        .zazu-dashboard-quickbar{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-top:10px;padding:14px 16px}
        .zazu-dashboard-quickbar strong{display:block;margin-top:4px;color:var(--zazu-text-main);font-size:11px}
        .zazu-dashboard-quickbar nav{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:5px}
        .zazu-dashboard-quickbar nav a{padding:7px 9px;border:1px solid var(--zazu-surface-border);border-radius:4px;color:var(--zazu-text-muted);font-size:9px;font-weight:700;text-decoration:none}
        .zazu-dashboard-quickbar nav a:hover{color:var(--zazu-blue-primary);border-color:var(--zazu-blue-subtle);background:var(--zazu-blue-canvas)}
        @media(max-width:1000px){.zazu-dashboard-telemetry{grid-template-columns:repeat(2,minmax(0,1fr))}.zazu-dashboard-bento{grid-template-columns:1fr}.zazu-dashboard-workbench{grid-row:auto}.zazu-dashboard-assets{grid-column:auto}}
        @media(max-width:700px){.zazu-dashboard-setup,.zazu-dashboard-command,.zazu-dashboard-quickbar{flex-direction:column;align-items:flex-start}.zazu-run-item{grid-template-columns:70px 8px minmax(0,1fr);}.zazu-run-status{grid-column:3;justify-self:start}.zazu-asset-manifest{grid-template-columns:1fr}.zazu-dashboard-quickbar nav{justify-content:flex-start}.zazu-dashboard-telemetry{grid-template-columns:1fr 1fr}}
        @media(max-width:480px){.zazu-dashboard-telemetry{grid-template-columns:1fr}.zazu-dashboard-command{padding:18px}.zazu-dashboard-workbench,.zazu-dashboard-commercial,.zazu-dashboard-assets{padding:15px}.zazu-run-item{grid-template-columns:62px 8px minmax(0,1fr);gap:9px}}
    </style>
</x-app-layout>