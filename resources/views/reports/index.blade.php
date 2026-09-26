<x-app-layout>
    <x-slot:title>Reports</x-slot:title>
    <x-slot:heading>Reports</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-secondary">Dashboard</a>
        <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-ghost">Jobs</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Insights</div>
            <h2 class="zazu-command-title">Operational reporting</h2>
            <p class="zazu-command-copy">These summaries are calculated from current Zazu records. They are visibility tools, not a second source of business truth.</p>
        </div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Live metrics</div><div class="zazu-command-meta-value">4</div></div>
    </section>

    <section class="zazu-metric-grid">
        <a href="{{ route('work.index') }}" class="zazu-metric-card zazu-metric-link">
            <div class="zazu-metric-label">Active jobs</div>
            <div class="zazu-metric-value">{{ $metrics['active_jobs'] }}</div>
            <div class="zazu-metric-copy">Current operational records.</div>
        </a>
        <a href="{{ route('calendar.index') }}" class="zazu-metric-card zazu-metric-link">
            <div class="zazu-metric-label">Next 14 days</div>
            <div class="zazu-metric-value">{{ $metrics['upcoming_jobs'] }}</div>
            <div class="zazu-metric-copy">Scheduled active work.</div>
        </a>
        <a href="{{ route('customers.index') }}" class="zazu-metric-card zazu-metric-link">
            <div class="zazu-metric-label">Customers</div>
            <div class="zazu-metric-value">{{ $metrics['customers'] }}</div>
            <div class="zazu-metric-copy">Customer records in this business.</div>
        </a>
        <a href="{{ route('work.index') }}" class="zazu-metric-card zazu-metric-link">
            <div class="zazu-metric-label">Outstanding preparation</div>
            <div class="zazu-metric-value">{{ $metrics['outstanding_preparation'] }}</div>
            <div class="zazu-metric-copy">Open or blocked readiness items.</div>
        </a>
    </section>

    <div class="zazu-detail-grid mt-5">
        <section class="zazu-panel">
            <div class="zazu-panel-head">
                <div>
                    <div class="zazu-panel-title">Jobs by status</div>
                    <div class="zazu-panel-copy">A direct count of current Work records by lifecycle state.</div>
                </div>
                <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-ghost">Open jobs</a>
            </div>
            <div class="mt-4 grid gap-2">
                @forelse ($jobsByStatus as $status => $total)
                    <div class="zazu-detail-row">
                        <div class="zazu-detail-label">{{ str_replace('_', ' ', ucfirst($status)) }}</div>
                        <div class="zazu-detail-value">{{ $total }}</div>
                    </div>
                @empty
                    <div class="zazu-empty"><div class="zazu-empty-title">No job records yet</div></div>
                @endforelse
            </div>
        </section>

        <section class="zazu-panel">
            <div class="zazu-panel-title">Commercial snapshots</div>
            <div class="zazu-panel-copy">Latest saved quote versions grouped without mixing currencies.</div>
            <div class="mt-4 grid gap-3">
                @forelse ($quoteTotalsByCurrency as $currency => $totals)
                    <div class="zazu-context-card">
                        <div class="zazu-detail-label">{{ $currency }} quotes</div>
                        <div class="zazu-command-meta-value mt-1">{{ $currency }} {{ number_format($totals['total'], 2) }}</div>
                        <div class="zazu-field-help">{{ $totals['count'] }} quote{{ $totals['count'] === 1 ? '' : 's' }} with a saved latest version</div>
                    </div>
                @empty
                    <div class="zazu-placeholder"><div class="zazu-placeholder-title">No quote totals yet</div><div class="zazu-placeholder-copy">Create a quote from a job with at least one service.</div></div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="zazu-panel mt-5">
        <div class="zazu-panel-head">
            <div>
                <div class="zazu-panel-title">Operating costs</div>
                <div class="zazu-panel-copy">Projected and actual operational cost records, kept separate by currency.</div>
            </div>
            <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-secondary">Open work</a>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @forelse ($costTotalsByCurrency as $currency => $totals)
                <div class="zazu-route-card">
                    <div>
                        <div class="zazu-route-card-title">{{ $currency }}</div>
                        <div class="zazu-route-card-copy">Projected {{ $currency }} {{ number_format($totals['projected'], 2) }} · Actual {{ $currency }} {{ number_format($totals['actual'], 2) }}</div>
                    </div>
                </div>
            @empty
                <div class="zazu-placeholder"><div class="zazu-placeholder-title">No operating costs yet</div><div class="zazu-placeholder-copy">Cost reporting will appear here as operational cost records are captured on jobs.</div></div>
            @endforelse
        </div>
    </section>

    <section class="zazu-detail-grid mt-5">
        <div class="zazu-panel">
            <div class="zazu-panel-title">Reporting boundary</div>
            <div class="zazu-panel-copy mt-1">Finance, profitability, payments and invoice reporting remain gated until their underlying transaction models exist.</div>
        </div>
        <div class="zazu-detail-stack">
            <a href="{{ route('quotes.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Quote history</div><div class="zazu-route-card-copy">Inspect the commercial records behind these totals.</div></div><span class="zazu-route-card-arrow">→</span></a>
            <a href="{{ route('customers.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Customers</div><div class="zazu-route-card-copy">Open relationship records behind operational reporting.</div></div><span class="zazu-route-card-arrow">→</span></a>
            <a href="{{ route('dashboard') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Dashboard</div><div class="zazu-route-card-copy">Return to the operational overview.</div></div><span class="zazu-route-card-arrow">→</span></a>
        </div>
    </section>
</x-app-layout>