<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\SyncDevice;
use App\Support\Offline\OfflineDomainMutationHandler;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OfflineMutationOperationValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsupported_delete_operations_cannot_create_customer_job_or_service_records(): void
    {
        $business = Business::create([
            'name' => 'Offline Operation Guard Business',
            'slug' => 'offline-operation-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'status' => 'active',
            'token_expires_at' => now()->addDay(),
            'metadata' => ['sync_token_hash' => hash('sha256', 'offline-operation-token')],
        ]);

        $cases = [
            'customer' => ['name' => 'Must not be created as a delete'],
            'job' => [
                'name' => 'Must not be created as a delete',
                'event_type' => 'Other',
                'status' => 'draft',
                'event_date' => now()->addDays(10)->toDateString(),
            ],
            'service' => [
                'name' => 'Must not be created as a delete',
                'capability_type' => 'service',
                'pricing_basis' => 'fixed',
                'currency' => 'ZAR',
                'category' => 'Catering',
            ],
            'supplier' => [],
            'preparation' => [],
            'purchase_order' => [],
            'quote' => [],
            'quote_acceptance' => [],
            'invoice' => [],
            'payment' => [],
            'expense' => [],
            'purchase_receipt' => [],
            'inventory_item' => [],
            'inventory_movement' => [],
            'event_cost' => [],
            'asset' => [],
            'asset_allocation' => [],
        ];

        foreach ($cases as $entityType => $record) {
            $localId = (string) Str::uuid();
            $mutation = app(SyncMutationRecorder::class)->record(
                $device,
                $entityType,
                $localId,
                'delete',
                ['local_id' => $localId, 'record' => $record],
            );

            try {
                app(SyncMutationApplier::class)->apply($mutation, [
                    $entityType => app(OfflineDomainMutationHandler::class),
                ]);
                $this->fail("Unsupported delete operation was accepted for {$entityType}.");
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('operation', $exception->errors());
            }

            $this->assertSame('pending', $mutation->fresh()->status);
            $this->assertSame(0, $mutation->fresh()->attempts);
        }

        foreach ([
            'customers',
            'events',
            'business_capabilities',
            'suppliers',
            'event_preparation_items',
            'purchase_orders',
            'quotes',
            'invoices',
            'payments',
            'finance_expenses',
            'inventory_items',
            'inventory_movements',
            'event_costs',
            'assets',
            'asset_allocations',
        ] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }
}
