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
        $setupDate = $schedule['setup_date'] ?? null;
        $collectionDate = $schedule['collection_date'] ?? null;
        $eventDate = $schedule['event_date'] ?? null;
        $eventEndDate = $schedule['event_end_date'] ?? null;
        $eventStartTime = $schedule['event_start_time'] ?? null;
        $eventEndTime = $schedule['event_end_time'] ?? null;
        $startDate = $setupDate ?: $eventDate;
        $endDate = $collectionDate ?: ($eventEndDate ?: $eventDate);
        $hasCompleteTimeRange = $eventStartTime !== null && $eventStartTime !== ''
            && $eventEndTime !== null && $eventEndTime !== '';
        $startTime = $setupDate || ! $hasCompleteTimeRange ? '00:00' : $eventStartTime;
        $endTime = $collectionDate || ! $hasCompleteTimeRange ? '23:59' : $eventEndTime;

        if (! $startDate || ! $endDate) {
            return collect();
        }

        $windowStart = CarbonImmutable::parse($startDate.' '.$startTime);
        $windowEnd = CarbonImmutable::parse($endDate.' '.$endTime);

        return Event::query()
            ->where('business_id', $businessId)
            ->whereNotIn('status', Event::TERMINAL_STATUSES)
            ->when($exceptEventId, fn ($query) => $query->where('id', '!=', $exceptEventId))
            ->where(function ($query) use ($endDate): void {
                $query->whereDate('setup_date', '<=', $endDate)
                    ->orWhere(function ($fallback) use ($endDate): void {
                        $fallback->whereNull('setup_date')->whereDate('event_date', '<=', $endDate);
                    });
            })
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

                $hasOtherTimeRange = $event->event_start_time && $event->event_end_time;
                $otherStartTime = $event->setup_date || ! $hasOtherTimeRange ? '00:00' : $event->event_start_time;
                $otherEndTime = $event->collection_date || ! $hasOtherTimeRange ? '23:59' : $event->event_end_time;
                $otherStart = CarbonImmutable::parse($otherStartDate.' '.$otherStartTime);
                $otherEnd = CarbonImmutable::parse($otherEndDate.' '.$otherEndTime);

                return $windowStart->lt($otherEnd) && $otherStart->lt($windowEnd);
            })
            ->values();
    }
}
