<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

final class EventScheduleValidator
{
    /**
     * Check that the dates and times for a job follow the correct order.
     *
     * The caller validates the individual date/time formats first. This
     * class checks the relationships between those values for both online
     * forms and offline changes.
     *
     * @param array<string, mixed> $schedule
     */
    public function validate(array $schedule): void
    {
        $startDate = $schedule['event_date'] ?? null;
        $endDate = $schedule['event_end_date'] ?? $startDate;

        $this->validateDateOrder($startDate, $endDate);
        $this->validateSetupDate($schedule['setup_date'] ?? null, $startDate);
        $this->validateCollectionDate($schedule['collection_date'] ?? null, $endDate);
        $this->validateTimeOrder(
            $startDate,
            $endDate,
            $schedule['event_start_time'] ?? null,
            $schedule['event_end_time'] ?? null,
        );
    }

    private function validateDateOrder(?string $startDate, ?string $endDate): void
    {
        if ($startDate && $endDate && $endDate < $startDate) {
            throw ValidationException::withMessages([
                'event_end_date' => 'The job end date must be the same as or later than the start date.',
            ]);
        }
    }

    private function validateSetupDate(?string $setupDate, ?string $startDate): void
    {
        if ($setupDate && $startDate && $setupDate > $startDate) {
            throw ValidationException::withMessages([
                'setup_date' => 'The setup date must be the same as or earlier than the job start date.',
            ]);
        }
    }

    private function validateCollectionDate(?string $collectionDate, ?string $endDate): void
    {
        if ($collectionDate && $endDate && $collectionDate < $endDate) {
            throw ValidationException::withMessages([
                'collection_date' => 'The equipment collection date must be the same as or later than the job end date.',
            ]);
        }
    }

    private function validateTimeOrder(
        ?string $startDate,
        ?string $endDate,
        ?string $startTime,
        ?string $endTime,
    ): void {
        if ($startDate && $endDate === $startDate && $startTime && $endTime && $endTime <= $startTime) {
            throw ValidationException::withMessages([
                'event_end_time' => 'For a one-day job, the end time must be later than the start time.',
            ]);
        }
    }
}
