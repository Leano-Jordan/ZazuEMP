<x-app-layout>
    <x-slot:title>Quotes</x-slot:title>
    <x-slot:heading>Quotes</x-slot:heading>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Commercial</div>
            <h2 class="zazu-command-title">Quotes</h2>
            <p class="zazu-command-copy">Quotes are linked to Work and keep their revision history.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Quotes</div>
            <div class="zazu-command-meta-value">{{ $quotes->total() }}</div>
        </div>
    </section>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header grid grid-cols-[minmax(0,1fr)_auto_auto_auto] gap-4">
            <div>
                <div class="zazu-card-title">Recent quotes</div>
                <div class="zazu-card-description">Open a quote to view its latest revision and history.</div>
            </div>
            <span class="zazu-eyebrow self-center">Version</span>
            <span class="zazu-eyebrow self-center">Status</span>
            <span class="zazu-eyebrow self-center">Total</span>
        </div>

        @forelse ($quotes as $quote)
            <a href="{{ route('quotes.show', $quote) }}" class="zazu-list-item grid grid-cols-[minmax(0,1fr)_auto_auto_auto]">
                <div class="zazu-list-main">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="zazu-list-title">{{ $quote->reference }}</span>
                        <span class="zazu-chip zazu-chip-neutral">{{ $quote->event->name }}</span>
                    </div>
                    <div class="zazu-list-meta">
                        {{ $quote->event->customer?->name ?? 'Customer' }} · {{ $quote->currency }}
                    </div>
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
