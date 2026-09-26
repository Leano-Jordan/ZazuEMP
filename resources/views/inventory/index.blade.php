<x-app-layout>
    <x-slot:title>Inventory</x-slot:title>
    <x-slot:heading>Inventory</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('capabilities.index') }}" class="zazu-btn zazu-btn-secondary">Services & prices</a></x-slot:headerAction>

    <section class="zazu-command-band">
        <div><div class="zazu-eyebrow">Resources</div><h2 class="zazu-command-title">Inventory</h2><p class="zazu-command-copy">Stock visibility will connect what the business owns, what is available and what each job needs.</p></div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Stage</div><div class="zazu-command-meta-value">Foundation</div></div>
    </section>

    <section class="zazu-metric-grid">
        <a href="{{ route('assets.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-label">Owned assets</div><div class="zazu-metric-value">—</div><div class="zazu-metric-copy">Open the asset register →</div></a>
        <a href="{{ route('work.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-label">Job allocation</div><div class="zazu-metric-value">—</div><div class="zazu-metric-copy">Open jobs →</div></a>
        <div class="zazu-metric-card"><div class="zazu-metric-label">Low stock</div><div class="zazu-metric-value">—</div><div class="zazu-metric-copy">Live stock rules are not active yet.</div></div>
        <div class="zazu-metric-card"><div class="zazu-metric-label">Movements</div><div class="zazu-metric-value">—</div><div class="zazu-metric-copy">Stock movement history is not active yet.</div></div>
    </section>

    <section class="zazu-card mt-5">
        <div class="zazu-card-header"><div class="zazu-card-title">Inventory workspace</div><div class="zazu-card-description">No live quantities are shown until stock movements have an authoritative transaction model.</div></div>
        <div class="zazu-placeholder"><div class="zazu-placeholder-title">Inventory is being prepared</div><div class="zazu-placeholder-copy">Zazu will not invent stock figures. Inventory becomes useful when receiving, allocation, adjustment and availability rules are implemented together.</div></div>
    </section>
</x-app-layout>