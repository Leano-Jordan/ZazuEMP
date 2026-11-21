<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\SyncDevice;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfflinePurchasePrecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_offline_purchase_order_rejects_precision_loss_in_quantity_and_unit_price(): void
    {
        $business = Business::create([
            'name' => 'Offline Purchase Precision Business',
            'slug' => 'offline-purchase-precision-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $supplier = \App\Models\Supplier::create([
            'business_id' => $business->id,
            'name' => 'Precision Supplier',
        ]);
        $supplierIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($supplier, 'supplier');

        $invalidLines = [
            ['quantity' => '0.004', 'unit_price' => '10.00'],
            ['quantity' => '1.00', 'unit_price' => '1.005'],
            // Exercise decoded JSON numeric values as floats, not only strings.
            ['quantity' => 1.005, 'unit_price' => 10.00],
            ['quantity' => 1.00, 'unit_price' => 1.005],
        ];

        foreach ($invalidLines as $line) {
            $localId = (string) Str::uuid();
            $mutation = app(SyncMutationRecorder::class)->record(
                $device,
                'purchase_order',
                $localId,
                'create',
                [
                    'local_id' => $localId,
                    'record' => [
                        'supplier_local_id' => $supplierIdentity->entity_uuid,
                        'currency' => 'ZAR',
                        'lines' => [[
                            'local_id' => (string) Str::uuid(),
                            'description' => 'Precision validation line',
                            ...$line,
                        ]],
                    ],
                ],
            );

            try {
                app(SyncMutationApplier::class)->apply($mutation, [
                    'purchase_order' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
                ]);
                $this->fail('Offline purchase orders must reject values that lose decimal precision.');
            } catch (\Illuminate\Validation\ValidationException) {
                $this->assertSame(0, \App\Models\PurchaseOrder::query()
                    ->where('business_id', $business->id)
                    ->count());
            }
        }
    }


    public function test_offline_event_cost_rejects_money_precision_loss(): void
    {
        $business = Business::create([
            'name' => 'Offline Cost Precision Business',
            'slug' => 'offline-cost-precision-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $event = \App\Models\Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-COST-PRECISION',
            'name' => 'Precision Cost Event',
            'status' => 'draft',
        ]);
        $eventIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($event, 'job');

        $invalidRecords = [
            ['status' => 'planned', 'projected_amount' => '1.005', 'actual_amount' => null],
            ['status' => 'incurred', 'projected_amount' => '1.00', 'actual_amount' => '1.005'],
        ];

        foreach ($invalidRecords as $invalid) {
            $localId = (string) Str::uuid();
            $mutation = app(SyncMutationRecorder::class)->record(
                $device,
                'event_cost',
                $localId,
                'create',
                [
                    'local_id' => $localId,
                    'record' => [
                        'event_local_id' => $eventIdentity->entity_uuid,
                        'category' => 'Food',
                        'description' => 'Precision validation cost',
                        'currency' => 'ZAR',
                        ...$invalid,
                    ],
                ],
            );

            try {
                app(SyncMutationApplier::class)->apply($mutation, [
                    'event_cost' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
                ]);
                $this->fail('Offline event costs must reject values with more than two decimal places.');
            } catch (\Illuminate\Validation\ValidationException) {
                $this->assertSame(0, \App\Models\EventCost::query()
                    ->where('business_id', $business->id)
                    ->count());
            }
        }
    }

    public function test_offline_asset_rejects_purchase_cost_precision_loss(): void
    {
        $business = Business::create([
            'name' => 'Offline Asset Precision Business',
            'slug' => 'offline-asset-precision-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $localId = (string) Str::uuid();
        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'asset',
            $localId,
            'create',
            [
                'local_id' => $localId,
                'record' => [
                    'asset_tag' => 'PRECISION-ASSET-001',
                    'name' => 'Offline precision asset',
                    'condition' => 'good',
                    'purchase_cost' => '1.005',
                ],
            ],
        );

        try {
            app(SyncMutationApplier::class)->apply($mutation, [
                'asset' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
            ]);
            $this->fail('Offline asset costs must reject values with more than two decimal places.');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->assertSame(0, \App\Models\Asset::query()
                ->where('business_id', $business->id)
                ->count());
        }
    }

    public function test_offline_quote_rejects_quantity_precision_loss(): void
    {
        $business = Business::create([
            'name' => 'Offline Quote Precision Business',
            'slug' => 'offline-quote-precision-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $event = \App\Models\Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-QUOTE-PRECISION',
            'name' => 'Precision Quote Event',
            'status' => 'draft',
        ]);
        $eventIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($event, 'job');
        $localId = (string) Str::uuid();
        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'quote',
            $localId,
            'create',
            [
                'local_id' => $localId,
                'record' => [
                    'event_local_id' => $eventIdentity->entity_uuid,
                    'currency' => 'ZAR',
                    'items' => [[
                        'description' => 'Precision validation line',
                        'quantity' => '1.005',
                        'unit_price' => '10.00',
                    ]],
                ],
            ],
        );

        try {
            app(SyncMutationApplier::class)->apply($mutation, [
                'quote' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
            ]);
            $this->fail('Offline quote quantities must reject values with more than two decimal places.');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->assertSame(0, \App\Models\Quote::query()->count());
        }
    }


    public function test_offline_quote_rejects_unit_price_precision_loss(): void
    {
        $business = Business::create([
            'name' => 'Offline Quote Price Precision Business',
            'slug' => 'offline-quote-price-precision-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $event = \App\Models\Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-QUOTE-PRICE-PRECISION',
            'name' => 'Precision Quote Price Event',
            'status' => 'draft',
        ]);
        $eventIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($event, 'job');
        $localId = (string) Str::uuid();
        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'quote',
            $localId,
            'create',
            [
                'local_id' => $localId,
                'record' => [
                    'event_local_id' => $eventIdentity->entity_uuid,
                    'currency' => 'ZAR',
                    'items' => [[
                        'description' => 'Precision validation line',
                        'quantity' => '1.00',
                        'unit_price' => '1.005',
                    ]],
                ],
            ],
        );

        try {
            app(SyncMutationApplier::class)->apply($mutation, [
                'quote' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
            ]);
            $this->fail('Offline quote prices must reject values with more than two decimal places.');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->assertSame(0, \App\Models\Quote::query()
                ->whereHas('event', fn ($query) => $query->where('business_id', $business->id))
                ->count());
        }
    }

    public function test_offline_inventory_movement_rejects_unit_cost_precision_loss(): void
    {
        $business = Business::create([
            'name' => 'Offline Movement Precision Business',
            'slug' => 'offline-movement-precision-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $item = \App\Models\InventoryItem::create([
            'business_id' => $business->id,
            'name' => 'Precision stock item',
            'unit' => 'unit',
            'reorder_level' => '0.00',
        ]);
        $itemIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($item, 'inventory_item');
        $localId = (string) Str::uuid();
        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'inventory_movement',
            $localId,
            'create',
            [
                'local_id' => $localId,
                'record' => [
                    'inventory_item_local_id' => $itemIdentity->entity_uuid,
                    'type' => 'receipt',
                    'quantity' => '1.00',
                    'unit_cost' => '1.005',
                    'movement_date' => now()->toDateString(),
                ],
            ],
        );

        try {
            app(SyncMutationApplier::class)->apply($mutation, [
                'inventory_movement' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
            ]);
            $this->fail('Offline inventory movement costs must reject values with more than two decimal places.');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->assertSame(0, \App\Models\InventoryMovement::query()
                ->where('business_id', $business->id)
                ->count());
        }
    }

    public function test_offline_expense_rejects_money_precision_loss(): void
    {
        $business = Business::create([
            'name' => 'Offline Expense Precision Business',
            'slug' => 'offline-expense-precision-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $localId = (string) Str::uuid();
        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'expense',
            $localId,
            'create',
            [
                'local_id' => $localId,
                'record' => [
                    'description' => 'Precision validation expense',
                    'amount' => '1.005',
                    'status' => 'unpaid',
                    'expense_date' => now()->toDateString(),
                ],
            ],
        );

        try {
            app(SyncMutationApplier::class)->apply($mutation, [
                'expense' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
            ]);
            $this->fail('Offline expenses must reject values with more than two decimal places.');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->assertSame(0, \App\Models\FinanceExpense::query()
                ->where('business_id', $business->id)
                ->count());
        }
    }

    public function test_offline_purchase_receipt_rejects_quantity_precision_loss(): void
    {
        $business = Business::create([
            'name' => 'Offline Receipt Precision Business',
            'slug' => 'offline-receipt-precision-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $supplier = \App\Models\Supplier::create([
            'business_id' => $business->id,
            'name' => 'Receipt Precision Supplier',
        ]);
        $supplierIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($supplier, 'supplier');
        $orderLocalId = (string) Str::uuid();
        $lineLocalId = (string) Str::uuid();
        $recorder = app(SyncMutationRecorder::class);
        $applier = app(SyncMutationApplier::class);
        $handler = app(\App\Support\Offline\OfflineDomainMutationHandler::class);

        $orderMutation = $recorder->record(
            $device,
            'purchase_order',
            $orderLocalId,
            'create',
            [
                'local_id' => $orderLocalId,
                'record' => [
                    'supplier_local_id' => $supplierIdentity->entity_uuid,
                    'currency' => 'ZAR',
                    'status' => 'ordered',
                    'lines' => [[
                        'local_id' => $lineLocalId,
                        'description' => 'Receipt validation item',
                        'quantity' => '2.00',
                        'unit_price' => '10.00',
                    ]],
                ],
            ],
        );
        $applier->apply($orderMutation, ['purchase_order' => $handler]);

        $receiptLocalId = (string) Str::uuid();
        $receiptMutation = $recorder->record(
            $device,
            'purchase_receipt',
            $receiptLocalId,
            'create',
            [
                'local_id' => $receiptLocalId,
                'record' => [
                    'purchase_order_local_id' => $orderLocalId,
                    'received_quantities' => [$lineLocalId => '0.004'],
                ],
            ],
        );

        try {
            $applier->apply($receiptMutation, ['purchase_receipt' => $handler]);
            $this->fail('Offline receipt quantities must reject values with more than two decimal places.');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->assertSame(0, \App\Models\PurchaseOrderReceipt::query()
                ->where('business_id', $business->id)
                ->count());
            $this->assertSame('0.00', \App\Models\PurchaseOrderItem::query()
                ->where('business_id', $business->id)
                ->value('received_quantity'));
        }
    }


    public function test_offline_expense_preserves_business_links_and_applies_idempotently(): void
    {
        $business = Business::create([
            'name' => 'Offline Expense Reconciliation Business',
            'slug' => 'offline-expense-reconciliation-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $event = \App\Models\Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-EXPENSE-RECONCILIATION',
            'name' => 'Offline expense job',
            'status' => 'confirmed',
        ]);
        $supplier = \App\Models\Supplier::create([
            'business_id' => $business->id,
            'name' => 'Offline expense supplier',
        ]);
        $order = \App\Models\PurchaseOrder::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'supplier_id' => $supplier->id,
            'idempotency_key' => (string) Str::uuid(),
            'reference' => 'PO-OFF-EXPENSE-001',
            'status' => 'received',
            'currency' => 'ZAR',
            'total_amount' => '450.00',
        ]);

        $registry = app(\App\Support\Offline\SyncEntityIdentityRegistry::class);
        $eventIdentity = $registry->identify($event, 'job');
        $supplierIdentity = $registry->identify($supplier, 'supplier');
        $orderIdentity = $registry->identify($order, 'purchase_order');
        $localId = (string) Str::uuid();
        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'expense',
            $localId,
            'create',
            [
                'local_id' => $localId,
                'record' => [
                    'description' => 'Offline catering fuel',
                    'amount' => '125.50',
                    'status' => 'paid',
                    'expense_date' => now()->toDateString(),
                    'reference' => 'FUEL-OFF-001',
                    'notes' => 'Captured while disconnected.',
                    'event_local_id' => $eventIdentity->entity_uuid,
                    'supplier_local_id' => $supplierIdentity->entity_uuid,
                    'purchase_order_local_id' => $orderIdentity->entity_uuid,
                ],
            ],
        );

        $handler = app(\App\Support\Offline\OfflineDomainMutationHandler::class);
        $applier = app(SyncMutationApplier::class);
        $applied = $applier->apply($mutation, ['expense' => $handler]);

        $expense = \App\Models\FinanceExpense::query()
            ->where('business_id', $business->id)
            ->where('idempotency_key', $mutation->mutation_id)
            ->firstOrFail();
        $identity = \App\Models\SyncEntityIdentity::query()
            ->where('business_id', $business->id)
            ->where('entity_type', 'expense')
            ->where('entity_uuid', $localId)
            ->firstOrFail();

        $this->assertSame('applied', $applied->status);
        $this->assertSame('Offline catering fuel', $expense->description);
        $this->assertSame('125.50', $expense->amount);
        $this->assertSame('ZAR', $expense->currency);
        $this->assertSame('paid', $expense->status);
        $this->assertSame($event->id, $expense->event_id);
        $this->assertSame($supplier->id, $expense->supplier_id);
        $this->assertSame($order->id, $expense->purchase_order_id);
        $this->assertSame($expense->id, $identity->record_id);
        $this->assertSame(1, (int) $identity->revision);
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'action' => 'finance.expense.recorded',
            'subject_id' => $expense->id,
        ]);

        // An applied mutation replay is a no-op: no duplicate expense or audit.
        $applier->apply($applied, ['expense' => $handler]);

        $this->assertSame(1, \App\Models\FinanceExpense::query()->where('business_id', $business->id)->count());
        $this->assertSame(1, \App\Models\AuditLog::query()
            ->where('business_id', $business->id)
            ->where('action', 'finance.expense.recorded')
            ->count());
        $this->assertSame(1, (int) $identity->fresh()->revision);
    }


}
