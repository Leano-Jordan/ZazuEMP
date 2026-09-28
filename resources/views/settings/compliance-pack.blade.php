<x-app-layout>
    <x-slot:title>Business compliance pack</x-slot:title>
    <x-slot:heading>Business compliance pack</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('settings.compliance') }}" class="zazu-btn zazu-btn-secondary print-hide">Compliance centre</a>
        <button type="button" class="zazu-btn zazu-btn-primary print-hide" data-zazu-print>Print / Save as PDF</button>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Prepared by Zazu · {{ $generatedAt->format('d M Y, H:i') }}</div>
            <h2 class="zazu-command-title">{{ $business->taxProfile?->legal_name ?: $business->name }}</h2>
            <p class="zazu-command-copy">A reusable business profile and evidence index for SARS, tenders, supplier onboarding and applicable local requirements.</p>
        </div>
    </section>

    <section class="zazu-detail-grid">
        <div class="zazu-detail-stack">
            <section class="zazu-panel">
                <div class="zazu-eyebrow">Business identity</div>
                <div class="zazu-detail-rows mt-3">
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Trading name</div><div class="zazu-detail-value">{{ $business->taxProfile?->trading_name ?: $business->name }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Registration</div><div class="zazu-detail-value">{{ $business->taxProfile?->registration_type ?: 'Not recorded' }} · {{ $business->taxProfile?->registration_number ?: 'Not recorded' }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Address</div><div class="zazu-detail-value">{{ $business->address ?: 'Not recorded' }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Contact</div><div class="zazu-detail-value">{{ $business->email ?: 'No email' }} · {{ $business->phone ?: 'No phone' }}</div></div>
                </div>
            </section>

            <section class="zazu-panel">
                <div class="zazu-eyebrow">Tax profile</div>
                <div class="zazu-detail-rows mt-3">
                    <div class="zazu-detail-row"><div class="zazu-detail-label">Income tax</div><div class="zazu-detail-value">{{ $business->taxProfile?->income_tax_number ?: 'Not recorded' }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">VAT status</div><div class="zazu-detail-value">{{ config('zazu.tax.vat_statuses.'.$business->taxProfile?->vat_status, 'Not configured') }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">VAT number</div><div class="zazu-detail-value">{{ $business->taxProfile?->vat_number ?: 'Not recorded' }}</div></div>
                    <div class="zazu-detail-row"><div class="zazu-detail-label">PAYE / UIF / SDL</div><div class="zazu-detail-value">{{ $business->taxProfile?->paye_number ?: '—' }} / {{ $business->taxProfile?->uif_number ?: '—' }} / {{ $business->taxProfile?->sdl_number ?: '—' }}</div></div>
                </div>
            </section>

            <section class="zazu-card">
                <div class="zazu-card-header">
                    <div>
                        <div class="zazu-eyebrow">Evidence index</div>
                        <div class="zazu-card-title mt-1">Documents recorded in Zazu</div>
                    </div>
                </div>
                <div class="zazu-list">
                    @forelse($documents as $document)
                        <div class="zazu-list-item">
                            <div class="zazu-list-main">
                                <div class="zazu-list-title">{{ $document->title }}</div>
                                <div class="zazu-list-meta">
                                    {{ config('zazu.compliance.document_types.'.$document->document_type, $document->document_type) }}
                                    @if($document->reference_number) · {{ $document->reference_number }} @endif
                                    @if($document->expiry_date) · expires {{ $document->expiry_date->format('d M Y') }} @endif
                                </div>
                            </div>
                            <div class="zazu-list-side">
                                <span class="zazu-chip {{ $document->status === 'current' ? 'zazu-chip-success' : ($document->status === 'expired' ? 'zazu-chip-danger' : 'zazu-chip-neutral') }}">{{ ucfirst(str_replace('_',' ', $document->status)) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="zazu-empty">No supporting documents have been recorded.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="zazu-detail-stack">
            <section class="zazu-context-card">
                <div class="zazu-context-title">Use this pack as the starting point</div>
                <div class="zazu-context-copy">Official authorities and tender issuers can impose requirements that depend on the business, activity, municipality and opportunity. Add the exact tender-specific forms and supporting evidence requested for each submission.</div>
            </section>

            <section class="zazu-context-card">
                <div class="zazu-context-title">SARS TCS</div>
                <div class="zazu-context-copy">{{ $business->taxProfile?->tcs_pin_expires_at ? 'PIN expiry: '.$business->taxProfile->tcs_pin_expires_at->format('d M Y') : 'No TCS PIN expiry recorded.' }}</div>
                <div class="zazu-context-copy mt-2">SARS uses Good Standing for TCS requests; the PIN authorises a third party to verify the current status at the time of verification.</div>
            </section>

            <section class="zazu-context-card">
                <div class="zazu-context-title">Integrity boundary</div>
                <div class="zazu-context-copy">This is a preparation and recordkeeping pack. It is not an official SARS, CIPC, CSD or municipal form, and it does not certify legal compliance.</div>
            </section>
        </aside>
    </section>
</x-app-layout>
