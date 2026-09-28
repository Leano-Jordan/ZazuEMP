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

        <div class="zazu-dashboard-side-stack">
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
        </div>

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

</x-app-layout>