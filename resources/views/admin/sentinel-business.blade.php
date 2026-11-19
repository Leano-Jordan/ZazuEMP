<x-app-layout>
    <x-slot:title>Business account · Founder Control Centre</x-slot:title>
    <x-slot:heading>Business account</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('admin.sentinel', ['section' => 'accounts']) }}" class="zazu-btn zazu-btn-secondary">Back to Founder Control</a>
    </x-slot:headerAction>

    <style nonce="{{ request()->attributes->get('cspNonce') }}">
        .founder-account{display:grid;gap:1rem}
        .founder-account-panel{padding:1.1rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:16px;background:var(--zazu-surface,#fff)}
        .founder-account-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.75rem}
        .founder-account-stat{padding:.9rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:12px}
        .founder-account-stat strong{display:block;font-size:1.45rem;font-variant-numeric:tabular-nums}
        .founder-account-stat span,.founder-muted{font-size:.82rem;color:var(--zazu-muted,#687386)}
        .founder-account-table{width:100%;border-collapse:collapse;font-size:.9rem}
        .founder-account-table th,.founder-account-table td{text-align:left;padding:.75rem;border-bottom:1px solid var(--zazu-border,#e3e7ed)}
        .founder-account-table th{font-size:.72rem;text-transform:uppercase;color:var(--zazu-muted,#687386)}
        .founder-pill{display:inline-block;padding:.2rem .6rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:999px;font-size:.75rem}
        @media(max-width:700px){.founder-account-grid{grid-template-columns:1fr}.founder-account-table-wrap{overflow-x:auto}}
    </style>

    <div class="founder-account">
        <section class="founder-account-panel">
            <div class="founder-muted">Rosscore Labs · Founder-only account inspection</div>
            <h2 class="text-xl font-semibold mt-1">{{ $business->name }}</h2>
            <div class="flex flex-wrap gap-2 mt-3">
                <span class="founder-pill">{{ strtoupper($business->status ?: 'unknown') }}</span>
                <span class="founder-pill">Business #{{ $business->id }}</span>
                <span class="founder-pill">Created {{ $business->created_at?->format('d M Y H:i') ?? 'Unknown' }}</span>
            </div>
            <p class="founder-muted mt-3">This page provides account-level visibility only. It does not switch your session into this business or grant access to its private operational records.</p>
        </section>

        <section class="founder-account-panel">
            <h3 class="font-semibold mb-3">Business profile</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <div><span class="founder-muted">Business email</span><p>{{ $business->email ?: 'Not provided' }}</p></div>
                <div><span class="founder-muted">Phone</span><p>{{ $business->phone ?: 'Not provided' }}</p></div>
                <div><span class="founder-muted">Website</span><p class="break-all">{{ $business->website ?: 'Not provided' }}</p></div>
                <div><span class="founder-muted">Address</span><p>{{ $business->address ?: 'Not provided' }}</p></div>
                <div><span class="founder-muted">Tax number</span><p>{{ $business->tax_number ?: 'Not provided' }}</p></div>
                <div><span class="founder-muted">Currency</span><p>{{ $business->currency ?: 'Not set' }}</p></div>
            </div>
        </section>

        <section class="founder-account-grid" aria-label="Business account summary">
            <div class="founder-account-stat"><strong>{{ number_format($business->users_count) }}</strong><span>Linked user accounts</span></div>
            <div class="founder-account-stat"><strong>{{ number_format($business->customers_count) }}</strong><span>Customers</span></div>
            <div class="founder-account-stat"><strong>{{ number_format($business->events_count) }}</strong><span>Events</span></div>
        </section>

        <section class="founder-account-panel">
            <h3 class="font-semibold mb-2">Account membership</h3>
            <p class="founder-muted mb-3">Names, email addresses and business roles are shown for founder account administration. Passwords, reset tokens and session credentials are never exposed.</p>
            <div class="founder-account-table-wrap">
                <table class="founder-account-table">
                    <thead><tr><th>User</th><th>Email</th><th>Business role</th><th>Joined</th></tr></thead>
                    <tbody>
                    @forelse($business->users as $member)
                        <tr>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->email }}</td>
                            <td>{{ ucfirst($member->pivot->role ?? 'member') }}</td>
                            <td>{{ $member->pivot->created_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="founder-muted">No user accounts are currently linked to this business.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="founder-account-panel">
            <h3 class="font-semibold mb-2">Recent founder support history</h3>
            <p class="founder-muted mb-3">The latest 10 support sessions for this business, including their founder, reason and outcome.</p>
            <div class="founder-account-table-wrap">
                <table class="founder-account-table">
                    <thead><tr><th>Founder</th><th>Reason</th><th>Started</th><th>Expires</th><th>State</th></tr></thead>
                    <tbody>
                    @forelse($supportSessions as $support)
                        <tr>
                            <td>{{ $support->actor?->name ?? 'Deleted account' }}<br><span class="founder-muted">{{ $support->actor?->email ?? '—' }}</span></td>
                            <td>{{ $support->reason }}</td>
                            <td>{{ $support->started_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td>{{ $support->expires_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td>{{ $support->ended_at ? 'Ended' : ($support->expires_at?->isFuture() ? 'Active / available' : 'Expired') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="founder-muted">No founder support sessions have been recorded.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="founder-account-panel">
            <h3 class="font-semibold mb-2">Founder support access</h3>
            <p class="founder-muted mb-3">Start a separate, time-limited, read-only support session. The business owner is not impersonated and your own active workspace is not changed. Access is limited to customer names and recent event summaries.</p>
            <form method="POST" action="{{ route('admin.sentinel.support.start', $business) }}" class="grid gap-3">
                @csrf
                <label for="support-reason" class="font-medium text-sm">Reason for access</label>
                <textarea id="support-reason" name="reason" rows="3" minlength="10" maxlength="500" required class="w-full rounded-xl border border-[var(--zazu-border)] bg-[var(--zazu-surface)] p-3" placeholder="Describe the support request and why account visibility is needed.">{{ old('reason') }}</textarea>
                @error('reason')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <div><button type="submit" class="zazu-btn zazu-btn-primary">Start 15-minute read-only session</button></div>
            </form>
            <p class="founder-muted mt-3">Every start and view is written to the separate platform audit log. No edits, exports, payment access, credential access or account mutations are enabled.</p>
        </section>
    </div>
</x-app-layout>
