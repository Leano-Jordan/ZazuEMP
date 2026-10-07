<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Business;
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
}
