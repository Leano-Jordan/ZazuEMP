<?php

namespace App\Services;

use App\Models\Business;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderReceipt;
use App\Support\Audit;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PurchaseOrderReceivingService
{
    /**
     * Record one receipt operation atomically.
     *
     * Returns true when the supplied idempotency key had already been processed.
     */
    public function receive(
        PurchaseOrder $purchaseOrder,
        int $businessId,
        array $receivedQuantities,
        string $idempotencyKey,
        EventLifecycleService $lifecycle
    ): bool {
        $alreadyProcessed = false;

        DB::transaction(function () use (
            $purchaseOrder,
            $businessId,
            $receivedQuantities,
            $idempotencyKey,
            $lifecycle,
            &$alreadyProcessed
        ): void {
            $this->lockBusiness($businessId);

            $eventId = PurchaseOrder::query()
                ->where('business_id', $businessId)
                ->whereKey($purchaseOrder->id)
                ->value('event_id');

            $lockedEvent = $eventId
                ? $lifecycle->lock($businessId, (int) $eventId)
                : null;

            $lockedOrder = $this->lockOrder($businessId, $purchaseOrder->id);

            if ($lockedEvent) {
                $lifecycle->assertOperational($lockedEvent);
            }

            $this->assertReceivable($lockedOrder);

            if ($this->receiptAlreadyProcessed($businessId, $lockedOrder->id, $idempotencyKey)) {
                $alreadyProcessed = true;
                return;
            }

            PurchaseOrderReceipt::create([
                'business_id' => $businessId,
                'purchase_order_id' => $lockedOrder->id,
                'idempotency_key' => $idempotencyKey,
            ]);

            $lockedOrder->load('items');

            foreach ($receivedQuantities as $itemId => $received) {
                $this->receiveLine(
                    $lockedOrder,
                    $lockedEvent?->id,
                    $businessId,
                    (int) $itemId,
                    (string) ($received ?: '0')
                );
            }

            $lockedOrder->update([
                'status' => $lockedOrder->isFullyReceived() ? 'received' : 'ordered',
                'last_receipt_idempotency_key' => $idempotencyKey,
            ]);

            Audit::record('purchasing.order.received', $lockedOrder, [
                'fully_received' => $lockedOrder->status === 'received',
                'receipt_idempotency_key' => $idempotencyKey,
            ], $businessId);
        });

        return $alreadyProcessed;
    }

    private function lockBusiness(int $businessId): void
    {
        Business::query()
            ->whereKey($businessId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function lockOrder(int $businessId, int $purchaseOrderId): PurchaseOrder
    {
        return PurchaseOrder::query()
            ->where('business_id', $businessId)
            ->lockForUpdate()
            ->findOrFail($purchaseOrderId);
    }

    private function assertReceivable(PurchaseOrder $purchaseOrder): void
    {
        abort_unless(
            in_array($purchaseOrder->status, ['ordered', 'received'], true),
            422,
            'Only ordered purchase orders can receive goods.'
        );
    }

    private function receiptAlreadyProcessed(
        int $businessId,
        int $purchaseOrderId,
        string $idempotencyKey
    ): bool {
        return PurchaseOrderReceipt::query()
            ->where('business_id', $businessId)
            ->where('purchase_order_id', $purchaseOrderId)
            ->where('idempotency_key', $idempotencyKey)
            ->exists();
    }

    private function receiveLine(
        PurchaseOrder $purchaseOrder,
        ?int $eventId,
        int $businessId,
        int $itemId,
        string $received
    ): void {
        $receivedHundredths = Money::toHundredths($received);

        if ($receivedHundredths <= 0) {
            return;
        }

        /** @var PurchaseOrderItem|null $item */
        $item = $purchaseOrder->items->firstWhere('id', $itemId);

        abort_unless(
            $item
            && (int) $item->business_id === $businessId
            && (int) $item->purchase_order_id === (int) $purchaseOrder->id,
            409,
            'Purchase order line integrity could not be verified.'
        );

        $orderedHundredths = Money::toHundredths((string) $item->quantity);
        $alreadyReceivedHundredths = Money::toHundredths((string) $item->received_quantity);

        abort_if(
            $alreadyReceivedHundredths + $receivedHundredths > $orderedHundredths,
            422,
            'Received quantity cannot exceed the ordered quantity.'
        );

        $inventoryItem = $this->findInventoryItem($item, $businessId);

        if (!$inventoryItem) {
            $inventoryItem = InventoryItem::create([
                'business_id' => $businessId,
                'capability_id' => $item->capability_id,
                'name' => $item->description,
                'unit' => $item->unit ?: 'unit',
                'reorder_level' => 0,
            ]);
        }

        $inventoryItem->movements()->create([
            'business_id' => $businessId,
            'idempotency_key' => (string) Str::uuid(),
            'purchase_order_id' => $purchaseOrder->id,
            'purchase_order_item_id' => $item->id,
            'event_id' => $eventId,
            'type' => 'receipt',
            'quantity' => $received,
            'unit_cost' => $item->unit_price,
            'movement_date' => now()->toDateString(),
            'reference' => $purchaseOrder->reference,
            'notes' => 'Receipt from purchase order.',
        ]);

        $item->update([
            'received_quantity' => Money::fromCents(
                $alreadyReceivedHundredths + $receivedHundredths
            ),
        ]);
    }

    private function findInventoryItem(PurchaseOrderItem $item, int $businessId): ?InventoryItem
    {
        return InventoryItem::query()
            ->where('business_id', $businessId)
            ->when(
                $item->capability_id,
                fn ($query) => $query->where('capability_id', $item->capability_id)
            )
            ->where(function ($query) use ($item): void {
                $query->where('name', $item->description)
                    ->orWhere('sku', $item->description);
            })
            ->first();
    }
}
