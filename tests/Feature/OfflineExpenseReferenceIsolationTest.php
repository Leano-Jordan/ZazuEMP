<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Event;
use App\Models\FinanceExpense;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SyncDevice;
use App\Models\SyncMutation;
use App\Support\Offline\OfflineDomainMutationHandler;
use App\Support\Offline\SyncEntityIdentityRegistry;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationRecorder;
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

        $this->assertSame(0, \App\Models\EventCost::query()
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
