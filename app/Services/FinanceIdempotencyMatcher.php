<?php

namespace App\Services;

use App\Models\FinanceExpense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Support\Money;

final class FinanceIdempotencyMatcher
{
    /**
     * @param array<string, mixed> $data
     */
    public function paymentMatches(Payment $payment, int $businessId, array $data): bool
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

    /**
     * @param array<string, mixed> $data
     */
    public function expenseMatches(
        FinanceExpense $expense,
        int $businessId,
        string $currency,
        array $data
    ): bool {
        $links = $this->expectedExpenseLinks($businessId, $data);

        return $links !== null
            && $this->expenseLinksMatch($expense, $links)
            && $this->expenseFinancialDetailsMatch($expense, $currency, $data)
            && $this->expenseMetadataMatches($expense, $data);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{event_id:?int,purchase_order_id:?int,supplier_id:?int}|null
     */
    private function expectedExpenseLinks(int $businessId, array $data): ?array
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
}
