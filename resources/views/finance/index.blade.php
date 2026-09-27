<x-app-layout>
<x-slot:title>Finance</x-slot:title><x-slot:heading>Finance</x-slot:heading>
<x-slot:headerAction><a href="{{ route('finance.invoices.create') }}" class="zazu-btn zazu-btn-primary">New invoice</a><a href="{{ route('finance.payments.create') }}" class="zazu-btn zazu-btn-secondary">Record payment</a><a href="{{ route('finance.expenses.create') }}" class="zazu-btn zazu-btn-ghost">Record expense</a></x-slot:headerAction>

<section class="zazu-command-band">
    <div><div class="zazu-eyebrow">Commercial control</div><h2 class="zazu-command-title">Money in, money out</h2><p class="zazu-command-copy">Invoices, customer payments and finance expenses now live as separate transaction records.</p></div>
</section>

<section class="zazu-metric-grid" aria-label="Finance totals">
    <div class="zazu-metric-card">
        <div class="zazu-metric-label">Invoiced</div>
        <div class="zazu-metric-currency-stack">
            @forelse($invoicedByCurrency as $currency => $total)
                <div class="zazu-metric-value" data-numeric="true">{{ $currency }} {{ number_format((float) $total, 2) }}</div>
            @empty
                <div class="zazu-metric-value" data-numeric="true">0.00</div>
            @endforelse
        </div>
    </div>
    <div class="zazu-metric-card">
        <div class="zazu-metric-label">Payments recorded</div>
        <div class="zazu-metric-currency-stack">
            @forelse($paidByCurrency as $currency => $total)
                <div class="zazu-metric-value" data-numeric="true">{{ $currency }} {{ number_format((float) $total, 2) }}</div>
            @empty
                <div class="zazu-metric-value" data-numeric="true">0.00</div>
            @endforelse
        </div>
    </div>
    <div class="zazu-metric-card">
        <div class="zazu-metric-label">Expenses</div>
        <div class="zazu-metric-currency-stack">
            @forelse($expensesByCurrency as $currency => $total)
                <div class="zazu-metric-value" data-numeric="true">{{ $currency }} {{ number_format((float) $total, 2) }}</div>
            @empty
                <div class="zazu-metric-value" data-numeric="true">0.00</div>
            @endforelse
        </div>
    </div>
</section>

<section class="zazu-card zazu-list mt-5">
    <div class="zazu-card-header"><div class="zazu-card-title">Invoices</div></div>
    @forelse($invoices as $invoice)
        <div class="zazu-list-item">
            <div class="zazu-list-main">
                <div class="zazu-list-title">{{ $invoice->number }}</div>
                <div class="zazu-list-meta">{{ $invoice->event?->name ?: 'No job linked' }} · due {{ $invoice->due_at?->format('d M Y') ?: 'Not set' }}</div>
            </div>
            <div class="zazu-list-side">
                <div class="zazu-side-primary" data-numeric="true">{{ $invoice->currency }} {{ number_format((float)$invoice->total,2) }}</div>
                <div class="zazu-list-meta" data-numeric="true">balance {{ $invoice->currency }} {{ number_format((float)$invoice->balance,2) }}</div>
            </div>
            <span class="zazu-chip zazu-chip-info">{{ ucfirst($invoice->status) }}</span>
            <a href="{{ route('finance.invoices.show', $invoice) }}" class="zazu-btn zazu-btn-ghost">Open</a>
        </div>
    @empty
        <div class="zazu-empty"><div class="zazu-empty-title">No invoices</div><p class="zazu-empty-copy">Create an invoice from a quote or job.</p></div>
    @endforelse
</section>

<section class="zazu-detail-grid mt-5">
    <div class="zazu-card zazu-list">
        <div class="zazu-card-header"><div class="zazu-card-title">Recent payments</div></div>
        @forelse($payments as $payment)
            <div class="zazu-list-item">
                <div class="zazu-list-main"><div class="zazu-list-title">{{ $payment->method }}</div><div class="zazu-list-meta">{{ $payment->reference ?: 'No reference' }} · {{ $payment->paid_at->format('d M Y') }}</div></div>
                <div class="zazu-list-side"><div class="zazu-side-primary" data-numeric="true">{{ $payment->currency }} {{ number_format((float)$payment->amount,2) }}</div></div>
            </div>
        @empty
            <div class="zazu-empty">No payments recorded.</div>
        @endforelse
    </div>
    <div class="zazu-card zazu-list">
        <div class="zazu-card-header"><div class="zazu-card-title">Recent expenses</div></div>
        @forelse($expenses as $expense)
            <div class="zazu-list-item">
                <div class="zazu-list-main"><div class="zazu-list-title">{{ $expense->description }}</div><div class="zazu-list-meta">{{ $expense->status }} · {{ $expense->expense_date->format('d M Y') }}</div></div>
                <div class="zazu-list-side"><div class="zazu-side-primary" data-numeric="true">{{ $expense->currency }} {{ number_format((float)$expense->amount,2) }}</div></div>
            </div>
        @empty
            <div class="zazu-empty">No expenses recorded.</div>
        @endforelse
    </div>
</section>
</x-app-layout>