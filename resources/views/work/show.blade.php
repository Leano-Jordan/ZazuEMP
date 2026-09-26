<x-app-layout>
    <x-slot:title>{{ $event->name }}</x-slot:title>
    <x-slot:heading>{{ $event->name }}</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.requirements.create', $event) }}" class="zazu-btn zazu-btn-primary">Add requirement</a>
        <a href="{{ route('work.edit', $event) }}" class="zazu-btn zazu-btn-secondary">Edit work</a>
        <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-ghost">← All work</a>
    </x-slot:headerAction>

    @php
        $statusClass = match ($event->status) {
            'confirmed' => 'zazu-chip-success',
            'in_progress' => 'zazu-chip-info',
            'completed' => 'zazu-chip-accent',
            'cancelled' => 'zazu-chip-danger',
            default => 'zazu-chip-neutral',
        };
    @endphp

    <section class="zazu-work-hero">
        <div>
            <div class="zazu-work-ref">{{ $event->reference }}</div>
            <div class="zazu-work-name">{{ $event->name }}</div>
            <div class="zazu-work-summary">
                {{ $event->customer?->name ?? $event->customer_name ?? 'No customer' }}
                · {{ $event->event_type ?: 'Work' }}
                @if ($event->event_date) · {{ $event->event_date->format('l, d F Y') }} @endif
            </div>
        </div>
        <div class="zazu-work-actions">
            <span class="zazu-chip {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($event->status)) }}</span>
        </div>
    </section>

    <div class="zazu-detail-grid">
        <div class="zazu-detail-stack">
            <section class="zazu-panel">
                <div class="zazu-panel-head">
                    <div>
                        <div class="zazu-panel-title">Customer relationship</div>
                        <div class="zazu-panel-copy">The customer and event-day contact behind this work.</div>
                    </div>
                    @if ($event->customer)
                        <a href="{{ route('customers.show', $event->customer) }}" class="zazu-btn zazu-btn-ghost">Open customer</a>
                    @endif
                </div>

                <div class="zazu-detail-rows">
                    <div class="zazu-detail-row">
                        <div class="zazu-detail-label">Customer</div>
                        <div class="zazu-detail-value">{{ $event->customer?->name ?? $event->customer_name }}</div>
                    </div>
                    <div class="zazu-detail-row">
                        <div class="zazu-detail-label">Event-day</div>
                        <div class="zazu-detail-value">
                            {{ $event->eventDayContact?->name ?? 'Not selected' }}
                            @if ($event->eventDayContact?->phone)
                                <div class="mt-1 text-[11px] font-normal text-[var(--zazu-muted)]">{{ $event->eventDayContact->phone }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            <section class="zazu-panel">
                <div class="zazu-panel-head">
                    <div>
                        <div class="zazu-panel-title">Requirements & services</div>
                        <div class="zazu-panel-copy">Work-specific quantities and deliverables before commercial quoting.</div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('work.requirements.index', $event) }}" class="zazu-btn zazu-btn-secondary">Requirements</a>
                        <a href="{{ route('capabilities.index') }}" class="zazu-btn zazu-btn-ghost">Capability catalogue</a>
                    </div>
                </div>

                <div class="zazu-placeholder">
                    <div class="zazu-placeholder-title">Build the requirement register</div>
                    <div class="zazu-placeholder-copy">Select reusable capabilities where useful, then record the quantity and specification this particular work item needs.</div>
                    <a href="{{ route('work.requirements.create', $event) }}" class="zazu-btn zazu-btn-primary mt-4">Add requirement</a>
                </div>
            </section>

            <section class="zazu-panel">
                <div class="zazu-panel-head">
                    <div>
                        <div class="zazu-panel-title">Location & travel</div>
                        <div class="zazu-panel-copy">Keep the operating location visible while route and travel costing are added later.</div>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-[var(--zazu-border)] bg-[var(--zazu-surface-2)] px-4 py-3">
                    <div class="text-xs font-semibold text-[var(--zazu-ink-2)]">{{ $event->event_address ?: 'No event location yet' }}</div>
                    <div class="mt-1 text-[11px] text-[var(--zazu-faint)]">Distance, travel time and fuel costing can attach here later.</div>
                </div>
            </section>
        </div>

        <aside class="zazu-detail-stack">
            <section class="zazu-panel">
                <div class="zazu-panel-title">Work at a glance</div>
                <div class="zazu-detail-rows">
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Date</div><div class="zazu-detail-value">{{ $event->event_date?->format('d M Y') ?? 'Not set' }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Location</div><div class="zazu-detail-value">{{ $event->event_address ?: 'Not set' }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Created</div><div class="zazu-detail-value">{{ $event->created_at->format('d M Y, H:i') }}</div></div>
                </div>
            </section>

            <section class="zazu-panel">
                <div class="zazu-panel-title">Operational progression</div>
                <div class="zazu-panel-copy">The workspace grows from this record into the operational layers below.</div>

                <div class="zazu-stage-list">
                    <a href="{{ route('work.show', $event) }}" class="zazu-stage current">
                        <span class="zazu-stage-marker"></span>
                        <div><div class="zazu-stage-title">Work</div><div class="zazu-stage-copy">Core event or job record</div></div>
                    </a>
                    <a href="{{ route('work.requirements.index', $event) }}" class="zazu-stage">
                        <span class="zazu-stage-marker"></span>
                        <div><div class="zazu-stage-title">Requirements</div><div class="zazu-stage-copy">Capabilities and quantities</div></div>
                    </a>
                    <div class="zazu-stage">
                        <span class="zazu-stage-marker"></span>
                        <div><div class="zazu-stage-title">Quote</div><div class="zazu-stage-copy">Commercial offer and versions</div></div>
                    </div>
                    <div class="zazu-stage">
                        <span class="zazu-stage-marker"></span>
                        <div><div class="zazu-stage-title">Preparation</div><div class="zazu-stage-copy">Buying and readiness</div></div>
                    </div>
                    <div class="zazu-stage">
                        <span class="zazu-stage-marker"></span>
                        <div><div class="zazu-stage-title">Execution</div><div class="zazu-stage-copy">Delivery and accountability</div></div>
                    </div>
                </div>
            </section>

            @if ($event->notes)
                <section class="zazu-panel">
                    <div class="zazu-panel-title">Notes</div>
                    <div class="mt-3 text-xs leading-6 text-[var(--zazu-ink-2)]">{{ $event->notes }}</div>
                </section>
            @endif
        </aside>
    </div>
</x-app-layout>
