<x-app-layout>
    <x-slot:title>Calendar</x-slot:title>
    <x-slot:heading>Calendar</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary">Create job</a></x-slot:headerAction>

    <section class="zazu-command-band zazu-calendar-command">
        <div><div class="zazu-eyebrow">Operations / Job calendar</div><h2 class="zazu-command-title">{{ $monthLabel }}</h2><p class="zazu-command-copy">Plan active jobs by event date. Select a job to open its operational record.</p></div>
        <div class="zazu-calendar-toolbar-main" aria-label="Calendar controls">
            <a href="{{ route('calendar.index', ['month' => $previousMonth]) }}" class="zazu-btn zazu-btn-ghost" aria-label="Previous month">←</a>
            <a href="{{ route('calendar.index') }}" class="zazu-btn zazu-btn-secondary">Today</a>
            <a href="{{ route('calendar.index', ['month' => $nextMonth]) }}" class="zazu-btn zazu-btn-ghost" aria-label="Next month">→</a>
        </div>
    </section>

    <section class="zazu-calendar-shell" aria-label="Monthly job calendar">
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
                <div class="zazu-calendar-cell {{ $isCurrentMonth ? 'is-current-month' : 'opacity-45' }} {{ $day->isToday() ? 'is-today' : '' }}">
                    <div class="zazu-calendar-date-row"><span class="zazu-calendar-date">{{ $day->format('j') }}</span>@if($day->isToday())<span class="zazu-calendar-today">Today</span>@endif</div>
                    <div class="zazu-calendar-events">
                        @foreach ($dayEvents->take(3) as $event)
                            <a href="{{ route('work.show', $event) }}" class="zazu-calendar-event" title="{{ $event->name }}">{{ $event->name }}</a>
                        @endforeach
                        @if($dayEvents->count() > 3)<span class="zazu-calendar-more">+ {{ $dayEvents->count() - 3 }} more</span>@endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="zazu-calendar-agenda">
            @foreach($days as $day)
                @php $key = $day->format('Y-m-d'); $dayEvents = $eventsByDate->get($key, collect()); $isCurrentMonth = $day->month === $month->month; @endphp
                @if($isCurrentMonth)
                    <div class="zazu-calendar-agenda-day">
                        <div class="zazu-calendar-agenda-date"><span>{{ $day->format('l, j F') }}</span>@if($day->isToday())<span class="zazu-calendar-today">Today</span>@endif</div>
                        @if($dayEvents->isNotEmpty())
                            <div class="zazu-calendar-agenda-events">
                                @foreach($dayEvents as $event)<a href="{{ route('work.show', $event) }}" class="zazu-calendar-agenda-event"><span>{{ $event->name }}</span><span>Open →</span></a>@endforeach
                            </div>
                        @else
                            <div class="zazu-calendar-empty">No scheduled jobs.</div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </section>
</x-app-layout>
