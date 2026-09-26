<x-app-layout>
    <x-slot:title>Requirements · {{ $event->name }}</x-slot:title>
    <x-slot:heading>Requirements</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.requirements.create', $event) }}" class="zazu-btn zazu-btn-primary">Add service</a>
        <a href="{{ route('work.show', $event) }}" class="zazu-btn zazu-btn-ghost">Job workspace</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · {{ $event->customer?->name ?? 'Customer' }}</div>
            <h2 class="zazu-command-title">Services for this job</h2>
            <p class="zazu-command-copy">Build the concrete services, rentals and deliverables that the quote will use.</p>
        </div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Records</div><div class="zazu-command-meta-value">{{ $requirements->count() }}</div></div>
    </section>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header">
            <div class="zazu-eyebrow">Operational records</div>
            <div class="zazu-card-title mt-1">Requirement list</div>
            <div class="zazu-card-description">Each row is one requirement. The quantity shown at right belongs to that row.</div>
        </div>
        @if ($requirements->count())
            <div class="zazu-record-header" style="--zazu-record-cols: 2">
                <div class="zazu-record-header-note">Service</div>
                <div class="zazu-record-header-cell">Quantity</div>
                <div class="zazu-record-header-cell">Status</div>
            </div>
        @endif
        @forelse ($requirements as $requirement)
            <div class="zazu-list-item">
                <div class="zazu-list-main">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="zazu-list-title">{{ $requirement->description }}</span>
                        <span class="zazu-chip zazu-chip-info">{{ ucfirst($requirement->status) }}</span>
                    </div>
                    <div class="zazu-list-meta">
                        {{ $requirement->category ?: 'General' }}
                        @if ($requirement->capability) · {{ $requirement->capability->name }} @endif
                        @if ($requirement->notes) · {{ $requirement->notes }} @endif
                    </div>
                </div>
                <div class="zazu-list-side"><div class="zazu-side-primary">{{ number_format((float) $requirement->quantity, 2) }} {{ $requirement->unit ?: 'units' }}</div></div>
                <div class="zazu-list-side"><div class="zazu-side-primary">{{ ucfirst($requirement->status) }}</div></div>
            </div>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No requirements yet</div>
                <p class="zazu-empty-copy">Start translating the customer brief into concrete quantities, services, rentals or other deliverables.</p>
                <a href="{{ route('work.requirements.create', $event) }}" class="zazu-btn zazu-btn-primary mt-5">Choose first service</a>
            </div>
        @endforelse
    </section>
</x-app-layout>