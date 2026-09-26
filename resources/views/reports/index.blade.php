<x-skeleton-page
    eyebrow="Insights"
    title="Reports"
    description="The reporting layer will turn Zazu's operational records into useful visibility without becoming a dashboard full of noise."
>
    <section class="zazu-metric-grid">
        @foreach ([['label'=>'Active work','copy'=>'Current operational workload'],['label'=>'Outstanding quotes','copy'=>'Commercial items needing attention'],['label'=>'Upcoming events','copy'=>'Work scheduled ahead'],['label'=>'Cash visibility','copy'=>'Finance layer to come']] as $metric)
            <div class="zazu-metric-card">
                <div class="zazu-metric-label">{{ $metric['label'] }}</div>
                <div class="zazu-metric-value">—</div>
                <div class="zazu-metric-copy">{{ $metric['copy'] }}</div>
            </div>
        @endforeach
    </section>

    <section class="zazu-card">
        <div class="zazu-card-header">
            <div class="zazu-card-title">Reporting workspace</div>
            <div class="zazu-card-description">No live report data is shown yet. Reports will use verified business records rather than sample numbers.</div>
        </div>
        <div class="zazu-placeholder">
            <div class="zazu-placeholder-title">Reporting is being prepared</div>
            <div class="zazu-placeholder-copy">This area is ready for the reporting workflow. Live reports will appear once the required business records are available.</div>
        </div>
    </section>

    <div class="zazu-skeleton-actions">
        <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-secondary">Dashboard</a>
        <a href="{{ route('settings.index') }}" class="zazu-btn zazu-btn-ghost">Settings →</a>
    </div>
</x-skeleton-page>
