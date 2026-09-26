<x-app-layout>
    <x-slot:title>Add cost · {{ $event->name }}</x-slot:title>
    <x-slot:heading>Add cost</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.show', $event) }}" class="zazu-btn zazu-btn-ghost">Job workspace</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · Planning</div>
            <h2 class="zazu-command-title">Record an operating cost</h2>
            <p class="zazu-command-copy">Enter the expected amount now. Add the actual amount once the cost is incurred.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('work.costs.store', $event) }}">
        @csrf
        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Cost details</div>
                        <div class="zazu-form-section-copy">This is an operational cost record, not a payment transaction.</div>
                    </div>
                    <div class="zazu-form-grid">
                        <label class="zazu-field">
                            <span class="zazu-label">Category</span>
                            <input name="category" value="{{ old('category') }}" class="zazu-input" placeholder="Food, transport, venue..." required>
                            @error('category')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Description</span>
                            <input name="description" value="{{ old('description') }}" class="zazu-input" required>
                            @error('description')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Currency</span>
                            <input name="currency" value="{{ old('currency', 'ZAR') }}" maxlength="3" class="zazu-input" required>
                            @error('currency')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Projected amount</span>
                            <input type="number" inputmode="decimal" min="0" step="0.01" name="projected_amount" value="{{ old('projected_amount') }}" class="zazu-input" required>
                            @error('projected_amount')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Actual amount</span>
                            <input type="number" inputmode="decimal" min="0" step="0.01" name="actual_amount" value="{{ old('actual_amount') }}" class="zazu-input">
                            @error('actual_amount')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Status</span>
                            <select name="status" class="zazu-input" required>
                                @foreach (['planned' => 'Planned', 'incurred' => 'Incurred', 'cancelled' => 'Cancelled'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', 'planned') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Notes</span>
                            <textarea name="notes" rows="4" class="zazu-textarea">{{ old('notes') }}</textarea>
                            @error('notes')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                </section>
                <div class="zazu-actionbar">
                    <a href="{{ route('work.costs.index', $event) }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                    <button class="zazu-btn zazu-btn-primary">Save cost</button>
                </div>
            </div>
            <aside class="zazu-form-aside">
                <div class="zazu-context-card">
                    <div class="zazu-context-title">Cost path</div>
                    <div class="zazu-context-copy">Expected → incurred → compared against the original projection.</div>
                </div>
            </aside>
        </div>
    </form>
</x-app-layout>