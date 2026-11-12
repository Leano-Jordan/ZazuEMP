<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\EventPreparationItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkCompletionGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsOwner();
    }

    public function test_work_cannot_be_completed_while_preparation_is_open_or_blocked(): void
    {
        foreach (['open', 'blocked'] as $status) {
            $event = $this->eventForCompletion('Preparation Guard Work '.$status);
            EventPreparationItem::create([
                'business_id' => $event->business_id,
                'event_id' => $event->id,
                'title' => 'Confirm final guest count',
                'status' => $status,
            ]);

            $this->put(route('work.update', $event), $this->completionPayload($event))
                ->assertStatus(422);

            $this->assertSame('in_progress', $event->fresh()->status);
        }
    }

    public function test_work_cannot_be_completed_while_a_cost_remains_planned(): void
    {
        $event = $this->eventForCompletion('Planned Cost Completion Guard');
        EventCost::create([
            'business_id' => $event->business_id,
            'event_id' => $event->id,
            'category' => 'Supplier',
            'description' => 'Unconfirmed supplier cost',
            'currency' => 'ZAR',
            'projected_amount' => '500.00',
            'status' => 'planned',
        ]);

        $this->put(route('work.update', $event), $this->completionPayload($event))
            ->assertStatus(422);

        $this->assertSame('in_progress', $event->fresh()->status);
    }

    public function test_work_cannot_be_completed_while_a_linked_purchase_order_is_active(): void
    {
        $event = $this->eventForCompletion('Active Purchase Order Completion Guard');
        $supplier = Supplier::create([
            'business_id' => $event->business_id,
            'name' => 'Completion Guard Supplier',
        ]);
        PurchaseOrder::create([
            'business_id' => $event->business_id,
            'event_id' => $event->id,
            'supplier_id' => $supplier->id,
            'idempotency_key' => (string) Str::uuid(),
            'reference' => 'PO-COMPLETION-GUARD-001',
            'status' => 'ordered',
            'currency' => 'ZAR',
            'total_amount' => '500.00',
        ]);

        $this->put(route('work.update', $event), $this->completionPayload($event))
            ->assertStatus(422);

        $this->assertSame('in_progress', $event->fresh()->status);
    }

    private function eventForCompletion(string $name): Event
    {
        $customer = Customer::create(['name' => $name.' Customer']);

        return Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-COMPLETE-'.Str::upper(Str::random(8)),
            'name' => $name,
            'event_type' => 'Catering order',
            'event_date' => '2026-11-15',
            'status' => 'in_progress',
        ]);
    }

    private function completionPayload(Event $event): array
    {
        return [
            'customer_id' => $event->customer_id,
            'name' => $event->name,
            'event_type' => $event->event_type,
            'event_date' => $event->event_date?->toDateString(),
            'event_address' => $event->event_address,
            'notes' => $event->notes,
            'status' => 'completed',
        ];
    }
}
