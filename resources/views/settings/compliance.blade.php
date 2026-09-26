<x-app-layout>
    <x-slot:title>Compliance centre</x-slot:title>
    <x-slot:heading>Compliance centre</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('settings.index') }}" class="zazu-btn zazu-btn-secondary">Business settings</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">South Africa · Business readiness</div>
            <h2 class="zazu-command-title">Keep the evidence ready before someone asks for it.</h2>
            <p class="zazu-command-copy">Zazu keeps tax, tender and operational compliance information in one place so business details can be reused when preparing invoices, tender packs and supporting records.</p>
        </div>
    </section>

    <section class="zazu-detail-grid">
        <div class="zazu-detail-stack">
            <section class="zazu-panel">
                <div class="zazu-eyebrow">Profile signals</div>
                <div class="zazu-panel-title mt-1">What Zazu thinks may apply</div>
                <div class="zazu-panel-copy">These prompts are guidance, not legal determinations. Confirm the requirements for the business activity and procurement opportunity.</div>
                <div class="zazu-list mt-4">
                    @foreach ($checklist as $item)
                        @if ($item['when'])
                            @php
                                $found = $documents->firstWhere('document_type', $item['type']);
                            @endphp
                            <div class="zazu-list-item">
                                <div class="zazu-list-main">
                                    <div class="zazu-list-title">{{ $item['label'] }}</div>
                                    <div class="zazu-list-meta">{{ $item['reason'] }}</div>
                                </div>
                                <div class="zazu-list-side">
                                    <span class="zazu-chip {{ $found ? 'zazu-chip-success' : 'zazu-chip-info' }}">{{ $found ? 'Recorded' : 'Add evidence' }}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="zazu-detail-stack">
            <section class="zazu-panel">
                <div class="zazu-eyebrow">Tax profile</div>
                <div class="zazu-panel-title mt-1">{{ $business->taxProfile?->vat_status === 'registered' ? 'VAT registered' : 'VAT not registered' }}</div>
                <div class="zazu-panel-copy">
                    {{ $business->taxProfile?->legal_name ?: $business->name }}
                    @if($business->taxProfile?->registration_number) · {{ $business->taxProfile->registration_number }} @endif
                    @if($business->taxProfile?->vat_number) · VAT {{ $business->taxProfile->vat_number }} @endif
                </div>
                <a href="{{ route('settings.index') }}" class="zazu-btn zazu-btn-ghost mt-4">Edit tax profile</a>
            </section>
        </aside>
    </section>

    <section class="zazu-card mt-5">
        <div class="zazu-card-header">
            <div>
                <div class="zazu-eyebrow">Evidence register</div>
                <div class="zazu-card-title mt-1">Documents and certificates</div>
            </div>
        </div>
        <div class="zazu-list">
            @forelse ($documents as $document)
                <div class="zazu-list-item">
                    <div class="zazu-list-main">
                        <div class="zazu-list-title">{{ $document->title }}</div>
                        <div class="zazu-list-meta">
                            {{ config('zazu.compliance.document_types.'.$document->document_type, $document->document_type) }}
                            @if($document->reference_number) · {{ $document->reference_number }} @endif
                            @if($document->expiry_date) · expires {{ $document->expiry_date->format('d M Y') }} @endif
                        </div>
                    </div>
                    <div class="zazu-list-side flex items-center gap-2">
                        <span class="zazu-chip {{ $document->status === 'current' ? 'zazu-chip-success' : ($document->status === 'expired' ? 'zazu-chip-danger' : 'zazu-chip-neutral') }}">{{ ucfirst(str_replace('_',' ', $document->status)) }}</span>
                        @if($document->storage_path)
                            <a href="{{ route('settings.compliance.download', $document) }}" class="zazu-btn zazu-btn-ghost">Open</a>
                        @endif
                        <form method="POST" action="{{ route('settings.compliance.destroy', $document) }}">
                            @csrf
                            @method('DELETE')
                            <button class="zazu-btn zazu-btn-ghost">Remove</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="zazu-empty">
                    <div class="zazu-empty-title">No compliance evidence recorded yet</div>
                    <p class="zazu-empty-copy">Start with the documents your business already has. Zazu will use the profile signals above to keep the register useful.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="zazu-card mt-5">
        <div class="zazu-card-header">
            <div>
                <div class="zazu-eyebrow">Add evidence</div>
                <div class="zazu-card-title mt-1">Record a certificate, report or supporting document</div>
            </div>
        </div>
        <form method="POST" action="{{ route('settings.compliance.store') }}" enctype="multipart/form-data" class="zazu-form-section">
            @csrf
            <div class="zazu-form-grid">
                <label class="zazu-field">
                    <span class="zazu-label">Document type <span class="zazu-required">*</span></span>
                    <select name="document_type" class="zazu-select" required>
                        @foreach(config('zazu.compliance.document_types') as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('document_type')<span class="zazu-field-error">{{ $message }}</span>@enderror
                </label>
                <label class="zazu-field">
                    <span class="zazu-label">Title <span class="zazu-required">*</span></span>
                    <input name="title" value="{{ old('title') }}" class="zazu-input" required placeholder="e.g. SARS TCS Good Standing">
                    @error('title')<span class="zazu-field-error">{{ $message }}</span>@enderror
                </label>
                <label class="zazu-field">
                    <span class="zazu-label">Reference number</span>
                    <input name="reference_number" value="{{ old('reference_number') }}" class="zazu-input">
                </label>
                <label class="zazu-field">
                    <span class="zazu-label">Status</span>
                    <select name="status" class="zazu-select">
                        <option value="current">Current</option>
                        <option value="pending">Pending</option>
                        <option value="expired">Expired</option>
                        <option value="not_applicable">Not applicable</option>
                    </select>
                </label>
                <label class="zazu-field">
                    <span class="zazu-label">Issue date</span>
                    <input type="date" name="issue_date" value="{{ old('issue_date') }}" class="zazu-input">
                </label>
                <label class="zazu-field">
                    <span class="zazu-label">Expiry date</span>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="zazu-input">
                </label>
                <label class="zazu-field zazu-field-wide">
                    <span class="zazu-label">Evidence file</span>
                    <input type="file" name="document" accept="application/pdf,image/jpeg,image/png,image/webp" class="zazu-input">
                    <span class="zazu-field-help">PDF or image, maximum 10 MB. Files are kept behind the authenticated business boundary.</span>
                </label>
                <label class="zazu-field zazu-field-wide">
                    <span class="zazu-label">Source / notes</span>
                    <textarea name="source_reference" rows="2" class="zazu-textarea" placeholder="Where it came from or which tender/procurement request it supports.">{{ old('source_reference') }}</textarea>
                </label>
                <label class="zazu-field zazu-field-wide">
                    <span class="zazu-label">Internal notes</span>
                    <textarea name="notes" rows="2" class="zazu-textarea">{{ old('notes') }}</textarea>
                </label>
                <label class="zazu-check-row zazu-field-wide">
                    <input type="checkbox" name="verified" value="1">
                    <span>Mark as verified by the business</span>
                </label>
            </div>
            <div class="zazu-actionbar">
                <a href="{{ route('settings.index') }}" class="zazu-btn zazu-btn-ghost">Back to settings</a>
                <button class="zazu-btn zazu-btn-primary">Save evidence</button>
            </div>
        </form>
    </section>

    <section class="zazu-panel mt-5">
        <div class="zazu-eyebrow">Important</div>
        <div class="zazu-panel-title mt-1">Zazu prepares evidence; it does not certify compliance.</div>
        <p class="zazu-panel-copy mt-2">Tax rules, municipal licensing, tender conditions and industry-specific obligations can change or depend on facts outside the system. Zazu should preserve the source and effective date of rules, surface missing evidence and never present a stored document as proof that a business is legally compliant today.</p>
    </section>
</x-app-layout>