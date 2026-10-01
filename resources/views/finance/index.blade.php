<x-app-layout>
<x-slot:title>Finance</x-slot:title><x-slot:heading>Finance</x-slot:heading>
<x-slot:headerAction>
    @if(app(\App\Support\PermissionService::class)->allows('finance.invoice.create', auth()->user(), $business))
        <a href="{{ route('finance.invoices.create') }}" class="zazu-btn zazu-btn-secondary">New invoice</a>
    @endif
    @if(app(\App\Support\PermissionService::class)->allows('finance.payment.create', auth()->user(), $business))
        <a href="{{ route('finance.payments.create') }}" class="zazu-btn zazu-btn-primary">Record payment</a>
    @endif
    @if(app(\App\Support\PermissionService::class)->allows('finance.expense.create', auth()->user(), $business))
        <a href="{{ route('finance.expenses.create') }}" class="zazu-btn zazu-btn-ghost">New expense</a>
    @endif
</x-slot:headerAction>
<section class="zazu-command-band zazu-finance-command">
    <div><div class="zazu-eyebrow">Commercial control</div><h2 class="zazu-command-title">Money in, money out</h2><p class="zazu-command-copy">Invoices, customer payments and finance expenses now live as separate transaction records.</p></div>
    <div class="zazu-command-badge"><span class="zazu-status-dot" aria-hidden="true"></span><span>Finance workspace</span></div>
</section>

<section class="zazu-metric-grid zazu-finance-directory" aria-label="Finance totals">
    <div class="zazu-metric-card">
        <div class="zazu-metric-top"><div class="zazu-metric-label">Invoiced</div><span class="zazu-finance-symbol" aria-hidden="true">INV</span></div>
        <div class="zazu-metric-currency-stack">
            @forelse($invoicedByCurrency as $currency => $total)
                <div class="zazu-metric-value" data-numeric="true">{{ $currency }} {{ $total }}</div>
            @empty
                <div class="zazu-metric-value" data-numeric="true">0.00</div>
            @endforelse
        </div>
    </div>
    <div class="zazu-metric-card">
        <div class="zazu-metric-top"><div class="zazu-metric-label">Payments recorded</div><span class="zazu-finance-symbol" aria-hidden="true">IN</span></div>
        <div class="zazu-metric-currency-stack">
            @forelse($paidByCurrency as $currency => $total)
                <div class="zazu-metric-value" data-numeric="true">{{ $currency }} {{ $total }}</div>
            @empty
                <div class="zazu-metric-value" data-numeric="true">0.00</div>
            @endforelse
        </div>
    </div>
    <div class="zazu-metric-card">
        <div class="zazu-metric-top"><div class="zazu-metric-label">Expenses</div><span class="zazu-finance-symbol" aria-hidden="true">OUT</span></div>
        <div class="zazu-metric-currency-stack">
            @forelse($expensesByCurrency as $currency => $total)
                <div class="zazu-metric-value" data-numeric="true">{{ $currency }} {{ $total }}</div>
            @empty
                <div class="zazu-metric-value" data-numeric="true">0.00</div>
            @endforelse
        </div>
    </div>
</section>

<section class="zazu-card zazu-list mt-5">
    <div class="zazu-card-header"><div><div class="zazu-eyebrow">Receivables</div><div class="zazu-card-title mt-1">Invoices</div></div><span class="zazu-section-count">{{ $invoices->total() }}</span></div>
    @forelse($invoices as $invoice)
        <div class="zazu-list-item">
            <div class="zazu-list-main">
                <div class="zazu-list-title">{{ $invoice->number }}</div>
                <div class="zazu-list-meta">{{ $invoice->event?->name ?: 'No job linked' }} · due {{ $invoice->due_at?->format('d M Y') ?: 'Not set' }}</div>
            </div>
            <div class="zazu-list-side">
                <div class="zazu-side-primary" data-numeric="true">{{ $invoice->currency }} {{ $invoice->total }}</div>
                <div class="zazu-list-meta" data-numeric="true">balance {{ $invoice->currency }} {{ $invoice->balance }}</div>
            </div>
            <span class="zazu-chip zazu-chip-info">{{ ucfirst($invoice->status) }}</span>
            <a href="{{ route('finance.invoices.show', $invoice) }}" class="zazu-btn zazu-btn-ghost">Open</a>
        </div>
    @empty
        <div class="zazu-empty"><div class="zazu-empty-title">No invoices</div><p class="zazu-empty-copy">Create an invoice from a quote or job.</p></div>
    @endforelse
</section>

@if ($invoices->hasPages())
    <div class="zazu-pagination-wrap mt-4">
        {{ $invoices->links() }}
    </div>
@endif

<section class="zazu-detail-grid mt-5">
    <div class="zazu-card zazu-list">
        <div class="zazu-card-header"><div class="zazu-card-title">Recent payments</div></div>
        @forelse($payments as $payment)
            <div class="zazu-list-item">
                <div class="zazu-list-main"><div class="zazu-list-title">{{ $payment->method }}</div><div class="zazu-list-meta">{{ $payment->reference ?: 'No reference' }} · {{ $payment->paid_at->format('d M Y') }}</div></div>
                <div class="zazu-list-side"><div class="zazu-side-primary" data-numeric="true">{{ $payment->currency }} {{ $payment->amount }}</div></div>
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
                <div class="zazu-list-side"><div class="zazu-side-primary" data-numeric="true">{{ $expense->currency }} {{ $expense->amount }}</div></div>
            </div>
        @empty
            <div class="zazu-empty">No expenses recorded.</div>
        @endforelse
    </div>
</section>
</x-app-layout>