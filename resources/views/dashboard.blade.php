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
            <div class="zazu-dash-kicker"><span class="zazu-dash-live"></span> YOUR BUSINESS AT A GLANCE · {{ $nicheDefinition['label'] }}</div>
            <span class="zazu-dash-greeting">{{ now()->hour < 12 ? 'Good morning' : (now()->hour < 18 ? 'Good afternoon' : 'Good evening') }}</span>
            <h2>{{ auth()->user()->name }} <span aria-hidden="true">👋</span></h2>
            <p>Run your event business from one place. Manage events, quotes, customers, inventory, purchasing and finance.</p>
        </div>
        <aside class="zazu-dash-hero-side zazu-dash-capability" aria-label="Business capabilities">
            <div class="zazu-dash-capability-copy">
                <span class="zazu-dash-side-label">Also in this business</span>
                <strong>{{ count($supportingNiches) ? implode(' · ', $supportingNiches) : $nicheDefinition['label'] }}</strong>
                <span>Zazu keeps these capabilities available when the job needs them.</span>
                @if($workspaceTools['services'])
                    <a href="{{ route('capabilities.index') }}" class="zazu-text-action">View services →</a>
                @endif
            </div>
            @if($business?->dashboard_image_path)
                <img
                    src="{{ route('business.media', ['type' => 'dashboard']) }}?v={{ $business->updated_at?->timestamp ?? 0 }}"
                    alt=""
                    aria-hidden="true"
                    class="zazu-dash-capability-art"
                >
            @endif
        </aside>
    </section>

    <section class="zazu-dash-grid" aria-label="Dashboard operational overview">
        <aside class="zazu-dash-metrics">
            @if($workspaceTools['work'])
            <a href="{{ route('work.index') }}" class="zazu-dash-metric zazu-dash-surface"><span class="zazu-dash-metric-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="15.5" rx="2.5"/><path d="M7.5 3.5v3M16.5 3.5v3M3.5 9.5h17"/></svg></span><span>Active events</span><strong>{{ number_format($metrics['active_work']) }}</strong><b>→</b></a>
            @endif
            @if($workspaceTools['quotes'])
            <a href="{{ route('quotes.index') }}" class="zazu-dash-metric zazu-dash-surface"><span class="zazu-dash-metric-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M7 3.5h7l4.5 4.5v12A1.5 1.5 0 0 1 17 21.5H7A1.5 1.5 0 0 1 5.5 20V5A1.5 1.5 0 0 1 7 3.5Z"/><path d="M14 3.5V8h4.5M8.5 12h7M8.5 16h7"/></svg></span><span>Draft quotes</span><strong>{{ number_format($metrics['draft_quotes']) }}</strong><b>→</b></a>
            @elseif($workspaceTools['finance'] && $experienceLevel !== 'basic')
            <a href="{{ route('finance.index') }}" class="zazu-dash-metric zazu-dash-surface"><span>Unpaid invoices</span><strong>{{ number_format($metrics['unpaid_invoices']) }}</strong><small>Issued invoices with a balance</small><b>→</b></a>
            @endif
            @if($workspaceTools['customers'])
            <a href="{{ route('customers.index') }}" class="zazu-dash-metric zazu-dash-surface"><span class="zazu-dash-metric-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3.25"/><path d="M3.5 19.5v-1.2a5.5 5.5 0 0 1 11 0v1.2M16 5.2a3.25 3.25 0 0 1 0 6.2M17 14.5a4.5 4.5 0 0 1 3.5 4.4v.6"/></svg></span><span>Customers</span><strong>{{ number_format($metrics['customers']) }}</strong><b>→</b></a>
            @endif
            @if($workspaceTools['calendar'])
            <a href="{{ route('calendar.index') }}" class="zazu-dash-metric zazu-dash-surface"><span class="zazu-dash-metric-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="15.5" rx="2.5"/><path d="M7.5 3.5v3M16.5 3.5v3M3.5 9.5h17M12 12.5v3l2 1.2"/></svg></span><span>Next 14 days</span><strong>{{ number_format($metrics['upcoming_work']) }}</strong><b>→</b></a>
            @endif
        </aside>

        <section class="zazu-dash-quick-actions zazu-dash-surface" aria-label="Quick actions">
            <div class="zazu-dash-actions">
                @if($workspaceTools['work'] && app(\App\Support\PermissionService::class)->allows('work.create', auth()->user(), $business))
                    <a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-cta">Create event <span>+</span></a>
                    <a href="{{ route('work.create', ['start' => 'quote']) }}" class="zazu-btn zazu-btn-secondary">Create quote <span>+</span></a>
                @endif
                @if($workspaceTools['customers'] && app(\App\Support\PermissionService::class)->allows('customers.create', auth()->user(), $business))
                    <a href="{{ route('customers.create') }}" class="zazu-btn zazu-btn-secondary">Add customer <span>+</span></a>
                @endif
                @if($workspaceTools['calendar'])
                    <a href="{{ route('calendar.index') }}" class="zazu-btn zazu-btn-secondary">Open calendar <span>→</span></a>
                @endif
            </div>
        </section>

        <section class="zazu-dash-run zazu-dash-surface">
            <header class="zazu-dash-surface-head">
                <div><span class="zazu-dash-kicker">Run-sheet</span><h3>Upcoming events</h3></div>
                @if($workspaceTools['work'])<a href="{{ route('work.index') }}" class="zazu-text-action">All work →</a>@endif
            </header>
            <div class="zazu-dash-timeline">
                @forelse($upcoming as $event)
                    <a href="{{ $workspaceTools['work'] ? route('work.show', $event) : route('calendar.index') }}" class="zazu-dash-event">
                        <div class="zazu-dash-event-time"><strong>{{ $event->event_date ? 'DATE SET' : 'DATE TBC' }}</strong><span>{{ $event->event_date?->format('d M Y') ?: 'Date to be confirmed' }}</span></div>
                        <div class="zazu-dash-event-line"><i></i></div>
                        <div class="zazu-dash-event-copy"><strong>{{ $event->name }}</strong><span>{{ $event->customer?->name ?? 'No customer' }}</span><small class="mono">{{ $event->reference }}</small></div>
                        <span class="zazu-dash-status status-{{ str_replace('_', '-', strtolower($event->status ?? 'open')) }}">{{ strtoupper(str_replace('_',' ',$event->status ?? 'OPEN')) }}</span>
                    </a>
                @empty
                    <div class="zazu-dash-empty"><strong>No scheduled work yet.</strong><span>Create a job with an event date and it will appear here.</span>@if($workspaceTools['work'])<a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary">Create job</a>@endif</div>
                @endforelse
            </div>
        </section>



        <section class="zazu-dash-commercial zazu-dash-surface">
            <header class="zazu-dash-surface-head"><div><span class="zazu-dash-kicker">Commercial queue</span><h3>Quote pipeline</h3></div></header>
            <div class="zazu-dash-quote-stages" aria-label="Quote status breakdown">
                <div class="zazu-dash-quote-stage is-draft"><span>Draft</span><strong>{{ number_format((int) ($quoteStatusCounts['draft'] ?? 0)) }}</strong><small>Being prepared</small></div>
                <div class="zazu-dash-quote-stage is-sent"><span>Sent</span><strong>{{ number_format((int) ($quoteStatusCounts['sent'] ?? 0)) }}</strong><small>Awaiting response</small></div>
                <div class="zazu-dash-quote-stage is-accepted"><span>Accepted</span><strong>{{ number_format((int) ($quoteStatusCounts['accepted'] ?? 0)) }}</strong><small>Customer approved</small></div>
                <div class="zazu-dash-quote-stage is-closed"><span>Closed</span><strong>{{ number_format((int) (($quoteStatusCounts['declined'] ?? 0) + ($quoteStatusCounts['expired'] ?? 0))) }}</strong><small>Declined or expired</small></div>
            </div>
            <div class="zazu-dash-queue-state {{ $metrics['draft_quotes'] > 0 ? 'is-warning' : 'is-clear' }}">
                <strong>{{ $metrics['draft_quotes'] > 0 ? 'Action needed' : 'Queue clear' }}</strong>
                <span>{{ $metrics['draft_quotes'] > 0 ? 'Draft quotes are waiting for the next step.' : 'No draft quotes are waiting for action.' }}</span>
            </div>
            <div class="zazu-dash-actions">
                @if($workspaceTools['quotes'])<a href="{{ route('quotes.index') }}" class="zazu-btn zazu-btn-primary">{{ $metrics['draft_quotes'] > 0 ? 'Review draft quotes' : 'Open quote queue' }}</a>@endif
                @if($workspaceTools['finance'])<a href="{{ route('finance.index') }}" class="zazu-btn zazu-btn-secondary">Open finance</a>@endif
            </div>
        </section>

        <section class="zazu-dash-focus zazu-dash-surface" aria-label="Priority actions">
            <header class="zazu-dash-surface-head">
                <div>
                    <span class="zazu-dash-kicker">Priority now</span>
                    <h3>What needs your attention</h3>
                </div>
            </header>
            <div class="zazu-dash-focus-grid">
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
                <div><span class="zazu-dash-kicker">Workspace health</span><h3>Operational readiness</h3></div>
                <div class="flex flex-wrap gap-2">
                    @if($workspaceTools['assets'])<a href="{{ route('assets.index') }}" class="zazu-text-action">Asset register →</a>@endif
                    @if($workspaceTools['inventory'])<a href="{{ route('inventory.index') }}" class="zazu-text-action">Stock control →</a>@endif
                </div>
            </header>
            <div class="zazu-dash-resource-grid">
                @if($workspaceTools['purchasing'])
                    <a href="{{ route('purchasing.index') }}" class="zazu-quick-link"><span><strong>Open purchase orders</strong></span><strong>{{ number_format($metrics['open_purchase_orders']) }}</strong></a>
                @endif
                @if($workspaceTools['inventory'])
                    <a href="{{ route('inventory.index') }}" class="zazu-quick-link"><span><strong>Low-stock items</strong></span><strong>{{ number_format($metrics['low_stock']) }}</strong></a>
                @endif
                @if($workspaceTools['assets'])
                    <a href="{{ route('assets.index') }}" class="zazu-quick-link"><span><strong>Available assets</strong></span><strong>{{ number_format($metrics['available_assets']) }}</strong></a>
                @endif
                @if($workspaceTools['finance'])
                    <a href="{{ route('finance.index') }}" class="zazu-quick-link"><span><strong>Unpaid invoices</strong></span><strong>{{ number_format($metrics['unpaid_invoices']) }}</strong></a>
                @endif
            </div>
        </section>
        @endif
    </section>


</x-app-layout>