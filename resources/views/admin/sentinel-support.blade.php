<x-app-layout>
    <x-slot:title>Founder support session · Zazu</x-slot:title>
    <x-slot:heading>Founder support session</x-slot:heading>
    <x-slot:headerAction>
        <form method="POST" action="{{ route('admin.sentinel.support.end', $supportSession) }}">
            @csrf
            <button type="submit" class="zazu-btn zazu-btn-secondary">End support access</button>
        </form>
    </x-slot:headerAction>

    <style nonce="{{ request()->attributes->get('cspNonce') }}">
        .founder-support{display:grid;gap:1rem;max-width:1100px}
        .founder-support-panel{padding:1rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:16px;background:var(--zazu-surface,#fff);min-width:0}
        .founder-support-banner{border-left:4px solid #b7791f}
        .founder-support-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;min-width:0}
        .founder-support-table-wrap{max-width:100%;min-width:0}
        .founder-support table{width:100%;border-collapse:collapse;font-size:.9rem}
        .founder-support th,.founder-support td{text-align:left;padding:.7rem;border-bottom:1px solid var(--zazu-border,#e3e7ed);vertical-align:top}
        .founder-support th{font-size:.72rem;text-transform:uppercase;color:var(--zazu-muted,#687386)}
        .founder-support-muted{font-size:.83rem;color:var(--zazu-muted,#687386)}
        @media(max-width:850px){.founder-support-grid{grid-template-columns:1fr}}
        @media(max-width:560px){
            .founder-support-panel{padding:.85rem}
            .founder-support table,.founder-support tbody{display:block;width:100%}
            .founder-support thead{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
            .founder-support tbody tr{display:block;margin:.65rem 0;padding:.35rem .65rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:12px}
            .founder-support tbody td{display:flex;justify-content:space-between;gap:1rem;padding:.5rem .1rem;border-bottom:1px solid var(--zazu-border,#e3e7ed);text-align:right;overflow-wrap:anywhere}
            .founder-support tbody td:last-child{border-bottom:0}
            .founder-support tbody td[data-label]::before{content:attr(data-label);font-weight:600;color:var(--zazu-muted,#687386);text-align:left;flex:0 0 38%}
            .founder-support tbody td[colspan]{display:block;text-align:left}
        }
    </style>

    <main class="founder-support">
        <section class="founder-support-panel founder-support-banner">
            <p class="font-semibold">READ-ONLY FOUNDER SUPPORT ACCESS</p>
            <h2 class="text-xl font-semibold mt-1">{{ $business->name }}</h2>
            <p class="founder-support-muted mt-2">You are viewing limited customer and event summaries for this business. No owner impersonation, edits, payments, exports or account mutations are available in this session.</p>
            <div class="founder-support-grid mt-3">
                <div><span class="founder-support-muted">Reason</span><p>{{ $supportSession->reason }}</p></div>
                <div><span class="founder-support-muted">Access expires</span><p>{{ $supportSession->expires_at->format('d M Y H:i:s') }}</p></div>
            </div>
            <p class="founder-support-muted mt-2">Session #{{ $supportSession->id }} · Started {{ $supportSession->started_at->format('d M Y H:i:s') }}</p>
        </section>

        <section class="founder-support-grid">
            <div class="founder-support-panel">
                <h3 class="font-semibold">Customer overview <span class="founder-support-muted">({{ $customers->count() }} shown)</span></h3>
                <div class="founder-support-table-wrap mt-3">
                    <table><thead><tr><th>Customer</th><th>Created</th></tr></thead><tbody>
                    @forelse($customers as $customer)
                        <tr><td data-label="Customer">{{ $customer->name }}</td><td data-label="Created">{{ $customer->created_at?->format('d M Y') ?? '—' }}</td></tr>
                    @empty
                        <tr><td colspan="2">No customer records.</td></tr>
                    @endforelse
                    </tbody></table>
                </div>
                <p class="founder-support-muted mt-2">Showing at most 20 records. Contact details and private notes are not exposed.</p>
            </div>

            <div class="founder-support-panel">
                <h3 class="font-semibold">Recent events <span class="founder-support-muted">({{ $events->count() }} shown)</span></h3>
                <div class="founder-support-table-wrap mt-3">
                    <table><thead><tr><th>Reference</th><th>Event</th><th>Date</th><th>Status</th></tr></thead><tbody>
                    @forelse($events as $event)
                        <tr><td data-label="Reference">{{ $event->reference }}</td><td data-label="Event">{{ $event->name }}</td><td data-label="Date">{{ $event->event_date?->format('d M Y') ?? '—' }}</td><td data-label="Status">{{ ucfirst($event->status) }}</td></tr>
                    @empty
                        <tr><td colspan="4">No event records.</td></tr>
                    @endforelse
                    </tbody></table>
                </div>
                <p class="founder-support-muted mt-2">Showing at most 20 records. Quotes, payments, financials, attachments and customer contact details are not exposed.</p>
            </div>
        </section>
        <section class="founder-support-panel">
            <h3 class="font-semibold">Audit notice</h3>
            <p class="founder-support-muted">Opening this support view has been recorded in the platform audit log. Access automatically expires after 15 minutes. End it immediately when support is complete.</p>
        </section>
    </main>
</x-app-layout>
