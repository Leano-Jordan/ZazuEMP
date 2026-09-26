<x-app-layout>
    <x-slot:title>{{ $customer->name }}</x-slot:title>
    <x-slot:heading>{{ $customer->name }}</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('customers.edit', $customer) }}" class="zazu-btn zazu-btn-secondary">Edit customer</a>
        <a href="{{ route('customers.contacts.create', $customer) }}" class="zazu-btn zazu-btn-primary">Add contact</a>
        <a href="{{ route('work.create', ['customer_id' => $customer->id]) }}" class="zazu-btn zazu-btn-ghost">Start work</a>
        <a href="{{ route('customers.index') }}" class="zazu-btn zazu-btn-ghost">← Customers</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div class="flex min-w-0 items-center gap-4">
            <x-profile-avatar :name="$customer->name" :path="$customer->profile_photo_path" size="lg" />
            <div class="min-w-0">
                <div class="zazu-eyebrow">Customer relationship</div>
                <h2 class="zazu-command-title">{{ $customer->name }}</h2>
                <p class="zazu-command-copy">Relationship details, contacts and work history connected to this customer.</p>
            </div>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Work items</div>
            <div class="zazu-command-meta-value">{{ $customer->events->count() }}</div>
        </div>
    </section>

    <div class="zazu-detail-grid">
        <div class="zazu-detail-stack">
            <section class="zazu-panel">
                <div class="zazu-panel-head">
                    <div>
                        <div class="zazu-panel-title">Contacts</div>
                        <div class="zazu-panel-copy">Active people connected to this relationship. Historical Work records can retain a removed contact reference.</div>
                    </div>
                    <a href="{{ route('customers.contacts.create', $customer) }}" class="zazu-btn zazu-btn-secondary">Add contact</a>
                </div>

                <div class="mt-4 grid gap-3">
                    @forelse ($customer->contacts as $contact)
                        <div class="rounded-xl border border-[var(--zazu-border)] bg-[var(--zazu-surface-2)] p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="zazu-detail-value">{{ $contact->name }}</div>
                                    <div class="mt-1 zazu-detail-label">{{ $contact->label ?: 'Contact' }}{{ $contact->is_primary ? ' · Primary' : '' }}</div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('customers.contacts.edit', [$customer, $contact]) }}" class="zazu-btn zazu-btn-ghost">Edit</a>
                                    @unless ($contact->is_primary)
                                        <form method="POST" action="{{ route('customers.contacts.destroy', [$customer, $contact]) }}" onsubmit="return confirm('Remove this contact from the active customer record? Historical Work references will be retained.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="zazu-btn zazu-btn-ghost text-[var(--zazu-danger-ink)]">Remove</button>
                                        </form>
                                    @endunless
                                </div>
                            </div>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                <div><div class="zazu-detail-label">Phone</div><div class="zazu-detail-value mt-1">{{ $contact->phone ?: 'Not provided' }}</div></div>
                                <div><div class="zazu-detail-label">Email</div><div class="zazu-detail-value mt-1 break-all">{{ $contact->email ?: 'Not provided' }}</div></div>
                            </div>
                        </div>
                    @empty
                        <div class="zazu-empty">
                            <div class="zazu-empty-title">No active contacts</div>
                            <p class="zazu-empty-copy">Add a contact only when there is a business purpose for keeping another person connected to the customer.</p>
                            <a href="{{ route('customers.contacts.create', $customer) }}" class="zazu-btn zazu-btn-primary mt-5">Add contact</a>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="zazu-panel">
                <div class="zazu-panel-head">
                    <div>
                        <div class="zazu-panel-title">Work history</div>
                        <div class="zazu-panel-copy">Open a workspace or continue editing an active job.</div>
                    </div>
                    <a href="{{ route('work.create', ['customer_id' => $customer->id]) }}" class="zazu-btn zazu-btn-secondary">Create work</a>
                </div>

                <div class="zazu-list mt-3">
                    @forelse ($customer->events as $event)
                        <div class="zazu-list-item">
                            <a href="{{ route('work.show', $event) }}" class="zazu-list-main min-w-0 flex-1">
                                <div class="zazu-list-title">{{ $event->name }}</div>
                                <div class="zazu-list-meta">{{ $event->reference }} · {{ $event->event_type ?: 'Work' }}</div>
                            </a>
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <div class="zazu-list-side"><div class="zazu-side-primary">{{ $event->event_date?->format('d M Y') ?? 'Date not set' }}</div></div>
                                <a href="{{ route('work.edit', $event) }}" class="zazu-btn zazu-btn-ghost">Edit</a>
                            </div>
                        </div>
                    @empty
                        <div class="zazu-empty"><div class="zazu-empty-title">No work yet</div><p class="zazu-empty-copy">Start the first work item for this customer.</p><a href="{{ route('work.create', ['customer_id' => $customer->id]) }}" class="zazu-btn zazu-btn-primary mt-5">Create work</a></div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="zazu-detail-stack">
            <section class="zazu-panel">
                <div class="zazu-panel-title">Relationship at a glance</div>
                <div class="zazu-detail-rows">
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Customer</div><div class="zazu-detail-value">{{ $customer->name }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Primary</div><div class="zazu-detail-value">{{ $customer->primaryContact?->name ?: 'Not set' }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Contacts</div><div class="zazu-detail-value">{{ $customer->contacts->count() }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Work items</div><div class="zazu-detail-value">{{ $customer->events->count() }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Created</div><div class="zazu-detail-value">{{ $customer->created_at->format('d M Y, H:i') }}</div></div>
                </div>
            </section>
            @if ($customer->notes)
                <section class="zazu-panel"><div class="zazu-panel-title">Notes</div><div class="mt-3 text-xs leading-6 text-[var(--zazu-ink-2)]">{{ $customer->notes }}</div></section>
            @endif
            <section class="zazu-panel">
                <div class="zazu-panel-title">Privacy-aware handling</div>
                <div class="mt-3 text-[11px] leading-5 text-[var(--zazu-muted)]">Names, contact details and identifiable profile photos are personal information. Keep access limited to people who need it for the relationship, retain information only for a lawful business purpose, and use lifecycle controls when a contact is no longer active.</div>
            </section>
        </aside>
    </div>
</x-app-layout>
