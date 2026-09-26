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
            <p class="zazu-command-copy">Choose what you are providing for this job. Zazu uses these services to build the quote.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Requirements</div>
            <div class="zazu-command-meta-value">{{ $requirements->count() }}</div>
        </div>
    </section>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header">
            <div class="zazu-card-title">Requirement register</div>
            <div class="zazu-card-description">Add or review the services before creating the quote.</div>
        </div>

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
                <div class="zazu-list-side">
                    <div class="zazu-side-primary">{{ number_format((float) $requirement->quantity, 2) }} {{ $requirement->unit ?: 'units' }}</div>
                    <div class="zazu-side-secondary">Work requirement</div>
                </div>
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
