<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventSchemaContractTest extends TestCase
{
    public function test_events_table_matches_the_event_model_schema_contract(): void
    {
        $this->assertTrue(Schema::hasTable('events'));

        foreach ([
            'business_id',
            'customer_id',
            'event_day_contact_id',
            'event_night_contact_id',
            'deleted_at',
        ] as $column) {
            $this->assertTrue(
                Schema::hasColumn('events', $column),
                "events.{$column} is required by the Event model but is missing from the live schema."
            );
        }
    }
}
