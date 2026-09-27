<x-app-layout>
    <x-slot:title>Set up your catalogue</x-slot:title>
    <x-slot:heading>Set up what you offer</x-slot:heading>

    <section class="zazu-onboarding-shell">
        <div class="zazu-onboarding-main">
            <div class="zazu-onboarding-progress">
                <span class="active">1</span><i></i><span>2</span><i></i><span>✓</span>
            </div>
            <div class="zazu-eyebrow">First things first</div>
            <h2 class="zazu-onboarding-title">Add what you offer</h2>
            <p class="zazu-onboarding-copy">Start with the things you regularly sell, provide or hire out. You can add pricing and other details now or later.</p>

            <form method="POST" action="{{ route('onboarding.catalogue.store') }}" class="zazu-form-section">
                @csrf
                <div class="zazu-form-grid">
                    <label class="zazu-field zazu-field-wide">
                        <span class="zazu-label">What do you offer? <span class="zazu-required">*</span></span>
                        <input name="name" value="{{ old('name') }}" class="zazu-input" required placeholder="e.g. Wedding catering">
                        <span class="zazu-field-help">One item is enough to get started. Add more when you are ready.</span>
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Type</span>
                        <select name="capability_type" class="zazu-select">
                            <option value="service" @selected(old('capability_type', 'service') === 'service')>Service</option>
                            <option value="product" @selected(old('capability_type') === 'product')>Product</option>
                            <option value="rental" @selected(old('capability_type') === 'rental')>Rental</option>
                            <option value="package" @selected(old('capability_type') === 'package')>Package</option>
                            <option value="other" @selected(old('capability_type') === 'other')>Other</option>
                        </select>
                    </label>
                </div>

                <details class="zazu-progressive-details">
                    <summary>Optional details</summary>
                    <div class="zazu-form-grid mt-4">
                        <label class="zazu-field">
                            <span class="zazu-label">Category</span>
                            <select name="category" class="zazu-select">
                                <option value="">No category yet</option>
                                @foreach ($serviceCategories as $category)
                                    <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Pricing</span>
                            <select name="pricing_basis" class="zazu-select">
                                <option value="custom" @selected(old('pricing_basis', 'custom') === 'custom')>Custom price</option>
                                <option value="fixed" @selected(old('pricing_basis') === 'fixed')>Fixed price</option>
                                <option value="per_unit" @selected(old('pricing_basis') === 'per_unit')>Per unit</option>
                                <option value="per_person" @selected(old('pricing_basis') === 'per_person')>Per person</option>
                                <option value="per_hour" @selected(old('pricing_basis') === 'per_hour')>Per hour</option>
                                <option value="per_day" @selected(old('pricing_basis') === 'per_day')>Per day</option>
                            </select>
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Default price</span>
                            <input name="default_price" value="{{ old('default_price') }}" type="number" min="0" step="0.01" class="zazu-input" placeholder="Optional">
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Unit</span>
                            <select name="default_unit" class="zazu-select">
                                <option value="">No unit</option>
                                @foreach (config('zazu.units') as $unit => $label)
                                    <option value="{{ $unit }}" @selected(old('default_unit') === $unit)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Description</span>
                            <textarea name="description" class="zazu-textarea" rows="3" placeholder="Optional details your team should see.">{{ old('description') }}</textarea>
                        </label>
                    </div>
                </details>

                <div class="zazu-actionbar">
                    <button type="submit" form="skip-catalogue-form" class="zazu-btn zazu-btn-ghost">Skip for now</button>
                    <button class="zazu-btn zazu-btn-secondary" name="add_another" value="1">Save & add another</button>
                    <button class="zazu-btn zazu-btn-primary">Continue</button>
                </div>
            </form>
            <form id="skip-catalogue-form" method="POST" action="{{ route('onboarding.catalogue.skip') }}">@csrf</form>

            <section class="zazu-panel">
                <div class="zazu-panel-head">
                    <div>
                        <div class="zazu-panel-title">Your catalogue so far</div>
                        <div class="zazu-panel-copy">{{ $capabilities->count() }} item(s) added during setup.</div>
                    </div>
                </div>
                <div class="zazu-list">
                    @forelse ($capabilities as $capability)
                        <div class="zazu-list-item">
                            <div class="zazu-list-main">
                                <div class="zazu-list-title">{{ $capability->name }}</div>
                                <div class="zazu-list-meta">{{ ucfirst($capability->capability_type) }}{{ $capability->category ? ' · '.$capability->category : '' }}</div>
                            </div>
                            <div class="zazu-list-side">{{ $capability->default_price !== null ? $capability->default_price.' '.$capability->currency : 'Custom pricing' }}</div>
                        </div>
                    @empty
                        <div class="zazu-empty"><div class="zazu-empty-title">Nothing added yet</div><p class="zazu-empty-copy">You can skip this and build the catalogue later.</p></div>
                    @endforelse
                </div>
            </section>
        </div>
        <aside class="zazu-onboarding-aside">
            <div class="zazu-context-card">
                <div class="zazu-context-title">Start small</div>
                <div class="zazu-context-copy">Zazu keeps the first setup focused. Add the details that matter now and fill in the rest when your work calls for them.</div>
                <div class="zazu-step-list">
                    <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">What you offer</div><div class="zazu-step-copy">Add one service, product or rental.</div></div></div>
                    <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Business details</div><div class="zazu-step-copy">Add reusable identity information.</div></div></div>
                    <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Dashboard</div><div class="zazu-step-copy">Your workspace grows with your work.</div></div></div>
                </div>
            </div>
        </aside>
    </section>
</x-app-layout>
