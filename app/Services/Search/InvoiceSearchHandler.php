<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;

class InvoiceSearchHandler extends AbstractWorkspaceSearchHandler
{
    public function search(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = Invoice::query()->where('business_id', $business->id);
        $this->text($query, $term, ['number', 'customer_name', 'customer_email', 'customer_phone', 'business_legal_name', 'business_trading_name']);
        $this->applyStatusAndDate($query, $status, $from, $to, 'issued_at');

        return $query->latest('issued_at')->limit(10)->get()->map(fn (Invoice $invoice) => [
            'type' => 'invoice',
            'type_label' => 'Invoice',
            'title' => $invoice->number,
            'meta' => trim(($invoice->customer_name ?: 'Customer').' · '.($invoice->currency ?: '')),
            'status' => $invoice->status,
            'date' => $invoice->issued_at?->format('d M Y'),
            'href' => route('finance.invoices.show', $invoice),
            'sort_date' => ($invoice->issued_at ?: $invoice->created_at)?->toDateString(),
        ])->all();
    }
}
