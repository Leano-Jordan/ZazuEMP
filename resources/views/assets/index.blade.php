<x-skeleton-page
    eyebrow="Resources"
    title="Assets"
    description="Reusable equipment and other accountable assets will be managed here and attached to work when needed."
>
    <section class="zazu-card zazu-list">
        <div class="zazu-card-header">
            <div class="zazu-card-title">Asset register</div>
            <div class="zazu-card-description">Planned asset register for equipment, availability and accountability.</div>
        </div>
        @foreach (['Owned equipment','Reusable catering equipment','Transport assets','Other accountable assets'] as $asset)
            <div class="zazu-list-item">
                <div class="zazu-list-main">
                    <div class="zazu-list-title">{{ $asset }}</div>
                    <div class="zazu-list-meta">Availability · allocation · condition · accountability</div>
                </div>
                <span class="zazu-chip zazu-chip-neutral">Planned</span>
            </div>
        @endforeach
    </section>
    <div class="zazu-skeleton-actions">
        <a href="{{ route('inventory.index') }}" class="zazu-btn zazu-btn-secondary">Inventory</a>
        <a href="{{ route('reports.index') }}" class="zazu-btn zazu-btn-ghost">Reports →</a>
    </div>
</x-skeleton-page>
