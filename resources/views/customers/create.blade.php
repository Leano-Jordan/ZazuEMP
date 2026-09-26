<x-app-layout>
    <x-slot:title>New customer</x-slot:title>
    <x-slot:heading>New customer</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('customers.index') }}" class="zazu-btn zazu-btn-ghost">← Customers</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Relationship record</div>
            <h2 class="zazu-command-title">Create a customer</h2>
            <p class="zazu-command-copy">Capture the relationship once. The same customer can then sit behind multiple event or job workspaces.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('customers.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Customer profile</div>
                        <div class="zazu-form-section-copy">The person or organisation you are doing business with.</div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <x-profile-avatar name="{{ old('name', '') }}" size="lg" />
                        <label class="zazu-field min-w-[240px] flex-1">
                            <span class="zazu-label">Customer name <span class="zazu-required">*</span></span>
                            <input name="name" value="{{ old('name') }}" required class="zazu-input" placeholder="e.g. Thandi Mokoena">
                            @error('name')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                    </div>

                    <div class="mt-5 grid gap-4">
                        <label class="zazu-field">
                            <span class="zazu-label">Profile photo <span class="font-normal text-[var(--zazu-faint)]">(optional)</span></span>
                            <input type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="zazu-input">
                            <span class="mt-1 block text-[10px] leading-5 text-[var(--zazu-faint)]">JPEG, PNG or WebP, up to 2 MB.</span>
                            @error('profile_photo')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Notes</span>
                            <textarea name="notes" rows="4" class="zazu-textarea" placeholder="Useful customer information">{{ old('notes') }}</textarea>
                        </label>
                    </div>
                </section>

                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Primary contact</div>
                        <div class="zazu-form-section-copy">Keep contact details separate so additional contacts can be added later.</div>
                    </div>

                    <div class="zazu-form-grid">
                        <label class="zazu-field">
                            <span class="zazu-label">Contact name</span>
                            <input name="primary_contact_name" value="{{ old('primary_contact_name') }}" required class="zazu-input">
                            @error('primary_contact_name')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Phone</span>
                            <input name="primary_contact_phone" value="{{ old('primary_contact_phone') }}" class="zazu-input" placeholder="e.g. 071 234 5678">
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Email</span>
                            <input type="email" name="primary_contact_email" value="{{ old('primary_contact_email') }}" class="zazu-input">
                        </label>
                    </div>
                </section>

                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Optional operating contacts</div>
                        <div class="zazu-form-section-copy">Use these when daytime and nighttime coordination need different people.</div>
                    </div>
                    <div class="grid gap-4">
                        <details class="rounded-xl border border-[var(--zazu-border)] bg-[var(--zazu-surface-2)] p-4" open>
                            <summary class="cursor-pointer text-xs font-semibold text-[var(--zazu-ink-2)]">Day contact</summary>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <label class="zazu-field"><span class="zazu-label">Name</span><input name="day_contact_name" value="{{ old('day_contact_name') }}" class="zazu-input">@error('day_contact_name')<span class="zazu-field-error">{{ $message }}</span>@enderror</label>
                                <label class="zazu-field"><span class="zazu-label">Phone</span><input name="day_contact_phone" value="{{ old('day_contact_phone') }}" class="zazu-input"></label>
                                <label class="zazu-field sm:col-span-2"><span class="zazu-label">Email</span><input type="email" name="day_contact_email" value="{{ old('day_contact_email') }}" class="zazu-input"></label>
                            </div>
                        </details>
                        <details class="rounded-xl border border-[var(--zazu-border)] bg-[var(--zazu-surface-2)] p-4">
                            <summary class="cursor-pointer text-xs font-semibold text-[var(--zazu-ink-2)]">Night contact</summary>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <label class="zazu-field"><span class="zazu-label">Name</span><input name="night_contact_name" value="{{ old('night_contact_name') }}" class="zazu-input">@error('night_contact_name')<span class="zazu-field-error">{{ $message }}</span>@enderror</label>
                                <label class="zazu-field"><span class="zazu-label">Phone</span><input name="night_contact_phone" value="{{ old('night_contact_phone') }}" class="zazu-input"></label>
                                <label class="zazu-field sm:col-span-2"><span class="zazu-label">Email</span><input type="email" name="night_contact_email" value="{{ old('night_contact_email') }}" class="zazu-input"></label>
                            </div>
                        </details>
                    </div>
                </section>

                <div class="zazu-actionbar">
                    <a href="{{ route('customers.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                    <button class="zazu-btn zazu-btn-primary">Save customer</button>
                </div>
            </div>

            <aside class="zazu-form-aside">
                <div class="zazu-context-card">
                    <div class="zazu-context-title">Relationship model</div>
                    <div class="zazu-context-copy">Customer data is entered once and reused across the workspaces created for that relationship.</div>

                    <div class="zazu-step-list">
                        <div class="zazu-step">
                            <span class="zazu-step-dot"></span>
                            <div>
                                <div class="zazu-step-title">Customer</div>
                                <div class="zazu-step-copy">Relationship record</div>
                            </div>
                        </div>
                        <div class="zazu-step">
                            <span class="zazu-step-dot"></span>
                            <div>
                                <div class="zazu-step-title">Work</div>
                                <div class="zazu-step-copy">Event or job workspace</div>
                            </div>
                        </div>
                        <div class="zazu-step">
                            <span class="zazu-step-dot"></span>
                            <div>
                                <div class="zazu-step-title">Requirements</div>
                                <div class="zazu-step-copy">What must be delivered</div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</x-app-layout>
