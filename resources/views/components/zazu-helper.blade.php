@php
    $routeName = request()->route()?->getName();
    $business = app(\App\Support\CurrentBusiness::class)->resolve(auth()->user());
    $experience = app(\App\Support\ExperienceLevel::class);
    $level = $business ? $experience->for(auth()->user(), $business) : 'intermediate';
    $attention = $business
        ? app(\App\Support\ZazuHelperService::class)->attention(auth()->user(), $business, (string) $routeName)
        : [];
    $attention = array_slice($attention, 0, $level === 'basic' ? 2 : ($level === 'intermediate' ? 3 : 4));
    $guides = [
        'dashboard' => [
            ['label' => 'Start', 'title' => 'Define what your business offers', 'copy' => 'Services and products become reusable building blocks for jobs, requirements and quotes.', 'href' => route('capabilities.index'), 'link' => 'Open Services', 'target' => 'dashboard.overview'],
            ['label' => 'Next', 'title' => 'Create the job', 'copy' => 'The Job is the operational home for the customer, requirements, quotes, preparation and costs.', 'href' => route('work.index'), 'link' => 'Open Jobs'],
            ['label' => 'Then', 'title' => 'Follow the job through execution', 'copy' => 'Move from accepted quote to requirements, purchasing, inventory, preparation and finance without losing the thread.'],
        ],
        'onboarding.experience' => [
            ['label' => 'Choose', 'title' => 'Set the amount of detail you want surfaced', 'copy' => 'Basic keeps the workspace focused. Intermediate adds operational depth. Advanced exposes the broader control picture.'],
            ['label' => 'Remember', 'title' => 'This does not change your permissions', 'copy' => 'Your role still controls access and actions. Experience level only changes presentation and guidance.'],
        ],
        'capabilities.index' => [
            ['label' => 'Services', 'title' => 'Define what you sell or provide', 'copy' => 'Keep your common offerings reusable instead of rebuilding them for every job.'],
            ['label' => 'Use', 'title' => 'Connect offerings to requirements and quotes', 'copy' => 'A capability can become a requirement on a job and a line in a commercial document.'],
            ['label' => 'Maintain', 'title' => 'Keep the catalogue trustworthy', 'copy' => 'Archive or deactivate outdated offerings instead of deleting history.'],
        ],
        'work.index' => [
            ['label' => 'Jobs', 'title' => 'Treat each event as one operational workspace', 'copy' => 'The job should be the place where the customer, date, requirements, quotes and execution state meet.', 'target' => 'work.register'],
            ['label' => 'Plan', 'title' => 'Open a job before creating disconnected records', 'copy' => 'Use the job context to keep downstream work tied to the correct customer and event.'],
            ['label' => 'Control', 'title' => 'Watch exceptions', 'copy' => 'Pay attention to approaching work, blocked preparation, procurement gaps and unresolved actions.'],
        ],
        'work.show' => [
            ['label' => 'Review', 'title' => 'Start with the next useful action', 'copy' => 'Zazu highlights the next step based on this job’s actual records. Review it before moving through the workflow.', 'target' => 'work.next-action'],
            ['label' => 'Build', 'title' => 'Track the job journey', 'copy' => 'This progress strip follows scope, customer decision, payment, delivery readiness and close-out.', 'target' => 'work.progress'],
            ['label' => 'Execute', 'title' => 'Use the downstream workspaces', 'copy' => 'Quote, preparation, purchasing, inventory, assets, costs and travel should all remain connected to this job.'],
        ],
        'quotes.index' => [
            ['label' => 'Quotes', 'title' => 'Build from the work', 'copy' => 'Use the job requirements as the commercial starting point rather than retyping the scope.', 'target' => 'quotes.register'],
            ['label' => 'Control', 'title' => 'Revise before acceptance', 'copy' => 'Keep quote versions traceable so the accepted commercial state is clear.'],
            ['label' => 'Finance', 'title' => 'Accepted work becomes billable context', 'copy' => 'Keep the accepted quote and invoice relationship explicit.'],
        ],
        'finance.index' => [
            ['label' => 'Finance', 'title' => 'Know what is invoiced, paid and outstanding', 'copy' => 'Finance should explain the business position, not just display totals.'],
            ['label' => 'Control', 'title' => 'Payments are constrained by invoice balance', 'copy' => 'Duplicate submissions and overpayments are treated as integrity problems, not user inconveniences.'],
            ['label' => 'Reconcile', 'title' => 'Use the records and audit trail', 'copy' => 'When numbers matter, trace the source transactions instead of trusting a dashboard tile.'],
        ],
        'purchasing.index' => [
            ['label' => 'Purchasing', 'title' => 'Buy against operational demand', 'copy' => 'Purchase orders should answer what is needed, from whom and for which work.'],
            ['label' => 'Receive', 'title' => 'Receipt changes stock', 'copy' => 'Receiving is a controlled transition into inventory, not just a status label.'],
            ['label' => 'Protect', 'title' => 'Avoid duplicate commitments', 'copy' => 'Retry-safe operations and database uniqueness prevent accidental double posting.'],
        ],
        'inventory.index' => [
            ['label' => 'Inventory', 'title' => 'On-hand is the result of recorded movements', 'copy' => 'Receipts, issues, returns and adjustments explain the stock position.'],
            ['label' => 'Use', 'title' => 'Connect stock to work', 'copy' => 'Inventory decisions should make sense in the context of real jobs and requirements.'],
            ['label' => 'Watch', 'title' => 'Spot gaps before the event', 'copy' => 'Low stock and procurement gaps should become visible actions, not surprises on event day.'],
        ],
        'customers.index' => [
            ['label' => 'Customer', 'title' => 'Keep the relationship anchor clean', 'copy' => 'One customer record can support contacts, jobs, quotes, invoices and future rebooking.'],
            ['label' => 'Context', 'title' => 'Use customer history', 'copy' => 'A repeat customer should not require the business to rediscover the same information every time.'],
            ['label' => 'Next', 'title' => 'Create the job from the relationship', 'copy' => 'A customer becomes operationally useful when their next event is clear and actionable.'],
        ],
        'calendar.index' => [
            ['label' => 'Calendar', 'title' => 'Use it for workload visibility', 'copy' => 'The calendar helps you see timing and pressure points, while the job remains the source of operational detail.'],
            ['label' => 'Watch', 'title' => 'Scan what is approaching', 'copy' => 'Use the calendar to spot packed periods, preparation pressure and conflicts.'],
        ],
        'reports.index' => [
            ['label' => 'Reports', 'title' => 'Use reports to answer questions', 'copy' => 'A useful report explains something you need to decide or reconcile.'],
            ['label' => 'Avoid', 'title' => 'Do not repeat the dashboard', 'copy' => 'If a report adds no operational insight, it is probably the wrong report.'],
        ],
        'settings.index' => [
            ['label' => 'Settings', 'title' => 'Keep business rules deliberate', 'copy' => 'Business identity, defaults and compliance details affect the records Zazu creates.'],
            ['label' => 'Protect', 'title' => 'Treat settings as controlled state', 'copy' => 'Important configuration should be auditable and protected from accidental changes.'],
        ],
    ];
    $nicheService = app(\App\Support\NicheFocus::class);
    $nicheFocus = $business
        ? $nicheService->for(auth()->user(), $business)
        : \App\Support\NicheFocus::DEFAULT;
    $businessFocuses = $business
        ? $nicheService->activeFor($business, $nicheFocus)
        : [];
    $businessFocusLabels = collect($businessFocuses)
        ->map(fn ($focus) => $nicheService->label($focus))
        ->values();

    $event = request()->route()?->parameter('event');
    $eventComponents = collect();
    $eventNicheFocuses = [];

    if ($routeName === 'work.show'
        && $event instanceof \App\Models\Event
        && $business
        && (int) $event->business_id === (int) $business->id) {
        $eventRequirements = $event->requirements()
            ->with(['capability' => fn ($query) => $query->where('business_id', $business->id)])
            ->get();

        $eventComponents = $eventRequirements
            ->map(fn ($requirement) => trim((string) ($requirement->description ?: $requirement->capability?->name ?: $requirement->category)))
            ->filter()
            ->unique()
            ->values();

        $eventNicheFocuses = $nicheService->matchingCategories(
            $eventRequirements->pluck('category')->all()
        );
    }

    $focusGuidance = [
        'chairs_tents' => [
            'dashboard' => 'Keep hire quantities, availability, delivery, setup, collection and asset condition connected to each booking.',
            'work.index' => 'Watch hire availability, delivery/setup timing, collection plans and asset condition across upcoming bookings.',
            'work.show' => 'For this booking, review hire quantities, asset availability, delivery, setup, collection and condition.',
        ],
        'sound_dj' => [
            'dashboard' => 'Keep sound equipment, crew, setup/breakdown, programme timing and transport connected to each booking.',
            'work.index' => 'Watch equipment availability, crew, setup timing, programme details and transport across upcoming bookings.',
            'work.show' => 'For this booking, review sound equipment, crew, programme timing, setup/breakdown and transport.',
        ],
        'catering_baking' => [
            'dashboard' => 'Keep menus, quantities, dietary details, purchasing, preparation and delivery connected to each booking.',
            'work.index' => 'Watch menu confirmation, dietary details, purchasing, preparation deadlines and delivery across upcoming bookings.',
            'work.show' => 'For this booking, review menu quantities, dietary details, purchasing, preparation and delivery.',
        ],
    ];

    // Business-wide guidance reflects the active catalogue mix. On Job detail,
    // the saved requirements for this exact booking take precedence.
    $guidanceFocuses = $routeName === 'work.show'
        ? $eventNicheFocuses
        : $businessFocuses;
    $guidanceCopies = collect(array_slice($guidanceFocuses, 0, 3))
        ->map(fn ($focus) => $focusGuidance[$focus][$routeName] ?? null)
        ->filter()
        ->values();

    if ($guidanceCopies->isNotEmpty() && isset($guides[$routeName][2])) {
        $guides[$routeName][2]['copy'] = $guidanceCopies->count() > 1
            ? 'For your active service mix: '.$guidanceCopies->implode(' ')
            : $guidanceCopies->first();
    }

    $eventOperationalRecords = collect();

    if ($routeName === 'work.show'
        && $event instanceof \App\Models\Event
        && $business
        && (int) $event->business_id === (int) $business->id) {
        $permissions = app(\App\Support\PermissionService::class);
        $recordDefinitions = [
            'preparationItems' => ['label' => 'preparation item', 'permission' => 'work.view'],
            'purchaseOrders' => ['label' => 'purchase order', 'permission' => 'purchasing.view'],
            'assetAllocations' => ['label' => 'asset allocation', 'permission' => 'assets.view'],
            'costs' => ['label' => 'recorded cost', 'permission' => 'work.view'],
            'travelCosts' => ['label' => 'travel cost', 'permission' => 'work.view'],
        ];
        $eventCountRelations = [];

        foreach ($recordDefinitions as $relation => $definition) {
            if (!$permissions->allows($definition['permission'], auth()->user(), $business)) {
                continue;
            }

            // Counts are loaded only for relations the current member may view,
            // and constrained again to this business before they reach the UI.
            $eventCountRelations[$relation] = fn ($query) => $query->where('business_id', $business->id);
        }

        if ($eventCountRelations !== []) {
            $event->loadCount($eventCountRelations);
        }

        foreach ($recordDefinitions as $relation => $definition) {
            if (!isset($eventCountRelations[$relation])) {
                continue;
            }

            $count = (int) $event->getAttribute(\Illuminate\Support\Str::snake($relation).'_count');

            if ($count > 0) {
                $label = $definition['label'];
                $eventOperationalRecords->push($count.' '.$label.($count === 1 ? '' : 's'));
            }
        }
    }

    $steps = $guides[$routeName] ?? [];
