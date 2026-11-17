<x-app-layout><x-slot:title>Assets</x-slot:title><x-slot:heading>Assets</x-slot:heading>
<section class="zazu-command-band"><div><div class="zazu-eyebrow">Resources</div><h2 class="zazu-command-title">Physical asset register</h2><p class="zazu-command-copy">Track individual reusable equipment separately from consumable stock.</p></div><div class="zazu-command-meta"><div class="zazu-command-meta-label">Assets</div><div class="zazu-command-meta-value">{{ $assets->count() }}</div></div></section>
<section class="zazu-card zazu-list mt-5 zazu-assets-directory"><div class="zazu-card-header"><div class="zazu-section-heading"><div class="zazu-card-title">Asset register</div><a href="{{ route('assets.create') }}" class="zazu-btn zazu-btn-primary">Add asset</a></div></div>
@forelse($assets as $asset)
<div class="zazu-list-item">
    <div class="zazu-list-main">
        <div class="zazu-list-title">{{ $asset->name }} <span class="zazu-chip zazu-chip-info">{{ $asset->asset_tag }}</span></div>
        <div class="zazu-list-meta">{{ ucfirst($asset->condition) }} · {{ $asset->location ?: 'Location not set' }}</div>
    </div>
    <div class="zazu-list-side"><div class="zazu-side-primary">{{ ucfirst($asset->status) }}</div></div>
    <div class="zazu-action-group">
        <a href="{{ route('assets.edit', $asset) }}" class="zazu-btn zazu-btn-ghost">Edit</a>
        @if(in_array($asset->status, ['available', 'allocated'], true))
            <form method="POST" action="{{ route('assets.allocate', $asset) }}" class="flex flex-wrap gap-2" data-zazu-offline-entity="asset_allocation" data-zazu-offline-asset-id="{{ $asset->id }}">
                @csrf
                <select name="event_id" required><option value="">Allocate to job</option>@foreach($events as $event)<option value="{{ $event->id }}" data-allocated-from="{{ $event->setup_date?->toDateString() ?? $event->event_date?->toDateString() }}" data-allocated-until="{{ $event->collection_date?->toDateString() ?? $event->event_end_date?->toDateString() ?? $event->event_date?->toDateString() }}">{{ $event->name }}</option>@endforeach</select>
                <input type="date" name="allocated_from" value="{{ now()->toDateString() }}" required>
                <input type="date" name="allocated_until" aria-label="Allocation end date">
                <button class="zazu-btn zazu-btn-secondary">Book dates</button>
            </form>
        @endif
    </div>
    @if($asset->allocations->isNotEmpty())
        <div class="w-full basis-full border-t pt-3">
            <div class="zazu-eyebrow">Active bookings</div>
            @foreach($asset->allocations as $allocation)
                <div class="flex flex-wrap items-center justify-between gap-2 py-2">
                    <div class="zazu-list-meta">
                        {{ $allocation->event?->name ?? 'Job' }} ·
                        {{ $allocation->allocated_from?->format('d M Y') ?? $allocation->getRawOriginal('allocated_from') }}
                        @if($allocation->allocated_until)
                            – {{ $allocation->allocated_until->format('d M Y') }}
                        @else
                            · Return date not set
                        @endif
                    </div>
                    <form method="POST" action="{{ route('assets.release', $asset) }}" data-zazu-offline-entity="asset_allocation" data-zazu-offline-asset-id="{{ $asset->id }}" data-zazu-offline-allocation-id="{{ $allocation->id }}">
                        @csrf
                        <input type="hidden" name="allocation_id" value="{{ $allocation->id }}">
                        <button class="zazu-btn zazu-btn-secondary">Mark returned</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@empty
<div class="zazu-empty"><div class="zazu-empty-title">No assets yet</div><p class="zazu-empty-copy">Register physical equipment to track availability and return history.</p></div>
@endforelse
</section>
<section class="zazu-card zazu-list mt-5"><div class="zazu-card-header"><div class="zazu-eyebrow">Planning</div><div class="zazu-card-title mt-1">Rental demand from active jobs</div></div>
@forelse($demand as $requirement)<div class="zazu-list-item"><div class="zazu-list-main"><div class="zazu-list-title">{{ $requirement->capability?->name ?? $requirement->description }}</div><div class="zazu-list-meta">{{ $requirement->event->name }}</div></div><div class="zazu-list-side"><div class="zazu-side-primary">{{ number_format((float)$requirement->quantity,2) }} {{ $requirement->unit ?: 'units' }}</div></div></div>@empty<div class="zazu-empty">No active rental demand.</div>@endforelse
</section><script nonce="{{ request()->attributes->get('cspNonce') }}">
    document.querySelectorAll('form[data-zazu-offline-entity="asset_allocation"] select[name="event_id"]').forEach((select) => {
        const form = select.closest('form');
        const from = form.querySelector('[name="allocated_from"]');
        const until = form.querySelector('[name="allocated_until"]');
        select.addEventListener('change', () => {
            const option = select.selectedOptions[0];
            if (option?.dataset.allocatedFrom) from.value = option.dataset.allocatedFrom;
            if (option?.dataset.allocatedUntil) until.value = option.dataset.allocatedUntil;
        });
    });
</script>

</x-app-layout>
