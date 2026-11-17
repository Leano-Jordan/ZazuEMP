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
        $event = \\App\\Models\\Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-COST-PRECISION',
            'name' => 'Precision Cost Event',
            'status' => 'draft',
        ]);
        $eventIdentity = app(\\App\\Support\\Offline\\SyncEntityIdentityRegistry::class)->identify($event, 'job');

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
                    'event_cost' => app(\\App\\Support\\Offline\\OfflineDomainMutationHandler::class),
                ]);
                $this->fail('Offline event costs must reject values with more than two decimal places.');
            } catch (\\Illuminate\\Validation\\ValidationException) {
                $this->assertSame(0, \\App\\Models\\EventCost::query()
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
                'asset' => app(\\App\\Support\\Offline\\OfflineDomainMutationHandler::class),
            ]);
            $this->fail('Offline asset costs must reject values with more than two decimal places.');
        } catch (\\Illuminate\\Validation\\ValidationException) {
            $this->assertSame(0, \\App\\Models\\Asset::query()
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
        $event = \\App\\Models\\Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-QUOTE-PRECISION',
            'name' => 'Precision Quote Event',
            'status' => 'draft',
        ]);
        $eventIdentity = app(\\App\\Support\\Offline\\SyncEntityIdentityRegistry::class)->identify($event, 'job');
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
                'quote' => app(\\App\\Support\\Offline\\OfflineDomainMutationHandler::class),
            ]);
            $this->fail('Offline quote quantities must reject values with more than two decimal places.');
        } catch (\\Illuminate\\Validation\\ValidationException) {
            $this->assertSame(0, \\App\\Models\\Quote::query()->count());
        }
    }

}