@endphp

@if(!empty($steps))
    <div class="zazu-helper" data-zazu-helper data-zazu-guide-route="{{ $routeName }}" data-zazu-guide-enabled="on" data-zazu-helper-focus="{{ $nicheFocus }}" data-zazu-helper-focuses="{{ implode(',', $guidanceFocuses) }}" data-zazu-helper-attention="{{ count($attention) }}">
        <section id="zazu-helper-panel" class="zazu-helper-panel" data-zazu-helper-panel role="region" aria-label="Zazu workflow guide" hidden>
            <div class="zazu-helper-head">
                <div class="zazu-helper-bird zazu-helper-bird-panel" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none" role="presentation">
                        <path d="M10 27c0-9 6-16 15-16 5 0 9 2 12 6-1 1-3 2-5 2 2 2 4 4 5 7-3-1-5-1-7-1 0 5-4 10-10 11-6 1-10-2-10-9Z" fill="currentColor"/>
                        <path d="M37 17 44 13l-3 7" fill="currentColor"/>
                        <circle cx="30" cy="20" r="2" fill="white"/>
                        <path d="M14 30c4 0 7 2 9 5-5 2-10 0-12-4" fill="white" opacity=".72"/>
                    </svg>
                </div>
                <div>
                    <div class="zazu-helper-kicker">Zazu guide · {{ ucfirst($level) }}</div>
                    <div class="zazu-helper-status">{{ count($attention) ? count($attention).' item'.(count($attention) === 1 ? '' : 's').' need attention' : 'Guidance for this workspace' }}</div>
                </div>
            </div>
            <div class="zazu-helper-title" data-zazu-helper-title aria-live="polite">{{ $steps[0]['title'] }}</div>
            <p class="zazu-helper-copy" data-zazu-helper-copy aria-live="polite">{{ $steps[0]['copy'] }}</p>
            <div class="zazu-helper-step zazu-helper-next-action" data-zazu-helper-recommendation hidden>
                <div class="zazu-helper-step-label">Next best action</div>
                <strong class="zazu-helper-action-title" data-zazu-helper-recommendation-title></strong>
                <div class="zazu-helper-reason-label">Why this matters</div>
                <p class="zazu-helper-copy" data-zazu-helper-recommendation-copy></p>
                <a href="#" class="zazu-helper-link" data-zazu-helper-recommendation-link></a>
            </div>
            <p class="zazu-helper-copy" data-zazu-helper-connectivity role="status" aria-live="polite" hidden></p>
            @if ($routeName !== 'work.show' && $businessFocusLabels->isNotEmpty())
                <div class="zazu-helper-context" aria-label="Active business services">
                    <div class="zazu-helper-step-label">Your active services</div>
                    <div class="zazu-helper-chip-list">
                        @foreach ($businessFocusLabels as $focusLabel)
                            <span class="zazu-helper-context-chip">{{ $focusLabel }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($eventComponents->isNotEmpty())
                <div class="zazu-helper-step" data-zazu-helper-event-components="{{ $eventComponents->count() }}">
                    <div class="zazu-helper-step-label">This booking includes</div>
                    <div class="zazu-helper-chip-list" data-zazu-helper-event-component-list>
                        @foreach ($eventComponents->take(4) as $component)
                            <span class="zazu-helper-context-chip">{{ $component }}</span>
                        @endforeach
                    </div>
                    @if ($eventComponents->count() > 4)
                        <p class="zazu-helper-copy">{{ $eventComponents->count() - 4 }} more booking components</p>
                    @endif
                </div>
            @endif
            @if ($eventOperationalRecords->isNotEmpty())
                <div class="zazu-helper-step" data-zazu-helper-event-records="{{ $eventOperationalRecords->count() }}">
                    <div class="zazu-helper-step-label">Work linked to this booking</div>
                    <ul class="list-disc pl-5" data-zazu-helper-event-record-list>
                        @foreach ($eventOperationalRecords as $record)
                            <li>{{ $record }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(!empty($attention))
                <div class="zazu-helper-step">
                    <div class="zazu-helper-step-label">Needs attention</div>
                    @foreach($attention as $item)
                        <div class="mt-2" data-zazu-helper-attention-item data-priority="{{ $item['priority'] ?? 100 }}">
                            <strong class="block" data-zazu-helper-attention-title>{{ $item['title'] }}</strong>
                            <span class="block" data-zazu-helper-attention-copy>{{ $item['copy'] }}</span>
                            @if(!empty($item['href']))
                                <a href="{{ $item['href'] }}" class="zazu-helper-link" data-zazu-helper-attention-link>{{ $item['link'] }}</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="zazu-helper-step">
                <div class="zazu-helper-step-label" data-zazu-helper-step-label>{{ $steps[0]['label'] }}</div>
                <div data-zazu-helper-count>1 of {{ count($steps) }}</div>
                <a href="{{ route('dashboard') }}" class="zazu-helper-link" data-zazu-helper-link hidden></a>
            </div>
            <div class="zazu-helper-actions">
                <button type="button" class="zazu-helper-btn" data-zazu-helper-close>Close</button>
                <button type="button" class="zazu-helper-btn" data-zazu-helper-off>Turn guide off</button>
                <div class="zazu-helper-nav">
                    <button type="button" class="zazu-helper-btn" data-zazu-helper-back disabled>Back</button>
                    <button type="button" class="zazu-helper-btn zazu-helper-btn-primary" data-zazu-helper-next>Next</button>
                </div>
            </div>
        </section>
        <button type="button" class="zazu-helper-toggle" data-zazu-helper-toggle aria-expanded="false" aria-controls="zazu-helper-panel" aria-label="Open Zazu guide">
            <span class="zazu-helper-bird" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none" role="presentation">
                    <path d="M10 27c0-9 6-16 15-16 5 0 9 2 12 6-1 1-3 2-5 2 2 2 4 4 5 7-3-1-5-1-7-1 0 5-4 10-10 11-6 1-10-2-10-9Z" fill="currentColor"/>
                    <path d="M37 17 44 13l-3 7" fill="currentColor"/>
                    <circle cx="30" cy="20" r="2" fill="white"/>
                    <path d="M14 30c4 0 7 2 9 5-5 2-10 0-12-4" fill="white" opacity=".72"/>
                </svg>
                <span class="zazu-helper-attention-dot" aria-hidden="true"></span>
            </span>
            <span class="zazu-helper-toggle-copy">
                <strong>Zazu</strong>
                <small data-zazu-helper-toggle-label>Guide</small>
            </span>
        </button>
        <div hidden>
            @foreach($steps as $index => $step)
                <template data-zazu-helper-step-data data-index="{{ $index }}" data-label="{{ $step['label'] }}" data-title="{{ $step['title'] }}" data-copy="{{ $step['copy'] }}" @if(!empty($step['target'])) data-target="{{ $step['target'] }}" @endif @if(!empty($step['href'])) data-href="{{ $step['href'] }}" data-link="{{ $step['link'] }}" @endif></template>
            @endforeach
        </div>
    </div>
@endif