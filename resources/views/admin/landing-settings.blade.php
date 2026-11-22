<x-app-layout>
    <x-slot:title>Landing page administration</x-slot:title>
    <x-slot:heading>Landing page administration</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('admin.sentinel') }}" class="zazu-btn zazu-btn-ghost">Open Sentinel diagnostics</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Platform administration</div>
            <h2 class="zazu-command-title">Control the public Zazu presentation</h2>
            <p class="zazu-command-copy">Manage all six image positions used across the public landing page. Preview every bundled image, upload your own approved photographs, and experiment without changing workspace branding.</p>
        </div>
    </section>

    @if(session('success'))
        <div class="zazu-toast zazu-toast-success" role="status">{{ session('success') }}</div>
    @endif

    @php
        $current = [
            'hero_image' => $images['hero'] ?? null,
            'operations_image' => $images['operations'] ?? null,
            'resources_image' => $images['resources'] ?? null,
            'control_image' => $images['control'] ?? null,
            'venue_image' => $images['venue'] ?? null,
            'decor_image' => $images['decor'] ?? null,
        ];
        $slots = [
            'hero_image' => ['title' => 'Hero event image', 'copy' => 'The main photograph beside the public introduction.', 'preview' => 'Hero preview'],
            'operations_image' => ['title' => 'Operations image', 'copy' => 'Bookings, event delivery and preparation.', 'preview' => 'Operations preview'],
            'resources_image' => ['title' => 'Resources and sound image', 'copy' => 'Equipment, sound, staging and hire.', 'preview' => 'Resources preview'],
            'control_image' => ['title' => 'Commercial control image', 'copy' => 'Finance, quotes and business control.', 'preview' => 'Commercial control preview'],
            'venue_image' => ['title' => 'Venue image', 'copy' => 'Event spaces, tents and venue context.', 'preview' => 'Venue preview'],
            'decor_image' => ['title' => 'Decor and reception image', 'copy' => 'Styling, table settings and reception detail.', 'preview' => 'Decor preview'],
        ];
        $firstImage = $library ? reset($library)['url'] : '';
        $maxUploadFiles = (int) config('zazu.landing_media_uploads.max_files', 8);
        $maxUploadKilobytes = (int) config('zazu.landing_media_uploads.max_kilobytes', 5120);
    @endphp

    <form method="POST" action="{{ route('admin.landing.settings.update') }}" class="zazu-editor" enctype="multipart/form-data" data-zazu-landing-media-form>
        @csrf
        @method('PUT')

        <div class="zazu-form-main">
            <section class="zazu-form-section">
                <div class="zazu-form-section-head">
                    <div>
                        <div class="zazu-form-section-title">Landing page positions</div>
                        <div class="zazu-form-section-copy">Choose an image for every public position. Each selection updates its preview immediately; the live landing page changes only after saving.</div>
                    </div>
                </div>

                <div class="zazu-landing-slot-grid">
                    @foreach($slots as $field => $slot)
                        @php
                            $selectedUrl = $current[$field] ?: $firstImage;
                        @endphp
                        <article class="zazu-landing-slot">
                            <div class="zazu-field">
                                <label class="zazu-label" for="zazu-{{ $field }}">{{ $slot['title'] }}</label>
                                <span class="zazu-field-help">{{ $slot['copy'] }}</span>
                                <select id="zazu-{{ $field }}" name="{{ $field }}" class="zazu-select" required data-zazu-landing-select>
                                    @foreach($library as $key => $image)
                                        <option value="{{ $key }}" data-image-url="{{ $image['url'] }}" @selected($selectedUrl === $image['url'])>
                                            {{ $image['label'] }} · {{ $image['source'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error($field)<span class="zazu-field-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="zazu-admin-image-preview" aria-label="{{ $slot['preview'] }}">
                                <img src="{{ $selectedUrl }}" alt="{{ $slot['preview'] }}" loading="lazy" data-zazu-landing-preview>
                                <span>Live selection preview</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="zazu-form-section">
                <div class="zazu-form-section-head">
                    <div>
                        <div class="zazu-form-section-title">Complete image library</div>
                        <div class="zazu-form-section-copy">Every distinct bundled visual and every uploaded image is available to all six positions. Reuse an image where it strengthens the composition.</div>
                    </div>
                    <span class="zazu-chip zazu-chip-neutral">{{ count($library) }} images</span>
                </div>

                <div class="zazu-landing-library-grid">
                    @forelse($library as $image)
                        <figure class="zazu-landing-library-card">
                            <img src="{{ $image['url'] }}" alt="{{ $image['label'] }}" loading="lazy">
                            <figcaption>
                                <strong>{{ $image['label'] }}</strong>
                                <span>{{ $image['source'] }}</span>
                            </figcaption>
                        </figure>
                    @empty
                        <div class="zazu-empty">No landing images are currently available.</div>
                    @endforelse
                </div>
            </section>

            <section class="zazu-form-section">
                <div class="zazu-form-section-head">
                    <div>
                        <div class="zazu-form-section-title">Upload image experiments</div>
                        <div class="zazu-form-section-copy">Upload up to {{ $maxUploadFiles }} JPEG, PNG or WebP images at once. Each file can be up to {{ number_format($maxUploadKilobytes / 1024, 0) }} MB. New uploads are added to the library after saving and can then be assigned to any position.</div>
                    </div>
                </div>

                <label class="zazu-landing-upload">
                    <span class="zazu-landing-upload-title">Choose photographs to preview and upload</span>
                    <span class="zazu-landing-upload-copy">Use real event photography you own or are licensed to use. SVG, HTML and executable files are not accepted.</span>
                    <input type="file" name="uploads[]" accept="image/jpeg,image/png,image/webp" multiple data-zazu-landing-uploads>
                </label>
                <div class="zazu-field-help mt-2">Minimum 600 × 400 pixels. Maximum 6,000 × 6,000 pixels. Uploaded filenames are replaced with random identifiers.</div>
                @error('uploads')<span class="zazu-field-error">{{ $message }}</span>@enderror
                @error('uploads.*')<span class="zazu-field-error">{{ $message }}</span>@enderror
                <div class="zazu-landing-upload-preview-grid" data-zazu-upload-previews hidden></div>
            </section>

            <div class="zazu-actionbar">
                <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                <button type="submit" class="zazu-btn zazu-btn-primary">Save positions and upload images</button>
            </div>
        </div>

        <aside class="zazu-form-aside">
            <div class="zazu-context-card">
                <div class="zazu-context-title">Platform authority</div>
                <div class="zazu-context-copy">These controls affect the public Zazu landing page only. They do not modify a business workspace’s branding, customer data or uploaded event attachments.</div>
            </div>
            <div class="zazu-context-card mt-4">
                <div class="zazu-context-title">Safe image handling</div>
                <div class="zazu-context-copy">Only validated raster images are accepted. Zazu stores uploads under a dedicated landing-media path with random filenames, and the landing page uses local images rather than third-party image hosts.</div>
            </div>
            <div class="zazu-context-card mt-4">
                <div class="zazu-context-title">Experiment workflow</div>
                <div class="zazu-context-copy">Preview a local upload, save it into the library, then select it in one or more positions. Existing choices remain active until you save a new assignment.</div>
            </div>
        </aside>
    </form>

    <script nonce="{{ request()->attributes->get('cspNonce') }}">
        (() => {
            document.querySelectorAll('[data-zazu-landing-select]').forEach(select => {
                const slot = select.closest('.zazu-landing-slot');
                const preview = slot?.querySelector('[data-zazu-landing-preview]');
                if (!preview) return;

                const refresh = () => {
                    const option = select.selectedOptions[0];
                    if (!option?.dataset.imageUrl) return;
                    preview.src = option.dataset.imageUrl;
                    preview.alt = option.textContent.trim();
                };

                select.addEventListener('change', refresh);
                refresh();
            });

            const input = document.querySelector('[data-zazu-landing-uploads]');
            const previewGrid = document.querySelector('[data-zazu-upload-previews]');
            if (!input || !previewGrid) return;

            let previewUrls = [];
            input.addEventListener('change', () => {
                previewUrls.forEach(url => URL.revokeObjectURL(url));
                previewUrls = [];
                previewGrid.replaceChildren();

                [...(input.files || [])].forEach(file => {
                    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) return;

                    const url = URL.createObjectURL(file);
                    previewUrls.push(url);

                    const card = document.createElement('figure');
                    card.className = 'zazu-landing-library-card zazu-landing-upload-preview';

                    const image = document.createElement('img');
                    image.src = url;
                    image.alt = file.name;

                    const caption = document.createElement('figcaption');
                    const name = document.createElement('strong');
                    name.textContent = file.name;
                    const size = document.createElement('span');
                    size.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';

                    caption.append(name, size);
                    card.append(image, caption);
                    previewGrid.append(card);
                });

                previewGrid.hidden = previewGrid.childElementCount === 0;
            });

            window.addEventListener('beforeunload', () => {
                previewUrls.forEach(url => URL.revokeObjectURL(url));
            });
        })();
    </script>
</x-app-layout>
