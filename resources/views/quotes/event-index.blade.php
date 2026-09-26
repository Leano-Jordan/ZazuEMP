<x-app-layout>
    <x-slot:title>Quotes · {{ $event->name }}</x-slot:title>
    <x-slot:heading>Quotes</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.quotes.create', $event) }}" class="zazu-btn zazu-btn-primary">Create quote</a>
        <a href="{{ route('work.show', $event) }}" class="zazu-btn zazu-btn-ghost">Job workspace</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · {{ $event->customer?->name ?? 'Customer' }}</div>
            <h2 class="zazu-command-title">Quote history</h2>
            <p class="zazu-command-copy">Every quote stays attached to this Work record so revisions remain contextual.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Records</div>
            <div class="zazu-command-meta-value">{{ $quotes->count() }}</div>
        </div>
    </section>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header">
            <div class="zazu-eyebrow">Commercial records</div>
            <div class="zazu-card-title mt-1">Quote list</div>
            <div class="zazu-card-description">Each row is one quote. Revisions are viewed inside the quote.</div>
        </div>
        @if ($quotes->count())
            <div class="zazu-record-header" style="--zazu-record-cols: 2">
                <div class="zazu-record-header-note">Quote</div>
                <div class="zazu-record-header-cell">Created</div>
                <div class="zazu-record-header-cell">Current total</div>
            </div>
        @endif
        @forelse ($quotes as $quote)
            <a href="{{ route('quotes.show', $quote) }}" class="zazu-list-item">
                <div class="zazu-list-main">
                    <div class="zazu-list-title">{{ $quote->reference }}</div>
                    <div class="zazu-list-meta">{{ $quote->currency }} · {{ ucfirst($quote->status) }}</div>
                </div>
                <div class="zazu-list-side">
                    <div class="zazu-side-primary">{{ $quote->created_at->format('d M Y, H:i') }}</div>
                </div>
                <div class="zazu-list-side">
                    <div class="zazu-side-primary">{{ $quote->currency }} {{ number_format((float) ($quote->latestVersion?->total ?? 0), 2) }}</div>
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