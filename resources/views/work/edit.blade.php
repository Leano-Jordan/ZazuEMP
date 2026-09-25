<x-app-layout>
    <x-slot:title>Edit {{ $event->name }}</x-slot:title>
    <x-slot:heading>Edit work</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.show', $event) }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">← Back to workspace</a>
    </x-slot:headerAction>

    <form method="POST" action="{{ route('work.update', $event) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="font-semibold text-slate-950">Work details</h2>
                <p class="mt-1 text-sm text-slate-500">Change the information that defines this job.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block sm:col-span-2">
                    <span class="text-sm font-medium text-slate-700">Customer</span>
                    <select id="customer_id" name="customer_id" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $event->customer_id) == $customer->id)>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                </label>

                <label class="block sm:col-span-2">
                    <span class="text-sm font-medium text-slate-700">Work / event name</span>
                    <input name="name" value="{{ old('name', $event->name) }}" required class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Type</span>
                    <input name="event_type" value="{{ old('event_type', $event->event_type) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Date <span class="text-rose-500">*</span></span>
                    <input type="date" name="event_date" value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}" required class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                </label>

                <label class="block sm:col-span-2">
                    <span class="text-sm font-medium text-slate-700">Event-day contact <span class="font-normal text-slate-400">(optional)</span></span>
                    <select id="event_day_contact_id" name="event_day_contact_id" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                        <option value="">No event-day contact</option>
                    </select>
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Status</span>
                    <select name="status" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                        @foreach (['draft' => 'Draft', 'confirmed' => 'Confirmed', 'in_progress' => 'In progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $event->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block sm:col-span-2">
                    <span class="text-sm font-medium text-slate-700">Event location</span>
                    <input name="event_address" value="{{ old('event_address', $event->event_address) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                </label>

                <label class="block sm:col-span-2">
                    <span class="text-sm font-medium text-slate-700">Notes</span>
                    <textarea name="notes" rows="5" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">{{ old('notes', $event->notes) }}</textarea>
                </label>
            </div>
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ route('work.show', $event) }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</a>
            <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Save changes</button>
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
