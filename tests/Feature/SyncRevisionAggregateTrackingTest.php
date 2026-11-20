<?php

namespace Tests\Feature;

use App\Models\Asset;
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
            'quote' => 2,
            'job' => 2,
        ]);

        $payment = $this->applyCommand($device, 'payment', [
            'invoice_local_id' => $fixture['invoice']->entity_uuid,
        ]);
        $this->assertSame(2, (int) $fixture['invoice']->fresh()->revision);
        $this->assertRelatedRevisions($payment, ['invoice' => 2]);

        $receipt = $this->applyCommand($device, 'purchase_receipt', [
            'purchase_order_local_id' => $fixture['order']->entity_uuid,
            'received_quantities' => [$fixture['line']->id => '1.00'],
        ]);
        $this->assertSame(2, (int) $fixture['order']->fresh()->revision);
        $this->assertSame(3, (int) $fixture['inventory']->fresh()->revision);
        $this->assertRelatedRevisions($receipt, [
            'purchase_order' => 2,
            'inventory_item' => 3,
        ]);

        $movement = $this->applyCommand($device, 'inventory_movement', [
            'inventory_item_local_id' => $fixture['inventory']->entity_uuid,
        ]);
        $this->assertSame(4, (int) $fixture['inventory']->fresh()->revision);
        $this->assertRelatedRevisions($movement, ['inventory_item' => 4]);

        $allocation = $this->applyCommand($device, 'asset_allocation', [
            'asset_local_id' => $fixture['asset']->entity_uuid,
        ]);
        $this->assertSame(2, (int) $fixture['asset']->fresh()->revision);
        $this->assertRelatedRevisions($allocation, ['asset' => 2]);
    }

    private function commandRevisionFixture(Business $business, Customer $customer): array
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
        $line = PurchaseOrderItem::create([
            'business_id' => $business->id,
            'purchase_order_id' => $order->id,
            'description' => 'Revision command stock',
            'quantity' => '5.00',
            'received_quantity' => '0.00',
            'unit' => 'unit',
            'unit_price' => '10.00',
            'line_total' => '50.00',
        ]);
        $inventory = InventoryItem::create([
            'business_id' => $business->id,
            'name' => 'Revision command stock',
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

        InventoryMovement::create([
            'business_id' => $business->id,
            'inventory_item_id' => $inventory->id,
            'purchase_order_id' => $order->id,
            'purchase_order_item_id' => $line->id,
            'idempotency_key' => (string) Str::uuid(),
            'type' => 'receipt',
            'quantity' => '1.00',
            'unit_cost' => '10.00',
            'movement_date' => now()->toDateString(),
        ]);

        return [
            'job' => $registry->identify($event, 'job'),
            'quote' => $registry->identify($quote, 'quote'),
            'invoice' => $registry->identify($invoice, 'invoice'),
            'order' => $registry->identify($order, 'purchase_order'),
            'inventory' => $inventoryIdentity,
            'asset' => $registry->identify($asset, 'asset'),
            'line' => $line,
        ];
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
            ->mapWithKeys(fn (array $identity): array => [$identity['entity_type'] => $identity['revision']])
            ->all();

        foreach ($expected as $entityType => $revision) {
            $this->assertSame($revision, $actual[$entityType] ?? null, $entityType.' acknowledgement revision mismatch');
        }
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
