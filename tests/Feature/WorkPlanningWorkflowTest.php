<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\EventPreparationItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkPlanningWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function event(): Event
    {
        $business = Business::create([
            'name' => 'Planning Test Business',
            'slug' => 'planning-test-'.Str::lower(Str::random(8)),
        ]);

        return Event::create([
            'business_id' => $business->id,
            'reference' => 'ZAZ-PLAN-001',
            'name' => 'Wedding Preparation Test',
            'event_date' => '2026-11-15',
            'status' => 'draft',
        ]);
    }

    public function test_projected_and_actual_costs_are_saved_against_the_work_and_business(): void
    {
        $event = $this->event();

        $response = $this->post(route('work.costs.store', $event), [
            'category' => 'Transport',
            'description' => 'Delivery vehicle',
            'currency' => 'zar',
            'projected_amount' => '1500.00',
            'actual_amount' => '1650.00',
            'status' => 'incurred',
            'notes' => 'Final fuel and tolls',
        ]);

        $response->assertRedirect(route('work.costs.index', $event));
        $response->assertSessionHas('success', 'Cost record saved.');

        $cost = EventCost::query()->firstOrFail();

        $this->assertSame($event->business_id, $cost->business_id);
        $this->assertSame($event->id, $cost->event_id);
        $this->assertSame('ZAR', $cost->currency);
        $this->assertSame('1500.00', $cost->projected_amount);
        $this->assertSame('1650.00', $cost->actual_amount);
    }

    public function test_preparation_item_status_records_readiness_and_can_be_changed(): void
    {
        $event = $this->event();

        $response = $this->post(route('work.preparation.store', $event), [
            'title' => 'Pack 80 chairs',
            'category' => 'Equipment',
            'quantity' => '80',
            'unit' => 'chairs',
            'status' => 'open',
            'due_date' => '2026-11-13',
            'notes' => 'Confirm count before loading.',
        ]);

        $response->assertRedirect(route('work.preparation.index', $event));
        $response->assertSessionHas('success', 'Preparation item added.');

        $item = EventPreparationItem::query()->firstOrFail();

        $this->assertSame($event->business_id, $item->business_id);
        $this->assertSame($event->id, $item->event_id);
        $this->assertNull($item->completed_at);

        $response = $this->patch(route('work.preparation.status', [$event, $item]), [
            'status' => 'ready',
        ]);

        $response->assertRedirect(route('work.preparation.index', $event));
        $response->assertSessionHas('success', 'Preparation status updated.');

        $item->refresh();

        $this->assertSame('ready', $item->status);
        $this->assertNotNull($item->completed_at);
    }

    public function test_preparation_item_from_another_work_cannot_be_relabelled_through_work_route(): void
    {
        $first = $this->event();
        $second = $this->event();

        $item = EventPreparationItem::create([
            'business_id' => $first->business_id,
            'event_id' => $first->id,
            'title' => 'Private preparation item',
            'status' => 'open',
        ]);

        $response = $this->patch(route('work.preparation.status', [$second, $item]), [
            'status' => 'ready',
        ]);

        $response->assertNotFound();

        $this->assertSame('open', $item->fresh()->status);
    }
}
