<x-app-layout>
    <x-slot:title>{{ $quote->reference }}</x-slot:title>
    <x-slot:heading>{{ $quote->reference }}</x-slot:heading>
    <x-slot:headerAction>
        @if ($version?->status === 'draft')
            <a href="{{ route('quotes.versions.edit', [$quote, $version]) }}" class="zazu-btn zazu-btn-primary">Edit draft</a>
            <form method="POST" action="{{ route('quotes.status', $quote) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="sent">
                <button class="zazu-btn zazu-btn-secondary">Mark as sent</button>
            </form>
        @elseif ($quote->status === 'sent')
            <form method="POST" action="{{ route('quotes.status', $quote) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="accepted">
                <button class="zazu-btn zazu-btn-primary">Mark accepted</button>
            </form>
            <form method="POST" action="{{ route('quotes.status', $quote) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="declined">
                <button class="zazu-btn zazu-btn-ghost">Decline</button>
            </form>
        @else
            <form method="POST" action="{{ route('quotes.versions.store', $quote) }}">
                @csrf
                <button class="zazu-btn zazu-btn-primary">Create new revision</button>
            </form>
        @endif
        @if ($customerUrl)
            <a href="{{ $customerUrl }}" target="_blank" rel="noopener" class="zazu-btn zazu-btn-secondary">Customer view</a>
        @endif
        <a href="{{ route('work.show', $quote->event) }}" class="zazu-btn zazu-btn-secondary">Job workspace</a>
        <a href="{{ route('work.quotes.index', $quote->event) }}" class="zazu-btn zazu-btn-ghost">All quotes</a>
    </x-slot:headerAction>

    @php
        $displayStatus = $quote->status ?: ($version?->status ?? 'draft');
        $statusClass = match ($displayStatus) {
            'accepted' => 'zazu-chip-success',
            'declined', 'expired' => 'zazu-chip-danger',
            'superseded' => 'zazu-chip-neutral',
            default => 'zazu-chip-info',