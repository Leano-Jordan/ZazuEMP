<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Support\Money;

final class InvoiceIdempotencyMatcher
{
    /**
     * @param array<string, mixed> $intent
     */
    public function matches(Invoice $invoice, array $intent): bool
    {
        return $this->contextMatches($invoice, $intent)
            && $this->totalsMatch($invoice, $intent)
            && $this->datesAndNotesMatch($invoice, $intent)
            && $this->itemsMatch($invoice, $intent['lines'] ?? []);
    }

    /**
     * @param array<string, mixed> $intent
     */
    private function contextMatches(Invoice $invoice, array $intent): bool
    {
        return (int) ($invoice->event_id ?? 0) === (int) ($intent['event_id'] ?? 0)
            && (int) ($invoice->quote_id ?? 0) === (int) ($intent['quote_id'] ?? 0)
            && (int) ($invoice->quote_version_id ?? 0) === (int) ($intent['quote_version_id'] ?? 0)
            && strtoupper((string) $invoice->currency) === strtoupper((string) ($intent['currency'] ?? ''));
    }

    /**
     * @param array<string, mixed> $intent
     */
    private function totalsMatch(Invoice $invoice, array $intent): bool
    {
        return Money::toCents((string) $invoice->subtotal) === Money::toCents((string) $intent['subtotal'])
            && Money::toCents((string) $invoice->tax_total) === Money::toCents((string) $intent['tax_total'])
            && Money::toCents((string) $invoice->total) === Money::toCents((string) $intent['total'])
            && (int) ($invoice->tax_rate_id ?? 0) === (int) ($intent['tax_rate_id'] ?? 0)
            && $invoice->tax_code === ($intent['tax_code'] ?? null)
            && $invoice->tax_label === ($intent['tax_label'] ?? null)
            && $invoice->tax_treatment === ($intent['tax_treatment'] ?? null)
            && Money::toCents((string) $invoice->tax_rate) === Money::toCents((string) $intent['tax_rate']);
    }

    /**
     * @param array<string, mixed> $intent
     */
    private function datesAndNotesMatch(Invoice $invoice, array $intent): bool
    {
        return (! isset($intent['issued_at']) || $invoice->issued_at?->toDateString() === $intent['issued_at'])
            && (! isset($intent['due_at']) || $invoice->due_at?->toDateString() === $intent['due_at'])
            && $invoice->notes === ($intent['notes'] ?? null);
    }

    /**
     * @param array<int, array<string, mixed>> $lines
     */
    private function itemsMatch(Invoice $invoice, array $lines): bool
    {
        $existingLines = $invoice->items()->orderBy('id')->get();

        if ($existingLines->count() !== count($lines)) {
            return false;
        }

        foreach ($lines as $index => $line) {
            if (! $this->itemMatches($existingLines[$index], $line)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $line
     */
    private function itemMatches(InvoiceItem $existing, array $line): bool
    {
        return $existing->description === $line['description']
            && Money::toHundredths((string) $existing->quantity) === Money::toHundredths((string) $line['quantity'])
            && $existing->unit === $line['unit']
            && Money::toCents((string) $existing->unit_price) === Money::toCents((string) $line['unit_price'])
            && Money::toCents((string) $existing->line_total) === Money::toCents((string) $line['line_total'])
            && (int) ($existing->quote_item_id ?? 0) === (int) ($line['quote_item_id'] ?? 0);
    }
}
