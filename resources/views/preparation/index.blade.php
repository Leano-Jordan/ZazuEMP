<x-app-layout>
    <x-slot:title>Preparation · {{ $event->name }}</x-slot:title>
    <x-slot:heading>Preparation</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.preparation.create', $event) }}" class="zazu-btn zazu-btn-primary">Add preparation item</a>
        <a href="{{ route('work.show', $event) }}" class="zazu-btn zazu-btn-ghost">Job workspace</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · Readiness</div>
            <h2 class="zazu-command-title">Preparation and readiness</h2>
            <p class="zazu-command-copy">Turn the work brief into a practical readiness list. Mark items open, blocked or ready as preparation progresses.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Ready</div>
            <div class="zazu-command-meta-value">{{ $items->where('status', 'ready')->count() }}/{{ $items->count() }}</div>
        </div>
    </section>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header">
            <div class="zazu-card-title">Readiness items</div>
            <div class="zazu-card-description">Keep practical preparation visible before the event or job date.</div>
        </div>
        @forelse ($items as $item)
            <div class="zazu-list-item">
                <div class="zazu-list-main">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="zazu-list-title">{{ $item->title }}</span>
                        <span class="zazu-chip {{ $item->status === 'ready' ? 'zazu-chip-success' : ($item->status === 'blocked' ? 'zazu-chip-danger' : 'zazu-chip-info') }}">{{ ucfirst($item->status) }}</span>
                    </div>
                    <div class="zazu-list-meta">
                        @if ($item->category){{ $item->category }} · @endif
                        @if ($item->quantity !== null){{ number_format((float) $item->quantity, 2) }} {{ $item->unit ?: 'units' }} · @endif
                        @if ($item->due_date)Due {{ $item->due_date->format('d M Y') }} @endif
                    </div>
                    @if ($item->notes)<div class="zazu-list-meta">{{ $item->notes }}</div>@endif
                </div>
                <div class="zazu-list-side">
                    <form method="POST" action="{{ route('work.preparation.status', [$event, $item]) }}" class="flex flex-wrap gap-2 justify-end">
                        @csrf @method('PATCH')
                        @foreach (['open' => 'Open', 'blocked' => 'Blocked', 'ready' => 'Ready'] as $status => $label)
                            <button name="status" value="{{ $status }}" class="zazu-btn {{ $item->status === $status ? 'zazu-btn-secondary' : 'zazu-btn-ghost' }}">{{ $label }}</button>
                        @endforeach
                    </form>
                </div>
            </div>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No preparation items yet</div>
                <p class="zazu-empty-copy">Add the things that must be ready before the work date, such as ingredients, equipment, packing, staff checks or venue arrangements.</p>
                <a href="{{ route('work.preparation.create', $event) }}" class="zazu-btn zazu-btn-primary mt-5">Add first item</a>
            </div>
        @endforelse
    </section>
</x-app-layout>