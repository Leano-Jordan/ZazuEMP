<x-app-layout>
    <x-slot:title>Assets</x-slot:title>
    <x-slot:heading>Assets</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('inventory.index') }}" class="zazu-btn zazu-btn-secondary">Inventory</a></x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Resources</div>
            <h2 class="zazu-command-title">Reusable assets</h2>
            <p class="zazu-command-copy">Treat equipment as accountable business resources with availability, condition, allocation and return history.</p>
        </div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Stage</div><div class="zazu-command-meta-value">Foundation</div></div>
    </section>

    <section class="zazu-module-grid">
        <a href="{{ route('inventory.index') }}" class="zazu-module"><div class="zazu-module-top"><span class="zazu-module-group">Stock boundary</span><span class="zazu-module-arrow">→</span></div><div class="zazu-module-title">Consumables vs assets</div><div class="zazu-module-copy">Use inventory for consumable movement and assets for reusable equipment that must come back.</div></a>
        <a href="{{ route('work.index') }}" class="zazu-module"><div class="zazu-module-top"><span class="zazu-module-group">Allocation</span><span class="zazu-module-arrow">→</span></div><div class="zazu-module-title">Job allocation</div><div class="zazu-module-copy">Open work records where future asset allocation will happen in context.</div></a>
        <a href="{{ route('calendar.index') }}" class="zazu-module"><div class="zazu-module-top"><span class="zazu-module-group">Availability</span><span class="zazu-module-arrow">→</span></div><div class="zazu-module-title">Event timing</div><div class="zazu-module-copy">Use the job calendar as the timing reference for operational planning.</div></a>
        <a href="{{ route('suppliers.index') }}" class="zazu-module"><div class="zazu-module-top"><span class="zazu-module-group">External resources</span><span class="zazu-module-arrow">→</span></div><div class="zazu-module-title">Hire relationships</div><div class="zazu-module-copy">Open supplier context for equipment that may be sourced externally.</div></a>
    </section>

    <section class="zazu-detail-grid mt-5">
        <div class="zazu-panel">
            <div class="zazu-panel-head">
                <div><div class="zazu-panel-title">Asset register</div><div class="zazu-panel-copy">No fake availability or condition values are displayed.</div></div>
                <span class="zazu-chip zazu-chip-neutral">Not live</span>
            </div>
            <div class="zazu-placeholder">
                <div class="zazu-placeholder-title">Accountability comes before inventory decoration</div>
                <div class="zazu-placeholder-copy">The finished model should capture ownership, condition, availability, job allocation, return and damage/loss evidence as one auditable chain.</div>
            </div>
        </div>
        <div class="zazu-detail-stack">
            <a href="{{ route('inventory.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Inventory</div><div class="zazu-route-card-copy">Separate stock movement from reusable assets.</div></div><span class="zazu-route-card-arrow">→</span></a>
            <a href="{{ route('work.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Jobs</div><div class="zazu-route-card-copy">Open the operational workspace.</div></div><span class="zazu-route-card-arrow">→</span></a>
        </div>
    </section>
</x-app-layout>