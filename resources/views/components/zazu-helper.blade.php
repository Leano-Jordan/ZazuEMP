<style>
.zazu-helper {
    position: fixed;
    right: 18px;
    bottom: 18px;
    z-index: 80;
    font-size: 12px;
}
.zazu-helper-toggle {
    min-height: 40px;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 0 13px;
    border: 1px solid var(--zazu-border);
    border-radius: 4px;
    background: var(--zazu-surface);
    color: var(--zazu-ink);
    box-shadow: var(--zazu-shadow-strong, 0 10px 30px rgba(0,0,0,.12));
    cursor: pointer;
    font-weight: 750;
}
.zazu-helper-toggle:hover {
    border-color: var(--zazu-border-strong);
}
.zazu-helper-panel {
    width: min(360px, calc(100vw - 28px));
    margin-bottom: 9px;
    padding: 16px;
    border: 1px solid var(--zazu-border);
    border-radius: 6px;
    background: var(--zazu-surface);
    color: var(--zazu-ink);
    box-shadow: var(--zazu-shadow-strong, 0 16px 42px rgba(0,0,0,.15));
}
.zazu-helper-panel[hidden] {
    display: none;
}
.zazu-helper-kicker {
    color: var(--zazu-primary);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.zazu-helper-title {
    margin-top: 5px;
    font-size: 15px;
    font-weight: 800;
}
.zazu-helper-copy {
    margin-top: 7px;
    color: var(--zazu-muted);
    line-height: 1.55;
}
.zazu-helper-step {
    margin-top: 12px;
    padding: 10px;
    border: 1px solid var(--zazu-border);
    border-radius: 4px;
    background: var(--zazu-surface-2);
}
.zazu-helper-step-label {
    color: var(--zazu-faint);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.zazu-helper-step strong {
    display: block;
    margin-top: 3px;
    font-size: 12px;
}
.zazu-helper-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: 13px;
}
.zazu-helper-nav {
    display: flex;
    gap: 6px;
}
.zazu-helper-btn {
    min-height: 34px;
    padding: 0 10px;
    border: 1px solid var(--zazu-border);
    border-radius: 2px;
    background: var(--zazu-surface);
    color: var(--zazu-ink);
    font-size: 11px;
    font-weight: 750;
    cursor: pointer;
}
.zazu-helper-btn-primary {
    border-color: var(--zazu-primary);
    background: var(--zazu-primary);
    color: var(--zazu-primary-ink);
}
.zazu-helper-link {
    color: var(--zazu-link);
    font-weight: 750;
    text-decoration: none;
}
.zazu-helper-link:hover {
    text-decoration: underline;
}
@media (max-width: 640px) {
    .zazu-helper {
        right: 12px;
        bottom: 12px;
    }
    .zazu-helper-panel {
        width: min(360px, calc(100vw - 24px));
    }
}
@media (prefers-reduced-motion: reduce) {
    .zazu-helper *,
    .zazu-helper {
        scroll-behavior: auto !important;
        transition: none !important;
    }
}
</style>

@php
    $routeName = request()->route()?->getName();
    $guides = [
        'dashboard' => [
            ['label' => 'Start', 'title' => 'Define what your business offers', 'copy' => 'Services and products become reusable building blocks for jobs, requirements and quotes.', 'href' => route('capabilities.index'), 'link' => 'Open Services'],
            ['label' => 'Next', 'title' => 'Create the job', 'copy' => 'The Job is the operational home for the customer, requirements, quotes, preparation and costs.', 'href' => route('work.index'), 'link' => 'Open Jobs'],
            ['label' => 'Then', 'title' => 'Follow the job through execution', 'copy' => 'Move from accepted quote to requirements, purchasing, inventory, preparation and finance without losing the thread.'],
        ],
        'capabilities.index' => [
            ['label' => 'Services', 'title' => 'Define what you sell or provide', 'copy' => 'Keep your common offerings reusable instead of rebuilding them for every job.'],
            ['label' => 'Use', 'title' => 'Connect offerings to requirements and quotes', 'copy' => 'A capability can become a requirement on a job and a line in a commercial document.'],
            ['label' => 'Maintain', 'title' => 'Keep the catalogue trustworthy', 'copy' => 'Archive or deactivate outdated offerings instead of deleting history.'],
        ],
        'work.index' => [
            ['label' => 'Jobs', 'title' => 'Treat each event as one operational workspace', 'copy' => 'The job should be the place where the customer, date, requirements, quotes and execution state meet.'],
            ['label' => 'Plan', 'title' => 'Open a job before creating disconnected records', 'copy' => 'Use the job context to keep downstream work tied to the correct customer and event.'],
            ['label' => 'Control', 'title' => 'Watch exceptions', 'copy' => 'Pay attention to approaching work, blocked preparation, procurement gaps and unresolved actions.'],
        ],
        'work.show' => [
            ['label' => 'Review', 'title' => 'Start with the event facts', 'copy' => 'Confirm the customer, date, contacts, status and location before changing the job.'],
            ['label' => 'Build', 'title' => 'Turn the brief into requirements', 'copy' => 'Requirements become the operational checklist for what must be delivered or prepared.'],
            ['label' => 'Execute', 'title' => 'Use the downstream workspaces', 'copy' => 'Quote, preparation, purchasing, inventory, assets, costs and travel should all remain connected to this job.'],
        ],
        'quotes.index' => [
            ['label' => 'Quotes', 'title' => 'Build from the work', 'copy' => 'Use the job requirements as the commercial starting point rather than retyping the scope.'],
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
    $steps = $guides[$routeName] ?? [];
@endphp

@if(!empty($steps))
    <div class="zazu-helper" data-zazu-helper data-zazu-guide-route="{{ $routeName }}" data-zazu-guide-enabled="on">
        <section id="zazu-helper-panel" class="zazu-helper-panel" data-zazu-helper-panel role="dialog" aria-modal="false" aria-label="Zazu workflow guide" hidden>
            <div class="zazu-helper-kicker">Zazu guide</div>
            <div class="zazu-helper-title" data-zazu-helper-title>{{ $steps[0]['title'] }}</div>
            <p class="zazu-helper-copy" data-zazu-helper-copy>{{ $steps[0]['copy'] }}</p>
            <div class="zazu-helper-step">
                <div class="zazu-helper-step-label" data-zazu-helper-step-label>{{ $steps[0]['label'] }}</div>
                <div data-zazu-helper-count>1 of {{ count($steps) }}</div>
                <a href="#" class="zazu-helper-link" data-zazu-helper-link hidden></a>
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
        <button type="button" class="zazu-helper-toggle" data-zazu-helper-toggle aria-expanded="false" aria-pressed="true" aria-controls="zazu-helper-panel">
            <span aria-hidden="true">?</span>
            <span data-zazu-helper-toggle-label>Guide</span>
        </button>
        <div hidden>
            @foreach($steps as $index => $step)
                <template data-zazu-helper-step-data data-index="{{ $index }}" data-label="{{ $step['label'] }}" data-title="{{ $step['title'] }}" data-copy="{{ $step['copy'] }}" @if(!empty($step['href'])) data-href="{{ $step['href'] }}" data-link="{{ $step['link'] }}" @endif></template>
            @endforeach
        </div>
    </div>
@endif