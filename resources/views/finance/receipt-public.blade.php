<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Payment receipt · Invoice {{ $invoice->number }} · Zazu</title>
    @vite(['resources/css/app.css', 'resources/css/zazu-responsive-theme.css', 'resources/css/zazu-final-visual-sweep.css', 'resources/js/public-document.js'])
</head>
<body>
    <main class="zazu-public-shell">
        <section class="zazu-public-card">
            <header class="zazu-public-header">
                <div>
                    <div class="zazu-eyebrow">Payment receipt</div>
                    <h1 class="zazu-public-title">{{ $invoice->number }}</h1>
                    <p class="zazu-public-copy">{{ $invoice->business_trading_name ?: $invoice->business_legal_name ?: 'Business' }}</p>
                </div>
                <button type="button" class="zazu-btn zazu-btn-secondary print-hide" data-zazu-print>Print / Save as PDF</button>
            </header>
            <section class="zazu-public-party">
                <div>
                    <div class="zazu-eyebrow">Received by</div>
                    <strong>{{ $invoice->business_legal_name ?: $invoice->business_trading_name ?: 'Business' }}</strong>
                    <p class="zazu-public-copy">
                        @if($invoice->business_address){{ $invoice->business_address }}<br>@endif
                        @if($invoice->business_email){{ $invoice->business_email }}<br>@endif
                        @if($invoice->business_phone){{ $invoice->business_phone }}@endif
                    </p>
                </div>
                <div>
                    <div class="zazu-eyebrow">Received from</div>
                    <strong>{{ $invoice->customer_name ?: 'Customer' }}</strong>
                    <p class="zazu-public-copy">
                        @if($invoice->customer_email){{ $invoice->customer_email }}<br>@endif
                        @if($invoice->customer_phone){{ $invoice->customer_phone }}@endif
                    </p>
                </div>
            </section>
            <section class="zazu-public-lines">
                <div class="zazu-public-line"><span>Invoice</span><strong>{{ $invoice->number }}</strong></div>
                <div class="zazu-public-line">
                    <span>Payment type</span>
                    <strong>{{ $payment->type === 'deposit' ? 'Deposit received' : 'Payment received' }}</strong>
                </div>
                <div class="zazu-public-line">
                    <span>Payment date</span>
                    <strong>{{ $payment->paid_at?->format('d M Y') ?: 'Date not set' }}</strong>
                </div>
                <div class="zazu-public-line">
                    <span>Payment method</span>
                    <strong>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</strong>
                </div>
                @if($payment->reference)
                    <div class="zazu-public-line"><span>Payment reference</span><strong>{{ $payment->reference }}</strong></div>
                @endif
            </section>
            <section class="zazu-public-total">
                <span>Amount received</span>
                <strong>{{ $payment->currency }} {{ $payment->amount }}</strong>
            </section>
            <p class="zazu-public-copy">This receipt reflects the payment record entered by the business in Zazu.</p>
            <footer class="zazu-public-copy print-hide">
                This secure customer link expires seven days after it is generated. Contact the business for a refreshed link or help with this receipt.
            </footer>
        </section>
    </main>
</body>
</html>
