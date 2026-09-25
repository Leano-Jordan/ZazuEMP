<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_can_be_created_from_a_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Zazu Test Customer',
        ]);

        $contact = CustomerContact::create([
            'customer_id' => $customer->id,
            'name' => 'Event Contact',
            'phone' => '0123456789',
            'is_primary' => true,
        ]);

        $response = $this->post(route('work.store'), [
            'customer_id' => $customer->id,
            'event_day_contact_id' => $contact->id,
            'name' => 'Zazu Test Work',
            'event_type' => 'Catering',
            'event_date' => '2026-10-15',
            'event_address' => 'Pretoria',
            'notes' => 'Workflow test',
        ]);

        $event = Event::query()->where('name', 'Zazu Test Work')->first();

        $response->assertRedirect(route('work.show', $event));
        $response->assertSessionHas('success', 'Work created successfully.');

        $this->assertNotNull($event);
        $this->assertSame($customer->id, $event->customer_id);
        $this->assertSame($contact->id, $event->event_day_contact_id);
        $this->assertSame('draft', $event->status);
        $this->assertStringStartsWith('ZAZU-', $event->reference);
    }

    public function test_event_day_contact_must_belong_to_selected_customer(): void
    {
        $customer = Customer::create(['name' => 'Customer One']);
        $otherCustomer = Customer::create(['name' => 'Customer Two']);

        $contact = CustomerContact::create([
            'customer_id' => $otherCustomer->id,
            'name' => 'Wrong Customer Contact',
        ]);

        $response = $this->post(route('work.store'), [
            'customer_id' => $customer->id,
            'event_day_contact_id' => $contact->id,
            'name' => 'Invalid Work',
            'event_date' => '2026-10-20',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('events', ['name' => 'Invalid Work']);
    }
}
