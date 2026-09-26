<x-app-layout>
    <x-slot:title>Suppliers</x-slot:title>
    <x-slot:heading>Suppliers</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('inventory.index') }}" class="zazu-btn zazu-btn-secondary">Inventory</a>
        <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-ghost">Jobs</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Resources</div>
            <h2 class="zazu-command-title">Supplier planning</h2>
            <p class="zazu-command-copy">Use current job demand and catalogue resources as the planning context for future supplier management. No supplier records are fabricated before that transaction model exists.</p>
        </div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Resource drivers</div><div class="zazu-command-meta-value">{{ $resourceRequirementCount }}</div></div>
    </section>

    <section class="zazu-metric-grid">
        <a href="{{ route('inventory.index') }}" class="zazu-metric-card zazu-metric-link">
            <div class="zazu-metric-label">Consumables</div>
            <div class="zazu-metric-value">{{ $resourceCatalogue->where('capability_type', 'product')->count() }}</div>
            <div class="zazu-metric-copy">Catalogue items that may require purchasing.</div>
        </a>
        <a href="{{ route('assets.index') }}" class="zazu-metric-card zazu-metric-link">
            <div class="zazu-metric-label">Rental resources</div>
            <div class="zazu-metric-value">{{ $resourceCatalogue->where('capability_type', 'rental')->count() }}</div>
            <div class="zazu-metric-copy">Catalogue items that may require hire relationships.</div>
        </a>
        <div class="zazu-metric-card">
            <div class="zazu-metric-label">Active resource demand</div>
            <div class="zazu-metric-value">{{ $resourceRequirementCount }}</div>
            <div class="zazu-metric-copy">Current job requirements linked to resource-type catalogue items.</div>
        </div>
    </section>

    <section class="zazu-card zazu-list mt-5">
        <div class="zazu-card-header">
            <div class="zazu-eyebrow">Connected planning context</div>
            <div class="zazu-card-title mt-1">Resource-linked job requirements</div>
            <div class="zazu-card-description">These are real Work requirements already captured in Zazu. Supplier identity, pricing terms and purchase history will attach here when that model is introduced.</div>
        </div>
        @if ($resourceRequirements->count())
            <div class="zazu-record-header" style="--zazu-record-cols: 2">
                <div class="zazu-record-header-note">Requirement</div>
                <div class="zazu-record-header-cell">Job</div>
                <div class="zazu-record-header-cell">Need date</div>
            </div>
        @endif
        @forelse ($resourceRequirements as $requirement)
            <div class="zazu-list-item">
                <div class="zazu-list-main">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="zazu-list-title">{{ $requirement->description }}</span>
                        <span class="zazu-chip zazu-chip-info">{{ ucfirst($requirement->capability?->capability_type ?? 'resource') }}</span>
                    </div>
                    <div class="zazu-list-meta">{{ number_format((float) $requirement->quantity, 2) }} {{ $requirement->unit ?: 'units' }} · {{ $requirement->category }}</div>
                </div>
                <a href="{{ route('work.show', $requirement->event) }}" class="zazu-list-main min-w-0 flex-1">
                    <div class="zazu-list-title">{{ $requirement->event->name }}</div>
                    <div class="zazu-list-meta">{{ $requirement->event->customer?->name ?? 'Customer not linked' }}</div>
                </a>
                <div class="zazu-list-side">
                    <div class="zazu-side-primary">{{ $requirement->event->event_date?->format('d M Y') ?? 'Date not set' }}</div>
                </div>
            </div>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No resource demand yet</div>
                <p class="zazu-empty-copy">Add services or resource-type catalogue items to a job and the planning context will appear here.</p>
                <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-primary mt-5">Open jobs</a>
            </div>
        @endforelse
    </section>

    <section class="zazu-detail-grid mt-5">
        <div class="zazu-panel">
            <div class="zazu-panel-title">Supplier register boundary</div>
            <div class="zazu-panel-copy mt-1">The page is now connected to real demand, while supplier transactions remain intentionally absent.</div>
            <div class="zazu-placeholder">
                <div class="zazu-placeholder-title">Next data layer</div>
                <div class="zazu-placeholder-copy">Supplier identity, contacts, terms, status, supplier-linked catalogue items and purchasing history can be added without duplicating the Work or catalogue records already shown above.</div>
            </div>
        </div>
        <div class="zazu-detail-stack">
            <a href="{{ route('capabilities.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Services & prices</div><div class="zazu-route-card-copy">Review the reusable catalogue driving current demand.</div></div><span class="zazu-route-card-arrow">→</span></a>
            <a href="{{ route('calendar.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Upcoming work</div><div class="zazu-route-card-copy">Check timing before planning commitments.</div></div><span class="zazu-route-card-arrow">→</span></a>
        </div>
    </section>
</x-app-layout>