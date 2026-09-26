<x-app-layout>
    <x-slot:title>Travel & Costing · {{ $event->name }}</x-slot:title>
    <x-slot:heading>Travel & Costing</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.travel.create', $event) }}" class="zazu-btn zazu-btn-primary">Add calculation</a>
        <a href="{{ route('work.show', $event) }}" class="zazu-btn zazu-btn-ghost">← Workspace</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · {{ $event->customer?->name ?? 'Customer' }}</div>
            <h2 class="zazu-command-title">Travel and route details</h2>
            <p class="zazu-command-copy">Keep the route, fuel details and customer charge together. Each saved calculation is kept as a record.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Calculations</div>
            <div class="zazu-command-meta-value">{{ $travelCosts->count() }}</div>
        </div>
    </section>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header">
            <div class="zazu-card-title">Saved travel calculations</div>
            <div class="zazu-card-description">Routes are entered manually for now.</div>
        </div>

        @forelse ($travelCosts as $travel)
            <div class="zazu-list-item">
                <div class="zazu-list-main">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="zazu-list-title">{{ $travel->route_label }}</span>
                        <span class="zazu-chip zazu-chip-neutral">{{ ucfirst($travel->provider) }}</span>
                        <span class="zazu-chip zazu-chip-info">{{ $travel->round_trip ? 'Round trip' : 'One way' }}</span>
                    </div>
                    <div class="zazu-list-meta">
                        {{ $travel->origin }} → {{ $travel->destination }}
                        · {{ number_format((float) $travel->total_distance_km, 2) }} km total
                        @if ($travel->travel_time_minutes) · {{ $travel->travel_time_minutes }} min one way @endif
                    </div>
                </div>
                <div class="zazu-list-side">
                    <div class="zazu-side-primary">{{ $travel->currency }} {{ number_format((float) $travel->customer_charge, 2) }}</div>
                    <div class="zazu-side-secondary">Fuel {{ number_format((float) $travel->fuel_cost, 2) }}</div>
                </div>
            </div>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No travel calculations yet</div>
                <p class="zazu-empty-copy">Capture the route, distance, vehicle assumptions and customer rate used for this work.</p>
                <a href="{{ route('work.travel.create', $event) }}" class="zazu-btn zazu-btn-primary mt-5">Add first calculation</a>
            </div>
        @endforelse
    </section>

    <div class="zazu-skeleton-actions">
        <a href="{{ route('work.quotes.index', $event) }}" class="zazu-btn zazu-btn-secondary">Quotes</a>
        <a href="{{ route('work.requirements.index', $event) }}" class="zazu-btn zazu-btn-ghost">Requirements →</a>
    </div>
</x-app-layout>
