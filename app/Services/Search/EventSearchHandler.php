<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\Event;

class EventSearchHandler extends AbstractWorkspaceSearchHandler
{
    public function search(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = Event::query()->where('business_id', $business->id);
        $this->text($query, $term, ['reference', 'name', 'event_type', 'customer_name', 'customer_phone', 'customer_email', 'event_address']);
        $this->applyStatusAndDate($query, $status, $from, $to, 'event_date');

        return $query->latest('event_date')->limit(10)->get()->map(fn (Event $event) => [
            'type' => 'event',
            'type_label' => 'Job',
            'title' => $event->name,
            'meta' => $event->customer_name.' · '.$event->reference,
            'status' => $event->status,
            'date' => $event->event_date?->format('d M Y'),
            'href' => route('work.show', $event),
            'sort_date' => $event->event_date?->toDateString(),
        ])->all();
    }
}
