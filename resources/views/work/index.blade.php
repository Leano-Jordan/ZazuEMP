<x-app-layout>
    <x-slot:title>Work</x-slot:title>
    <x-slot:heading>Work</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary">Create work</a></x-slot:headerAction>

    <section class="zazu-command-band">
        <div><div class="zazu-eyebrow">Operations</div><h2 class="zazu-command-title">Workspaces</h2><p class="zazu-command-copy">Every event or job gets its own operational record.</p></div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Active work</div><div class="zazu-command-meta-value">{{ $events->total() }}</div></div>
    </section>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header flex items-center justify-between gap-4">
            <div><div class="zazu-card-title">Active work</div><div class="zazu-card-description">Open, edit or remove an active work record.</div></div>
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
            <div class="zazu-list-item">
                <a href="{{ route('work.show', $event) }}" class="zazu-list-main min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2"><span class="zazu-list-title">{{ $event->name }}</span><span class="zazu-chip {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($event->status)) }}</span></div>
                    <div class="zazu-list-meta">{{ $event->customer?->name ?? $event->customer_name ?? 'No customer' }} · {{ $event->reference }}</div>
                </a>
                <div class="flex flex-wrap items-center justify-end gap-2">
                    <div class="zazu-list-side"><div class="zazu-side-primary">{{ $event->event_date?->format('d M Y') ?? 'Date not set' }}</div><div class="zazu-side-secondary">{{ $event->event_type ?: 'Work' }}</div></div>
                    <a href="{{ route('work.edit', $event) }}" class="zazu-btn zazu-btn-ghost">Edit</a>
                    <form method="POST" action="{{ route('work.destroy', $event) }}" onsubmit="return confirm('Remove this work from active operations? Historical records will be retained.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="zazu-btn zazu-btn-ghost text-[var(--zazu-danger-ink)]">Remove</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="zazu-empty"><div class="zazu-empty-title">No active work</div><p class="zazu-empty-copy">Start with a customer and create a work workspace.</p><div class="mt-5 flex justify-center gap-2"><a href="{{ route('customers.create') }}" class="zazu-btn zazu-btn-secondary">Add customer</a><a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary">Create work</a></div></div>
        @endforelse

        @if ($events->hasPages())<div class="border-t border-[var(--zazu-border)] px-5 py-4">{{ $events->links() }}</div>@endif
    </section>
</x-app-layout>
