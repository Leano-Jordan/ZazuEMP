<x-app-layout>
    <x-slot:title>Costs · {{ $event->name }}</x-slot:title>
    <x-slot:heading>Costs</x-slot:heading>
    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · {{ $event->customer?->name ?? 'Customer' }}</div>
            <h2 class="zazu-command-title">Projected vs actual costs</h2>
            <p class="zazu-command-copy">Track expected operating cost against what was actually incurred. These are operational records, not payment or accounting records.</p>
        </div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Currencies</div><div class="zazu-command-meta-value">{{ $totalsByCurrency->count() }}</div></div>
    </section>

    <div class="zazu-metric-grid">
        @foreach ($totalsByCurrency as $currency => $totals)
            <section class="zazu-metric-card"><h2 class="zazu-metric-label">{{ $currency }} projected</h2><div class="zazu-metric-value">{{ $currency }} {{ $totals['projected'] }}</div><div class="zazu-metric-note">Actual: {{ $currency }} {{ $totals['actual'] }}</div></section>
        @endforeach
        <section class="zazu-metric-card"><h2 class="zazu-metric-label">Records</h2><div class="zazu-metric-value">{{ $costs->count() }}</div><div class="zazu-metric-note">Cost entries for this work</div></section>
    </div>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header">
            <div class="zazu-eyebrow">Operational records</div>
            <div class="zazu-section-heading"><div><div class="zazu-card-title mt-1">Cost list</div></div><a href="{{ route('work.costs.create', $event) }}" class="zazu-btn zazu-btn-primary">Add cost</a></div>
            <div class="zazu-card-description">Projected and actual amounts sit under clearly labelled columns.</div>
        </div>
        @if ($costs->count())
            <div class="zazu-record-header" data-record-cols="2">
                <div class="zazu-record-header-note">Cost</div>
                <div class="zazu-record-header-cell">Projected</div>
                <div class="zazu-record-header-cell">Actual</div>
            </div>
        @endif
        @forelse ($costs as $cost)
            <div class="zazu-list-item">
                <div class="zazu-list-main">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="zazu-list-title">{{ $cost->description }}</span>
                        <span class="zazu-chip zazu-chip-neutral">{{ ucfirst($cost->category) }}</span>
                        <span class="zazu-chip {{ $cost->status === 'incurred' ? 'zazu-chip-success' : ($cost->status === 'cancelled' ? 'zazu-chip-danger' : 'zazu-chip-info') }}">{{ ucfirst($cost->status) }}</span>
                    </div>
                    @if ($cost->notes)<div class="zazu-list-meta">{{ $cost->notes }}</div>@endif
                </div>
                <div class="zazu-list-side"><div class="zazu-side-primary">{{ $cost->currency }} {{ $cost->projected_amount }}</div></div>
                <div class="zazu-list-side"><div class="zazu-side-primary">{{ $cost->actual_amount !== null ? $cost->currency.' '.$cost->actual_amount : 'Not recorded' }}</div></div>
            </div>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No cost records yet</div>
                <p class="zazu-empty-copy">Capture expected costs first, then record the actual amount when the cost is incurred.</p>
                <a href="{{ route('work.costs.create', $event) }}" class="zazu-btn zazu-btn-primary mt-5">Add first cost</a>
            </div>
        @endforelse
    </section>
</x-app-layout>