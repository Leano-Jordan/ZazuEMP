<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetAllocation;
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
        $restrictedAsset = Asset::create([
            'business_id' => $business->id,
            'asset_tag' => 'HELPER-STAFF-ASSET',
            'name' => 'Restricted asset',
            'status' => 'allocated',
            'condition' => 'good',
            'purchase_cost' => '500.00',
            'currency' => 'ZAR',
        ]);
        AssetAllocation::create([
            'business_id' => $business->id,
            'asset_id' => $restrictedAsset->id,
            'event_id' => $event->id,
            'allocated_from' => now()->toDateString(),
            'allocated_until' => now()->addDay()->toDateString(),
            'status' => 'allocated',
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

        $response = $this->actingAs($user)
            ->get(route('work.show', $event))
            ->assertOk();

        // Scope these assertions to the helper itself. The shared offline
        // workspace is a separate page surface and must not contaminate a
        // test of helper recommendations.
        $document = new \DOMDocument();
        $document->loadHTML($response->getContent());
        $helper = (new \DOMXPath($document))->query('//*[@data-zazu-helper]')->item(0);
        $this->assertNotNull($helper);

        $helperMarkup = $document->saveHTML($helper);
        $this->assertStringContainsString('data-zazu-helper-event-records="1"', $helperMarkup);
        $this->assertStringContainsString('1 preparation item', $helperMarkup);
        $this->assertStringContainsString('Review preparation', $helperMarkup);
        $this->assertStringNotContainsString('purchase order', strtolower($helperMarkup));
        $this->assertStringNotContainsString('Review purchasing', $helperMarkup);
        $this->assertStringNotContainsString('HELPER-STAFF-ASSET', $helperMarkup);
        $this->assertStringNotContainsString('Restricted asset', $helperMarkup);
    }

    public function test_helper_guidance_adapts_to_the_saved_business_focus(): void
    {
        $cases = [
            'chairs_tents' => 'Keep hire quantities, availability, delivery, setup, collection and asset condition connected to each booking.',
            'sound_dj' => 'Keep sound equipment, crew, setup/breakdown, programme timing and transport connected to each booking.',
            'catering_baking' => 'Keep menus, quantities, dietary details, purchasing, preparation and delivery connected to each booking.',
            'decor' => 'Keep styling scope, materials, setup plans, venue coordination and collection connected to each booking.',
            'photography' => 'Keep coverage, shot lists, crew, equipment, venue timing and final delivery connected to each booking.',
            'transport' => 'Keep vehicle capacity, routes, loading, delivery windows and collection connected to each booking.',
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

    public function test_helper_does_not_truncate_businesses_with_more_than_three_active_focus_areas(): void
    {
        $business = $this->makeBusiness('Helper Broad Service Mix');
        $user = User::factory()->create();
        $business->users()->attach($user->id, [
            'role' => 'owner',
            'primary_niche' => 'mixed',
        ]);

        foreach ([
            ['name' => 'Sound hire', 'category' => 'Sound & entertainment'],
            ['name' => 'Chair hire', 'category' => 'Furniture & equipment'],
            ['name' => 'Catering', 'category' => 'Catering'],
            ['name' => 'Wedding decor', 'category' => 'Decor'],
        ] as $offering) {
            BusinessCapability::create($offering + [
                'business_id' => $business->id,
                'capability_type' => 'service',
                'pricing_basis' => 'custom',
                'currency' => 'ZAR',
                'is_active' => true,
            ]);
        }

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-zazu-helper-focuses="chairs_tents,catering_baking,sound_dj,decor"', false);
    }

    public function test_helper_derives_decor_and_photography_guidance_from_active_capabilities_only(): void
    {
        $business = $this->makeBusiness('Helper Creative Services');
        $user = User::factory()->create();
        $business->users()->attach($user->id, [
            'role' => 'owner',
            'primary_niche' => 'mixed',
        ]);

        foreach ([
            ['name' => 'Wedding styling', 'category' => 'Decor', 'capability_type' => 'service', 'is_active' => true],
            ['name' => 'Event photography', 'category' => 'Photography & video', 'capability_type' => 'service', 'is_active' => true],
            ['name' => 'Archived catering', 'category' => 'Catering', 'capability_type' => 'service', 'is_active' => false],
        ] as $offering) {
            BusinessCapability::create($offering + [
                'business_id' => $business->id,
                'pricing_basis' => 'custom',
                'currency' => 'ZAR',
            ]);
        }

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-zazu-helper-focus="mixed"', false)
            ->assertSee('data-zazu-helper-focuses="decor,photography"', false)
            ->assertSee('Keep styling scope, materials, setup plans, venue coordination and collection connected to each booking.')
            ->assertSee('Keep coverage, shot lists, crew, equipment, venue timing and final delivery connected to each booking.')
            ->assertDontSee('Keep menus, quantities, dietary details, purchasing, preparation and delivery connected to each booking.');
    }

    public function test_helper_uses_saved_decor_photography_and_transport_job_components(): void
    {
        $business = $this->makeBusiness('Helper Specialist Job Mix');
        $user = User::factory()->create();
        $business->users()->attach($user->id, [
            'role' => 'owner',
            'primary_niche' => 'mixed',
        ]);

        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'HELPER-SPECIALIST-'.Str::upper(Str::random(5)),
            'name' => 'Creative production booking',
            'status' => 'confirmed',
        ]);

        foreach ([
            ['description' => 'Reception styling', 'category' => 'Decor'],
            ['description' => 'Event photo coverage', 'category' => 'Photography & video'],
            ['description' => 'Guest shuttle service', 'category' => 'Transport'],
        ] as $component) {
            EventRequirement::create([
                'event_id' => $event->id,
                'description' => $component['description'],
                'category' => $component['category'],
                'quantity' => '1.00',
                'unit' => 'service',
                'status' => 'open',
            ]);
        }

        $this->actingAs($user)
            ->get(route('work.show', $event))
            ->assertOk()
            ->assertSee('data-zazu-helper-focuses="decor,photography,transport"', false)
            ->assertSee('Reception styling')
            ->assertSee('Event photo coverage')
            ->assertSee('Guest shuttle service')
            ->assertSee('For this booking, review the styling brief, materials, setup timing, venue access and collection.')
            ->assertSee('For this booking, review coverage, shot list, crew, equipment, venue timing and final delivery.')
            ->assertSee('For this booking, review vehicle capacity, route, loading, delivery window and collection.')
            ->assertDontSee('For this booking, review menu quantities, dietary details, purchasing, preparation and delivery.');
    }

    public function test_deactivating_a_capability_updates_catalogue_and_business_helper_but_preserves_saved_job_context(): void
    {
        $business = $this->makeBusiness('Helper Capability Deactivation');
        $owner = User::factory()->create();
        $business->users()->attach($owner->id, [
            'role' => 'owner',
            'primary_niche' => 'mixed',
        ]);

        $capability = BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'Wedding styling',
            'category' => 'Decor',
            'capability_type' => 'service',
            'pricing_basis' => 'custom',
            'currency' => 'ZAR',
            'is_active' => true,
        ]);

        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'HELPER-DEACTIVATE-'.Str::upper(Str::random(5)),
            'name' => 'Existing styling booking',
            'status' => 'confirmed',
        ]);

        EventRequirement::create([
            'event_id' => $event->id,
            'description' => 'Wedding styling',
            'category' => 'Decor',
            'quantity' => '1.00',
            'unit' => 'service',
            'status' => 'open',
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-zazu-helper-focuses="decor"', false)
            ->assertSee('Keep styling scope, materials, setup plans, venue coordination and collection connected to each booking.');

        $this->actingAs($owner)
            ->put(route('capabilities.update', $capability), [
                'name' => 'Wedding styling',
                'category' => 'Decor',
                'capability_type' => 'service',
                'pricing_basis' => 'custom',
                'currency' => 'ZAR',
                'is_active' => '0',
            ])
            ->assertRedirect(route('capabilities.index'));

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-zazu-helper-focuses="mixed"', false)
            ->assertDontSee('Keep styling scope, materials, setup plans, venue coordination and collection connected to each booking.');

        $this->actingAs($owner)
            ->get(route('capabilities.index'))
            ->assertOk()
            ->assertSee('Wedding styling')
            ->assertSee('HIDDEN');

        config(['zazu.permissions.roles.staff' => ['dashboard.view', 'capabilities.view']]);
        $staff = User::factory()->create();
        $business->users()->attach($staff->id, ['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('capabilities.index'))
            ->assertOk()
            ->assertDontSee('Wedding styling');

        $this->actingAs($owner)
            ->get(route('work.show', $event))
            ->assertOk()
            ->assertSee('data-zazu-helper-focuses="decor"', false)
            ->assertSee('Wedding styling')
            ->assertSee('For this booking, review the styling brief, materials, setup timing, venue access and collection.');

        $this->assertDatabaseHas('event_requirements', [
            'event_id' => $event->id,
            'description' => 'Wedding styling',
            'category' => 'Decor',
        ]);
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
        $assetTag = $this->createAllocatedHelperAsset($business, $event);

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
            ->assertSee('data-zazu-helper-event-records="4"', false)
            ->assertSee('1 preparation item')
            ->assertSee('1 purchase order')
            ->assertSee('href="'.route('work.preparation.index', $event).'"', false)
            ->assertSee('Review preparation')
            ->assertSee('href="'.route('purchasing.index', ['event_id' => $event->id]).'"', false)
            ->assertSee('Review purchasing')
            ->assertSee('data-zazu-helper-event-record-link', false)
            ->assertSee('data-zazu-helper-event-operational-details="4"', false)
            ->assertSee('Current operational state')
            ->assertSee('Prepare tent hardware')
            ->assertSee('Blocked')
            ->assertSee($purchaseOrderReference)
            ->assertSee('Sent')
            ->assertSee('Confirm equipment supplier cost')
            ->assertSee('Planned')
            ->assertSee($assetTag)
            ->assertSee('Allocated')
            ->assertDontSee('Catering preparation')
            ->assertDontSee('Camera editing');
    }

    private function createAllocatedHelperAsset(Business $business, Event $event): string
    {
        $assetTag = 'HELPER-ASSET-'.Str::upper(Str::random(5));
        $asset = Asset::create([
            'business_id' => $business->id,
            'asset_tag' => $assetTag,
            'name' => 'Stretch tent asset',
            'status' => 'allocated',
            'condition' => 'good',
            'purchase_cost' => '1500.00',
            'currency' => 'ZAR',
        ]);

        AssetAllocation::create([
            'business_id' => $business->id,
            'asset_id' => $asset->id,
            'event_id' => $event->id,
            'allocated_from' => now()->toDateString(),
            'allocated_until' => now()->addDay()->toDateString(),
            'status' => 'allocated',
        ]);

        return $assetTag;
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
