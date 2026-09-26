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