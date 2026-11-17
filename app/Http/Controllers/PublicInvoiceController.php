<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicInvoiceController extends Controller
{
    /**
     * Display an issued invoice through its expiring signed customer link.
     * Only the invoice snapshot and line items are exposed; payment records,
     * internal notes and business workspace data remain private.
     */
    public function show(Invoice $invoice): Response
    {
        abort_unless(in_array($invoice->status, ['issued', 'paid'], true), 404);

        $invoice->load('items');

        return response()
            ->view('finance.invoice-public', compact('invoice'))
            ->header('Cache-Control', 'private, no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Referrer-Policy', 'no-referrer');
    }
}
