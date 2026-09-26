<x-app-layout>
    <x-slot:title>Calendar</x-slot:title>
    <x-slot:heading>Calendar</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary">Create work</a></x-slot:headerAction>

    <section class="zazu-command-band">
        <div><div class="zazu-eyebrow">Operations / Calendar</div><h2 class="zazu-command-title">{{ $monthLabel }}</h2><p class="zazu-command-copy">Live Work records grouped by event date. Removed work stays out of active planning.</p></div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('calendar.index', ['month' => $previousMonth]) }}" class="zazu-btn zazu-btn-ghost">← Previous</a>
            <a href="{{ route('calendar.index') }}" class="zazu-btn zazu-btn-secondary">Current</a>
            <a href="{{ route('calendar.index', ['month' => $nextMonth]) }}" class="zazu-btn zazu-btn-ghost">Next →</a>
        </div>
    </section>

    <section class="zazu-calendar-shell">
        <div class="zazu-calendar-grid">
            @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $label)
                <div class="zazu-calendar-label">{{ $label }}</div>
            @endforeach
            @foreach ($days as $day)
                @php
                    $key = $day->format('Y-m-d');
                    $isCurrentMonth = $day->month === $month->month;
                    $dayEvents = $eventsByDate->get($key, collect());
                @endphp
                <div class="zazu-calendar-cell {{ $isCurrentMonth ? '' : 'opacity-45' }}">
                    <div class="zazu-calendar-date">{{ $day->format('j') }}</div>
                    @foreach ($dayEvents as $event)
                        <a href="{{ route('work.show', $event) }}" class="zazu-calendar-event block no-underline">{{ $event->name }}</a>
                    @endforeach
                    @if ($dayEvents->isEmpty() && $isCurrentMonth)
                        <span class="text-[9px] text-[var(--zazu-faint)]">No work</span>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
</x-app-layout>
