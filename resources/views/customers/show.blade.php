<x-app-layout>
    <x-slot:title>{{ $customer->name }}</x-slot:title>
    <x-slot:heading>{{ $customer->name }}</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.create', ['customer_id' => $customer->id]) }}" class="zazu-btn zazu-btn-primary">Start work</a>
        <a href="{{ route('customers.index') }}" class="zazu-btn zazu-btn-ghost">← Customers</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Customer relationship</div>
            <h2 class="zazu-command-title">{{ $customer->name }}</h2>
            <p class="zazu-command-copy">Relationship details, contacts and the work history connected to this customer.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Work items</div>
            <div class="zazu-command-meta-value">{{ $customer->events->count() }}</div>
        </div>
    </section>

    <div class="zazu-detail-grid">
        <div class="zazu-detail-stack">
            <section class="zazu-panel">
                <div class="zazu-panel-title">Contacts</div>
                <div class="zazu-panel-copy">People connected to this customer relationship.</div>

                <div class="zazu-detail-rows">
                    @forelse ($customer->contacts as $contact)
                        <div class="zazu-detail-row">
                            <div>
                                <div class="zazu-detail-value">{{ $contact->name }}</div>
                                <div class="zazu-detail-label">{{ $contact->label ?: 'Contact' }}{{ $contact->is_primary ? ' · Primary' : '' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="zazu-detail-value">{{ $contact->phone ?: 'No phone' }}</div>
                                <div class="zazu-detail-label">{{ $contact->email ?: 'No email' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="zazu-empty">No contacts recorded.</div>
                    @endforelse
                </div>
            </section>

            <section class="zazu-panel">
                <div class="zazu-panel-head">
                    <div>
                        <div class="zazu-panel-title">Work history</div>
                        <div class="zazu-panel-copy">Open a workspace to continue operational work for this relationship.</div>
                    </div>
                </div>

                <div class="zazu-list">
                    @forelse ($customer->events as $event)
                        <a href="{{ route('work.show', $event) }}" class="zazu-list-item">
                            <div class="zazu-list-main">
                                <div class="zazu-list-title">{{ $event->name }}</div>
                                <div class="zazu-list-meta">{{ $event->reference }} · {{ $event->event_type ?: 'Work' }}</div>
                            </div>
                            <div class="zazu-list-side">
                                <div class="zazu-side-primary">{{ $event->event_date?->format('d M Y') ?? 'Date not set' }}</div>
                                <div class="zazu-side-secondary">Open workspace →</div>
                            </div>
                        </a>
                    @empty
                        <div class="zazu-empty">
                            <div class="zazu-empty-title">No work yet</div>
                            <p class="zazu-empty-copy">Start the first work item for this customer.</p>
                            <a href="{{ route('work.create', ['customer_id' => $customer->id]) }}" class="zazu-btn zazu-btn-primary mt-5">Create work</a>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="zazu-detail-stack">
            <section class="zazu-panel">
                <div class="zazu-panel-title">Relationship at a glance</div>
                <div class="zazu-detail-rows">
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Customer</div><div class="zazu-detail-value">{{ $customer->name }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Created</div><div class="zazu-detail-value">{{ $customer->created_at->format('d M Y, H:i') }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Work items</div><div class="zazu-detail-value">{{ $customer->events->count() }}</div></div>
                </div>
            </section>

            @if ($customer->notes)
                <section class="zazu-panel">
                    <div class="zazu-panel-title">Notes</div>
                    <div class="mt-3 text-xs leading-6 text-[var(--zazu-ink-2)]">{{ $customer->notes }}</div>
                </section>
            @endif
        </aside>
    </div>
</x-app-layout>
