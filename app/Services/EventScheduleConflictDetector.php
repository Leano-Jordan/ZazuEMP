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
        $startDate = $schedule['setup_date'] ?: $schedule['event_date'];
        $endDate = $schedule['collection_date'] ?: ($schedule['event_end_date'] ?: $schedule['event_date']);
        $startTime = $schedule['setup_date'] ? '00:00' : ($schedule['event_start_time'] ?: '00:00');
        $endTime = $schedule['collection_date'] ? '23:59' : ($schedule['event_end_time'] ?: '23:59');

        $windowStart = CarbonImmutable::parse($startDate.' '.$startTime);
        $windowEnd = CarbonImmutable::parse($endDate.' '.$endTime);

        return Event::query()
            ->where('business_id', $businessId)
            ->whereNotIn('status', Event::TERMINAL_STATUSES)
            ->when($exceptEventId, fn ($query) => $query->whereKeyNot($exceptEventId))
            ->whereDate('event_date', '<=', $endDate)
            ->where(function ($query) use ($startDate): void {
                $query->whereDate('collection_date', '>=', $startDate)
                    ->orWhere(function ($fallback) use ($startDate): void {
                        $fallback->whereNull('collection_date')
                            ->where(function ($end) use ($startDate): void {
                                $end->whereDate('event_end_date', '>=', $startDate)
                                    ->orWhere(function ($single) use ($startDate): void {
                                        $single->whereNull('event_end_date')->whereDate('event_date', '>=', $startDate);
                                    });
                            });
                    });
            })
            ->orderBy('event_date')
            ->get()
            ->filter(function (Event $event) use ($windowStart, $windowEnd): bool {
                $otherStartDate = $event->setup_date?->format('Y-m-d') ?? $event->event_date?->format('Y-m-d');
                $otherEndDate = $event->collection_date?->format('Y-m-d')
                    ?? $event->event_end_date?->format('Y-m-d')
                    ?? $otherStartDate;

                if (! $otherStartDate || ! $otherEndDate) {
                    return false;
                }

                $otherStartTime = $event->setup_date ? '00:00' : ($event->event_start_time ?: '00:00');
                $otherEndTime = $event->collection_date ? '23:59' : ($event->event_end_time ?: '23:59');
                $otherStart = CarbonImmutable::parse($otherStartDate.' '.$otherStartTime);
                $otherEnd = CarbonImmutable::parse($otherEndDate.' '.$otherEndTime);

                return $windowStart->lt($otherEnd) && $otherStart->lt($windowEnd);
            })
            ->values();
    }
}
