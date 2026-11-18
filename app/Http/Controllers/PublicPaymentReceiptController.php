<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Response;

class PublicPaymentReceiptController extends Controller
{
    public function show(Payment $payment): Response
    {
        $payment->load('invoice');
        $invoice = $payment->invoice;

        abort_unless(
            $invoice !== null
                && (int) $invoice->business_id === (int) $payment->business_id
                && (float) $payment->amount > 0,
            404
        );

        return response()
            ->view('finance.receipt-public', compact('payment', 'invoice'))
            ->header('Cache-Control', 'private, no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Referrer-Policy', 'no-referrer');
    }
}
