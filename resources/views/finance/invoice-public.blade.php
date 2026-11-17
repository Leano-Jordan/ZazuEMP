<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Invoice {{ $invoice->number }} · Zazu</title>
    @vite(['resources/css/app.css', 'resources/css/zazu-responsive-theme.css', 'resources/css/zazu-final-visual-sweep.css', 'resources/js/public-document.js'])
</head>
<body>
    <main class="zazu-public-shell">
        <section class="zazu-public-card">
            <header class="zazu-public-header">
                <div>
                    <div class="zazu-eyebrow">{{ $invoice->business_vat_number ? 'Tax Invoice' : 'Invoice' }}</div>
                    <h1 class="zazu-public-title">{{ $invoice->number }}</h1>
                    <p class="zazu-public-copy">{{ $invoice->business_trading_name ?: $invoice->business_legal_name ?: 'Business invoice' }}</p>
                </div>
                <button type="button" class="zazu-btn zazu-btn-secondary print-hide" data-zazu-print>Print / Save as PDF</button>
            </header>

            <section class="zazu-public-party">
                <div>
                    <div class="zazu-eyebrow">Issued by</div>
                    <strong>{{ $invoice->business_legal_name ?: $invoice->business_trading_name ?: 'Business' }}</strong>
                    <p class="zazu-public-copy">
                        @if($invoice->business_address){{ $invoice->business_address }}<br>@endif
                        @if($invoice->business_email){{ $invoice->business_email }}<br>@endif
                        @if($invoice->business_phone){{ $invoice->business_phone }}@endif
                        @if($invoice->business_vat_number)<br>VAT No. {{ $invoice->business_vat_number }}@endif
                        @if(!$invoice->business_vat_number && $invoice->business_tax_number)<br>Tax reference {{ $invoice->business_tax_number }}@endif
                    </p>
                </div>
                <div>
                    <div class="zazu-eyebrow">Bill to</div>
                    <strong>{{ $invoice->customer_name ?: 'Customer' }}</strong>
                    <p class="zazu-public-copy">
                        @if($invoice->customer_address){{ $invoice->customer_address }}<br>@endif
                        @if($invoice->customer_email){{ $invoice->customer_email }}<br>@endif
                        @if($invoice->customer_phone){{ $invoice->customer_phone }}@endif
                        @if($invoice->customer_vat_number)<br>VAT No. {{ $invoice->customer_vat_number }}@endif
                        @if(!$invoice->customer_vat_number && $invoice->customer_tax_number)<br>Tax reference {{ $invoice->customer_tax_number }}@endif
                    </p>
                </div>
            </section>

            <section class="zazu-public-party">
                <div>
                    <div class="zazu-eyebrow">Invoice date</div>
                    <strong>{{ $invoice->issued_at?->format('d M Y') ?: 'Not set' }}</strong>
                </div>
                <div>
                    <div class="zazu-eyebrow">Due date</div>
                    <strong>{{ $invoice->due_at?->format('d M Y') ?: 'Not set' }}</strong>
                </div>
                <div>
                    <div class="zazu-eyebrow">Status</div>
                    <strong>{{ ucfirst($invoice->status) }}</strong>
                </div>
            </section>

            <section class="zazu-public-lines">
                <div class="zazu-public-section-title">Invoice items</div>
                @foreach($invoice->items as $item)
                    <div class="zazu-public-line">
                        <div>
                            <strong>{{ $item->description }}</strong>
                            <span>{{ number_format((float) $item->quantity, 2) }} {{ $item->unit ?: 'units' }} · {{ $invoice->currency }} {{ $item->unit_price }} each</span>
                        </div>
                        <strong>{{ $invoice->currency }} {{ $item->line_total }}</strong>
                    </div>
                @endforeach
            </section>

            <section class="zazu-public-lines">
                <div class="zazu-public-line">
                    <span>Subtotal</span>
                    <strong>{{ $invoice->currency }} {{ $invoice->subtotal }}</strong>
                </div>
                <div class="zazu-public-line">
                    <span>{{ $invoice->tax_label ?: 'Tax' }} @if((float) $invoice->tax_rate > 0) ({{ $invoice->tax_rate }}%) @endif</span>
                    <strong>{{ $invoice->currency }} {{ $invoice->tax_total }}</strong>
                </div>
            </section>

            <section class="zazu-public-total">
                <span>Total due</span>
                <strong>{{ $invoice->currency }} {{ $invoice->total }}</strong>
            </section>

            <footer class="zazu-public-copy print-hide">
                This secure customer link expires seven days after it was issued. Contact the business for a refreshed link or help with this invoice.
            </footer>
        </section>
    </main>
</body>
</html>
