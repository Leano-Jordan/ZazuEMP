<x-app-layout>
    <x-slot:title>Dashboard</x-slot:title>
    <x-slot:heading>Dashboard</x-slot:heading>

    <section class="zazu-command-band">
        <div><div class="zazu-eyebrow">Overview</div><h2 class="zazu-command-title">Business overview</h2><p class="zazu-command-copy">See current work, customers and quotes in one place.</p></div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Today</div><div class="zazu-command-meta-value">{{ now()->format('d M') }}</div></div>
    </section>

    @if ($isOwner && (! $business?->catalogue_setup_completed_at || ! $business?->business_setup_completed_at))
        <section class="zazu-next-action">
            <div>
                <div class="zazu-eyebrow">Setup centre</div>
                <h2 class="zazu-next-action-title">Keep building your business workspace.</h2>
                <p class="zazu-next-action-copy">Services, business identity and optional tax/compliance setup can be completed later without losing your progress.</p>
            </div>
            <a href="{{ route('onboarding.index') }}" class="zazu-btn zazu-btn-primary">Continue setup →</a>
        </section>
    @endif

    <section class="zazu-dashboard-bento mt-3" aria-label="Business command centre">
        <section class="zazu-dashboard-attention" aria-labelledby="zazu-dashboard-attention-title">
            <div>
                <div class="zazu-eyebrow">Operational overview</div>
                <h2 id="zazu-dashboard-attention-title" class="zazu-command-title">What needs attention next?</h2>
                <p class="zazu-command-copy">Start with active jobs, upcoming dates and commercial activity. Open a record to continue without leaving your workspace.</p>
            </div>
            <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-primary">Open active jobs <span aria-hidden="true">→</span></a>
        </section>

        <section class="zazu-dashboard-metrics" aria-label="Key business metrics">
            <a href="{{ route('work.index') }}" class="zazu-metric-card zazu-metric-link"><h2 class="zazu-metric-label">Active jobs</h2><div class="zazu-metric-value" data-numeric="true">{{ $metrics['active_work'] }}</div><div class="zazu-metric-copy">Open job list</div></a>
            <a href="{{ route('calendar.index') }}" class="zazu-metric-card zazu-metric-link"><h2 class="zazu-metric-label">Next 14 days</h2><div class="zazu-metric-value" data-numeric="true">{{ $metrics['upcoming_work'] }}</div><div class="zazu-metric-copy">Open calendar</div></a>
            <a href="{{ route('customers.index') }}" class="zazu-metric-card zazu-metric-link"><h2 class="zazu-metric-label">Customers</h2><div class="zazu-metric-value" data-numeric="true">{{ $metrics['customers'] }}</div><div class="zazu-metric-copy">Open customer records</div></a>
            <a href="{{ route('quotes.index') }}" class="zazu-metric-card zazu-metric-link"><h2 class="zazu-metric-label">Draft quotes</h2><div class="zazu-metric-value" data-numeric="true">{{ $metrics['draft_quotes'] }}</div><div class="zazu-metric-copy">Review draft quotes</div></a>
        </section>

        <div class="zazu-dashboard-lower">
            <section class="zazu-dashboard-work zazu-panel">
                <div class="zazu-panel-head">
                    <div><div class="zazu-panel-title">Upcoming work</div><div class="zazu-panel-copy">Scheduled jobs that are coming up next.</div></div>
                    <a href="{{ route('calendar.index') }}" class="zazu-btn zazu-btn-secondary">Calendar <span aria-hidden="true">→</span></a>
                </div>
                <div class="zazu-list mt-3">
                    @forelse ($upcoming as $event)
                        <div class="zazu-list-item">
                            <a href="{{ route('work.show', $event) }}" class="zazu-list-main min-w-0 flex-1">
                                <div class="zazu-list-title">{{ $event->name }}</div>
                                <div class="zazu-list-meta">{{ $event->customer?->name ?? 'No customer' }} · {{ $event->reference }}</div>
                            </a>
                            <div class="zazu-list-side"><div class="zazu-side-primary" data-numeric="true">{{ $event->event_date?->format('d M Y') }}</div><div class="zazu-side-secondary">{{ $event->event_type ?: 'Work' }}</div></div>
                        </div>
                    @empty
                        <div class="zazu-empty"><div class="zazu-empty-title">Nothing scheduled yet</div><p class="zazu-empty-copy">Create a job with a scheduled date and it will appear here and on the calendar.</p><a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary mt-4">Create job</a></div>
                    @endforelse
                </div>
            </section>

            <aside class="zazu-dashboard-quick zazu-panel" aria-labelledby="zazu-quick-access-title">
                <div id="zazu-quick-access-title" class="zazu-panel-title">Quick access</div>
                <div class="zazu-panel-copy">Common operating areas.</div>
                @php
                    $quickAccess = [
                        ['label' => 'Services & prices', 'route' => 'capabilities.index'],
                        ['label' => 'Jobs', 'route' => 'work.index'],
                        ['label' => 'Customers', 'route' => 'customers.index'],
                        ['label' => 'Quotes', 'route' => 'quotes.index'],
                    ];
                    if ($workspaceTools['finance']) $quickAccess[] = ['label' => 'Finance', 'route' => 'finance.index'];
                    if ($workspaceTools['purchasing']) $quickAccess[] = ['label' => 'Purchasing', 'route' => 'purchasing.index'];
                    if ($workspaceTools['inventory']) $quickAccess[] = ['label' => 'Inventory', 'route' => 'inventory.index'];
                    if ($workspaceTools['assets']) $quickAccess[] = ['label' => 'Assets', 'route' => 'assets.index'];
                    if ($isOwner) $quickAccess[] = ['label' => 'Settings', 'route' => 'settings.index'];
                @endphp
                <nav class="zazu-quick-links" aria-label="Quick access">
                    @foreach ($quickAccess as $item)
                        <a href="{{ route($item['route']) }}" class="zazu-quick-link"><span>{{ $item['label'] }}</span><span aria-hidden="true">→</span></a>
                    @endforeach
                </nav>
            </aside>
        </div>
    </section>



</x-app-layout>
