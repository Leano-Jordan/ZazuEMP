<?php

namespace TestsFeature;

use AppModelsBusiness;
use AppModelsBusinessCapability;
use AppModelsCustomer;
use AppModelsEvent;
use AppModelsEventRequirement;
use AppModelsEventCost;
use AppModelsEventPreparationItem;
use AppModelsQuote;
use AppModelsQuoteVersion;
use TestsTestCase;

class FoundationIntegrationTest extends TestCase
{
    public function test_resource_foundation_pages_are_connected_to_authoritative_work_and_catalogue_records(): void
    {
        $business = Business::create([
            'name' => 'Foundation Business',
            'slug' => 'foundation-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $this->signInAsOwner($business);

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Foundation Customer',
        ]);

        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'JOB-FND-001',
            'name' => 'Foundation Wedding',
            'event_type' => 'Wedding',
            'event_date' => now()->addDays(7)->toDateString(),
            'status' => 'confirmed',
        ]);

        $product = BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'Chicken',
            'category' => 'Catering',
            'capability_type' => 'product',
            'pricing_basis' => 'per_unit',
            'default_unit' => 'kg',
            'is_active' => true,
        ]);

        $rental = BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'Banquet chairs',
            'category' => 'Furniture & Equipment',
            'capability_type' => 'rental',
            'pricing_basis' => 'per_unit',
            'default_unit' => 'unit',
            'is_active' => true,
        ]);

        EventRequirement::create([
            'event_id' => $event->id,
            'capability_id' => $product->id,
            'description' => 'Chicken',
            'category' => 'Catering',
            'quantity' => 20,
            'unit' => 'kg',
            'status' => 'open',
        ]);

        EventRequirement::create([
            'event_id' => $event->id,
            'capability_id' => $rental->id,
            'description' => 'Banquet chairs',
            'category' => 'Furniture & Equipment',
            'quantity' => 100,
            'unit' => 'unit',
            'status' => 'open',
        ]);

        $this->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('Chicken')
            ->assertSee('Banquet chairs')
            ->assertSee('Foundation Wedding');

        $this->get(route('inventory.index'))
            ->assertOk()
            ->assertSee('Chicken')
            ->assertSee('20.00 kg')
            ->assertDontSee('Banquet chairs');

        $this->get(route('assets.index'))
            ->assertOk()
            ->assertSee('Banquet chairs')
            ->assertSee('100.00 unit')
            ->assertDontSee('Chicken');
    }

    public function test_reporting_foundation_reads_current_business_records_without_mixing_businesses(): void
    {
        $business = Business::create([
            'name' => 'Reporting Business',
            'slug' => 'reporting-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $otherBusiness = Business::create([
            'name' => 'Other Business',
            'slug' => 'other-reporting-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->signInAsOwner($business);

        $customer = Customer::create(['business_id' => $business->id, 'name' => 'Reporting Customer']);
        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'JOB-REP-001',
            'name' => 'Reporting Event',
            'event_date' => now()->addDays(3)->toDateString(),
            'status' => 'confirmed',
        ]);

        $otherCustomer = Customer::create(['business_id' => $otherBusiness->id, 'name' => 'Other Customer']);
        $otherEvent = Event::create([
            'business_id' => $otherBusiness->id,
            'customer_id' => $otherCustomer->id,
            'reference' => 'JOB-REP-002',
            'name' => 'Other Event',
            'event_date' => now()->addDays(3)->toDateString(),
            'status' => 'confirmed',
        ]);

        $quote = Quote::create([
            'event_id' => $event->id,
            'reference' => 'QUO-REP-001',
            'status' => 'draft',
            'currency' => 'ZAR',
        ]);
        $quote->versions()->create([
            'version' => 1,
            'status' => 'draft',
            'subtotal' => '12500.00',
            'tax_total' => '0.00',
            'total' => '12500.00',
        ]);

        EventCost::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'category' => 'Catering',
            'description' => 'Ingredients',
            'currency' => 'ZAR',
            'projected_amount' => '3000.00',
            'actual_amount' => '2800.00',
            'status' => 'incurred',
        ]);

        EventPreparationItem::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'title' => 'Pack catering',
            'status' => 'blocked',
        ]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Reporting Event')
            ->assertSee('12,500.00')
            ->assertSee('2,800.00')
            ->assertSee('1')
            ->assertDontSee('Other Event');

        $this->assertModelExists($otherEvent);
    }

    public function test_active_business_context_can_be_switched_only_to_an_active_membership(): void
    {
        $first = Business::create([
            'name' => 'First Business',
            'slug' => 'first-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $second = Business::create([
            'name' => 'Second Business',
            'slug' => 'second-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $user = $this->signInAsOwner($first);
        $second->users()->attach($user->id, ['role' => 'staff']);

        $this->assertSame($first->id, app(\App\Support\CurrentBusiness::class)->id($user));

        $this->post(route('business.switch'), ['business_id' => $second->id])
            ->assertRedirect();

        $this->assertSame($second->id, app(\App\Support\CurrentBusiness::class)->id($user));

        $third = Business::create([
            'name' => 'Unrelated Business',
            'slug' => 'unrelated-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->post(route('business.switch'), ['business_id' => $third->id])
            ->assertForbidden();

        $this->assertSame($second->id, app(\App\Support\CurrentBusiness::class)->id($user));
    }

    public function test_staff_ui_does_not_advertise_owner_only_settings_or_catalogue_editing(): void
    {
        $business = Business::create([
            'name' => 'Staff Business',
            'slug' => 'staff-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $staff = AppModelsUser::factory()->create();
        $business->users()->attach($staff->id, ['role' => 'staff']);

        BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'Staff-visible Service',
            'category' => 'Catering',
            'capability_type' => 'service',
            'pricing_basis' => 'custom',
            'is_active' => true,
        ]);

        $this->actingAs($staff);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('settings.index'), false);

        $this->get(route('capabilities.index'))
            ->assertOk()
            ->assertDontSee(route('capabilities.create'), false)
            ->assertDontSee(route('capabilities.edit', BusinessCapability::where('name', 'Staff-visible Service')->first()), false);
    }
}
