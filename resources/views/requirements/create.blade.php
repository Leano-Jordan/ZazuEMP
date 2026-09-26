<x-app-layout>
    <x-slot:title>Add requirement · {{ $event->name }}</x-slot:title>
    <x-slot:heading>Add requirement</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.requirements.index', $event) }}" class="zazu-btn zazu-btn-ghost">← Requirements</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · Requirements</div>
            <h2 class="zazu-command-title">Add a requirement</h2>
            <p class="zazu-command-copy">Record what this work needs. Select a reusable capability when one matches.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('work.requirements.store', $event) }}">
        @csrf

        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Requirement</div>
                        <div class="zazu-form-section-copy">This requirement belongs to this work. The catalogue remains reusable.</div>
                    </div>

                    <div class="zazu-form-grid">
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Reusable capability <span class="font-normal text-[var(--zazu-faint)]">(optional)</span></span>
                            <select name="capability_id" class="zazu-select">
                                <option value="">No catalogue capability</option>
                                @foreach ($capabilities as $capability)
                                    <option value="{{ $capability->id }}" @selected(old('capability_id') == $capability->id)>
                                        {{ $capability->name }}{{ $capability->category ? ' · '.$capability->category : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('capability_id')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Requirement</span>
                            <input name="description" value="{{ old('description') }}" required class="zazu-input" placeholder="e.g. 100 white folding chairs">
                            @error('description')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Category</span>
                            <input name="category" value="{{ old('category') }}" class="zazu-input" placeholder="Furniture, catering, decor...">
                            @error('category')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Quantity</span>
                            <input type="number" step="0.01" min="0.01" name="quantity" value="{{ old('quantity', 1) }}" required class="zazu-input">
                            @error('quantity')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field">
                            <span class="zazu-label">Unit</span>
                            <input name="unit" value="{{ old('unit') }}" class="zazu-input" placeholder="chairs, guests, hours...">
                            @error('unit')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Notes</span>
                            <textarea name="notes" rows="5" class="zazu-textarea" placeholder="Specification, colour, timing or other delivery detail">{{ old('notes') }}</textarea>
                            @error('notes')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                </section>

                <div class="zazu-actionbar">
                    <a href="{{ route('work.requirements.index', $event) }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                    <button class="zazu-btn zazu-btn-primary">Save requirement</button>
                </div>
            </div>

            <aside class="zazu-form-aside">
                <div class="zazu-context-card">
                    <div class="zazu-context-title">Workflow position</div>
                    <div class="zazu-context-copy">Requirements describe the work before the commercial layer prices it.</div>

                    <div class="zazu-step-list">
                        <div class="zazu-step"><span class="zazu-step-dot current"></span><div><div class="zazu-step-title">Requirements</div><div class="zazu-step-copy">What must be delivered</div></div></div>
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Quote</div><div class="zazu-step-copy">Commercial offer</div></div></div>
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Preparation</div><div class="zazu-step-copy">Buying and readiness</div></div></div>
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Execution</div><div class="zazu-step-copy">Delivery and accountability</div></div></div>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</x-app-layout>
