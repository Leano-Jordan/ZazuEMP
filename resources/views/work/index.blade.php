<x-app-layout>
    <x-slot:title>Work</x-slot:title>
    <x-slot:heading>Event Operations</x-slot:heading>
<section class="zazu-ops-hero">
        <div>
            <div class="zazu-eyebrow">Operations</div>
            <h2 class="zazu-command-title">Your event workload</h2>
            <p class="zazu-command-copy">See what needs attention, open a job, and inspect its details without leaving the workspace.</p>
        </div>
        <div class="zazu-ops-hero-meta">
            <span class="zazu-live-mark"><i></i> Workspace live</span>
            <span class="zazu-ops-total">{{ $events->total() }} records</span>
        </div>
    </section>

    <nav class="zazu-workload-pills" aria-label="Workload filters">
        @foreach([
            ['label' => 'All Work', 'value' => $events->total(), 'filter' => ''],
            ['label' => 'Today', 'value' => $workload['today'], 'filter' => 'today'],
            ['label' => 'Next 7 Days', 'value' => $workload['next_7_days'], 'filter' => 'next_7_days'],
            ['label' => 'In Progress', 'value' => $workload['in_progress'], 'filter' => 'in_progress'],
            ['label' => 'Drafts', 'value' => $workload['draft'], 'filter' => 'draft'],
            ['label' => 'Overdue', 'value' => $workload['overdue'], 'filter' => 'overdue'],
        ] as $pill)
            <a href="{{ route('work.index', $pill['filter'] ? ['filter' => $pill['filter']] : []) }}" class="zazu-workload-pill {{ $filter === $pill['filter'] ? 'is-active' : '' }}">
                <span>{{ $pill['label'] }}</span><strong>{{ $pill['value'] }}</strong>
            </a>
        @endforeach
    </nav>

    <section class="zazu-ops-register zazu-card" aria-labelledby="work-register-title">
        <div class="zazu-card-header zazu-ops-register-head">
            <div>
                <div class="zazu-eyebrow">Operational register</div>
                <div id="work-register-title" class="zazu-card-title mt-1">Jobs</div>
                <div class="zazu-card-description">Select a record to inspect its catering, equipment and financial context.</div>
            </div>
            <div class="zazu-register-key" aria-hidden="true"><span><i class="is-record"></i> Record</span><span><i class="is-status"></i> Status</span><span><i class="is-action"></i> Action</span></div>
        </div>

        @if ($events->count())
            <div class="zazu-ops-table-head" aria-hidden="true">
                <span>Job reference</span><span>Client</span><span>Function</span><span>Execution</span><span>Status</span><span>Actions</span>
            </div>
        @endif

        @forelse ($events as $event)
            @php
                $statusClass = match ($event->status) {
                    'confirmed' => 'zazu-chip-success',
                    'in_progress' => 'zazu-chip-info',
                    'completed' => 'zazu-chip-accent',
                    'cancelled' => 'zazu-chip-danger',
                    default => 'zazu-chip-neutral',
                };
                $statusLabel = str_replace('_', ' ', ucfirst($event->status));
                $contact = $event->eventDayContact?->name ?? $event->eventNightContact?->name ?? null;
            @endphp
            <article class="zazu-ops-row" data-zazu-job-row>
                <button type="button" class="zazu-ops-row-main" data-zazu-inspector-open aria-label="Inspect {{ $event->name }}">
                    <span class="zazu-ops-ref">
                        <span class="zazu-ops-ref-label">Reference</span>
                        <strong>{{ $event->reference }}</strong>
                    </span>
                    <span class="zazu-ops-client">
                        <strong>{{ $event->customer?->name ?? $event->customer_name ?? 'No customer' }}</strong>
                        <small>{{ $contact ?? $event->customer_phone ?? $event->customer_email ?? 'Contact not recorded' }}</small>
                    </span>
                    <span class="zazu-ops-function">
                        <strong>{{ $event->event_type ?: 'Event' }}</strong>
                        <small>Guest headcount not recorded</small>
                    </span>
                    <span class="zazu-ops-date">
                        <strong>{{ $event->event_date?->format('d M Y') ?? 'Date not set' }}</strong>
                        <small>{{ $event->name }}</small>
                    </span>
                    <span><span class="zazu-chip {{ $statusClass }}">{{ $statusLabel }}</span></span>
                </button>
                <div class="zazu-ops-actions">
                    <a href="{{ route('work.edit', $event) }}" class="zazu-btn zazu-btn-ghost">Edit</a>
                    <button type="button" class="zazu-btn zazu-btn-ghost" data-zazu-inspector-open>View function sheet</button>
                    <form method="POST" action="{{ route('work.destroy', $event) }}" data-zazu-confirm="Remove this work from active operations? Historical records will be retained." data-zazu-confirm-title="Remove work?">
                        @csrf @method('DELETE')
                        <button type="submit" class="zazu-btn zazu-btn-ghost text-[var(--zazu-danger-ink)]">Remove</button>
                    </form>
                </div>

                <template data-zazu-inspector>
                    <div data-event-name="{{ e($event->name) }}" data-event-reference="{{ e($event->reference) }}" data-event-type="{{ e($event->event_type ?: 'Event') }}" data-event-date="{{ $event->event_date?->format('d M Y') ?? 'Date not set' }}" data-event-status="{{ e($statusLabel) }}" data-show-route="{{ route('work.show', $event) }}" data-customer="{{ e($event->customer?->name ?? $event->customer_name ?? 'No customer') }}" data-contact="{{ e($contact ?? $event->customer_phone ?? $event->customer_email ?? 'Contact not recorded') }}">
                        <div class="zazu-inspector-empty">Live operational details can be reviewed from the function sheet. This preview does not invent catering, equipment or financial values that are not recorded on the job.</div>
                    </div>
                </template>
            </article>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No work matches this view</div>
                <p class="zazu-empty-copy">Change the workload view or create a new job.</p>
                <div class="mt-5 flex justify-center gap-2">
                    @if ($filter)<a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-secondary">Show all work</a>@endif
                    <a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary">Create work</a>
                </div>
            </div>
        @endforelse

        @if ($events->hasPages())<div class="px-5 py-4 pt-4">{{ $events->links() }}</div>@endif
    </section>

    <div class="zazu-inspector-backdrop" data-zazu-inspector-close></div>
    <aside class="zazu-inspector" aria-hidden="true" aria-labelledby="zazu-inspector-title" data-zazu-inspector-panel>
        <header class="zazu-inspector-head">
            <div>
                <div class="zazu-eyebrow">Function sheet</div>
                <h2 id="zazu-inspector-title">Job inspection</h2>
                <div class="zazu-inspector-reference" data-zazu-inspector-reference>—</div>
            </div>
            <button type="button" class="zazu-inspector-close" data-zazu-inspector-close aria-label="Close job inspection">×</button>
        </header>

        <div class="zazu-inspector-summary">
            <div><span>Client</span><strong data-zazu-inspector-client>—</strong></div>
            <div><span>Function</span><strong data-zazu-inspector-type>—</strong></div>
            <div><span>Execution</span><strong data-zazu-inspector-date>—</strong></div>
            <div><span>Status</span><strong><span class="zazu-chip zazu-chip-neutral" data-zazu-inspector-status>—</span></strong></div>
        </div>

        <div class="zazu-inspector-tabs" role="tablist" aria-label="Function sheet sections">
            <button type="button" class="is-active" data-zazu-tab="catering" role="tab" aria-selected="true" aria-controls="zazu-inspector-panel-catering">Catering &amp; Menu Prep</button>
            <button type="button" data-zazu-tab="equipment" role="tab" aria-selected="false" aria-controls="zazu-inspector-panel-equipment">Equipment Hire Manifest</button>
            <button type="button" data-zazu-tab="finance" role="tab" aria-selected="false" aria-controls="zazu-inspector-panel-finance">Financial Ledger</button>
        </div>

        <div class="zazu-inspector-body">
            <section id="zazu-inspector-panel-catering" role="tabpanel" tabindex="0" data-zazu-tab-panel="catering">
                <div class="zazu-inspector-section-title">Catering &amp; Menu Prep</div>
                <div class="zazu-inspector-grid">
                    <div class="zazu-inspector-field"><span>Packages</span><strong>Review function sheet</strong></div>
                    <div class="zazu-inspector-field"><span>Headcount</span><strong>Not recorded</strong></div>
                    <div class="zazu-inspector-field zazu-inspector-field-wide"><span>Dietary notes</span><strong>None recorded</strong></div>
                </div>
            </section>

            <section id="zazu-inspector-panel-equipment" role="tabpanel" tabindex="0" class="hidden" data-zazu-tab-panel="equipment">
                <div class="zazu-inspector-section-title">Equipment Hire Manifest</div>
                <div class="zazu-inspector-empty">No equipment requirements are recorded on this job yet.</div>
            </section>

            <section id="zazu-inspector-panel-finance" role="tabpanel" tabindex="0" class="hidden" data-zazu-tab-panel="finance">
                <div class="zazu-inspector-section-title">Financial Ledger</div>
                <div class="zazu-inspector-finance">
                    <div><span>SARS VAT</span><strong>15%</strong></div>
                    <div><span>Total quote</span><strong>Not quoted</strong></div>
                    <div><span>Deposit status</span><strong>Not recorded</strong></div>
                </div>
                <a href="{{ route('work.show', $events->first() ?? 1) }}" class="zazu-btn zazu-btn-primary w-full mt-4" data-zazu-finance-link>Open financial record</a>
            </section>
        </div>
    </aside>

    <script>
        (() => {
            const panel = document.querySelector('[data-zazu-inspector-panel]');
            const backdrop = document.querySelector('.zazu-inspector-backdrop');
            if (!panel || !backdrop) return;

            const open = (button) => {
                const row = button.closest('[data-zazu-job-row]');
                const source = row?.querySelector('[data-zazu-inspector]');
                const data = source?.firstElementChild?.dataset;
                if (!data) return;

                panel.querySelector('[data-zazu-inspector-reference]').textContent = data.eventReference || '—';
                panel.querySelector('[data-zazu-inspector-client]').textContent = data.customer || '—';
                panel.querySelector('[data-zazu-inspector-type]').textContent = data.eventType || '—';
                panel.querySelector('[data-zazu-inspector-date]').textContent = data.eventDate || '—';
                panel.querySelector('[data-zazu-inspector-status]').textContent = data.eventStatus || '—';

                const financeLink = panel.querySelector('[data-zazu-finance-link]');
                if (financeLink) financeLink.href = data.showRoute || '#';

                panel.querySelectorAll('[data-zazu-tab]').forEach(tab => {
                    tab.classList.toggle('is-active', tab.dataset.zazuTab === 'catering');
                    tab.setAttribute('aria-selected', tab.dataset.zazuTab === 'catering' ? 'true' : 'false');
                });
                panel.querySelectorAll('[data-zazu-tab-panel]').forEach(section => section.classList.toggle('hidden', section.dataset.zazuTab !== 'catering'));

                panel.classList.add('is-open');
                backdrop.classList.add('is-visible');
                panel.setAttribute('aria-hidden', 'false');
                document.body.classList.add('zazu-inspector-open');
            };

            const close = () => {
                panel.classList.remove('is-open');
                backdrop.classList.remove('is-visible');
                panel.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('zazu-inspector-open');
            };

            document.querySelectorAll('[data-zazu-inspector-open]').forEach(button => button.addEventListener('click', (event) => {
                if (button.matches('a, form, button') && button.closest('.zazu-ops-actions') && button.tagName !== 'BUTTON') return;
                event.preventDefault();
                open(button);
            }));

            document.querySelectorAll('[data-zazu-inspector-close]').forEach(button => button.addEventListener('click', close));
            document.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });

            const tabs = [...panel.querySelectorAll('[data-zazu-tab]')];
            const activateTab = (tab, moveFocus = false) => {
                const target = tab.dataset.zazuTab;
                tabs.forEach(item => {
                    const active = item === tab;
                    item.classList.toggle('is-active', active);
                    item.setAttribute('aria-selected', active ? 'true' : 'false');
                    item.tabIndex = active ? 0 : -1;
                });
                panel.querySelectorAll('[data-zazu-tab-panel]').forEach(section => {
                    section.classList.toggle('hidden', section.dataset.zazuTab !== target);
                });
                if (moveFocus) tab.focus();
            };
            tabs.forEach((tab, index) => {
                tab.addEventListener('click', () => activateTab(tab));
                tab.addEventListener('keydown', event => {
                    if (!['ArrowRight', 'ArrowLeft', 'Home', 'End'].includes(event.key)) return;
                    event.preventDefault();
                    const nextIndex = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 :
                        (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
                    activateTab(tabs[nextIndex], true);
                });
            });
        })();
    </script>
</x-app-layout>