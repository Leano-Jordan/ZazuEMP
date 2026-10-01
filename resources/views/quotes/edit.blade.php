<x-app-layout>
    <x-slot:title>Edit quote · {{ $quote->reference }}</x-slot:title>
    <x-slot:heading>Edit quote v{{ $version->version }}</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('quotes.show', $quote) }}" class="zazu-btn zazu-btn-ghost">Back to quote</a>
        <a href="{{ route('work.show', $quote->event) }}" class="zazu-btn zazu-btn-secondary">Job workspace</a>
    </x-slot:headerAction>

    @php
        $itemsByRequirement = $version->items->keyBy('event_requirement_id');
    @endphp

    <section class="zazu-command-band zazu-quote-editor-command">
        <div>
            <div class="zazu-eyebrow">{{ $quote->reference }} · Revision v{{ $version->version }}</div>
            <h2 class="zazu-command-title">Review the current services and prices</h2>
            <p class="zazu-command-copy">Zazu carries forward prices from the previous revision where possible. New services need a price before you save.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Currency</div>
            <div class="zazu-command-meta-value">{{ $quote->currency }}</div>
        </div>
    </section>

    <form method="POST" action="{{ route('quotes.versions.update', [$quote, $version]) }}">
        @csrf
        @method('PUT')
        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Commercial inputs</div>
                        <div class="zazu-form-section-copy">The quote currency remains {{ $quote->currency }} for this revision.</div>
                    </div>
                    <div class="zazu-form-grid">
                        <div class="zazu-field">
                            <span class="zazu-label">Currency</span>
                            <div class="zazu-input flex items-center font-bold">{{ $quote->currency }}</div>
                        </div>
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
                            <span class="zazu-label">Tax treatment</span>
                            <select name="tax_rate_id" class="zazu-select">
                                <option value="">Keep current tax treatment</option>
                                <option value="none" @selected(old('tax_rate_id') === 'none')>No tax / 0%</option>
                                @foreach ($taxRates as $taxRate)
                                    <option value="{{ $taxRate->id }}" data-tax-rate="{{ $taxRate->rate }}" @selected((string) old('tax_rate_id') === (string) $taxRate->id)>
                                        {{ $taxRate->name }} · {{ $taxRate->rate }}%
                                    </option>
                                @endforeach
                            </select>
                            <span class="zazu-field-help">Current snapshot: {{ $version->tax_label ?: 'No tax' }} · {{ $version->tax_rate }}%</span>
                            @error('tax_rate_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Quote notes</span>
                            <textarea name="notes" rows="3" class="zazu-textarea">{{ old('notes', $version->notes) }}</textarea>
                            @error('notes')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                </section>

                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Current Work services</div>
                        <div class="zazu-form-section-copy">This draft records the current Work services and prices when you save it. Earlier quote revisions remain unchanged.</div>
                    </div>
                    <div class="zazu-card zazu-list zazu-quote-line-editor">
                        @foreach ($quote->event->requirements as $requirement)
                            @php
                                $item = $itemsByRequirement->get($requirement->id);
                                $defaultPrice = $item?->unit_price;

                                if (!$item && $requirement->capability?->default_price !== null && ($requirement->capability->currency ?? $quote->currency) === $quote->currency) {
                                    $defaultPrice = $requirement->capability->default_price;
                                }
                            @endphp
                            <div class="zazu-list-item">
                                <div class="zazu-list-main">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="zazu-list-title">{{ $requirement->description }}</span>
                                        @if (!$item)
                                            <span class="zazu-chip zazu-chip-info">New</span>
                                        @endif
                                    </div>
                                    <div class="zazu-list-meta">
                                        {{ number_format((float) $requirement->quantity, 2) }} {{ $requirement->unit ?: 'units' }}
                                        · {{ $requirement->category ?: 'General' }}
                                        @if ($requirement->capability) · {{ $requirement->capability->name }} @endif
                                    </div>
                                </div>
                                <label class="w-40 shrink-0">
                                    <span class="zazu-label">Price ({{ $quote->currency }})</span>
                                    <input type="number" min="0" step="0.01" inputmode="decimal" name="unit_price[{{ $requirement->id }}]" data-quote-quantity="{{ (float) $requirement->quantity }}" value="{{ old('unit_price.'.$requirement->id, $defaultPrice) }}" class="zazu-input" required>
                                    @if ($item)
                                        <span class="zazu-field-help">Carried forward from the previous revision.</span>
                                    @elseif ($defaultPrice !== null)
                                        <span class="zazu-field-help">Saved catalogue price reused.</span>
                                    @else
                                        <span class="zazu-field-help">Enter the price for this new service.</span>
                                    @endif
                                    @error('unit_price.'.$requirement->id)<span class="zazu-field-error">{{ $message }}</span>@enderror
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('unit_price')<span class="zazu-field-error mt-3">{{ $message }}</span>@enderror
                </section>

                <div class="zazu-actionbar zazu-actionbar-sticky zazu-quote-savebar">
                    <a href="{{ route('quotes.show', $quote) }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                    <button class="zazu-btn zazu-btn-primary">Save quote v{{ $version->version }}</button>
                </div>
            </div>
            <aside class="zazu-form-aside">
                <div class="zazu-context-card zazu-financial-summary zazu-next-card"
                     data-quote-summary
                     data-current-tax-rate="{{ $version->tax_rate ?? 0 }}">
                    <div class="zazu-context-title">Live quote summary</div>
                    <div class="zazu-context-copy">Use this as a quick check before saving. Earlier quote revisions remain unchanged.</div>
                    <dl class="zazu-financial-summary-list">
                        <div><dt>Subtotal</dt><dd data-quote-subtotal>{{ $quote->currency }} 0.00</dd></div>
                        <div><dt>Tax</dt><dd data-quote-tax>{{ $quote->currency }} 0.00</dd></div>
                        <div class="is-total"><dt>Total</dt><dd data-quote-total>{{ $quote->currency }} 0.00</dd></div>
                        <div><dt>Deposit</dt><dd data-quote-deposit>{{ $quote->currency }} 0.00</dd></div>
                    </dl>
                </div>

                <div class="zazu-context-card">
                    <div class="zazu-context-title">Snapshot boundary</div>
                    <div class="zazu-context-copy">Save this revision to record the current Work services, quantities and prices without rewriting earlier revisions.</div>
                </div>
            </aside>
        </div>
    </form>

    <script>
        const editPriceInputs = [...document.querySelectorAll('[data-quote-quantity]')];
        const editDepositInput = document.querySelector('input[name="deposit_percent"]');
        const editTaxSelect = document.querySelector('select[name="tax_rate_id"]');
        const editSummary = document.querySelector('[data-quote-summary]');
        const editCurrency = @json($quote->currency);
        const editMoney = new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        function refreshEditQuoteSummary() {
            if (!editSummary) return;

            let subtotal = 0;
            editPriceInputs.forEach((input) => {
                const quantity = Number.parseFloat(input.dataset.quoteQuantity || '1');
                const price = Number.parseFloat(input.value || '');
                if (Number.isFinite(quantity) && Number.isFinite(price)) subtotal += quantity * price;
            });

            const option = editTaxSelect?.selectedOptions[0];
            const fallbackRate = Number.parseFloat(editSummary.dataset.currentTaxRate || '0');
            const selectedRate = option?.value === 'none' ? 0 : Number.parseFloat(option?.dataset.taxRate ?? '');
            const taxRate = Number.isFinite(selectedRate) ? selectedRate : fallbackRate;
            const tax = subtotal * taxRate / 100;
            const total = subtotal + tax;
            const depositPercent = Number.parseFloat(editDepositInput?.value || '0');
            const deposit = total * (Number.isFinite(depositPercent) ? depositPercent : 0) / 100;
            const format = (value) => editCurrency + ' ' + editMoney.format(value);

            editSummary.querySelector('[data-quote-subtotal]').textContent = format(subtotal);
            editSummary.querySelector('[data-quote-tax]').textContent = format(tax);
            editSummary.querySelector('[data-quote-total]').textContent = format(total);
            editSummary.querySelector('[data-quote-deposit]').textContent = format(deposit);
        }

        editPriceInputs.forEach((input) => input.addEventListener('input', refreshEditQuoteSummary));
        editDepositInput?.addEventListener('input', refreshEditQuoteSummary);
        editTaxSelect?.addEventListener('change', refreshEditQuoteSummary);
        refreshEditQuoteSummary();
    </script>
</x-app-layout>