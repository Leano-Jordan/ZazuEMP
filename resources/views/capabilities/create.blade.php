<x-app-layout>
    <x-slot:title>Add service</x-slot:title>
    <x-slot:heading>Add service</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('capabilities.index') }}" class="zazu-btn zazu-btn-ghost">Service catalogue</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Service catalogue · New</div>
            <h2 class="zazu-command-title">Add something you provide</h2>
            <p class="zazu-command-copy">Add a picture, choose the group it belongs to, and save the usual price. Zazu will reuse this information when you build jobs and quotes.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('capabilities.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">What do you provide?</div>
                        <div class="zazu-form-section-copy">Use familiar choices. Only use Other when the service group really is not listed.</div>
                    </div>
                    <div class="zazu-form-grid">
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Service name <span class="zazu-required">*</span></span>
                            <input name="name" value="{{ old('name') }}" required class="zazu-input">
                            <span class="zazu-field-help">Example: “Wedding buffet for 100 guests”.</span>
                            @error('name')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <fieldset class="zazu-field zazu-field-wide">
                            <legend class="zazu-label">Service group <span class="zazu-required">*</span></legend>
                            <div class="zazu-choice-grid">
                                @foreach ($serviceCategories as $category)
                                    <label class="zazu-choice-card">
                                        <input type="radio" name="category" value="{{ $category }}" @checked(old('category') === $category) required>
                                        <span>{{ $category }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('category')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </fieldset>

                        <label class="zazu-field">
                            <span class="zazu-label">What is it?</span>
                            <select name="capability_type" class="zazu-select" required>
                                @foreach (['service' => 'Service', 'rental' => 'Hire / rental', 'product' => 'Product', 'package' => 'Package', 'other' => 'Other'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('capability_type', 'service') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Usual price</span>
                            <input type="number" name="default_price" value="{{ old('default_price') }}" min="0" step="0.01" inputmode="decimal" class="zazu-input">
                            <span class="zazu-field-help">Leave blank if you always price it manually.</span>
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">How do you charge?</span>
                            <select name="pricing_basis" class="zazu-select" required>
                                @foreach (['custom' => 'Set a price each time', 'fixed' => 'One fixed price', 'per_unit' => 'Per item / unit', 'per_person' => 'Per person', 'per_hour' => 'Per hour', 'per_day' => 'Per day'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('pricing_basis', 'custom') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Default unit</span>
                            <select name="default_unit" class="zazu-select">
                                <option value="">Not specified</option>
                                @foreach (config('zazu.units') as $value => $label)
                                    <option value="{{ $value }}" @selected(old('default_unit') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Description</span>
                            <textarea name="description" rows="4" class="zazu-textarea"></textarea>
                            <span class="zazu-field-help">Short description customers or staff can understand.</span>
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Preview picture</span>
                            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="zazu-input" data-image-preview>
                            <span class="zazu-field-help">Use a clear picture of the service, product, setup or equipment.</span>
                            @error('image')<span class="zazu-field-error">{{ $message }}</span>@enderror
                            <img data-image-preview-output alt="" class="zazu-image-preview" hidden>
                        </label>

                        <label class="zazu-check-row">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                            <span><span class="block text-xs font-bold">Show this service in job choices</span><span class="mt-1 block text-[10px] text-[var(--zazu-faint)]">Keep it off only when you do not want staff selecting it.</span></span>
                        </label>
                    </div>
                </section>

                <div class="zazu-actionbar">
                    <a href="{{ route('capabilities.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                    <button class="zazu-btn zazu-btn-primary">Save service</button>
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