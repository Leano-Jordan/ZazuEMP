<x-app-layout>
    <x-slot:title>Suppliers</x-slot:title>
    <x-slot:heading>Suppliers</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-secondary">Jobs</a></x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Resources</div>
            <h2 class="zazu-command-title">Supplier relationships</h2>
            <p class="zazu-command-copy">Keep buying relationships close to the work they support. The register will become the source for supplier contacts, terms and job-linked purchasing.</p>
        </div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Stage</div><div class="zazu-command-meta-value">Foundation</div></div>
    </section>

    <section class="zazu-module-grid">
        <a href="{{ route('capabilities.index') }}" class="zazu-module">
            <div class="zazu-module-top"><span class="zazu-module-group">Services</span><span class="zazu-module-arrow">→</span></div>
            <div class="zazu-module-title">Catering & consumables</div>
            <div class="zazu-module-copy">Review the services and usual pricing that may drive future supplier requirements.</div>
        </a>
        <a href="{{ route('assets.index') }}" class="zazu-module">
            <div class="zazu-module-top"><span class="zazu-module-group">Equipment</span><span class="zazu-module-arrow">→</span></div>
            <div class="zazu-module-title">Equipment & hire</div>
            <div class="zazu-module-copy">Move into the reusable asset view for equipment that needs accountability.</div>
        </a>
        <a href="{{ route('work.index') }}" class="zazu-module">
            <div class="zazu-module-top"><span class="zazu-module-group">Operations</span><span class="zazu-module-arrow">→</span></div>
            <div class="zazu-module-title">Job requirements</div>
            <div class="zazu-module-copy">Open work records to see the requirements that will eventually drive buying.</div>
        </a>
        <a href="{{ route('calendar.index') }}" class="zazu-module">
            <div class="zazu-module-top"><span class="zazu-module-group">Timing</span><span class="zazu-module-arrow">→</span></div>
            <div class="zazu-module-title">Upcoming work</div>
            <div class="zazu-module-copy">Check scheduled jobs before planning future supplier commitments.</div>
        </a>
    </section>

    <section class="zazu-detail-grid mt-5">
        <div class="zazu-panel">
            <div class="zazu-panel-head">
                <div><div class="zazu-panel-title">Supplier register</div><div class="zazu-panel-copy">No supplier records are fabricated before the supplier transaction model exists.</div></div>
                <span class="zazu-chip zazu-chip-neutral">Not live</span>
            </div>
            <div class="zazu-placeholder">
                <div class="zazu-placeholder-title">The register is ready for the next data layer</div>
                <div class="zazu-placeholder-copy">The eventual record should own supplier identity, contacts, terms, status and buying history, then connect those records to jobs without duplicating supplier truth.</div>
            </div>
        </div>
        <div class="zazu-detail-stack">
            <a href="{{ route('work.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Open jobs</div><div class="zazu-route-card-copy">See operational requirements and preparation.</div></div><span class="zazu-route-card-arrow">→</span></a>
            <a href="{{ route('assets.index') }}" class="zazu-route-card"><div><div class="zazu-route-card-title">Open assets</div><div class="zazu-route-card-copy">Review reusable equipment accountability.</div></div><span class="zazu-route-card-arrow">→</span></a>
        </div>
    </section>
</x-app-layout>