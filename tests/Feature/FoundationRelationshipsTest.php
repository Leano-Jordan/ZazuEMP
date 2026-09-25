<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_contacts_and_events_are_related_to_a_customer_and_business(): void
    {
        $business = Business::create([
            'name' => 'Test Events',
            'slug' => 'test-events',
        ]);

        $user = User::factory()->create();

        $business->users()->attach($user, ['role' => 'owner']);

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Jane Customer',
        ]);

        $contact = CustomerContact::create([
            'customer_id' => $customer->id,
            'name' => 'Jane Customer',
            'phone' => '0123456789',
            'is_primary' => true,
        ]);

        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'event_day_contact_id' => $contact->id,
            'reference' => 'ZAZ-TEST-001',
            'name' => 'Test Event',
            'event_date' => '2026-10-01',
            'status' => 'draft',
        ]);

        $this->assertTrue($business->customers->contains($customer));
        $this->assertTrue($business->events->contains($event));
        $this->assertTrue($customer->contacts->contains($contact));
        $this->assertTrue($customer->events->contains($event));
        $this->assertTrue($event->customer->is($customer));
        $this->assertTrue($event->eventDayContact->is($contact));
        $this->assertTrue($customer->primaryContact->is($contact));
    }
}