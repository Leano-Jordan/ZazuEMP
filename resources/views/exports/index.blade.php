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
            <span class="zazu-chip zazu-chip-neutral">ZAZU ARCHIVE V1</span>
        </div>
        <div class="flex flex-wrap gap-3 mt-4">
            <a href="{{ route('exports.archive.download') }}" class="zazu-btn zazu-btn-primary">Download business archive</a>
        </div>
        <div class="zazu-detail-row mt-4">
            <div class="zazu-detail-label">Restore boundary</div>
            <div class="zazu-detail-value">Restore is allowed only into an empty, unpaired business workspace. Existing records and offline sync state are never overwritten or merged. Record IDs are remapped and business ownership is re-bound to this workspace.</div>
        </div>
        <form method="POST" action="{{ route('exports.archive.restore') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
            @csrf
            <label for="business-archive" class="zazu-field-label">Restore a Zazu business archive (.zazu)</label>
            <input id="business-archive" name="archive" type="file" accept=".zazu,application/zip" required class="zazu-input w-full">
            @error('archive')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            <button type="submit" class="zazu-btn zazu-btn-secondary">Validate and restore archive</button>
        </form>
        <p class="zazu-panel-copy mt-3">Restore limit: 100 MB upload / 250 MB expanded archive. Missing files, unsupported versions, unresolved relationships or a non-empty target stop the restore without committing partial database changes.</p>
    </section>
</x-app-layout>
