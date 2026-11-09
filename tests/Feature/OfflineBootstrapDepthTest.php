<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfflineBootstrapDepthTest extends TestCase
{
    use RefreshDatabase;

    public function test_phone_bootstrap_includes_inventory_assets_and_operational_costs(): void
    {
        $business = Business::create([
            'name' => 'Bootstrap Depth Business',
            'slug' => 'bootstrap-depth-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $user = User::factory()->create(['username' => 'bootstrapdepth']);
        $business->users()->attach($user->id, ['role' => 'owner']);

        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'BOOT-001',
            'name' => 'Bootstrap Job',
            'status' => 'confirmed',
        ]);

        $inventory = InventoryItem::create([
            'business_id' => $business->id,
            'name' => 'Plates',
            'unit' => 'unit',
            'reorder_level' => '10.00',
        ]);

        $asset = Asset::create([
            'business_id' => $business->id,
            'asset_tag' => 'BOOT-ASSET-001',
            'name' => 'Tent',
            'status' => 'available',
            'condition' => 'good',
            'purchase_cost' => '1000.00',
            'currency' => 'ZAR',
        ]);

        EventCost::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'category' => 'Food',
            'description' => 'Bootstrap cost',
            'currency' => 'ZAR',
            'projected_amount' => '100.00',
            'status' => 'planned',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['zazu_business_id' => $business->id])
            ->get(route('offline.bootstrap'))
            ->assertOk();

        $response->assertJsonStructure([
            'inventory_items',
            'assets',
            'costs',
        ]);

        $this->assertSame($inventory->id, $response->json('inventory_items.0.id'));
        $this->assertSame($asset->id, $response->json('assets.0.id'));
        $this->assertSame('Bootstrap cost', $response->json('costs.0.description'));
    }

    public function test_phone_bootstrap_never_includes_another_business_records(): void
    {
        $ownerBusiness = Business::create([
            'name' => 'Owner Bootstrap Business',
            'slug' => 'owner-bootstrap-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $foreignBusiness = Business::create([
            'name' => 'Foreign Bootstrap Business',
            'slug' => 'foreign-bootstrap-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $user = User::factory()->create(['username' => 'bootstrapowner']);
        $ownerBusiness->users()->attach($user->id, ['role' => 'owner']);

        Customer::create([
            'business_id' => $foreignBusiness->id,
            'name' => 'Foreign Customer Must Not Leak',
        ]);
        Event::create([
            'business_id' => $foreignBusiness->id,
            'reference' => 'FOREIGN-BOOT-001',
            'name' => 'Foreign Event Must Not Leak',
            'status' => 'confirmed',
        ]);
        InventoryItem::create([
            'business_id' => $foreignBusiness->id,
            'name' => 'Foreign Stock Must Not Leak',
            'unit' => 'unit',
            'reorder_level' => '1.00',
        ]);
        Asset::create([
            'business_id' => $foreignBusiness->id,
            'asset_tag' => 'FOREIGN-BOOT-ASSET',
            'name' => 'Foreign Asset Must Not Leak',
            'status' => 'available',
            'condition' => 'good',
            'purchase_cost' => '100.00',
            'currency' => 'ZAR',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['zazu_business_id' => $ownerBusiness->id])
            ->get(route('offline.bootstrap'))
            ->assertOk();

        foreach (['customers', 'events', 'inventory_items', 'assets', 'costs'] as $collection) {
            $this->assertSame([], $response->json($collection), "Unexpected foreign records in {$collection}.");
        }

        $this->assertStringNotContainsString('Foreign ', $response->getContent());
        $this->assertSame($ownerBusiness->id, $response->json('business.id'));
    }

}
