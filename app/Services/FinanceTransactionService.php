<?php

namespace App\Services;

use App\Models\Business;
use App\Models\FinanceExpense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Support\Audit;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

final class FinanceTransactionService
{
    /**
     * Record a customer payment atomically with invoice reconciliation.
     *
     * @param array<string, mixed> $data
     */
    public function recordPayment(
        int $businessId,
        array $data,
        EventLifecycleService $lifecycle
    ): bool {
        return DB::transaction(function () use ($businessId, $data, $lifecycle): bool {
            $this->lockBusiness($businessId);

            if (Payment::query()
                ->where('business_id', $businessId)
                ->where('idempotency_key', $data['idempotency_key'])
                ->exists()) {
                return true;
            }

            $invoiceEventId = Invoice::query()
                ->where('business_id', $businessId)
                ->whereKey($data['invoice_id'])
                ->value('event_id');

            $lockedEvent = $invoiceEventId
                ? $lifecycle->lock($businessId, (int) $invoiceEventId)
                : null;

            if ($lockedEvent) {
                $lifecycle->assertFinanciallyActive($lockedEvent);
            }

            $invoice = Invoice::query()
                ->where('business_id', $businessId)
                ->whereKey($data['invoice_id'])
                ->with(['payments', 'quoteVersion'])
                ->lockForUpdate()
                ->firstOrFail();

            abort_if(
                !in_array($invoice->status, ['issued'], true),
                422,
                'Only issued invoices can accept payments.'
            );

            abort_if(
                $lockedEvent
                    && (int) $invoice->event_id !== (int) $lockedEvent->id,
                409,
                'The invoice job changed while the payment was being prepared. Please retry.'
            );

            abort_if(
                $invoice->payments->contains(
                    fn (Payment $payment): bool => strtoupper((string) $payment->currency) !== strtoupper((string) $invoice->currency)
                ),
                409,
                'This invoice contains a payment recorded in a different currency. Review the payment history before continuing.'
            );

            $totalCents = Money::toCents((string) $invoice->total);
            $paidCents = $invoice->payments->sum(
                fn (Payment $payment): int => Money::toCents((string) $payment->amount)
            );
            $paymentCents = Money::toCents((string) $data['amount']);

            abort_if(
                $paymentCents > ($totalCents - $paidCents),
                422,
                'Payment cannot exceed the outstanding invoice balance.'
            );

            if ($data['type'] === 'deposit') {
                $depositRequiredCents = Money::toCents((string) ($invoice->quoteVersion?->deposit_amount ?? '0.00'));
                $depositPaidCents = $invoice->payments
                    ->where('type', 'deposit')
                    ->sum(fn (Payment $payment): int => Money::toCents((string) $payment->amount));

                abort_if($depositRequiredCents <= 0, 422, 'This invoice has no deposit requirement.');
                abort_if(
                    $depositPaidCents + $paymentCents > $depositRequiredCents,
                    422,
                    'This deposit would exceed the required deposit amount.'
                );
            }

            $payment = Payment::create([
                ...$data,
                'business_id' => $businessId,
                'event_id' => $invoice->event_id,
                'currency' => strtoupper((string) $invoice->currency),
            ]);

            $newPaidCents = $paidCents + $paymentCents;

            $invoice->update([
                'status' => $newPaidCents >= $totalCents ? 'paid' : 'issued',
            ]);

            Audit::record('finance.payment.recorded', $payment, [
                'invoice_id' => $invoice->id,
                'event_id' => $invoice->event_id,
                'amount' => $data['amount'],
                'type' => $data['type'],
                'currency' => $invoice->currency,
                'method' => $data['method'],
            ], $businessId);

            return false;
        });
    }

    /**
     * Record an expense atomically with its business-bound relationships.
     *
     * @param array<string, mixed> $data
     */
    public function recordExpense(
        int $businessId,
        string $currency,
        array $data,
        EventLifecycleService $lifecycle
    ): bool {
        return DB::transaction(function () use ($businessId, $currency, $data, $lifecycle): bool {
            $this->lockBusiness($businessId);

            $existingExpense = FinanceExpense::query()
                ->where('business_id', $businessId)
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existingExpense) {
                return true;
            }

            $eventId = !empty($data['event_id']) ? (int) $data['event_id'] : null;
            $purchaseOrderId = !empty($data['purchase_order_id']) ? (int) $data['purchase_order_id'] : null;

            $purchaseOrder = null;

            if ($purchaseOrderId !== null) {
                $purchaseOrder = PurchaseOrder::query()
                    ->where('business_id', $businessId)
                    ->whereKey($purchaseOrderId)
                    ->firstOrFail();

                abort_unless(
                    in_array($purchaseOrder->status, ['ordered', 'received', 'cancelled'], true),
                    422,
                    'An expense can only be linked to an ordered, received or cancelled purchase order.'
                );

                if ($eventId === null && $purchaseOrder->event_id !== null) {
                    $eventId = (int) $purchaseOrder->event_id;
                    $data['event_id'] = $eventId;
                }

                abort_if(
                    $eventId !== null
                        && $purchaseOrder->event_id !== null
                        && $eventId !== (int) $purchaseOrder->event_id,
                    422,
                    'The selected purchase order and job do not match.'
                );
            }

            $lockedEvent = $eventId !== null
                ? $lifecycle->lock($businessId, $eventId)
                : null;

            if ($lockedEvent) {
                // Expenses are financial records. They may still be reconciled after
                // operational completion, but never against cancelled work.
                $lifecycle->assertFinanciallyActive($lockedEvent);
            }

            $lockedPurchaseOrder = $purchaseOrderId !== null
                ? PurchaseOrder::query()
                    ->where('business_id', $businessId)
                    ->lockForUpdate()
                    ->findOrFail($purchaseOrderId)
                : null;

            if ($lockedPurchaseOrder) {
                abort_unless(
                    in_array($lockedPurchaseOrder->status, ['ordered', 'received', 'cancelled'], true),
                    422,
                    'An expense can only be linked to an ordered, received or cancelled purchase order.'
                );

                abort_if(
                    $lockedPurchaseOrder->event_id !== null
                        && $eventId !== null
                        && (int) $lockedPurchaseOrder->event_id !== $eventId,
                    409,
                    'The selected purchase order and job do not match.'
                );
            }

            $supplierId = !empty($data['supplier_id']) ? (int) $data['supplier_id'] : null;

            if ($supplierId !== null) {
                Supplier::query()
                    ->where('business_id', $businessId)
                    ->whereKey($supplierId)
                    ->firstOrFail();
            }

            if (
                $lockedPurchaseOrder
                && $supplierId !== null
                && (int) $lockedPurchaseOrder->supplier_id !== $supplierId
            ) {
                abort(422, 'The selected supplier does not match the purchase order supplier.');
            }

            if ($lockedPurchaseOrder && $supplierId === null) {
                $data['supplier_id'] = $lockedPurchaseOrder->supplier_id;
            }

            $expense = FinanceExpense::create([
                ...$data,
                'business_id' => $businessId,
                'event_id' => $eventId,
                'purchase_order_id' => $purchaseOrderId,
                'currency' => strtoupper($currency),
            ]);

            Audit::record('finance.expense.recorded', $expense, [
                'amount' => $expense->amount,
                'currency' => $expense->currency,
                'status' => $expense->status,
                'event_id' => $expense->event_id,
                'purchase_order_id' => $expense->purchase_order_id,
            ], $businessId);

            return false;
        });
    }

    private function lockBusiness(int $businessId): void
    {
        Business::query()
            ->whereKey($businessId)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
