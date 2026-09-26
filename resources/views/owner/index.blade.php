<x-app-layout>
    <x-slot:title>Owner Administration</x-slot:title>
    <x-slot:heading>Owner Administration</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('settings.index') }}" class="zazu-btn zazu-btn-secondary">Business settings</a>
        <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-ghost">Dashboard</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Protected control</div>
            <h2 class="zazu-command-title">{{ $business->name }}</h2>
            <p class="zazu-command-copy">Owner administration for the active business workspace. Authority is enforced server-side.</p>
        </div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Authority</div><div class="zazu-command-meta-value">Owner</div></div>
    </section>

    <section class="zazu-metric-grid">
        <a href="{{ route('customers.index') }}" class="zazu-metric-card zazu-metric-link">
            <div class="zazu-metric-label">Customers</div>
            <div class="zazu-metric-value">{{ $customerCount }}</div>
            <div class="zazu-metric-copy">Customer relationships in this workspace.</div>
        </a>
        <a href="{{ route('work.index') }}" class="zazu-metric-card zazu-metric-link">
            <div class="zazu-metric-label">Active jobs</div>
            <div class="zazu-metric-value">{{ $jobCount }}</div>
            <div class="zazu-metric-copy">Current operational work.</div>
        </a>
        <a href="{{ route('capabilities.index') }}" class="zazu-metric-card zazu-metric-link">
            <div class="zazu-metric-label">Active services</div>
            <div class="zazu-metric-value">{{ $capabilityCount }}</div>
            <div class="zazu-metric-copy">Reusable catalogue entries.</div>
        </a>
        <div class="zazu-metric-card">
            <div class="zazu-metric-label">Workspace members</div>
            <div class="zazu-metric-value">{{ $ownerCount + $staffCount }}</div>
            <div class="zazu-metric-copy">{{ $ownerCount }} owner{{ $ownerCount === 1 ? '' : 's' }} · {{ $staffCount }} staff</div>
        </div>
    </section>

    <section class="zazu-detail-grid">
        <section class="zazu-panel">
            <div class="zazu-panel-head">
                <div>
                    <div class="zazu-panel-title">Administration boundary</div>
                    <div class="zazu-panel-copy">Business settings and catalogue administration are currently the owner-controlled configuration surfaces.</div>
                </div>
                <span class="zazu-chip zazu-chip-accent">Protected</span>
            </div>

            <div class="mt-4 grid gap-3">
                <a href="{{ route('settings.index') }}" class="zazu-route-card">
                    <div>
                        <div class="zazu-route-card-title">Business settings</div>
                        <div class="zazu-route-card-copy">Manage identity, currency and business artwork.</div>
                    </div>
                    <span class="zazu-route-card-arrow">→</span>
                </a>
                <a href="{{ route('capabilities.index') }}" class="zazu-route-card">
                    <div>
                        <div class="zazu-route-card-title">Services & prices</div>
                        <div class="zazu-route-card-copy">Maintain reusable services, products and rental definitions.</div>
                    </div>
                    <span class="zazu-route-card-arrow">→</span>
                </a>
            </div>
        </section>

        <aside class="zazu-detail-stack">
            <div class="zazu-panel">
                <div class="zazu-panel-title">Current workspace</div>
                <div class="zazu-detail-rows">
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Business</div><div class="zazu-detail-value">{{ $business->name }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Currency</div><div class="zazu-detail-value">{{ $business->currency }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Status</div><div class="zazu-detail-value">{{ ucfirst($business->status) }}</div></div>
                </div>
            </div>
            <div class="zazu-panel">
                <div class="zazu-panel-title">Security boundary</div>
                <div class="zazu-panel-copy">Owner access is checked against active business membership. Staff cannot elevate their role through the owner entry point.</div>
            </div>
        </aside>
    </section>
</x-app-layout>