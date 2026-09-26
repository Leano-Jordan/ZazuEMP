<x-app-layout>
    <x-slot:title>Quotes · {{ $event->name }}</x-slot:title>
    <x-slot:heading>Quotes</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.quotes.create', $event) }}" class="zazu-btn zazu-btn-primary">Create quote</a>
        <a href="{{ route('work.show', $event) }}" class="zazu-btn zazu-btn-ghost">← Workspace</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · {{ $event->customer?->name ?? 'Customer' }}</div>
            <h2 class="zazu-command-title">Commercial history</h2>
            <p class="zazu-command-copy">Quotes stay attached to this Work record so the commercial story remains contextual.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Quotes</div>
            <div class="zazu-command-meta-value">{{ $quotes->count() }}</div>
        </div>
    </section>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header">
            <div class="zazu-card-title">Quote records</div>
            <div class="zazu-card-description">Each quote may contain multiple revisions.</div>
        </div>

        @forelse ($quotes as $quote)
            <a href="{{ route('quotes.show', $quote) }}" class="zazu-list-item">
                <div class="zazu-list-main">
                    <div class="zazu-list-title">{{ $quote->reference }}</div>
                    <div class="zazu-list-meta">{{ $quote->currency }} · Created {{ $quote->created_at->format('d M Y, H:i') }}</div>
                </div>
                <div class="zazu-list-side">
                    <div class="zazu-side-primary">v{{ $quote->latestVersion?->version ?? '—' }}</div>
                    <div class="zazu-side-secondary">{{ number_format((float) ($quote->latestVersion?->total ?? 0), 2) }}</div>
                </div>
            </a>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No quotes for this work</div>
                <p class="zazu-empty-copy">Create the first quote from the current requirement register.</p>
                <a href="{{ route('work.quotes.create', $event) }}" class="zazu-btn zazu-btn-primary mt-5">Create quote</a>
            </div>
        @endforelse
    </section>
</x-app-layout>
