<?php

namespace Tests\Feature;

use App\Services\EventScheduleValidator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EventScheduleValidatorTest extends TestCase
{
    public function test_conflicting_job_dates_and_times_are_rejected(): void
    {
        $validator = app(EventScheduleValidator::class);
        $base = [
            'event_date' => '2026-12-11',
            'event_end_date' => '2026-12-12',
            'event_start_time' => '09:00',
            'event_end_time' => '17:00',
            'setup_date' => '2026-12-10',
            'collection_date' => '2026-12-13',
        ];

        $cases = [
            [
                'field' => 'event_end_date',
                'values' => ['event_end_date' => '2026-12-10'],
            ],
            [
                'field' => 'setup_date',
                'values' => ['setup_date' => '2026-12-12'],
            ],
            [
                'field' => 'collection_date',
                'values' => ['collection_date' => '2026-12-11'],
            ],
            [
                'field' => 'collection_date',
                'values' => ['event_end_date' => null, 'collection_date' => '2026-12-10'],
            ],
            [
                'field' => 'event_end_time',
                'values' => [
                    'event_end_date' => '2026-12-11',
                    'event_end_time' => '08:59',
                ],
            ],
        ];

        foreach ($cases as $case) {
            try {
                $validator->validate(array_replace($base, $case['values']));
                $this->fail('Expected the '.$case['field'].' schedule conflict to be rejected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey($case['field'], $exception->errors());
                $this->assertNotEmpty($exception->errors()[$case['field']]);
            }
        }
    }

    public function test_multi_day_jobs_can_end_earlier_by_clock_time_on_the_last_day(): void
    {
        app(EventScheduleValidator::class)->validate([
            'event_date' => '2026-12-11',
            'event_end_date' => '2026-12-12',
            'event_start_time' => '21:00',
            'event_end_time' => '07:00',
            'setup_date' => '2026-12-10',
            'collection_date' => '2026-12-13',
        ]);

        $this->assertTrue(true);
    }
}
