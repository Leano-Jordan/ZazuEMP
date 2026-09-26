<x-app-layout>
    <x-slot:title>Inventory</x-slot:title>
    <x-slot:heading>Inventory</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('assets.index') }}" class="zazu-btn zazu-btn-secondary">Assets</a>
        <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-ghost">Jobs</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Resources</div>
            <h2 class="zazu-command-title">Inventory planning</h2>
            <p class="zazu-command-copy">The catalogue and live Work requirements provide the planning inputs today. Stock balances stay hidden until receiving, movement and allocation rules share one authoritative transaction history.</p>
        </div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Product items</div><div class="zazu-command-meta-value">{{ $products->count() }}</div></div>
    </section>

    <section class="zazu-metric-grid">
        <div class="zazu-metric-card">
            <div class="zazu-metric-label">Catalogue products</div>
            <div class="zazu-metric-value">{{ $products->count() }}</div>
            <div class="zazu-metric-copy">Reusable product definitions already captured.</div>
        </div>
        <div class="zazu-metric-card">
            <div class="zazu-metric-label">Upcoming demand lines</div>
            <div class="zazu-metric-value">{{ $demand->count() }}</div>
            <div class="zazu-metric-copy">Product-linked requirements on active jobs.</div>
        </div>
        <div class="zazu-metric-card">
            <div class="zazu-metric-label">Planned units</div>
            <div class="zazu-metric-value">{{ number_format($plannedUnits, 2) }}</div>
            <div class="zazu-metric-copy">Demand quantity, not confirmed stock on hand.</div>
        </div>
        <a href="{{ route('suppliers.index') }}" class="zazu-metric-card zazu-metric-link">
            <div class="zazu-metric-label">Buying context</div>
            <div class="zazu-metric-value">→</div>
            <div class="zazu-metric-copy">Open resource demand for future supplier workflows.</div>
        </a>
    </section>

    <section class="zazu-card zazu-list mt-5">
        <div class="zazu-card-header">
            <div class="zazu-eyebrow">Planning demand</div>
            <div class="zazu-card-title mt-1">Product requirements</div>
            <div class="zazu-card-description">Demand comes directly from active Work records and their saved service selections.</div>
        </div>
        @if ($demand->count())
            <div class="zazu-record-header" style="--zazu-record-cols: 2">
                <div class="zazu-record-header-note">Product</div>
                <div class="zazu-record-header-cell">Quantity</div>
                <div class="zazu-record-header-cell">Job date</div>
            </div>
        @endif
        @forelse ($demand as $requirement)
            <div class="zazu-list-item">
                <a href="{{ route('work.show', $requirement->event) }}" class="zazu-list-main min-w-0 flex-1">
                    <div class="zazu-list-title">{{ $requirement->capability?->name ?? $requirement->description }}</div>
                    <div class="zazu-list-meta">{{ $requirement->event->name }} · {{ $requirement->event->customer?->name ?? 'Customer not linked' }}</div>
                </a>
                <div class="zazu-list-side">
                    <div class="zazu-side-primary">{{ number_format((float) $requirement->quantity, 2) }} {{ $requirement->unit ?: 'units' }}</div>
                </div>
                <div class="zazu-list-side">
                    <div class="zazu-side-primary">{{ $requirement->event->event_date?->format('d M Y') ?? 'Date not set' }}</div>
                </div>
            </div>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No product demand yet</div>
                <p class="zazu-empty-copy">Products only appear here when a service or product from the catalogue is attached to an active job.</p>
                <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-primary mt-5">Open jobs</a>
            </div>
        @endforelse
    </section>

    <section class="zazu-detail-grid mt-5">
        <div class="zazu-panel">
            <div class="zazu-panel-title">Stock truth boundary</div>
            <div class="zazu-panel-copy mt-1">No balance is calculated from requirements alone.</div>
            <div class="zazu-placeholder">
                <div class="zazu-placeholder-title">Movement model still required</div>
                <div class="zazu-placeholder-copy">Receiving, adjustments, allocation, returns and availability need one auditable movement history before Zazu can safely show stock on hand.</div>
            </div>
        </div>
        <div class="zazu-detail-stack">
            <a href="{{ route('capabilities.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Catalogue</div><div class="zazu-route-card-copy">Review the product definitions feeding planning demand.</div></div><span class="zazu-route-card-arrow">→</span></a>
            <a href="{{ route('assets.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Assets</div><div class="zazu-route-card-copy">Keep reusable equipment separate from consumable movement.</div></div><span class="zazu-route-card-arrow">→</span></a>
        </div>
    </section>
</x-app-layout>