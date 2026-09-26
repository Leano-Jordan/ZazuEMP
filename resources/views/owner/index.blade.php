<x-app-layout>
    <x-slot:title>Owner Administration</x-slot:title>
    <x-slot:heading>Owner Administration</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('settings.index') }}" class="zazu-btn zazu-btn-secondary">Business settings</a></x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Protected control</div>
            <h2 class="zazu-command-title">Owner administration</h2>
            <p class="zazu-command-copy">This area is available only to the owner of the active business workspace. Staff access is rejected server-side.</p>
        </div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Authority</div><div class="zazu-command-meta-value">Owner</div></div>
    </section>

    <section class="zazu-detail-grid">
        <div class="zazu-panel">
            <div class="zazu-panel-head">
                <div>
                    <div class="zazu-panel-title">Ownership boundary</div>
                    <div class="zazu-panel-copy">Business configuration and future administration controls belong behind the owner authorization boundary.</div>
                </div>
                <span class="zazu-chip zazu-chip-accent">Protected</span>
            </div>
            <div class="zazu-placeholder">
                <div class="zazu-placeholder-title">Owner control surface</div>
                <div class="zazu-placeholder-copy">This foundation establishes the protected entry point before deeper administration features are added.</div>
            </div>
        </div>

        <div class="zazu-detail-stack">
            <a href="{{ route('settings.index') }}" class="zazu-route-card">
                <div>
                    <div class="zazu-route-card-title">Business settings</div>
                    <div class="zazu-route-card-copy">Manage workspace identity, currency and branding.</div>
                </div>
                <span class="zazu-route-card-arrow">→</span>
            </a>
            <a href="{{ route('capabilities.index') }}" class="zazu-route-card">
                <div>
                    <div class="zazu-route-card-title">Capabilities</div>
                    <div class="zazu-route-card-copy">Review the business service and capability catalogue.</div>
                </div>
                <span class="zazu-route-card-arrow">→</span>
            </a>
        </div>
    </section>
</x-app-layout>
