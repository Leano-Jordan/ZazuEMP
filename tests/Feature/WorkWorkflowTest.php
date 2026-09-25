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

    public function test_customer_can_be_created_with_a_primary_contact(): void
    {
        $response = $this->post(route('customers.store'), [
            'name' => 'Jane Customer',
            'notes' => 'Test customer',
            'primary_contact_name' => 'Jane Customer',
            'primary_contact_phone' => '0712345678',
            'primary_contact_email' => 'jane@example.com',
        ]);

        $response->assertRedirect(route('customers.index'));

        $customer = Customer::query()->where('name', 'Jane Customer')->firstOrFail();

        $this->assertDatabaseHas('customer_contacts', [
            'customer_id' => $customer->id,
            'name' => 'Jane Customer',
            'is_primary' => true,
        ]);
    }

    public function test_work_can_be_created_for_a_customer_and_contact(): void
    {
        $customer = Customer::create(['name' => 'Jane Customer']);

        $contact = CustomerContact::create([
            'customer_id' => $customer->id,
            'name' => 'Jane Customer',
            'phone' => '0712345678',
            'is_primary' => true,
        ]);

        $response = $this->post(route('work.store'), [
            'customer_id' => $customer->id,
            'event_day_contact_id' => $contact->id,
            'name' => 'Jane Wedding',
            'event_type' => 'Wedding',
            'event_date' => '2026-10-10',
            'event_address' => 'Pretoria',
            'notes' => 'Test work',
        ]);

        $event = Event::query()->where('name', 'Jane Wedding')->firstOrFail();

        $response->assertRedirect(route('work.show', $event));

        $this->assertSame($customer->id, $event->customer_id);
        $this->assertSame($contact->id, $event->event_day_contact_id);
        $this->assertSame('Jane Customer', $event->customer_name);
    }

    public function test_work_rejects_a_contact_belonging_to_another_customer(): void
    {
        $customer = Customer::create(['name' => 'Jane Customer']);
        $otherCustomer = Customer::create(['name' => 'Other Customer']);

        $contact = CustomerContact::create([
            'customer_id' => $otherCustomer->id,
            'name' => 'Other Contact',
            'is_primary' => true,
        ]);

        $response = $this->post(route('work.store'), [
            'customer_id' => $customer->id,
            'event_day_contact_id' => $contact->id,
            'name' => 'Invalid Work',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('events', ['name' => 'Invalid Work']);
    }
}
