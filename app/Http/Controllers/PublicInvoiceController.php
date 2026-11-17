<?php

namespace App\\Http\\Controllers;

use App\\Models\\Invoice;
use Illuminate\\View\\View;

class PublicInvoiceController extends Controller
{
    /**
     * Display an issued invoice through its expiring signed customer link.
     * Only the invoice snapshot and line items are exposed; payment records,
     * internal notes and business workspace data remain private.
     */
    public function show(Invoice $invoice): View
    {
        abort_unless(in_array($invoice->status, ['issued', 'paid'], true), 404);

        $invoice->load('items');

        return view('finance.invoice-public', compact('invoice'));
    }
}
