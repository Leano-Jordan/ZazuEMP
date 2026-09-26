<x-skeleton-page
    eyebrow="Resources"
    title="Suppliers"
    description="Suppliers will connect purchasing, hired resources and the preparation stage of each work workspace."
>
    <section class="zazu-card zazu-list">
        <div class="zazu-card-header">
            <div class="zazu-card-title">Supplier directory</div>
            <div class="zazu-card-description">Skeleton rows showing the future shape without pretending live supplier data exists.</div>
        </div>
        @foreach (['Catering supplier','Equipment hire supplier','Decor supplier','Transport supplier'] as $supplier)
            <div class="zazu-list-item">
                <div class="zazu-list-main">
                    <div class="zazu-list-title">{{ $supplier }}</div>
                    <div class="zazu-list-meta">Supplier profile, contacts, terms and linked buying activity</div>
                </div>
                <span class="zazu-chip zazu-chip-neutral">Coming next</span>
            </div>
        @endforeach
    </section>
    <div class="zazu-skeleton-actions">
        <a href="{{ route('inventory.index') }}" class="zazu-btn zazu-btn-secondary">Inventory</a>
        <a href="{{ route('assets.index') }}" class="zazu-btn zazu-btn-ghost">Assets →</a>
    </div>
</x-skeleton-page>
