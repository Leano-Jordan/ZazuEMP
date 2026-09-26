<x-app-layout>
    <x-slot:title>Assets</x-slot:title>
    <x-slot:heading>Assets</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('inventory.index') }}" class="zazu-btn zazu-btn-secondary">Inventory</a>
        <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-ghost">Jobs</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Resources</div>
            <h2 class="zazu-command-title">Asset planning</h2>
            <p class="zazu-command-copy">Rental catalogue items and job requirements now provide a connected demand view. Actual asset ownership, condition and return history remain separate records.</p>
        </div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Rental items</div><div class="zazu-command-meta-value">{{ $rentalCapabilities->count() }}</div></div>
    </section>

    <section class="zazu-metric-grid">
        <div class="zazu-metric-card">
            <div class="zazu-metric-label">Rental catalogue</div>
            <div class="zazu-metric-value">{{ $rentalCapabilities->count() }}</div>
            <div class="zazu-metric-copy">Reusable catalogue definitions already captured.</div>
        </div>
        <div class="zazu-metric-card">
            <div class="zazu-metric-label">Upcoming demand</div>
            <div class="zazu-metric-value">{{ $demand->count() }}</div>
            <div class="zazu-metric-copy">Rental-linked requirements on active jobs.</div>
        </div>
        <div class="zazu-metric-card">
            <div class="zazu-metric-label">Jobs needing rentals</div>
            <div class="zazu-metric-value">{{ $demandJobs }}</div>
            <div class="zazu-metric-copy">Distinct active jobs with rental demand.</div>
        </div>
        <a href="{{ route('suppliers.index') }}" class="zazu-metric-card zazu-metric-link">
            <div class="zazu-metric-label">Hire context</div>
            <div class="zazu-metric-value">→</div>
            <div class="zazu-metric-copy">Open supplier planning for externally sourced resources.</div>
        </a>
    </section>

    <section class="zazu-card zazu-list mt-5">
        <div class="zazu-card-header">
            <div class="zazu-eyebrow">Planning demand</div>
            <div class="zazu-card-title mt-1">Rental requirements</div>
            <div class="zazu-card-description">Demand is linked to the same Work and catalogue records used throughout the system.</div>
        </div>
        @if ($demand->count())
            <div class="zazu-record-header" style="--zazu-record-cols: 2">
                <div class="zazu-record-header-note">Resource</div>
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
                <div class="zazu-list-side"><div class="zazu-side-primary">{{ number_format((float) $requirement->quantity, 2) }} {{ $requirement->unit ?: 'units' }}</div></div>
                <div class="zazu-list-side"><div class="zazu-side-primary">{{ $requirement->event->event_date?->format('d M Y') ?? 'Date not set' }}</div></div>
            </div>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No rental demand yet</div>
                <p class="zazu-empty-copy">Rental resources will appear here when a rental service is added to an active job.</p>
                <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-primary mt-5">Open jobs</a>
            </div>
        @endforelse
    </section>

    <section class="zazu-detail-grid mt-5">
        <div class="zazu-panel">
            <div class="zazu-panel-title">Asset register boundary</div>
            <div class="zazu-panel-copy mt-1">The page is connected to actual demand without pretending the catalogue itself is a physical asset register.</div>
            <div class="zazu-placeholder">
                <div class="zazu-placeholder-title">Ownership and condition model still required</div>
                <div class="zazu-placeholder-copy">The future asset layer should track physical asset identity, condition, availability, allocation, return and damage/loss evidence without duplicating catalogue definitions.</div>
            </div>
        </div>
        <div class="zazu-detail-stack">
            <a href="{{ route('inventory.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Inventory</div><div class="zazu-route-card-copy">Keep consumable movement separate from reusable equipment.</div></div><span class="zazu-route-card-arrow">→</span></a>
            <a href="{{ route('calendar.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Calendar</div><div class="zazu-route-card-copy">Review job dates that influence resource planning.</div></div><span class="zazu-route-card-arrow">→</span></a>
        </div>
    </section>
</x-app-layout>