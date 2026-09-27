<x-app-layout>
    <x-slot:title>{{ isset($asset) ? 'Edit asset' : 'New asset' }}</x-slot:title>
    <x-slot:heading>{{ isset($asset) ? 'Edit asset' : 'New asset' }}</x-slot:heading>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Assets · Register</div>
            <h2 class="zazu-command-title">{{ isset($asset) ? 'Maintain asset details' : 'Register a physical asset' }}</h2>
            <p class="zazu-command-copy">{{ isset($asset) ? 'Keep condition, location and rental classification current.' : 'Record equipment that exists as an individual accountable asset.' }}</p>
        </div>
    </section>

    <section class="zazu-card">
        <div class="zazu-card-header"><div class="zazu-card-title">Asset details</div></div>

        <form method="POST" action="{{ isset($asset) ? route('assets.update', $asset) : route('assets.store') }}" class="zazu-form p-5">
            @csrf
            @if(isset($asset)) @method('PUT') @endif

            <div class="zazu-form-grid">
                <label class="zazu-field">
                    <span class="zazu-label">Asset tag</span>
                    <input name="asset_tag" required class="zazu-input" value="{{ old('asset_tag', $asset->asset_tag ?? '') }}">
                    @error('asset_tag')<span class="zazu-field-error">{{ $message }}</span>@enderror
                </label>

                <label class="zazu-field">
                    <span class="zazu-label">Name</span>
                    <input name="name" required class="zazu-input" value="{{ old('name', $asset->name ?? '') }}">
                    @error('name')<span class="zazu-field-error">{{ $message }}</span>@enderror
                </label>

                <label class="zazu-field">
                    <span class="zazu-label">Condition</span>
                    <select name="condition" class="zazu-select" required>
                        @foreach(['good', 'fair', 'poor', 'damaged'] as $condition)
                            <option value="{{ $condition }}" @selected(old('condition', $asset->condition ?? 'good') === $condition)>{{ ucfirst($condition) }}</option>
                        @endforeach
                    </select>
                    @error('condition')<span class="zazu-field-error">{{ $message }}</span>@enderror
                </label>

                <label class="zazu-field">
                    <span class="zazu-label">Location</span>
                    <input name="location" class="zazu-input" value="{{ old('location', $asset->location ?? '') }}">
                    @error('location')<span class="zazu-field-error">{{ $message }}</span>@enderror
                </label>

                <label class="zazu-field">
                    <span class="zazu-label">Acquired date</span>
                    <input type="date" name="acquired_at" class="zazu-input" value="{{ old('acquired_at', isset($asset) && $asset->acquired_at ? $asset->acquired_at->format('Y-m-d') : '') }}">
                    @error('acquired_at')<span class="zazu-field-error">{{ $message }}</span>@enderror
                </label>

                <label class="zazu-field">
                    <span class="zazu-label">Purchase cost</span>
                    <input type="number" name="purchase_cost" step="0.01" min="0" required class="zazu-input" value="{{ old('purchase_cost', $asset->purchase_cost ?? '0.00') }}">
                    @error('purchase_cost')<span class="zazu-field-error">{{ $message }}</span>@enderror
                </label>

                <label class="zazu-field">
                    <span class="zazu-label">Rental catalogue item</span>
                    <select name="capability_id" class="zazu-select">
                        <option value="">Not linked</option>
                        @foreach($capabilities as $capability)
                            <option value="{{ $capability->id }}" @selected((string) old('capability_id', $asset->capability_id ?? '') === (string) $capability->id)>{{ $capability->name }}</option>
                        @endforeach
                    </select>
                    @error('capability_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="zazu-field mt-4">
                <span class="zazu-label">Notes</span>
                <textarea name="notes" rows="3" class="zazu-textarea">{{ old('notes', $asset->notes ?? '') }}</textarea>
                @error('notes')<span class="zazu-field-error">{{ $message }}</span>@enderror
            </label>

            <div class="zazu-actionbar">
                <a href="{{ route('assets.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                <button class="zazu-btn zazu-btn-primary">{{ isset($asset) ? 'Save asset changes' : 'Register asset' }}</button>
            </div>
        </form>
    </section>
</x-app-layout>