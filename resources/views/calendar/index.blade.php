<x-app-layout>
    <x-slot:title>Calendar</x-slot:title>
    <x-slot:heading>Calendar</x-slot:heading>

    <section class="zazu-calendar-hero" aria-labelledby="calendar-title">
        <div class="zazu-calendar-hero-copy">
            <div class="zazu-eyebrow">Operations / Schedule</div>
            <div class="zazu-calendar-title-row">
                <div>
                    <h2 id="calendar-title" class="zazu-calendar-title">{{ $monthLabel }}</h2>
                    <p class="zazu-calendar-subtitle">Your work at a glance. Spot busy days and anything that needs attention.</p>
                </div>
                <div class="zazu-calendar-month-nav" aria-label="Calendar controls">
                    <a href="{{ route('calendar.index', ['month' => $previousMonth]) }}" class="zazu-calendar-nav-btn" aria-label="Previous month">←</a>
                    <a href="{{ route('calendar.index') }}" class="zazu-calendar-today-btn">Today</a>
                    <a href="{{ route('calendar.index', ['month' => $nextMonth]) }}" class="zazu-calendar-nav-btn" aria-label="Next month">→</a>
                </div>
            </div>
        </div>

        <div class="zazu-calendar-summary" aria-label="Monthly calendar summary">
            <div class="zazu-calendar-summary-item">
                <span class="zazu-calendar-summary-value">{{ $monthEventCount }}</span>
                <span class="zazu-calendar-summary-label">Jobs this month</span>
            </div>
            <div class="zazu-calendar-summary-item">
                <span class="zazu-calendar-summary-value">{{ $confirmedCount }}</span>
                <span class="zazu-calendar-summary-label">Confirmed</span>
            </div>
            <div class="zazu-calendar-summary-item {{ $attentionCount > 0 ? 'is-attention' : '' }}">
                <span class="zazu-calendar-summary-value">{{ $attentionCount }}</span>
                <span class="zazu-calendar-summary-label">Needs attention</span>
            </div>
        </div>
    </section>

    <section class="zazu-calendar-shell zazu-calendar-command-shell zazu-calendar-page" aria-label="Monthly job calendar">
        <div class="zazu-calendar-legend" aria-label="Event status legend">
            <span><i class="zazu-calendar-dot is-draft"></i>Planning</span>
            <span><i class="zazu-calendar-dot is-confirmed"></i>Confirmed</span>
            <span><i class="zazu-calendar-dot is-progress"></i>In progress</span>
            <span><i class="zazu-calendar-dot is-complete"></i>Complete</span>
        </div>

        <div class="zazu-calendar-grid">
            @foreach ([['short' => 'Mon', 'full' => 'Monday'], ['short' => 'Tue', 'full' => 'Tuesday'], ['short' => 'Wed', 'full' => 'Wednesday'], ['short' => 'Thu', 'full' => 'Thursday'], ['short' => 'Fri', 'full' => 'Friday'], ['short' => 'Sat', 'full' => 'Saturday'], ['short' => 'Sun', 'full' => 'Sunday']] as $label)
                <div class="zazu-calendar-label" aria-label="{{ $label['full'] }}">{{ $label['short'] }}</div>
            @endforeach

            @foreach ($days as $day)
                @php
                    $key = $day->format('Y-m-d');
                    $isCurrentMonth = $day->month === $month->month;
                    $dayEvents = $eventsByDate->get($key, collect());
                @endphp
                <div class="zazu-calendar-cell {{ $isCurrentMonth ? 'is-current-month' : 'is-adjacent' }} {{ $day->isToday() ? 'is-today' : '' }}">
                    <div class="zazu-calendar-date-row">
                        <span class="zazu-calendar-date">{{ $day->format('j') }}</span>
                        @if($day->isToday())<span class="zazu-calendar-today">Today</span>@endif
                    </div>

                    @if($dayEvents->isNotEmpty())
                        <div class="zazu-calendar-events">
                            @foreach ($dayEvents->take(3) as $event)
                                @php
                                    $status = $event->status ?: 'draft';
                                    $statusLabel = str_replace('_', ' ', ucfirst($status));
                                @endphp
                                <a href="{{ route('work.show', $event) }}" class="zazu-calendar-event status-{{ $status }}" title="{{ $event->name }}">
                                    <span class="zazu-calendar-event-accent"></span>
                                    <span class="zazu-calendar-event-content">
                                        <strong>{{ $event->name }}</strong>
                                        @if($event->event_type || $event->customer_name)
                                            <small>{{ $event->event_type ?: $event->customer_name }}</small>
                                        @endif
                                    </span>
                                    <span class="zazu-calendar-status" aria-label="{{ $statusLabel }}">{{ $statusLabel }}</span>
                                </a>
                            @endforeach
                            @if($dayEvents->count() > 3)
                                <span class="zazu-calendar-more">+ {{ $dayEvents->count() - 3 }} more jobs</span>
                            @endif
                        </div>
                    @else
                        <span class="zazu-calendar-empty-cell">—</span>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="zazu-calendar-agenda" aria-label="Upcoming jobs">
            <div class="zazu-calendar-agenda-header">
                <div>
                    <span class="zazu-eyebrow">Upcoming work</span>
                    <h3>What is happening next</h3>
                </div>
                <span class="zazu-calendar-agenda-count">{{ $monthEventCount }} {{ $monthEventCount === 1 ? 'job' : 'jobs' }}</span>
            </div>

            @php $agendaHasEvents = false; @endphp
            @foreach($days as $day)
                @php $key = $day->format('Y-m-d'); $dayEvents = $eventsByDate->get($key, collect()); $isCurrentMonth = $day->month === $month->month; @endphp
                @if($isCurrentMonth && $dayEvents->isNotEmpty())
                    @php $agendaHasEvents = true; @endphp
                    <div class="zazu-calendar-agenda-day {{ $day->isToday() ? 'is-today' : '' }}">
                        <div class="zazu-calendar-agenda-date">
                            <strong>{{ $day->format('D') }}</strong>
                            <span>{{ $day->format('j M') }}</span>
                            @if($day->isToday())<em>Today</em>@endif
                        </div>
                        <div class="zazu-calendar-agenda-events">
                            @foreach($dayEvents as $event)
                                @php $status = $event->status ?: 'draft'; @endphp
                                <a href="{{ route('work.show', $event) }}" class="zazu-calendar-agenda-event status-{{ $status }}">
                                    <span class="zazu-calendar-event-accent"></span>
                                    <span class="zazu-calendar-agenda-event-main">
                                        <strong>{{ $event->name }}</strong>
                                        <small>
                                            {{ $event->event_type ?: 'Event' }}
                                            @if($event->customer_name) · {{ $event->customer_name }}@endif
                                            @if($event->event_address) · {{ $event->event_address }}@endif
                                        </small>
                                    </span>
                                    <span class="zazu-calendar-agenda-status">{{ str_replace('_', ' ', ucfirst($status)) }}</span>
                                    <span class="zazu-calendar-open" aria-hidden="true">→</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            @if(!$agendaHasEvents)
                <div class="zazu-calendar-empty-state">
                    <span class="zazu-calendar-empty-icon">◷</span>
                    <strong>No jobs scheduled this month</strong>
                    <p>Your calendar is clear. New work will appear here automatically.</p>
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
