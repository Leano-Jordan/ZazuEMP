<section class="zazu-dash-surface zazu-offline-workspace" data-zazu-offline-workspace hidden aria-label="Offline workspace">
    <header class="zazu-dash-surface-head zazu-dash-panel-head zazu-offline-workspace-head">
        <div class="zazu-dash-panel-heading">
            <span class="zazu-dash-kicker">Device workspace</span>
            <h2>Offline workspace</h2>
            <p class="zazu-card-description">Your locally saved business records. Changes stay on this device until Zazu reconnects.</p>
        </div>
        <nav class="zazu-action-group" aria-label="Offline workspace navigation">
            {{-- Reuse the dashboard's business-scoped permission snapshot for offline navigation. --}}
            @if($can('customers.view'))
                <a href="{{ route('customers.index') }}" class="zazu-btn zazu-btn-secondary">Open customers</a>
            @endif
            @if($can('work.view'))
                <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-secondary">Open work</a>
            @endif
            @if($can('quotes.view'))
                <a href="{{ route('quotes.index') }}" class="zazu-btn zazu-btn-secondary">Quotes</a>
            @endif
            @if($can('suppliers.view'))
                <a href="{{ route('suppliers.index') }}" class="zazu-btn zazu-btn-secondary">Suppliers</a>
            @endif
            @if($can('purchasing.view'))
                <a href="{{ route('purchasing.index') }}" class="zazu-btn zazu-btn-secondary">Purchasing</a>
            @endif
            @if($can('inventory.view'))
                <a href="{{ route('inventory.index') }}" class="zazu-btn zazu-btn-secondary">Inventory</a>
            @endif
            @if($can('assets.view'))
                <a href="{{ route('assets.index') }}" class="zazu-btn zazu-btn-secondary">Assets</a>
            @endif
            @if($can('finance.view'))
                <a href="{{ route('finance.index') }}" class="zazu-btn zazu-btn-secondary">Finance</a>
            @endif
        </nav>
        <span class="zazu-chip zazu-chip-neutral">Device local</span>
    </header>
    <section class="zazu-dash-grid zazu-offline-overview" data-zazu-offline-view="overview" aria-label="Offline workspace overview">
        <article class="zazu-dash-surface">
            <span class="zazu-dash-kicker">Saved records</span>
            <strong class="zazu-offline-overview-value" data-zazu-offline-overview-count="records">0</strong>
            <span>Available on this device</span>
        </article>
        <article class="zazu-dash-surface">
            <span class="zazu-dash-kicker">Waiting to sync</span>
            <strong class="zazu-offline-overview-value" data-zazu-offline-overview-count="pending">0</strong>
            <span>Local changes not yet confirmed by the server</span>
        </article>
        <article class="zazu-dash-surface">
            <span class="zazu-dash-kicker">Needs attention</span>
            <strong class="zazu-offline-overview-value" data-zazu-offline-overview-count="conflicts">0</strong>
            <span>Conflicts requiring a decision</span>
        </article>
        <article class="zazu-dash-surface zazu-offline-overview-status">
            <span class="zazu-dash-kicker">Workspace status</span>
            <strong data-zazu-offline-overview-status>Your saved workspace is ready on this device</strong>
            <span>Reconnect to send queued changes and refresh server data.</span>
            <div class="zazu-offline-storage-protection mt-3" data-zazu-offline-storage>
                <span class="zazu-dash-kicker">Device storage protection</span>
                <strong data-zazu-offline-storage-status>Checking browser storage protection…</strong>
                <span data-zazu-offline-storage-usage></span>
                <p class="zazu-card-description">Persistent storage can reduce automatic browser cleanup. It cannot prevent someone from clearing this browser's site data.</p>
                <p class="zazu-field-help" data-zazu-offline-storage-error hidden role="alert"></p>
                <button type="button" class="zazu-btn zazu-btn-secondary mt-2" data-zazu-offline-storage-request hidden>Request storage protection</button>
            </div>
        </article>
    </section>
    <section class="zazu-dash-surface" data-zazu-offline-unavailable hidden aria-labelledby="zazu-offline-unavailable-title">
        <h3 id="zazu-offline-unavailable-title" class="zazu-dash-panel-heading">This area needs a connection</h3>
        <p class="zazu-card-description">This screen is not available offline yet. Your saved business records remain on this device. Reconnect to use this area, or return to the offline workspace.</p>
        <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-secondary">Back to offline workspace</a>
    </section>
    <div class="zazu-dash-grid">
        <section class="zazu-dash-surface" data-zazu-offline-view="customers" aria-label="Customers saved on this device">
            <h3 class="zazu-dash-panel-heading">Customers</h3>
            <div class="zazu-customer-directory zazu-list">
                <div class="zazu-empty">No customer records saved on this device.</div>
            </div>
        </section>
        <section class="zazu-dash-surface" data-zazu-offline-view="jobs" aria-label="Jobs saved on this device">
            <h3 class="zazu-dash-panel-heading">Event Operations</h3>
            <div class="zazu-ops-register zazu-card">
                <div class="zazu-empty">No jobs saved on this device.</div>
            </div>
        </section>
        <section class="zazu-dash-surface" data-zazu-offline-view="quotes" aria-label="Quotes saved on this device">
            <h3 class="zazu-dash-panel-heading">Quotes</h3>
            <div class="zazu-customer-directory zazu-list" data-zazu-local-quotes>
                <div class="zazu-empty">No quote records available on this device.</div>
            </div>
        </section>
        <section class="zazu-dash-surface" aria-label="Suppliers saved on this device" data-zazu-offline-collection="suppliers" data-zazu-offline-view="suppliers">
            <h3 class="zazu-dash-panel-heading">Suppliers</h3>
            <div class="zazu-customer-directory zazu-list" data-zazu-offline-collection-list="suppliers">
                <div class="zazu-empty">No supplier records stored on this device.</div>
            </div>
        </section>
        <section class="zazu-dash-surface" aria-label="Services and catalogue saved on this device" data-zazu-offline-collection="catalogue" data-zazu-offline-view="catalogue">
            <h3 class="zazu-dash-panel-heading">Services &amp; catalogue</h3>
            <div class="zazu-customer-directory zazu-list" data-zazu-offline-collection-list="catalogue">
                <div class="zazu-empty">No service records stored on this device.</div>
            </div>
        </section>
        <section class="zazu-dash-surface" aria-label="Inventory saved on this device" data-zazu-offline-collection="inventory_items" data-zazu-offline-view="inventory_items">
            <h3 class="zazu-dash-panel-heading">Inventory</h3>
            <div class="zazu-customer-directory zazu-list" data-zazu-offline-collection-list="inventory_items">
                <div class="zazu-empty">No inventory records stored on this device.</div>
            </div>
        </section>
        <section class="zazu-dash-surface" aria-label="Assets saved on this device" data-zazu-offline-collection="assets" data-zazu-offline-view="assets">
            <h3 class="zazu-dash-panel-heading">Assets</h3>
            <div class="zazu-customer-directory zazu-list" data-zazu-offline-collection-list="assets">
                <div class="zazu-empty">No asset records stored on this device.</div>
            </div>
        </section>
        <section class="zazu-dash-surface" aria-label="Purchase orders saved on this device" data-zazu-offline-collection="purchase_orders" data-zazu-offline-view="purchase_orders">
            <h3 class="zazu-dash-panel-heading">Purchase orders</h3>
            <div class="zazu-customer-directory zazu-list" data-zazu-offline-collection-list="purchase_orders">
                <div class="zazu-empty">No purchase orders stored on this device.</div>
            </div>
        </section>
        <section class="zazu-dash-surface" aria-label="Invoices saved on this device" data-zazu-offline-collection="invoices" data-zazu-offline-view="invoices">
            <h3 class="zazu-dash-panel-heading">Invoices</h3>
            <div class="zazu-customer-directory zazu-list" data-zazu-offline-collection-list="invoices">
                <div class="zazu-empty">No invoices stored on this device.</div>
            </div>
        </section>
        <section class="zazu-dash-surface" aria-label="Expenses saved on this device" data-zazu-offline-collection="expenses" data-zazu-offline-view="expenses">
            <h3 class="zazu-dash-panel-heading">Expenses</h3>
            <div class="zazu-customer-directory zazu-list" data-zazu-offline-collection-list="expenses">
                <div class="zazu-empty">No expenses stored on this device.</div>
            </div>
        </section>
        <section class="zazu-dash-surface" aria-label="Event costs saved on this device" data-zazu-offline-collection="costs" data-zazu-offline-view="costs">
            <h3 class="zazu-dash-panel-heading">Event costs</h3>
            <div class="zazu-customer-directory zazu-list" data-zazu-offline-collection-list="costs">
                <div class="zazu-empty">No event costs stored on this device.</div>
            </div>
        </section>
    </div>
</section>