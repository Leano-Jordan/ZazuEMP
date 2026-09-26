<x-app-layout>
    <x-slot:title>Edit contact</x-slot:title>
    <x-slot:heading>Edit contact</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('customers.show', $customer) }}" class="zazu-btn zazu-btn-ghost">← Customer</a></x-slot:headerAction>

    <section class="zazu-command-band"><div><div class="zazu-eyebrow">{{ $customer->name }}</div><h2 class="zazu-command-title">Edit contact</h2><p class="zazu-command-copy">Keep this person's business contact details current.</p></div></section>

    <form method="POST" action="{{ route('customers.contacts.update', [$customer, $contact]) }}">
        @csrf @method('PUT')
        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head"><div class="zazu-form-section-title">Contact details</div><div class="zazu-form-section-copy">The primary flag controls the default contact inherited by new Work records.</div></div>
                    <div class="zazu-form-grid">
                        <label class="zazu-field zazu-field-wide"><span class="zazu-label">Name <span class="zazu-required">*</span></span><input name="name" value="{{ old('name', $contact->name) }}" required class="zazu-input">@error('name')<span class="zazu-field-error">{{ $message }}</span>@enderror</label>
                        <label class="zazu-field"><span class="zazu-label">Label</span><input name="label" value="{{ old('label', $contact->label) }}" class="zazu-input"></label>
                        <label class="zazu-field"><span class="zazu-label">Phone</span><input type="tel" name="phone" value="{{ old('phone', $contact- autocomplete="tel">phone) }}" class="zazu-input"></label>
                        <label class="zazu-field zazu-field-wide"><span class="zazu-label">Email</span><input type="email" name="email" value="{{ old('email', $contact->email) }}" class="zazu-input"></label>
                        <label class="zazu-check-row"><input type="checkbox" name="is_primary" value="1" @checked(old('is_primary', $contact->is_primary))><span><span class="block text-xs font-semibold text-[var(--zazu-ink-2)]">Primary contact</span><span class="mt-1 block text-[10px] text-[var(--zazu-faint)]">Selecting this demotes the current primary contact.</span></span></label>
                    </div>
                </section>
                <div class="zazu-actionbar"><a href="{{ route('customers.show', $customer) }}" class="zazu-btn zazu-btn-ghost">Cancel</a><button class="zazu-btn zazu-btn-primary">Save changes</button></div>
            </div>
            <aside class="zazu-form-aside"><div class="zazu-context-card"><div class="zazu-context-title">Lifecycle</div><div class="zazu-context-copy">Non-primary contacts can be removed from active customer records while historical Work references remain available.</div></div></aside>
        </div>
    </form>
</x-app-layout>
