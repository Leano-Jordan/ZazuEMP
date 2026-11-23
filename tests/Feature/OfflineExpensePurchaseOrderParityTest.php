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

class OfflineExpensePurchaseOrderParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_expense_inherits_its_event_and_supplier(): void
    {
        $fixture = $this->fixture();
        $mutation = $this->recordExpense($fixture['device'], $this->expenseRecord($fixture['order_id']));
        $applied = $this->apply($mutation);
        $expense = FinanceExpense::query()
            ->where('business_id', $fixture['business']->id)
            ->where('idempotency_key', $mutation->mutation_id)
            ->firstOrFail();

        $this->assertSame('applied', $applied->status);
        $this->assertSame($fixture['event']->id, $expense->event_id);
        $this->assertSame($fixture['supplier']->id, $expense->supplier_id);
        $this->assertSame($fixture['order']->id, $expense->purchase_order_id);
    }

    public function test_purchase_order_expense_rejects_a_different_event(): void
    {
        $fixture = $this->fixture();
        $record = $this->expenseRecord($fixture['order_id']);
        $record['event_local_id'] = $fixture['other_event_id'];

        $this->assertRejected($fixture['device'], $record, 'purchase_order_local_id');
        $this->assertSame(0, FinanceExpense::query()->where('business_id', $fixture['business']->id)->count());
    }

    public function test_purchase_order_expense_rejects_a_different_supplier(): void
    {
        $fixture = $this->fixture();
        $record = $this->expenseRecord($fixture['order_id']);
        $record['supplier_local_id'] = $fixture['other_supplier_id'];

        $this->assertRejected($fixture['device'], $record, 'supplier_local_id');
        $this->assertSame(0, FinanceExpense::query()->where('business_id', $fixture['business']->id)->count());
    }

    public function test_purchase_order_expense_rejects_a_draft_order(): void
    {
        $fixture = $this->fixture('draft');

        $this->assertRejected(
            $fixture['device'],
            $this->expenseRecord($fixture['order_id']),
            'purchase_order_local_id',
        );
        $this->assertSame(0, FinanceExpense::query()->where('business_id', $fixture['business']->id)->count());
    }

    private function fixture(string $orderStatus = 'received'): array
    {
        $business = $this->business('Expense purchase order parity business');
        $device = $this->device($business);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'EXPENSE-PARITY-EVENT',
            'name' => 'Expense parity event',
            'status' => 'confirmed',
        ]);
        $otherEvent = Event::create([
            'business_id' => $business->id,
            'reference' => 'EXPENSE-OTHER-EVENT',
            'name' => 'Different event',
            'status' => 'confirmed',
        ]);
        $supplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'Purchase order supplier',
        ]);
        $otherSupplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'Different supplier',
        ]);
        $order = PurchaseOrder::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'supplier_id' => $supplier->id,
            'idempotency_key' => (string) Str::uuid(),
            'reference' => 'PO-EXPENSE-PARITY',
            'status' => $orderStatus,
            'currency' => 'ZAR',
            'total_amount' => '450.00',
        ]);

        $identities = app(SyncEntityIdentityRegistry::class);

        return [
            'business' => $business,
            'device' => $device,
            'event' => $event,
            'supplier' => $supplier,
            'order' => $order,
            'order_id' => $identities->identify($order, 'purchase_order')->entity_uuid,
            'other_event_id' => $identities->identify($otherEvent, 'job')->entity_uuid,
            'other_supplier_id' => $identities->identify($otherSupplier, 'supplier')->entity_uuid,
        ];
    }

    private function expenseRecord(string $orderId): array
    {
        return [
            'description' => 'Expense linked to purchase order',
            'amount' => '25.00',
            'status' => 'unpaid',
            'expense_date' => now()->toDateString(),
            'supplier_local_id' => null,
            'event_local_id' => null,
            'purchase_order_local_id' => $orderId,
        ];
    }

    private function assertRejected(SyncDevice $device, array $record, string $field): void
    {
        $mutation = $this->recordExpense($device, $record);

        try {
            $this->apply($mutation);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
            $this->assertSame('pending', $mutation->fresh()->status);

            return;
        }

        $this->fail('An invalid offline purchase-order expense link must be rejected.');
    }

    private function apply(SyncMutation $mutation): SyncMutation
    {
        return app(SyncMutationApplier::class)->apply($mutation, [
            'expense' => app(OfflineDomainMutationHandler::class),
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
            ['local_id' => $localId, 'record' => $record],
        );
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
}
