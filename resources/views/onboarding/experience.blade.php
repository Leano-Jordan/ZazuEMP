<x-app-layout>
    <x-slot:title>Business profile</x-slot:title>
    <x-slot:heading>Your business</x-slot:heading>
    <section class="zazu-page-intro">
        <div>
            <span class="zazu-eyebrow">Step 1 · Understand your business</span>
            <h2>What kind of business do you run?</h2>
            <p>Select every activity that describes your business. Zazu uses this foundation to make the next setup steps relevant; you can combine event services, rentals, products and other work.</p>
            @if($isFounder)
                <p class="zazu-field-help mt-3" role="status">Founder access: all experience levels are available to this configured account. This does not change the business licence or customer entitlements.</p>
            @elseif($isTrial)
                <p class="zazu-field-help mt-3" role="status">Your 30-day Advanced trial ends {{ $trialEndsAt?->format('j M Y') }}. It moves to Basic Free at expiry unless you actively install a paid entitlement.</p>
            @else
                <p class="zazu-field-help mt-3" role="status">Workspace tier: <strong>{{ $maximumLevelLabel }}</strong>. You can select this tier or a lower experience.</p>
            @endif
        </div>
    </section>
    <form method="POST" action="{{ route('onboarding.experience.store') }}" class="zazu-form">
        @csrf
        <fieldset class="zazu-fieldset">
            <legend class="zazu-fieldset-legend">Your business activities</legend>
            <p class="zazu-fieldset-help">Choose as many as apply. A business can provide DJ and sound, catering, photography, decor, rentals and more.</p>
            @php($chosenFocuses = old('business_focuses', $selectedFocuses))
            @php($defaultPrimaryFocus = old('primary_niche', $selectedNiche ?? (count($chosenFocuses) === 1 ? $chosenFocuses[0] : 'mixed')))
            <input type="hidden" name="business_focuses_present" value="1">
            <div class="zazu-niche-grid">
                @foreach($nicheOptions as $key => $option)
                    @continue($key === 'mixed')
                    <label class="zazu-niche-card {{ in_array($key, $chosenFocuses, true) ? 'is-selected' : '' }}">
                        <span class="zazu-niche-card-main">
                            <input type="checkbox" name="business_focuses[]" value="{{ $key }}" @checked(in_array($key, $chosenFocuses, true))>
                            <span class="zazu-niche-card-copy">
                                <strong>{{ $option['label'] }}</strong>
                                <small>{{ $option['description'] }}</small>
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
            <label class="zazu-field mt-4">
                <span class="zazu-label">Which activity should Zazu prioritise?</span>
                <select name="primary_niche" class="zazu-select">
                    <option value="mixed" @selected($defaultPrimaryFocus === 'mixed')>Balanced view across my business</option>
                    @foreach($nicheOptions as $key => $option)
                        @continue($key === 'mixed')
                        <option value="{{ $key }}" @selected($defaultPrimaryFocus === $key)>{{ $option['label'] }}</option>
                    @endforeach
                </select>
                <span class="zazu-field-help">This affects guidance and starting suggestions, not what you are allowed to sell or manage.</span>
            </label>
        </fieldset>
        <details class="zazu-disclosure" {{ $selectedLevel ? '' : 'open' }}>
            <summary>
                <span><strong>How much of Zazu should be visible?</strong><small>Current: {{ $selectedLevel ? ucfirst($selectedLevel) : 'Not selected' }}</small></span>
                <span aria-hidden="true">+</span>
            </summary>
            <div class="zazu-disclosure-body">
                <p class="zazu-fieldset-help">Basic, Intermediate and Advanced are the three workspace payment tiers. Your selection controls presentation within the tier available to this business.</p>
                <div class="zazu-form-grid">
                    @foreach($options as $key => $option)
                        @php($isAvailable = in_array($key, $availableLevels, true))
                        <label class="zazu-panel p-5 {{ $isAvailable ? 'cursor-pointer' : 'opacity-60' }}">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="experience_level" value="{{ $key }}" @checked($isAvailable && old('experience_level', $selectedLevel) === $key) @disabled(!$isAvailable) class="mt-1" required>
                                <span>
                                    <strong class="block">{{ $option['label'] }}</strong>
                                    <span class="zazu-field-help block mt-2">{{ $option['description'] }}</span>
                                    @unless($isAvailable)
                                        <span class="zazu-field-help block mt-2">Requires an active {{ $option['label'] }} tier or above.</span>
                                    @endunless
                                </span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </details>
        <div class="zazu-actionbar mt-6">
            <a href="{{ route('onboarding.index') }}" class="zazu-btn zazu-btn-secondary">Back to setup</a>
            <button type="submit" class="zazu-btn zazu-btn-primary">Continue setup</button>
        </div>
    </form>
</x-app-layout>
