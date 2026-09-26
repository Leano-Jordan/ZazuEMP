<x-skeleton-page
    eyebrow="Commercial"
    title="Quotes"
    description="Commercial offers will live here as versioned records connected to the relevant work workspace."
>
    <section class="zazu-skeleton-board">
        <div class="zazu-skeleton-head">
            <div>
                <div class="zazu-card-title">Quote pipeline</div>
                <div class="zazu-card-description">Skeleton only. Quote calculations and version history come next.</div>
            </div>
            <span class="zazu-chip zazu-chip-neutral">No live data yet</span>
        </div>

        <div class="zazu-skeleton-row">
            <span>Draft quotes</span><strong>—</strong>
        </div>
        <div class="zazu-skeleton-row">
            <span>Sent to customer</span><strong>—</strong>
        </div>
        <div class="zazu-skeleton-row">
            <span>Accepted</span><strong>—</strong>
        </div>
        <div class="zazu-skeleton-row">
            <span>Expired / superseded</span><strong>—</strong>
        </div>

        <div class="zazu-skeleton-actions">
            <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-secondary">Open work</a>
            <a href="{{ route('calendar.index') }}" class="zazu-btn zazu-btn-ghost">View calendar →</a>
        </div>
    </section>
</x-skeleton-page>
