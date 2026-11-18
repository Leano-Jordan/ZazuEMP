<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\InvoiceNumberSequence;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use LogicException;

final class InvoiceNumberGenerator
{
    public function nextForBusiness(int $businessId, ?DateTimeInterface $issuedAt = null): string
    {
        if (DB::transactionLevel() < 1) {
            throw new LogicException('Invoice numbers must be allocated inside the invoice creation transaction.');
        }

        Business::query()
            ->whereKey($businessId)
            ->lockForUpdate()
            ->firstOrFail();

        $period = ($issuedAt ?? now())->format('Ym');
        $prefix = 'INV-'.$period.'-';

        $sequence = InvoiceNumberSequence::query()
            ->where('business_id', $businessId)
            ->where('period', $period)
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            $sequence = InvoiceNumberSequence::create([
                'business_id' => $businessId,
                'period' => $period,
                'next_number' => 1,
            ]);
        }

        // Reconcile the counter against existing records so imports and restores
        // from installations predating this sequence table cannot reuse a number.
        $nextNumber = max(
            (int) $sequence->next_number,
            $this->nextAfterExistingInvoices($businessId, $prefix),
        );

        $sequence->update(['next_number' => $nextNumber + 1]);

        return $prefix.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    private function nextAfterExistingInvoices(int $businessId, string $prefix): int
    {
        $pattern = '/^'.preg_quote($prefix, '/').'([0-9]{6,})$/D';
        $highest = 0;

        $numbers = Invoice::query()
            ->where('business_id', $businessId)
            ->where('number', 'like', $prefix.'%')
            ->pluck('number');

        foreach ($numbers as $number) {
            if (preg_match($pattern, (string) $number, $matches) === 1) {
                $highest = max($highest, (int) $matches[1]);
            }
        }

        return $highest + 1;
    }
}
