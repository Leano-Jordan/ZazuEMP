<x-app-layout>
    <x-slot:title>Reports</x-slot:title>
    <x-slot:heading>Reports</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-secondary">Dashboard</a></x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Insights</div>
            <h2 class="zazu-command-title">Reports</h2>
            <p class="zazu-command-copy">Turn verified operational records into useful visibility. A report must point back to the records that produced it.</p>
        </div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Principle</div><div class="zazu-command-meta-value">Traceable</div></div>
    </section>

    <section class="zazu-metric-grid">
        <a href="{{ route('work.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-label">Operations</div><div class="zazu-metric-value">→</div><div class="zazu-metric-copy">Open the job records behind operational reporting.</div></a>
        <a href="{{ route('quotes.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-label">Commercial</div><div class="zazu-metric-value">→</div><div class="zazu-metric-copy">Open quotes and pricing history.</div></a>
        <a href="{{ route('calendar.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-label">Scheduling</div><div class="zazu-metric-value">→</div><div class="zazu-metric-copy">Open dated work and planning.</div></a>
        <a href="{{ route('customers.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-label">Relationships</div><div class="zazu-metric-value">→</div><div class="zazu-metric-copy">Open customers and their work history.</div></a>
    </section>

    <section class="zazu-detail-grid mt-5">
        <div class="zazu-panel">
            <div class="zazu-panel-head">
                <div><div class="zazu-panel-title">Reporting boundary</div><div class="zazu-panel-copy">Reports should read from authoritative records, not create a second set of business numbers.</div></div>
                <span class="zazu-chip zazu-chip-info">Foundation</span>
            </div>
            <div class="zazu-placeholder">
                <div class="zazu-placeholder-title">Reporting layer is deliberately conservative</div>
                <div class="zazu-placeholder-copy">Finance, profitability and performance reporting will be introduced only when their underlying transactions and business rules can be reconciled.</div>
            </div>
        </div>
        <div class="zazu-detail-stack">
            <a href="{{ route('dashboard') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Dashboard signals</div><div class="zazu-route-card-copy">Return to the operational overview.</div></div><span class="zazu-route-card-arrow">→</span></a>
            <a href="{{ route('quotes.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Quote history</div><div class="zazu-route-card-copy">Review the commercial source records.</div></div><span class="zazu-route-card-arrow">→</span></a>
            <a href="{{ route('calendar.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Calendar</div><div class="zazu-route-card-copy">Review scheduling information.</div></div><span class="zazu-route-card-arrow">→</span></a>
        </div>
    </section>
</x-app-layout>