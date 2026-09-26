<x-app-layout>
    <x-slot:title>Work</x-slot:title>
    <x-slot:heading>Work</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary">Create work</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Operations</div>
            <h2 class="zazu-command-title">Workspaces</h2>
            <p class="zazu-command-copy">Every event or job gets its own operational record. Open one to move from customer context into the work itself.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Total work</div>
            <div class="zazu-command-meta-value">{{ $events->total() }}</div>
        </div>
    </section>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header flex items-center justify-between gap-4">
            <div>
                <div class="zazu-card-title">Recent work</div>
                <div class="zazu-card-description">Your latest operational records, ordered by event date.</div>
            </div>
            <a href="{{ route('customers.index') }}" class="zazu-btn zazu-btn-ghost">Customers →</a>
        </div>

        @forelse ($events as $event)
            @php
                $statusClass = match ($event->status) {
                    'confirmed' => 'zazu-chip-success',
                    'in_progress' => 'zazu-chip-info',
                    'completed' => 'zazu-chip-accent',
                    'cancelled' => 'zazu-chip-danger',
                    default => 'zazu-chip-neutral',
                };
            @endphp

            <a href="{{ route('work.show', $event) }}" class="zazu-list-item">
                <div class="zazu-list-main">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="zazu-list-title">{{ $event->name }}</span>
                        <span class="zazu-chip {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($event->status)) }}</span>
                    </div>
                    <div class="zazu-list-meta">
                        {{ $event->customer?->name ?? $event->customer_name ?? 'No customer' }}
                        · {{ $event->reference }}
                    </div>
                </div>

                <div class="zazu-list-side">
                    <div class="zazu-side-primary">{{ $event->event_date?->format('d M Y') ?? 'Date not set' }}</div>
                    <div class="zazu-side-secondary">{{ $event->event_type ?: 'Work' }}</div>
                </div>
            </a>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No work yet</div>
                <p class="zazu-empty-copy">Start with a customer and turn that relationship into an event or job workspace.</p>
                <div class="mt-5 flex justify-center gap-2">
                    <a href="{{ route('customers.create') }}" class="zazu-btn zazu-btn-secondary">Add customer</a>
                    <a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary">Create work</a>
                </div>
            </div>
        @endforelse

        @if ($events->hasPages())
            <div class="border-t border-[var(--zazu-border)] px-5 py-4">{{ $events->links() }}</div>
        @endif
    </section>
</x-app-layout>
