<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventSchedulingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function ownerWorkspace(): array
    {
        $business = Business::create([
            'name' => 'Event Scheduling Business',
            'slug' => 'event-scheduling-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $user = User::factory()->create(['username' => 'schedule-owner']);
        $business->users()->attach($user->id, ['role' => 'owner']);
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Schedule Customer',
        ]);

        $this->actingAs($user);

        return [$business, $customer];
    }

    public function test_event_schedule_fields_are_persisted_and_clashes_are_reported(): void
    {
        [$business, $customer] = $this->ownerWorkspace();

        Event::create([
            'business_id' => $business->id,
            'reference' => 'SCHEDULE-EXISTING',
            'name' => 'Existing venue booking',
            'event_date' => '2026-12-11',
            'event_start_time' => '10:00',
            'event_end_time' => '12:00',
            'status' => 'confirmed',
        ]);

        $response = $this->post(route('work.store'), [
            'customer_id' => $customer->id,
            'name' => 'Multi-day wedding',
            'event_type' => config('zazu.job_types')[0],
            'event_date' => '2026-12-11',
            'event_end_date' => '2026-12-12',
            'event_start_time' => '09:00',
            'event_end_time' => '18:00',
            'setup_date' => '2026-12-10',
            'collection_date' => '2026-12-13',
            'event_address' => 'Community hall',
        ]);

        $event = Event::query()->where('business_id', $business->id)
            ->where('name', 'Multi-day wedding')
            ->firstOrFail();

        $this->assertSame('2026-12-12', $event->event_end_date->format('Y-m-d'));
        $this->assertSame('09:00', substr((string) $event->event_start_time, 0, 5));
        $this->assertSame('2026-12-10', $event->setup_date->format('Y-m-d'));
        $this->assertSame('2026-12-13', $event->collection_date->format('Y-m-d'));
        $response->assertSessionHas('schedule_warning');
    }

    public function test_same_day_event_end_time_must_follow_start_time(): void
    {
        [, $customer] = $this->ownerWorkspace();

        $this->from(route('work.create'))->post(route('work.store'), [
            'customer_id' => $customer->id,
            'name' => 'Invalid event time',
            'event_type' => config('zazu.job_types')[0],
            'event_date' => '2026-12-11',
            'event_end_date' => '2026-12-11',
            'event_start_time' => '15:00',
            'event_end_time' => '14:00',
        ])->assertSessionHasErrors('event_end_time');
    }

    public function test_schedule_columns_are_present_for_existing_event_records(): void
    {
        foreach (['event_end_date', 'event_start_time', 'event_end_time', 'setup_date', 'collection_date'] as $column) {
            $this->assertTrue(Schema::hasColumn('events', $column));
        }
    }
}
