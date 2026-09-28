<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Support\CurrentBusiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
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
            ->assertStatus(422);

        $this->patch(route('purchasing.status', $order), ['status' => 'sent'])
            ->assertRedirect();

        $this->patch(route('purchasing.status', $order), ['status' => 'received'])
            ->assertStatus(422);

        $this->patch(route('purchasing.status', $order), ['status' => 'ordered'])
            ->assertRedirect();

        $this->patch(route('purchasing.status', $order), ['status' => 'received'])
            ->assertRedirect();

        $this->patch(route('purchasing.status', $order), ['status' => 'received'])
            ->assertRedirect();

        $this->assertDatabaseCount('inventory_movements', 1);

        $item = \App\Models\InventoryItem::where('business_id', $business->id)->where('name', 'Chicken')->firstOrFail();

        $this->assertSame(20.0, $item->on_hand);
        $this->assertDatabaseHas('inventory_movements', [
            'business_id' => $business->id,
            'inventory_item_id' => $item->id,
            'purchase_order_id' => $order->id,
            'type' => 'receipt',
        ]);
    }

    public function test_purchase_order_supports_partial_receipts_without_over_receiving_or_duplicate_stock(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $supplier = \App\Models\Supplier::create([
            'business_id' => $business->id,
            'name' => 'Partial Receipt Supplier',
        ]);

        $this->post(route('purchasing.store'), [
            'supplier_id' => $supplier->id,
            'currency' => 'ZAR',
            'description' => ['Chairs'],
            'quantity' => ['20'],
            'unit' => ['units'],
            'unit_price' => ['50.00'],
            'capability_id' => [''],
        ])->assertRedirect();

        $order = \App\Models\PurchaseOrder::firstOrFail();

        $this->patch(route('purchasing.status', $order), ['status' => 'sent'])->assertRedirect();
        $this->patch(route('purchasing.status', $order), ['status' => 'ordered'])->assertRedirect();

        $key = (string) Str::uuid();

        $this->post(route('purchasing.receive', $order), [
            'idempotency_key' => $key,
            'received_quantity' => [$order->items()->first()->id => '8'],
        ])->assertRedirect();

        $order->refresh();
        $line = $order->items()->first();

        $this->assertSame('ordered', $order->status);
        $this->assertSame('8.00', $line->received_quantity);
        $this->assertDatabaseHas('inventory_movements', [
            'purchase_order_id' => $order->id,
            'purchase_order_item_id' => $line->id,
            'quantity' => '8.00',
            'type' => 'receipt',
        ]);

        $this->post(route('purchasing.receive', $order), [
            'idempotency_key' => $key,
            'received_quantity' => [$line->id => '8'],
        ])->assertRedirect();

        $this->assertDatabaseCount('inventory_movements', 1);

        $this->post(route('purchasing.receive', $order), [
            'idempotency_key' => (string) Str::uuid(),
            'received_quantity' => [$line->id => '12'],
        ])->assertRedirect();

        $this->assertSame('received', $order->fresh()->status);
        $this->assertSame('20.00', $line->fresh()->received_quantity);
        $this->assertDatabaseCount('inventory_movements', 2);
    }

    public function test_purchase_order_idempotency_prevents_duplicate_supplier_commitment(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $supplier = \App\Models\Supplier::create([
            'business_id' => $business->id,
            'name' => 'Idempotent Supplier',
        ]);

        $payload = [
            'idempotency_key' => (string) Str::uuid(),
            'supplier_id' => $supplier->id,
            'currency' => 'ZAR',
            'description' => ['Chicken'],
            'quantity' => ['10'],
            'unit' => ['kg'],
            'unit_price' => ['85.00'],
            'capability_id' => [''],
        ];

        $this->post(route('purchasing.store'), $payload)->assertRedirect();
        $this->post(route('purchasing.store'), $payload)->assertRedirect();

        $this->assertDatabaseCount('purchase_orders', 1);
        $this->assertDatabaseHas('audit_logs', ['action' => 'purchasing.order.created', 'business_id' => $business->id]);
    }

    public function test_finance_dashboard_totals_include_records_beyond_display_limit(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        foreach (range(1, 11) as $index) {
            \App\Models\Payment::create([
                'business_id' => $business->id,
                'amount' => '100.01',
                'currency' => 'ZAR',
                'method' => 'bank_transfer',
                'paid_at' => now()->toDateString(),
            ]);

            \App\Models\FinanceExpense::create([
                'business_id' => $business->id,
                'description' => 'Expense '.$index,
                'amount' => '50.02',
                'currency' => 'ZAR',
                'expense_date' => now()->toDateString(),
                'status' => 'paid',
            ]);
        }

        $this->get(route('finance.index'))
            ->assertOk()
            ->assertViewHas('paidByCurrency', fn ($totals) => $totals->get('ZAR') === '1100.11')
            ->assertViewHas('expensesByCurrency', fn ($totals) => $totals->get('ZAR') === '550.22');
    }

    public function test_purchase_order_receipt_tracks_each_purchase_order_line_separately(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $supplier = \App\Models\Supplier::create([
            'business_id' => $business->id,
            'name' => 'Duplicate Item Supplier',
        ]);

        $this->post(route('purchasing.store'), [
            'supplier_id' => $supplier->id,
            'currency' => 'ZAR',
            'description' => ['Chicken', 'Chicken'],
            'quantity' => ['10', '20'],
            'unit' => ['kg', 'kg'],
            'unit_price' => ['85.00', '85.00'],
            'capability_id' => ['', ''],
        ])->assertRedirect();

        $order = \App\Models\PurchaseOrder::firstOrFail();

        $this->patch(route('purchasing.status', $order), ['status' => 'sent'])->assertRedirect();
        $this->patch(route('purchasing.status', $order), ['status' => 'ordered'])->assertRedirect();
        $this->patch(route('purchasing.status', $order), ['status' => 'received'])->assertRedirect();
        $this->patch(route('purchasing.status', $order), ['status' => 'received'])->assertRedirect();

        $this->assertDatabaseCount('inventory_movements', 2);
        $this->assertDatabaseCount('purchase_order_items', 2);

        $item = \App\Models\InventoryItem::where('business_id', $business->id)
            ->where('name', 'Chicken')
            ->firstOrFail();

        $this->assertSame(30.0, $item->on_hand);

        $lines = \App\Models\PurchaseOrderItem::where('purchase_order_id', $order->id)
            ->orderBy('id')
            ->get();

        $this->assertDatabaseHas('inventory_movements', [
            'purchase_order_id' => $order->id,
            'purchase_order_item_id' => $lines[0]->id,
            'quantity' => '10.00',
            'type' => 'receipt',
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'purchase_order_id' => $order->id,
            'purchase_order_item_id' => $lines[1]->id,
            'quantity' => '20.00',
            'type' => 'receipt',
        ]);
    }

    public function test_purchase_order_requires_business_currency(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $supplier = \App\Models\Supplier::create([
            'business_id' => $business->id,
            'name' => 'Currency Supplier',
        ]);

        $this->post(route('purchasing.store'), [
            'supplier_id' => $supplier->id,
            'currency' => 'USD',
            'description' => ['Chicken'],
            'quantity' => ['10'],
            'unit' => ['kg'],
            'unit_price' => ['85.00'],
            'capability_id' => [''],
        ])->assertStatus(422);

        $this->assertDatabaseCount('purchase_orders', 0);
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

    public function test_payment_idempotency_prevents_duplicate_financial_posting(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $invoice = Invoice::create([
            'business_id' => $business->id,
            'number' => 'INV-IDEMP-001',
            'status' => 'issued',
            'currency' => 'ZAR',
            'subtotal' => '500.00',
            'tax_total' => '0.00',
            'total' => '500.00',
            'issued_at' => now()->toDateString(),
            'due_at' => now()->addDays(7)->toDateString(),
        ]);

        $payload = [
            'idempotency_key' => (string) Str::uuid(),
            'invoice_id' => $invoice->id,
            'amount' => '250.00',
            'method' => 'bank_transfer',
            'paid_at' => now()->toDateString(),
        ];

        $this->post(route('finance.payments.store'), $payload)->assertRedirect(route('finance.index'));
        $this->post(route('finance.payments.store'), $payload)->assertRedirect(route('finance.index'));

        $this->assertDatabaseCount('payments', 1);
        $this->assertSame('issued', $invoice->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'finance.payment.recorded', 'business_id' => $business->id]);
    }

    public function test_inventory_idempotency_prevents_duplicate_stock_change(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $item = \App\Models\InventoryItem::create([
            'business_id' => $business->id,
            'name' => 'Flour',
            'unit' => 'kg',
            'reorder_level' => 5,
        ]);

        $payload = [
            'idempotency_key' => (string) Str::uuid(),
            'type' => 'receipt',
            'quantity' => '10',
            'unit_cost' => '20.00',
            'movement_date' => now()->toDateString(),
        ];

        $this->post(route('inventory.movement', $item), $payload)->assertRedirect();
        $this->post(route('inventory.movement', $item), $payload)->assertRedirect();

        $this->assertDatabaseCount('inventory_movements', 1);
        $this->assertSame(10.0, $item->fresh()->on_hand);
        $this->assertDatabaseHas('audit_logs', ['action' => 'inventory.movement.recorded', 'business_id' => $business->id]);
    }

    public function test_inventory_fractional_quantities_use_deterministic_hundredths(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $item = \App\Models\InventoryItem::create([
            'business_id' => $business->id,
            'name' => 'Liquid Ingredient',
            'unit' => 'litre',
            'reorder_level' => '0.10',
        ]);

        foreach ([
            ['type' => 'receipt', 'quantity' => '0.10'],
            ['type' => 'receipt', 'quantity' => '0.20'],
            ['type' => 'issue', 'quantity' => '0.10'],
        ] as $movement) {
            $this->post(route('inventory.movement', $item), [
                ...$movement,
                'unit_cost' => '10.00',
                'movement_date' => now()->toDateString(),
            ])->assertRedirect();
        }

        $item = $item->fresh();

        $this->assertSame(20, $item->on_hand_hundredths);
        $this->assertSame(0.2, $item->on_hand);
    }


    public function test_staff_permission_boundary_allows_operational_finance_but_denies_owner_settings(): void
    {
        $business = Business::create([
            'name' => 'Staff Boundary Business',
            'slug' => 'staff-boundary-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $staff = User::factory()->create(['username' => 'boundarystaff']);
        $business->users()->attach($staff->id, ['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('finance.index'))
            ->assertOk();

        $this->actingAs($staff)
            ->get(route('settings.index'))
            ->assertForbidden();
    }

    public function test_asset_details_can_be_updated_and_audit_is_recorded(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $asset = \App\Models\Asset::create([
            'business_id' => $business->id,
            'asset_tag' => 'ASSET-UPDATE-001',
            'name' => 'Tent',
            'status' => 'available',
            'condition' => 'good',
            'location' => 'Depot',
            'purchase_cost' => '3000.00',
            'currency' => 'ZAR',
        ]);

        $this->get(route('assets.edit', $asset))->assertOk();

        $this->put(route('assets.update', $asset), [
            'asset_tag' => 'ASSET-UPDATE-001',
            'name' => 'Tent',
            'condition' => 'damaged',
            'location' => 'Repair area',
            'purchase_cost' => '3000.00',
        ])->assertRedirect(route('assets.index'));

        $asset->refresh();

        $this->assertSame('damaged', $asset->condition);
        $this->assertSame('Repair area', $asset->location);
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'action' => 'assets.updated',
            'subject_id' => $asset->id,
        ]);
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

        $this->actingAs($user)
            ->post(route('assets.allocate', $asset), [
            'event_id' => $event->id,
            'allocated_from' => now()->toDateString(),
        ])->assertNotFound();

        $this->assertDatabaseCount('asset_allocations', 0);
    }

    public function test_invoice_idempotency_prevents_duplicate_invoice_creation(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'EVT-IDEMP-001',
            'name' => 'Invoice Idempotency Event',
            'customer_name' => 'Invoice Customer',
            'status' => 'draft',
        ]);

        $payload = [
            'idempotency_key' => (string) Str::uuid(),
            'event_id' => $event->id,
            'lines' => [[
                'description' => 'Catering service',
                'quantity' => '1',
                'unit' => 'service',
                'unit_price' => '1000.00',
            ]],
        ];

        $this->post(route('finance.invoices.store'), $payload)->assertRedirect();
        $this->post(route('finance.invoices.store'), $payload)->assertRedirect();

        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'finance.invoice.created',
            'business_id' => $business->id,
        ]);
    }

    public function test_expense_idempotency_prevents_duplicate_expense_creation(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $payload = [
            'idempotency_key' => (string) Str::uuid(),
            'description' => 'Fuel',
            'amount' => '250.00',
            'expense_date' => now()->toDateString(),
            'status' => 'paid',
        ];

        $this->post(route('finance.expenses.store'), $payload)->assertRedirect();
        $this->post(route('finance.expenses.store'), $payload)->assertRedirect();

        $this->assertDatabaseCount('finance_expenses', 1);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'finance.expense.recorded',
            'business_id' => $business->id,
        ]);
    }

    public function test_payment_rejects_mixed_currency_invoice_history(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $invoice = Invoice::create([
            'business_id' => $business->id,
            'number' => 'INV-CURRENCY-001',
            'status' => 'issued',
            'currency' => 'ZAR',
            'subtotal' => '100.00',
            'tax_total' => '0.00',
            'total' => '100.00',
            'issued_at' => now()->toDateString(),
        ]);

        Payment::create([
            'business_id' => $business->id,
            'invoice_id' => $invoice->id,
            'amount' => '10.00',
            'currency' => 'USD',
            'method' => 'bank_transfer',
            'paid_at' => now()->toDateString(),
        ]);

        $this->from(route('finance.payments.create'))
            ->post(route('finance.payments.store'), [
                'invoice_id' => $invoice->id,
                'amount' => '20.00',
                'method' => 'bank_transfer',
                'paid_at' => now()->toDateString(),
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertStatus(409);

        $this->assertDatabaseCount('payments', 1);
    }

    public function test_invoice_balance_is_calculated_without_float_rounding(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $invoice = Invoice::create([
            'business_id' => $business->id,
            'number' => 'INV-DECIMAL-001',
            'status' => 'issued',
            'currency' => 'ZAR',
            'subtotal' => '1000000000.03',
            'tax_total' => '0.00',
            'total' => '1000000000.03',
            'issued_at' => now()->toDateString(),
        ]);

        \App\Models\Payment::create([
            'business_id' => $business->id,
            'invoice_id' => $invoice->id,
            'amount' => '0.01',
            'currency' => 'ZAR',
            'method' => 'bank_transfer',
            'paid_at' => now()->toDateString(),
        ]);

        $invoice->refresh();

        $this->assertSame('0.01', $invoice->paid_amount);
        $this->assertSame('1000000000.02', $invoice->balance);
    }
    public function test_deposit_payment_is_reconciled_and_cannot_exceed_quote_requirement(): void
    {
        [$business, $user] = $this->businessUser();
        $this->actingAs($user);

        $customer = \App\Models\Customer::create([
            'business_id' => $business->id,
            'name' => 'Deposit Customer',
        ]);

        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'DEP-001',
            'name' => 'Deposit Event',
            'event_date' => now()->addDays(10)->toDateString(),
            'status' => 'confirmed',
        ]);

        $quote = \App\Models\Quote::create([
            'event_id' => $event->id,
            'reference' => 'QUO-DEP-001',
            'status' => 'accepted',
            'currency' => 'ZAR',
        ]);

        $version = $quote->versions()->create([
            'version' => 1,
            'status' => 'accepted',
            'subtotal' => '1000.00',
            'tax_total' => '150.00',
            'total' => '1150.00',
            'deposit_percent' => '20.00',
            'deposit_amount' => '230.00',
        ]);

        $invoice = Invoice::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'quote_id' => $quote->id,
            'quote_version_id' => $version->id,
            'number' => 'INV-DEP-001',
            'status' => 'issued',
            'currency' => 'ZAR',
            'subtotal' => '1000.00',
            'tax_total' => '150.00',
            'total' => '1150.00',
            'issued_at' => now()->toDateString(),
        ]);

        $this->post(route('finance.payments.store'), [
            'invoice_id' => $invoice->id,
            'type' => 'deposit',
            'amount' => '200.00',
            'method' => 'bank_transfer',
            'paid_at' => now()->toDateString(),
            'idempotency_key' => (string) Str::uuid(),
        ])->assertRedirect();

        $this->assertSame('200.00', $invoice->fresh()->deposit_paid_amount);
        $this->assertSame('30.00', $invoice->fresh()->deposit_balance);

        $this->post(route('finance.payments.store'), [
            'invoice_id' => $invoice->id,
            'type' => 'deposit',
            'amount' => '31.00',
            'method' => 'bank_transfer',
            'paid_at' => now()->toDateString(),
            'idempotency_key' => (string) Str::uuid(),
        ])->assertStatus(422);

        $this->assertDatabaseCount('payments', 1);
    }


}