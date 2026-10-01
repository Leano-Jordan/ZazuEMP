<x-app-layout>
    <x-slot:title>{{ $event->name }}</x-slot:title>
    <x-slot:heading>{{ $event->name }}</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.edit', $event) }}" class="zazu-btn zazu-btn-secondary">Edit job</a>
        <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-ghost">All jobs</a>
    </x-slot:headerAction>

    @php
        $statusClass = match ($event->status) {
            'confirmed' => 'zazu-chip-success',
            'in_progress' => 'zazu-chip-info',
            'completed' => 'zazu-chip-accent',
            'cancelled' => 'zazu-chip-danger',
            default => 'zazu-chip-neutral',
        };
        $latestQuote = $event->quotes->sortByDesc('created_at')->first();
        $hasRequirements = $event->requirements->isNotEmpty();
        $latestQuoteNeedsRevision = $latestQuote?->latestVersion
            ? !$latestQuote->latestVersion->matchesRequirements($event->requirements)
            : false;
        $nextAction = $latestQuoteNeedsRevision
            ? ['label' => 'Review quote', 'route' => route('quotes.show', $latestQuote), 'copy' => 'The job services changed after the latest quote. Review the current lines before using the quote.']
            : ($latestQuote
                ? ['label' => 'Open quote', 'route' => route('quotes.show', $latestQuote), 'copy' => 'Review the current quote for this job.']
                : ['label' => 'Add services', 'route' => route('work.requirements.create', $event), 'copy' => 'Choose what you are providing for this job.']);
    @endphp

    <section class="zazu-work-hero zazu-work-hero-refined">
        <div>
            <div class="zazu-work-ref">{{ $event->reference }}</div>
            <div class="zazu-work-name">{{ $event->name }}</div>
            <div class="zazu-work-summary">
                {{ $event->customer?->name ?? $event->customer_name ?? 'No customer' }}
                · {{ $event->event_type ?: 'Job' }}
                @if ($event->event_date) · {{ $event->event_date->format('l, d F Y') }} @endif
            </div>
        </div>
        <div class="zazu-work-actions zazu-work-actions-refined">
            <div class="zazu-work-meta-stack">
                <span class="zazu-chip {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($event->status)) }}</span>
                <span class="zazu-work-meta-line">{{ $event->event_type ?: 'Job' }} @if ($event->event_date) · {{ $event->event_date->format('d M Y') }} @endif</span>
            </div>
        </div>
    </section>

    <section class="zazu-next-action zazu-next-action-primary">
        <div>
            <div class="zazu-eyebrow">Next action</div>
            <h2 class="zazu-next-action-title">{{ $nextAction['label'] }}</h2>
            <p class="zazu-next-action-copy">{{ $nextAction['copy'] }}</p>
        </div>
        <a href="{{ $nextAction['route'] }}" class="zazu-btn zazu-btn-primary">{{ $nextAction['label'] }} →</a>
    </section>

    @if ($latestQuoteNeedsRevision)
        <section class="zazu-next-action zazu-next-action-warning">
            <div>
                <div class="zazu-eyebrow">Quote needs review</div>
                <h2 class="zazu-next-action-title">The job changed after the latest quote</h2>
                <p class="zazu-next-action-copy">Create a revision from the current services before treating the quote as current.</p>
            </div>
            <form method="POST" action="{{ route('quotes.versions.store', $latestQuote) }}">
                @csrf
                <button class="zazu-btn zazu-btn-primary">Revise quote →</button>
            </form>
        </section>
    @endif

    <section class="zazu-work-progress zazu-work-progress-refined" aria-label="Job progress">
        <a href="{{ route('work.show', $event) }}" class="zazu-work-progress-step current">
            <span>1</span><strong>Job</strong><small>Details</small>
        </a>
        <a href="{{ route('work.requirements.index', $event) }}" class="zazu-work-progress-step {{ $hasRequirements ? 'complete' : '' }}">
            <span>2</span><strong>Services</strong><small>{{ $event->requirements->count() }} added</small>
        </a>
        <a href="{{ $latestQuote ? route('quotes.show', $latestQuote) : route('work.quotes.create', $event) }}" class="zazu-work-progress-step {{ $latestQuote ? 'complete' : '' }}">
            <span>3</span><strong>Quote</strong><small>{{ $latestQuote ? 'Created' : 'Not created' }}</small>
        </a>
        <a href="{{ route('work.preparation.index', $event) }}" class="zazu-work-progress-step">
            <span>4</span><strong>Prepare</strong><small>Get ready</small>
        </a>
        <a href="{{ route('work.edit', $event) }}" class="zazu-work-progress-step {{ $event->status === 'completed' ? 'complete current' : '' }}">
            <span>5</span><strong>Complete</strong><small>{{ $event->status === 'completed' ? 'Completed' : 'Update status' }}</small>
        </a>
    </section>

    <section class="zazu-panel mb-5" aria-label="Operational chain">
        <div class="zazu-panel-head">
            <div>
                <div class="zazu-panel-title">Operational chain</div>
                <div class="zazu-panel-copy">Everything connected to this job stays together as the work moves from services to preparation, purchasing, costs and finance.</div>
            </div>
        </div>
        <div class="grid gap-2 md:grid-cols-5">
            <a href="{{ route('work.requirements.index', $event) }}" class="zazu-quick-link">
                <span><strong>Services</strong><small class="block opacity-70">{{ $event->requirements->count() }} linked</small></span><span>→</span>
            </a>
            <a href="{{ route('work.preparation.index', $event) }}" class="zazu-quick-link">
                <span><strong>Preparation</strong><small class="block opacity-70">{{ $event->preparationItems->whereIn('status', ['open', 'blocked'])->count() }} outstanding</small></span><span>→</span>
            </a>
            <a href="{{ route('purchasing.index', ['event_id' => $event->id]) }}" class="zazu-quick-link">
                <span><strong>Purchasing</strong><small class="block opacity-70">{{ $event->purchaseOrders->count() }} linked</small></span><span>→</span>
            </a>
            <a href="{{ route('work.costs.index', $event) }}" class="zazu-quick-link">
                <span><strong>Costs</strong><small class="block opacity-70">{{ $event->costs->count() }} recorded</small></span><span>→</span>
            </a>
            <a href="{{ $event->invoices->count() ? route('finance.invoices.show', $event->invoices->sortByDesc('created_at')->first()) : route('finance.index') }}" class="zazu-quick-link">
                <span><strong>Finance</strong><small class="block opacity-70">{{ $event->invoices->count() }} invoice{{ $event->invoices->count() === 1 ? '' : 's' }}</small></span><span>→</span>
            </a>
        </div>
    </section>

    <section class="mb-5">
        @include('work.attachments')
    </section>

    <div class="zazu-detail-grid">
        <div class="zazu-detail-stack">
            <section class="zazu-panel">
                <div class="zazu-panel-head">
                    <div>
                        <div class="zazu-panel-title">Services for this job</div>
                        <div class="zazu-panel-copy">These are the services or items you are providing. Add another one without leaving the job.</div>
                    </div>
                    <a href="{{ route('work.requirements.create', $event) }}" class="zazu-btn zazu-btn-primary">Add service</a>
                </div>

                @forelse ($event->requirements as $requirement)
                    <div class="zazu-list-item">
                        <div class="zazu-list-main">
                            <div class="zazu-list-title">{{ $requirement->description }}</div>
                            <div class="zazu-list-meta">{{ $requirement->category }} · {{ number_format((float) $requirement->quantity, 2) }} {{ $requirement->unit ?: 'units' }}</div>
                            @if ($requirement->notes)
                                <div class="zazu-list-meta">{{ $requirement->notes }}</div>
                            @endif
                        </div>
                        @if ($requirement->capability)
                            <div class="zazu-chip zazu-chip-info">{{ $requirement->capability->name }}</div>
                        @endif
                    </div>
                @empty
                    <div class="zazu-empty">
                        <div class="zazu-empty-title">No services added yet</div>
                        <p class="zazu-empty-copy">Choose Catering, Decor, Sound, Furniture & Equipment, Photography & Video, Baking, Transport or another service.</p>
                        <a href="{{ route('work.requirements.create', $event) }}" class="zazu-btn zazu-btn-primary mt-5">Choose services</a>
                    </div>
                @endforelse

                @if ($hasRequirements)
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('work.requirements.index', $event) }}" class="zazu-btn zazu-btn-ghost">View all services</a>
                        @if (!$latestQuote)
                            <a href="{{ route('work.quotes.create', $event) }}" class="zazu-btn zazu-btn-secondary">Create quote →</a>
                        @endif
                    </div>
                @endif
            </section>

            <section class="zazu-panel">
                <div class="zazu-panel-head">
                    <div>
                        <div class="zazu-panel-title">Customer</div>
                        <div class="zazu-panel-copy">The people and contact details attached to this job.</div>
                    </div>
                    @if ($event->customer)
                        <a href="{{ route('customers.show', $event->customer) }}" class="zazu-btn zazu-btn-ghost">View customer</a>
                    @endif
                </div>
                <div class="zazu-detail-rows">
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Customer</div><div class="zazu-detail-value">{{ $event->customer?->name ?? $event->customer_name }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Day contact</div><div class="zazu-detail-value">{{ $event->eventDayContact?->name ?? 'Not selected' }} @if($event->eventDayContact?->phone)<small>{{ $event->eventDayContact->phone }}</small>@endif</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Night contact</div><div class="zazu-detail-value">{{ $event->eventNightContact?->name ?? 'Not selected' }} @if($event->eventNightContact?->phone)<small>{{ $event->eventNightContact->phone }}</small>@endif</div></div>
                </div>
            </section>

            <section class="zazu-panel">
                <div class="zazu-panel-head">
                    <div>
                        <div class="zazu-panel-title">Location</div>
                        <div class="zazu-panel-copy">Where the job will happen or where the delivery should go.</div>
                    </div>
                    <a href="{{ route('work.travel.index', $event) }}" class="zazu-btn zazu-btn-secondary">Travel & distance</a>
                </div>
                <div class="zazu-detail-value-box">
                    {{ $event->event_address ?: 'No location added yet.' }}
                </div>
            </section>
        </div>

        <aside class="zazu-detail-stack">
            <section class="zazu-panel">
                <div class="zazu-panel-title">Job details</div>
                <div class="zazu-detail-rows">
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Date</div><div class="zazu-detail-value">{{ $event->event_date?->format('d M Y') ?? 'Not set' }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Type</div><div class="zazu-detail-value">{{ $event->event_type ?: 'Not set' }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Status</div><div class="zazu-detail-value">{{ str_replace('_', ' ', ucfirst($event->status)) }}</div></div>
                </div>
            </section>

            <section class="zazu-panel">
                <div class="zazu-panel-title">Job tools</div>
                <div class="zazu-panel-copy">Open the work connected to this job.</div>
                <div class="mt-4 grid gap-2">
                    <a href="{{ route('work.quotes.index', $event) }}" class="zazu-quick-link">Quotes <span>→</span></a>
                    <a href="{{ route('work.costs.index', $event) }}" class="zazu-quick-link">Costs <span>→</span></a>
                    <a href="{{ route('work.preparation.index', $event) }}" class="zazu-quick-link">Preparation <span>→</span></a>
                    <a href="{{ route('work.travel.index', $event) }}" class="zazu-quick-link">Travel & distance <span>→</span></a>
                </div>
            </section>

            @if ($event->notes)
                <section class="zazu-panel">
                    <div class="zazu-panel-title">Notes</div>
                    <div class="mt-3 text-sm leading-6 text-[var(--zazu-ink-2)]">{{ $event->notes }}</div>
                </section>
            @endif
        </aside>
    </div>
</x-app-layout>