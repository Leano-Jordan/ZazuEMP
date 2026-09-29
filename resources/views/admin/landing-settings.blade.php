<x-app-layout>
    <x-slot:title>Landing page administration</x-slot:title>
    <x-slot:heading>Landing page administration</x-slot:heading>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Platform administration</div>
            <h2 class="zazu-command-title">Control the public Zazu presentation</h2>
            <p class="zazu-command-copy">Choose the event, catering and sound-hire imagery used in the four public landing-page positions. This is a platform-level control, not a workspace branding setting.</p>
        </div>
    </section>

    @if(session('success'))
        <div class="zazu-toast zazu-toast-success" role="status">{{ session('success') }}</div>
    @endif

    @php
        $current = [
            'hero_image' => $settings?->hero_image_path,
            'operations_image' => $settings?->operations_image_path,
            'resources_image' => $settings?->resources_image_path,
            'control_image' => $settings?->control_image_path,
        ];
        $slots = [
            'hero_image' => ['title' => 'Hero event image', 'copy' => 'The main visual beside the public landing-page introduction.'],
            'operations_image' => ['title' => 'Operations image', 'copy' => 'Supports the booking, preparation and event-work story.'],
            'resources_image' => ['title' => 'Resources image', 'copy' => 'Supports equipment, sound, staging and hire.'],
            'control_image' => ['title' => 'Commercial control image', 'copy' => 'Supports the finance and commercial-control story.'],
        ];
    @endphp

    <form method="POST" action="{{ route('admin.landing.settings.update') }}" class="zazu-editor">
        @csrf
        @method('PUT')

        <div class="zazu-form-main">
            <details class="zazu-form-section zazu-settings-collapsible" open>
                <summary class="zazu-form-section-head zazu-settings-section-toggle">
                    <span>
                        <span class="zazu-form-section-title">Landing page imagery</span>
                        <span class="zazu-form-section-copy">Assign a curated image to each public slot. The same image may be used more than once when the composition calls for it.</span>
                    </span>
                    <span class="zazu-settings-toggle-icon" aria-hidden="true">+</span>
                </summary>

                <div class="zazu-form-grid">
                    @foreach($slots as $field => $slot)
                        <div class="zazu-field zazu-field-wide">
                            <span class="zazu-label">{{ $slot['title'] }}</span>
                            <span class="zazu-field-help">{{ $slot['copy'] }}</span>
                            <select name="{{ $field }}" class="zazu-select" required>
                                @foreach($library as $key => $image)
                                    <option value="{{ $key }}" @selected($current[$field] === $image['url'])>{{ $image['label'] }}</option>
                                @endforeach
                            </select>
                            <div class="zazu-admin-image-preview" style="background-image:url('{{ $current[$field] ?: array_values($library)[0]['url'] }}')" aria-label="Current landing image preview"></div>
                            @error($field)<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </div>
                    @endforeach
                </div>
            </details>

            <div class="zazu-actionbar">
                <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                <button type="submit" class="zazu-btn zazu-btn-primary">Save landing page</button>
            </div>
        </div>

        <aside class="zazu-form-aside">
            <div class="zazu-context-card">
                <div class="zazu-context-title">Admin authority</div>
                <div class="zazu-context-copy">These controls affect the public Zazu landing page. They are intentionally separated from workspace-level business branding.</div>
            </div>
            <div class="zazu-context-card mt-4">
                <div class="zazu-context-title">Image direction</div>
                <div class="zazu-context-copy">The current library is deliberately centred on events, catering, sound, staging and commercial delivery—not generic stock imagery.</div>
            </div>
        </aside>
    </form>
</x-app-layout>