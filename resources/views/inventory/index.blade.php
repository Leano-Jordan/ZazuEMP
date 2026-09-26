<x-skeleton-page
    eyebrow="Resources"
    title="Inventory"
    description="Stock visibility will connect what the business owns, what is available and what a work workspace needs."
>
    <section class="zazu-metric-grid">
        @foreach ([['label'=>'In stock','value'=>'—'],['label'=>'Reserved','value'=>'—'],['label'=>'Low stock','value'=>'—'],['label'=>'Movements','value'=>'—']] as $metric)
            <div class="zazu-metric-card">
                <div class="zazu-metric-label">{{ $metric['label'] }}</div>
                <div class="zazu-metric-value">{{ $metric['value'] }}</div>
                <div class="zazu-metric-copy">Live inventory data will appear here.</div>
            </div>
        @endforeach
    </section>

    <section class="zazu-card">
        <div class="zazu-card-header">
            <div class="zazu-card-title">Inventory workspace</div>
            <div class="zazu-card-description">Future surface for stock items, availability, movements and work allocation.</div>
        </div>
        <div class="zazu-placeholder">
            <div class="zazu-placeholder-title">Inventory engine not connected yet</div>
            <div class="zazu-placeholder-copy">The page exists and is navigable. The data model will be added after the first complete workflow slice is proven.</div>
        </div>
    </section>

    <div class="zazu-skeleton-actions">
        <a href="{{ route('suppliers.index') }}" class="zazu-btn zazu-btn-secondary">Suppliers</a>
        <a href="{{ route('assets.index') }}" class="zazu-btn zazu-btn-ghost">Assets →</a>
    </div>
</x-skeleton-page>
