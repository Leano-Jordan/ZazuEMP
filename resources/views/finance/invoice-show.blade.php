<x-app-layout>
    <x-slot:title>{{ $invoice->number }}</x-slot:title>
    <x-slot:heading>Invoice {{ $invoice->number }}</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('finance.index') }}" class="zazu-btn zazu-btn-ghost print-hide">← Finance</a>
        <button type="button" class="zazu-btn zazu-btn-primary print-hide" data-zazu-print>Print / Save as PDF</button>
    </x-slot:headerAction>

    <section class="zazu-document">
        <header class="zazu-document-header">
            <div>
                <div class="zazu-eyebrow">{{ $invoice->business_vat_number ? 'Tax Invoice' : 'Invoice' }}</div>
                <h2 class="zazu-document-title">{{ $invoice->business_trading_name ?: $invoice->business_legal_name }}</h2>
                @if($invoice->business_legal_name && $invoice->business_legal_name !== $invoice->business_trading_name)
                    <div class="zazu-document-muted">{{ $invoice->business_legal_name }}</div>
                @endif
                <div class="zazu-document-muted">
                    {{ $invoice->business_address ?: 'Business address not recorded' }}<br>
                    {{ $invoice->business_email ?: 'No email recorded' }}
                    @if($invoice->business_phone) · {{ $invoice->business_phone }} @endif
                </div>
                @if($invoice->business_vat_number)
                    <div class="zazu-document-meta">VAT No. {{ $invoice->business_vat_number }}</div>
                @elseif($invoice->business_tax_number)
                    <div class="zazu-document-meta">Tax reference {{ $invoice->business_tax_number }}</div>
                @endif
            </div>

            <div class="zazu-document-side">
                <div class="zazu-document-number">{{ $invoice->number }}</div>
                <div class="zazu-document-status">{{ strtoupper($invoice->status) }}</div>
                <div class="zazu-document-muted">Issued {{ $invoice->issued_at?->format('d M Y') ?: 'Not set' }}</div>
                <div class="zazu-document-muted">Due {{ $invoice->due_at?->format('d M Y') ?: 'Not set' }}</div>
            </div>
        </header>

        <section class="zazu-document-parties">
            <div>
                <div class="zazu-eyebrow">Bill to</div>
                <div class="zazu-document-party-name">{{ $invoice->customer_name ?: 'Customer not recorded' }}</div>
                <div class="zazu-document-muted">
                    @if($invoice->customer_address){{ $invoice->customer_address }}<br>@endif
                    @if($invoice->customer_vat_number)VAT No. {{ $invoice->customer_vat_number }}<br>@endif
                    @if($invoice->customer_tax_number)Tax reference {{ $invoice->customer_tax_number }}<br>@endif
                    @if($invoice->customer_email){{ $invoice->customer_email }}<br>@endif
                    @if($invoice->customer_phone){{ $invoice->customer_phone }}@endif
                </div>
            </div>
        </section>

        <div class="zazu-document-table-wrap">
            <table class="zazu-document-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th class="text-right">Unit price</th>
                        <th class="text-right">Line total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->unit ?: '—' }}</td>
                            <td class="text-right">{{ $invoice->currency }} {{ $item->unit_price }}</td>
                            <td class="text-right">{{ $invoice->currency }} {{ $item->line_total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <section class="zazu-document-total">
            <div class="zazu-document-total-row"><span>Subtotal</span><strong>{{ $invoice->currency }} {{ $invoice->subtotal }}</strong></div>
            <div class="zazu-document-total-row"><span>{{ $invoice->tax_label ?: 'Tax' }} @if((float)$invoice->tax_rate > 0) ({{ $invoice->tax_rate }}%) @endif</span><strong>{{ $invoice->currency }} {{ $invoice->tax_total }}</strong></div>
            <div class="zazu-document-total-row zazu-document-total-grand"><span>Total</span><strong>{{ $invoice->currency }} {{ $invoice->total }}</strong></div>
        </section>

        @if($invoice->quoteVersion?->deposit_amount > 0)
            <section class="zazu-panel mt-5 print-hide">
                <div class="zazu-eyebrow">Deposit reconciliation</div>
                <div class="zazu-detail-rows mt-2">
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Required</div><div class="zazu-detail-value">{{ $invoice->currency }} {{ $invoice->quoteVersion->deposit_amount }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Received</div><div class="zazu-detail-value">{{ $invoice->currency }} {{ $invoice->deposit_paid_amount }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Deposit balance</div><div class="zazu-detail-value">{{ $invoice->currency }} {{ $invoice->deposit_balance }}</div></div>
                </div>
            </section>
        @endif

        @if($invoice->quote_id)
            <div class="zazu-document-muted mt-4">Source: accepted quote linked to this job. Invoice values preserve the commercial and tax snapshot used when the invoice was issued.</div>
        @endif

        @if($invoice->notes)
            <section class="zazu-panel mt-5 print-hide">
                <div class="zazu-eyebrow">Notes</div>
                <div class="zazu-panel-copy mt-1">{{ $invoice->notes }}</div>
            </section>
        @endif

        <footer class="zazu-document-footer">
            Zazu prepared this document from the business and customer records stored at issue time. It is the business owner's responsibility to verify the document against applicable SARS and contractual requirements before issuing it.
        </footer>
    </section>

    <style>
        @media print {
            .print-hide,
            .zazu-sidebar,
            .zazu-topbar-actions,
            .zazu-mobile-nav { display: none !important; }
            .zazu-topbar { position: static !important; }
            .zazu-content { padding: 0 !important; max-width: none !important; }
            body { background: white !important; }
            .zazu-document { border: 0 !important; box-shadow: none !important; }
        }
    </style>
</x-app-layout>