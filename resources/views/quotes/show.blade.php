<x-app-layout>
    <x-slot:title>{{ $quote->reference }}</x-slot:title>
    <x-slot:heading>{{ $quote->reference }}</x-slot:heading>
    <x-slot:headerAction>
        <form method="POST" action="{{ route('quotes.versions.store', $quote) }}">
            @csrf
            <button class="zazu-btn zazu-btn-primary">Create new revision</button>
        </form>
        <a href="{{ route('work.show', $quote->event) }}" class="zazu-btn zazu-btn-secondary">Job workspace</a>
        <a href="{{ route('work.quotes.index', $quote->event) }}" class="zazu-btn zazu-btn-ghost">All quotes</a>
    </x-slot:headerAction>

    @php
        $statusClass = match ($version?->status) {
            'superseded' => 'zazu-chip-neutral',
            'accepted' => 'zazu-chip-success',
            'rejected' => 'zazu-chip-danger',
            default => 'zazu-chip-info',
        };
    @endphp

    <section class="zazu-work-hero">
        <div>
            <div class="zazu-work-ref">{{ $quote->reference }}</div>
            <div class="zazu-work-name">Quote v{{ $version?->version ?? '—' }}</div>
            <div class="zazu-work-summary">{{ $quote->event->name }} · {{ $quote->event->customer?->name ?? 'Customer' }} · {{ $quote->currency }}</div>
        </div>
        <div class="zazu-work-actions">
            <span class="zazu-chip {{ $statusClass }}">{{ ucfirst($version?->status ?? $quote->status) }}</span>
        </div>
    </section>

    <div class="zazu-detail-grid">
        <div class="zazu-detail-stack">
            <section class="zazu-card zazu-list">
                <div class="zazu-card-header">
                    <div class="zazu-eyebrow">Quote lines</div>
                    <div class="zazu-card-title mt-1">Services and prices</div>
                    <div class="zazu-card-description">Each row is one saved commercial line.</div>
                </div>
                @if (($version?->items?->count() ?? 0) > 0)
                    <div class="zazu-record-header" style="--zazu-record-cols: 2">
                        <div class="zazu-record-header-note">Service</div>
                        <div class="zazu-record-header-cell">Unit price</div>
                        <div class="zazu-record-header-cell">Line total</div>
                    </div>
                @endif
                @forelse ($version?->items ?? [] as $item)
                    <div class="zazu-list-item">
                        <div class="zazu-list-main">
                            <div class="zazu-list-title">{{ $item->description }}</div>
                            <div class="zazu-list-meta">{{ number_format((float) $item->quantity, 2) }} {{ $item->unit ?: 'units' }} · {{ $item->pricing_basis ?: 'Custom pricing' }}</div>
                        </div>
                        <div class="zazu-list-side"><div class="zazu-side-primary">{{ $quote->currency }} {{ number_format((float) $item->unit_price, 2) }}</div></div>
                        <div class="zazu-list-side"><div class="zazu-side-primary">{{ $quote->currency }} {{ number_format((float) $item->line_total, 2) }}</div></div>
                    </div>
                @empty
                    <div class="zazu-empty">No quote lines.</div>
                @endforelse
            </section>

            <section class="zazu-panel">
                <div class="zazu-eyebrow">Integrity</div>
                <div class="zazu-panel-title mt-1">Historical snapshot</div>
                <div class="zazu-panel-copy">This revision keeps the original requirement details used when it was created.</div>
                <div class="zazu-placeholder">
                    <div class="zazu-placeholder-title">Quote details stay fixed</div>
                    <div class="zazu-placeholder-copy">Changing the current Work requirements does not rewrite the saved quote line descriptions and quantities.</div>
                </div>
            </section>
        </div>

        <aside class="zazu-detail-stack">
            <section class="zazu-panel">
                <div class="zazu-eyebrow">Commercial summary</div>
                <div class="zazu-panel-title mt-1">Totals</div>
                <div class="zazu-detail-rows">
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Subtotal</div><div class="zazu-detail-value">{{ $quote->currency }} {{ number_format((float) ($version?->subtotal ?? 0), 2) }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Tax</div><div class="zazu-detail-value">{{ $quote->currency }} {{ number_format((float) ($version?->tax_total ?? 0), 2) }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Total</div><div class="zazu-detail-value">{{ $quote->currency }} {{ number_format((float) ($version?->total ?? 0), 2) }}</div></div>
                </div>
            </section>

            <section class="zazu-panel">
                <div class="zazu-eyebrow">Revision history</div>
                <div class="zazu-panel-title mt-1">Versions</div>
                <div class="zazu-panel-copy">Each revision remains a separate commercial snapshot.</div>
                <div class="zazu-stage-list">
                    @foreach ($quote->versions->sortByDesc('version') as $quoteVersion)
                        <div class="zazu-stage {{ $quoteVersion->id === $version?->id ? 'current' : '' }}">
                            <span class="zazu-stage-marker"></span>
                            <div>
                                <div class="zazu-stage-title">v{{ $quoteVersion->version }} · {{ ucfirst($quoteVersion->status) }}</div>
                                <div class="zazu-stage-copy">{{ $quote->currency }} {{ number_format((float) $quoteVersion->total, 2) }} · {{ $quoteVersion->created_at->format('d M Y, H:i') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            @if ($version?->notes)
                <section class="zazu-panel">
                    <div class="zazu-eyebrow">Notes</div>
                    <div class="zazu-panel-title mt-1">Quote notes</div>
                    <div class="mt-3 text-xs leading-6 text-[var(--zazu-ink-2)]">{{ $version->notes }}</div>
                </section>
            @endif
        </aside>
    </div>
</x-app-layout>