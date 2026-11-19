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
        .founder-support-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
        .founder-support-table-wrap{overflow-x:auto;max-width:100%;-webkit-overflow-scrolling:touch}
        .founder-support table{width:100%;border-collapse:collapse;min-width:560px;font-size:.9rem}
        .founder-support th,.founder-support td{text-align:left;padding:.7rem;border-bottom:1px solid var(--zazu-border,#e3e7ed);vertical-align:top}
        .founder-support th{font-size:.72rem;text-transform:uppercase;color:var(--zazu-muted,#687386)}
        .founder-support-muted{font-size:.83rem;color:var(--zazu-muted,#687386)}
        @media(max-width:700px){.founder-support-grid{grid-template-columns:1fr}.founder-support-panel{padding:.85rem}}
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
                        <tr><td>{{ $customer->name }}</td><td>{{ $customer->created_at?->format('d M Y') ?? '—' }}</td></tr>
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
                        <tr><td>{{ $event->reference }}</td><td>{{ $event->name }}</td><td>{{ $event->event_date?->format('d M Y') ?? '—' }}</td><td>{{ ucfirst($event->status) }}</td></tr>
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
