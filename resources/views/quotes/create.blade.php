<x-app-layout>
    <x-slot:title>Create quote · {{ $event->name }}</x-slot:title>
    <x-slot:heading>Create quote</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.show', $event) }}" class="zazu-btn zazu-btn-ghost">Job workspace</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · Commercial</div>
            <h2 class="zazu-command-title">Build draft quote v1</h2>
            <p class="zazu-command-copy">Your job services are already listed. Zazu fills in saved usual prices where you have them. Check the amounts, change anything needed, then save the quote.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('work.quotes.store', $event) }}">
        @csrf

        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Commercial inputs</div>
                        <div class="zazu-form-section-copy">Tax stays at zero in this foundation slice until the finance/tax configuration is implemented.</div>
                    </div>

                    <div class="zazu-form-grid">
                        <label class="zazu-field">
                            <span class="zazu-label">Currency</span>
                            <input name="currency" value="{{ old('currency', 'ZAR') }}" maxlength="3" class="zazu-input" required>
                            @error('currency')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Quote notes</span>
                            <textarea name="notes" rows="3" class="zazu-textarea">{{ old('notes') }}</textarea>
                            @error('notes')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                </section>

                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Quote lines</div>
                        <div class="zazu-form-section-copy">These values are copied into the quote snapshot when saved.</div>
                    </div>

                    <div class="zazu-card zazu-list">
                        @foreach ($event->requirements as $requirement)
                            <div class="zazu-list-item">
                                <div class="zazu-list-main">
                                    <div class="zazu-list-title">{{ $requirement->description }}</div>
                                    <div class="zazu-list-meta">
                                        {{ number_format((float) $requirement->quantity, 2) }} {{ $requirement->unit ?: 'units' }}
                                        · {{ $requirement->category ?: 'General' }}
                                        @if ($requirement->capability) · {{ $requirement->capability->name }} @endif
                                    </div>
                                </div>
                                <label class="w-32 shrink-0">
                                    <span class="zazu-label">Price</span>
                                    <input type="number" min="0" step="0.01" name="unit_price[{{ $requirement->id }}]" value="{{ old('unit_price.'.$requirement->id, $requirement->capability?->default_price ?? 0) }}" class="zazu-input" required>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    @error('unit_price')<span class="zazu-field-error mt-3">{{ $message }}</span>@enderror
                </section>

                <div class="zazu-actionbar">
                    <a href="{{ route('work.quotes.index', $event) }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                    <button class="zazu-btn zazu-btn-primary">Save quote v1</button>
                </div>
            </div>

            <aside class="zazu-form-aside">
                <div class="zazu-context-card">
                    <div class="zazu-context-title">Commercial position</div>
                    <div class="zazu-context-copy">This foundation establishes the quote identity, version and historical line snapshot before tax, travel and richer costing are attached.</div>

                    <div class="zazu-step-list">
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Requirements</div><div class="zazu-step-copy">Source record</div></div></div>
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Quote v1</div><div class="zazu-step-copy">Current commercial draft</div></div></div>
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Travel & costing</div><div class="zazu-step-copy">Later foundation</div></div></div>
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Customer acceptance</div><div class="zazu-step-copy">Later workflow</div></div></div>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</x-app-layout>
