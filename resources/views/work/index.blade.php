<x-app-layout>
    <x-slot:title>Work</x-slot:title>
    <x-slot:heading>Work</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary">Create work</a></x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Operations</div>
            <h2 class="zazu-command-title">Workspaces</h2>
            <p class="zazu-command-copy">Each event or job has one operational record. Use the quick views below instead of typing filter values.</p>
        </div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Records</div><div class="zazu-command-meta-value">{{ $events->total() }}</div></div>
    </section>

    <div class="zazu-workload-grid">
        <a href="{{ route('work.index') }}" class="zazu-filter-card {{ $filter === '' ? 'active' : '' }}">
            <div><div class="zazu-filter-label">All work</div><div class="zazu-filter-value">{{ $events->total() }}</div><div class="zazu-filter-copy">Every active and closed record.</div></div>
            <span class="zazu-module-arrow">→</span>
        </a>
        <a href="{{ route('work.index', ['filter' => 'today']) }}" class="zazu-filter-card {{ $filter === 'today' ? 'active' : '' }}">
            <div><div class="zazu-filter-label">Today</div><div class="zazu-filter-value">{{ $workload['today'] }}</div><div class="zazu-filter-copy">Work scheduled today.</div></div>
            <span class="zazu-module-arrow">→</span>
        </a>
        <a href="{{ route('work.index', ['filter' => 'next_7_days']) }}" class="zazu-filter-card {{ $filter === 'next_7_days' ? 'active' : '' }}">
            <div><div class="zazu-filter-label">Next 7 days</div><div class="zazu-filter-value">{{ $workload['next_7_days'] }}</div><div class="zazu-filter-copy">Upcoming operational load.</div></div>
            <span class="zazu-module-arrow">→</span>
        </a>
        <a href="{{ route('work.index', ['filter' => 'in_progress']) }}" class="zazu-filter-card {{ $filter === 'in_progress' ? 'active' : '' }}">
            <div><div class="zazu-filter-label">In progress</div><div class="zazu-filter-value">{{ $workload['in_progress'] }}</div><div class="zazu-filter-copy">Work currently being executed.</div></div>
            <span class="zazu-module-arrow">→</span>
        </a>
        <a href="{{ route('work.index', ['filter' => 'draft']) }}" class="zazu-filter-card {{ $filter === 'draft' ? 'active' : '' }}">
            <div><div class="zazu-filter-label">Drafts</div><div class="zazu-filter-value">{{ $workload['draft'] }}</div><div class="zazu-filter-copy">Work not yet confirmed.</div></div>
            <span class="zazu-module-arrow">→</span>
        </a>
        <a href="{{ route('work.index', ['filter' => 'overdue']) }}" class="zazu-filter-card {{ $filter === 'overdue' ? 'active' : '' }}">
            <div><div class="zazu-filter-label">Overdue</div><div class="zazu-filter-value">{{ $workload['overdue'] }}</div><div class="zazu-filter-copy">Work with unfinished preparation past its due date.</div></div>
            <span class="zazu-module-arrow">→</span>
        </a>
    </div>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header">
            <div class="zazu-eyebrow">Records</div>
            <div class="zazu-card-title mt-1">Work list</div>
            <div class="zazu-card-description">The row contains one job. The right-hand area is scheduling and actions, not another record.</div>
        </div>

        @if ($events->count())
            <div class="zazu-record-header" style="--zazu-record-cols: 2">
                <div class="zazu-record-header-note">Work item</div>
                <div class="zazu-record-header-cell">Schedule</div>
                <div class="zazu-record-header-cell">Actions</div>
            </div>
        @endif

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
                <div class="zazu-list-side">
                    <div class="zazu-side-primary">{{ $event->event_date?->format('d M Y') ?? 'Date not set' }}</div>
                    <div class="zazu-side-secondary">{{ $event->event_type ?: 'Work' }}</div>
                </div>
                <div class="zazu-action-group">
                    <a href="{{ route('work.edit', $event) }}" class="zazu-btn zazu-btn-ghost">Edit</a>
                    <form method="POST" action="{{ route('work.destroy', $event) }}" onsubmit="return confirm('Remove this work from active operations? Historical records will be retained.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="zazu-btn zazu-btn-ghost text-[var(--zazu-danger-ink)]">Remove</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No work matches this view</div>
                <p class="zazu-empty-copy">Change the quick view or create a new job.</p>
                <div class="mt-5 flex justify-center gap-2">
                    @if ($filter)<a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-secondary">Show all work</a>@endif
                    <a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary">Create work</a>
                </div>
            </div>
        @endforelse

        @if ($events->hasPages())<div class="border-t border-[var(--zazu-border)] px-5 py-4">{{ $events->links() }}</div>@endif
    </section>
</x-app-layout>