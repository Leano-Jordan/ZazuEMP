<x-app-layout>
    <x-slot:title>Data export &amp; recovery</x-slot:title>
    <x-slot:heading>Data export &amp; recovery</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('reports.index') }}" class="zazu-btn zazu-btn-secondary">Reports</a>
        <a href="{{ route('settings.audit') }}" class="zazu-btn zazu-btn-ghost">Audit history</a>
    </x-slot:headerAction>

    @php
        $exportGroups = [
            'People &amp; work' => ['customers', 'customer_contacts', 'events'],
            'Catalogue &amp; assets' => ['business_capabilities', 'assets', 'asset_allocations'],
            'Event delivery' => ['event_requirements', 'event_preparation'],
            'Money &amp; tax' => ['quotes', 'quote_items', 'invoices', 'invoice_items', 'payments', 'expenses', 'event_costs', 'travel_costs', 'tax_rates'],
            'Purchasing &amp; stock' => ['suppliers', 'purchase_orders', 'purchase_order_items', 'purchase_order_receipts', 'inventory_items', 'inventory_movements'],
        ];
        $groupedExports = collect($exportGroups)->map(fn ($resources) => collect($exports)->filter(fn ($export) => in_array($export['resource'], $resources, true))->values());
    @endphp

    <section class="zazu-command-band zazu-export-intro">
        <div>
            <div class="zazu-eyebrow">Business data portability</div>
            <h2 class="zazu-command-title">Your records. Ready when you need them.</h2>
            <p class="zazu-command-copy">Export individual datasets for spreadsheets, or create one encrypted archive for a complete business handover or recovery. Every export is restricted to {{ $business->name }}.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">CSV datasets</div>
            <div class="zazu-command-meta-value">{{ count($exports) }}</div>
        </div>
    </section>

    <section class="zazu-export-archive" aria-labelledby="business-archive-heading">
        <div class="zazu-export-section-heading">
            <div>
                <div class="zazu-eyebrow">Full workspace backup</div>
                <h2 id="business-archive-heading">Complete business archive</h2>
                <p>One encrypted file containing linked operational records and private uploaded files.</p>
            </div>
            <span class="zazu-chip zazu-chip-neutral">ZAZU V2 · ENCRYPTED</span>
        </div>

        <div class="zazu-export-archive-grid">
            <article class="zazu-export-action">
                <div class="zazu-export-action-icon" aria-hidden="true">↓</div>
                <h3>Create an archive</h3>
                <p>Protect the archive with a unique passphrase. Keep that passphrase separately; Zazu cannot recover it if lost.</p>
                <form method="POST" action="{{ route('exports.archive.download') }}" class="zazu-export-form">
                    @csrf
                    <label class="zazu-field-label" for="archive-export-password">New archive passphrase
                        <input id="archive-export-password" name="archive_password" type="password" minlength="12" maxlength="128" autocomplete="new-password" required class="zazu-input mt-1 w-full">
                    </label>
                    <label class="zazu-field-label" for="archive-export-password-confirmation">Confirm passphrase
                        <input id="archive-export-password-confirmation" name="archive_password_confirmation" type="password" minlength="12" maxlength="128" autocomplete="new-password" required class="zazu-input mt-1 w-full">
                    </label>
                    @error('archive_password')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="zazu-btn zazu-btn-primary">Encrypt &amp; download archive</button>
                </form>
            </article>

            <article class="zazu-export-action zazu-export-restore">
                <div class="zazu-export-action-icon" aria-hidden="true">↥</div>
                <h3>Restore an archive</h3>
                <p>Validate and restore a Zazu business archive into a clean, empty workspace.</p>
                <form method="POST" action="{{ route('exports.archive.restore') }}" enctype="multipart/form-data" class="zazu-export-form">
                    @csrf
                    <label for="business-archive" class="zazu-field-label">Business archive file
                        <input id="business-archive" name="archive" type="file" accept=".zazu,application/zip" required class="zazu-input mt-1 w-full">
                    </label>
                    <label for="archive-restore-password" class="zazu-field-label">Archive passphrase <span class="font-normal opacity-70">(V2 only)</span>
                        <input id="archive-restore-password" name="archive_password" type="password" minlength="12" maxlength="128" autocomplete="off" class="zazu-input mt-1 w-full">
                    </label>
                    @error('archive')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('archive_password')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="zazu-btn zazu-btn-secondary">Validate &amp; restore</button>
                </form>
            </article>
        </div>

        <div class="zazu-export-safety">
            <strong>Restore safety boundary</strong>
            <span>Restore is allowed only into an empty, unpaired business workspace. Existing records and offline sync state are never overwritten or merged. Record IDs are remapped and business ownership is re-bound. Older V1 archives remain importable but are unencrypted.</span>
        </div>
        <p class="zazu-export-footnote">Passwords, user sessions, device pairing tokens, licence entitlements and the SARS TCS PIN are never included. Re-enter the TCS PIN after restoring. Limits: 100 MB upload / 250 MB expanded archive. Incomplete or unsafe archives are rejected without committing partial database changes.</p>
    </section>

    <section class="zazu-export-datasets" aria-labelledby="dataset-export-heading">
        <div class="zazu-export-section-heading">
            <div>
                <div class="zazu-eyebrow">Individual downloads</div>
                <h2 id="dataset-export-heading">Export a specific dataset</h2>
                <p>Compact CSV downloads for Excel, LibreOffice and other spreadsheet tools.</p>
            </div>
            <span class="zazu-chip zazu-chip-neutral">{{ count($exports) }} CSV files</span>
        </div>

        <div class="zazu-export-groups">
            @foreach ($groupedExports as $group => $items)
                @if ($items->isNotEmpty())
                    <details class="zazu-export-group">
                        <summary>
                            <span class="zazu-export-group-name">{{ $group }}</span>
                            <span class="zazu-export-group-count">{{ $items->count() }} datasets</span>
                            <span class="zazu-export-group-chevron" aria-hidden="true">⌄</span>
                        </summary>
                        <div class="zazu-export-list">
                            @foreach ($items as $export)
                                <div class="zazu-export-row">
                                    <div class="zazu-export-row-copy">
                                        <strong>{{ $export['label'] }}</strong>
                                        <span>{{ $export['description'] }}</span>
                                    </div>
                                    <a href="{{ route('exports.download', ['resource' => $export['resource']]) }}" class="zazu-btn zazu-btn-secondary">Download CSV</a>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endif
            @endforeach
        </div>
    </section>
</x-app-layout>
