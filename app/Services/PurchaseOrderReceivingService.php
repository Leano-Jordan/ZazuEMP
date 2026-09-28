<?php

namespace App\Services;

use App\Models\Business;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderReceipt;
use App\Support\Audit;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

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
        string $idempotencyKey
    ): bool {
        $alreadyProcessed = false;

        DB::transaction(function () use (
            $purchaseOrder,
            $businessId,
            $receivedQuantities,
            $idempotencyKey,
            &$alreadyProcessed
        ): void {
            // Keep business -> purchase order lock ordering consistent with the
            // other commercial mutation paths in Zazu.
            Business::query()
                ->whereKey($businessId)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedOrder = PurchaseOrder::query()
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->findOrFail($purchaseOrder->id);

            abort_unless(
                in_array($lockedOrder->status, ['ordered', 'received'], true),
                422,
                'Only ordered purchase orders can receive goods.'
            );

            $existingReceipt = PurchaseOrderReceipt::query()
                ->where('business_id', $businessId)
                ->where('purchase_order_id', $lockedOrder->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingReceipt) {
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
                $receivedHundredths = Money::toHundredths((string) ($received ?: '0'));

                if ($receivedHundredths <= 0) {
                    continue;
                }

                /** @var PurchaseOrderItem|null $item */
                $item = $lockedOrder->items->firstWhere('id', (int) $itemId);

                abort_unless(
                    $item
                    && (int) $item->business_id === $businessId
                    && (int) $item->purchase_order_id === (int) $lockedOrder->id,
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
                    'idempotency_key' => $idempotencyKey,
                    'purchase_order_id' => $lockedOrder->id,
                    'purchase_order_item_id' => $item->id,
                    'type' => 'receipt',
                    'quantity' => $received,
                    'unit_cost' => $item->unit_price,
                    'movement_date' => now()->toDateString(),
                    'reference' => $lockedOrder->reference,
                    'notes' => 'Receipt from purchase order.',
                ]);

                $item->update([
                    'received_quantity' => Money::fromCents(
                        $alreadyReceivedHundredths + $receivedHundredths
                    ),
                ]);
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
