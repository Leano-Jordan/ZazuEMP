<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\EventPreparationItem;
use App\Models\EventRequirement;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ZazuHelperContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_helper_hides_linked_booking_record_counts_without_their_workspace_permission(): void
    {
        config(['zazu.permissions.roles.staff' => ['dashboard.view', 'work.view']]);

        $business = $this->makeBusiness('Helper Record Permissions');
        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'staff']);

        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'HELPER-PERMISSION-'.Str::upper(Str::random(5)),
            'name' => 'Restricted booking records',
            'status' => 'confirmed',
        ]);
        EventPreparationItem::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'title' => 'Prepare equipment',
            'category' => 'Equipment',
            'quantity' => '1.00',
            'unit' => 'set',
            'status' => 'open',
        ]);
        $supplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'Restricted supplier',
        ]);
        PurchaseOrder::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'supplier_id' => $supplier->id,
            'reference' => 'HELPER-RESTRICTED-PO-'.Str::upper(Str::random(5)),
            'status' => 'sent',
            'currency' => 'ZAR',
            'total_amount' => '350.00',
        ]);

        $this->actingAs($user)
            ->get(route('work.show', $event))
            ->assertOk()
            ->assertSee('data-zazu-helper-event-records="1"', false)
            ->assertSee('1 preparation item')
            ->assertSee('Review preparation')
            ->assertDontSee('purchase order')
            ->assertDontSee('Review purchasing');
    }

    public function test_helper_guidance_adapts_to_the_saved_business_focus(): void
    {
        $cases = [
            'chairs_tents' => 'Keep hire quantities, availability, delivery, setup, collection and asset condition connected to each booking.',
            'sound_dj' => 'Keep sound equipment, crew, setup/breakdown, programme timing and transport connected to each booking.',
            'catering_baking' => 'Keep menus, quantities, dietary details, purchasing, preparation and delivery connected to each booking.',
        ];

        foreach ($cases as $focus => $expectedGuidance) {
            $business = $this->makeBusiness('Helper Focus '.Str::random(6));
            $user = User::factory()->create();
            $business->users()->attach($user->id, [
                'role' => 'owner',
                'primary_niche' => $focus,
                'experience_level' => 'intermediate',
            ]);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertSee('data-zazu-helper-focus="'.$focus.'"', false)
                ->assertSee($expectedGuidance);
        }
    }

    public function test_helper_combines_primary_focus_with_multiple_active_catalogue_capabilities(): void
    {
        $business = $this->makeBusiness('Helper Multi Service Business');
        $user = User::factory()->create();
        $business->users()->attach($user->id, [
            'role' => 'owner',
            'primary_niche' => 'sound_dj',
        ]);

        BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'White chair hire',
            'category' => 'Furniture & equipment',
            'capability_type' => 'rental',
            'pricing_basis' => 'per_unit',
            'default_unit' => 'chair',
            'currency' => 'ZAR',
            'is_active' => true,
        ]);
        BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'Buffet catering',
            'category' => 'Catering',
            'capability_type' => 'service',
            'pricing_basis' => 'per_person',
            'default_unit' => 'person',
            'currency' => 'ZAR',
            'is_active' => true,
        ]);
        BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'Archived cake service',
            'category' => 'Baking',
            'capability_type' => 'service',
            'pricing_basis' => 'custom',
            'currency' => 'ZAR',
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-zazu-helper-focus="sound_dj"', false)
            ->assertSee('data-zazu-helper-focuses="sound_dj,chairs_tents,catering_baking"', false)
            ->assertSee('For your active service mix:')
            ->assertSee('Keep sound equipment, crew, setup/breakdown, programme timing and transport connected to each booking.')
            ->assertSee('Keep hire quantities, availability, delivery, setup, collection and asset condition connected to each booking.')
            ->assertSee('Keep menus, quantities, dietary details, purchasing, preparation and delivery connected to each booking.')
            ->assertDontSee('Archived cake service')
            ->assertDontSee('Camera editing');
    }

    public function test_helper_shows_only_the_current_jobs_saved_components(): void
    {
        $business = $this->makeBusiness('Helper Event Scope');
        $user = User::factory()->create();
        $business->users()->attach($user->id, [
            'role' => 'owner',
            'primary_niche' => 'catering_baking',
        ]);
        BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'Buffet catering',
            'category' => 'Catering',
            'capability_type' => 'service',
            'pricing_basis' => 'per_person',
            'currency' => 'ZAR',
            'is_active' => true,
        ]);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'HELPER-SCOPE-'.Str::upper(Str::random(5)),
            'name' => 'Tent and chair booking',
            'status' => 'confirmed',
        ]);
        EventRequirement::create([
            'event_id' => $event->id,
            'description' => '100 white chairs',
            'category' => 'Furniture & equipment',
            'quantity' => '100.00',
            'unit' => 'chair',
            'status' => 'open',
        ]);
        EventRequirement::create([
            'event_id' => $event->id,
            'description' => 'Two stretch tents',
            'category' => 'Rentals',
            'quantity' => '2.00',
            'unit' => 'unit',
            'status' => 'open',
        ]);
        EventPreparationItem::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'title' => 'Prepare tent hardware',
            'category' => 'Equipment',
            'quantity' => '2.00',
            'unit' => 'set',
            'status' => 'blocked',
        ]);
        $this->createPlannedHelperCost($business, $event);

        $supplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'Event component supplier',
        ]);
        $purchaseOrderReference = 'HELPER-EVENT-PO-'.Str::upper(Str::random(5));
        PurchaseOrder::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'supplier_id' => $supplier->id,
            'reference' => $purchaseOrderReference,
            'status' => 'sent',
            'currency' => 'ZAR',
            'total_amount' => '250.00',
        ]);

        $this->actingAs($user)
            ->get(route('work.show', $event))
            ->assertOk()
            ->assertSee('data-zazu-helper-event-components="2"', false)
            ->assertSee('100 white chairs')
            ->assertSee('Two stretch tents')
            ->assertSee('data-zazu-helper-focus="catering_baking"', false)
            ->assertSee('data-zazu-helper-focuses="chairs_tents"', false)
            ->assertSee('For this booking, review hire quantities, asset availability, delivery, setup, collection and condition.')
            ->assertDontSee('For this booking, review menu quantities, dietary details, purchasing, preparation and delivery.')
            ->assertSee('data-zazu-helper-event-records="2"', false)
            ->assertSee('1 preparation item')
            ->assertSee('1 purchase order')
            ->assertSee('href="'.route('work.preparation.index', $event).'"', false)
            ->assertSee('Review preparation')
            ->assertSee('href="'.route('purchasing.index', ['event_id' => $event->id]).'"', false)
            ->assertSee('Review purchasing')
            ->assertSee('data-zazu-helper-event-record-link', false)
            ->assertSee('data-zazu-helper-event-operational-details="3"', false)
            ->assertSee('Current operational state')
            ->assertSee('Prepare tent hardware')
            ->assertSee('Blocked')
            ->assertSee($purchaseOrderReference)
            ->assertSee('Sent')
            ->assertSee('Confirm equipment supplier cost')
            ->assertSee('Planned')
            ->assertDontSee('Catering preparation')
            ->assertDontSee('Camera editing');
    }

    private function createPlannedHelperCost(Business $business, Event $event): EventCost
    {
        return EventCost::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'category' => 'Equipment',
            'description' => 'Confirm equipment supplier cost',
            'currency' => 'ZAR',
            'projected_amount' => '250.00',
            'actual_amount' => null,
            'status' => 'planned',
        ]);
    }

    private function makeBusiness(string $name): Business
    {
        return Business::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
    }
}
