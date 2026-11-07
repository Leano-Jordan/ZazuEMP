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
        <div class="zazu-panel-title">Export boundary</div>
        <div class="zazu-panel-copy mt-1">Exports contain structured business records, not original uploaded attachments or a restorable database image. Downloads are private and recorded in the business audit history.</div>
    </section>
</x-app-layout>
