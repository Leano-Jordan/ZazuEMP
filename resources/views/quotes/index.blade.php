<x-app-layout>
    <x-slot:title>Quotes</x-slot:title>
    <x-slot:heading>Quotes</x-slot:heading>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Commercial</div>
            <h2 class="zazu-command-title">Quote register</h2>
            <p class="zazu-command-copy">Each row is one quote record. Open it to see the revision history and saved commercial snapshot.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Records</div>
            <div class="zazu-command-meta-value">{{ $quotes->total() }}</div>
        </div>
    </section>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header">
            <div class="zazu-eyebrow">Records</div>
            <div class="zazu-card-title mt-1">Quote list</div>
            <div class="zazu-card-description">Headers above describe columns. They are not additional quote records.</div>
        </div>

        @if ($quotes->count())
            <div class="zazu-record-header" style="--zazu-record-cols: 3">
                <div class="zazu-record-header-note">Quote</div>
                <div class="zazu-record-header-cell">Version</div>
                <div class="zazu-record-header-cell">Status</div>
                <div class="zazu-record-header-cell">Total</div>
            </div>
        @endif

        @forelse ($quotes as $quote)
            <a href="{{ route('quotes.show', $quote) }}" class="zazu-list-item grid grid-cols-[minmax(0,1fr)_auto_auto_auto]">
                <div class="zazu-list-main">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="zazu-list-title">{{ $quote->reference }}</span>
                        <span class="zazu-chip zazu-chip-neutral">{{ $quote->event->name }}</span>
                    </div>
                    <div class="zazu-list-meta">{{ $quote->event->customer?->name ?? 'Customer' }} · {{ $quote->currency }}</div>
                </div>
                <div class="zazu-side-primary">v{{ $quote->latestVersion?->version ?? '—' }}</div>
                <div><span class="zazu-chip {{ $quote->latestVersion?->status === 'draft' ? 'zazu-chip-neutral' : 'zazu-chip-success' }}">{{ ucfirst($quote->latestVersion?->status ?? $quote->status) }}</span></div>
                <div class="zazu-side-primary">{{ $quote->latestVersion ? number_format((float) $quote->latestVersion->total, 2) : '—' }}</div>
            </a>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No quotes yet</div>
                <p class="zazu-empty-copy">Open a Work record, capture its requirements and create the first commercial draft.</p>
                <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-primary mt-5">Open work</a>
            </div>
        @endforelse

        @if ($quotes->hasPages())
            <div class="border-t border-[var(--zazu-border)] px-5 py-4">{{ $quotes->links() }}</div>
        @endif
    </section>
</x-app-layout>