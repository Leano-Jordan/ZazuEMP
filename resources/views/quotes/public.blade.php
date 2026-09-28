<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $quote->reference }} · Zazu</title>
    @vite(['resources/css/app.css', 'resources/css/zazu-responsive-theme.css'])
</head>
<body>
    <main class="zazu-public-shell">
        <section class="zazu-public-card">
            <header class="zazu-public-header">
                <div>
                    <div class="zazu-eyebrow">Zazu · Quote</div>
                    <h1 class="zazu-public-title">{{ $quote->reference }}</h1>
                    <p class="zazu-public-copy">{{ $quote->event?->business?->name ?? 'Event services' }}</p>
                </div>
                <span class="zazu-chip {{ $quote->status === 'accepted' ? 'zazu-chip-success' : 'zazu-chip-info' }}">
                    {{ ucfirst($quote->status) }}
                </span>
            </header>

            <section class="zazu-public-party">
                <div>
                    <div class="zazu-eyebrow">Prepared for</div>
                    <strong>{{ $quote->event?->customer?->name ?? 'Customer' }}</strong>
                </div>
                <div>
                    <div class="zazu-eyebrow">Event</div>
                    <strong>{{ $quote->event?->name ?? 'Event' }}</strong>
                </div>
            </section>

            <section class="zazu-public-lines">
                <div class="zazu-public-section-title">Services</div>
                @foreach ($version->items as $item)
                    <div class="zazu-public-line">
                        <div>
                            <strong>{{ $item->description }}</strong>
                            <span>{{ number_format((float) $item->quantity, 2) }} {{ $item->unit ?: 'units' }}</span>
                        </div>
                        <strong>{{ $quote->currency }} {{ $item->line_total }}</strong>
                    </div>
                @endforeach
            </section>

            <section class="zazu-public-total">
                <span>Total</span>
                <strong>{{ $quote->currency }} {{ number_format((float) $version->total, 2) }}</strong>
            </section>

            <section class="zazu-public-accepted">
                <div class="zazu-public-section-title">Deposit</div>
                <p class="zazu-public-copy">
                    This quote requires a {{ number_format((float) ($version->deposit_percent ?? 0), 2) }}% deposit:
                    <strong>{{ $quote->currency }} {{ $version->deposit_amount }}</strong>.
                    The business can confirm the deposit payment against the resulting invoice.
                </p>
            </section>

            @if ($quote->status === 'sent' && $acceptUrl)
                <section class="zazu-public-accept">
                    <div>
                        <div class="zazu-public-section-title">Ready to proceed?</div>
                        <p class="zazu-public-copy">Confirm acceptance below. Zazu will record the acceptance against this quote revision.</p>
                    </div>

                    <form method="POST" action="{{ $acceptUrl }}" class="zazu-form">
                        @csrf
                        <label class="zazu-field">
                            <span class="zazu-label">Your name</span>
                            <input name="customer_name" value="{{ old('customer_name') }}" class="zazu-input" required autocomplete="name">
                            @error('customer_name')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-check-row mt-4">
                            <input type="checkbox" name="acceptance" value="1" required>
                            <span>I confirm that I accept this quote.</span>
                        </label>

                        <button class="zazu-btn zazu-btn-primary mt-4">Accept quote</button>
                    </form>
                </section>
            @else
                <section class="zazu-public-accepted">
                    <div class="zazu-public-section-title">Quote accepted</div>
                    <p class="zazu-public-copy">This quote has already been accepted. Please contact the business if anything needs to change.</p>
                </section>
            @endif
        </section>
    </main>
</body>
</html>
