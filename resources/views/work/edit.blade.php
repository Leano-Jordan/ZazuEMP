<x-app-layout>
    <x-slot:title>Edit {{ $event->name }}</x-slot:title>
    <x-slot:heading>Edit work</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.show', $event) }}" class="zazu-btn zazu-btn-ghost">← Workspace</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Workspace settings</div>
            <h2 class="zazu-command-title">{{ $event->name }}</h2>
            <p class="zazu-command-copy">Update the work record without leaving the operational context.</p>
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
                        <div class="zazu-form-section-title">Core work details</div>
                        <div class="zazu-form-section-copy">Change the information that defines this workspace.</div>
                    </div>

                    <div class="zazu-form-grid">
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Customer</span>
                            <select id="customer_id" name="customer_id" required class="zazu-select">
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id', $event->customer_id) == $customer->id)>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Work / event name</span>
                            <input name="name" value="{{ old('name', $event->name) }}" required class="zazu-input">
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Type</span>
                            <input name="event_type" value="{{ old('event_type', $event->event_type) }}" class="zazu-input">
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Event date <span class="zazu-required">*</span></span>
                            <input type="date" name="event_date" value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}" required class="zazu-input">
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Event-day contact <span class="font-normal text-[var(--zazu-faint)]">(optional)</span></span>
                            <select id="event_day_contact_id" name="event_day_contact_id" class="zazu-select">
                                <option value="">No event-day contact</option>
                            </select>
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Status</span>
                            <select name="status" class="zazu-select">
                                @foreach (['draft' => 'Draft', 'confirmed' => 'Confirmed', 'in_progress' => 'In progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $event->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </section>

                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Operational context</div>
                        <div class="zazu-form-section-copy">Location and notes remain close to the work record.</div>
                    </div>

                    <div class="zazu-form-grid">
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Event location</span>
                            <input name="event_address" value="{{ old('event_address', $event->event_address) }}" class="zazu-input">
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Notes</span>
                            <textarea name="notes" rows="6" class="zazu-textarea">{{ old('notes', $event->notes) }}</textarea>
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

        const selectedContact = @json(old('event_day_contact_id', $event->event_day_contact_id));
        const customerSelect = document.getElementById('customer_id');
        const contactSelect = document.getElementById('event_day_contact_id');

        function refreshContacts() {
            const customer = customers.find(item => String(item.id) === customerSelect.value);
            contactSelect.innerHTML = '<option value="">No event-day contact</option>';

            if (!customer) return;

            customer.contacts.forEach(contact => {
                const option = document.createElement('option');
                option.value = contact.id;
                option.textContent = contact.name + (contact.label ? ' · ' + contact.label : '') + (contact.phone ? ' · ' + contact.phone : '');
                option.selected = String(contact.id) === String(selectedContact);
                contactSelect.appendChild(option);
            });
        }

        customerSelect.addEventListener('change', () => {
            contactSelect.value = '';
            refreshContacts();
        });

        refreshContacts();
    </script>
</x-app-layout>
