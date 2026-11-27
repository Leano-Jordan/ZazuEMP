<x-app-layout>
    <x-slot:title>Requirements · {{ $event->name }}</x-slot:title>
    <x-slot:heading>Requirements</x-slot:heading>
    <section class="zazu-command-band zazu-requirements-command">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · {{ $event->customer?->name ?? 'Customer' }}</div>
            <h2 class="zazu-command-title">Products and services for this job</h2>
            <p class="zazu-command-copy">Review and correct the products, services, quantities and details that feed the quote. Earlier quote versions stay unchanged.</p>
        </div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Records</div><div class="zazu-command-meta-value">{{ $requirements->count() }}</div></div>
    </section>

    @if ($scopeLocked)
        <div class="zazu-warning-note mb-4" role="status">This job has an accepted quote. Its agreed scope is locked; create a separate job for additional work.</div>
    @endif

    <section class="zazu-card zazu-list zazu-requirements-register">
        <div class="zazu-card-header">
            <div class="zazu-eyebrow">Operational records</div>
            <div class="zazu-section-heading"><div><div class="zazu-card-title mt-1">Requirement list</div></div>@unless ($scopeLocked)<a href="{{ route('work.requirements.create', $event) }}" class="zazu-btn zazu-btn-primary">Add service</a>@endunless</div>
            <div class="zazu-card-description">Each row is one requirement. The quantity shown at right belongs to that row.</div>
        </div>
        @if ($requirements->count())
            <div class="zazu-record-header" data-record-cols="2">
                <div class="zazu-record-header-note">Product / service</div>
                <div class="zazu-record-header-cell">Quantity</div>
                <div class="zazu-record-header-cell">Status</div>
            </div>
        @endif
        @forelse ($requirements as $requirement)
            <div class="zazu-list-item">
                <div class="zazu-list-main">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="zazu-list-title">{{ $requirement->description }}</span>
                        <span class="zazu-chip zazu-chip-info zazu-requirement-status">{{ ucfirst($requirement->status) }}</span>
                    </div>
                    <div class="zazu-list-meta">
                        {{ $requirement->category ?: 'General' }}
                        @if ($requirement->capability) · {{ $requirement->capability->name }} @endif
                        @if ($requirement->notes) · {{ $requirement->notes }} @endif
                    </div>
                </div>
                <div class="zazu-list-side"><div class="zazu-side-primary">{{ number_format((float) $requirement->quantity, 2) }} {{ $requirement->unit ?: 'units' }}</div></div>
                <div class="zazu-list-side">
                    <div class="zazu-side-primary">{{ ucfirst($requirement->status) }}</div>
                    @unless ($scopeLocked)
                    <div class="mt-2 flex flex-wrap items-center justify-end gap-2">
                    <a href="{{ route('work.requirements.edit', [$event, $requirement]) }}" class="zazu-btn zazu-btn-secondary zazu-btn-sm">Edit</a>
                    <form method="POST" action="{{ route('work.requirements.destroy', [$event, $requirement]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="zazu-btn zazu-btn-ghost zazu-btn-sm">Remove</button>
                    </form>
                    </div>
                    @endunless
                </div>
            </div>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No requirements yet</div>
                <p class="zazu-empty-copy">Start translating the customer brief into concrete quantities, services, rentals or other deliverables.</p>
                @unless ($scopeLocked)
                    <a href="{{ route('work.requirements.create', $event) }}" class="zazu-btn zazu-btn-primary mt-5">Choose first service</a>
                @endunless
            </div>
        @endforelse
    </section>
</x-app-layout>