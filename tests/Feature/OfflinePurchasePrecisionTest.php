<?php

namespace Tests\\Feature;

use App\\Models\\Business;
use App\\Models\\SyncDevice;
use App\\Support\\Offline\\SyncMutationApplier;
use App\\Support\\Offline\\SyncMutationRecorder;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Illuminate\\Support\\Str;
use Tests\\TestCase;

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
        $supplier = \\App\\Models\\Supplier::create([
            'business_id' => $business->id,
            'name' => 'Precision Supplier',
        ]);
        $supplierIdentity = app(\\App\\Support\\Offline\\SyncEntityIdentityRegistry::class)->identify($supplier, 'supplier');

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
                    'purchase_order' => app(\\App\\Support\\Offline\\OfflineDomainMutationHandler::class),
                ]);
                $this->fail('Offline purchase orders must reject values that lose decimal precision.');
            } catch (\\Illuminate\\Validation\\ValidationException) {
                $this->assertSame(0, \\App\\Models\\PurchaseOrder::query()
                    ->where('business_id', $business->id)
                    ->count());
            }
        }
    }
}
