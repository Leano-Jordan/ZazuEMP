<x-app-layout>
    <x-slot:title>Calendar</x-slot:title>
    <x-slot:heading>Calendar</x-slot:heading>

    <section class="zazu-calendar-hero" aria-labelledby="calendar-title">
        <div class="zazu-calendar-hero-copy">
            <div class="zazu-eyebrow">Operations / Schedule</div>
            <div class="zazu-calendar-title-row">
                <div>
                    <h2 id="calendar-title" class="zazu-calendar-title">{{ $monthLabel }}</h2>
                    <p class="zazu-calendar-subtitle">
                        @if($view === 'year')
                            See the shape of your work across {{ $month->format('Y') }}.
                        @elseif($view === '3-months')
                            See the next three months of planned work at a glance.
                        @elseif($view === 'agenda')
                            Follow scheduled work in date order without the calendar grid.
                        @else
                            {{ $isCurrentMonth ? 'See what is coming up, what is confirmed, and what still needs action.' : 'Review the jobs planned for this month.' }}
                        @endif
                    </p>
                </div>
                <div class="zazu-calendar-month-nav" aria-label="Calendar controls">
                    <a href="{{ route('calendar.index', ['month' => $previousMonth, 'view' => $view]) }}" class="zazu-calendar-nav-btn" aria-label="Previous period">←</a>
                    <a href="{{ route('calendar.index', ['view' => $view]) }}" class="zazu-calendar-today-btn">Today</a>
                    <a href="{{ route('calendar.index', ['month' => $nextMonth, 'view' => $view]) }}" class="zazu-calendar-nav-btn" aria-label="Next period">→</a>
                </div>
            </div>
        </div>
        <div class="zazu-calendar-summary" aria-label="Calendar summary">
            <div class="zazu-calendar-summary-item"><span class="zazu-calendar-summary-value">{{ $monthEventCount }}</span><span class="zazu-calendar-summary-label">Jobs this month</span></div>
            <div class="zazu-calendar-summary-item"><span class="zazu-calendar-summary-value">{{ $upcomingCount }}</span><span class="zazu-calendar-summary-label">{{ $isCurrentMonth ? 'Still to come' : 'Scheduled' }}</span></div>
            <div class="zazu-calendar-summary-item"><span class="zazu-calendar-summary-value">{{ $confirmedCount }}</span><span class="zazu-calendar-summary-label">Confirmed</span></div>
            <div class="zazu-calendar-summary-item {{ $attentionCount > 0 ? 'is-attention' : '' }}"><span class="zazu-calendar-summary-value">{{ $attentionCount }}</span><span class="zazu-calendar-summary-label">Needs action</span></div>
        </div>
    </section>

    <section class="zazu-calendar-shell zazu-calendar-command-shell zazu-calendar-page" aria-label="Calendar">
        <div class="zazu-calendar-toolbar">
            <nav class="zazu-calendar-view-switch" aria-label="Calendar views">
                @foreach(['month' => 'Month', '3-months' => '3 Months', 'year' => 'Year', 'agenda' => 'Agenda'] as $viewKey => $label)
                    <a href="{{ route('calendar.index', ['month' => $jumpMonth, 'view' => $viewKey]) }}" class="{{ $view === $viewKey ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </nav>
            <form method="GET" action="{{ route('calendar.index') }}" class="zazu-calendar-jump">
                <input type="hidden" name="view" value="{{ $view }}">
                <label for="calendar-month">Jump to</label>
                <input id="calendar-month" type="month" name="month" value="{{ $jumpMonth }}" aria-label="Jump to month">
                <button type="submit">Go</button>
            </form>
        </div>

        @if($view === 'month')
            <div class="zazu-calendar-legend" aria-label="Event status legend">
                <span><i class="zazu-calendar-dot is-draft"></i>Planning</span><span><i class="zazu-calendar-dot is-confirmed"></i>Confirmed</span><span><i class="zazu-calendar-dot is-progress"></i>In progress</span><span><i class="zazu-calendar-dot is-complete"></i>Complete</span><span><i class="zazu-calendar-dot is-cancelled"></i>Cancelled</span>
            </div>
            <div class="zazu-calendar-grid">
                @foreach ([['short' => 'Mon', 'full' => 'Monday'], ['short' => 'Tue', 'full' => 'Tuesday'], ['short' => 'Wed', 'full' => 'Wednesday'], ['short' => 'Thu', 'full' => 'Thursday'], ['short' => 'Fri', 'full' => 'Friday'], ['short' => 'Sat', 'full' => 'Saturday'], ['short' => 'Sun', 'full' => 'Sunday']] as $label)
                    <div class="zazu-calendar-label" aria-label="{{ $label['full'] }}">{{ $label['short'] }}</div>
                @endforeach
                @foreach ($days as $day)
                    @php $key=$day->format('Y-m-d'); $isCurrentMonthDay=$day->month===$month->month && $day->year===$month->year; $dayEvents=$eventsByDate->get($key,collect()); @endphp
                    <div class="zazu-calendar-cell {{ $isCurrentMonthDay ? 'is-current-month' : 'is-adjacent' }} {{ $day->isToday() ? 'is-today' : '' }}">
                        <div class="zazu-calendar-date-row"><span class="zazu-calendar-date">{{ $day->format('j') }}</span>@if($day->isToday())<span class="zazu-calendar-today">Today</span>@endif</div>
                        @if($dayEvents->isNotEmpty())
                            <div class="zazu-calendar-events">
                                @foreach($dayEvents->take(3) as $event)
                                    @php $status=$event->status?:'draft'; $statusLabel=str_replace('_',' ',ucfirst($status)); @endphp
                                    <a href="{{ route('work.show',$event) }}" class="zazu-calendar-event status-{{ $status }}" title="{{ $event->name }}"><span class="zazu-calendar-event-accent"></span><span class="zazu-calendar-event-content"><strong>{{ $event->name }}</strong>@if($event->event_type || $event->customer_name)<small>{{ $event->event_type ?: $event->customer_name }}</small>@endif</span><span class="zazu-calendar-status">{{ $statusLabel }}</span></a>
                                @endforeach
                                @if($dayEvents->count()>3)<span class="zazu-calendar-more">+ {{ $dayEvents->count()-3 }} more jobs</span>@endif
                            </div>
                        @else <span class="zazu-calendar-empty-cell">—</span> @endif
                    </div>
                @endforeach
            </div>
        @elseif($view === '3-months')
            <div class="zazu-calendar-multi-grid">
                @foreach($threeMonthBlocks as $block)
                    <section class="zazu-calendar-mini-month">
                        <header><a href="{{ route('calendar.index',['month'=>$block['month']->format('Y-m'),'view'=>'month']) }}">{{ $block['month']->format('F Y') }}</a></header>
                        <div class="zazu-calendar-mini-weekdays">@foreach(['M','T','W','T','F','S','S'] as $weekday)<span>{{ $weekday }}</span>@endforeach</div>
                        <div class="zazu-calendar-mini-grid">
                            @foreach($block['days'] as $day)
                                @php $key=$day->format('Y-m-d'); $inMonth=$day->month===$block['month']->month && $day->year===$block['month']->year; $count=$block['eventsByDate']->get($key,collect())->count(); @endphp
                                <a href="{{ route('calendar.index',['month'=>$block['month']->format('Y-m'),'view'=>'month']) }}" class="{{ $inMonth ? '' : 'is-adjacent' }} {{ $day->isToday() ? 'is-today' : '' }}" title="{{ $count }} {{ $count===1?'job':'jobs' }}">{{ $day->format('j') }}@if($count)<i></i>@endif</a>
                            @endforeach
                        </div>
                        <footer>{{ $block['eventsByDate']->flatten(1)->count() }} {{ $block['eventsByDate']->flatten(1)->count()===1?'job':'jobs' }}</footer>
                    </section>
                @endforeach
            </div>
        @elseif($view === 'year')
            <div class="zazu-calendar-year-grid">
                @foreach($yearMonths as $yearMonth)
                    @php $count=$yearMonth['eventsByDate']->flatten(1)->count(); @endphp
                    <a href="{{ route('calendar.index',['month'=>$yearMonth['month']->format('Y-m'),'view'=>'month']) }}" class="zazu-calendar-year-month {{ $yearMonth['month']->isSameMonth(now())?'is-current':'' }}">
                        <header><strong>{{ $yearMonth['month']->format('F') }}</strong><span>{{ $count }} {{ $count===1?'job':'jobs' }}</span></header>
                        <div class="zazu-calendar-mini-weekdays">@foreach(['M','T','W','T','F','S','S'] as $weekday)<span>{{ $weekday }}</span>@endforeach</div>
                        <div class="zazu-calendar-mini-grid">
                            @foreach($yearMonth['days'] as $day)
                                @php $inMonth=$day->month===$yearMonth['month']->month && $day->year===$yearMonth['month']->year; $dayCount=$yearMonth['eventsByDate']->get($day->format('Y-m-d'),collect())->count(); @endphp
                                <span class="{{ $inMonth?'':'is-adjacent' }} {{ $day->isToday()?'is-today':'' }}">{{ $day->format('j') }}@if($dayCount)<i></i>@endif</span>
                            @endforeach
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="zazu-calendar-agenda zazu-calendar-agenda-standalone">
                <div class="zazu-calendar-agenda-header"><div><span class="zazu-eyebrow">Scheduled work</span><h3>Jobs in {{ $monthLabel }}</h3></div><span class="zazu-calendar-agenda-count">{{ $monthEventCount }} {{ $monthEventCount===1?'job':'jobs' }}</span></div>
                @php $agendaHasEvents=false; @endphp
                @foreach($days as $day)
                    @php $key=$day->format('Y-m-d'); $dayEvents=$eventsByDate->get($key,collect()); $show=$day->month===$month->month && $day->year===$month->year && $dayEvents->isNotEmpty() && (!$isCurrentMonth || $day->gte(now()->startOfDay())); @endphp
                    @if($show)
                        @php $agendaHasEvents=true; @endphp
                        <div class="zazu-calendar-agenda-day {{ $day->isToday()?'is-today':'' }}"><div class="zazu-calendar-agenda-date"><strong>{{ $day->format('D') }}</strong><span>{{ $day->format('j M') }}</span>@if($day->isToday())<em>Today</em>@endif</div><div class="zazu-calendar-agenda-events">
                            @foreach($dayEvents as $event)
                                @php $status=$event->status?:'draft'; @endphp
                                <a href="{{ route('work.show',$event) }}" class="zazu-calendar-agenda-event status-{{ $status }}"><span class="zazu-calendar-event-accent"></span><span class="zazu-calendar-agenda-event-main"><strong>{{ $event->name }}</strong><small>{{ $event->event_type ?: 'Event' }}@if($event->customer_name) · {{ $event->customer_name }}@endif @if($event->event_address) · {{ $event->event_address }}@endif</small></span><span class="zazu-calendar-agenda-status">{{ str_replace('_',' ',ucfirst($status)) }}</span><span class="zazu-calendar-open" aria-hidden="true">→</span></a>
                            @endforeach
                        </div></div>
                    @endif
                @endforeach
                @if(!$agendaHasEvents)<div class="zazu-calendar-empty-state"><span class="zazu-calendar-empty-icon">◷</span><strong>{{ $isCurrentMonth?'Nothing else scheduled this month':'No jobs scheduled this month' }}</strong><p>{{ $isCurrentMonth?'Your remaining work will appear here as soon as it is scheduled.':'New work will appear here automatically.' }}</p></div>@endif
            </div>
        @endif

        @if($view === 'month')
            <div class="zazu-calendar-agenda">
                <div class="zazu-calendar-agenda-header"><div><span class="zazu-eyebrow">{{ $isCurrentMonth?'Upcoming work':'Scheduled work' }}</span><h3>{{ $isCurrentMonth?'What is happening next':'Jobs in this month' }}</h3></div><span class="zazu-calendar-agenda-count">{{ $upcomingCount }} {{ $upcomingCount===1?'job':'jobs' }}</span></div>
                @php $agendaHasEvents=false; @endphp
                @foreach($days as $day)
                    @php $key=$day->format('Y-m-d'); $dayEvents=$eventsByDate->get($key,collect()); $show=$day->month===$month->month && $day->year===$month->year && $dayEvents->isNotEmpty() && (!$isCurrentMonth || $day->gte(now()->startOfDay())); @endphp
                    @if($show)
                        @php $agendaHasEvents=true; @endphp
                        <div class="zazu-calendar-agenda-day {{ $day->isToday()?'is-today':'' }}"><div class="zazu-calendar-agenda-date"><strong>{{ $day->format('D') }}</strong><span>{{ $day->format('j M') }}</span>@if($day->isToday())<em>Today</em>@endif</div><div class="zazu-calendar-agenda-events">
                            @foreach($dayEvents as $event)
                                @php $status=$event->status?:'draft'; @endphp
                                <a href="{{ route('work.show',$event) }}" class="zazu-calendar-agenda-event status-{{ $status }}"><span class="zazu-calendar-event-accent"></span><span class="zazu-calendar-agenda-event-main"><strong>{{ $event->name }}</strong><small>{{ $event->event_type ?: 'Event' }}@if($event->customer_name) · {{ $event->customer_name }}@endif @if($event->event_address) · {{ $event->event_address }}@endif</small></span><span class="zazu-calendar-agenda-status">{{ str_replace('_',' ',ucfirst($status)) }}</span><span class="zazu-calendar-open" aria-hidden="true">→</span></a>
                            @endforeach
                        </div></div>
                    @endif
                @endforeach
                @if(!$agendaHasEvents)<div class="zazu-calendar-empty-state"><span class="zazu-calendar-empty-icon">◷</span><strong>{{ $isCurrentMonth?'Nothing else scheduled this month':'No jobs scheduled this month' }}</strong><p>{{ $isCurrentMonth?'Your remaining work will appear here as soon as it is scheduled.':'New work will appear here automatically.' }}</p></div>@endif
            </div>
        @endif
    </section>
</x-app-layout>
