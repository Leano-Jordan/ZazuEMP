<x-app-layout>
    <x-slot:title>Edit job</x-slot:title>
    <x-slot:heading>Edit job</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.show', $event) }}" class="zazu-btn zazu-btn-ghost">Job workspace</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Job details</div>
            <h2 class="zazu-command-title">{{ $event->name }}</h2>
            <p class="zazu-command-copy">Update the job details without leaving this record.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Reference</div>
            <div class="zazu-command-meta-value text-base">{{ $event->reference }}</div>
        </div>
    </section>

    <form method="POST" action="{{ route('work.update', $event) }}">
        @csrf
        @method('PUT')

        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Job details</div>
                        <div class="zazu-form-section-copy">Change the information that defines this work.</div>
                    </div>

                    <div class="zazu-form-grid">
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Customer</span>
                            @if ($hasQuotes)
                                <div class="mb-2 rounded-lg border border-[var(--zazu-border)] bg-[var(--zazu-warning-soft)] px-3 py-2 text-[11px] leading-5 text-[var(--zazu-warning-ink)]">
                                    Customer is locked because this Work record already has a quote. This protects historical commercial attribution.
                                </div>
                                <input type="hidden" name="customer_id" value="{{ $event->customer_id }}">
                            @endif
                            <select id="customer_id" name="customer_id" required class="zazu-select" @disabled($hasQuotes)>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id', $event->customer_id) == $customer->id)>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Work / event name</span>
                            <input name="name" value="{{ old('name', $event->name) }}" required class="zazu-input">
                            @error('name')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <fieldset class="zazu-field zazu-field-wide">
                            <legend class="zazu-label">What kind of job is it? <span class="zazu-required">*</span></legend>
                            <div class="zazu-choice-grid">
                                @foreach (config('zazu.job_types') as $jobType)
                                    <label class="zazu-choice-card">
                                        <input type="radio" name="event_type" value="{{ $jobType }}" @checked(old('event_type', $event->event_type) === $jobType) required>
                                        <span>{{ $jobType }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('event_type')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </fieldset>

                        <label class="zazu-field">
                            <span class="zazu-label">Job date <span class="zazu-required">*</span></span>
                            <input type="date" name="event_date" value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}" required class="zazu-input">
                            @error('event_date')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Day contact <span class="font-normal text-[var(--zazu-faint)]">(optional)</span></span>
                            <select id="event_day_contact_id" name="event_day_contact_id" class="zazu-select">
                                <option value="">No day contact</option>
                            </select>
                            @error('event_day_contact_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Night contact <span class="font-normal text-[var(--zazu-faint)]">(optional)</span></span>
                            <select id="event_night_contact_id" name="event_night_contact_id" class="zazu-select">
                                <option value="">No night contact</option>
                            </select>
                            @error('event_night_contact_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Status</span>
                            <select name="status" class="zazu-select">
                                @foreach (['draft' => 'Draft', 'confirmed' => 'Confirmed', 'in_progress' => 'In progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $event->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                </section>

                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Location and notes</div>
                        <div class="zazu-form-section-copy">Keep the address and notes with the work record.</div>
                    </div>

                    <div class="zazu-form-grid">
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Job location</span>
                            <input name="event_address" value="{{ old('event_address', $event->event_address) }}" class="zazu-input">
                            @error('event_address')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Notes</span>
                            <textarea name="notes" rows="6" class="zazu-textarea">{{ old('notes', $event->notes) }}</textarea>
                            @error('notes')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                </section>

                <div class="zazu-actionbar">
                    <a href="{{ route('work.show', $event) }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                    <button class="zazu-btn zazu-btn-primary">Save changes</button>
                </div>
            </div>

            <aside class="zazu-form-aside">
                <div class="zazu-context-card">
                    <div class="zazu-context-title">Current workspace</div>
                    <div class="zazu-context-copy">Changes here update the source record used by the rest of the workspace.</div>
                    <div class="mt-4 grid gap-3">
                        <div>
                            <div class="zazu-detail-label">Reference</div>
                            <div class="zazu-detail-value mt-1">{{ $event->reference }}</div>
                        </div>
                        <div>
                            <div class="zazu-detail-label">Created</div>
                            <div class="zazu-detail-value mt-1">{{ $event->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </form>

    <script>
        const customers = @json($customers->map(fn ($customer) => [
            'id' => $customer->id,
            'contacts' => $customer->contacts->map(fn ($contact) => [
                'id' => $contact->id,
                'name' => $contact->name,
                'phone' => $contact->phone,
                'label' => $contact->label,
            ])->values(),
        ])->values());

        const selectedDayContact = @json(old('event_day_contact_id', $event->event_day_contact_id));
        const selectedNightContact = @json(old('event_night_contact_id', $event->event_night_contact_id));
        const customerSelect = document.getElementById('customer_id');
        const dayContactSelect = document.getElementById('event_day_contact_id');
        const nightContactSelect = document.getElementById('event_night_contact_id');

        function refreshContacts(daySelected = selectedDayContact, nightSelected = selectedNightContact) {
            const customer = customers.find(item => String(item.id) === customerSelect.value);

            for (const select of [dayContactSelect, nightContactSelect]) {
                select.innerHTML = '<option value="">No contact selected</option>';
            }

            if (!customer) return;

            for (const contact of customer.contacts) {
                const suffix = [contact.label, contact.phone].filter(Boolean).join(' · ');

                const dayOption = document.createElement('option');
                dayOption.value = contact.id;
                dayOption.textContent = contact.name + (suffix ? ' · ' + suffix : '');
                dayOption.selected = String(contact.id) === String(daySelected);
                dayContactSelect.appendChild(dayOption);

                const nightOption = dayOption.cloneNode(true);
                nightOption.selected = String(contact.id) === String(nightSelected);
                nightContactSelect.appendChild(nightOption);
            }
        }

        customerSelect.addEventListener('change', () => refreshContacts('', ''));
        refreshContacts();
    </script>
</x-app-layout>
