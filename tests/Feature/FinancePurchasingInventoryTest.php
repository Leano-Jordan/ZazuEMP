<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\User;
use App\Support\CurrentBusiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancePurchasingInventoryTest extends TestCase
{
    use RefreshDatabase;

    private function businessUser(): array
    {
        $business = Business::create([
            'name' => 'Transaction Business',
            'slug' => 'transaction-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create(['username' => 'transactionowner']);
        $business->users()->attach($user->id, ['role' => 'owner']);

        return [$business, $user];
    }

    public function test_purchase_order_receipt_creates_stock_movement(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $supplier = \App\Models\Supplier::create([
            'business_id' => $business->id,
            'name' => 'Fresh Foods',
        ]);

        $response = $this->post(route('purchasing.store'), [
            'supplier_id' => $supplier->id,
            'currency' => 'ZAR',
            'description' => ['Chicken'],
            'quantity' => ['20'],
            'unit' => ['kg'],
            'unit_price' => ['85.00'],
            'capability_id' => [''],
        ]);

        $order = \App\Models\PurchaseOrder::firstOrFail();
        $response->assertRedirect(route('purchasing.show', $order));

        $this->patch(route('purchasing.status', $order), ['status' => 'received'])
            ->assertRedirect();

        $item = \App\Models\InventoryItem::where('business_id', $business->id)->where('name', 'Chicken')->firstOrFail();

        $this->assertSame(20.0, $item->on_hand);
        $this->assertDatabaseHas('inventory_movements', [
            'business_id' => $business->id,
            'inventory_item_id' => $item->id,
            'purchase_order_id' => $order->id,
            'type' => 'receipt',
        ]);
    }

    public function test_purchase_order_rejects_a_foreign_business_capability(): void
    {
        [$business, $user] = $this->businessUser();
        $otherBusiness = Business::create([
            'name' => 'Capability Owner',
            'slug' => 'capability-owner',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $foreignCapability = \App\Models\BusinessCapability::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Foreign Ingredient',
            'category' => 'Catering',
            'capability_type' => 'product',
            'pricing_basis' => 'per_unit',
            'default_unit' => 'kg',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $this->post(route('purchasing.store'), [
            'supplier_id' => \App\Models\Supplier::create([
                'business_id' => $business->id,
                'name' => 'Local Supplier',
            ])->id,
            'currency' => 'ZAR',
            'description' => ['Foreign Ingredient'],
            'quantity' => ['10'],
            'unit' => ['kg'],
            'unit_price' => ['20.00'],
            'capability_id' => [(string) $foreignCapability->id],
        ])->assertNotFound();

        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_inventory_cannot_be_issued_below_zero(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $item = \App\Models\InventoryItem::create([
            'business_id' => $business->id,
            'name' => 'Plates',
            'unit' => 'plate',
            'reorder_level' => 5,
        ]);

        $this->from(route('inventory.index'))
            ->post(route('inventory.movement', $item), [
                'type' => 'issue',
                'quantity' => 1,
                'unit_cost' => 0,
                'movement_date' => now()->toDateString(),
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_payment_updates_invoice_status_and_cannot_overpay(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $invoice = Invoice::create([
            'business_id' => $business->id,
            'number' => 'INV-TEST-001',
            'status' => 'issued',
            'currency' => 'ZAR',
            'subtotal' => '1000.00',
            'tax_total' => '0.00',
            'total' => '1000.00',
            'issued_at' => now()->toDateString(),
            'due_at' => now()->addDays(7)->toDateString(),
        ]);

        $this->post(route('finance.payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => '600.00',
            'method' => 'bank_transfer',
            'paid_at' => now()->toDateString(),
        ])->assertRedirect(route('finance.index'));

        $this->assertSame('issued', $invoice->fresh()->status);

        $this->post(route('finance.payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => '400.00',
            'method' => 'bank_transfer',
            'paid_at' => now()->toDateString(),
        ])->assertRedirect(route('finance.index'));

        $this->assertSame('paid', $invoice->fresh()->status);

        $this->from(route('finance.index'))
            ->post(route('finance.payments.store'), [
                'invoice_id' => $invoice->id,
                'amount' => '1.00',
                'method' => 'cash',
                'paid_at' => now()->toDateString(),
            ])
            ->assertStatus(422);
    }

    public function test_asset_allocation_is_business_scoped(): void
    {
        [$business, $user] = $this->businessUser();
        $otherBusiness = Business::create([
            'name' => 'Other Business',
            'slug' => 'other-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->actingAs($user);

        $asset = \App\Models\Asset::create([
            'business_id' => $otherBusiness->id,
            'asset_tag' => 'OTHER-001',
            'name' => 'Other Tent',
            'status' => 'available',
            'condition' => 'good',
            'purchase_cost' => '0.00',
            'currency' => 'ZAR',
        ]);

        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'EVT-TEST-001',
            'name' => 'Test Event',
            'customer_name' => 'Test Customer',
            'status' => 'draft',
        ]);

        $this->post(route('assets.allocate', $asset), [
            'event_id' => $event->id,
            'allocated_from' => now()->toDateString(),
        ])->assertNotFound();

        $this->assertDatabaseCount('asset_allocations', 0);
    }
}
