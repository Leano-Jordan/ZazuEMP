<x-app-layout>
    <x-slot:title>Setup centre</x-slot:title>
    <x-slot:heading>Setup centre</x-slot:heading>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Business setup</div>
            <h2 class="zazu-command-title">Finish the pieces that make Zazu useful.</h2>
            <p class="zazu-command-copy">Skipped setup is never lost. Come back here whenever you are ready, without rebuilding anything you already entered.</p>
        </div>
    </section>

    <section class="zazu-detail-grid">
        <div class="zazu-detail-stack">
            <section class="zazu-card">
                <div class="zazu-card-header">
                    <div>
                        <div class="zazu-eyebrow">Core setup</div>
                        <div class="zazu-card-title mt-1">Your business workspace</div>
                    </div>
                </div>

                <div class="zazu-list">
                    <div class="zazu-list-item">
                        <div class="zazu-list-main">
                            <div class="zazu-list-title">Services & prices</div>
                            <div class="zazu-list-meta">Add the services, products, rentals and packages your business actually offers.</div>
                        </div>
                        <div class="zazu-list-side flex items-center gap-2">
                            <span class="zazu-chip {{ $catalogueStatus === 'completed' ? 'zazu-chip-success' : ($catalogueStatus === 'deferred' ? 'zazu-chip-neutral' : 'zazu-chip-info') }}">{{ $catalogueStatus === 'deferred' ? 'Deferred' : ucfirst(str_replace('_',' ', $catalogueStatus)) }}</span>
                            <a href="{{ route('onboarding.catalogue') }}" class="zazu-btn {{ $catalogueStatus === 'completed' ? 'zazu-btn-ghost' : 'zazu-btn-primary' }}">{{ $catalogueStatus === 'completed' ? 'Review' : 'Continue' }}</a>
                        </div>
                    </div>

                    <div class="zazu-list-item">
                        <div class="zazu-list-main">
                            <div class="zazu-list-title">Workspace experience</div>
                            <div class="zazu-list-meta">Choose how much of Zazu is surfaced at once. This affects presentation and guidance, not permissions.</div>
                        </div>
                        <div class="zazu-list-side flex items-center gap-2">
                            <span class="zazu-chip {{ $experienceStatus === 'completed' ? 'zazu-chip-success' : 'zazu-chip-info' }}">{{ $experienceLevel ? ucfirst($experienceLevel) : 'Not selected' }}</span>
                            <a href="{{ route('preferences.experience') }}" class="zazu-btn zazu-btn-ghost">{{ $experienceStatus === 'completed' ? 'Change' : 'Choose' }}</a>
                        </div>
                    </div>

                    <div class="zazu-list-item">
                        <div class="zazu-list-main">
                            <div class="zazu-list-title">Business identity</div>
                            <div class="zazu-list-meta">Business name, contact details, address, website, registration and tax reference information.</div>
                        </div>
                        <div class="zazu-list-side flex items-center gap-2">
                            <span class="zazu-chip {{ $businessStatus === 'completed' ? 'zazu-chip-success' : ($businessStatus === 'deferred' ? 'zazu-chip-neutral' : 'zazu-chip-info') }}">{{ $businessStatus === 'deferred' ? 'Deferred' : ucfirst(str_replace('_',' ', $businessStatus)) }}</span>
                            <a href="{{ route('onboarding.business') }}" class="zazu-btn {{ $businessStatus === 'completed' ? 'zazu-btn-ghost' : 'zazu-btn-primary' }}">{{ $businessStatus === 'completed' ? 'Review' : 'Continue' }}</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="zazu-card">
                <div class="zazu-card-header">
                    <div>
                        <div class="zazu-eyebrow">Optional but powerful</div>
                        <div class="zazu-card-title mt-1">Tax & compliance</div>
                        <div class="zazu-card-description">Prepare reusable business evidence for SARS, tenders, supplier onboarding and applicable local or industry requirements.</div>
                    </div>
                    <span class="zazu-chip {{ $taxStatus === 'in_progress' ? 'zazu-chip-info' : 'zazu-chip-neutral' }}">{{ $taxStatus === 'in_progress' ? 'Started' : 'Not started' }}</span>
                </div>
                <div class="zazu-panel-copy mt-3">Set your VAT status and effective tax rates, then keep certificates and supporting documents in the Compliance Centre. Zazu preserves the source and date of tax rules used on commercial documents.</div>
                <a href="{{ route('settings.compliance') }}" class="zazu-btn zazu-btn-secondary mt-4">Open Compliance Centre</a>
            </section>
        </div>

        <aside class="zazu-detail-stack">
            <section class="zazu-context-card">
                <div class="zazu-context-title">A better rule for setup</div>
                <div class="zazu-context-copy">Setup is a workspace, not a gate. You can start trading before every optional detail is complete, then return later without losing your progress.</div>
                <div class="zazu-step-list mt-4">
                    <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Build what you sell</div><div class="zazu-step-copy">Services & prices come first.</div></div></div>
                    <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Choose your workspace level</div><div class="zazu-step-copy">Control how much detail Zazu surfaces.</div></div></div>
                    <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Identify the business</div><div class="zazu-step-copy">Reusable details feed documents.</div></div></div>
                    <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Make it tender-ready</div><div class="zazu-step-copy">Add compliance evidence when needed.</div></div></div>
                </div>
            </section>

            <section class="zazu-context-card">
                <div class="zazu-context-title">Active workspace</div>
                <div class="zazu-context-copy">{{ $business->name }}</div>
                <div class="zazu-context-copy mt-1">{{ $business->currency ?? 'ZAR' }} · {{ $business->status }}</div>
            </section>
        </aside>
    </section>
</x-app-layout>