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

            $existingPayment = $this->existingPayment($businessId, $data['idempotency_key']);

            if ($existingPayment) {
                abort_unless(
                    $this->paymentPayloadMatches($existingPayment, $businessId, $data),
                    409,
                    'This payment key was already used for a different payment. Refresh the form before retrying.'
                );

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

            $invoice = $this->lockInvoice($businessId, (int) $data['invoice_id']);
            $this->assertPaymentContext($invoice, $lockedEvent, $data);

            $paymentCents = Money::toCents((string) $data['amount']);
            $paidCents = $this->paidCents($invoice);
            $totalCents = Money::toCents((string) $invoice->total);

            $this->assertPaymentAmount($invoice, $paymentCents, $paidCents, $totalCents);
            $this->assertDepositAmount($invoice, $data, $paymentCents);

            $payment = Payment::create([
                ...$data,
                'business_id' => $businessId,
                'event_id' => $invoice->event_id,
                'currency' => strtoupper((string) $invoice->currency),
            ]);

            $invoice->update([
                'status' => $paidCents + $paymentCents >= $totalCents ? 'paid' : 'issued',
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

            $existingExpense = $this->existingExpense($businessId, $data['idempotency_key']);

            if ($existingExpense) {
                abort_unless(
                    $this->expensePayloadMatches($existingExpense, $businessId, $currency, $data),
                    409,
                    'This expense key was already used for different expense details. Refresh the form before retrying.'
                );

                return true;
            }

            [$eventId, $purchaseOrderId] = $this->resolveExpenseLinks($businessId, $data);
            $lockedEvent = $eventId !== null
                ? $lifecycle->lock($businessId, $eventId)
                : null;

            if ($lockedEvent) {
                $lifecycle->assertFinanciallyActive($lockedEvent);
            }

            $lockedPurchaseOrder = $this->lockPurchaseOrder($businessId, $purchaseOrderId, $eventId);
            $data = $this->validateExpenseSupplier($businessId, $data, $lockedPurchaseOrder);

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

    private function existingPayment(int $businessId, string $idempotencyKey): ?Payment
    {
        return Payment::query()
            ->where('business_id', $businessId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function paymentPayloadMatches(Payment $payment, int $businessId, array $data): bool
    {
        $invoice = Invoice::query()
            ->where('business_id', $businessId)
            ->find($data['invoice_id']);

        return $invoice
            && $this->paymentInvoiceMatches($payment, $invoice)
            && $this->paymentDetailsMatch($payment, $data);
    }

    private function paymentInvoiceMatches(Payment $payment, Invoice $invoice): bool
    {
        return (int) $payment->invoice_id === (int) $invoice->id
            && (int) $payment->event_id === (int) $invoice->event_id
            && strtoupper((string) $payment->currency) === strtoupper((string) $invoice->currency);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function paymentDetailsMatch(Payment $payment, array $data): bool
    {
        return $payment->type === ($data['type'] ?? 'payment')
            && Money::toCents((string) $payment->amount) === Money::toCents((string) $data['amount'])
            && $payment->method === $data['method']
            && $payment->reference === ($data['reference'] ?? null)
            && $payment->paid_at?->toDateString() === date('Y-m-d', strtotime((string) $data['paid_at']))
            && $payment->notes === ($data['notes'] ?? null);
    }

    private function existingExpense(int $businessId, string $idempotencyKey): ?FinanceExpense
    {
        return FinanceExpense::query()
            ->where('business_id', $businessId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function expensePayloadMatches(
        FinanceExpense $expense,
        int $businessId,
        string $currency,
        array $data
    ): bool {
        $links = $this->expectedExpenseRetryLinks($businessId, $data);

        return $links !== null
            && $this->expenseLinksMatch($expense, $links)
            && $this->expenseFinancialDetailsMatch($expense, $currency, $data)
            && $this->expenseMetadataMatches($expense, $data);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{event_id:?int,purchase_order_id:?int,supplier_id:?int}|null
     */
    private function expectedExpenseRetryLinks(int $businessId, array $data): ?array
    {
        $eventId = ! empty($data['event_id']) ? (int) $data['event_id'] : null;
        $purchaseOrderId = ! empty($data['purchase_order_id']) ? (int) $data['purchase_order_id'] : null;
        $supplierId = ! empty($data['supplier_id']) ? (int) $data['supplier_id'] : null;

        if ($purchaseOrderId !== null) {
            $purchaseOrder = PurchaseOrder::query()
                ->where('business_id', $businessId)
                ->find($purchaseOrderId);

            if (! $purchaseOrder) {
                return null;
            }

            $eventId ??= $purchaseOrder->event_id ? (int) $purchaseOrder->event_id : null;
            $supplierId ??= (int) $purchaseOrder->supplier_id;
        }

        return [
            'event_id' => $eventId,
            'purchase_order_id' => $purchaseOrderId,
            'supplier_id' => $supplierId,
        ];
    }

    /**
     * @param array{event_id:?int,purchase_order_id:?int,supplier_id:?int} $links
     */
    private function expenseLinksMatch(FinanceExpense $expense, array $links): bool
    {
        return (int) ($expense->event_id ?? 0) === (int) ($links['event_id'] ?? 0)
            && (int) ($expense->purchase_order_id ?? 0) === (int) ($links['purchase_order_id'] ?? 0)
            && (int) ($expense->supplier_id ?? 0) === (int) ($links['supplier_id'] ?? 0);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function expenseFinancialDetailsMatch(FinanceExpense $expense, string $currency, array $data): bool
    {
        return $expense->description === (string) $data['description']
            && Money::toCents((string) $expense->amount) === Money::toCents((string) $data['amount'])
            && strtoupper((string) $expense->currency) === strtoupper($currency)
            && $expense->expense_date?->toDateString() === date('Y-m-d', strtotime((string) $data['expense_date']));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function expenseMetadataMatches(FinanceExpense $expense, array $data): bool
    {
        return $expense->status === $data['status']
            && $expense->reference === ($data['reference'] ?? null)
            && $expense->notes === ($data['notes'] ?? null);
    }

    private function lockInvoice(int $businessId, int $invoiceId): Invoice
    {
        return Invoice::query()
            ->where('business_id', $businessId)
            ->whereKey($invoiceId)
            ->with(['payments', 'quoteVersion'])
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function assertPaymentContext(Invoice $invoice, ?object $lockedEvent, array $data): void
    {
        abort_if(
            $invoice->status !== 'issued',
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
    }

    private function paidCents(Invoice $invoice): int
    {
        return $invoice->payments->sum(
            fn (Payment $payment): int => Money::toCents((string) $payment->amount)
        );
    }

    private function assertPaymentAmount(Invoice $invoice, int $paymentCents, int $paidCents, int $totalCents): void
    {
        abort_if(
            $paymentCents > ($totalCents - $paidCents),
            422,
            'Payment cannot exceed the outstanding invoice balance.'
        );
    }

    private function assertDepositAmount(Invoice $invoice, array $data, int $paymentCents): void
    {
        if ($data['type'] !== 'deposit') {
            return;
        }

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

    /**
     * @return array{0:int|null,1:int|null}
     */
    private function resolveExpenseLinks(int $businessId, array &$data): array
    {
        $eventId = !empty($data['event_id']) ? (int) $data['event_id'] : null;
        $purchaseOrderId = !empty($data['purchase_order_id']) ? (int) $data['purchase_order_id'] : null;

        if ($purchaseOrderId === null) {
            return [$eventId, null];
        }

        $purchaseOrder = PurchaseOrder::query()
            ->where('business_id', $businessId)
            ->whereKey($purchaseOrderId)
            ->firstOrFail();

        $this->assertPurchaseOrderExpenseStatus($purchaseOrder->status);

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

        return [$eventId, $purchaseOrderId];
    }

    private function lockPurchaseOrder(int $businessId, ?int $purchaseOrderId, ?int $eventId): ?PurchaseOrder
    {
        if ($purchaseOrderId === null) {
            return null;
        }

        $purchaseOrder = PurchaseOrder::query()
            ->where('business_id', $businessId)
            ->lockForUpdate()
            ->findOrFail($purchaseOrderId);

        $this->assertPurchaseOrderExpenseStatus($purchaseOrder->status);

        abort_if(
            $purchaseOrder->event_id !== null
                && $eventId !== null
                && (int) $purchaseOrder->event_id !== $eventId,
            409,
            'The selected purchase order and job do not match.'
        );

        return $purchaseOrder;
    }

    private function assertPurchaseOrderExpenseStatus(string $status): void
    {
        abort_unless(
            in_array($status, ['ordered', 'received', 'cancelled'], true),
            422,
            'An expense can only be linked to an ordered, received or cancelled purchase order.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validateExpenseSupplier(int $businessId, array $data, ?PurchaseOrder $purchaseOrder): array
    {
        $supplierId = !empty($data['supplier_id']) ? (int) $data['supplier_id'] : null;

        if ($supplierId !== null) {
            Supplier::query()
                ->where('business_id', $businessId)
                ->whereKey($supplierId)
                ->firstOrFail();
        }

        if (
            $purchaseOrder
            && $supplierId !== null
            && (int) $purchaseOrder->supplier_id !== $supplierId
        ) {
            abort(422, 'The selected supplier does not match the purchase order supplier.');
        }

        if ($purchaseOrder && $supplierId === null) {
            $data['supplier_id'] = $purchaseOrder->supplier_id;
        }

        return $data;
    }

    private function lockBusiness(int $businessId): void
    {
        Business::query()
            ->whereKey($businessId)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
