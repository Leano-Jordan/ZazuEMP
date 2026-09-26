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
        @csrf
        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Choose a service</div>
                        <div class="zazu-form-section-copy">Pick the closest match. You can add more services from the job workspace afterwards.</div>
                    </div>

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
                            <span id="description-help" class="zazu-field-help">Zazu fills this in when you choose a service. Change it only when you need a more specific description.</span>
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
                                <option value="">Choose a unit</option>
                                <option value="service" @selected(old('unit') === 'service')>Service</option>
                                <option value="person" @selected(old('unit') === 'person')>People</option>
                                <option value="item" @selected(old('unit') === 'item')>Items</option>
                                <option value="hour" @selected(old('unit') === 'hour')>Hours</option>
                                <option value="day" @selected(old('unit') === 'day')>Days</option>
                                <option value="other" @selected(old('unit') === 'other')>Other</option>
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
        const help = document.getElementById('description-help');

        document.querySelectorAll('[data-service-name]').forEach((input) => {
            input.addEventListener('change', () => {
                if (!input.checked) return;
                description.value = input.dataset.serviceName;
                description.readOnly = true;
                help.textContent = 'This service name came from your service list. You can edit it if this job needs a more specific description.';
            });
        });

        document.querySelector('[data-other-service]')?.addEventListener('change', (event) => {
            if (!event.target.checked) return;
            description.value = '';
            description.readOnly = false;
            description.focus();
            help.textContent = 'Type the service only because it is not in the list.';
        });
    </script>
</x-app-layout>