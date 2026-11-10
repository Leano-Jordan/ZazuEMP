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

<form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="zazu-editor zazu-settings-page" data-branding-form>
        @csrf
        @method('PUT')

        <div class="zazu-form-main">
            <details class="zazu-form-section zazu-settings-collapsible" open>
                <summary class="zazu-form-section-head zazu-settings-section-toggle">
                    <span>
                        <span class="zazu-form-section-title">Business identity</span>
                        <span class="zazu-form-section-copy">This name appears in the application shell and business records.</span>
                    </span>
                    <span class="zazu-settings-toggle-icon" aria-hidden="true">+</span>
                </summary>
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
            </details>

            <details class="zazu-form-section zazu-settings-collapsible" @if($errors->hasAny(['legal_name','trading_name','registration_type','registration_number','income_tax_number','tax_regime','vat_status','vat_number','paye_number','uif_number','sdl_number','financial_year_end','representative_name','representative_email','representative_phone','tcs_reference','tcs_pin','tcs_pin_expires_at','default_vat_rate','tax_effective_from','food_handling','employees','government_supply','tendering','regulated_activity','compliance_notes'])) open @endif>
                <summary class="zazu-form-section-head zazu-settings-section-toggle">
                    <span>
                        <span class="zazu-form-section-title">Tax & compliance profile</span>
                        <span class="zazu-form-section-copy">Store the business identity, tax registrations and activity flags that Zazu can reuse when preparing commercial and compliance documents.</span>
                    </span>
                    <span class="zazu-settings-toggle-icon" aria-hidden="true">+</span>
                </summary>

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
                        <input name="tcs_pin" value="" class="zazu-input" autocomplete="off" placeholder="{{ $hasTcsPin ? 'PIN stored securely — leave blank to keep it' : 'Enter only when needed' }}">
                        @if ($hasTcsPin)
                            <span class="zazu-field-help">PIN stored securely — leave blank to keep it</span>
                        @else
                            <span class="zazu-field-help">Stored encrypted. Zazu should never display or expose the PIN unnecessarily.</span>
                        @endif
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
            </details>

            <details class="zazu-form-section zazu-settings-collapsible">
                <summary class="zazu-form-section-head zazu-settings-section-toggle">
                    <span>
                        <span class="zazu-form-section-title">Tender & evidence readiness</span>
                        <span class="zazu-form-section-copy">Keep the source documents behind the business boundary so Zazu can build evidence packs without making a legal or procurement decision for the business.</span>
                    </span>
                    <span class="zazu-settings-toggle-icon" aria-hidden="true">+</span>
                </summary>
                <div class="zazu-panel">
                    <div class="zazu-panel-title">Compliance Centre</div>
                    <div class="zazu-panel-copy mt-1">Track CIPC, SARS, CSD, B-BBEE, UIF/SDL/COID, food-premises and tender-specific evidence where applicable.</div>
                    <a href="{{ route('settings.compliance') }}" class="zazu-btn zazu-btn-secondary mt-4">Open Compliance Centre</a>
                </div>
            </details>

            <details class="zazu-form-section zazu-settings-collapsible" open>
                <summary class="zazu-form-section-head zazu-settings-section-toggle">
                    <span>
                        <span class="zazu-form-section-title">Branding images</span>
                        <span class="zazu-form-section-copy">Use clear images. Zazu keeps the original upload and displays it responsively.</span>
                    </span>
                    <span class="zazu-settings-toggle-icon" aria-hidden="true">+</span>
                </summary>

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
                        <span class="zazu-field-error zazu-branding-error" data-branding-error="logo" role="alert" hidden>@error('logo'){{ $message }}@enderror</span>
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
                        <span class="zazu-field-error zazu-branding-error" data-branding-error="dashboard_image" role="alert" hidden>@error('dashboard_image'){{ $message }}@enderror</span>
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
                        <span class="zazu-field-error zazu-branding-error" data-branding-error="wallpaper" role="alert" hidden>@error('wallpaper'){{ $message }}@enderror</span>
                        @if ($business->wallpaper_path)
                            <label class="zazu-check-row mt-3"><input type="checkbox" name="remove_wallpaper" value="1"><span>Remove current wallpaper</span></label>
                        @endif
                    </div>
                </div>
            </details>

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
                <div class="zazu-context-title">Operational controls</div>
                <div class="zazu-context-copy">Review important business activity recorded by Zazu.</div>
                <a href="{{ route('settings.audit') }}" class="zazu-btn zazu-btn-secondary mt-4">Open activity audit</a>
            </div>
            <div class="zazu-context-card mt-4">
                <div class="zazu-context-title">Keep it readable</div>
                <div class="zazu-context-copy">Choose artwork with enough empty space and contrast. Zazu places a translucent surface over wallpaper so operational information stays readable in light and dark mode.</div>
            </div>
            <div class="zazu-context-card mt-4">
                <div class="zazu-context-title">Privacy note</div>
                <div class="zazu-context-copy">Only upload artwork you have the right to use. Do not put customer documents or private personal information into business branding images.</div>
            </div>
        </aside>
    </form>

    @php($license = $business->license)

    <section class="zazu-context-card mt-4" aria-labelledby="zazu-license-title">
        <div class="zazu-context-title" id="zazu-license-title">Zazu licence</div>
        <p class="zazu-context-copy mt-1">Install a signed licence issued for this business. Verification happens locally; activation and renewal do not require an internet connection. Never share your licence token with anyone who is not authorised to manage this business.</p>

        @if ($license)
            <div class="zazu-list-item mt-4">
                <div class="min-w-0 flex-1">
                    <strong class="block">{{ strtoupper($license->plan) }} plan · {{ $license->status === 'revoked' ? 'Revoked' : ($license->isActive() ? 'Active' : (($license->starts_at && now()->lt($license->starts_at)) ? 'Not yet active' : 'Expired')) }}</strong>
                    <p class="zazu-context-copy mt-1">
                        @if ($license->starts_at)
                            Starts {{ $license->starts_at->format('d M Y') }}
                        @endif
                        @if ($license->expires_at)
                            · Expires {{ $license->expires_at->format('d M Y H:i') }}
                        @else
                            · No expiry date
                        @endif
                    </p>
                    <p class="zazu-field-help mt-1">Licence reference: {{ $license->license_id }}</p>
                </div>
            </div>
        @else
            <p class="zazu-field-help mt-3">No signed licence is installed for this business.</p>
        @endif

        <form method="POST" action="{{ route('settings.license.store') }}" class="mt-4">
            @csrf
            <label class="zazu-field">
                <span class="zazu-label">Signed licence token</span>
                <textarea
                    name="license_token"
                    class="zazu-textarea w-full"
                    rows="4"
                    maxlength="16384"
                    autocomplete="off"
                    autocapitalize="off"
                    spellcheck="false"
                    placeholder="Paste the signed licence supplied by Rosscore"
                    required
                ></textarea>
                @error('license_token')
                    <span class="zazu-field-error">{{ $message }}</span>
                @enderror
            </label>
            <div class="flex flex-wrap items-center gap-3 mt-3">
                <button type="submit" class="zazu-btn zazu-btn-secondary">Verify & install licence</button>
                <span class="zazu-field-help">A renewal replaces the current licence only after signature, business and issue-date checks pass.</span>
            </div>
        </form>
    </section>

    <section class="zazu-context-card mt-4" data-zazu-phone-sync>
        <div class="zazu-context-title">Phone sync</div>
        <div class="zazu-context-copy">Pair a trusted Zazu phone. The initial offline working set includes up to 25 recent customers, Work records, quotes and preparation records, plus the business catalogue, suppliers, purchasing, inventory, assets, costs, invoices and expenses. Business data is stored on the paired device.</div>
        <div class="flex flex-wrap gap-3 mt-4">
            <button type="button" class="zazu-btn zazu-btn-secondary" data-zazu-generate-pairing>Generate phone pairing code</button>
            <span class="zazu-field-help" data-zazu-pairing-result aria-live="polite"></span>
        </div>
    </section>

    <section class="zazu-context-card mt-4" aria-labelledby="zazu-trusted-devices-title">
        <div class="zazu-context-title" id="zazu-trusted-devices-title">Trusted phone access</div>
        <p class="zazu-context-copy mt-1">Review phones paired with this business. Revoking a phone blocks its sync token immediately; queued changes remaining only on that phone will not be uploaded unless it is paired again.</p>
        @if ($syncDevices->isEmpty())
            <p class="zazu-field-help mt-3">No phones have been paired with this business.</p>
        @else
            <div class="grid gap-3 mt-4">
                @foreach ($syncDevices as $device)
                    <article class="zazu-list-item">
                        <div class="min-w-0 flex-1">
                            <strong class="block">{{ $device->device_name ?: 'Unpaired phone' }}</strong>
                            <p class="zazu-context-copy mt-1">
                                {{ ucfirst($device->device_type ?: 'device') }}
                                · {{ ucfirst($device->status) }}
                                · Last seen {{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}
                            </p>
                        </div>
                        @if ($device->status !== 'revoked' && $device->revoked_at === null)
                            <form method="POST" action="{{ route('settings.sync.devices.revoke', $device) }}" data-zazu-confirm="Revoke this phone's access to business sync?" data-zazu-confirm-title="Revoke trusted phone?" data-zazu-confirm-action="Revoke access">
                                @csrf
                                <button type="submit" class="zazu-btn zazu-btn-ghost">Revoke access</button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="zazu-context-card mt-4" data-zazu-offline-conflicts hidden>
        <div class="zazu-context-title">Offline sync exceptions</div>
        <p class="zazu-context-copy mt-1">These changes were saved on this device but need attention before they can sync. Fix the underlying issue before retrying, or discard a change you no longer need.</p>
        <div class="grid gap-3 mt-4" data-zazu-offline-conflict-list></div>
        <p class="zazu-field-help mt-3" data-zazu-offline-conflict-status role="status" aria-live="polite"></p>
    </section>

    <section class="zazu-context-card mt-4" aria-labelledby="zazu-data-protection-title">
        <div class="zazu-context-title" id="zazu-data-protection-title">Data protection</div>
        <p class="zazu-context-copy mt-1">Installation-wide backups and restores are managed by the system operator. Business self-service export and restore is not available in this build.</p>
    </section>

<script nonce="{{ request()->attributes->get('cspNonce') }}">
    document.addEventListener('DOMContentLoaded', () => {
        const pairingButton = document.querySelector('[data-zazu-generate-pairing]');
        const pairingResult = document.querySelector('[data-zazu-pairing-result]');

        const conflictSection = document.querySelector('[data-zazu-offline-conflicts]');
        const conflictList = document.querySelector('[data-zazu-offline-conflict-list]');
        const conflictStatus = document.querySelector('[data-zazu-offline-conflict-status]');

        const renderOfflineConflicts = async () => {
            const offline = window.ZazuOffline;
            if (!conflictSection || !conflictList || !offline) return;

            await offline.ready;
            const conflicts = (offline.getState()?.conflicts || [])
                .filter(conflict => conflict.status === 'open');

            conflictList.replaceChildren();
            conflictSection.hidden = !offline.isPaired() || conflicts.length === 0;
            if (conflicts.length === 0) return;

            for (const conflict of conflicts) {
                const row = document.createElement('article');
                row.className = 'zazu-list-item';

                const details = document.createElement('div');
                details.className = 'min-w-0 flex-1';

                const title = document.createElement('strong');
                title.className = 'block';
                title.textContent = (conflict.entity_type || 'Offline change') + ' · ' + (conflict.entity_id || 'record');

                const message = document.createElement('p');
                message.className = 'zazu-context-copy mt-1';
                message.textContent = conflict.message || 'The server rejected this saved change.';

                details.append(title, message);

                const actions = document.createElement('div');
                actions.className = 'flex flex-wrap gap-2 mt-3';

                const retry = document.createElement('button');
                retry.type = 'button';
                retry.className = 'zazu-btn zazu-btn-secondary';
                retry.textContent = 'Retry sync';

                const discard = document.createElement('button');
                discard.type = 'button';
                discard.className = 'zazu-btn zazu-btn-ghost';
                discard.textContent = 'Discard change';

                const setBusy = (busy) => {
                    retry.disabled = busy;
                    discard.disabled = busy;
                };

                retry.addEventListener('click', async () => {
                    setBusy(true);
                    conflictStatus.textContent = 'Returning the saved change to the sync queue…';
                    try {
                        await offline.resolveConflict(conflict.id, 'retry');
                        conflictStatus.textContent = 'Retry queued. Zazu will sync it now or when the connection returns.';
                    } catch (error) {
                        conflictStatus.textContent = error.message || 'Could not retry this saved change.';
                    } finally {
                        setBusy(false);
                    }
                });

                discard.addEventListener('click', async () => {
                    if (!window.confirm('Discard this saved offline change? This cannot be undone.')) return;
                    setBusy(true);
                    conflictStatus.textContent = 'Discarding the saved change…';
                    try {
                        await offline.resolveConflict(conflict.id, 'discard');
                        conflictStatus.textContent = 'Saved change discarded.';
                    } catch (error) {
                        conflictStatus.textContent = error.message || 'Could not discard this saved change.';
                    } finally {
                        setBusy(false);
                    }
                });

                actions.append(retry, discard);
                details.append(actions);
                row.append(details);
                conflictList.append(row);
            }
        };

        renderOfflineConflicts().catch(() => {
            if (conflictSection) conflictSection.hidden = true;
        });
        window.addEventListener('zazu:offline-state-updated', () => {
            renderOfflineConflicts().catch(() => {});
        });

        pairingButton?.addEventListener('click', async () => {
            pairingButton.disabled = true;
            pairingResult.textContent = 'Generating…';

            try {
                const response = await fetch('{{ route('settings.sync.pairing') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ expires_in_minutes: 10, selection: [] }),
                });

                const result = await response.json().catch(() => ({}));
                if (!response.ok || !result.pairing_code) {
                    throw new Error(result.message || 'Could not generate a pairing code.');
                }

                pairingResult.textContent = 'Code: ' + result.pairing_code + ' · expires ' + new Date(result.expires_at).toLocaleTimeString();
            } catch (error) {
                pairingResult.textContent = error.message || 'Could not generate a pairing code.';
            } finally {
                pairingButton.disabled = false;
            }
        });

        const restoreForm = document.querySelector('[data-zazu-restore-form]');
        const restoreFile = document.querySelector('[data-zazu-restore-file]');
        const restoreConfirm = document.querySelector('[data-zazu-restore-confirm]');
        const restoreSubmit = document.querySelector('[data-zazu-restore-submit]');

        const syncRestoreState = () => {
            if (!restoreSubmit) return;
            restoreSubmit.disabled = !(restoreFile?.files?.length && restoreConfirm?.checked);
        };

        restoreFile?.addEventListener('change', syncRestoreState);
        restoreConfirm?.addEventListener('change', syncRestoreState);

        syncRestoreState();
    });
</script>

</x-app-layout>