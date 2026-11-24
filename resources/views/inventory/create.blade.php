<x-app-layout>
    <x-slot:title>New inventory item</x-slot:title>
    <x-slot:heading>New inventory item</x-slot:heading>

    <section class="zazu-command-band zazu-compact-editor-command">
        <div>
            <div class="zazu-eyebrow">Resources / Inventory</div>
            <h2 class="zazu-command-title">Define a stock item</h2>
            <p class="zazu-command-copy">Give the item a stable identity, unit and replenishment threshold before recording movement.</p>
        </div>
    </section>

    <section class="zazu-card zazu-compact-editor-card">
        <div class="zazu-card-header">
            <div class="zazu-card-title">Inventory definition</div>
        </div>
        <form method="POST" action="{{ route('inventory.store') }}" class="zazu-form p-5" data-zazu-offline-entity="inventory_item">
            @csrf
            <div class="zazu-form-grid">
                <label class="zazu-field">
                    <span class="zazu-label">Name <span class="zazu-required">*</span></span>
                    <input name="name" class="zazu-input" required>
                </label>
                <label class="zazu-field">
                    <span class="zazu-label">SKU</span>
                    <input name="sku" class="zazu-input">
                </label>
                <label class="zazu-field">
                    <span class="zazu-label">Unit <span class="zazu-required">*</span></span>
                    <input name="unit" value="unit" class="zazu-input" required>
                </label>
                <label class="zazu-field">
                    <span class="zazu-label">Reorder level <span class="zazu-required">*</span></span>
                    <input type="number" name="reorder_level" min="0" step="0.01" value="0" class="zazu-input" required inputmode="decimal">
                    <span class="zazu-field-help">Zazu flags the item when recorded stock reaches this level.</span>
                </label>
                <label class="zazu-field">
                    <span class="zazu-label">Catalogue product</span>
                    <select name="capability_id" class="zazu-select">
                        <option value="">Not linked</option>
                        @foreach($capabilities as $capability)
                            <option value="{{ $capability->id }}">{{ $capability->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="zazu-actionbar zazu-actionbar-sticky">
                <button class="zazu-btn zazu-btn-primary">Save item</button>
                <a href="{{ route('inventory.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
</x-app-layout>
