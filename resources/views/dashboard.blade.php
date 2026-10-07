<x-app-layout>
    <x-slot:title>Dashboard</x-slot:title>
    <x-slot:heading>Dashboard</x-slot:heading>

    @if ($isOwner && (! $business?->catalogue_setup_completed_at || ! $business?->business_setup_completed_at))
        <section class="zazu-dash-setup">
            <div><span class="zazu-dash-kicker">Workspace setup</span><h2>Finish the foundation when you are ready.</h2><p>Complete business identity and catalogue setup without losing existing work.</p></div>
            <a href="{{ route('onboarding.index') }}" class="zazu-btn zazu-btn-primary">Continue setup <span>→</span></a>
        </section>
    @endif

    <section class="zazu-dash-hero">
        <div class="zazu-dash-hero-main">
            <div class="zazu-dash-kicker"><span class="zazu-dash-live"></span> ZAZU EMP / OPERATIONAL COMMAND · {{ $nicheDefinition['label'] }}</div>
            <h2>{{ $experienceLevel === 'basic' ? 'Keep the next operational action clear.' : 'Everything important, closer to the work.' }}</h2>
            <p>{{ $nicheDefinition['description'] }} {{ $experienceLevel === 'basic' ? 'Start with the next action and keep the rest quiet until you need it.' : 'Use the event record as the centre of operations, then reveal deeper operational detail when it helps.' }}</p>
            @if(count($supportingNiches))
                <div class="zazu-dash-context-note"><strong>Also in this business:</strong> {{ implode(' · ', $supportingNiches) }}. Zazu keeps these capabilities available when the job needs them.</div>
            @endif
            <div class="zazu-dash-actions">
                @if($workspaceTools['customers'] && app(\App\Support\PermissionService::class)->allows('customers.create', auth()->user(), $business))
                    <a href="{{ route('customers.create') }}" class="zazu-btn zazu-btn-secondary">Start with customer <span>+</span></a>
                @endif
                @if($workspaceTools['work'] && app(\App\Support\PermissionService::class)->allows('work.create', auth()->user(), $business))
                    <a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-cta">Start with job <span>+</span></a>
                    <a href="{{ route('work.create', ['start' => 'quote']) }}" class="zazu-btn zazu-btn-secondary">Start with quote <span>+</span></a>
                @endif
                @if($workspaceTools['calendar'])<a href="{{ route('calendar.index') }}" class="zazu-btn zazu-btn-secondary">Open calendar <span>→</span></a>@endif
            </div>        </div>
        <div class="zazu-dash-hero-side zazu-dash-hero-visual">
            @if($business?->dashboard_image_path)
                <img
                    src="{{ route('business.media', ['type' => 'dashboard']) }}?v={{ $business->updated_at?->timestamp ?? 0 }}"
                    alt="{{ $business->name }} dashboard image"
                    class="zazu-dash-hero-image"
                >
                <div class="zazu-dash-hero-overlay">
                    <span class="zazu-dash-side-label">Workspace state</span>
                    <strong>Operational</strong>
                    <span>Live workspace records</span>
                    <div class="zazu-dash-side-line"><i></i><span>Event operations</span><b>ONLINE</b></div>
                </div>
            @else
                <span class="zazu-dash-side-label">Workspace state</span>
                <strong>Operational</strong>
                <span>Live workspace records</span>
                <div class="zazu-dash-side-line"><i></i><span>Event operations</span><b>ONLINE</b></div>
            @endif
        </div>
    </section>

    <section class="zazu-dash-grid" aria-label="Dashboard operational overview">
        <section class="zazu-dash-run zazu-dash-surface">
            <header class="zazu-dash-surface-head">
                <div><span class="zazu-dash-kicker">Run-sheet</span><h3>Upcoming work</h3><p>Real events from the workspace, ordered by execution date.</p></div>
                @if($workspaceTools['work'])<a href="{{ route('work.index') }}" class="zazu-text-action">All work →</a>@endif
            </header>
            <div class="zazu-dash-timeline">
                @forelse($upcoming as $event)
                    <a href="{{ $workspaceTools['work'] ? route('work.show', $event) : route('calendar.index') }}" class="zazu-dash-event">
                        <div class="zazu-dash-event-time"><strong>{{ $event->event_date?->format('H:i') ?: '—' }}</strong><span>{{ $event->event_date?->format('d M Y') ?: 'Unscheduled' }}</span></div>
                        <div class="zazu-dash-event-line"><i></i></div>
                        <div class="zazu-dash-event-copy"><strong>{{ $event->name }}</strong><span>{{ $event->customer?->name ?? 'No customer' }}</span><small class="mono">{{ $event->reference }}</small></div>
                        <span class="zazu-dash-status status-{{ str_replace('_', '-', strtolower($event->status ?? 'open')) }}">{{ strtoupper(str_replace('_',' ',$event->status ?? 'OPEN')) }}</span>
                    </a>
                @empty
                    <div class="zazu-dash-empty"><strong>No scheduled work yet.</strong><span>Create a job with an event date and it will appear here.</span>@if($workspaceTools['work'])<a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary">Create job</a>@endif</div>
                @endforelse
            </div>
        </section>

        <aside class="zazu-dash-metrics">
            @if($workspaceTools['work'])
            <a href="{{ route('work.index') }}" class="zazu-dash-metric zazu-dash-surface"><span>Active events</span><strong>{{ number_format($metrics['active_work']) }}</strong><small>Open operational records</small><b>→</b></a>
            @endif
            @if($workspaceTools['calendar'])
            <a href="{{ route('calendar.index') }}" class="zazu-dash-metric zazu-dash-surface"><span>Next 14 days</span><strong>{{ number_format($metrics['upcoming_work']) }}</strong><small>Scheduled work ahead</small><b>→</b></a>
            @endif
            @if($workspaceTools['quotes'])
            <a href="{{ route('quotes.index') }}" class="zazu-dash-metric zazu-dash-surface"><span>Draft quotes</span><strong>{{ number_format($metrics['draft_quotes']) }}</strong><small>Commercial records awaiting action</small><b>→</b></a>
            @endif
            @if($workspaceTools['customers'])
            <a href="{{ route('customers.index') }}" class="zazu-dash-metric zazu-dash-surface"><span>Customers</span><strong>{{ number_format($metrics['customers']) }}</strong><small>People and organisations</small><b>→</b></a>
            @endif
        </aside>

        <section class="zazu-dash-commercial zazu-dash-surface">
            <header class="zazu-dash-surface-head"><div><span class="zazu-dash-kicker">Commercial queue</span><h3>Quote action</h3><p>Turn outstanding commercial work into the next deliberate action.</p></div></header>
            <div class="zazu-dash-commercial-main"><div><span>Draft quotes</span><strong>{{ number_format($metrics['draft_quotes']) }}</strong></div><span class="zazu-dash-queue-state {{ $metrics['draft_quotes'] > 0 ? 'is-warning' : 'is-clear' }}">{{ $metrics['draft_quotes'] > 0 ? 'ACTION REQUIRED' : 'QUEUE CLEAR' }}</span></div>
            <div class="zazu-dash-actions">
                @if($workspaceTools['quotes'])<a href="{{ route('quotes.index') }}" class="zazu-btn zazu-btn-primary">{{ $metrics['draft_quotes'] > 0 ? 'Review draft quotes' : 'Open quote queue' }}</a>@endif
                @if($workspaceTools['finance'])<a href="{{ route('finance.index') }}" class="zazu-btn zazu-btn-secondary">Open finance</a>@endif
            </div>
            <div class="zazu-dash-note"><b>i</b><span>Finance remains authoritative in its own workspace. This surface only tells you whether commercial records need attention.</span></div>
        </section>

        <section class="zazu-dash-focus zazu-dash-surface" aria-label="Priority actions">
            <header class="zazu-dash-surface-head">
                <div>
                    <span class="zazu-dash-kicker">Priority now</span>
                    <h3>What needs your attention</h3>
                    <p>A short action queue based on the records already visible in this workspace.</p>
                </div>
            </header>
            <div class="zazu-dash-focus-grid">
                @if($isOwner && (! $business?->catalogue_setup_completed_at || ! $business?->business_setup_completed_at))
                    <a href="{{ route('onboarding.index') }}" class="zazu-dash-focus-item is-warning">
                        <span class="zazu-dash-focus-marker"></span>
                        <span><strong>Finish workspace setup</strong><small>Business identity and catalogue setup are still incomplete.</small></span>
                        <b>→</b>
                    </a>
                @endif

                @if($upcoming->isNotEmpty())
                    @php($nextEvent = $upcoming->first())
                    <a href="{{ $workspaceTools['work'] ? route('work.show', $nextEvent) : route('calendar.index') }}" class="zazu-dash-focus-item is-info">
                        <span class="zazu-dash-focus-marker"></span>
                        <span><strong>Next scheduled work: {{ $nextEvent->name }}</strong><small>{{ $nextEvent->event_date?->format('d M Y · H:i') ?: 'Date to be confirmed' }} · {{ $nextEvent->customer?->name ?? 'No customer' }}</small></span>
                        <b>→</b>
                    </a>
                @endif

                @if($workspaceTools['quotes'] && $metrics['draft_quotes'] > 0)
                    <a href="{{ route('quotes.index') }}" class="zazu-dash-focus-item is-warning">
                        <span class="zazu-dash-focus-marker"></span>
                        <span><strong>{{ number_format($metrics['draft_quotes']) }} draft quote{{ $metrics['draft_quotes'] === 1 ? '' : 's' }} need review</strong><small>Commercial records are waiting for a decision or next step.</small></span>
                        <b>→</b>
                    </a>
                @endif

                @if($workspaceTools['purchasing'] && $experienceLevel !== 'basic' && $metrics['open_purchase_orders'] > 0)
                    <a href="{{ route('purchasing.index') }}" class="zazu-dash-focus-item is-warning">
                        <span class="zazu-dash-focus-marker"></span>
                        <span><strong>{{ number_format($metrics['open_purchase_orders']) }} purchase order{{ $metrics['open_purchase_orders'] === 1 ? '' : 's' }} remain open</strong><small>Supplier commitments still need receiving or closure.</small></span>
                        <b>→</b>
                    </a>
                @endif

                @if($workspaceTools['inventory'] && $experienceLevel !== 'basic' && $metrics['low_stock'] > 0)
                    <a href="{{ route('inventory.index') }}" class="zazu-dash-focus-item is-warning">
                        <span class="zazu-dash-focus-marker"></span>
                        <span><strong>{{ number_format($metrics['low_stock']) }} stock item{{ $metrics['low_stock'] === 1 ? '' : 's' }} need replenishment</strong><small>Items are at or below their reorder level.</small></span>
                        <b>→</b>
                    </a>
                @endif

                @if($workspaceTools['finance'] && $experienceLevel !== 'basic' && $metrics['unpaid_invoices'] > 0)
                    <a href="{{ route('finance.index') }}" class="zazu-dash-focus-item is-warning">
                        <span class="zazu-dash-focus-marker"></span>
                        <span><strong>{{ number_format($metrics['unpaid_invoices']) }} invoice{{ $metrics['unpaid_invoices'] === 1 ? '' : 's' }} need collection</strong><small>Issued invoices still have an outstanding balance.</small></span>
                        <b>→</b>
                    </a>
                @endif

                @if(
                    (!$isOwner || ($business?->catalogue_setup_completed_at && $business?->business_setup_completed_at))
                    && $upcoming->isEmpty()
                    && (!$workspaceTools['quotes'] || $metrics['draft_quotes'] === 0)
                    && ($experienceLevel === 'basic' || (
                        $metrics['open_purchase_orders'] === 0
                        && $metrics['low_stock'] === 0
                        && $metrics['unpaid_invoices'] === 0
                    ))
                )
                    <div class="zazu-dash-focus-item is-clear">
                        <span class="zazu-dash-focus-marker"></span>
                        <span><strong>No immediate priority flagged</strong><small>The workspace has no setup, schedule, quote or resource action to surface right now.</small></span>
                    </div>
                @endif
            </div>
        </section>

        @if($experienceLevel !== 'basic')
        <section class="zazu-dash-resources zazu-dash-surface">
            <header class="zazu-dash-surface-head">
                <div><span class="zazu-dash-kicker">Resource readiness</span><h3>{{ $nicheDefinition['label'] }} · resources &amp; money</h3><p>Live counts from the workspace show where an established business needs attention.</p></div>
                <div class="flex flex-wrap gap-2">
                    @if($workspaceTools['assets'])<a href="{{ route('assets.index') }}" class="zazu-text-action">Asset register →</a>@endif
                    @if($workspaceTools['inventory'])<a href="{{ route('inventory.index') }}" class="zazu-text-action">Stock control →</a>@endif
                </div>
            </header>
            <div class="zazu-dash-resource-grid">
                @if($workspaceTools['purchasing'])
                    <a href="{{ route('purchasing.index') }}" class="zazu-quick-link"><span><strong>Open purchase orders</strong><small class="block opacity-70">Supplier commitments still in motion</small></span><strong>{{ number_format($metrics['open_purchase_orders']) }}</strong></a>
                @endif
                @if($workspaceTools['inventory'])
                    <a href="{{ route('inventory.index') }}" class="zazu-quick-link"><span><strong>Low-stock items</strong><small class="block opacity-70">At or below reorder level</small></span><strong>{{ number_format($metrics['low_stock']) }}</strong></a>
                @endif
                @if($workspaceTools['assets'])
                    <a href="{{ route('assets.index') }}" class="zazu-quick-link"><span><strong>Available assets</strong><small class="block opacity-70">Ready for allocation</small></span><strong>{{ number_format($metrics['available_assets']) }}</strong></a>
                @endif
                @if($workspaceTools['finance'])
                    <a href="{{ route('finance.index') }}" class="zazu-quick-link"><span><strong>Unpaid invoices</strong><small class="block opacity-70">Issued invoices with a balance</small></span><strong>{{ number_format($metrics['unpaid_invoices']) }}</strong></a>
                @endif
            </div>
        </section>
        @endif
    </section>

    <section class="zazu-dash-commandbar">
        <div><span class="zazu-dash-kicker">Workspace surfaces</span><strong>Move directly into the record you need.</strong></div>
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