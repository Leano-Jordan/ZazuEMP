<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Event;
use App\Models\FinanceExpense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Supplier;
use App\Support\Audit;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class FinanceTransactionService
{
    /**
     * Record a customer payment atomically with invoice reconciliation.
     *
     * @param array<string, mixed> $data
     */
    public function recordPayment(int $businessId, array $data): bool
    {
        return DB::transaction(function () use ($businessId, $data): bool {
            $this->lockBusiness($businessId);

            if (Payment::query()
                ->where('business_id', $businessId)
                ->where('idempotency_key', $data['idempotency_key'])
                ->exists()) {
                return true;
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
    public function recordExpense(int $businessId, string $currency, array $data): bool
    {
        return DB::transaction(function () use ($businessId, $currency, $data): bool {
            $this->lockBusiness($businessId);

            $existingExpense = FinanceExpense::query()
                ->where('business_id', $businessId)
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existingExpense) {
                return true;
            }

            if (!empty($data['supplier_id'])) {
                Supplier::query()
                    ->where('business_id', $businessId)
                    ->whereKey($data['supplier_id'])
                    ->firstOrFail();
            }

            if (!empty($data['event_id'])) {
                Event::query()
                    ->where('business_id', $businessId)
                    ->whereKey($data['event_id'])
                    ->firstOrFail();
            }

            $expense = FinanceExpense::create([
                ...$data,
                'business_id' => $businessId,
                'currency' => strtoupper($currency),
            ]);

            Audit::record('finance.expense.recorded', $expense, [
                'amount' => $expense->amount,
                'currency' => $expense->currency,
                'status' => $expense->status,
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
