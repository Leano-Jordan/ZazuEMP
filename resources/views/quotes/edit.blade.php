<x-app-layout>
    <x-slot:title>Edit quote · {{ $quote->reference }}</x-slot:title>
    <x-slot:heading>Edit quote v{{ $version->version }}</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('quotes.show', $quote) }}" class="zazu-btn zazu-btn-ghost">Back to quote</a>
        <a href="{{ route('work.show', $quote->event) }}" class="zazu-btn zazu-btn-secondary">Job workspace</a>
    </x-slot:headerAction>

    @php($itemsByRequirement = $version->items->keyBy('event_requirement_id'))

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $quote->reference }} · Revision v{{ $version->version }}</div>
            <h2 class="zazu-command-title">Review the current services and prices</h2>
            <p class="zazu-command-copy">Zazu carries forward prices from the previous revision where possible. New services need a price before you save.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Currency</div>
            <div class="zazu-command-meta-value">{{ $quote->currency }}</div>
        </div>
    </section>

    <form method="POST" action="{{ route('quotes.versions.update', [$quote, $version]) }}">
        @csrf
        @method('PUT')
        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Commercial inputs</div>
                        <div class="zazu-form-section-copy">The quote currency remains {{ $quote->currency }} for this revision.</div>
                    </div>
                    <div class="zazu-form-grid">
                        <div class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Currency</span>
                            <div class="zazu-input flex items-center font-bold">{{ $quote->currency }}</div>
                        </div>
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Quote notes</span>
                            <textarea name="notes" rows="3" class="zazu-textarea">{{ old('notes', $version->notes) }}</textarea>
                            @error('notes')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                </section>

                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Current Work services</div>
                        <div class="zazu-form-section-copy">This draft records the current Work services and prices when you save it. Earlier quote revisions remain unchanged.</div>
                    </div>
                    <div class="zazu-card zazu-list">
                        @foreach ($quote->event->requirements as $requirement)
                            @php
                                $item = $itemsByRequirement->get($requirement->id);
                                $defaultPrice = $item?->unit_price;

                                if (!$item && $requirement->capability?->default_price !== null && ($requirement->capability->currency ?? $quote->currency) === $quote->currency) {
                                    $defaultPrice = number_format((float) $requirement->capability->default_price, 2, '.', '');
                                }
                            @endphp
                            <div class="zazu-list-item">
                                <div class="zazu-list-main">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="zazu-list-title">{{ $requirement->description }}</span>
                                        @if (!$item)
                                            <span class="zazu-chip zazu-chip-info">New</span>
                                        @endif
                                    </div>
                                    <div class="zazu-list-meta">
                                        {{ number_format((float) $requirement->quantity, 2) }} {{ $requirement->unit ?: 'units' }}
                                        · {{ $requirement->category ?: 'General' }}
                                        @if ($requirement->capability) · {{ $requirement->capability->name }} @endif
                                    </div>
                                </div>
                                <label class="w-40 shrink-0">
                                    <span class="zazu-label">Price ({{ $quote->currency }})</span>
                                    <input type="number" min="0" step="0.01" inputmode="decimal" name="unit_price[{{ $requirement->id }}]" value="{{ old('unit_price.'.$requirement->id, $defaultPrice) }}" class="zazu-input" required>
                                    @if ($item)
                                        <span class="zazu-field-help">Carried forward from the previous revision.</span>
                                    @elseif ($defaultPrice !== null)
                                        <span class="zazu-field-help">Saved catalogue price reused.</span>
                                    @else
                                        <span class="zazu-field-help">Enter the price for this new service.</span>
                                    @endif
                                    @error('unit_price.'.$requirement->id)<span class="zazu-field-error">{{ $message }}</span>@enderror
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('unit_price')<span class="zazu-field-error mt-3">{{ $message }}</span>@enderror
                </section>

                <div class="zazu-actionbar">
                    <a href="{{ route('quotes.show', $quote) }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                    <button class="zazu-btn zazu-btn-primary">Save quote v{{ $version->version }}</button>
                </div>
            </div>
            <aside class="zazu-form-aside">
                <div class="zazu-context-card zazu-next-card">
                    <div class="zazu-context-title">Snapshot boundary</div>
                    <div class="zazu-context-copy">Save this revision to record the current Work services, quantities and prices without rewriting earlier revisions.</div>
                </div>
            </aside>
        </div>
    </form>
</x-app-layout>