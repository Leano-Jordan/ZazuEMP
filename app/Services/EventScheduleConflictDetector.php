<?php

namespace App\Services;

use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final class EventScheduleConflictDetector
{
    /**
     * Return other active jobs whose event or logistics window overlaps.
     *
     * @param array<string, mixed> $schedule
     * @return Collection<int, Event>
     */
    public function conflicts(int $businessId, array $schedule, ?int $exceptEventId = null): Collection
    {
        $window = $this->scheduleWindow($schedule);
        if ($window === null) {
            return collect();
        }

        return Event::query()
            ->where('business_id', $businessId)
            ->whereNotIn('status', Event::TERMINAL_STATUSES)
            ->when($exceptEventId, fn ($query) => $query->where('id', '!=', $exceptEventId))
            ->get()
            ->filter(fn (Event $event): bool => $this->eventOverlapsWindow($event, $window))
            ->values();
    }

    /**
     * @param array<string, mixed> $schedule
     * @return array{start: CarbonImmutable, end: CarbonImmutable}|null
     */
    private function scheduleWindow(array $schedule): ?array
    {
        $eventDate = $schedule['event_date'] ?? null;
        if (! $eventDate) {
            return null;
        }

        $setupDate = $schedule['setup_date'] ?? null;
        $collectionDate = $schedule['collection_date'] ?? null;
        $eventEndDate = $schedule['event_end_date'] ?? null;
        $startTime = $schedule['event_start_time'] ?? null;
        $endTime = $schedule['event_end_time'] ?? null;
        $hasTimeRange = $startTime && $endTime;

        $start = $setupDate ?: $eventDate;
        $end = $collectionDate ?: ($eventEndDate ?: $eventDate);

        return [
            'start' => CarbonImmutable::parse($start.' '.($setupDate || ! $hasTimeRange ? '00:00' : $startTime)),
            'end' => CarbonImmutable::parse($end.' '.($collectionDate || ! $hasTimeRange ? '23:59' : $endTime)),
        ];
    }

    /**
     * @param array{start: CarbonImmutable, end: CarbonImmutable} $window
     */
    private function eventOverlapsWindow(Event $event, array $window): bool
    {
        $eventStartDate = $event->setup_date?->format('Y-m-d') ?? $event->event_date?->format('Y-m-d');
        if (! $eventStartDate) {
            return false;
        }

        $eventEndDate = $event->collection_date?->format('Y-m-d')
            ?? $event->event_end_date?->format('Y-m-d')
            ?? $eventStartDate;
        $hasTimeRange = $event->event_start_time && $event->event_end_time;
        $startTime = $event->setup_date || ! $hasTimeRange ? '00:00' : $event->event_start_time;
        $endTime = $event->collection_date || ! $hasTimeRange ? '23:59' : $event->event_end_time;

        $eventStart = CarbonImmutable::parse($eventStartDate.' '.$startTime);
        $eventEnd = CarbonImmutable::parse($eventEndDate.' '.$endTime);

        return $window['start']->lt($eventEnd) && $eventStart->lt($window['end']);
    }
}
