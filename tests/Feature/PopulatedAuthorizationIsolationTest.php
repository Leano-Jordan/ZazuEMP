<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopulatedAuthorizationIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_second_business_cannot_read_or_mutate_first_business_populated_records(): void
    {
        $this->seed(DemoScenarioSeeder::class);

        $demoBusiness = Business::query()
            ->where('slug', 'zazu-demo-catering')
            ->firstOrFail();

        $foreignBusiness = Business::create([
            'name' => 'Second Demo Business',
            'slug' => 'second-demo-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $foreignOwner = User::factory()->create([
            'email' => 'second.owner@example.test',
            'username' => 'second_demo_owner',
        ]);

        $foreignBusiness->users()->attach($foreignOwner->id, [
            'role' => 'owner',
            'experience_level' => 'intermediate',
        ]);

        $customer = Customer::query()
            ->where('business_id', $demoBusiness->id)
            ->where('name', 'Mokoena Family Events')
            ->firstOrFail();

        $event = Event::query()
            ->where('business_id', $demoBusiness->id)
            ->where('reference', 'ZAZU-DEMO-001')
            ->firstOrFail();

        $invoice = Invoice::query()
            ->where('business_id', $demoBusiness->id)
            ->where('number', 'INV-ZAZU-DEMO-001')
            ->firstOrFail();

        $ownCustomer = Customer::create([
            'business_id' => $foreignBusiness->id,
            'name' => 'Second Business Customer',
            'email' => 'second.customer@example.test',
        ]);

        $this->actingAs($foreignOwner);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee($customer->name)
            ->assertDontSee($event->name);

        $this->get(route('customers.show', $customer))
            ->assertNotFound();

        $this->get(route('work.show', $event))
            ->assertNotFound();

        $this->get(route('finance.invoices.show', $invoice))
            ->assertNotFound();

        $this->get(route('search.index', [
            'q' => 'Mokoena',
            'type' => 'customer',
        ]))
            ->assertOk()
            ->assertDontSee($customer->name);

        $this->post(route('work.store'), [
            'customer_id' => $customer->id,
            'name' => 'Blocked Cross Business Work',
            'event_type' => 'Wedding',
            'event_date' => now()->addDays(5)->toDateString(),
        ])->assertNotFound();

        $this->assertDatabaseMissing('events', [
            'name' => 'Blocked Cross Business Work',
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $ownCustomer->id,
            'business_id' => $foreignBusiness->id,
        ]);
    }
}
