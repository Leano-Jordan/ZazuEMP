<x-app-layout>
    <x-slot:title>Dashboard</x-slot:title>
    <x-slot:heading>Dashboard</x-slot:heading>

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
                <div class="zazu-dashboard-attention-kicker"><span class="zazu-status-dot" aria-hidden="true"></span><span>Live workspace</span></div>
                <h2 id="zazu-dashboard-attention-title" class="zazu-command-title">Your operation at a glance.</h2>
                <p class="zazu-command-copy">Jump straight into the work that moves the business forward. Zazu keeps operational records, commercial activity and planning connected.</p>
            </div>
            @if ($workspaceTools['work'])
                <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-primary">Open active jobs <span aria-hidden="true">→</span></a>
            @elseif ($workspaceTools['customers'])
                <a href="{{ route('customers.index') }}" class="zazu-btn zazu-btn-primary">Open customers <span aria-hidden="true">→</span></a>
            @endif
        </section>

        <section class="zazu-dashboard-metrics" aria-label="Key business metrics">
            @if ($workspaceTools['work']) <a href="{{ route('work.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-top"><h2 class="zazu-metric-label">Active jobs</h2><span class="zazu-metric-arrow" aria-hidden="true">↗</span></div><div class="zazu-metric-value" data-numeric="true">{{ $metrics['active_work'] }}</div><div class="zazu-metric-copy">Open operational records</div></a> @endif
            @if ($workspaceTools['calendar']) <a href="{{ route('calendar.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-top"><h2 class="zazu-metric-label">Next 14 days</h2><span class="zazu-metric-arrow" aria-hidden="true">↗</span></div><div class="zazu-metric-value" data-numeric="true">{{ $metrics['upcoming_work'] }}</div><div class="zazu-metric-copy">Scheduled work ahead</div></a> @endif
            @if ($workspaceTools['customers']) <a href="{{ route('customers.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-top"><h2 class="zazu-metric-label">Customers</h2><span class="zazu-metric-arrow" aria-hidden="true">↗</span></div><div class="zazu-metric-value" data-numeric="true">{{ $metrics['customers'] }}</div><div class="zazu-metric-copy">People and organisations</div></a> @endif
            @if ($workspaceTools['quotes']) <a href="{{ route('quotes.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-top"><h2 class="zazu-metric-label">Draft quotes</h2><span class="zazu-metric-arrow" aria-hidden="true">↗</span></div><div class="zazu-metric-value" data-numeric="true">{{ $metrics['draft_quotes'] }}</div><div class="zazu-metric-copy">Commercial work awaiting review</div></a> @endif
        </section>

        <div class="zazu-dashboard-lower">
            <section class="zazu-dashboard-work zazu-panel">
                <div class="zazu-panel-head">
                    <div><div class="zazu-panel-title">Upcoming work</div><div class="zazu-panel-copy">Scheduled jobs that are coming up next.</div></div>
                    @if ($workspaceTools['calendar'])<a href="{{ route('calendar.index') }}" class="zazu-btn zazu-btn-secondary">Calendar <span aria-hidden="true">→</span></a>@endif
                </div>
                <div class="zazu-list mt-3">
                    @if ($workspaceTools['work'] || $workspaceTools['calendar'])
                    @forelse ($upcoming as $event)
                        <div class="zazu-list-item">
                            @if ($workspaceTools['work'])
                                <a href="{{ route('work.show', $event) }}" class="zazu-list-main min-w-0 flex-1">
                            @else
                                <div class="zazu-list-main min-w-0 flex-1">
                            @endif
                                <div class="zazu-list-title">{{ $event->name }}</div>
                                <div class="zazu-list-meta">{{ $event->customer?->name ?? 'No customer' }} · {{ $event->reference }}</div>
                            @if ($workspaceTools['work'])
                                </a>
                            @else
                                </div>
                            @endif
                            <div class="zazu-list-side"><div class="zazu-side-primary" data-numeric="true">{{ $event->event_date?->format('d M Y') }}</div><div class="zazu-side-secondary">{{ $event->event_type ?: 'Work' }}</div></div>
                        </div>
                    @empty
                        <div class="zazu-empty"><div class="zazu-empty-title">Nothing scheduled yet</div><p class="zazu-empty-copy">Create a job with a scheduled date and it will appear here and on the calendar.</p>@if ($workspaceTools['work'])<a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary mt-4">Create job</a>@endif</div>
                    @endforelse
                    @else
                        <div class="zazu-empty"><div class="zazu-empty-title">Upcoming work is not available</div><p class="zazu-empty-copy">Your current access level does not include jobs or calendar planning.</p></div>
                    @endif
                </div>
            </section>

            <aside class="zazu-dashboard-quick zazu-panel" aria-labelledby="zazu-quick-access-title">
                <div class="zazu-panel-kicker">Workspace</div>
                <div id="zazu-quick-access-title" class="zazu-panel-title">Quick access</div>
                <div class="zazu-panel-copy">The places you use most to run the business.</div>
                @php
                    $quickAccess = [];
                    foreach ([
                        'services' => ['label' => 'Services & prices', 'route' => 'capabilities.index'],
                        'work' => ['label' => 'Jobs', 'route' => 'work.index'],
                        'customers' => ['label' => 'Customers', 'route' => 'customers.index'],
                        'quotes' => ['label' => 'Quotes', 'route' => 'quotes.index'],
                        'calendar' => ['label' => 'Calendar', 'route' => 'calendar.index'],
                        'finance' => ['label' => 'Finance', 'route' => 'finance.index'],
                        'purchasing' => ['label' => 'Purchasing', 'route' => 'purchasing.index'],
                        'suppliers' => ['label' => 'Suppliers', 'route' => 'suppliers.index'],
                        'inventory' => ['label' => 'Inventory', 'route' => 'inventory.index'],
                        'assets' => ['label' => 'Assets', 'route' => 'assets.index'],
                        'reports' => ['label' => 'Reports', 'route' => 'reports.index'],
                    ] as $tool => $item) {
                        if ($workspaceTools[$tool]) $quickAccess[] = $item;
                    }
                    if ($isOwner) $quickAccess[] = ['label' => 'Settings', 'route' => 'settings.index'];
                @endphp
                <nav class="zazu-quick-links" aria-label="Quick access">
                    @foreach ($quickAccess as $item)
                        <a href="{{ route($item['route']) }}" class="zazu-quick-link"><span class="zazu-quick-link-label">{{ $item['label'] }}</span><span class="zazu-quick-link-arrow" aria-hidden="true">↗</span></a>
                    @endforeach
                </nav>
            </aside>
        </div>
    </section>



</x-app-layout>
