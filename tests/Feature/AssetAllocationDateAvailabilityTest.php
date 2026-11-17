<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Business;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetAllocationDateAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_asset_can_be_allocated_to_two_non_overlapping_event_periods(): void
    {
        $business = Business::create([
            'name' => 'Rental Date Availability',
            'slug' => 'rental-date-availability',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $user = User::factory()->create(['username' => 'rentalowner']);
        $business->users()->attach($user->id, ['role' => 'owner']);
        $this->actingAs($user);

        $asset = Asset::create([
            'business_id' => $business->id,
            'asset_tag' => 'CHAIR-001',
            'name' => 'Event Chair',
            'status' => 'available',
            'condition' => 'good',
            'purchase_cost' => '100.00',
            'currency' => 'ZAR',
        ]);

        $firstEvent = Event::create([
            'business_id' => $business->id,
            'reference' => 'RENTAL-001',
            'name' => 'First Rental',
            'event_date' => '2026-12-01',
            'customer_name' => 'First Customer',
            'status' => 'draft',
        ]);
        $secondEvent = Event::create([
            'business_id' => $business->id,
            'reference' => 'RENTAL-002',
            'name' => 'Second Rental',
            'event_date' => '2026-12-10',
            'customer_name' => 'Second Customer',
            'status' => 'draft',
        ]);

        $this->post(route('assets.allocate', $asset), [
            'event_id' => $firstEvent->id,
            'allocated_from' => '2026-12-01',
            'allocated_until' => '2026-12-02',
        ])->assertRedirect();

        $this->post(route('assets.allocate', $asset), [
            'event_id' => $secondEvent->id,
            'allocated_from' => '2026-12-10',
            'allocated_until' => '2026-12-11',
        ])->assertRedirect();

        $this->assertDatabaseCount('asset_allocations', 2);
        $this->assertDatabaseHas('asset_allocations', [
            'asset_id' => $asset->id,
            'event_id' => $secondEvent->id,
            'status' => 'allocated',
        ]);
    }
}
