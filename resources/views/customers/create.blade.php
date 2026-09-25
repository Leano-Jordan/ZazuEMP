<x-app-layout>
    <x-slot:title>New customer</x-slot:title>
    <x-slot:heading>New customer</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('customers.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">← Customers</a>
    </x-slot:headerAction>

    <form method="POST" action="{{ route('customers.store') }}" class="space-y-6">
        @csrf

        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="font-semibold text-slate-950">Customer</h2>
                <p class="mt-1 text-sm text-slate-500">The person or organisation you are doing business with.</p>
            </div>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">Customer name</span>
                <input name="name" value="{{ old('name') }}" required class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="e.g. Thandi Mokoena">
                @error('name')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
            </label>

            <label class="mt-4 block">
                <span class="text-sm font-medium text-slate-700">Notes</span>
                <textarea name="notes" rows="4" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="Useful customer information">{{ old('notes') }}</textarea>
            </label>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="font-semibold text-slate-950">Primary contact</h2>
                <p class="mt-1 text-sm text-slate-500">We will keep the contact separate so alternative contacts can be added later.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Contact name</span>
                    <input name="primary_contact_name" value="{{ old('primary_contact_name') }}" required class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    @error('primary_contact_name')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Phone</span>
                    <input name="primary_contact_phone" value="{{ old('primary_contact_phone') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="e.g. 071 234 5678">
                </label>

                <label class="block sm:col-span-2">
                    <span class="text-sm font-medium text-slate-700">Email</span>
                    <input type="email" name="primary_contact_email" value="{{ old('primary_contact_email') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                </label>
            </div>
        </section>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('customers.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</a>
            <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Save customer</button>
        </div>
    </form>
</x-app-layout>
