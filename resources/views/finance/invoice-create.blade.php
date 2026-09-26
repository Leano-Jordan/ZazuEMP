<x-app-layout>
    <x-slot:title>New invoice</x-slot:title>
    <x-slot:heading>New invoice</x-slot:heading>
    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Finance · Controlled issue</div>
            <h2 class="zazu-command-title">Create an invoice from a commercial record.</h2>
            <p class="zazu-command-copy">Accepted quotes carry their historical tax treatment forward. Direct job invoices require a subtotal and the applicable tax treatment.</p>
        </div>
    </section>

    <section class="zazu-card">
        <div class="zazu-card-header">
            <div class="zazu-card-title">Invoice source</div>
        </div>

        <form method="POST" action="{{ route('finance.invoices.store') }}" class="zazu-form p-5">
            @csrf

            <label class="zazu-field">
                <span class="zazu-label">Accepted quote</span>
                <select name="quote_id" class="zazu-select">
                    <option value="">No quote</option>
                    @foreach($quotes as $quote)
                        <option value="{{ $quote->id }}" @selected(old('quote_id') == $quote->id)>
                            {{ $quote->reference }} · {{ $quote->status }} · {{ $quote->event?->customer?->name }} · {{ $quote->currency }} {{ number_format((float)($quote->latestVersion?->total ?? 0),2) }}
                        </option>
                    @endforeach
                </select>
                <span class="zazu-field-help">Only accepted quotes can be converted into invoices. Draft and sent quotes remain commercial negotiations.</span>
                @error('quote_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
            </label>

            <label class="zazu-field mt-4">
                <span class="zazu-label">Job (when invoicing without a quote)</span>
                <select name="event_id" class="zazu-select">
                    <option value="">No job</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @selected(old('event_id') == $event->id)>{{ $event->name }} · {{ $event->event_date?->format('d M Y') }}</option>
                    @endforeach
                </select>
                @error('event_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
            </label>

            <div class="zazu-form-grid mt-4">
                <label class="zazu-field">
                    <span class="zazu-label">Subtotal (direct invoice)</span>
                    <input type="number" name="subtotal" value="{{ old('subtotal') }}" min="0" step="0.01" inputmode="decimal" class="zazu-input">
                    <span class="zazu-field-help">Not used when an accepted quote is selected.</span>
                    @error('subtotal')<span class="zazu-field-error">{{ $message }}</span>@enderror
                </label>

                <label class="zazu-field">
                    <span class="zazu-label">Tax treatment</span>
                    <select name="tax_rate_id" class="zazu-select">
                        <option value="">No tax / 0%</option>
                        @foreach($taxRates as $taxRate)
                            <option value="{{ $taxRate->id }}" @selected((string)old('tax_rate_id', $defaultTaxRate?->id) === (string)$taxRate->id)>
                                {{ $taxRate->name }} · {{ $taxRate->rate }}%
                            </option>
                        @endforeach
                    </select>
                    <span class="zazu-field-help">For direct invoices, this tax treatment is snapshotted onto the invoice.</span>
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

            <div class="zazu-actionbar">
                <a href="{{ route('finance.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                <button class="zazu-btn zazu-btn-primary">Create invoice</button>
            </div>
        </form>
    </section>
</x-app-layout>