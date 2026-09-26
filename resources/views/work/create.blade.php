<x-app-layout>
    <x-slot:title>New job</x-slot:title>
    <x-slot:heading>New job</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
    </x-slot:headerAction>

    @if ($customers->isEmpty())
        <section class="zazu-command-band">
            <div>
                <div class="zazu-eyebrow">Start here</div>
                <h2 class="zazu-command-title">Add the customer first</h2>
                <p class="zazu-command-copy">Every job needs a customer. Add the customer once, then Zazu will bring their details into the job automatically.</p>
                <a href="{{ route('customers.create') }}" class="zazu-btn zazu-btn-primary mt-5">Add customer</a>
            </div>
        </section>
    @else
        <section class="zazu-command-band">
            <div>
                <div class="zazu-eyebrow">New job · 1 of 1</div>
                <h2 class="zazu-command-title">Tell Zazu what you are doing</h2>
                <p class="zazu-command-copy">Choose the customer, type of job and services you need. Zazu will create the job and take you straight to its workspace.</p>
            </div>
        </section>

        <form method="POST" action="{{ route('work.store') }}" id="new-job-form">
            @csrf
            <div class="zazu-editor">
                <div class="zazu-form-main">
                    <section class="zazu-form-section">
                        <div class="zazu-form-section-head">
                            <div class="zazu-form-section-title">Who is this job for?</div>
                            <div class="zazu-form-section-copy">Select an existing customer. Their saved contact details will be available on the job.</div>
                        </div>
                        <div class="zazu-form-grid">
                            <label class="zazu-field zazu-field-wide">
                                <span class="zazu-label">Customer <span class="zazu-required">*</span></span>
                                <select id="customer_id" name="customer_id" required class="zazu-select">
                                    <option value="">Choose a customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" @selected(old('customer_id', $selectedCustomerId) == $customer->id)>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
                            </label>
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
                        </div>

                        <div class="zazu-service-groups">
                            @foreach ($serviceCategories as $group => $services)
                                <fieldset class="zazu-service-group">
                                    <legend>{{ $group }}</legend>
                                    <div class="zazu-choice-grid">
                                        @foreach ($services as $service)
                                            <label class="zazu-choice-card zazu-service-choice">
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

                    <section class="zazu-form-section">
                        <div class="zazu-form-section-head">
                            <div class="zazu-form-section-title">Who should we contact?</div>
                            <div class="zazu-form-section-copy">Optional. These contacts come from the selected customer.</div>
                        </div>
                        <div class="zazu-form-grid">
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
                    </section>

                    <section class="zazu-form-section">
                        <label class="zazu-field">
                            <span class="zazu-label">Extra notes</span>
                            <textarea name="notes" rows="4" class="zazu-textarea"></textarea>
                            <span class="zazu-field-help">Add anything the team must know now. You can add more detail later.</span>
                        </label>
                    </section>

                    <div class="zazu-actionbar">
                        <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                        <button class="zazu-btn zazu-btn-primary">Create job</button>
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

        <script>
            const customers = @json($customerOptions);
            const oldDayContactId = @json(old('event_day_contact_id'));
            const oldNightContactId = @json(old('event_night_contact_id'));
            const customerSelect = document.getElementById('customer_id');
            const dayContactSelect = document.getElementById('event_day_contact_id');
            const nightContactSelect = document.getElementById('event_night_contact_id');
            const otherToggle = document.getElementById('other-service-toggle');
            const otherWrap = document.getElementById('other-service-wrap');

            function refreshContacts(selectedDay = oldDayContactId, selectedNight = oldNightContactId) {
                const customer = customers.find(item => String(item.id) === customerSelect.value);
                dayContactSelect.innerHTML = '<option value="">No day contact</option>';
                nightContactSelect.innerHTML = '<option value="">No night contact</option>';
                if (!customer) return;

                for (const contact of customer.contacts) {
                    const label = [contact.name, contact.label, contact.phone].filter(Boolean).join(' · ');
                    const day = new Option(label, contact.id, false, String(contact.id) === String(selectedDay));
                    const night = new Option(label, contact.id, false, String(contact.id) === String(selectedNight));
                    dayContactSelect.add(day);
                    nightContactSelect.add(night);
                }
            }

            function refreshOtherService() {
                otherWrap.hidden = !otherToggle?.checked;
                if (!otherToggle?.checked) {
                    document.getElementById('other_service').value = '';
                }
            }

            customerSelect.addEventListener('change', () => refreshContacts('', ''));
            otherToggle?.addEventListener('change', refreshOtherService);
            refreshContacts();
            refreshOtherService();
        </script>
    @endif
</x-app-layout>
