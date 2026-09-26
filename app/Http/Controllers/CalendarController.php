<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\CurrentBusiness;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __invoke(Request $request): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $month = $this->resolveMonth($request->string('month')->toString());
        $gridStart = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $events = Event::query()
            ->where('business_id', $businessId)
            ->with('customer')
            ->whereBetween('event_date', [$gridStart, $gridEnd])
            ->orderBy('event_date')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($event) => $event->event_date->format('Y-m-d'));

        $days = collect();
        for ($day = $gridStart->copy(); $day->lte($gridEnd); $day->addDay()) {
            $days->push($day->copy());
        }

        return view('calendar.index', [
            'month' => $month,
            'monthLabel' => $month->format('F Y'),
            'previousMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'days' => $days,
            'eventsByDate' => $events,
        ]);
    }

    private function resolveMonth(?string $value): Carbon
    {
        if (!$value) {
            return now()->startOfMonth();
        }

        try {
            return Carbon::createFromFormat('Y-m', $value)->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }
}
