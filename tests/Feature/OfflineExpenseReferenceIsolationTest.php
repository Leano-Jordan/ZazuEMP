<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\FinanceExpense;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SyncDelivery;
use App\Models\SyncDevice;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
use App\Support\Offline\OfflineDomainMutationHandler;
use App\Support\Offline\SyncBootstrapDeliveryTargetResolver;
use App\Support\Offline\SyncEntityIdentityRegistry;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationRecorder;
use App\Support\Offline\SyncMutationRelatedIdentityResolver;
use App\Support\Offline\SyncEntitySnapshotBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OfflineExpenseReferenceIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_offline_expense_accepts_explicitly_empty_optional_links(): void
    {
        $business = $this->business('Optional expense business');
        $device = $this->device($business);
        $mutation = $this->recordExpense($device, [
            'description' => 'Unassigned cash expense',
            'amount' => '25.00',
            'status' => 'unpaid',
            'expense_date' => now()->toDateString(),
            'supplier_local_id' => null,
            'event_local_id' => null,
            'purchase_order_local_id' => null,
        ]);

        $applied = app(SyncMutationApplier::class)->apply(
            $mutation,
            ['expense' => app(OfflineDomainMutationHandler::class)],
        );
        $expense = FinanceExpense::query()
            ->where('business_id', $business->id)
            ->where('idempotency_key', $mutation->mutation_id)
            ->firstOrFail();

        $this->assertSame('applied', $applied->status);
        $this->assertSame('25.00', $expense->amount);
        $this->assertSame('ZAR', $expense->currency);
        $this->assertNull($expense->supplier_id);
        $this->assertNull($expense->event_id);
        $this->assertNull($expense->purchase_order_id);
    }

    public function test_offline_event_cost_rejects_a_foreign_business_event_identity(): void
    {
        $business = $this->business('Event cost owner business');
        $foreignBusiness = $this->business('Foreign event cost business');
        $device = $this->device($business);

        $foreignEvent = Event::create([
            'business_id' => $foreignBusiness->id,
            'reference' => 'FOREIGN-EVENT-COST',
            'name' => 'Foreign event cost job',
            'status' => 'confirmed',
        ]);
        $foreignEventIdentity = app(SyncEntityIdentityRegistry::class)
            ->identify($foreignEvent, 'job')
            ->entity_uuid;

        $localId = (string) Str::uuid();
        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'event_cost',
            $localId,
            'create',
            [
                'local_id' => $localId,
                'record' => [
                    'event_local_id' => $foreignEventIdentity,
                    'category' => 'Food',
                    'description' => 'Cross-business event cost attempt',
                    'currency' => 'ZAR',
                    'projected_amount' => '100.00',
                    'actual_amount' => null,
                    'status' => 'planned',
                ],
            ],
        );

        try {
            app(SyncMutationApplier::class)->apply($mutation, [
                'event_cost' => app(OfflineDomainMutationHandler::class),
            ]);
            $this->fail('An offline event cost must reject a job identity owned by another business.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['The offline mutation references an unknown job.'],
                $exception->errors()['payload'] ?? [],
            );
            $this->assertSame('pending', $mutation->fresh()->status);
        }

        $this->assertSame(0, EventCost::query()
            ->where('business_id', $business->id)
            ->count());
    }

    public function test_offline_expense_rejects_foreign_event_supplier_and_purchase_order_identities(): void
    {
        $business = $this->business('Expense owner business');
        $foreignBusiness = $this->business('Foreign expense business');
        $device = $this->device($business);

        $foreignEvent = Event::create([
            'business_id' => $foreignBusiness->id,
            'reference' => 'FOREIGN-EXPENSE-JOB',
            'name' => 'Foreign expense job',
            'status' => 'confirmed',
        ]);
        $foreignSupplier = Supplier::create([
            'business_id' => $foreignBusiness->id,
            'name' => 'Foreign expense supplier',
        ]);
        $foreignOrder = PurchaseOrder::create([
            'business_id' => $foreignBusiness->id,
            'event_id' => $foreignEvent->id,
            'supplier_id' => $foreignSupplier->id,
            'idempotency_key' => (string) Str::uuid(),
            'reference' => 'PO-FOREIGN-EXPENSE',
            'status' => 'received',
            'currency' => 'ZAR',
            'total_amount' => '450.00',
        ]);

        $identities = app(SyncEntityIdentityRegistry::class);
        $foreignEventId = $identities->identify($foreignEvent, 'job')->entity_uuid;
        $foreignSupplierId = $identities->identify($foreignSupplier, 'supplier')->entity_uuid;
        $foreignOrderId = $identities->identify($foreignOrder, 'purchase_order')->entity_uuid;

        $cases = [
            ['supplier_local_id' => $foreignSupplierId, 'expected_field' => 'supplier_local_id'],
            ['purchase_order_local_id' => $foreignOrderId, 'expected_field' => 'purchase_order_local_id'],
            ['event_local_id' => $foreignEventId, 'expected_field' => 'payload'],
        ];

        $applier = app(SyncMutationApplier::class);
        $handler = app(OfflineDomainMutationHandler::class);

        foreach ($cases as $index => $case) {
            $mutation = $this->recordExpense($device, array_merge([
                'description' => 'Foreign link attempt '.$index,
                'amount' => '25.00',
                'status' => 'unpaid',
                'expense_date' => now()->toDateString(),
                'supplier_local_id' => null,
                'event_local_id' => null,
                'purchase_order_local_id' => null,
            ], array_intersect_key($case, array_flip([
                'supplier_local_id',
                'event_local_id',
                'purchase_order_local_id',
            ]))));

            try {
                $applier->apply($mutation, ['expense' => $handler]);
                $this->fail('A foreign offline expense reference must be rejected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey($case['expected_field'], $exception->errors());
            }

            $this->assertSame('pending', $mutation->fresh()->status);
        }

        $this->assertSame(0, FinanceExpense::query()->where('business_id', $business->id)->count());
    }

    public function test_offline_update_rejects_an_identity_linked_to_another_business_record(): void
    {
        $business = $this->business('Identity owner business');
        $foreignBusiness = $this->business('Identity record owner business');
        $device = $this->device($business);
        $foreignCustomer = Customer::create([
            'business_id' => $foreignBusiness->id,
            'name' => 'Foreign customer must remain untouched',
        ]);
        $localId = (string) Str::uuid();

        // Simulate a stale or corrupted identity mapping. The identity row is
        // scoped to this device's business, but its morph target belongs elsewhere.
        SyncEntityIdentity::create([
            'business_id' => $business->id,
            'entity_type' => 'customer',
            'entity_uuid' => $localId,
            'record_type' => $foreignCustomer->getMorphClass(),
            'record_id' => $foreignCustomer->id,
        ]);

        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'customer',
            $localId,
            'update',
            [
                'local_id' => $localId,
                'expected_revision' => 2,
                'record' => ['name' => 'Attempted cross-business takeover'],
            ],
        );

        try {
            app(SyncMutationApplier::class)->apply($mutation, [
                'customer' => app(OfflineDomainMutationHandler::class),
            ]);
            $this->fail('An offline update must reject an identity linked to a foreign business record.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['The offline synchronization identity does not belong to this business.'],
                $exception->errors()['entity_id'] ?? [],
            );
        }

        $this->assertSame('pending', $mutation->fresh()->status);
        $this->assertSame($foreignBusiness->id, $foreignCustomer->fresh()->business_id);
        $this->assertSame('Foreign customer must remain untouched', $foreignCustomer->fresh()->name);
    }

    public function test_offline_job_rejects_a_customer_identity_with_the_wrong_record_type(): void
    {
        $business = $this->business('Typed identity business');
        $device = $this->device($business);
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Customer behind mismatched identity',
        ]);
        $customerUuid = (string) Str::uuid();

        // The identity claims to represent a job, but its numeric record ID
        // collides with a real Customer ID in the same business.
        SyncEntityIdentity::create([
            'business_id' => $business->id,
            'entity_type' => 'customer',
            'entity_uuid' => $customerUuid,
            'record_type' => (new Event)->getMorphClass(),
            'record_id' => $customer->id,
        ]);

        $localId = (string) Str::uuid();
        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'job',
            $localId,
            'create',
            [
                'local_id' => $localId,
                'record' => [
                    'name' => 'Must not attach a mismatched identity',
                    'customer_local_id' => $customerUuid,
                    'event_type' => 'Wedding',
                    'event_date' => now()->addDays(14)->toDateString(),
                    'services' => [],
                ],
            ],
        );

        try {
            app(SyncMutationApplier::class)->apply($mutation, [
                'job' => app(OfflineDomainMutationHandler::class),
            ]);
            $this->fail('A customer identity with a non-Customer morph type must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('customer_local_id', $exception->errors());
        }

        $this->assertSame('pending', $mutation->fresh()->status);
        $this->assertDatabaseMissing('events', [
            'business_id' => $business->id,
            'name' => 'Must not attach a mismatched identity',
        ]);
        $this->assertSame('Customer behind mismatched identity', $customer->fresh()->name);
    }

    public function test_related_sync_acknowledgement_rejects_identity_with_wrong_morph_type(): void
    {
        $business = $this->business('Related identity type business');
        $device = $this->device($business);
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Not an invoice',
        ]);
        $invoiceUuid = (string) Str::uuid();

        $identity = SyncEntityIdentity::create([
            'business_id' => $business->id,
            'entity_type' => 'invoice',
            'entity_uuid' => $invoiceUuid,
            'record_type' => $customer->getMorphClass(),
            'record_id' => $customer->id,
        ]);

        $this->assertFalse(app(SyncMutationRelatedIdentityResolver::class)->isSafeIdentity($identity));

        $mutationId = (string) Str::uuid();
        $paymentUuid = (string) Str::uuid();
        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'payment',
            $paymentUuid,
            'create',
            [
                'local_id' => $paymentUuid,
                'record' => ['invoice_local_id' => $invoiceUuid],
            ],
            $mutationId,
        );

        $related = app(SyncMutationRelatedIdentityResolver::class)
            ->resolve($mutation);

        $this->assertSame([], $related);
    }

    public function test_bootstrap_delivery_fallback_rejects_wrong_morph_identity_targets(): void
    {
        $business = $this->business('Bootstrap identity type business');
        $device = $this->device($business);
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Not an invoice',
        ]);
        $invoiceUuid = (string) Str::uuid();
        SyncEntityIdentity::create([
            'business_id' => $business->id,
            'entity_type' => 'invoice',
            'entity_uuid' => $invoiceUuid,
            'record_type' => $customer->getMorphClass(),
            'record_id' => $customer->id,
        ]);

        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'invoice',
            $invoiceUuid,
            'update',
            ['local_id' => $invoiceUuid, 'expected_revision' => 2, 'record' => ['number' => 'INV-INVALID']],
        );
        $mutation->update(['status' => 'applied']);
        SyncDelivery::create([
            'business_id' => $business->id,
            'sync_mutation_id' => $mutation->id,
            'destination_device_id' => $device->id,
            'stream' => 'business',
            'delivery_sequence' => 1,
        ]);

        $targets = app(SyncBootstrapDeliveryTargetResolver::class)->resolve($device, [1]);

        $this->assertSame([], $targets);
    }

    public function test_sync_snapshot_rejects_a_same_business_identity_with_wrong_morph_type(): void
    {
        $business = $this->business('Snapshot type identity business');
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'SNAPSHOT-TYPE-001',
            'name' => 'Wrong model snapshot target',
            'event_type' => 'Other',
            'event_date' => now()->addDays(10)->toDateString(),
            'status' => 'confirmed',
        ]);
        $identity = SyncEntityIdentity::create([
            'business_id' => $business->id,
            'entity_type' => 'customer',
            'entity_uuid' => (string) Str::uuid(),
            'record_type' => $event->getMorphClass(),
            'record_id' => $event->id,
        ]);

        $snapshot = app(SyncEntitySnapshotBuilder::class)->build($identity);

        $this->assertTrue($snapshot['deleted']);
        $this->assertSame([], $snapshot['record']);
    }

    public function test_sync_snapshot_does_not_serialize_a_foreign_business_record(): void
    {
        $business = $this->business('Snapshot identity business');
        $foreignBusiness = $this->business('Snapshot record owner');
        $foreignCustomer = Customer::create([
            'business_id' => $foreignBusiness->id,
            'name' => 'Private foreign customer data',
            'notes' => 'Must never appear in a conflict snapshot',
        ]);
        $identity = SyncEntityIdentity::create([
            'business_id' => $business->id,
            'entity_type' => 'customer',
            'entity_uuid' => (string) Str::uuid(),
            'record_type' => $foreignCustomer->getMorphClass(),
            'record_id' => $foreignCustomer->id,
        ]);

        $snapshot = app(SyncEntitySnapshotBuilder::class)->build($identity);

        $this->assertTrue($snapshot['deleted']);
        $this->assertSame([], $snapshot['record']);
        $this->assertSame([], $snapshot['related']);
    }

    public function test_expense_rejects_corrupted_local_identity_rows_that_point_to_foreign_records(): void
    {
        $business = $this->business('Corrupted expense identity owner');
        $foreignBusiness = $this->business('Corrupted expense identity target');
        $device = $this->device($business);

        $foreignEvent = Event::create([
            'business_id' => $foreignBusiness->id,
            'reference' => 'CORRUPT-EXPENSE-EVENT',
            'name' => 'Foreign event target',
            'status' => 'confirmed',
        ]);
        $foreignSupplier = Supplier::create([
            'business_id' => $foreignBusiness->id,
            'name' => 'Foreign supplier target',
        ]);
        $foreignOrder = PurchaseOrder::create([
            'business_id' => $foreignBusiness->id,
            'event_id' => $foreignEvent->id,
            'supplier_id' => $foreignSupplier->id,
            'idempotency_key' => (string) Str::uuid(),
            'reference' => 'PO-CORRUPT-EXPENSE',
            'status' => 'received',
            'currency' => 'ZAR',
            'total_amount' => '450.00',
        ]);

        $cases = [
            [
                'entity_type' => 'job',
                'record' => $foreignEvent,
                'field' => 'event_local_id',
                'error_field' => 'payload',
            ],
            [
                'entity_type' => 'supplier',
                'record' => $foreignSupplier,
                'field' => 'supplier_local_id',
                'error_field' => 'supplier_local_id',
            ],
            [
                'entity_type' => 'purchase_order',
                'record' => $foreignOrder,
                'field' => 'purchase_order_local_id',
                'error_field' => 'purchase_order_local_id',
            ],
        ];

        foreach ($cases as $index => $case) {
            $localIdentity = (string) Str::uuid();

            // Simulate a corrupted identity table: the identity is scoped to
            // this business and has the expected morph type, but its target
            // record belongs to a different business.
            SyncEntityIdentity::create([
                'business_id' => $business->id,
                'entity_type' => $case['entity_type'],
                'entity_uuid' => $localIdentity,
                'record_type' => $case['record']->getMorphClass(),
                'record_id' => $case['record']->id,
            ]);

            $mutation = $this->recordExpense($device, array_merge([
                'description' => 'Corrupted identity attempt '.$index,
                'amount' => '25.00',
                'status' => 'unpaid',
                'expense_date' => now()->toDateString(),
                'supplier_local_id' => null,
                'event_local_id' => null,
                'purchase_order_local_id' => null,
            ], [$case['field'] => $localIdentity]));

            try {
                app(SyncMutationApplier::class)->apply($mutation, [
                    'expense' => app(OfflineDomainMutationHandler::class),
                ]);
                $this->fail('An expense must not resolve a local identity to a foreign business record.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey($case['error_field'], $exception->errors());
            }

            $this->assertSame('pending', $mutation->fresh()->status);
        }

        $this->assertSame(0, FinanceExpense::query()->where('business_id', $business->id)->count());
        $this->assertSame('Foreign event target', $foreignEvent->fresh()->name);
        $this->assertSame('Foreign supplier target', $foreignSupplier->fresh()->name);
        $this->assertSame('PO-CORRUPT-EXPENSE', $foreignOrder->fresh()->reference);
    }

    private function business(string $name): Business
    {
        return Business::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
    }

    private function device(Business $business): SyncDevice
    {
        return SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
    }

    private function recordExpense(SyncDevice $device, array $record): SyncMutation
    {
        $localId = (string) Str::uuid();

        return app(SyncMutationRecorder::class)->record(
            $device,
            'expense',
            $localId,
            'create',
            [
                'local_id' => $localId,
                'record' => $record,
            ],
        );
    }
}
