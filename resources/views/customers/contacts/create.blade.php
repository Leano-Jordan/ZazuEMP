<x-app-layout>
    <x-slot:title>Add contact</x-slot:title>
    <x-slot:heading>Add contact</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('customers.show', $customer) }}" class="zazu-btn zazu-btn-ghost">← Customer</a></x-slot:headerAction>

    <section class="zazu-command-band"><div><div class="zazu-eyebrow">Customer relationship</div><h2 class="zazu-command-title">Add contact for {{ $customer->name }}</h2><p class="zazu-command-copy">Add another person when this relationship needs a distinct operational contact.</p></div></section>

    <form method="POST" action="{{ route('customers.contacts.store', $customer) }}">
        @csrf
        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head"><div class="zazu-form-section-title">Contact details</div><div class="zazu-form-section-copy">Role labels can describe day, night, billing, venue, site or another business purpose.</div></div>
                    <div class="zazu-form-grid">
                        <label class="zazu-field zazu-field-wide"><span class="zazu-label">Name <span class="zazu-required">*</span></span><input name="name" value="{{ old('name') }}" required class="zazu-input">@error('name')<span class="zazu-field-error">{{ $message }}</span>@enderror</label>
                        <label class="zazu-field"><span class="zazu-label">Label</span><input name="label" value="{{ old('label') }}" class="zazu-input" placeholder="Day, Night, Billing..."></label>
                        <label class="zazu-field"><span class="zazu-label">Phone</span><input type="tel" name="phone" value="{{ old('phone') }}" class="zazu-input" autocomplete="tel"></label>
                        <label class="zazu-field zazu-field-wide"><span class="zazu-label">Email</span><input type="email" name="email" value="{{ old('email') }}" class="zazu-input" autocomplete="email"></label>
                        <label class="zazu-check-row"><input type="checkbox" name="is_primary" value="1" @checked(old('is_primary'))><span><span class="block text-xs font-semibold text-[var(--zazu-ink-2)]">Make this the primary contact</span><span class="mt-1 block text-[10px] text-[var(--zazu-faint)]">The current primary will be demoted in the same transaction.</span></span></label>
                    </div>
                </section>
                <div class="zazu-actionbar"><a href="{{ route('customers.show', $customer) }}" class="zazu-btn zazu-btn-ghost">Cancel</a><button class="zazu-btn zazu-btn-primary">Save contact</button></div>
            </div>
            <aside class="zazu-form-aside"><div class="zazu-context-card"><div class="zazu-context-title">Available to Work</div><div class="zazu-context-copy">The new contact can be selected as a Work day or night contact for this customer.</div></div></aside>
        </div>
    </form>
</x-app-layout>
