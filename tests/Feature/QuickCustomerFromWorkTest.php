<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickCustomerFromWorkTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_job_can_create_a_customer_in_context_and_return_it_as_selected_data(): void
    {
        $this->signInAsOwner();

        $this->get(route('work.create'))
            ->assertOk()
            ->assertSee('Add customer', false)
            ->assertSee('No customer yet? Add their essentials here without leaving this job.');

        $response = $this->postJson(route('customers.quick_from_work'), [
            'name' => 'New Job Customer',
            'primary_contact_name' => 'Thandi Mokoena',
            'primary_contact_phone' => '0712345678',
            'primary_contact_email' => 'thandi@example.test',
        ]);

        $response->assertCreated()
            ->assertJsonPath('customer.name', 'New Job Customer')
            ->assertJsonPath('customer.contacts.0.name', 'Thandi Mokoena');

        $this->assertDatabaseHas('customers', [
            'name' => 'New Job Customer',
        ]);

        $this->assertDatabaseHas('customer_contacts', [
            'name' => 'Thandi Mokoena',
            'is_primary' => true,
        ]);
    }

    public function test_quick_customer_creation_remains_scoped_to_the_active_business(): void
    {
        $user = $this->signInAsOwner();
        $business = app(\App\Support\CurrentBusiness::class)->model($user);

        $this->postJson(route('customers.quick_from_work'), [
            'name' => 'Scoped Quick Customer',
            'primary_contact_name' => 'Primary Contact',
        ])->assertCreated();

        $this->assertDatabaseHas('customers', [
            'business_id' => $business->id,
            'name' => 'Scoped Quick Customer',
        ]);
    }

    public function test_duplicate_customer_name_is_rejected_by_quick_creation(): void
    {
        $this->signInAsOwner();

        Customer::create([
            'business_id' => app(\App\Support\CurrentBusiness::class)->model(auth()->user())->id,
            'name' => 'Existing Customer',
        ]);

        $this->postJson(route('customers.quick_from_work'), [
            'name' => 'Existing Customer',
            'primary_contact_name' => 'Primary Contact',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }
}
