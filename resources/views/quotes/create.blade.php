<x-app-layout>
    <x-slot:title>Create quote · {{ $event->name }}</x-slot:title>
    <x-slot:heading>Create quote</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.show', $event) }}" class="zazu-btn zazu-btn-ghost">Job workspace</a>
    </x-slot:headerAction>

    @if (session('schedule_warning'))
        <div class="zazu-warning-note mb-4" role="status">{{ session('schedule_warning') }}</div>
    @endif

    <section class="zazu-command-band zazu-quote-editor-command">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · Commercial</div>
            <h2 class="zazu-command-title">Build draft quote v1</h2>
            <p class="zazu-command-copy">Some starter lines begin at 1. Confirm the correct quantity, unit and price for every product or service before saving. Saved prices are filled in where available.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('work.quotes.store', $event) }}" data-zazu-offline-entity="quote" data-zazu-offline-job-id="{{ $event->id }}">
        @csrf

        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Commercial inputs</div>
                        <div class="zazu-form-section-copy">Choose the tax treatment that applies to this quote. Zazu stores the rate and treatment used so later tax changes do not rewrite old commercial documents.</div>
                    </div>

                    <div class="zazu-form-grid">
                        <label class="zazu-field">
    <span class="zazu-label">Deposit required</span>
    <div class="flex items-center gap-2">
        <input type="number" name="deposit_percent" min="0" max="100" step="0.01" required class="zazu-input" value="{{ old('deposit_percent', $version->deposit_percent ?? 0) }}">
        <span class="text-sm text-[var(--zazu-ink-2)]">%</span>
    </div>
    <span class="zazu-field-help">Optional upfront deposit percentage. Zazu calculates the exact deposit from the final quote total.</span>
    @error('deposit_percent')<span class="zazu-field-error">{{ $message }}</span>@enderror
