<?php

namespace App\Support;

use Carbon\Carbon;

class SouthAfricaHolidayCalendar
{
    public const CATEGORIES = [
        'public' => 'South African public holidays',
        'muslim' => 'Muslim observances',
        'hindu' => 'Hindu observances',
        'christian' => 'Christian observances',
        'jewish' => 'Jewish observances',
        'cultural' => 'South African and cultural observances',
    ];

    public function forYear(int $year): array
    {
        if ($year !== 2026) {
            return [];
        }

        return [
            ['date' => '2026-01-01', 'name' => "New Year's Day", 'category' => 'public'],
            ['date' => '2026-03-21', 'name' => 'Human Rights Day', 'category' => 'public'],
            ['date' => '2026-04-03', 'name' => 'Good Friday', 'category' => 'public'],
            ['date' => '2026-04-06', 'name' => 'Family Day', 'category' => 'public'],
            ['date' => '2026-04-27', 'name' => 'Freedom Day', 'category' => 'public'],
            ['date' => '2026-05-01', 'name' => "Workers' Day", 'category' => 'public'],
            ['date' => '2026-06-16', 'name' => 'Youth Day', 'category' => 'public'],
            ['date' => '2026-08-09', 'name' => "National Women's Day", 'category' => 'public'],
            ['date' => '2026-08-10', 'name' => "National Women's Day — observed public holiday", 'category' => 'public'],
            ['date' => '2026-09-24', 'name' => 'Heritage Day', 'category' => 'public'],
            ['date' => '2026-11-04', 'name' => 'Local Government Elections', 'category' => 'public'],
            ['date' => '2026-12-16', 'name' => 'Day of Reconciliation', 'category' => 'public'],
            ['date' => '2026-12-25', 'name' => 'Christmas Day', 'category' => 'public'],
            ['date' => '2026-12-26', 'name' => 'Day of Goodwill', 'category' => 'public'],

            ['date' => '2026-02-19', 'name' => 'Ramadan begins', 'category' => 'muslim'],
            ['date' => '2026-03-16', 'name' => 'Laylat al-Qadr', 'category' => 'muslim'],
            ['date' => '2026-03-21', 'name' => 'Eid al-Fitr', 'category' => 'muslim'],
            ['date' => '2026-05-28', 'name' => 'Eid al-Adha', 'category' => 'muslim'],
            ['date' => '2026-06-17', 'name' => 'Islamic New Year (Muharram)', 'category' => 'muslim'],
            ['date' => '2026-08-25', 'name' => 'Mawlid / Milad un Nabi', 'category' => 'muslim'],

            ['date' => '2026-02-15', 'name' => 'Maha Shivaratri', 'category' => 'hindu'],
            ['date' => '2026-03-03', 'name' => 'Holi', 'category' => 'hindu'],
            ['date' => '2026-08-27', 'name' => 'Raksha Bandhan', 'category' => 'hindu'],
            ['date' => '2026-09-04', 'name' => 'Janmashtami', 'category' => 'hindu'],
            ['date' => '2026-09-14', 'name' => 'Ganesh Chaturthi', 'category' => 'hindu'],
            ['date' => '2026-11-08', 'name' => 'Diwali', 'category' => 'hindu'],

            ['date' => '2026-04-05', 'name' => 'Easter Sunday', 'category' => 'christian'],
            ['date' => '2026-04-04', 'name' => 'Holy Saturday', 'category' => 'christian'],
            ['date' => '2026-12-24', 'name' => 'Christmas Eve', 'category' => 'christian'],

            ['date' => '2026-07-18', 'name' => 'Nelson Mandela International Day', 'category' => 'cultural'],
            ['date' => '2026-02-21', 'name' => 'Armed Forces Day', 'category' => 'cultural'],
        ];
    }

    public function visibleForYear(int $year, array $preferences = []): array
    {
        $enabled = array_merge(array_fill_keys(array_keys(self::CATEGORIES), true), $preferences);
        return collect($this->forYear($year))
            ->filter(fn (array $holiday) => (bool) ($enabled[$holiday['category']] ?? true))
            ->map(function (array $holiday) {
                $holiday['date'] = Carbon::parse($holiday['date']);
                return $holiday;
            })
            ->values()
            ->all();
    }
}
