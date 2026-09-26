<x-app-layout>
    <x-slot:title>Add travel calculation · {{ $event->name }}</x-slot:title>
    <x-slot:heading>Travel & Costing</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.travel.index', $event) }}" class="zazu-btn zazu-btn-ghost">Job workspace</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $event->reference }} · Travel</div>
            <h2 class="zazu-command-title">Capture route costing evidence</h2>
            <p class="zazu-command-copy">This calculation is stored as its own record. The saved result can be reproduced from the captured distance, fuel and vehicle assumptions.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('work.travel.store', $event) }}">
        @csrf

        <div class="zazu-editor">
            <div class="zazu-form-main">
                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Route</div>
                        <div class="zazu-form-section-copy">Manual routing is the current source. The provider field keeps this boundary replaceable later.</div>
                    </div>

                    <div class="zazu-form-grid">
                        <label class="zazu-field">
                            <span class="zazu-label">Currency</span>
                            <input name="currency" value="{{ old('currency', 'ZAR') }}" maxlength="3" class="zazu-input" required>
                            @error('currency')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Route label</span>
                            <input name="route_label" value="{{ old('route_label', 'Route A') }}" class="zazu-input" required>
                            @error('route_label')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Provider</span>
                            <input name="provider" value="{{ old('provider', 'manual') }}" class="zazu-input" required>
                            @error('provider')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Origin</span>
                            <input name="origin" value="{{ old('origin') }}" class="zazu-input" required>
                            @error('origin')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Destination</span>
                            <input name="destination" value="{{ old('destination', $event->event_address) }}" class="zazu-input" required>
                            @error('destination')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">One-way distance (km)</span>
                            <input type="number" step="0.01" min="0.01" name="distance_km" value="{{ old('distance_km') }}" class="zazu-input" required>
                            @error('distance_km')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Travel time (minutes)</span>
                            <input type="number" min="0" name="travel_time_minutes" value="{{ old('travel_time_minutes') }}" class="zazu-input">
                            @error('travel_time_minutes')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                </section>

                <section class="zazu-form-section">
                    <div class="zazu-form-section-head">
                        <div class="zazu-form-section-title">Cost assumptions</div>
                        <div class="zazu-form-section-copy">These values are stored with the calculation so later changes do not rewrite this historical result.</div>
                    </div>

                    <div class="zazu-form-grid">
                        <label class="zazu-field">
                            <span class="zazu-label">Fuel price per litre</span>
                            <input type="number" step="0.01" min="0.01" name="fuel_price_per_litre" value="{{ old('fuel_price_per_litre') }}" class="zazu-input" required>
                            @error('fuel_price_per_litre')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Vehicle consumption (L/100km)</span>
                            <input type="number" step="0.01" min="0.01" name="vehicle_consumption_l_per_100km" value="{{ old('vehicle_consumption_l_per_100km', 10) }}" class="zazu-input" required>
                            @error('vehicle_consumption_l_per_100km')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-field">
                            <span class="zazu-label">Customer rate per km</span>
                            <input type="number" step="0.01" min="0" name="customer_rate_per_km" value="{{ old('customer_rate_per_km') }}" class="zazu-input" required>
                            @error('customer_rate_per_km')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="zazu-check-row">
                            <input type="checkbox" name="round_trip" value="1" @checked(old('round_trip', true))>
                            <span>
                                <span class="zazu-label mb-1">Round trip</span>
                                <span class="zazu-panel-copy block">Multiply the captured one-way distance by two for fuel and customer charge.</span>
                            </span>
                        </label>
                        <label class="zazu-field zazu-field-wide">
                            <span class="zazu-label">Notes</span>
                            <textarea name="notes" rows="4" class="zazu-textarea" placeholder="Route notes or evidence reference">{{ old('notes') }}</textarea>
                            @error('notes')<span class="zazu-field-error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                </section>

                <div class="zazu-actionbar">
                    <a href="{{ route('work.travel.index', $event) }}" class="zazu-btn zazu-btn-ghost">Cancel</a>
                    <button class="zazu-btn zazu-btn-primary">Save travel calculation</button>
                </div>
            </div>

            <aside class="zazu-form-aside">
                <div class="zazu-context-card">
                    <div class="zazu-context-title">Calculation path</div>
                    <div class="zazu-context-copy">Distance → total route → fuel litres → fuel cost → customer charge.</div>
                    <div class="zazu-step-list">
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Route</div><div class="zazu-step-copy">Source inputs</div></div></div>
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Fuel</div><div class="zazu-step-copy">Operating cost</div></div></div>
                        <div class="zazu-step"><span class="zazu-step-dot"></span><div><div class="zazu-step-title">Customer charge</div><div class="zazu-step-copy">Commercial value</div></div></div>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</x-app-layout>
