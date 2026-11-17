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

    public function test_same_asset_can_be_allocated_for_non_overlapping_periods_and_released_independently(): void
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
        $overlappingEvent = Event::create([
            'business_id' => $business->id,
            'reference' => 'RENTAL-003',
            'name' => 'Overlapping Rental',
            'event_date' => '2026-12-02',
            'customer_name' => 'Third Customer',
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

        $this->from(route('assets.index'))->post(route('assets.allocate', $asset), [
            'event_id' => $overlappingEvent->id,
            'allocated_from' => '2026-12-02',
            'allocated_until' => '2026-12-10',
        ])->assertStatus(422);

        $this->assertDatabaseCount('asset_allocations', 2);
        $allocations = $asset->allocations()->orderBy('id')->get();

        $this->post(route('assets.release', $asset), ['allocation_id' => $allocations[0]->id])->assertRedirect();
        $this->assertSame('returned', $allocations[0]->fresh()->status);
        $this->assertSame('allocated', $asset->fresh()->status);

        $this->post(route('assets.release', $asset), ['allocation_id' => $allocations[1]->id])->assertRedirect();
        $this->assertSame('returned', $allocations[1]->fresh()->status);
        $this->assertSame('available', $asset->fresh()->status);


    }
    public function test_open_ended_asset_booking_blocks_a_later_reservation(): void
    {
        $business = Business::create([
            'name' => 'Open-ended Rental Availability',
            'slug' => 'open-ended-rental-availability',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $user = User::factory()->create(['username' => 'openendedowner']);
        $business->users()->attach($user->id, ['role' => 'owner']);
        $this->actingAs($user);

        $asset = Asset::create([
            'business_id' => $business->id,
            'asset_tag' => 'CHAIR-OPEN-001',
            'name' => 'Open-ended Chair',
            'status' => 'available',
            'condition' => 'good',
            'purchase_cost' => '100.00',
            'currency' => 'ZAR',
        ]);
        $futureEvent = Event::create([
            'business_id' => $business->id,
            'reference' => 'RENTAL-004',
            'name' => 'Later Rental',
            'event_date' => '2026-12-25',
            'customer_name' => 'Fourth Customer',
            'status' => 'draft',
        ]);
        $openEndedEvent = Event::create([
            'business_id' => $business->id,
            'reference' => 'RENTAL-005',
            'name' => 'Open-ended Rental',
            'event_date' => '2026-12-20',
            'customer_name' => 'Fifth Customer',
            'status' => 'draft',
        ]);

        $this->post(route('assets.allocate', $asset), [
            'event_id' => $futureEvent->id,
            'allocated_from' => '2026-12-25',
            'allocated_until' => '2026-12-26',
        ])->assertRedirect();

        $this->from(route('assets.index'))->post(route('assets.allocate', $asset), [
            'event_id' => $openEndedEvent->id,
            'allocated_from' => '2026-12-20',
        ])->assertStatus(422);

        $this->assertDatabaseCount('asset_allocations', 1);
    }

}
