<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantSecurityInvalidStateTest extends TestCase
{
    use RefreshDatabase;

    private function business(string $name): Business
    {
        return Business::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
    }

    private function ownerFor(Business $business): User
    {
        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        return $user;
    }

    public function test_foreign_customer_is_not_visible_by_direct_id(): void
    {
        $own = $this->business('Own Customer Workspace');
        $foreign = $this->business('Foreign Customer Workspace');
        $user = $this->ownerFor($own);

        $customer = Model::withoutEvents(fn () => Customer::create([
            'business_id' => $foreign->id,
            'name' => 'Foreign Customer Secret',
        ]));

        $this->actingAs($user)->get(route('customers.show', $customer))
            ->assertNotFound()->assertDontSee('Foreign Customer Secret');
    }

    public function test_foreign_purchase_order_is_not_visible_by_direct_id(): void
    {
        $own = $this->business('Own Purchasing Workspace');
        $foreign = $this->business('Foreign Purchasing Workspace');
        $user = $this->ownerFor($own);

        $supplier = Model::withoutEvents(fn () => Supplier::create([
            'business_id' => $foreign->id, 'name' => 'Foreign Supplier Secret',
        ]));

        $order = Model::withoutEvents(fn () => PurchaseOrder::create([
            'business_id' => $foreign->id, 'supplier_id' => $supplier->id,
            'reference' => 'PO-FOREIGN-SECRET', 'status' => 'ordered',
            'currency' => 'ZAR', 'total_amount' => '500.00',
            'ordered_at' => now()->toDateString(),
        ]));

        $this->actingAs($user)->get(route('purchasing.show', $order))
            ->assertNotFound()->assertDontSee('PO-FOREIGN-SECRET');
    }

    public function test_foreign_invoice_is_not_visible_by_direct_id(): void
    {
        $own = $this->business('Own Finance Workspace');
        $foreign = $this->business('Foreign Finance Workspace');
        $user = $this->ownerFor($own);

        $invoice = Model::withoutEvents(fn () => Invoice::create([
            'business_id' => $foreign->id, 'number' => 'INV-FOREIGN-SECRET',
            'status' => 'issued', 'currency' => 'ZAR', 'subtotal' => '900.00',
            'tax_total' => '0.00', 'total' => '900.00',
            'issued_at' => now()->toDateString(),
        ]));

        $this->actingAs($user)->get(route('finance.invoices.show', $invoice))
            ->assertNotFound()->assertDontSee('INV-FOREIGN-SECRET');
    }

    public function test_foreign_asset_is_not_visible_by_direct_id(): void
    {
        $own = $this->business('Own Asset Workspace');
        $foreign = $this->business('Foreign Asset Workspace');
        $user = $this->ownerFor($own);

        $asset = Model::withoutEvents(fn () => Asset::create([
            'business_id' => $foreign->id, 'asset_tag' => 'AST-FOREIGN-SECRET',
            'name' => 'Foreign Asset Secret', 'status' => 'available',
            'condition' => 'good', 'location' => 'Foreign Depot',
            'purchase_cost' => '5000.00', 'currency' => 'ZAR',
        ]));

        $this->actingAs($user)->get(route('assets.edit', $asset))
            ->assertNotFound()->assertDontSee('Foreign Asset Secret');
    }

    public function test_foreign_work_is_not_visible_by_direct_id(): void
    {
        $own = $this->business('Own Work Workspace');
        $foreign = $this->business('Foreign Work Workspace');
        $user = $this->ownerFor($own);

        $event = Model::withoutEvents(fn () => \App\Models\Event::create([
            'business_id' => $foreign->id, 'reference' => 'EVT-FOREIGN-SECRET',
            'name' => 'Foreign Work Secret', 'event_type' => 'Catering',
            'event_date' => now()->addDays(7)->toDateString(), 'status' => 'draft',
        ]));

        $this->actingAs($user)->get(route('work.show', $event))
            ->assertNotFound()->assertDontSee('Foreign Work Secret');
    }

    public function test_receiving_an_already_received_order_changes_nothing(): void
    {
        $business = $this->business('Receiving State Workspace');
        $user = $this->ownerFor($business);
        $supplier = Supplier::create(['business_id' => $business->id, 'name' => 'State Supplier']);

        $order = PurchaseOrder::create([
            'business_id' => $business->id, 'supplier_id' => $supplier->id,
            'reference' => 'PO-STATE-001', 'status' => 'received',
            'currency' => 'ZAR', 'total_amount' => '500.00',
            'ordered_at' => now()->toDateString(),
        ]);
        $line = PurchaseOrderItem::create([
            'business_id' => $business->id, 'purchase_order_id' => $order->id,
            'description' => 'Already received goods', 'quantity' => '10.00',
            'received_quantity' => '10.00', 'unit' => 'unit',
            'unit_price' => '50.00', 'line_total' => '500.00',
        ]);

        $this->actingAs($user)->post(route('purchasing.receive', $order), [
            'idempotency_key' => Str::uuid()->toString(),
            'received_quantity' => [$line->id => '1.00'],
        ])->assertStatus(422);

        $this->assertDatabaseCount('purchase_order_receipts', 0);
        $this->assertDatabaseCount('inventory_movements', 0);
        $this->assertSame('10.00', $line->fresh()->received_quantity);
    }

    public function test_receiving_more_than_ordered_rolls_back_the_receipt(): void
    {
        $business = $this->business('Over Receipt Workspace');
        $user = $this->ownerFor($business);
        $supplier = Supplier::create(['business_id' => $business->id, 'name' => 'Over Receipt Supplier']);

        $order = PurchaseOrder::create([
            'business_id' => $business->id, 'supplier_id' => $supplier->id,
            'reference' => 'PO-OVER-001', 'status' => 'ordered',
            'currency' => 'ZAR', 'total_amount' => '500.00',
            'ordered_at' => now()->toDateString(),
        ]);
        $line = PurchaseOrderItem::create([
            'business_id' => $business->id, 'purchase_order_id' => $order->id,
            'description' => 'Limited goods', 'quantity' => '10.00',
            'received_quantity' => '8.00', 'unit' => 'unit',
            'unit_price' => '50.00', 'line_total' => '500.00',
        ]);

        $this->actingAs($user)->post(route('purchasing.receive', $order), [
            'idempotency_key' => Str::uuid()->toString(),
            'received_quantity' => [$line->id => '3.00'],
        ])->assertStatus(422);

        $this->assertDatabaseCount('purchase_order_receipts', 0);
        $this->assertDatabaseCount('inventory_movements', 0);
        $this->assertSame('8.00', $line->fresh()->received_quantity);
        $this->assertSame('ordered', $order->fresh()->status);
    }

    public function test_payment_cannot_exceed_outstanding_balance_and_does_not_post(): void
    {
        $business = $this->business('Overpayment Workspace');
        $user = $this->ownerFor($business);

        $invoice = Invoice::create([
            'business_id' => $business->id, 'number' => 'INV-OVER-001',
            'status' => 'issued', 'currency' => 'ZAR',
            'subtotal' => '1000.00', 'tax_total' => '0.00',
            'total' => '1000.00', 'issued_at' => now()->toDateString(),
        ]);

        \App\Models\Payment::create([
            'business_id' => $business->id, 'invoice_id' => $invoice->id,
            'amount' => '800.00', 'currency' => 'ZAR',
            'method' => 'bank_transfer', 'paid_at' => now()->toDateString(),
        ]);

        $this->actingAs($user)->post(route('finance.payments.store'), [
            'invoice_id' => $invoice->id, 'amount' => '250.00',
            'method' => 'bank_transfer', 'paid_at' => now()->toDateString(),
            'idempotency_key' => Str::uuid()->toString(),
        ])->assertStatus(422);

        $this->assertDatabaseCount('payments', 1);
        $this->assertSame('issued', $invoice->fresh()->status);
    }
}
