<x-app-layout>
    <x-slot:title>Reports</x-slot:title>
    <x-slot:heading>Reports</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-secondary">Dashboard</a></x-slot:headerAction>

    <section class="zazu-command-band">
        <div><div class="zazu-eyebrow">Insights</div><h2 class="zazu-command-title">Reports</h2><p class="zazu-command-copy">Useful business information built from verified Zazu records, not decorative numbers.</p></div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Stage</div><div class="zazu-command-meta-value">Foundation</div></div>
    </section>

    <section class="zazu-metric-grid">
        <a href="{{ route('work.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-label">Active jobs</div><div class="zazu-metric-value">→</div><div class="zazu-metric-copy">Open operational records.</div></a>
        <a href="{{ route('quotes.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-label">Quotes</div><div class="zazu-metric-value">→</div><div class="zazu-metric-copy">Open commercial records.</div></a>
        <a href="{{ route('calendar.index') }}" class="zazu-metric-card zazu-metric-link"><div class="zazu-metric-label">Upcoming jobs</div><div class="zazu-metric-value">→</div><div class="zazu-metric-copy">Open scheduled work.</div></a>
        <div class="zazu-metric-card"><div class="zazu-metric-label">Cash visibility</div><div class="zazu-metric-value">—</div><div class="zazu-metric-copy">Finance reporting requires the authoritative financial layer.</div></div>
    </section>

    <section class="zazu-card mt-5">
        <div class="zazu-card-header"><div class="zazu-card-title">Reporting workspace</div><div class="zazu-card-description">Reports will reconcile to operational records before they are presented as financial or performance facts.</div></div>
        <div class="zazu-placeholder"><div class="zazu-placeholder-title">Reporting is being prepared</div><div class="zazu-placeholder-copy">Current operational information is already available through Jobs, Quotes and Calendar. The reporting layer will consolidate those records without creating a second source of truth.</div></div>
    </section>
</x-app-layout>