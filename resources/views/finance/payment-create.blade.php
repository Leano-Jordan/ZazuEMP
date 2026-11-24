<x-app-layout>
    <x-slot:title>Record payment</x-slot:title>
    @php($selectedInvoice = $selectedInvoiceId ? $invoices->firstWhere('id', $selectedInvoiceId) : null)
    <x-slot:heading>Record payment</x-slot:heading>

    <section class="zazu-command-band zazu-compact-editor-command">
        <div>
            <div class="zazu-eyebrow">Finance</div>
            <h2 class="zazu-command-title">Record a customer payment</h2>
            <p class="zazu-command-copy">Connect the payment to its commercial record so outstanding balances stay traceable.</p>
        </div>
    </section>

    <section class="zazu-card zazu-compact-editor-card">
        <div class="zazu-card-header">
            <div class="zazu-card-title">Customer payment</div>
        </div>
        <form method="POST" action="{{ route('finance.payments.store') }}" class="zazu-form p-5" data-zazu-offline-entity="payment">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

            <div class="zazu-form-grid">
                <label class="zazu-field">
                    <span class="zazu-label">Payment type <span class="zazu-required">*</span></span>
                    <select name="type" class="zazu-select" required>
                        <option value="payment">Invoice payment</option>
                        <option value="deposit">Deposit</option>
                    </select>
                </label>
                <label class="zazu-field">
                    <span class="zazu-label">Invoice <span class="zazu-required">*</span></span>
                    <select name="invoice_id" class="zazu-select" required>
                        <option value="">Choose invoice</option>
                        @foreach($invoices as $invoice)
                            <option value="{{ $invoice->id }}" @selected($selectedInvoiceId === $invoice->id)>{{ $invoice->number }} · {{ $invoice->currency }} {{ $invoice->balance }} outstanding</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="zazu-form-grid mt-4">
                <label class="zazu-field">
                    <span class="zazu-label">Amount <span class="zazu-required">*</span></span>
                    <input type="number" step="0.01" min="0.01" name="amount" class="zazu-input" required inputmode="decimal">
                </label>
                <label class="zazu-field">
                    <span class="zazu-label">Method</span>
                    <select name="method" class="zazu-select">
                        <option value="bank_transfer">Bank transfer</option>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="other">Other</option>
                    </select>
                </label>
                <label class="zazu-field">
                    <span class="zazu-label">Paid date <span class="zazu-required">*</span></span>
                    <input type="date" name="paid_at" value="{{ now()->toDateString() }}" class="zazu-input" required>
                </label>
                <label class="zazu-field">
                    <span class="zazu-label">Reference</span>
                    <input name="reference" class="zazu-input">
                </label>
            </div>

            <label class="zazu-field mt-4">
                <span class="zazu-label">Notes</span>
                <textarea name="notes" rows="3" class="zazu-textarea"></textarea>
            </label>

            <div class="zazu-actionbar zazu-actionbar-sticky">
                <button class="zazu-btn zazu-btn-primary">Record payment</button>
                <a href="{{ $selectedInvoice ? route('finance.invoices.show', $selectedInvoice) : route('finance.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
</x-app-layout>
