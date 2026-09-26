<x-app-layout>
    <x-slot:title>Edit service</x-slot:title>
    <x-slot:heading>Edit service</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('capabilities.index') }}" class="zazu-btn zazu-btn-ghost">Service catalogue</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div class="flex items-center gap-4">
            <div class="zazu-catalogue-image zazu-catalogue-image-small">
                @if ($capability->image_path)
                    <img src="{{ Storage::disk('public')->url($capability->image_path) }}" alt="{{ $capability->name }}">
                @else
                    <span>{{ strtoupper(substr($capability->name, 0, 1)) }}</span>
                @endif
            </div>
            <div>
                <div class="zazu-eyebrow">{{ $capability->category }}</div>
                <h2 class="zazu-command-title">{{ $capability->name }}</h2>
                <p class="zazu-command-copy">Change the service details once. Zazu will use the updated catalogue information for future jobs.</p>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('capabilities.update', $capability) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-grid">
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Service name <span class="zazu-required">*</span></span>
                            <input name="name" value="{{ old('name', $capability->name) }}" required class="zazu-input">
                        </label>

                        <fieldset class="zazu-field zazu-field-wide">
                            <legend class="zazu-label">Service group <span class="zazu-required">*</span></legend>
                            <div class="zazu-choice-grid">
                                @foreach ($serviceCategories as $category)
                                    <label class="zazu-choice-card">
                                        <input type="radio" name="category" value="{{ $category }}" @checked(old('category', $capability->category) === $category) required>
                                        <span>{{ $category }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <label class="zazu-field">
                            <span class="zazu-label">What is it?</span>
                            <select name="capability_type" class="zazu-select" required>
                                @foreach (['service' => 'Service', 'rental' => 'Hire / rental', 'product' => 'Product', 'package' => 'Package', 'other' => 'Other'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('capability_type', $capability->capability_type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Usual price</span>
                            <input type="number" name="default_price" value="{{ old('default_price', $capability->default_price) }}" min="0" step="0.01" class="zazu-input">
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">How do you charge?</span>
                            <select name="pricing_basis" class="zazu-select" required>
                                @foreach (['custom' => 'Set a price each time', 'fixed' => 'One fixed price', 'per_unit' => 'Per item / unit', 'per_person' => 'Per person', 'per_hour' => 'Per hour', 'per_day' => 'Per day'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('pricing_basis', $capability->pricing_basis) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        @php($currentUnit = old('default_unit', $capability->default_unit))
                        <label class="zazu-field">
                            <span class="zazu-label">Default unit</span>
                            <select name="default_unit" class="zazu-select">
                                <option value="">Not specified</option>
                                @foreach (config('zazu.units') as $value => $label)
                                    <option value="{{ $value }}" @selected($currentUnit === $value)>{{ $label }}</option>
                                @endforeach
                                @if ($currentUnit && !array_key_exists($currentUnit, config('zazu.units')))
                                    <option value="{{ $currentUnit }}" selected>{{ $currentUnit }} (current)</option>
                                @endif
                            </select>
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Description</span>
                            <textarea name="description" rows="4" class="zazu-textarea">{{ old('description', $capability->description) }}</textarea>
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Replace preview picture</span>
                            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="zazu-file-input" data-image-preview>
                            @error('image')<span class="zazu-field-error">{{ $message }}</span>@enderror
                            <img data-image-preview-output alt="" class="zazu-image-preview" hidden>
                        </label>

                        <label class="zazu-check-row">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $capability->is_active))>
                            <span><span class="block text-xs font-bold">Show this service in job choices</span><span class="mt-1 block text-[10px] text-[var(--zazu-faint)]">Turn this off when the service is no longer offered.</span></span>
                        </label>
                    </div>
                </section>

                <div class="zazu-actionbar">
                    <a href="{{ route('capabilities.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                    <button class="zazu-btn zazu-btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.querySelector('[data-image-preview]')?.addEventListener('change', (event) => {
            const file = event.target.files?.[0];
            const preview = document.querySelector('[data-image-preview-output]');
            if (!file || !preview) return;
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
        });
    </script>
</x-app-layout>