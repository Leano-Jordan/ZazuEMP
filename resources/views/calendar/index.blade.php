<x-app-layout>
    <x-slot:title>Calendar</x-slot:title>
    <x-slot:heading>Calendar</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary">Create work</a></x-slot:headerAction>

    <section class="zazu-command-band">
        <div><div class="zazu-eyebrow">Operations / Calendar</div><h2 class="zazu-command-title">{{ $monthLabel }}</h2><p class="zazu-command-copy">Work is shown by event date. Removed work is not included in active planning.</p></div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('calendar.index', ['month' => $previousMonth]) }}" class="zazu-btn zazu-btn-ghost">Previous month</a>
            <a href="{{ route('calendar.index') }}" class="zazu-btn zazu-btn-secondary">Current</a>
            <a href="{{ route('calendar.index', ['month' => $nextMonth]) }}" class="zazu-btn zazu-btn-ghost">Next month</a>
        </div>
    </section>

    <section class="zazu-calendar-shell">
        <div class="zazu-calendar-grid">
            @foreach ([['short' => 'Mon', 'full' => 'Monday'], ['short' => 'Tue', 'full' => 'Tuesday'], ['short' => 'Wed', 'full' => 'Wednesday'], ['short' => 'Thu', 'full' => 'Thursday'], ['short' => 'Fri', 'full' => 'Friday'], ['short' => 'Sat', 'full' => 'Saturday'], ['short' => 'Sun', 'full' => 'Sunday']] as $label)
                <div class="zazu-calendar-label" aria-label="{{ $label['full'] }}" title="{{ $label['full'] }}">{{ $label['short'] }}</div>
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
                </div>
            @endforeach
        </div>
    </section>
</x-app-layout>
