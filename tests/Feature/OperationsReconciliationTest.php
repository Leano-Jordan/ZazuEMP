<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\InventoryMovement;
use App\Models\PurchaseOrder;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OperationsReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_populated_purchase_order_receipt_creates_inventory_and_event_cost_trace(): void
    {
        $this->seed(DemoScenarioSeeder::class);

        $user = \App\Models\User::query()->where('email', 'demo@zazu.local')->firstOrFail();
        $business = $user->businesses()->where('slug', 'zazu-demo-catering')->firstOrFail();
        $event = Event::query()->where('business_id', $business->id)->where('reference', 'ZAZU-DEMO-001')->firstOrFail();
        $order = PurchaseOrder::query()
            ->where('business_id', $business->id)
            ->where('reference', 'PO-ZAZU-DEMO-001')
            ->with('items')
            ->firstOrFail();

        $this->actingAs($user)->withSession(['zazu_business_id' => $business->id]);

        $order->update(['status' => 'sent']);

        $this->patch(route('purchasing.status', $order), [
            'status' => 'ordered',
        ])->assertRedirect();
        $item = $order->items->firstOrFail();
        $item->update(['received_quantity' => '0.00']);

        $this->post(route('purchasing.receive', $order), [
            'idempotency_key' => (string) Str::uuid(),
            'received_quantity' => [$item->id => '1.00'],
        ])->assertRedirect();

        $order->refresh();
        $item->refresh();

        $this->assertSame('received', $order->status);
        $this->assertSame('1.00', (string) $item->received_quantity);

        $receipt = InventoryMovement::query()
            ->where('business_id', $business->id)
            ->where('purchase_order_id', $order->id)
            ->where('purchase_order_item_id', $item->id)
            ->where('type', 'receipt')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('1.00', (string) $receipt->quantity);
        $this->assertSame('2800.00', (string) $receipt->unit_cost);
        $this->assertSame($event->id, $receipt->event_id);

        $this->post(route('work.costs.store', $event), [
            'category' => 'Food',
            'description' => 'Fresh catering supplies received',
            'currency' => 'ZAR',
            'projected_amount' => '2800.00',
            'actual_amount' => '2800.00',
            'status' => 'incurred',
            'notes' => 'Reconciled to PO-ZAZU-DEMO-001 receipt.',
        ])->assertRedirect();

        $event->refresh();

        $this->assertSame(
            '2800.00',
            number_format(
                (float) $event->costs()->where('status', 'incurred')->sum('actual_amount'),
                2,
                '.',
                ''
            )
        );

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'action' => 'purchasing.order.received',
            'subject_id' => $order->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'action' => 'inventory.movement.recorded',
            'subject_id' => $receipt->id,
        ]);
    }
}
