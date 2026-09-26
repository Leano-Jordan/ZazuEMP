<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Models\EventRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BusinessIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function business(string $name): Business
    {
        return Business::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(6)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
    }

    private function userFor(Business $business): User
    {
        $user = User::factory()->create();
        $business->users()->attach($user, ['role' => 'owner']);

        return $user;
    }

    private function eventFor(Business $business, array $overrides = []): Event
    {
        return Event::create(array_merge([
            'business_id' => $business->id,
            'reference' => 'ZAZ-ISO-' . Str::upper(Str::random(8)),
            'name' => 'Isolation Test Work',
            'event_date' => now()->addDays(3)->toDateString(),
            'status' => 'draft',
        ], $overrides));
    }

    public function test_direct_business_models_receive_the_active_business_context_when_not_supplied(): void
    {
        $business = $this->business('Context Business');
        $user = $this->userFor($business);

        $this->actingAs($user);

        $customer = Customer::create(['name' => 'Context Customer']);
        $capability = BusinessCapability::create([
            'name' => 'Context Service',
            'category' => 'Catering',
            'capability_type' => 'service',
            'pricing_basis' => 'custom',
            'is_active' => true,
        ]);

        $this->assertSame($business->id, $customer->business_id);
        $this->assertSame($business->id, $capability->business_id);
    }

    public function test_customer_directory_isolated_to_active_business(): void
    {
        $first = $this->business('First Business');
        $second = $this->business('Second Business');
        $user = $this->userFor($first);

        Customer::create(['business_id' => $first->id, 'name' => 'Visible Customer']);
        Customer::create(['business_id' => $second->id, 'name' => 'Hidden Customer']);

        $response = $this->actingAs($user)->get(route('customers.index'));

        $response->assertOk();
        $response->assertSee('Visible Customer');
        $response->assertDontSee('Hidden Customer');
    }

    public function test_work_cannot_be_created_for_a_customer_from_another_business(): void
    {
        $first = $this->business('First Business');
        $second = $this->business('Second Business');
        $user = $this->userFor($first);

        $foreignCustomer = Customer::create([
            'business_id' => $second->id,
            'name' => 'Foreign Customer',
        ]);

        $response = $this->actingAs($user)->post(route('work.store'), [
            'customer_id' => $foreignCustomer->id,
            'name' => 'Blocked Cross Business Work',
            'event_type' => 'Wedding',
            'event_date' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertNotFound();
        $this->assertDatabaseMissing('events', ['name' => 'Blocked Cross Business Work']);
    }

    public function test_workload_overdue_filter_only_returns_current_business_work(): void
    {
        $first = $this->business('First Business');
        $second = $this->business('Second Business');
        $user = $this->userFor($first);

        $overdueOwn = $this->eventFor($first, ['name' => 'Own Overdue Work']);
        $overdueForeign = $this->eventFor($second, ['name' => 'Foreign Overdue Work']);

        EventPreparationItem::create([
            'business_id' => $first->id,
            'event_id' => $overdueOwn->id,
            'title' => 'Own overdue item',
            'status' => 'open',
            'due_date' => now()->subDay()->toDateString(),
        ]);

        EventPreparationItem::create([
            'business_id' => $second->id,
            'event_id' => $overdueForeign->id,
            'title' => 'Foreign overdue item',
            'status' => 'open',
            'due_date' => now()->subDay()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('work.index', ['filter' => 'overdue']));

        $response->assertOk();
        $response->assertSee('Own Overdue Work');
        $response->assertDontSee('Foreign Overdue Work');
    }

    public function test_work_status_transitions_follow_the_operational_lifecycle(): void
    {
        $business = $this->business('Lifecycle Business');
        $user = $this->userFor($business);
        $event = $this->eventFor($business);

        $invalid = $this->actingAs($user)->put(route('work.update', $event), [
            'name' => $event->name,
            'event_type' => 'Wedding',
            'event_date' => $event->event_date->toDateString(),
            'status' => 'in_progress',
        ]);

        $invalid->assertStatus(422);
        $this->assertSame('draft', $event->fresh()->status);

        $confirmed = $this->actingAs($user)->put(route('work.update', $event), [
            'name' => $event->name,
            'event_type' => 'Wedding',
            'event_date' => $event->event_date->toDateString(),
            'status' => 'confirmed',
        ]);

        $confirmed->assertRedirect(route('work.show', $event));

        $event->refresh();

        $invalidReverse = $this->actingAs($user)->put(route('work.update', $event), [
            'name' => $event->name,
            'event_type' => 'Wedding',
            'event_date' => $event->event_date->toDateString(),
            'status' => 'draft',
        ]);

        $invalidReverse->assertStatus(422);
        $this->assertSame('confirmed', $event->fresh()->status);
    }

    public function test_closed_work_cannot_receive_new_requirements(): void
    {
        $business = $this->business('Closed Work Business');
        $user = $this->userFor($business);
        $event = $this->eventFor($business, ['status' => 'completed']);

        $response = $this->actingAs($user)->post(route('work.requirements.store', $event), [
            'description' => 'Closed work requirement',
            'category' => 'Catering',
            'quantity' => 10,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('event_requirements', [
            'event_id' => $event->id,
            'description' => 'Closed work requirement',
        ]);
    }
}
