<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsOwner();
    }

    use RefreshDatabase;

    public function test_work_can_be_created_from_a_customer_with_day_and_night_contacts(): void
    {
        $customer = Customer::create(['name' => 'Zazu Test Customer']);

        $day = CustomerContact::create([
            'customer_id' => $customer->id,
            'name' => 'Day Contact',
            'phone' => '0123456789',
            'label' => 'Day',
            'is_primary' => true,
        ]);

        $night = CustomerContact::create([
            'customer_id' => $customer->id,
            'name' => 'Night Contact',
            'phone' => '0987654321',
            'label' => 'Night',
        ]);

        $response = $this->post(route('work.store'), [
            'customer_id' => $customer->id,
            'event_day_contact_id' => $day->id,
            'event_night_contact_id' => $night->id,
            'name' => 'Zazu Test Work',
            'event_type' => 'Catering order',
            'event_date' => '2026-10-15',
            'event_address' => 'Pretoria',
            'notes' => 'Workflow test',
        ]);

        $event = Event::query()->where('name', 'Zazu Test Work')->first();

        $response->assertRedirect(route('work.show', $event));
        $response->assertSessionHas('success', 'Job created. Start by checking the services below.');

        $this->assertNotNull($event);
        $this->assertSame($customer->id, $event->customer_id);
        $this->assertSame($day->id, $event->event_day_contact_id);
        $this->assertSame($night->id, $event->event_night_contact_id);
        $this->assertSame('draft', $event->status);
    }

    public function test_work_can_be_edited_and_reassigned_to_different_day_and_night_contacts(): void
    {
        $customer = Customer::create(['name' => 'Edit Customer']);
        $oldDay = CustomerContact::create(['customer_id' => $customer->id, 'name' => 'Old Day']);
        $newDay = CustomerContact::create(['customer_id' => $customer->id, 'name' => 'New Day']);
        $newNight = CustomerContact::create(['customer_id' => $customer->id, 'name' => 'New Night']);

        $event = Event::create([
            'customer_id' => $customer->id,
            'event_day_contact_id' => $oldDay->id,
            'reference' => 'ZAZU-EDIT-001',
            'name' => 'Original Work',
            'event_date' => '2026-10-20',
            'status' => 'draft',
        ]);

        $response = $this->put(route('work.update', $event), [
            'customer_id' => $customer->id,
            'event_day_contact_id' => $newDay->id,
            'event_night_contact_id' => $newNight->id,
            'name' => 'Updated Work',
            'event_type' => 'Equipment hire',
            'event_date' => '2026-10-21',
            'status' => 'confirmed',
        ]);

        $response->assertRedirect(route('work.show', $event));
        $fresh = $event->fresh();
        $this->assertSame('Updated Work', $fresh->name);
        $this->assertSame('confirmed', $fresh->status);
        $this->assertSame($newDay->id, $fresh->event_day_contact_id);
        $this->assertSame($newNight->id, $fresh->event_night_contact_id);
    }

    public function test_work_customer_cannot_change_after_a_quote_exists(): void
    {
        $customer = Customer::create(['name' => 'Quoted Customer']);
        $otherCustomer = Customer::create(['name' => 'Other Customer']);

        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZU-LOCK-001',
            'name' => 'Quoted Work',
            'event_date' => '2026-10-22',
            'status' => 'draft',
        ]);

        // Use the relationship existence check that the controller uses.
        $event->quotes()->create([
            'reference' => 'QUO-LOCK-001',
            'status' => 'draft',
            'currency' => 'ZAR',
        ]);

        $response = $this->put(route('work.update', $event), [
            'customer_id' => $otherCustomer->id,
            'name' => 'Quoted Work',
            'event_type' => 'Catering order',
            'event_date' => '2026-10-22',
            'status' => 'confirmed',
        ]);

        $response->assertRedirect(route('work.edit', $event));
        $response->assertSessionHas('error');

        $this->assertSame($customer->id, $event->fresh()->customer_id);
    }

    public function test_work_can_be_removed_without_destroying_the_record(): void
    {
        $customer = Customer::create(['name' => 'Remove Customer']);
        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZU-REMOVE-001',
            'name' => 'Removable Work',
            'event_date' => '2026-10-22',
            'status' => 'draft',
        ]);

        $response = $this->delete(route('work.destroy', $event));

        $response->assertRedirect(route('work.index'));
        $this->assertSoftDeleted('events', ['id' => $event->id]);
        $this->assertNotNull(Event::withTrashed()->find($event->id));
    }

    public function test_event_day_and_night_contacts_must_belong_to_selected_customer(): void
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
            'event_night_contact_id' => $contact->id,
            'name' => 'Invalid Work',
            'event_type' => 'Catering order',
            'event_date' => '2026-10-20',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('events', ['name' => 'Invalid Work']);
    }
}
