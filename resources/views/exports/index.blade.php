<x-app-layout>
    <x-slot:title>Export business data</x-slot:title>
    <x-slot:heading>Export business data</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('reports.index') }}" class="zazu-btn zazu-btn-secondary">Reports</a>
        <a href="{{ route('settings.audit') }}" class="zazu-btn zazu-btn-ghost">Audit history</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Business data portability</div>
            <h2 class="zazu-command-title">Take your records with you.</h2>
            <p class="zazu-command-copy">Download spreadsheet-ready CSV files for the records in {{ $business->name }}. Each file is limited to this business workspace and can be opened in Excel or LibreOffice.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Available exports</div>
            <div class="zazu-command-meta-value">{{ count($exports) }}</div>
        </div>
    </section>

    <section class="zazu-detail-grid">
        @foreach ($exports as $export)
            <article class="zazu-panel">
                <div class="zazu-panel-head">
                    <div>
                        <div class="zazu-panel-title">{{ $export['label'] }}</div>
                        <div class="zazu-panel-copy mt-1">{{ $export['description'] }}</div>
                    </div>
                    <span class="zazu-chip zazu-chip-neutral">CSV</span>
                </div>
                <div class="zazu-detail-row mt-4">
                    <div class="zazu-detail-label">Scope</div>
                    <div class="zazu-detail-value">{{ $export['scope'] }}</div>
                </div>
                <a href="{{ route('exports.download', ['resource' => $export['resource']]) }}" class="zazu-btn zazu-btn-secondary mt-4">Download {{ $export['label'] }}</a>
            </article>
        @endforeach
    </section>

    <section class="zazu-panel mt-5">
        <div class="zazu-panel-head">
            <div>
                <div class="zazu-panel-title">Complete business archive</div>
                <div class="zazu-panel-copy mt-1">Package this workspace's linked operational records and private uploaded files into one portable archive. Account passwords, user sessions, device pairing tokens, licence entitlements and the SARS TCS PIN are never included. Re-enter the TCS PIN after restoring.</div>
            </div>
            <span class="zazu-chip zazu-chip-neutral">ZAZU ARCHIVE V2 · ENCRYPTED</span>
        </div>
        <p class="zazu-panel-copy mt-3"><strong>Security:</strong> new business archives are encrypted with XChaCha20-Poly1305 and an Argon2id-derived key. Choose a strong, unique passphrase and keep it separately from the archive. Zazu cannot recover a forgotten passphrase. Older V1 archives remain importable but are unencrypted.</p>
        <form method="POST" action="{{ route('exports.archive.download') }}" class="mt-4 grid gap-3 sm:grid-cols-2">
            @csrf
            <label class="zazu-field-label" for="archive-export-password">New archive passphrase
                <input id="archive-export-password" name="archive_password" type="password" minlength="12" maxlength="128" autocomplete="new-password" required class="zazu-input mt-1 w-full">
            </label>
            <label class="zazu-field-label" for="archive-export-password-confirmation">Confirm passphrase
                <input id="archive-export-password-confirmation" name="archive_password_confirmation" type="password" minlength="12" maxlength="128" autocomplete="new-password" required class="zazu-input mt-1 w-full">
            </label>
            @error('archive_password')
                <p class="text-sm text-red-600 sm:col-span-2">{{ $message }}</p>
            @enderror
            <div class="sm:col-span-2">
                <button type="submit" class="zazu-btn zazu-btn-primary">Encrypt and download business archive</button>
            </div>
        </form>
        <div class="zazu-detail-row mt-4">
            <div class="zazu-detail-label">Restore boundary</div>
            <div class="zazu-detail-value">Restore is allowed only into an empty, unpaired business workspace. Existing records and offline sync state are never overwritten or merged. Record IDs are remapped and business ownership is re-bound to this workspace.</div>
        </div>
        <form method="POST" action="{{ route('exports.archive.restore') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
            @csrf
            <label for="business-archive" class="zazu-field-label">Restore a Zazu business archive (.zazu)</label>
            <input id="business-archive" name="archive" type="file" accept=".zazu,application/zip" required class="zazu-input w-full">
            <label for="archive-restore-password" class="zazu-field-label">Archive passphrase <span class="font-normal opacity-70">(required for encrypted V2 archives)</span></label>
            <input id="archive-restore-password" name="archive_password" type="password" minlength="12" maxlength="128" autocomplete="off" class="zazu-input w-full">
            @error('archive')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('archive_password')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            <button type="submit" class="zazu-btn zazu-btn-secondary">Validate and restore archive</button>
        </form>
        <p class="zazu-panel-copy mt-3">Restore limit: 100 MB upload / 250 MB expanded archive. Missing files, unsupported versions, unresolved relationships or a non-empty target stop the restore without committing partial database changes.</p>
    </section>
</x-app-layout>
