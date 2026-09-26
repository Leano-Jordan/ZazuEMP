<x-app-layout>
    <x-slot:title>Edit {{ $customer->name }}</x-slot:title>
    <x-slot:heading>Edit customer</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('customers.show', $customer) }}" class="zazu-btn zazu-btn-ghost">← Customer</a></x-slot:headerAction>

    <section class="zazu-command-band">
        <div><div class="zazu-eyebrow">Relationship record</div><h2 class="zazu-command-title">{{ $customer->name }}</h2><p class="zazu-command-copy">Update the customer identity, primary contact and profile image without rewriting linked operational history.</p></div>
    </section>

    <form method="POST" action="{{ route('customers.update', $customer) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head"><div class="zazu-form-section-title">Customer profile</div><div class="zazu-form-section-copy">Profile photos are optional and should have a clear business purpose.</div></div>
                    <div class="flex flex-wrap items-center gap-4">
                        <x-profile-avatar :name="$customer->name" :path="$customer->profile_photo_path" size="lg" />
                        <label class="zazu-field min-w-[240px] flex-1"><span class="zazu-label">Customer name <span class="zazu-required">*</span></span><input name="name" value="{{ old('name', $customer->name) }}" required class="zazu-input">@error('name')<span class="zazu-field-error">{{ $message }}</span>@enderror</label>
                    </div>
                    <div class="mt-5 grid gap-4">
                        <label class="zazu-field"><span class="zazu-label">Replace profile photo</span><input type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="zazu-input">@error('profile_photo')<span class="zazu-field-error">{{ $message }}</span>@enderror</label>
                        @if ($customer->profile_photo_path)
                            <label class="zazu-check-row"><input type="checkbox" name="remove_profile_photo" value="1" @checked(old('remove_profile_photo'))><span><span class="block text-xs font-semibold text-[var(--zazu-ink-2)]">Remove current photo</span><span class="mt-1 block text-[10px] text-[var(--zazu-faint)]">The active record will return to initials.</span></span></label>
                        @endif
                        <label class="zazu-field"><span class="zazu-label">Notes</span><textarea name="notes" rows="5" class="zazu-textarea">{{ old('notes', $customer->notes) }}</textarea></label>
                    </div>
                </section>

                <section class="zazu-form-section">
                    <div class="zazu-form-section-head"><div class="zazu-form-section-title">Primary contact</div><div class="zazu-form-section-copy">Update the default customer contact used when new Work records inherit relationship details.</div></div>
                    <div class="zazu-form-grid">
                        <label class="zazu-field"><span class="zazu-label">Contact name <span class="zazu-required">*</span></span><input name="primary_contact_name" value="{{ old('primary_contact_name', $customer->primaryContact?->name) }}" required class="zazu-input">@error('primary_contact_name')<span class="zazu-field-error">{{ $message }}</span>@enderror</label>
                        <label class="zazu-field"><span class="zazu-label">Phone</span><input type="tel" name="primary_contact_phone" value="{{ old('primary_contact_phone', $customer->primaryContact?->phone) }}" class="zazu-input" autocomplete="tel"></label>
                        <label class="zazu-field zazu-field-wide"><span class="zazu-label">Email</span><input type="email" name="primary_contact_email" value="{{ old('primary_contact_email', $customer->primaryContact?->email) }}" class="zazu-input">@error('primary_contact_email')<span class="zazu-field-error">{{ $message }}</span>@enderror</label>
                    </div>
                </section>

                <div class="zazu-actionbar"><a href="{{ route('customers.show', $customer) }}" class="zazu-btn zazu-btn-ghost">Cancel</a><button class="zazu-btn zazu-btn-primary">Save changes</button></div>
            </div>
            <aside class="zazu-form-aside"><div class="zazu-context-card"><div class="zazu-context-title">History stays intact</div><div class="zazu-context-copy">Changing the customer profile does not rewrite historical quote snapshots or the contacts recorded against past Work.</div></div></aside>
        </div>
    </form>
</x-app-layout>
