<x-app-layout>
    <x-slot:title>New invoice</x-slot:title>
    <x-slot:heading>New invoice</x-slot:heading>
    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Finance · Controlled issue</div>
            <h2 class="zazu-command-title">Create an invoice from a commercial record.</h2>
            <p class="zazu-command-copy">Accepted quotes carry their historical tax treatment forward. Direct job invoices are built from itemised lines and the applicable tax treatment.</p>
        </div>
    </section>

    <section class="zazu-card">
        <div class="zazu-card-header">
            <div class="zazu-card-title">Invoice source</div>
        </div>

        <form method="POST" action="{{ route('finance.invoices.store') }}" class="zazu-form p-5" data-zazu-offline-entity="invoice">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

            <label class="zazu-field">
                <span class="zazu-label">Accepted quote</span>
                <select name="quote_id" class="zazu-select" data-invoice-quote>
                    <option value="">No quote</option>
                    @foreach($quotes as $quote)
                        <option value="{{ $quote->id }}" data-event-id="{{ $quote->event_id }}" data-event-name="{{ $quote->event?->name }}" data-customer-name="{{ $quote->event?->customer?->name }}" data-currency="{{ $quote->currency }}" data-total="{{ $quote->latestVersion?->total ?? '0.00' }}" @selected((string) old('quote_id', $selectedQuoteId) === (string) $quote->id)>
                            {{ $quote->reference }} · {{ $quote->status }} · {{ $quote->event?->customer?->name }} · {{ $quote->currency }} {{ $quote->latestVersion?->total ?? '0.00' }}
                        </option>
                    @endforeach
                </select>
                <span class="zazu-field-help">Only accepted quotes can be converted into invoices. Draft and sent quotes remain commercial negotiations.</span>
                @error('quote_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
            </label>

            <label class="zazu-field mt-4">
                <span class="zazu-label">Job (when invoicing without a quote)</span>
                <select name="event_id" class="zazu-select" data-invoice-event>
                    <option value="">No job</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @selected(old('event_id') == $event->id)>{{ $event->name }} · {{ $event->event_date?->format('d M Y') }}</option>
                    @endforeach
                </select>
                @error('event_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
            </label>

            <section class="zazu-form-section mt-5" data-invoice-lines>
                <div class="zazu-form-section-head">
                    <div>
                        <div class="zazu-form-section-title">Direct invoice lines</div>
                        <div class="zazu-form-section-copy">Use these only when no accepted quote is selected. Zazu calculates the subtotal from the lines so the invoice has its own evidence.</div>
                    </div>
                    <button type="button" class="zazu-btn zazu-btn-ghost" data-add-invoice-line>+ Add line</button>
                </div>

                <div class="grid gap-3" data-invoice-line-list>
                    <div class="zazu-invoice-line" data-invoice-line>
                        <label class="zazu-field">
                            <span class="zazu-label">Description</span>
                            <input name="lines[0][description]" value="{{ old('lines.0.description') }}" class="zazu-input" placeholder="Service or item">
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Quantity</span>
                            <input type="number" name="lines[0][quantity]" value="{{ old('lines.0.quantity', '1.00') }}" min="0.01" step="0.01" class="zazu-input">
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Unit</span>
                            <input name="lines[0][unit]" value="{{ old('lines.0.unit') }}" class="zazu-input" placeholder="service, item, hour">
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Unit price</span>
                            <input type="number" name="lines[0][unit_price]" value="{{ old('lines.0.unit_price') }}" min="0" step="0.01" class="zazu-input">
                        </label>
                        <button type="button" class="zazu-btn zazu-btn-ghost" data-remove-invoice-line aria-label="Remove invoice line">Remove</button>
                    </div>
                </div>
            </section>

            <div class="zazu-form-grid mt-5">
                <label class="zazu-field">
                    <span class="zazu-label">Tax treatment</span>
                    <select name="tax_rate_id" class="zazu-select" data-invoice-tax-rate>
                        <option value="">No tax / 0%</option>
                        @foreach($taxRates as $taxRate)
                            <option value="{{ $taxRate->id }}" @selected((string)old('tax_rate_id', $defaultTaxRate?->id) === (string)$taxRate->id)>
                                {{ $taxRate->name }} · {{ $taxRate->rate }}%
                            </option>
                        @endforeach
                    </select>
                    <span class="zazu-field-help">For direct invoices, this tax treatment is stored on the invoice. Accepted quotes already carry their historical tax treatment.</span>
                    @error('tax_rate_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
                </label>

                <label class="zazu-field">
                    <span class="zazu-label">Issue date</span>
                    <input type="date" name="issued_at" value="{{ old('issued_at', now()->toDateString()) }}" class="zazu-input">
                    @error('issued_at')<span class="zazu-field-error">{{ $message }}</span>@enderror
                </label>

                <label class="zazu-field">
                    <span class="zazu-label">Due date</span>
                    <input type="date" name="due_at" value="{{ old('due_at', now()->addDays(7)->toDateString()) }}" class="zazu-input">
                    @error('due_at')<span class="zazu-field-error">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="zazu-field mt-4">
                <span class="zazu-label">Notes</span>
                <textarea name="notes" rows="3" class="zazu-textarea">{{ old('notes') }}</textarea>
                @error('notes')<span class="zazu-field-error">{{ $message }}</span>@enderror
            </label>

            <p class="zazu-field-help mt-4" data-invoice-source-summary aria-live="polite"></p>

            <div class="zazu-actionbar">
                <a href="{{ route('finance.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                <button class="zazu-btn zazu-btn-primary">Create invoice</button>
            </div>
        </form>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.querySelector('[data-invoice-lines]');
            const list = root?.querySelector('[data-invoice-line-list]');
            const add = root?.querySelector('[data-add-invoice-line]');
            if (!root || !list || !add) return;

            let index = 1;
            const form = root.closest('form');
            const quoteSelect = form?.querySelector('[data-invoice-quote]');
            const eventSelect = form?.querySelector('[data-invoice-event]');
            const taxSelect = form?.querySelector('[data-invoice-tax-rate]');
            const summary = form?.querySelector('[data-invoice-source-summary]');
            const directControls = () => [...root.querySelectorAll('input, button, select, textarea')];

            if (quoteSelect && eventSelect && taxSelect && summary) {
                let manualEventId = eventSelect.value;

                const syncInvoiceSource = () => {
                    const option = quoteSelect.selectedOptions[0];
                    const quoteSelected = Boolean(quoteSelect.value);

                    if (quoteSelected) {
                        const linkedEventId = option.dataset.eventId ?? '';
                        if ([...eventSelect.options].some((item) => item.value === linkedEventId)) {
                            eventSelect.value = linkedEventId;
                        }
                    } else {
                        eventSelect.value = manualEventId;
                    }

                    eventSelect.disabled = quoteSelected;
                    taxSelect.disabled = quoteSelected;
                    directControls().forEach((control) => { control.disabled = quoteSelected; });
                    root.hidden = quoteSelected;

                    summary.textContent = quoteSelected
                        ? 'Invoice source: ' + option.textContent.trim() + '. Job, lines, currency and tax treatment come from this accepted quote version.'
                        : 'Invoice source: direct job invoice. Choose a job and enter invoice lines; the selected tax treatment applies.';
                };

                eventSelect.addEventListener('change', () => {
                    if (!quoteSelect.value) manualEventId = eventSelect.value;
                });
                quoteSelect.addEventListener('change', syncInvoiceSource);
                syncInvoiceSource();
            }

            const createField = (labelText, attributes = {}) => {
                const label = document.createElement('label');
                label.className = 'zazu-field';

                const labelTextNode = document.createElement('span');
                labelTextNode.className = 'zazu-label';
                labelTextNode.textContent = labelText;

                const input = document.createElement('input');
                input.className = 'zazu-input';

                Object.entries(attributes).forEach(([name, value]) => {
                    if (name === 'required') {
                        input.required = Boolean(value);
                    } else {
                        input.setAttribute(name, String(value));
                    }
                });

                label.append(labelTextNode, input);

                return label;
            };

            add.addEventListener('click', () => {
                const row = document.createElement('div');
                row.className = 'zazu-invoice-line';
                row.dataset.invoiceLine = '';

                row.append(
                    createField('Description', {
                        name: `lines[${index}][description]`,
                        required: true,
                        placeholder: 'Service or item',
                    }),
                    createField('Quantity', {
                        type: 'number',
                        name: `lines[${index}][quantity]`,
                        value: '1.00',
                        min: '0.01',
                        step: '0.01',
                        required: true,
                    }),
                    createField('Unit', {
                        name: `lines[${index}][unit]`,
                        placeholder: 'service, item, hour',
                    }),
                    createField('Unit price', {
                        type: 'number',
                        name: `lines[${index}][unit_price]`,
                        min: '0',
                        step: '0.01',
                        required: true,
                    }),
                );

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'zazu-btn zazu-btn-ghost';
                remove.dataset.removeInvoiceLine = '';
                remove.setAttribute('aria-label', 'Remove invoice line');
                remove.textContent = 'Remove';
                row.appendChild(remove);

                list.appendChild(row);
                index += 1;
            });

            list.addEventListener('click', (event) => {
                const button = event.target.closest('[data-remove-invoice-line]');
                if (!button) return;
                const rows = list.querySelectorAll('[data-invoice-line]');
                if (rows.length <= 1) return;
                button.closest('[data-invoice-line]')?.remove();
            });
        });
    </script>
</x-app-layout>