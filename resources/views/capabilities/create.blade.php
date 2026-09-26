<x-app-layout>
    <x-slot:title>Add capability</x-slot:title>
    <x-slot:heading>Add capability</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('capabilities.index') }}" class="zazu-btn zazu-btn-ghost">← Capabilities</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Catalogue / New</div>
            <h2 class="zazu-command-title">Define a capability</h2>
            <p class="zazu-command-copy">Keep the definition reusable. Work-specific quantities and commercial pricing belong to later workflow layers.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('capabilities.store') }}">
        @csrf

        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Capability definition</div>
                        <div class="zazu-form-section-copy">Describe what the business can deliver and how it is normally measured.</div>
                    </div>

                    <div class="zazu-form-grid">
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Name</span>
                            <input name="name" value="{{ old('name') }}" required class="zazu-input" placeholder="e.g. Wedding catering">
                            @error('name')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Category</span>
                            <input name="category" value="{{ old('category') }}" class="zazu-input" placeholder="Catering, hire, decor...">
                            @error('category')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Capability type</span>
                            <select name="capability_type" required class="zazu-select">
                                @foreach (['service' => 'Service', 'rental' => 'Rental', 'product' => 'Product', 'package' => 'Package', 'other' => 'Other'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('capability_type', 'service') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('capability_type')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Pricing basis</span>
                            <select name="pricing_basis" required class="zazu-select">
                                @foreach (['custom' => 'Custom', 'fixed' => 'Fixed', 'per_unit' => 'Per unit', 'per_person' => 'Per person', 'per_hour' => 'Per hour', 'per_day' => 'Per day'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('pricing_basis', 'custom') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('pricing_basis')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Default unit</span>
                            <input name="default_unit" value="{{ old('default_unit') }}" class="zazu-input" placeholder="Guests, chairs, hours...">
                            @error('default_unit')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Description</span>
                            <textarea name="description" rows="5" class="zazu-textarea" placeholder="What does this capability cover?">{{ old('description') }}</textarea>
                            @error('description')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-check-row">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                            <span>
                                <span class="block text-xs font-bold text-[var(--zazu-ink-2)]">Active capability</span>
                                <span class="mt-1 block text-[10px] text-[var(--zazu-faint)]">Available for future Work and quote selection.</span>
                            </span>
                        </label>
                    </div>
                </section>

                <div class="zazu-actionbar">
                    <a href="{{ route('capabilities.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                    <button class="zazu-btn zazu-btn-primary">Save capability</button>
                </div>
            </div>

            <aside class="zazu-form-aside">
                <div class="zazu-context-card">
                    <div class="zazu-context-title">Catalogue rule</div>
                    <div class="zazu-context-copy">A capability is a reusable definition, not a quote line. Keep its identity stable so future workspaces can reference it cleanly.</div>

                    <div class="zazu-step-list">
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Define</div><div class="zazu-step-copy">What the business delivers</div></div></div>
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Select in Work</div><div class="zazu-step-copy">Work-specific requirement</div></div></div>
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Price in Quote</div><div class="zazu-step-copy">Commercial versioning layer</div></div></div>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</x-app-layout>
