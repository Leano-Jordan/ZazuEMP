<x-app-layout>
    <x-slot:title>Settings</x-slot:title>
    <x-slot:heading>Business settings</x-slot:heading>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Business identity</div>
            <h2 class="zazu-command-title">Make Zazu look like your business</h2>
            <p class="zazu-command-copy">Use your logo, default currency and artwork throughout the workspace. Images stay with this business and are used only where configured.</p>
        </div>
    </section>

    @php
    $brandingVersion = $business->updated_at?->timestamp ?? 0;
@endphp

<form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="zazu-editor" data-branding-form>
        @csrf
        @method('PUT')

        <div class="zazu-form-main">
            <section class="zazu-form-section">
                <div class="zazu-form-section-head">
                    <div class="zazu-form-section-title">Business identity</div>
                    <div class="zazu-form-section-copy">This name appears in the application shell and business records.</div>
                </div>
                <div class="zazu-form-grid">
                    <label class="zazu-field zazu-field-medium">
                        <span class="zazu-label">Business name <span class="zazu-required">*</span></span>
                        <input name="name" value="{{ old('name', $business->name) }}" class="zazu-input" required>
                        @error('name')<span class="zazu-field-error">{{ $message }}</span>@enderror
                    </label>
                    <label class="zazu-field zazu-field-narrow">
                        <span class="zazu-label">Default currency <span class="zazu-required">*</span></span>
                        <select name="currency" class="zazu-select" required>
                            @foreach ($currencies as $code => $label)
                                <option value="{{ $code }}" @selected(old('currency', $business->currency ?? 'ZAR') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="zazu-field-help">New quotes, costs and travel records use this as their starting currency.</span>
                        @error('currency')<span class="zazu-field-error">{{ $message }}</span>@enderror
                    </label>
                </div>
            </section>

            <section class="zazu-form-section">
                <div class="zazu-form-section-head">
                    <div class="zazu-form-section-title">Tax & compliance profile</div>
                    <div class="zazu-form-section-copy">Store the business identity, tax registrations and activity flags that Zazu can reuse when preparing commercial and compliance documents.</div>
                </div>

                <div class="zazu-form-grid">
                    <label class="zazu-field zazu-field-medium">
                        <span class="zazu-label">Legal business name</span>
                        <input name="legal_name" value="{{ old('legal_name', $business->taxProfile?->legal_name ?? $business->name) }}" class="zazu-input">
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Trading name</span>
                        <input name="trading_name" value="{{ old('trading_name', $business->taxProfile?->trading_name) }}" class="zazu-input">
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Registration type</span>
                        <select name="registration_type" class="zazu-select">
                            @foreach(['company'=>'Company','sole_proprietor'=>'Sole proprietor','close_corporation'=>'Close corporation','trust'=>'Trust','cooperative'=>'Co-operative','other'=>'Other / specialist'] as $code => $label)
                                <option value="{{ $code }}" @selected(old('registration_type', $business->taxProfile?->registration_type) === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Registration number</span>
                        <input name="registration_number" value="{{ old('registration_number', $business->taxProfile?->registration_number) }}" class="zazu-input">
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Income tax number</span>
                        <input name="income_tax_number" value="{{ old('income_tax_number', $business->taxProfile?->income_tax_number ?? $business->tax_number) }}" class="zazu-input">
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Tax regime</span>
                        <select name="tax_regime" class="zazu-select">
                            @foreach($taxRegimes as $code => $label)
                                <option value="{{ $code }}" @selected(old('tax_regime', $business->taxProfile?->tax_regime ?? 'standard_income_tax') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">VAT status</span>
                        <select name="vat_status" id="vat-status" class="zazu-select" required>
                            @foreach($vatStatuses as $code => $label)
                                <option value="{{ $code }}" @selected(old('vat_status', $business->taxProfile?->vat_status ?? 'not_registered') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">VAT registration number</span>
                        <input name="vat_number" value="{{ old('vat_number', $business->taxProfile?->vat_number) }}" class="zazu-input">
                        @error('vat_number')<span class="zazu-field-error">{{ $message }}</span>@enderror
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Default VAT rate (%)</span>
                        <input name="default_vat_rate" value="{{ old('default_vat_rate', $taxRates->firstWhere('code', 'VAT_STANDARD')?->rate ?? config('zazu.tax.default_standard_rate')) }}" class="zazu-input" inputmode="decimal" type="number" min="0" max="100" step="0.01" required>
                        <span class="zazu-field-help">Current SARS standard VAT rate is 15%; only apply a different rate when the applicable rule supports it.</span>
                        @error('default_vat_rate')<span class="zazu-field-error">{{ $message }}</span>@enderror
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Tax rate effective from</span>
                        <input name="tax_effective_from" value="{{ old('tax_effective_from', now()->toDateString()) }}" class="zazu-input" type="date" required>
                        <span class="zazu-field-help">Zazu creates an effective-dated rate instead of changing old quote history.</span>
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">PAYE number</span>
                        <input name="paye_number" value="{{ old('paye_number', $business->taxProfile?->paye_number) }}" class="zazu-input">
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">UIF number</span>
                        <input name="uif_number" value="{{ old('uif_number', $business->taxProfile?->uif_number) }}" class="zazu-input">
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">SDL number</span>
                        <input name="sdl_number" value="{{ old('sdl_number', $business->taxProfile?->sdl_number) }}" class="zazu-input">
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Financial year end</span>
                        <input name="financial_year_end" value="{{ old('financial_year_end', optional($business->taxProfile?->financial_year_end)->format('Y-m-d')) }}" class="zazu-input" type="date">
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Representative taxpayer</span>
                        <input name="representative_name" value="{{ old('representative_name', $business->taxProfile?->representative_name) }}" class="zazu-input">
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Representative email</span>
                        <input name="representative_email" value="{{ old('representative_email', $business->taxProfile?->representative_email) }}" class="zazu-input" type="email">
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">TCS reference</span>
                        <input name="tcs_reference" value="{{ old('tcs_reference', $business->taxProfile?->tcs_reference) }}" class="zazu-input">
                        <span class="zazu-field-help">SARS uses the Good Standing TCS application for tender-related compliance status.</span>
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">TCS PIN expiry</span>
                        <input name="tcs_pin_expires_at" value="{{ old('tcs_pin_expires_at', optional($business->taxProfile?->tcs_pin_expires_at)->format('Y-m-d')) }}" class="zazu-input" type="date">
                    </label>
                    <label class="zazu-field zazu-field-wide">
                        <span class="zazu-label">TCS PIN</span>
                        <input name="tcs_pin" value="" class="zazu-input" autocomplete="off" placeholder="{{ $business->taxProfile?->tcs_pin ? 'PIN stored securely — leave blank to keep it' : 'Enter only when needed' }}">
                        <span class="zazu-field-help">Stored encrypted. Zazu should never display or expose the PIN unnecessarily.</span>
                    </label>
                    <div class="zazu-field zazu-field-wide">
                        <span class="zazu-label">Business activity flags</span>
                        <div class="flex flex-wrap gap-4 mt-2">
                            @foreach(['food_handling'=>'Handles food','employees'=>'Employs staff','government_supply'=>'Supplies government','tendering'=>'Prepares tenders','regulated_activity'=>'Other regulated activity'] as $flag => $label)
                                <label class="zazu-check-row"><input type="checkbox" name="{{ $flag }}" value="1" @checked(old($flag, ($business->taxProfile?->activity_flags ?? [])[$flag] ?? false))><span>{{ $label }}</span></label>
                            @endforeach
                        </div>
                    </div>
                    <label class="zazu-field zazu-field-wide">
                        <span class="zazu-label">Compliance notes</span>
                        <textarea name="compliance_notes" rows="3" class="zazu-textarea">{{ old('compliance_notes', $business->taxProfile?->compliance_notes) }}</textarea>
                    </label>
                </div>
            </section>

            <section class="zazu-form-section">
                <div class="zazu-form-section-head">
                    <div class="zazu-form-section-title">Tender & evidence readiness</div>
                    <div class="zazu-form-section-copy">Keep the source documents behind the business boundary so Zazu can build evidence packs without making a legal or procurement decision for the business.</div>
                </div>
                <div class="zazu-panel">
                    <div class="zazu-panel-title">Compliance Centre</div>
                    <div class="zazu-panel-copy mt-1">Track CIPC, SARS, CSD, B-BBEE, UIF/SDL/COID, food-premises and tender-specific evidence where applicable.</div>
                    <a href="{{ route('settings.compliance') }}" class="zazu-btn zazu-btn-secondary mt-4">Open Compliance Centre</a>
                </div>
            </section>

            <section class="zazu-form-section">
                <div class="zazu-form-section-head">
                    <div class="zazu-form-section-title">Branding images</div>
                    <div class="zazu-form-section-copy">Use clear images. Zazu keeps the original upload and displays it responsively.</div>
                </div>

                <div class="zazu-branding-preview-grid">
                    <div class="zazu-branding-preview">
                        <div class="zazu-branding-preview-media zazu-branding-logo" data-branding-preview-container="logo" aria-live="polite">
                            @if ($business->logo_path)
                                <img src="{{ route('business.media', ['type' => 'logo']) }}?v={{ $brandingVersion }}" alt="{{ $business->name }} logo" data-branding-preview="logo">
                            @else
                                <span class="zazu-branding-placeholder" data-branding-placeholder="logo">{{ strtoupper(substr($business->name, 0, 1)) }}</span>
                                <img alt="{{ $business->name }} logo" data-branding-preview="logo" hidden>
                            @endif
                            <span class="zazu-branding-loading" data-branding-loading="logo" hidden>
                                <span class="zazu-spinner" aria-hidden="true"></span>
                                <span>Preparing preview…</span>
                            </span>
                        </div>
                        <strong>Business logo</strong>
                        <span class="zazu-branding-description">Used in the application identity.</span>
                        <label class="zazu-btn zazu-btn-secondary mt-3 cursor-pointer">
                            Choose logo
                            <input type="file" name="logo" accept="image/jpeg,image/png,image/webp" class="hidden" data-branding-upload="logo">
                        </label>
                        <span class="zazu-branding-file" data-branding-file="logo" aria-live="polite"></span>
                        @if ($business->logo_path)
                            <label class="zazu-check-row mt-3"><input type="checkbox" name="remove_logo" value="1"><span>Remove current logo</span></label>
                        @endif
                    </div>

                    <div class="zazu-branding-preview">
                        <div class="zazu-branding-preview-media zazu-branding-dashboard" data-branding-preview-container="dashboard_image" aria-live="polite">
                            @if ($business->dashboard_image_path)
                                <img src="{{ route('business.media', ['type' => 'dashboard']) }}?v={{ $brandingVersion }}" alt="" data-branding-preview="dashboard_image">
                            @else
                                <span class="zazu-branding-placeholder" data-branding-placeholder="dashboard_image">Dashboard image</span>
                                <img alt="" data-branding-preview="dashboard_image" hidden>
                            @endif
                            <span class="zazu-branding-loading" data-branding-loading="dashboard_image" hidden>
                                <span class="zazu-spinner" aria-hidden="true"></span>
                                <span>Preparing preview…</span>
                            </span>
                        </div>
                        <strong>Dashboard picture</strong>
                        <span class="zazu-branding-description">A visual focal image for the dashboard.</span>
                        <label class="zazu-btn zazu-btn-secondary mt-3 cursor-pointer">
                            Choose dashboard picture
                            <input type="file" name="dashboard_image" accept="image/jpeg,image/png,image/webp" class="hidden" data-branding-upload="dashboard_image">
                        </label>
                        <span class="zazu-branding-file" data-branding-file="dashboard_image" aria-live="polite"></span>
                        @if ($business->dashboard_image_path)
                            <label class="zazu-check-row mt-3"><input type="checkbox" name="remove_dashboard_image" value="1"><span>Remove current picture</span></label>
                        @endif
                    </div>

                    <div class="zazu-branding-preview">
                        <div class="zazu-branding-preview-media zazu-branding-wallpaper" data-branding-preview-container="wallpaper" aria-live="polite">
                            @if ($business->wallpaper_path)
                                <img src="{{ route('business.media', ['type' => 'wallpaper']) }}?v={{ $brandingVersion }}" alt="" data-branding-preview="wallpaper">
                            @else
                                <span class="zazu-branding-placeholder" data-branding-placeholder="wallpaper">Workspace wallpaper</span>
                                <img alt="" data-branding-preview="wallpaper" hidden>
                            @endif
                            <span class="zazu-branding-loading" data-branding-loading="wallpaper" hidden>
                                <span class="zazu-spinner" aria-hidden="true"></span>
                                <span>Preparing preview…</span>
                            </span>
                        </div>
                        <strong>Workspace wallpaper</strong>
                        <span class="zazu-branding-description">Optional art used subtly behind the workspace.</span>
                        <label class="zazu-btn zazu-btn-secondary mt-3 cursor-pointer">
                            Choose wallpaper
                            <input type="file" name="wallpaper" accept="image/jpeg,image/png,image/webp" class="hidden" data-branding-upload="wallpaper">
                        </label>
                        <span class="zazu-branding-file" data-branding-file="wallpaper" aria-live="polite"></span>
                        @if ($business->wallpaper_path)
                            <label class="zazu-check-row mt-3"><input type="checkbox" name="remove_wallpaper" value="1"><span>Remove current wallpaper</span></label>
                        @endif
                    </div>
                </div>>
            </section>

            <div class="zazu-actionbar">
                <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                <button type="submit" class="zazu-btn zazu-btn-primary" data-branding-save>
                    <span data-branding-save-label>Save business appearance</span>
                    <span class="zazu-btn-spinner" data-branding-save-spinner hidden aria-hidden="true"></span>
                </button>
            </div>
        </div>

        <aside class="zazu-form-aside">
            <div class="zazu-context-card">
                <div class="zazu-context-title">Keep it readable</div>
                <div class="zazu-context-copy">Choose artwork with enough empty space and contrast. Zazu places a translucent surface over wallpaper so operational information stays readable in light and dark mode.</div>
            </div>
            <div class="zazu-context-card mt-4">
                <div class="zazu-context-title">Privacy note</div>
                <div class="zazu-context-copy">Only upload artwork you have the right to use. Do not put customer documents or private personal information into business branding images.</div>
            </div>
        </aside>
    </form>
</x-app-layout>