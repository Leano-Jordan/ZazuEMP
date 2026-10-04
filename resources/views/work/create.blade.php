<x-app-layout>
    <x-slot:title>New job</x-slot:title>
    <x-slot:heading>New job</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">New job</div>
            <h2 class="zazu-command-title">Start with the job essentials</h2>
            <p class="zazu-command-copy">Capture only what you need to get this job onto the schedule. Add operational detail after the job exists.</p>
        </div>
    </section>

        @if(request('start') === 'quote')
            <section class="zazu-start-intent zazu-start-intent-quote" aria-label="Quote start">
                <span class="zazu-start-intent-kicker">Quote workflow</span>
                <strong>Create the job details first, then Zazu will open the quote builder.</strong>
                <span>Customer, event and service details become the foundation for the quote.</span>
            </section>
        @endif

        <form method="POST" action="{{ route('work.store') }}" id="new-job-form">
            @csrf
        <input type="hidden" name="start_intent" value="{{ request('start') === 'quote' ? 'quote' : '' }}">
            <div class="zazu-editor">
                <div class="zazu-form-main">
                    <section class="zazu-form-section">
                        <div class="zazu-form-section-head">
                            <div class="zazu-form-section-title">Who is this job for?</div>
                            <div class="zazu-form-section-copy">Select an existing customer. Their saved contact details will be available on the job.</div>
                        </div>
                        <div class="zazu-form-grid">
                            <div class="zazu-field zazu-field-wide">
                                <span class="zazu-label">Customer <span class="zazu-required">*</span></span>
                                <div class="zazu-inline-control">
                                    <select id="customer_id" name="customer_id" required class="zazu-select">
                                        <option value="">Choose a customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" @selected(old('customer_id', $selectedCustomerId) == $customer->id)>{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="zazu-btn zazu-btn-secondary" data-open-quick-customer>Add customer</button>
                                </div>
                                @if ($customers->isEmpty())
                                    <span class="zazu-field-help">No customer yet? Add their essentials here without leaving this job.</span>
                                @else
                                    <span class="zazu-field-help">Need a new customer? Add them here and Zazu will select them for this job.</span>
                                @endif
                                <a id="quick-customer-edit-link" href="#" class="zazu-text-action mt-2 inline-flex" hidden>Complete customer profile →</a>
                                @error('customer_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </section>

                    <section class="zazu-form-section">
                        <div class="zazu-form-section-head">
                            <div class="zazu-form-section-title">What kind of job is it?</div>
                            <div class="zazu-form-section-copy">Pick the closest option. You can choose Other if none of these describes the job.</div>
                        </div>
                        <div class="zazu-choice-grid">
                            @foreach ($jobTypes as $jobType)
                                <label class="zazu-choice-card">
                                    <input type="radio" name="event_type" value="{{ $jobType }}" @checked(old('event_type') === $jobType) required>
                                    <span>{{ $jobType }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('event_type')<span class="zazu-field-error">{{ $message }}</span>@enderror
                    </section>

                    <section class="zazu-form-section">
                        <div class="zazu-form-section-head">
                            <div class="zazu-form-section-title">What are you providing?</div>
                            <div class="zazu-form-section-copy">Select everything that applies. You do not need to type service names.</div>
                            <div class="zazu-selection-summary" id="service-selection-summary" aria-live="polite">No services selected yet</div>
                        </div>

                        <div class="zazu-service-groups">
                            @foreach ($serviceCategories as $group => $services)
                                <fieldset class="zazu-service-group">
                                    <legend>{{ $group }}</legend>
                                    <div class="zazu-choice-grid">
                                        @foreach ($services as $service)
                                            <label class="zazu-choice-card zazu-service-choice" data-service-choice>
                                                <input type="checkbox" name="services[]" value="{{ $service }}" @checked(in_array($service, old('services', []), true))>
                                                <span>{{ $service }}</span>
                                            </label>
                                        @endforeach
                                        @if ($group === 'Other')
                                            <label class="zazu-choice-card zazu-service-choice">
                                                <input type="checkbox" id="other-service-toggle" value="1">
                                                <span>Something else</span>
                                            </label>
                                        @endif
                                    </div>
                                </fieldset>
                            @endforeach
                        </div>

                        <div id="other-service-wrap" class="zazu-field mt-5" hidden>
                            <label for="other_service" class="zazu-label">What else are you providing?</label>
                            <input id="other_service" name="other_service" value="{{ old('other_service') }}" class="zazu-input" maxlength="100">
                            <span class="zazu-field-help">Only type it here if the service you need is not listed above.</span>
                            @error('other_service')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </div>
                    </section>

                    <section class="zazu-form-section">
                        <div class="zazu-form-section-head">
                            <div class="zazu-form-section-title">When and where?</div>
                            <div class="zazu-form-section-copy">Only the details needed to get the job onto your schedule.</div>
                        </div>
                        <div class="zazu-form-grid">
                            <label class="zazu-field">
                                <span class="zazu-label">Job name <span class="zazu-required">*</span></span>
                                <input name="name" value="{{ old('name') }}" required class="zazu-input">
                                <span class="zazu-field-help">Use a name you will recognise later, for example “Mokoena Wedding”.</span>
                                @error('name')<span class="zazu-field-error">{{ $message }}</span>@enderror
                            </label>
                            <label class="zazu-field">
                                <span class="zazu-label">Job date <span class="zazu-required">*</span></span>
                                <input type="date" name="event_date" value="{{ old('event_date') }}" required class="zazu-input">
                                @error('event_date')<span class="zazu-field-error">{{ $message }}</span>@enderror
                            </label>
                            <label class="zazu-field zazu-field-wide">
                                <span class="zazu-label">Job location</span>
                                <input name="event_address" value="{{ old('event_address') }}" class="zazu-input">
                                <span class="zazu-field-help">Venue, street address or delivery location.</span>
                                @error('event_address')<span class="zazu-field-error">{{ $message }}</span>@enderror
                            </label>
                        </div>
                    </section>

                    <details class="zazu-progressive-details zazu-form-section">
                        <summary>Optional contacts</summary>
                        <div class="zazu-form-section-copy px-4 pt-3">Use these only when the job needs different day or night contacts.</div>
                        <div class="zazu-form-grid p-4">
                            <label class="zazu-field">
                                <span class="zazu-label">Day contact</span>
                                <select id="event_day_contact_id" name="event_day_contact_id" class="zazu-select">
                                    <option value="">No day contact</option>
                                </select>
                            </label>
                            <label class="zazu-field">
                                <span class="zazu-label">Night contact</span>
                                <select id="event_night_contact_id" name="event_night_contact_id" class="zazu-select">
                                    <option value="">No night contact</option>
                                </select>
                            </label>
                        </div>
                    </details>

                    <details class="zazu-progressive-details zazu-form-section">
                        <summary>Extra notes</summary>
                        <div class="p-4">
                            <label class="zazu-field">
                                <span class="zazu-label">Notes</span>
                                <textarea name="notes" rows="4" class="zazu-textarea"></textarea>
                                <span class="zazu-field-help">Add anything the team must know now. You can add more detail later.</span>
                            </label>
                        </div>
                    </details>

                    <div class="zazu-actionbar">
                        <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                        <button class="zazu-btn zazu-btn-primary">{{ request('start') === 'quote' ? 'Create job & continue to quote' : 'Create job' }}</button>
                    </div>
                </div>

                <aside class="zazu-form-aside">
                    <div class="zazu-context-card zazu-next-card">
                        <div class="zazu-context-title">What happens next</div>
                        <div class="zazu-context-copy">You will land on this job's workspace. Zazu will show the next useful action there, so you do not have to remember where to go.</div>
                        <div class="zazu-step-list">
                            <div class="zazu-step current"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Create job</div><div class="zazu-step-copy">Customer, service and schedule</div></div></div>
                            <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Check services</div><div class="zazu-step-copy">Add quantities and details</div></div></div>
                            <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Prepare quote</div><div class="zazu-step-copy">Add prices when you are ready</div></div></div>
                        </div>
                    </div>
                </aside>
            </div>
        </form>

        <dialog class="zazu-quick-customer-dialog" id="quick-customer-dialog" aria-labelledby="quick-customer-title">
            <div class="zazu-quick-customer-dialog-panel">
                <div class="zazu-quick-customer-dialog-head">
                    <div>
                        <div class="zazu-eyebrow">New customer</div>
                        <h2 class="zazu-quick-customer-dialog-title" id="quick-customer-title">Add a customer to this job</h2>
                        <p class="zazu-quick-customer-dialog-copy">Capture the essentials now. You can complete the customer's full profile later.</p>
                    </div>
                    <button type="button" class="zazu-quick-customer-close" data-close-quick-customer aria-label="Close">×</button>
                </div>

                <form id="quick-customer-form" action="{{ route('customers.quick_from_work') }}" method="POST">
                    @csrf
                    <div class="zazu-form-grid mt-5">
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Customer name <span class="zazu-required">*</span></span>
                            <input class="zazu-input" name="name" required maxlength="255" autocomplete="name" placeholder="e.g. Mokoena Family Events">
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Contact name <span class="zazu-required">*</span></span>
                            <input class="zazu-input" name="primary_contact_name" required maxlength="255" autocomplete="name" placeholder="e.g. Thandi Mokoena">
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Phone</span>
                            <input class="zazu-input" type="tel" name="primary_contact_phone" maxlength="50" autocomplete="tel" placeholder="071 234 5678">
                        </label>
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Email</span>
                            <input class="zazu-input" type="email" name="primary_contact_email" maxlength="255" autocomplete="email" placeholder="customer@example.com">
                        </label>
                    </div>
                    <div class="zazu-quick-customer-status" id="quick-customer-status" role="status" aria-live="polite"></div>
                    <div class="zazu-quick-customer-dialog-actions">
                        <button type="button" class="zazu-btn zazu-btn-ghost" data-close-quick-customer>Cancel</button>
                        <button type="submit" class="zazu-btn zazu-btn-primary">Save and use customer</button>
                    </div>
                </form>
            </div>
        </dialog>

        <script>
            const customers = {{ Illuminate\Support\Js::from($customerOptions) }};
            const oldDayContactId = {{ Illuminate\Support\Js::from(old('event_day_contact_id')) }};
            const oldNightContactId = {{ Illuminate\Support\Js::from(old('event_night_contact_id')) }};
            const customerSelect = document.getElementById('customer_id');
            const dayContactSelect = document.getElementById('event_day_contact_id');
            const nightContactSelect = document.getElementById('event_night_contact_id');
            const otherToggle = document.getElementById('other-service-toggle');
            const otherWrap = document.getElementById('other-service-wrap');
            const serviceSummary = document.getElementById('service-selection-summary');
            const quickCustomerDialog = document.getElementById('quick-customer-dialog');
            const quickCustomerForm = document.getElementById('quick-customer-form');
            const quickCustomerStatus = document.getElementById('quick-customer-status');
            const quickCustomerEditLink = document.getElementById('quick-customer-edit-link');
            const quickCustomerOpen = document.querySelector('[data-open-quick-customer]');
            const quickCustomerCloseButtons = document.querySelectorAll('[data-close-quick-customer]');

            function refreshContacts(selectedDay = oldDayContactId, selectedNight = oldNightContactId) {
                const customer = customers.find(item => String(item.id) === customerSelect.value);
                dayContactSelect.replaceChildren(new Option('No day contact', ''));
                nightContactSelect.replaceChildren(new Option('No night contact', ''));
                if (!customer) return;

                for (const contact of customer.contacts) {
                    const label = [contact.name, contact.label, contact.phone].filter(Boolean).join(' · ');
                    const day = new Option(label, contact.id, false, String(contact.id) === String(selectedDay));
                    const night = new Option(label, contact.id, false, String(contact.id) === String(selectedNight));
                    dayContactSelect.add(day);
                    nightContactSelect.add(night);
                }
            }

            function refreshServiceSummary() {
                if (!serviceSummary) return;
                const selected = [...document.querySelectorAll('input[name="services[]"]:checked')];
                serviceSummary.textContent = selected.length ? `${selected.length} service${selected.length === 1 ? '' : 's'} selected` : 'No services selected yet';
                serviceSummary.classList.toggle('is-ready', selected.length > 0);
            }

            function refreshOtherService() {
                otherWrap.hidden = !otherToggle?.checked;
                if (!otherToggle?.checked) {
                    document.getElementById('other_service').value = '';
                }
            }

            customerSelect.addEventListener('change', () => refreshContacts('', ''));
            otherToggle?.addEventListener('change', refreshOtherService);
            document.querySelectorAll('input[name="services[]"]').forEach(input => input.addEventListener('change', refreshServiceSummary));

            quickCustomerOpen?.addEventListener('click', () => {
                quickCustomerStatus.textContent = '';
                if (quickCustomerEditLink) {
                    quickCustomerEditLink.hidden = true;
                    quickCustomerEditLink.removeAttribute('href');
                }
                quickCustomerForm.reset();
                quickCustomerDialog.showModal();
                quickCustomerForm.elements.name.focus();
            });

            quickCustomerCloseButtons.forEach(button => {
                button.addEventListener('click', () => quickCustomerDialog.close());
            });

            quickCustomerDialog?.addEventListener('click', (event) => {
                if (event.target === quickCustomerDialog) quickCustomerDialog.close();
            });

            quickCustomerForm?.addEventListener('submit', async (event) => {
                event.preventDefault();
                quickCustomerStatus.textContent = 'Saving customer…';

                const response = await fetch(quickCustomerForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': quickCustomerForm.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                    },
                    body: new FormData(quickCustomerForm),
                    credentials: 'same-origin',
                });

                if (response.ok) {
                    const payload = await response.json();
                    const customer = payload.customer;
                    customers.push(customer);

                    const option = new Option(customer.name, customer.id, true, true);
                    customerSelect.add(option);
                    customerSelect.value = String(customer.id);
                    refreshContacts('', '');

                    if (quickCustomerEditLink && customer.edit_url) {
                        quickCustomerEditLink.href = customer.edit_url;
                        quickCustomerEditLink.hidden = false;
                    }

                    quickCustomerDialog.close();
                    return;
                }

                if (response.status === 422) {
                    const payload = await response.json();
                    const errors = Object.values(payload.errors ?? {}).flat();
                    quickCustomerStatus.textContent = errors[0] ?? 'Check the customer details and try again.';
                    return;
                }

                quickCustomerStatus.textContent = 'Zazu could not add the customer. Please try again.';
            });

            refreshContacts();
            refreshOtherService();
            refreshServiceSummary();
        </script>
</x-app-layout>
