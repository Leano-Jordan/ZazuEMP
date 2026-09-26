<x-app-layout>
    <x-slot:title>{{ $event->name }}</x-slot:title>
    <x-slot:heading>{{ $event->name }}</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.edit', $event) }}" class="zazu-btn zazu-btn-primary">Edit work</a>
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
                        <div class="zazu-panel-copy">Reusable capabilities will be selected here before commercial quoting.</div>
                    </div>
                    <a href="{{ route('capabilities.index') }}" class="zazu-btn zazu-btn-secondary">Capability catalogue</a>
                </div>

                <div class="zazu-placeholder">
                    <div class="zazu-placeholder-title">Requirement selection is the next workflow layer</div>
                    <div class="zazu-placeholder-copy">The capability catalogue is ready. The next connection is to select those reusable definitions against this specific work item.</div>
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
                    @foreach ([
                        ['title' => 'Work', 'copy' => 'Core event or job record', 'current' => true],
                        ['title' => 'Requirements', 'copy' => 'Capabilities and quantities', 'current' => false],
                        ['title' => 'Quote', 'copy' => 'Commercial offer and versions', 'current' => false],
                        ['title' => 'Preparation', 'copy' => 'Buying and readiness', 'current' => false],
                        ['title' => 'Execution', 'copy' => 'Delivery and accountability', 'current' => false],
                    ] as $stage)
                        <div class="zazu-stage {{ $stage['current'] ? 'current' : '' }}">
                            <span class="zazu-stage-marker"></span>
                            <div>
                                <div class="zazu-stage-title">{{ $stage['title'] }}</div>
                                <div class="zazu-stage-copy">{{ $stage['copy'] }}</div>
                            </div>
                        </div>
                    @endforeach
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
