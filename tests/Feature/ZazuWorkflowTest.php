<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZazuWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_requirements_can_be_added_to_a_work_record(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
        ]);

        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-REQ-001',
            'name' => 'Test Wedding',
            'event_date' => '2026-10-01',
            'status' => 'draft',
        ]);

        $response = $this->post(route('work.requirements.store', $event), [
            'description' => '100 chairs',
            'category' => 'Furniture & equipment',
            'quantity' => 100,
            'unit' => 'chairs',
            'notes' => 'White folding chairs',
        ]);

        $response->assertRedirect(route('work.show', $event));

        $this->assertDatabaseHas('event_requirements', [
            'event_id' => $event->id,
            'description' => '100 chairs',
            'category' => 'Furniture',
            'status' => 'open',
        ]);
    }

    public function test_customer_directory_opens_customer_relationship_workspace(): void
    {
        $customer = Customer::create([
            'name' => 'Customer Workspace Test',
        ]);

        $response = $this->get(route('customers.show', $customer));

        $response->assertOk();
        $response->assertSee('Customer Workspace Test');
        $response->assertSee(route('work.create', ['customer_id' => $customer->id]));
    }

    public function test_duplicate_customer_names_are_rejected(): void
    {
        Customer::create([
            'name' => 'Duplicate Customer',
        ]);

        $response = $this->from(route('customers.create'))->post(route('customers.store'), [
            'name' => 'Duplicate Customer',
            'primary_contact_name' => 'Primary Contact',
        ]);

        $response->assertRedirect(route('customers.create'));
        $response->assertSessionHasErrors('name');

        $this->assertDatabaseCount('customers', 1);
    }
}
