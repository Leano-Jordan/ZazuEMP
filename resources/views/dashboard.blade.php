<x-app-layout>
    <x-slot:title>Dashboard</x-slot:title>
    <x-slot:heading>Dashboard</x-slot:heading>

    <section class="zazu-command-band">
        <div><div class="zazu-eyebrow">Overview</div><h2 class="zazu-command-title">Business overview</h2><p class="zazu-command-copy">See current work, customers and quotes in one place.</p></div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Today</div><div class="zazu-command-meta-value">{{ now()->format('d M') }}</div></div>
    </section>

    <section class="zazu-metric-grid">
        <a href="{{ route('work.index') }}" class="zazu-metric-card zazu-metric-link"><h2 class="zazu-metric-label">Active jobs</h2><div class="zazu-metric-value">{{ $metrics['active_work'] }}</div><div class="zazu-metric-copy">Open the job list →</div></a>
        <a href="{{ route('calendar.index') }}" class="zazu-metric-card zazu-metric-link"><h2 class="zazu-metric-label">Next 14 days</h2><div class="zazu-metric-value">{{ $metrics['upcoming_work'] }}</div><div class="zazu-metric-copy">Open the calendar →</div></a>
        <a href="{{ route('customers.index') }}" class="zazu-metric-card zazu-metric-link"><h2 class="zazu-metric-label">Customers</h2><div class="zazu-metric-value">{{ $metrics['customers'] }}</div><div class="zazu-metric-copy">Open customer records →</div></a>
        <a href="{{ route('quotes.index') }}" class="zazu-metric-card zazu-metric-link"><h2 class="zazu-metric-label">Draft quotes</h2><div class="zazu-metric-value">{{ $metrics['draft_quotes'] }}</div><div class="zazu-metric-copy">Review draft quotes →</div></a>
    </section>

    @if (!empty($business?->dashboard_image_path))
        <section class="zazu-dashboard-visual">
            <img src="{{ Storage::disk('public')->url($business->dashboard_image_path) }}" alt="" loading="lazy">
            <div class="zazu-dashboard-visual-overlay">
                <div class="zazu-eyebrow">Your business</div>
                <div class="zazu-dashboard-visual-title">{{ $business->name }}</div>
                <div class="zazu-dashboard-visual-copy">Your workspace, your services, your jobs.</div>
            </div>
        </section>
    @endif

    <div class="zazu-detail-grid">
        <section class="zazu-panel">
            <div class="zazu-panel-head">
                <div><div class="zazu-panel-title">Upcoming work</div><div class="zazu-panel-copy">Work scheduled for future dates.</div></div>
                <a href="{{ route('calendar.index') }}" class="zazu-btn zazu-btn-secondary">Calendar</a>
            </div>
            <div class="zazu-list mt-3">
                @forelse ($upcoming as $event)
                    <div class="zazu-list-item">
                        <a href="{{ route('work.show', $event) }}" class="zazu-list-main min-w-0 flex-1">
                            <div class="zazu-list-title">{{ $event->name }}</div>
                            <div class="zazu-list-meta">{{ $event->customer?->name ?? 'No customer' }} · {{ $event->reference }}</div>
                        </a>
                        <div class="zazu-list-side"><div class="zazu-side-primary">{{ $event->event_date?->format('d M Y') }}</div><div class="zazu-side-secondary">{{ $event->event_type ?: 'Work' }}</div></div>
                    </div>
                @empty
                    <div class="zazu-empty"><div class="zazu-empty-title">Nothing scheduled yet</div><p class="zazu-empty-copy">Create a job with a scheduled date and it will appear here and on the calendar.</p><a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary mt-5">Create job</a></div>
                @endforelse
            </div>
        </section>

        <section class="zazu-panel">
            <div class="zazu-panel-title">Quick access</div>
            <div class="zazu-panel-copy">Open a business area quickly.</div>
            <div class="mt-4 grid gap-2">
                @foreach ([
                    ['label' => 'Jobs', 'route' => 'work.index'],
                    ['label' => 'Customers', 'route' => 'customers.index'],
                    ['label' => 'Quotes', 'route' => 'quotes.index'],
                    ['label' => 'Services & prices', 'route' => 'capabilities.index'],
                    ['label' => 'Settings', 'route' => 'settings.index'],
                ] as $item)
                    <a href="{{ route($item['route']) }}" class="zazu-quick-link flex items-center justify-between rounded-lg border border-[var(--zazu-border)] bg-[var(--zazu-surface-2)] px-3 py-2 text-xs font-semibold text-[var(--zazu-ink-2)] no-underline hover:border-[var(--zazu-border-strong)] hover:text-[var(--zazu-link)]"><span>{{ $item['label'] }}</span><span aria-hidden="true">→</span></a>
                @endforeach
            </div>
        </section>
    </div>

    <section class="zazu-module-grid mt-5">
        @foreach ([
            ['label' => 'Jobs', 'copy' => 'Jobs, services, quotes and preparation.', 'route' => 'work.index', 'group' => 'Operations'],
            ['label' => 'Customers', 'copy' => 'Customer details, contacts and work history.', 'route' => 'customers.index', 'group' => 'Relationships'],
            ['label' => 'Quotes', 'copy' => 'Quotes and pricing history.', 'route' => 'quotes.index', 'group' => 'Commercial'],
            ['label' => 'Calendar', 'copy' => 'Dates and operational timing.', 'route' => 'calendar.index', 'group' => 'Operations'],
            ['label' => 'Suppliers', 'copy' => 'Buying relationships.', 'route' => 'suppliers.index', 'group' => 'Resources'],
            ['label' => 'Inventory', 'copy' => 'Stock and movement.', 'route' => 'inventory.index', 'group' => 'Resources'],
            ['label' => 'Assets', 'copy' => 'Reusable equipment and accountability.', 'route' => 'assets.index', 'group' => 'Resources'],
            ['label' => 'Reports', 'copy' => 'Business information from your records.', 'route' => 'reports.index', 'group' => 'Insights'],
            ['label' => 'Settings', 'copy' => 'Business and system controls.', 'route' => 'settings.index', 'group' => 'System'],
            ['label' => 'Services & prices', 'copy' => 'Your reusable services, rentals and usual prices.', 'route' => 'capabilities.index', 'group' => 'Catalogue'],
        ] as $module)
            <a href="{{ route($module['route']) }}" class="zazu-module">
                <div class="zazu-module-top"><span class="zazu-module-group">{{ $module['group'] }}</span><span class="zazu-module-arrow" aria-hidden="true">→</span></div>
                <div class="zazu-module-title">{{ $module['label'] }}</div>
                <div class="zazu-module-copy">{{ $module['copy'] }}</div>
            </a>
        @endforeach
    </section>
</x-app-layout>
