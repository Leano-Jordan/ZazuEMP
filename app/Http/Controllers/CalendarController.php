<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\CurrentBusiness;
use App\Support\SouthAfricaHolidayCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __invoke(Request $request, SouthAfricaHolidayCalendar $holidays): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $membership = $request->user()->businesses()->whereKey($businessId)->firstOrFail();
        $holidayPreferences = $membership->pivot->calendar_holiday_preferences ?? [];
        if (is_string($holidayPreferences)) {
            $holidayPreferences = json_decode($holidayPreferences, true) ?: [];
        }
        $month = $this->resolveMonth($request->string('month')->toString());
        $view = $this->resolveView($request->string('view')->toString());
        [$rangeStart, $rangeEnd] = $this->viewRange($month, $view);
        $holidayMap = collect(range($rangeStart->year, $rangeEnd->year))
            ->flatMap(fn (int $year) => $holidays->visibleForYear($year, $holidayPreferences))
            ->groupBy(fn (array $holiday) => $holiday['date']->format('Y-m-d'));

        $events = Event::query()
            ->where('business_id', $businessId)
            ->with('customer')
            ->whereBetween('event_date', [$rangeStart, $rangeEnd])
            ->orderBy('event_date')
            ->orderBy('name')
            ->get();

        $eventsByDate = $events->groupBy(fn ($event) => $event->event_date->format('Y-m-d'));
        $monthEvents = $events->filter(fn ($event) => $event->event_date->isSameMonth($month))->values();
        $statusCounts = $monthEvents->groupBy('status')->map->count();
        $attentionCount = $statusCounts->get('draft', 0);
        $isCurrentMonth = $month->isSameMonth(now());
        $agendaStart = $isCurrentMonth ? now()->startOfDay() : $month->copy()->startOfMonth();
        $upcomingCount = $monthEvents->filter(fn ($event) => $event->event_date->gte($agendaStart))->count();

        return view('calendar.index', [
            'month' => $month,
            'monthLabel' => $month->format('F Y'),
            'previousMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'jumpMonth' => $month->format('Y-m'),
            'view' => $view,
            'days' => $this->monthDays($month),
            'eventsByDate' => $eventsByDate,
            'monthEvents' => $monthEvents,
            'monthEventCount' => $monthEvents->count(),
            'upcomingCount' => $upcomingCount,
            'confirmedCount' => $statusCounts->get('confirmed', 0),
            'attentionCount' => $attentionCount,
            'isCurrentMonth' => $isCurrentMonth,
            'threeMonthBlocks' => $this->threeMonthBlocks($month, $eventsByDate, $holidayMap),
            'yearMonths' => $this->yearMonths($month, $eventsByDate, $holidayMap),
            'holidays' => $holidayMap,
            'holidayCategories' => SouthAfricaHolidayCalendar::CATEGORIES,
            'holidayPreferences' => $holidayPreferences,
        ]);
    }

    private function resolveView(?string $value): string
    {
        return in_array($value, ['month', '3-months', 'year', 'agenda'], true) ? $value : 'month';
    }

    private function viewRange(Carbon $month, string $view): array
    {
        return match ($view) {
            '3-months' => [$month->copy()->startOfMonth(), $month->copy()->addMonths(2)->endOfMonth()],
            'year' => [$month->copy()->startOfYear(), $month->copy()->endOfYear()],
            default => [$month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY), $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY)],
        };
    }

    private function monthDays(Carbon $month)
    {
        $start = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $days = collect();

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $days->push($day->copy());
        }

        return $days;
    }

    private function threeMonthBlocks(Carbon $month, $eventsByDate, $holidays)
    {
        return collect(range(0, 2))->map(function ($offset) use ($month, $eventsByDate, $holidays) {
            $blockMonth = $month->copy()->addMonths($offset);
            return [
                'month' => $blockMonth,
                'days' => $this->monthDays($blockMonth),
                'eventsByDate' => $eventsByDate->filter(fn ($events, $date) => Carbon::parse($date)->isSameMonth($blockMonth)),
                'holidays' => $holidays->filter(fn ($dayHolidays, $date) => Carbon::parse($date)->isSameMonth($blockMonth)),
            ];
        });
    }

    private function yearMonths(Carbon $month, $eventsByDate, $holidays)
    {
        return collect(range(0, 11))->map(function ($offset) use ($month, $eventsByDate, $holidays) {
            $yearMonth = $month->copy()->startOfYear()->addMonths($offset);
            return [
                'month' => $yearMonth,
                'days' => $this->monthDays($yearMonth),
                'eventsByDate' => $eventsByDate->filter(fn ($events, $date) => Carbon::parse($date)->isSameMonth($yearMonth)),
                'holidays' => $holidays->filter(fn ($dayHolidays, $date) => Carbon::parse($date)->isSameMonth($yearMonth)),
            ];
        });
    }

    private function resolveMonth(?string $value): Carbon
    {
        if (!$value) return now()->startOfMonth();
        try {
            return Carbon::createFromFormat('Y-m', $value)->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }
}
