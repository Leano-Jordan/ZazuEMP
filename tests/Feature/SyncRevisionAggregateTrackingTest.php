<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetAllocation;
use App\Models\EventCost;
use App\Models\EventPreparationItem;
use App\Models\EventRequirement;
use App\Models\FinanceExpense;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PurchaseOrderReceipt;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SyncMutation;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationHandler;
use App\Support\Offline\SyncMutationRecorder;
use App\Support\Offline\SyncMutationRelatedIdentityResolver;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\QuoteVersion;
use App\Models\SyncDevice;
use App\Services\QuoteService;
use App\Support\Offline\SyncBootstrapPayloadBuilder;
use App\Support\Offline\SyncEntityIdentityRegistry;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncRevisionAggregateTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_reads_aggregate_data_and_revisions_inside_one_business_transaction(): void
    {
        [$business, $customer, $device] = $this->customerFixtureWithDevice();
        $baselineLevel = DB::connection()->transactionLevel();
        $businessReadLevel = null;

        DB::listen(function (QueryExecuted $query) use (&$businessReadLevel): void {
            if ($businessReadLevel !== null || ! str_contains(strtolower($query->sql), 'businesses')) {
                return;
            }

            $businessReadLevel = $query->connection->transactionLevel();
        });

        app(SyncBootstrapPayloadBuilder::class)->build($device);

        $this->assertNotNull($businessReadLevel);
        $this->assertGreaterThan($baselineLevel, $businessReadLevel);
    }

    public function test_bootstrap_refreshes_revised_and_delivered_suppliers_outside_the_bounded_window(): void
    {
        [$business, $customer, $sourceDevice] = $this->customerFixtureWithDevice();
        $receivingDevice = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);

        $deliveredSupplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'Z-supplier-before-update',
        ]);
        $workingSetSuppliers = [];
        for ($index = 1; $index <= 500; $index++) {
            $workingSetSuppliers[] = Supplier::create([
                'business_id' => $business->id,
                'name' => sprintf('A-supplier-%04d', $index),
            ]);
        }

        $registry = app(SyncEntityIdentityRegistry::class);
        $identity = $registry->identify($deliveredSupplier, 'supplier');
        foreach ($workingSetSuppliers as $supplier) {
            $registry->identify($supplier, 'supplier');
        }

        // An ordinary online edit does not create a SyncDelivery. Its aggregate
        // revision timestamp must still promote it into the next bounded refresh.
        $deliveredSupplier->update(['name' => 'Z-supplier-updated-outside-window']);
        $builder = app(SyncBootstrapPayloadBuilder::class);
        $onlineSnapshot = $builder->build($receivingDevice);
        $onlineIncluded = collect($onlineSnapshot['suppliers'])->first(
            fn (array $item): bool => (int) ($item['server_id'] ?? 0) === (int) $deliveredSupplier->id,
        );
        $this->assertNotNull($onlineIncluded, 'Recently revised online aggregates must bubble into the bounded refresh.');
        $this->assertSame(2, (int) $onlineIncluded['revision']);

        $mutation = app(SyncMutationRecorder::class)->record(
            $sourceDevice,
            'supplier',
            $identity->entity_uuid,
            'update',
            [
                'local_id' => $identity->entity_uuid,
                'expected_revision' => 1,
                'record' => ['name' => 'Z-supplier-updated-outside-window'],
            ],
        );
        $mutation->update([
            'status' => 'applied',
            'result_revision' => (int) $identity->fresh()->revision,
            'applied_at' => now(),
        ]);
        app(SyncMutationRecorder::class)->publish($mutation);

        $delivery = \App\Models\SyncDelivery::query()
            ->where('business_id', $business->id)
            ->where('destination_device_id', $receivingDevice->id)
            ->where('sync_mutation_id', $mutation->id)
            ->firstOrFail();

        // Simulate an old identity timestamp to prove the explicit delivery
        // reference remains sufficient even when it is outside the top 500.
        DB::table('sync_entity_identities')
            ->where('business_id', $business->id)
            ->where('entity_type', 'supplier')
            ->where('entity_uuid', $identity->entity_uuid)
            ->update(['updated_at' => now()->subYear()]);

        $ordinarySnapshot = $builder->build($receivingDevice);
        $this->assertFalse(
            collect($ordinarySnapshot['suppliers'])->contains(
                fn (array $item): bool => (int) ($item['server_id'] ?? 0) === (int) $deliveredSupplier->id,
            ),
            'The supplier is deliberately outside the default 500-record revision-ranked window.',
        );

        $deliverySnapshot = $builder->build($receivingDevice, [(int) $delivery->delivery_sequence]);
        $included = collect($deliverySnapshot['suppliers'])->first(
            fn (array $item): bool => (int) ($item['server_id'] ?? 0) === (int) $deliveredSupplier->id,
        );

        $this->assertNotNull($included, 'A delivered aggregate must be included even when it falls outside the normal window.');
        $this->assertSame('Z-supplier-updated-outside-window', $included['record']['name']);
        $this->assertSame(2, (int) $included['revision']);
        $this->assertCount(501, $deliverySnapshot['suppliers']);
    }

    public function test_nested_quote_item_and_inventory_movement_writes_advance_parent_revisions(): void
    {
        [$business, $customer] = $this->customerFixture();
        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'REV-NESTED-001',
            'name' => 'Revision Nested Work',
            'status' => 'confirmed',
        ]);
        $quote = Quote::create([
            'event_id' => $event->id,
            'reference' => 'REV-QUOTE-001',
            'status' => 'draft',
            'currency' => 'ZAR',
        ]);
        $version = QuoteVersion::create([
            'quote_id' => $quote->id,
            'version' => 1,
            'status' => 'draft',
            'subtotal' => '0.00',
            'tax_total' => '0.00',
            'total' => '0.00',
            'deposit_percent' => '0.00',
            'deposit_amount' => '0.00',
        ]);
        $inventory = InventoryItem::create([
            'business_id' => $business->id,
            'name' => 'Revision Stock',
            'unit' => 'unit',
            'reorder_level' => '1.00',
        ]);

        $registry = app(SyncEntityIdentityRegistry::class);
        $quoteIdentity = $registry->identify($quote, 'quote');
        $inventoryIdentity = $registry->identify($inventory, 'inventory_item');

        QuoteItem::create([
            'quote_version_id' => $version->id,
            'description' => 'Nested quote line',
            'quantity' => '1.00',
            'unit' => 'unit',
            'unit_price' => '0.00',
            'line_total' => '0.00',
        ]);
        InventoryMovement::create([
            'business_id' => $business->id,
            'inventory_item_id' => $inventory->id,
            'idempotency_key' => (string) Str::uuid(),
            'type' => 'receipt',
            'quantity' => '2.00',
            'unit_cost' => '10.00',
            'movement_date' => now()->toDateString(),
        ]);

        $this->assertSame(2, (int) $quoteIdentity->fresh()->revision);
        $this->assertSame(2, (int) $inventoryIdentity->fresh()->revision);

        app(QuoteService::class)->updateDraft(
            $quote,
            $version,
            collect(),
            [],
            null,
            false,
            null,
            '0.00',
        );

        // Removing the last zero-value line does not change quote totals. The
        // child delete itself must still invalidate the parent snapshot.
        $this->assertGreaterThan(2, (int) $quoteIdentity->fresh()->revision);
    }



    public function test_online_snapshot_child_writes_advance_parent_revisions(): void
    {
        $fixture = $this->onlineRevisionMatrixFixture();

        $requirement = EventRequirement::create([
            'event_id' => $fixture['event']->id,
            'description' => 'Vegetarian meal option',
            'category' => 'dietary',
            'quantity' => '1.00',
            'unit' => 'person',
            'status' => 'open',
        ]);
        $this->assertSame(2, (int) $fixture['job_identity']->fresh()->revision);
        $requirement->update(['notes' => 'Confirm ingredients with supplier']);
        $this->assertSame(3, (int) $fixture['job_identity']->fresh()->revision);

        $invoiceItem = InvoiceItem::create([
            'invoice_id' => $fixture['invoice']->id,
            'description' => 'Catering service',
            'quantity' => '1.00',
            'unit' => 'service',
            'unit_price' => '100.00',
            'line_total' => '100.00',
        ]);
        $this->assertSame(2, (int) $fixture['invoice_identity']->fresh()->revision);
        $invoiceItem->update(['description' => 'Updated catering service']);
        $this->assertSame(3, (int) $fixture['invoice_identity']->fresh()->revision);

        Payment::create([
            'business_id' => $fixture['business']->id,
            'invoice_id' => $fixture['invoice']->id,
            'event_id' => $fixture['event']->id,
            'type' => 'deposit',
            'idempotency_key' => (string) Str::uuid(),
            'amount' => '25.00',
            'currency' => 'ZAR',
            'method' => 'cash',
            'paid_at' => now()->toDateString(),
        ]);
        $this->assertSame(4, (int) $fixture['invoice_identity']->fresh()->revision);

        $orderItem = PurchaseOrderItem::create([
            'business_id' => $fixture['business']->id,
            'purchase_order_id' => $fixture['order']->id,
            'description' => 'Disposable plates',
            'quantity' => '2.00',
            'received_quantity' => '0.00',
            'unit' => 'pack',
            'unit_price' => '10.00',
            'line_total' => '20.00',
        ]);
        $this->assertSame(2, (int) $fixture['order_identity']->fresh()->revision);
        $orderItem->update(['description' => 'Compostable plates']);
        $this->assertSame(3, (int) $fixture['order_identity']->fresh()->revision);

        PurchaseOrderReceipt::create([
            'business_id' => $fixture['business']->id,
            'purchase_order_id' => $fixture['order']->id,
            'idempotency_key' => (string) Str::uuid(),
        ]);
        $this->assertSame(4, (int) $fixture['order_identity']->fresh()->revision);

        $allocation = AssetAllocation::create([
            'business_id' => $fixture['business']->id,
            'asset_id' => $fixture['asset']->id,
            'event_id' => $fixture['event']->id,
            'allocated_from' => now()->toDateString(),
            'status' => 'allocated',
        ]);
        $this->assertSame(2, (int) $fixture['asset_identity']->fresh()->revision);
        $allocation->update(['notes' => 'Loaded for event']);
        $this->assertSame(3, (int) $fixture['asset_identity']->fresh()->revision);
    }

    public function test_online_preparation_expense_and_event_cost_revisions_advance(): void
    {
        $fixture = $this->onlineRevisionMatrixFixture();

        $fixture['preparation']->update(['notes' => 'Packed and checked']);
        $fixture['expense']->update(['notes' => 'Receipt recorded']);
        $fixture['cost']->update(['actual_amount' => '180.00', 'status' => 'incurred']);

        $this->assertSame(2, (int) $fixture['preparation_identity']->fresh()->revision);
        $this->assertSame(2, (int) $fixture['expense_identity']->fresh()->revision);
        $this->assertSame(2, (int) $fixture['cost_identity']->fresh()->revision);
    }

    private function onlineRevisionMatrixFixture(): array
    {
        [$business, $customer] = $this->customerFixture();
        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'REV-MATRIX-WORK',
            'name' => 'Revision Matrix Work',
            'status' => 'confirmed',
        ]);
        $invoice = Invoice::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'number' => 'INV-REV-MATRIX-001',
            'status' => 'issued',
            'currency' => 'ZAR',
            'subtotal' => '100.00',
            'tax_total' => '0.00',
            'total' => '100.00',
            'issued_at' => now()->toDateString(),
        ]);
        $supplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'Revision Matrix Supplier',
        ]);
        $order = PurchaseOrder::create([
            'business_id' => $business->id,
            'supplier_id' => $supplier->id,
            'reference' => 'PO-REV-MATRIX-001',
            'status' => 'ordered',
            'currency' => 'ZAR',
            'total_amount' => '20.00',
        ]);
        $asset = Asset::create([
            'business_id' => $business->id,
            'asset_tag' => 'REV-MATRIX-ASSET',
            'name' => 'Revision Matrix Asset',
            'status' => 'available',
            'condition' => 'good',
            'purchase_cost' => '1000.00',
            'currency' => 'ZAR',
        ]);
        $preparation = EventPreparationItem::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'title' => 'Pack serving equipment',
            'category' => 'equipment',
            'quantity' => '1.00',
            'unit' => 'set',
            'status' => 'open',
        ]);
        $expense = FinanceExpense::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'description' => 'Transport expense',
            'amount' => '120.00',
            'currency' => 'ZAR',
            'expense_date' => now()->toDateString(),
            'status' => 'recorded',
        ]);
        $cost = EventCost::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'category' => 'transport',
            'description' => 'Fuel estimate',
            'currency' => 'ZAR',
            'projected_amount' => '200.00',
            'status' => 'planned',
        ]);

        $registry = app(SyncEntityIdentityRegistry::class);

        return [
            'business' => $business,
            'event' => $event,
            'invoice' => $invoice,
            'order' => $order,
            'asset' => $asset,
            'preparation' => $preparation,
            'expense' => $expense,
            'cost' => $cost,
            'job_identity' => $registry->identify($event, 'job'),
            'invoice_identity' => $registry->identify($invoice, 'invoice'),
            'order_identity' => $registry->identify($order, 'purchase_order'),
            'asset_identity' => $registry->identify($asset, 'asset'),
            'preparation_identity' => $registry->identify($preparation, 'preparation'),
            'expense_identity' => $registry->identify($expense, 'expense'),
            'cost_identity' => $registry->identify($cost, 'event_cost'),
        ];
    }

    public function test_offline_commands_advance_every_related_aggregate_revision(): void
    {
        [$business, $customer, $device] = $this->customerFixtureWithDevice();
        $fixture = $this->commandRevisionFixture($business, $customer);

        $accepted = $this->applyCommand($device, 'quote_acceptance', [
            'quote_local_id' => $fixture['quote']->entity_uuid,
        ]);
        $this->assertSame(2, (int) $fixture['quote']->fresh()->revision);
        $this->assertSame(2, (int) $fixture['job']->fresh()->revision);
        $this->assertRelatedRevisions($accepted, [
            'quote:'.$fixture['quote']->entity_uuid => 2,
            'job:'.$fixture['job']->entity_uuid => 2,
        ]);

        $payment = $this->applyCommand($device, 'payment', [
            'invoice_local_id' => $fixture['invoice']->entity_uuid,
        ]);
        $this->assertSame(2, (int) $fixture['invoice']->fresh()->revision);
        $this->assertRelatedRevisions($payment, ['invoice:'.$fixture['invoice']->entity_uuid => 2]);

        $receipt = $this->applyCommand($device, 'purchase_receipt', [
            'purchase_order_local_id' => $fixture['order']->entity_uuid,
            'received_quantities' => [$fixture['line']->id => '1.00'],
        ]);
        $this->assertSame(2, (int) $fixture['order']->fresh()->revision);
        $this->assertSame(3, (int) $fixture['inventory']->fresh()->revision);
        $this->assertSame(2, (int) $fixture['unreceived_inventory']->fresh()->revision);
        $this->assertRelatedRevisions($receipt, [
            'purchase_order:'.$fixture['order']->entity_uuid => 2,
            'inventory_item:'.$fixture['inventory']->entity_uuid => 3,
        ]);

        $movement = $this->applyCommand($device, 'inventory_movement', [
            'inventory_item_local_id' => $fixture['inventory']->entity_uuid,
        ]);
        $this->assertSame(4, (int) $fixture['inventory']->fresh()->revision);
        $this->assertRelatedRevisions($movement, ['inventory_item:'.$fixture['inventory']->entity_uuid => 4]);

        $allocation = $this->applyCommand($device, 'asset_allocation', [
            'asset_local_id' => $fixture['asset']->entity_uuid,
        ]);
        $this->assertSame(2, (int) $fixture['asset']->fresh()->revision);
        $this->assertRelatedRevisions($allocation, ['asset:'.$fixture['asset']->entity_uuid => 2]);
    }

    private function commandRevisionFixture(Business $business, Customer $customer): array
    {
        $commercial = $this->commandRevisionCommercialRecords($business, $customer);
        $stock = $this->commandRevisionStockRecords(
            $business,
            $commercial['order'],
            $commercial['line'],
            $commercial['unreceived_line'],
        );
        $registry = app(SyncEntityIdentityRegistry::class);

        return [
            'job' => $registry->identify($commercial['event'], 'job'),
            'quote' => $registry->identify($commercial['quote'], 'quote'),
            'invoice' => $registry->identify($commercial['invoice'], 'invoice'),
            'order' => $registry->identify($commercial['order'], 'purchase_order'),
            'inventory' => $stock['inventory'],
            'unreceived_inventory' => $stock['unreceived_inventory'],
            'asset' => $stock['asset'],
            'line' => $commercial['line'],
        ];
    }

    private function commandRevisionCommercialRecords(Business $business, Customer $customer): array
    {
        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'REV-COMMAND-WORK',
            'name' => 'Revision Command Work',
            'status' => 'confirmed',
        ]);
        $quote = Quote::create([
            'event_id' => $event->id,
            'reference' => 'REV-COMMAND-QUOTE',
            'status' => 'sent',
            'currency' => 'ZAR',
        ]);
        $invoice = Invoice::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'number' => 'INV-REV-COMMAND-001',
            'status' => 'issued',
            'currency' => 'ZAR',
            'subtotal' => '100.00',
            'tax_total' => '0.00',
            'total' => '100.00',
            'issued_at' => now()->toDateString(),
        ]);
        $supplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'Revision Command Supplier',
        ]);
        $order = PurchaseOrder::create([
            'business_id' => $business->id,
            'supplier_id' => $supplier->id,
            'reference' => 'PO-REV-COMMAND-001',
            'status' => 'ordered',
            'currency' => 'ZAR',
            'total_amount' => '50.00',
        ]);

        return [
            'event' => $event,
            'quote' => $quote,
            'invoice' => $invoice,
            'order' => $order,
            'line' => $this->commandPurchaseOrderLine($business, $order, 'Revision command stock', '5.00', '10.00', '50.00'),
            'unreceived_line' => $this->commandPurchaseOrderLine($business, $order, 'Unreceived command stock', '3.00', '5.00', '15.00'),
        ];
    }

    private function commandPurchaseOrderLine(
        Business $business,
        PurchaseOrder $order,
        string $description,
        string $quantity,
        string $unitPrice,
        string $lineTotal,
    ): PurchaseOrderItem {
        return PurchaseOrderItem::create([
            'business_id' => $business->id,
            'purchase_order_id' => $order->id,
            'description' => $description,
            'quantity' => $quantity,
            'received_quantity' => '0.00',
            'unit' => 'unit',
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
        ]);
    }

    private function commandRevisionStockRecords(
        Business $business,
        PurchaseOrder $order,
        PurchaseOrderItem $line,
        PurchaseOrderItem $unreceivedLine,
    ): array {
        $inventory = InventoryItem::create([
            'business_id' => $business->id,
            'name' => 'Revision command stock',
            'unit' => 'unit',
            'reorder_level' => '1.00',
        ]);
        $unreceivedInventory = InventoryItem::create([
            'business_id' => $business->id,
            'name' => 'Unreceived command stock',
            'unit' => 'unit',
            'reorder_level' => '1.00',
        ]);
        $asset = Asset::create([
            'business_id' => $business->id,
            'asset_tag' => 'REV-COMMAND-ASSET',
            'name' => 'Revision command asset',
            'status' => 'available',
            'condition' => 'good',
            'purchase_cost' => '2500.00',
            'currency' => 'ZAR',
        ]);
        $registry = app(SyncEntityIdentityRegistry::class);
        $inventoryIdentity = $registry->identify($inventory, 'inventory_item');
        $unreceivedInventoryIdentity = $registry->identify($unreceivedInventory, 'inventory_item');

        $this->recordPurchaseMovement($business, $order, $line, $inventory, '10.00');
        $this->recordPurchaseMovement($business, $order, $unreceivedLine, $unreceivedInventory, '5.00');

        return [
            'inventory' => $inventoryIdentity,
            'unreceived_inventory' => $unreceivedInventoryIdentity,
            'asset' => $registry->identify($asset, 'asset'),
        ];
    }

    private function recordPurchaseMovement(
        Business $business,
        PurchaseOrder $order,
        PurchaseOrderItem $line,
        InventoryItem $inventory,
        string $unitCost,
    ): void {
        InventoryMovement::create([
            'business_id' => $business->id,
            'inventory_item_id' => $inventory->id,
            'purchase_order_id' => $order->id,
            'purchase_order_item_id' => $line->id,
            'idempotency_key' => (string) Str::uuid(),
            'type' => 'receipt',
            'quantity' => '1.00',
            'unit_cost' => $unitCost,
            'movement_date' => now()->toDateString(),
        ]);
    }

    private function applyCommand(SyncDevice $device, string $entityType, array $record): SyncMutation
    {
        $handler = new class implements SyncMutationHandler {
            public function apply(SyncMutation $mutation): void
            {
                // Domain outcomes are tested by the command lifecycle suite.
            }
        };
        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            $entityType,
            (string) Str::uuid(),
            'create',
            ['record' => $record],
        );

        return app(SyncMutationApplier::class)->apply($mutation, [$entityType => $handler]);
    }

    /**
     * @param array<string, int> $expected
     */
    private function assertRelatedRevisions(SyncMutation $mutation, array $expected): void
    {
        $actual = collect(app(SyncMutationRelatedIdentityResolver::class)->resolve($mutation))
            ->mapWithKeys(fn (array $identity): array => [
                $identity['entity_type'].':'.$identity['local_id'] => $identity['revision'],
            ])
            ->all();
        ksort($actual);
        ksort($expected);

        $this->assertSame($expected, $actual, 'Acknowledgement must contain only the aggregates changed by this command.');
    }

    private function customerFixture(): array
    {
        $business = Business::create([
            'name' => 'Revision Tracking Business',
            'slug' => 'revision-tracking-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Revision Tracking Customer',
        ]);

        return [$business, $customer];
    }

    private function customerFixtureWithDevice(): array
    {
        [$business, $customer] = $this->customerFixture();
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Revision tracking phone',
            'device_type' => 'phone',
            'metadata' => ['sync_token_hash' => hash('sha256', 'revision-tracking-token')],
            'token_expires_at' => now()->addDays(SyncDevice::SYNC_TOKEN_IDLE_EXPIRY_DAYS),
        ]);
        app(SyncEntityIdentityRegistry::class)->identify($customer, 'customer');

        return [$business, $customer, $device];
    }
}
