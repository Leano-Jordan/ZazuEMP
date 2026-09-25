<x-app-layout>
    <x-slot:title>Create work</x-slot:title>
    <x-slot:heading>Create work</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">← Work</a>
    </x-slot:headerAction>

    @if ($customers->isEmpty())
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <h2 class="font-semibold text-amber-950">Add a customer first</h2>
            <p class="mt-1 text-sm text-amber-800">A work record starts from a customer. Once you have one, it can become an event/job workspace.</p>
            <a href="{{ route('customers.create') }}" class="mt-4 inline-flex rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Add customer</a>
        </div>
    @else
        <form method="POST" action="{{ route('work.store') }}" class="space-y-6">
            @csrf

            <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                <div class="mb-5">
                    <h2 class="font-semibold text-slate-950">Work details</h2>
                    <p class="mt-1 text-sm text-slate-500">Start the operational record. Requirements, quotes, travel and costs attach later.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block sm:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Customer</span>
                        <select id="customer_id" name="customer_id" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                            <option value="">Select customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id', $selectedCustomerId) == $customer->id)>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Work / event name</span>
                        <input name="name" value="{{ old('name') }}" required class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="e.g. Mokoena Wedding">
                        @error('name')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Type</span>
                        <input name="event_type" value="{{ old('event_type') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="Wedding, funeral, hire, catering...">
                        @error('event_type')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Date <span class="text-rose-500">*</span></span>
                        <input type="date" name="event_date" value="{{ old('event_date') }}" required class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                        @error('event_date')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Event-day contact <span class="font-normal text-slate-400">(optional)</span></span>
                        <select id="event_day_contact_id" name="event_day_contact_id" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                            <option value="">Select a customer first</option>
                        </select>
                        @error('event_day_contact_id')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Event location</span>
                        <input name="event_address" value="{{ old('event_address') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="Address or venue">
                        @error('event_address')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Notes</span>
                        <textarea name="notes" rows="4" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">{{ old('notes') }}</textarea>
                        @error('notes')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>
                </div>
            </section>

            <div class="flex justify-end gap-3">
                <a href="{{ route('work.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</a>
                <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Create work</button>
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

                if (!customer) {
                    return;
                }

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
