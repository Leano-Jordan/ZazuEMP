<x-app-layout>
    <x-slot:title>New purchase order</x-slot:title>
    <x-slot:heading>New purchase order</x-slot:heading>

    @php
        $oldDescriptions = old('description', ['']);
        $oldQuantities = old('quantity', ['']);
        $oldUnits = old('unit', ['unit']);
        $oldPrices = old('unit_price', ['']);
        $oldCapabilities = old('capability_id', ['']);
        $lineCount = max(1, count($oldDescriptions));
    @endphp

    <section class="zazu-command-band zazu-compact-editor-command">
        <div>
            <div class="zazu-eyebrow">Resources / Buying</div>
            <h2 class="zazu-command-title">Create a supplier commitment</h2>
            <p class="zazu-command-copy">Choose the supplier, add everything you are buying, and issue the order when it is complete. Zazu keeps the active business currency.</p>
        </div>
    </section>

    <section class="zazu-card zazu-compact-editor-card">
        <div class="zazu-card-header">
            <div>
                <div class="zazu-eyebrow">Buying</div>
                <div class="zazu-card-title mt-1">Order details</div>
            </div>
        </div>

        <form method="POST" action="{{ route('purchasing.store') }}" class="zazu-form p-5" id="purchase-order-form">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

            <div class="zazu-form-grid">
                <label class="zazu-field">
                    <span>Job (optional)</span>
                    <select name="event_id" class="zazu-select">
                        <option value="">General purchase</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" @selected(old('event_id', $selectedEventId) == $event->id)>
                                {{ $event->reference }} · {{ $event->name }} · {{ ucfirst($event->status) }}
                            </option>
                        @endforeach
                    </select>
                    <span class="zazu-field-help">Link it to a job when the purchase is specifically for that work.</span>
                </label>

                <label class="zazu-field">
                    <span>Supplier <span class="zazu-required">*</span></span>
                    <select name="supplier_id" required class="zazu-select">
                        <option value="">Choose supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="zazu-field">
                    <span>Currency</span>
                    <input value="{{ $currency }}" class="zazu-input" readonly aria-describedby="purchase-currency-help">
                    <input type="hidden" name="currency" value="{{ $currency }}">
                    <span id="purchase-currency-help" class="zazu-field-help">Uses the active business currency.</span>
                </label>

                <label class="zazu-field">
                    <span>Expected date</span>
                    <input type="date" name="expected_at" value="{{ old('expected_at') }}" class="zazu-input">
                    <span class="zazu-field-help">Optional supplier delivery date.</span>
                </label>
            </div>

            <section class="zazu-form-section mt-5" aria-labelledby="purchase-lines-title">
                <div class="zazu-form-section-head">
                    <div>
                        <div class="zazu-form-section-title" id="purchase-lines-title">Order lines</div>
                        <div class="zazu-form-section-copy">Add each item you are ordering. Catalogue links are optional and help connect purchases to your reusable services, products or rentals.</div>
                    </div>
                    <button type="button" class="zazu-btn zazu-btn-secondary" data-add-purchase-line>+ Add line</button>
                </div>

                <div class="grid gap-3" data-purchase-line-list>
                    @for($i = 0; $i < $lineCount; $i++)
                        <div class="zazu-purchase-line zazu-card" data-purchase-line>
                            <div class="zazu-purchase-line-head">
                                <strong>Line <span data-purchase-line-number>{{ $i + 1 }}</span></strong>
                                <button type="button" class="zazu-btn zazu-btn-ghost" data-remove-purchase-line @if($lineCount === 1) hidden @endif>Remove</button>
                            </div>
                            <div class="zazu-form-grid mt-3">
                                <label class="zazu-field zazu-field-wide">
                                    <span>Description <span class="zazu-required">*</span></span>
                                    <input name="description[]" value="{{ $oldDescriptions[$i] ?? '' }}" class="zazu-input" required placeholder="e.g. 50 white banquet chairs">
                                </label>
                                <label class="zazu-field">
                                    <span>Quantity <span class="zazu-required">*</span></span>
                                    <input type="number" step="0.01" min="0.01" name="quantity[]" value="{{ $oldQuantities[$i] ?? '' }}" class="zazu-input" required inputmode="decimal" data-purchase-quantity>
                                </label>
                                <label class="zazu-field">
                                    <span>Unit</span>
                                    <input name="unit[]" value="{{ $oldUnits[$i] ?? 'unit' }}" class="zazu-input" placeholder="unit">
                                </label>
                                <label class="zazu-field">
                                    <span>Unit price <span class="zazu-required">*</span></span>
                                    <input type="number" step="0.01" min="0" name="unit_price[]" value="{{ $oldPrices[$i] ?? '' }}" class="zazu-input" required inputmode="decimal" data-purchase-price>
                                </label>
                                <label class="zazu-field zazu-field-wide">
                                    <span>Catalogue item</span>
                                    <select name="capability_id[]" class="zazu-select">
                                        <option value="">Not linked</option>
                                        @foreach($catalogue as $item)
                                            <option value="{{ $item->id }}" @selected(($oldCapabilities[$i] ?? '') == $item->id)>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                            <div class="zazu-line-total" aria-live="polite">
                                Line total: <strong data-purchase-line-total>{{ $currency }} 0.00</strong>
                            </div>
                        </div>
                    @endfor
                </div>

                @error('description')<span class="zazu-field-error mt-3">{{ $message }}</span>@enderror
                @error('quantity')<span class="zazu-field-error mt-3">{{ $message }}</span>@enderror
                @error('unit_price')<span class="zazu-field-error mt-3">{{ $message }}</span>@enderror

                <div class="zazu-purchase-total mt-4" aria-live="polite">
                    <span>Estimated order total</span>
                    <strong data-purchase-order-total>{{ $currency }} 0.00</strong>
                </div>
            </section>

            <label class="zazu-field mt-4">
                <span>Notes</span>
                <textarea name="notes" rows="3" class="zazu-textarea">{{ old('notes') }}</textarea>
            </label>

            <div class="zazu-actionbar zazu-actionbar-sticky">
                <button class="zazu-btn zazu-btn-primary">Create purchase order</button>
                <a href="{{ route('purchasing.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
            </div>
        </form>
    </section>

    <template id="purchase-line-template">
        <div class="zazu-purchase-line zazu-card" data-purchase-line>
            <div class="zazu-purchase-line-head">
                <strong>Line <span data-purchase-line-number>1</span></strong>
                <button type="button" class="zazu-btn zazu-btn-ghost" data-remove-purchase-line>Remove</button>
            </div>
            <div class="zazu-form-grid mt-3">
                <label class="zazu-field zazu-field-wide">
                    <span>Description <span class="zazu-required">*</span></span>
                    <input name="description[]" class="zazu-input" required placeholder="e.g. 50 white banquet chairs">
                </label>
                <label class="zazu-field">
                    <span>Quantity <span class="zazu-required">*</span></span>
                    <input type="number" step="0.01" min="0.01" name="quantity[]" class="zazu-input" required inputmode="decimal" data-purchase-quantity>
                </label>
                <label class="zazu-field">
                    <span>Unit</span>
                    <input name="unit[]" value="unit" class="zazu-input" placeholder="unit">
                </label>
                <label class="zazu-field">
                    <span>Unit price <span class="zazu-required">*</span></span>
                    <input type="number" step="0.01" min="0" name="unit_price[]" class="zazu-input" required inputmode="decimal" data-purchase-price>
                </label>
                <label class="zazu-field zazu-field-wide">
                    <span>Catalogue item</span>
                    <select name="capability_id[]" class="zazu-select">
                        <option value="">Not linked</option>
                        @foreach($catalogue as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="zazu-line-total" aria-live="polite">
                Line total: <strong data-purchase-line-total>{{ $currency }} 0.00</strong>
            </div>
        </div>
    </template>

    <script>
        (() => {
            const list = document.querySelector('[data-purchase-line-list]');
            const add = document.querySelector('[data-add-purchase-line]');
            const template = document.getElementById('purchase-line-template');
            const currency = @json($currency);
            const formatter = new Intl.NumberFormat(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            if (!list || !add || !template) return;

            const refresh = () => {
                let total = 0;
                const lines = [...list.querySelectorAll('[data-purchase-line]')];

                lines.forEach((line, index) => {
                    const number = line.querySelector('[data-purchase-line-number]');
                    const remove = line.querySelector('[data-remove-purchase-line]');
                    const quantity = Number.parseFloat(line.querySelector('[data-purchase-quantity]')?.value || '');
                    const price = Number.parseFloat(line.querySelector('[data-purchase-price]')?.value || '');
                    const lineTotal = Number.isFinite(quantity) && Number.isFinite(price) ? quantity * price : 0;

                    if (number) number.textContent = String(index + 1);
                    if (remove) remove.hidden = lines.length === 1;

                    const display = line.querySelector('[data-purchase-line-total]');
                    if (display) display.textContent = currency + ' ' + formatter.format(lineTotal);

                    total += lineTotal;
                });

                const orderTotal = document.querySelector('[data-purchase-order-total]');
                if (orderTotal) orderTotal.textContent = currency + ' ' + formatter.format(total);
            };

            const bind = (line) => {
                line.querySelectorAll('[data-purchase-quantity], [data-purchase-price]')
                    .forEach(input => input.addEventListener('input', refresh));
                line.querySelector('[data-remove-purchase-line]')?.addEventListener('click', () => {
                    if (list.querySelectorAll('[data-purchase-line]').length === 1) return;
                    line.remove();
                    refresh();
                });
            };

            list.querySelectorAll('[data-purchase-line]').forEach(bind);

            add.addEventListener('click', () => {
                const fragment = template.content.cloneNode(true);
                const line = fragment.querySelector('[data-purchase-line]');
                list.appendChild(fragment);
                bind(line || list.lastElementChild);
                refresh();
                line?.querySelector('input[name="description[]"]')?.focus();
            });

            refresh();
        })();
    </script>

    <style>
        .zazu-purchase-line {
            padding: 14px;
            border: 1px solid var(--zazu-border);
            background: var(--zazu-surface-2);
        }
        .zazu-purchase-line-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .zazu-purchase-line-head strong {
            color: var(--zazu-ink);
            font-size: 11px;
        }
        .zazu-line-total,
        .zazu-purchase-total {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: var(--zazu-muted);
            font-size: 11px;
        }
        .zazu-line-total {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid var(--zazu-border);
        }
        .zazu-line-total strong,
        .zazu-purchase-total strong {
            color: var(--zazu-ink);
            font-variant-numeric: tabular-nums;
        }
        .zazu-purchase-total {
            padding: 13px 14px;
            border: 1px solid var(--zazu-border-strong);
            background: var(--zazu-surface);
            font-weight: 700;
        }
    </style>
</x-app-layout>
