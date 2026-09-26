<x-app-layout>
    <x-slot:title>Create work</x-slot:title>
    <x-slot:heading>Create work</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-ghost">← Work</a>
    </x-slot:headerAction>

    @if ($customers->isEmpty())
        <section class="zazu-panel">
            <div class="zazu-panel-title">A customer is needed first</div>
            <div class="zazu-panel-copy">A work record starts from a customer. Create one, then return here to build the event or job workspace.</div>
            <a href="{{ route('customers.create') }}" class="zazu-btn zazu-btn-primary mt-4">Add customer</a>
        </section>
    @else
        <section class="zazu-command-band">
            <div>
                <div class="zazu-eyebrow">Operations / New workspace</div>
                <h2 class="zazu-command-title">Create work</h2>
                <p class="zazu-command-copy">Capture the core event or job facts now. Requirements, quotes, travel and costs can attach to this workspace later.</p>
            </div>
        </section>

        <form method="POST" action="{{ route('work.store') }}">
            @csrf

            <div class="zazu-editor">
                <div class="zazu-form-main">
                    <section class="zazu-form-section">
                        <div class="zazu-form-section-head">
                            <div class="zazu-form-section-title">Core work details</div>
                            <div class="zazu-form-section-copy">Start with the relationship, name and timing of the work.</div>
                        </div>

                        <div class="zazu-form-grid">
                            <label class="zazu-field zazu-field-wide">
                                <span class="zazu-label">Customer</span>
                                <select id="customer_id" name="customer_id" required class="zazu-select">
                                    <option value="">Select customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" @selected(old('customer_id', $selectedCustomerId) == $customer->id)>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
                            </label>

                            <label class="zazu-field zazu-field-wide">
                                <span class="zazu-label">Work / event name</span>
                                <input name="name" value="{{ old('name') }}" required class="zazu-input" placeholder="e.g. Mokoena Wedding">
                                @error('name')<span class="zazu-field-error">{{ $message }}</span>@enderror
                            </label>

                            <label class="zazu-field">
                                <span class="zazu-label">Type</span>
                                <input name="event_type" value="{{ old('event_type') }}" class="zazu-input" placeholder="Wedding, funeral, hire...">
                                @error('event_type')<span class="zazu-field-error">{{ $message }}</span>@enderror
                            </label>

                            <label class="zazu-field">
                                <span class="zazu-label">Event date <span class="zazu-required">*</span></span>
                                <input type="date" name="event_date" value="{{ old('event_date') }}" required class="zazu-input">
                                @error('event_date')<span class="zazu-field-error">{{ $message }}</span>@enderror
                            </label>

                            <label class="zazu-field zazu-field-wide">
                                <span class="zazu-label">Event-day contact <span class="zazu-required">*</span><span class="font-normal text-[var(--zazu-faint)]"> optional</span></span>
                                <select id="event_day_contact_id" name="event_day_contact_id" class="zazu-select">
                                    <option value="">Select a customer first</option>
                                </select>
                                @error('event_day_contact_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
                            </label>
                        </div>
                    </section>

                    <section class="zazu-form-section">
                        <div class="zazu-form-section-head">
                            <div class="zazu-form-section-title">Operational context</div>
                            <div class="zazu-form-section-copy">Useful information the team may need before the requirements layer is built.</div>
                        </div>

                        <div class="zazu-form-grid">
                            <label class="zazu-field zazu-field-wide">
                                <span class="zazu-label">Event location</span>
                                <input name="event_address" value="{{ old('event_address') }}" class="zazu-input" placeholder="Address or venue">
                                @error('event_address')<span class="zazu-field-error">{{ $message }}</span>@enderror
                            </label>

                            <label class="zazu-field zazu-field-wide">
                                <span class="zazu-label">Notes</span>
                                <textarea name="notes" rows="5" class="zazu-textarea" placeholder="Important context, instructions or customer notes">{{ old('notes') }}</textarea>
                                @error('notes')<span class="zazu-field-error">{{ $message }}</span>@enderror
                            </label>
                        </div>
                    </section>

                    <div class="zazu-actionbar">
                        <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                        <button class="zazu-btn zazu-btn-primary">Create workspace</button>
                    </div>
                </div>

                <aside class="zazu-form-aside">
                    <div class="zazu-context-card">
                        <div class="zazu-context-title">Workspace flow</div>
                        <div class="zazu-context-copy">This record is deliberately small at creation time. Each later layer should add operational value instead of turning this into one giant form.</div>

                        <div class="zazu-step-list">
                            <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Work</div><div class="zazu-step-copy">Core event or job record</div></div></div>
                            <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Requirements</div><div class="zazu-step-copy">Capabilities and quantities</div></div></div>
                            <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Quote</div><div class="zazu-step-copy">Versioned commercial offer</div></div></div>
                            <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Preparation</div><div class="zazu-step-copy">Buying and readiness</div></div></div>
                            <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Execution</div><div class="zazu-step-copy">Delivery and accountability</div></div></div>
                        </div>
                    </div>
                </aside>
            </div>
        </form>

        <script>
            const customers = @json($customerOptions);
            const oldContactId = @json(old('event_day_contact_id'));

            const customerSelect = document.getElementById('customer_id');
            const contactSelect = document.getElementById('event_day_contact_id');

            function refreshContacts(selectedContactId = oldContactId) {
                const customer = customers.find(item => String(item.id) === customerSelect.value);
                contactSelect.innerHTML = '<option value="">No event-day contact</option>';

                if (!customer) return;

                if (customer.contacts.length === 0) {
                    contactSelect.innerHTML = '<option value="">No contacts available</option>';
                    return;
                }

                customer.contacts.forEach(contact => {
                    const option = document.createElement('option');
                    option.value = contact.id;
                    option.textContent = contact.name + (contact.label ? ' · ' + contact.label : '') + (contact.phone ? ' · ' + contact.phone : '');
                    option.selected = String(contact.id) === String(selectedContactId);
                    contactSelect.appendChild(option);
                });
            }

            customerSelect.addEventListener('change', () => refreshContacts(''));
            refreshContacts();
        </script>
    @endif
</x-app-layout>
