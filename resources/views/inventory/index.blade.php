<x-app-layout>
    <x-slot:title>Inventory</x-slot:title>
    <x-slot:heading>Inventory</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-secondary">Jobs</a></x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Resources</div>
            <h2 class="zazu-command-title">Inventory</h2>
            <p class="zazu-command-copy">Stock becomes trustworthy only when receiving, allocation, adjustment and movement rules share one transaction history.</p>
        </div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Stage</div><div class="zazu-command-meta-value">Foundation</div></div>
    </section>

    <section class="zazu-metric-grid">
        <a href="{{ route('assets.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-label">Reusable assets</div><div class="zazu-metric-value">→</div><div class="zazu-metric-copy">Open the asset accountability layer.</div></a>
        <a href="{{ route('work.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-label">Job allocation</div><div class="zazu-metric-value">→</div><div class="zazu-metric-copy">Open operational records and requirements.</div></a>
        <a href="{{ route('suppliers.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-label">Suppliers</div><div class="zazu-metric-value">→</div><div class="zazu-metric-copy">Open the buying relationship layer.</div></a>
        <div class="zazu-metric-card"><div class="zazu-metric-label">Stock truth</div><div class="zazu-metric-value">—</div><div class="zazu-metric-copy">Live quantities remain hidden until movement rules have an authoritative source.</div></div>
    </section>

    <section class="zazu-detail-grid mt-5">
        <div class="zazu-panel">
            <div class="zazu-panel-head">
                <div><div class="zazu-panel-title">Inventory control</div><div class="zazu-panel-copy">The system will prefer auditable movement history over manually edited totals.</div></div>
                <span class="zazu-chip zazu-chip-neutral">Not live</span>
            </div>
            <div class="zazu-placeholder">
                <div class="zazu-placeholder-title">No invented stock figures</div>
                <div class="zazu-placeholder-copy">Receiving, job allocation, returns, adjustments and availability should reconcile through one business rule set before stock is presented as fact.</div>
            </div>
        </div>
        <div class="zazu-detail-stack">
            <a href="{{ route('work.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Work requirements</div><div class="zazu-route-card-copy">See what jobs need before stock is allocated.</div></div><span class="zazu-route-card-arrow">→</span></a>
            <a href="{{ route('suppliers.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Supplier relationships</div><div class="zazu-route-card-copy">Open the future purchasing context.</div></div><span class="zazu-route-card-arrow">→</span></a>
            <a href="{{ route('assets.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Asset accountability</div><div class="zazu-route-card-copy">Separate reusable equipment from consumable stock.</div></div><span class="zazu-route-card-arrow">→</span></a>
        </div>
    </section>
</x-app-layout>