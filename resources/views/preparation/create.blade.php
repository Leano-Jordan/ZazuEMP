<x-app-layout>
    <x-slot:title>Add preparation item · {{ $event->name }}</x-slot:title>
    <x-slot:heading>Add preparation item</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.preparation.index', $event) }}" class="zazu-btn zazu-btn-ghost">← Preparation</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · Readiness</div>
            <h2 class="zazu-command-title">Add a readiness item</h2>
            <p class="zazu-command-copy">Record one concrete thing that must be ready before this work happens.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('work.preparation.store', $event) }}">
        @csrf
        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Readiness detail</div>
                        <div class="zazu-form-section-copy">Keep the item specific enough that someone can verify it is ready.</div>
                    </div>
                    <div class="zazu-form-grid">
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Item</span>
                            <input name="title" value="{{ old('title') }}" class="zazu-input" placeholder="Pack 80 chairs" required>
                            @error('title')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Category</span>
                            <input name="category" value="{{ old('category') }}" class="zazu-input" placeholder="Equipment, food, staff...">
                            @error('category')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Quantity</span>
                            <input type="number" inputmode="decimal" min="0" step="0.01" name="quantity" value="{{ old('quantity') }}" class="zazu-input">
                            @error('quantity')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Unit</span>
                            <input name="unit" value="{{ old('unit') }}" class="zazu-input" placeholder="chairs, trays, boxes...">
                            @error('unit')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Due date</span>
                            <input type="date" name="due_date" value="{{ old('due_date') }}" class="zazu-input">
                            @error('due_date')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Status</span>
                            <select name="status" class="zazu-input" required>
                                @foreach (['open' => 'Open', 'blocked' => 'Blocked', 'ready' => 'Ready'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', 'open') === $value)>{{ $label }}</option>
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
                    <a href="{{ route('work.preparation.index', $event) }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                    <button class="zazu-btn zazu-btn-primary">Save preparation item</button>
                </div>
            </div>
            <aside class="zazu-form-aside">
                <div class="zazu-context-card">
                    <div class="zazu-context-title">Readiness path</div>
                    <div class="zazu-context-copy">Open → prepare → verify → ready.</div>
                </div>
            </aside>
        </div>
    </form>
</x-app-layout>