<x-app-layout>
    <x-slot:title>Settings</x-slot:title>
    <x-slot:heading>Settings</x-slot:heading>

    <section class="zazu-command-band">
        <div><div class="zazu-eyebrow">System</div><h2 class="zazu-command-title">Business settings</h2><p class="zazu-command-copy">Manage business settings, user access and privacy here.</p></div>
    </section>

    <section class="zazu-settings-grid">
        <div class="zazu-card zazu-setting-card">
            <div class="zazu-card-title">Business profile</div>
            <div class="zazu-card-description">Business identity and operational defaults.</div>
            <span class="zazu-chip zazu-chip-neutral mt-4">Foundation</span>
        </div>

        <div class="zazu-card zazu-setting-card">
            <div class="zazu-card-title">Staff profiles</div>
            <div class="zazu-card-description">Staff/user records now support an optional profile photo. The authenticated profile editor and staff directory remain part of the access-control layer.</div>
            <span class="zazu-chip zazu-chip-info mt-4">Access layer next</span>
        </div>

        <div class="zazu-card zazu-setting-card">
            <div class="zazu-card-title">Privacy & data</div>
            <div class="zazu-card-description">Customer names, contact details and identifiable photos are treated as personal information. Capture only what has a business purpose and apply lifecycle and access controls.</div>
            <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-secondary mt-4">Return to dashboard</a>
        </div>

        <div class="zazu-card zazu-setting-card">
            <div class="zazu-card-title">Security & access</div>
            <div class="zazu-card-description">Authentication, active business context, server-side business isolation, role permissions and protected media are required before real customer or staff data is handled in production.</div>
            <span class="zazu-chip zazu-chip-warning mt-4">Important for live use</span>
        </div>
    </section>

    <section class="mt-5 rounded-xl border border-[var(--zazu-border)] bg-[var(--zazu-surface-2)] p-5">
        <div class="zazu-detail-label">Privacy engineering baseline</div>
        <div class="mt-2 max-w-3xl text-[11px] leading-5 text-[var(--zazu-muted)]">POPIA-oriented controls in the current foundation include minimal collection, optional photo fields, server-side validation and lifecycle preservation. Full compliance depends on the deployed business's actual processing purposes, lawful grounds, notices, retention rules, operators and security measures.</div>
    </section>
</x-app-layout>