</label>
<label class="zazu-field">
                            <span class="zazu-label">Currency</span>
                            <select name="currency" id="quote-currency" class="zazu-select" required>
                                @foreach ($currencies as $code => $label)
                                    <option value="{{ $code }}" @selected(old('currency', $defaultCurrency) === $code)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('currency')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Tax treatment</span>
                            <select name="tax_rate_id" class="zazu-select">
                                <option value="">No tax / 0%</option>
                                @foreach ($taxRates as $taxRate)
                                    <option value="{{ $taxRate->id }}" data-tax-rate="{{ $taxRate->rate }}" @selected((string) old('tax_rate_id', $defaultTaxRateId) === (string) $taxRate->id)>
                                        {{ $taxRate->name }} · {{ $taxRate->rate }}%
                                    </option>
                                @endforeach
                            </select>
                            <span class="zazu-field-help">The selected tax rule is copied into this quote revision as a historical snapshot.</span>
                            @error('tax_rate_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Quote notes</span>
                            <textarea name="notes" rows="3" class="zazu-textarea">{{ old('notes') }}</textarea>
                            @error('notes')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                </section>

                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Quote lines</div>
                        <div class="zazu-form-section-copy">Check the quantity and unit price on every line. The line total and quote summary update as you type.</div>
                        <div class="zazu-quote-quantity-instruction" role="note"><strong>Every line needs your attention.</strong> Enter the real quantity for each product or service, then check its unit price. A quantity of 1 is not assumed. When a line is exactly 1, confirm that single-unit quantity before saving.</div>
                    </div>

                    <div class="zazu-list zazu-quote-line-editor">
                        @foreach ($event->requirements as $requirement)
                            @php
                                $capability = $requirement->capability;
                                $capabilityTypeLabel = match ($capability?->capability_type) {
                                    'product' => 'Product',
                                    'service' => 'Service',
                                    'rental' => 'Hire / rental',
                                    'package' => 'Package',
                                    'other' => 'Other',
                                    default => $requirement->unit === 'service' ? 'Service' : 'Custom line',
                                };
                                $initialPrice = $capability
                                    && $capability->default_price !== null
                                    && ($capability->currency ?? $defaultCurrency) === old('currency', $defaultCurrency)
                                    ? $capability->default_price
                                    : null;
                            @endphp
                            <div class="zazu-list-item" data-quote-line>
                                <div class="zazu-list-main">
                                    <div class="zazu-list-title">{{ $requirement->description }}</div>
                                    <div class="zazu-list-meta">
                                        <span class="zazu-chip zazu-chip-info">{{ $capabilityTypeLabel }}</span>
                                        {{ $requirement->category ?: 'General' }}
                                        @if ($capability) · {{ $capability->name }} @endif
                                    </div>
                                    <div class="zazu-form-grid mt-3">
                                        <label class="zazu-field zazu-quote-quantity-field">
                                            <span class="zazu-label">Quantity to quote <span class="zazu-required">*</span></span>
                                            <input type="number" min="0.01" step="0.01" inputmode="decimal"
                                                name="quantity[{{ $requirement->id }}]"
                                                value="{{ old('quantity.'.$requirement->id, (float) $requirement->quantity === 1.0 ? '' : $requirement->quantity) }}"
                                                placeholder="Enter quantity" aria-label="Quantity to quote for {{ $requirement->description }}"
                                                data-quote-quantity class="zazu-input" required>
                                            <span class="zazu-field-help"><strong>Required:</strong> enter the total {{ $requirement->unit ?: 'amount' }} you will supply. Do not leave this at one unless one is genuinely correct.</span>
                                            @error('quantity.'.$requirement->id)<span class="zazu-field-error">{{ $message }}</span>@enderror
                                        </label>
                                        <div class="zazu-quote-single-confirm-wrap" data-quote-single-confirm-wrap>
                                            <label class="zazu-quote-single-confirm">
                                                <input type="checkbox" name="confirm_single_quantity[{{ $requirement->id }}]" value="1"
                                                    data-quote-single-confirm @checked(old('confirm_single_quantity.'.$requirement->id))>
                                                <span>Confirm that exactly one {{ $requirement->unit ?: 'unit' }} is the correct quantity for this line.</span>
                                            </label>
                                        </div>
                                        <label class="zazu-field">
                                            <span class="zazu-label">Unit price ({{ old('currency', $defaultCurrency) }}) <span class="zazu-required">*</span></span>
                                            <input type="number" min="0" step="0.01" inputmode="decimal"
                                                name="unit_price[{{ $requirement->id }}]"
                                                value="{{ old('unit_price.'.$requirement->id, $initialPrice) }}"
                                                data-quote-price data-default-price="{{ $initialPrice ?? '' }}"
                                                data-price-currency="{{ $capability?->currency ?? '' }}"
                                                class="zazu-input" required>
                                            <span class="zazu-field-help" data-price-help="{{ $requirement->id }}">
                                                @if ($initialPrice !== null)
                                                    Saved price in {{ $capability->currency }}
                                                @elseif ($capability?->default_price !== null)
                                                    Saved price is in {{ $capability->currency }}. Enter a price in the selected currency.
                                                @else
                                                    Enter a price for this line.
                                                @endif
                                            </span>
                                            @error('unit_price.'.$requirement->id)<span class="zazu-field-error">{{ $message }}</span>@enderror
                                        </label>
                                    </div>
                                    <div class="zazu-quote-line-total"><span>Line total</span><strong data-quote-line-total>{{ old('currency', $defaultCurrency) }} 0.00</strong></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @error('unit_price')<span class="zazu-field-error mt-3">{{ $message }}</span>@enderror
                </section>

                <div class="zazu-actionbar zazu-actionbar-sticky zazu-quote-savebar">
                    <a href="{{ route('work.quotes.index', $event) }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                    <button class="zazu-btn zazu-btn-primary">Save quote v1</button>
                </div>
            </div>

            <aside class="zazu-form-aside">
                <div class="zazu-context-card zazu-financial-summary" data-quote-summary>
                    <div class="zazu-context-title">Live quote summary</div>
                    <p class="zazu-quote-review-status" data-quote-review-status role="status">Review every line before saving.</p>
                    <div class="zazu-context-copy">Use this as a quick check before saving. Zazu recalculates the final figures from the saved quote data.</div>
                    <dl class="zazu-financial-summary-list">
                        <div><dt>Subtotal</dt><dd data-quote-subtotal>{{ $defaultCurrency }} 0.00</dd></div>
                        <div><dt>Tax</dt><dd data-quote-tax>{{ $defaultCurrency }} 0.00</dd></div>
                        <div class="is-total"><dt>Total</dt><dd data-quote-total>{{ $defaultCurrency }} 0.00</dd></div>
                        <div><dt>Deposit</dt><dd data-quote-deposit>{{ $defaultCurrency }} 0.00</dd></div>
                    </dl>
                </div>

                <div class="zazu-context-card">
                    <div class="zazu-context-title">Commercial position</div>
                    <div class="zazu-context-copy">This quote records the current job requirements as a historical commercial snapshot before later revisions or downstream finance actions.</div>

                    <div class="zazu-step-list">
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Requirements</div><div class="zazu-step-copy">Source record</div></div></div>
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Quote v1</div><div class="zazu-step-copy">Current commercial draft</div></div></div>
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Travel & costing</div><div class="zazu-step-copy">Available from this job</div></div></div>
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Customer decision</div><div class="zazu-step-copy">Review and accept the quote</div></div></div>
                    </div>
                </div>
            </aside>
        </div>
    </form>

    <script nonce="{{ request()->attributes->get('cspNonce') }}">
        const currencySelect = document.getElementById('quote-currency');
        const depositInput = document.querySelector('input[name="deposit_percent"]');
        const taxSelect = document.querySelector('select[name="tax_rate_id"]');
        const summary = document.querySelector('[data-quote-summary]');
        const money = new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        function refreshQuoteSummary() {
            if (!summary) return;

            let subtotal = 0;
            let incompleteLines = 0;
            let unconfirmedSingleLines = 0;
            priceInputs.forEach((input) => {
                const line = input.closest('[data-quote-line]');
                const quantity = Number.parseFloat(line?.querySelector('[data-quote-quantity]')?.value || '');
                const price = Number.parseFloat(input.value || '');
                const lineTotal = Number.isFinite(quantity) && quantity > 0 && Number.isFinite(price) && price >= 0 ? quantity * price : null;
                const confirmation = line?.querySelector('[data-quote-single-confirm]');
                const singleQuantityUnconfirmed = Number.isFinite(quantity)
                    && Math.round(quantity * 100) === 100
                    && confirmation
                    && !confirmation.checked;

                if (lineTotal !== null && !singleQuantityUnconfirmed) subtotal += lineTotal;
                if (lineTotal === null) incompleteLines += 1;
                if (singleQuantityUnconfirmed) unconfirmedSingleLines += 1;

                const output = line?.querySelector('[data-quote-line-total]');
                if (output) {
                    output.textContent = lineTotal === null
                        ? 'Enter quantity and price'
                        : (singleQuantityUnconfirmed ? 'Confirm quantity of 1' : currencySelect?.value + ' ' + money.format(lineTotal));
                }
            });

            const selectedTax = taxSelect?.selectedOptions[0];
            const taxRate = Number.parseFloat(selectedTax?.dataset.taxRate || '0');
            const tax = subtotal * (Number.isFinite(taxRate) ? taxRate : 0) / 100;
            const total = subtotal + tax;
            const depositPercent = Number.parseFloat(depositInput?.value || '0');
            const deposit = total * (Number.isFinite(depositPercent) ? depositPercent : 0) / 100;
            const format = (value) => currencySelect?.value + ' ' + money.format(value);

            const reviewStatus = summary.querySelector('[data-quote-review-status]');
            if (reviewStatus) {
                reviewStatus.textContent = incompleteLines
                    ? incompleteLines + ' line(s) still need a valid quantity and price.'
                    : (unconfirmedSingleLines
                        ? unconfirmedSingleLines + ' single-unit line(s) need explicit confirmation.'
                        : 'All line quantities and prices are entered.');
            }
            summary.querySelector('[data-quote-subtotal]').textContent = format(subtotal);
            summary.querySelector('[data-quote-tax]').textContent = format(tax);
            summary.querySelector('[data-quote-total]').textContent = format(total);
            summary.querySelector('[data-quote-deposit]').textContent = format(deposit);
        }
        const priceInputs = [...document.querySelectorAll('[data-quote-price]')];
        const quantityInputs = [...document.querySelectorAll('[data-quote-quantity]')];

        const priceId = (input) => input.name.replace('unit_price[', '').replace(']', '');

        function refreshQuotePriceInput(input) {
            if (input.dataset.touched === '1') return;
            const currency = currencySelect?.value;
            const defaultPrice = input.dataset.defaultPrice;
            const priceCurrency = input.dataset.priceCurrency;
            const help = document.querySelector('[data-price-help="' + priceId(input) + '"]');

            if (defaultPrice !== '' && priceCurrency === currency) {
                input.value = defaultPrice;
                if (help) help.textContent = 'Saved price in ' + priceCurrency;
            } else {
                input.value = '';
                if (help) help.textContent = priceCurrency
                    ? 'Saved price is in ' + priceCurrency + '. Enter a price in ' + currency + '.'
                    : 'Enter a price for this service in ' + currency + '.';
            }
        }

        priceInputs.forEach((input) => input.addEventListener('input', () => {
            input.dataset.touched = '1';
            const help = document.querySelector('[data-price-help="' + priceId(input) + '"]');
            if (help) help.textContent = 'Price entered manually.';
        }));

        depositInput?.addEventListener('input', refreshQuoteSummary);
        taxSelect?.addEventListener('change', refreshQuoteSummary);
        currencySelect?.addEventListener('change', () => {
            priceInputs.forEach(refreshQuotePriceInput);
            refreshQuoteSummary();
        });

        function updateSingleQuantityConfirmation(input) {
            const line = input.closest('[data-quote-line]');
            const wrapper = line?.querySelector('[data-quote-single-confirm-wrap]');
            const checkbox = wrapper?.querySelector('[data-quote-single-confirm]');
            if (!wrapper || !checkbox) return;

            const quantity = Number.parseFloat(input.value || '');
            const isExactlyOne = Number.isFinite(quantity) && Math.round(quantity * 100) === 100;
            wrapper.hidden = !isExactlyOne;
            checkbox.required = isExactlyOne;
            if (!isExactlyOne) checkbox.checked = false;
        }

        priceInputs.forEach((input) => input.addEventListener('input', refreshQuoteSummary));
        quantityInputs.forEach((input) => {
            updateSingleQuantityConfirmation(input);
            input.addEventListener('input', () => {
                updateSingleQuantityConfirmation(input);
                refreshQuoteSummary();
            });
        });
        document.querySelectorAll('[data-quote-single-confirm]').forEach((input) => {
            input.addEventListener('change', refreshQuoteSummary);
        });
        refreshQuoteSummary();
    </script>
</x-app-layout>
