<x-app-layout>
    <x-slot:title>Add service to job</x-slot:title>
    <x-slot:heading>Add service to job</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.show', $event) }}" class="zazu-btn zazu-btn-ghost">Job workspace</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · {{ $event->name }}</div>
            <h2 class="zazu-command-title">What does this job need?</h2>
            <p class="zazu-command-copy">Choose a service from the list. You only need to type something when the service is not listed.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('work.requirements.store', $event) }}" id="service-form">
        <input type="hidden" name="capability_id" id="capability_id" value="{{ old('capability_id') }}">
        @csrf
        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Choose a service</div>
                        <div class="zazu-form-section-copy">Pick the closest match. You can add more services from the job workspace afterwards.</div>
                    </div>

                    @if ($capabilities->isNotEmpty())
                        <div class="mb-6">
                            <div class="zazu-label mb-2">Your saved services</div>
                            <div class="zazu-catalogue-mini-grid">
                                @foreach ($capabilities as $capability)
                                    <button type="button" class="zazu-catalogue-mini" data-capability-id="{{ $capability->id }}" data-service-name="{{ $capability->name }}" data-category="{{ $capability->category }}" data-unit="{{ $capability->default_unit }}" data-price="{{ $capability->default_price }}">
                                        <span class="zazu-catalogue-mini-image">
                                            @if ($capability->image_path)
                                                <img src="{{ Storage::disk('public')->url($capability->image_path) }}" alt="">
                                            @else
                                                {{ strtoupper(substr($capability->name, 0, 1)) }}
                                            @endif
                                        </span>
                                        <span class="zazu-catalogue-mini-name">{{ $capability->name }}</span>
                                        @if ($capability->default_price !== null)
                                            <span class="zazu-catalogue-mini-price">ZAR {{ number_format((float) $capability->default_price, 2) }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="zazu-service-groups">
                        @foreach ($serviceCategories as $group => $services)
                            <fieldset class="zazu-service-group">
                                <legend>{{ $group }}</legend>
                                <div class="zazu-choice-grid">
                                    @foreach ($services as $service)
                                        <label class="zazu-choice-card zazu-service-choice">
                                            <input type="radio" name="category" value="{{ $group }}" data-service-name="{{ $service }}" @checked(old('category') === $group && old('description') === $service)>
                                            <span>{{ $service }}</span>
                                        </label>
                                    @endforeach
                                    @if ($group === 'Other')
                                        <label class="zazu-choice-card zazu-service-choice">
                                            <input type="radio" name="category" value="Other" data-other-service @checked(old('category') === 'Other' && old('description') !== '')>
                                            <span>Something else</span>
                                        </label>
                                    @endif
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                    @error('category')<span class="zazu-field-error mt-3">{{ $message }}</span>@enderror

                    <div class="zazu-form-grid mt-6">
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Service description <span class="zazu-required">*</span></span>
                            <input id="description" name="description" value="{{ old('description') }}" required class="zazu-input">
                            @error('description')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">How many?</span>
                            <input type="number" step="0.01" min="0.01" name="quantity" value="{{ old('quantity', 1) }}" required class="zazu-input">
                            @error('quantity')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Unit</span>
                            <select name="unit" class="zazu-select" id="unit-select">
                                <option value="">Not specified</option>
                                @foreach (config('zazu.units') as $value => $label)
                                    <option value="{{ $value }}" @selected(old('unit') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="zazu-field-help">Use Other only if the unit is not listed.</span>
                            @error('unit')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Special details</span>
                            <textarea name="notes" rows="4" class="zazu-textarea">{{ old('notes') }}</textarea>
                            <span class="zazu-field-help">Colour, size, menu, timing, quantity details or anything else the team needs to know.</span>
                        </label>
                    </div>
                </section>

                <div class="zazu-actionbar">
                    <a href="{{ route('work.show', $event) }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                    <button class="zazu-btn zazu-btn-primary">Add service to job</button>
                </div>
            </div>

            <aside class="zazu-form-aside">
                <div class="zazu-context-card zazu-next-card">
                    <div class="zazu-context-title">Next</div>
                    <div class="zazu-context-copy">After saving, Zazu takes you back to the job workspace. When the services are ready, the workspace will show you the quote action.</div>
                </div>
            </aside>
        </div>
    </form>

    <script>
        const description = document.getElementById('description');
        const capabilityId = document.getElementById('capability_id');
        const unitSelect = document.getElementById('unit-select');
         document.querySelectorAll('[data-capability-id]').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.zazu-catalogue-mini').forEach(item => item.classList.remove('selected'));
                button.classList.add('selected');
                capabilityId.value = button.dataset.capabilityId;
                description.value = button.dataset.serviceName;
                const unit = button.dataset.unit;
                if (unit) {
                    const option = [...unitSelect.options].find(item => item.value === unit);
                    if (option) unitSelect.value = unit;
                }
             });
        });

        document.querySelectorAll('[data-service-name]').forEach((input) => {
            input.addEventListener('change', () => {
                if (!input.checked) return;
                capabilityId.value = '';
                description.value = input.dataset.serviceName;
             });
        });

        document.querySelector('[data-other-service]')?.addEventListener('change', (event) => {
            if (!event.target.checked) return;
            capabilityId.value = '';
            description.value = '';
            description.focus();
        });
    </script>
</x-app-layout>