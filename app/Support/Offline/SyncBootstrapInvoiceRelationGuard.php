<?php

namespace App\Support\Offline;

use App\Models\Business;
use App\Models\Invoice;

class SyncBootstrapInvoiceRelationGuard
{
    public function sanitize(Invoice $invoice, Business $business): void
    {
        $quote = $invoice->quote;
        if (! $quote || (int) ($quote->event?->business_id ?? 0) !== (int) $business->id) {
            $invoice->setAttribute('quote_id', null);
            $invoice->setAttribute('quote_version_id', null);
            $invoice->setRelation('quote', null);
            $invoice->setRelation('quoteVersion', null);
            return;
        }

        $invoice->setRelation('quote', null);
        $quoteVersion = $invoice->quoteVersion;
        if (
            ! $quoteVersion
            || (int) $quoteVersion->quote_id !== (int) $quote->id
            || (int) ($quoteVersion->quote?->event?->business_id ?? 0) !== (int) $business->id
        ) {
            $invoice->setAttribute('quote_version_id', null);
            $quoteVersion = null;
        } else {
            $quoteVersion->unsetRelation('quote');
        }

        $invoice->setRelation('quoteVersion', $quoteVersion);
    }

}
