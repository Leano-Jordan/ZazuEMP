<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BusinessCapabilityCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogue_composes_rental_services_and_products_without_catering_only_clutter(): void
    {
        $business = $this->makeBusiness('Mixed Event Provider');
        $owner = User::factory()->create();
        $business->users()->attach($owner->id, ['role' => 'owner']);

        $this->createCapability($business, 'Speaker and sound hire', 'Audio & entertainment', 'rental');
        $this->createCapability($business, 'DJ performance', 'Sound & entertainment', 'service');
        $this->createCapability($business, 'LED dance floor', 'Event production', 'product');
        $this->createCapability($business, 'Archived camera hire', 'Photography', 'rental', false);

        $this->actingAs($owner)
            ->get(route('capabilities.index'))
            ->assertOk()
            ->assertSee('Hire &amp; rental', false)
            ->assertSee('Services &amp; products')
            ->assertSee('Speaker and sound hire')
            ->assertSee('DJ performance')
            ->assertSee('LED dance floor')
            ->assertSee('Archived camera hire')
            ->assertSee('HIDDEN')
            ->assertDontSee('Catering &amp; Food Packages', false)
            ->assertDontSee('Dietary profile not recorded');
    }

    public function test_service_only_business_does_not_receive_an_empty_rental_stream(): void
    {
        $business = $this->makeBusiness('DJ Specialist');
        $owner = User::factory()->create();
        $business->users()->attach($owner->id, ['role' => 'owner']);

        $this->createCapability($business, 'DJ performance', 'Sound & entertainment', 'service');
        $this->createCapability($business, 'Lighting package', 'Event production', 'package');

        $response = $this->actingAs($owner)->get(route('capabilities.index'));

        $response->assertOk()
            ->assertSee('Services &amp; products')
            ->assertSee('DJ performance')
            ->assertSee('Lighting package')
            ->assertDontSee('catalogue-panel-equipment', false)
            ->assertDontSee('Hire &amp; rental', false);
    }

    public function test_catalogue_hides_asset_allocation_action_without_asset_permission(): void
    {
        config(['zazu.permissions.roles.staff' => ['dashboard.view', 'capabilities.view']]);

        $business = $this->makeBusiness('Catalogue Staff Business');
        $staff = User::factory()->create();
        $business->users()->attach($staff->id, ['role' => 'staff']);

        $this->createCapability($business, 'Banquet chairs', 'Furniture & equipment', 'rental');
        $this->createCapability($business, 'Archived camera hire', 'Photography', 'rental', false);

        $response = $this->actingAs($staff)->get(route('capabilities.index'));

        $response->assertOk()
            ->assertSee('Banquet chairs')
            ->assertDontSee('Archived camera hire')
            ->assertDontSee('Photography')
            ->assertDontSee('Allocate');
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

    private function createCapability(Business $business, string $name, string $category, string $type, bool $active = true): BusinessCapability
    {
        return BusinessCapability::create([
            'business_id' => $business->id,
            'name' => $name,
            'category' => $category,
            'capability_type' => $type,
            'pricing_basis' => 'per_unit',
            'default_price' => '250.00',
            'currency' => 'ZAR',
            'default_unit' => 'unit',
            'is_active' => $active,
        ]);
    }
}
