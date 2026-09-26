<x-app-layout>
    <x-slot:title>Costs · {{ $event->name }}</x-slot:title>
    <x-slot:heading>Costs</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.costs.create', $event) }}" class="zazu-btn zazu-btn-primary">Add cost</a>
        <a href="{{ route('work.show', $event) }}" class="zazu-btn zazu-btn-ghost">← Workspace</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · {{ $event->customer?->name ?? 'Customer' }}</div>
            <h2 class="zazu-command-title">Projected vs actual costs</h2>
            <p class="zazu-command-copy">Track expected cost against what was actually incurred for this work. These records support operational costing and are not payment or accounting records.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Variance</div>
            <div class="zazu-command-meta-value">{{ number_format($actualTotal - $projectedTotal, 2) }}</div>
        </div>
    </section>

    <div class="zazu-metric-grid">
        <section class="zazu-metric-card">
            <h2 class="zazu-metric-label">Projected</h2>
            <div class="zazu-metric-value">ZAR {{ number_format($projectedTotal, 2) }}</div>
            <div class="zazu-metric-note">Expected operating cost</div>
        </section>
        <section class="zazu-metric-card">
            <h2 class="zazu-metric-label">Actual</h2>
            <div class="zazu-metric-value">ZAR {{ number_format($actualTotal, 2) }}</div>
            <div class="zazu-metric-note">Recorded incurred cost</div>
        </section>
        <section class="zazu-metric-card">
            <h2 class="zazu-metric-label">Records</h2>
            <div class="zazu-metric-value">{{ $costs->count() }}</div>
            <div class="zazu-metric-note">Cost entries for this work</div>
        </section>
    </div>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header">
            <div class="zazu-card-title">Cost records</div>
            <div class="zazu-card-description">Use one record per meaningful operating cost.</div>
        </div>
        @forelse ($costs as $cost)
            <div class="zazu-list-item">
                <div class="zazu-list-main">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="zazu-list-title">{{ $cost->description }}</span>
                        <span class="zazu-chip zazu-chip-neutral">{{ ucfirst($cost->category) }}</span>
                        <span class="zazu-chip {{ $cost->status === 'incurred' ? 'zazu-chip-success' : ($cost->status === 'cancelled' ? 'zazu-chip-danger' : 'zazu-chip-info') }}">{{ ucfirst($cost->status) }}</span>
                    </div>
                    @if ($cost->notes)
                        <div class="zazu-list-meta">{{ $cost->notes }}</div>
                    @endif
                </div>
                <div class="zazu-list-side">
                    <div class="zazu-side-primary">{{ $cost->currency }} {{ number_format((float) $cost->projected_amount, 2) }}</div>
                    <div class="zazu-side-secondary">
                        Actual: {{ $cost->actual_amount !== null ? $cost->currency.' '.number_format((float) $cost->actual_amount, 2) : 'Not recorded' }}
                    </div>
                </div>
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