<x-app-layout>
    <x-slot:title>Workspace focus</x-slot:title>
    <x-slot:heading>Workspace focus</x-slot:heading>
    <section class="zazu-page-intro">
        <div>
            <span class="zazu-eyebrow">Setup · Workspace tier and preference</span>
            <h2>What do you mainly provide?</h2>
            <p>Choose your main business focus and the level of detail you want to see. Your workspace tier controls the maximum experience; roles and permissions still control actions and records.</p>
            @if($isTrial)
                <p class="zazu-field-help mt-3" role="status">Your 30-day Advanced trial ends {{ $trialEndsAt?->format('j M Y') }}. It moves to Basic Free at expiry unless you actively install a paid entitlement.</p>
            @else
                <p class="zazu-field-help mt-3" role="status">Workspace tier: <strong>{{ $maximumLevelLabel }}</strong>. You can select this tier or a lower experience.</p>
            @endif
        </div>
    </section>
    <form method="POST" action="{{ route('onboarding.experience.store') }}" class="zazu-form">
        @csrf
        <fieldset class="zazu-fieldset">
            <legend class="zazu-fieldset-legend">Primary business focus</legend>
            <p class="zazu-fieldset-help">Pick the closest fit. You can change it later without losing data.</p>
            @include('components.niche-focus-options', ['options' => $nicheOptions, 'selected' => $selectedNiche, 'name' => 'primary_niche', 'required' => true])
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
