<x-app-layout>
    <x-slot:title>Workspace preferences</x-slot:title>
    <x-slot:heading>Workspace preferences</x-slot:heading>

    <section class="zazu-page-intro">
        <div>
            <span class="zazu-eyebrow">Workspace tier and personal preference</span>
            <h2>Choose how much of Zazu you want in view.</h2>
            <p>Your workspace tier sets the highest available experience. Each person chooses a presentation level within that ceiling. This preference never changes roles, permissions or access to existing business records.</p>
            @if($isTrial)
                <p class="zazu-field-help mt-3" role="status">Your 30-day Advanced trial ends {{ $trialEndsAt?->format('j M Y') }}. At expiry, your workspace continues on Basic Free unless an active paid entitlement is installed. No payment starts automatically.</p>
            @else
                <p class="zazu-field-help mt-3" role="status">Workspace tier: <strong>{{ $maximumLevelLabel }}</strong>. Higher experience levels require an active matching entitlement.</p>
            @endif
        </div>
    </section>

    <form method="POST" action="{{ route('preferences.experience.update') }}" class="zazu-form">
        @csrf
        @method('PUT')
        <fieldset class="zazu-fieldset">
            <legend class="zazu-fieldset-legend">Primary business focus</legend>
            <p class="zazu-fieldset-help">This is a presentation lens. Choose Sound & DJ, Catering & baking, Chairs & tents, or the shared view.</p>
            @include('components.niche-focus-options', ['options' => $nicheOptions, 'selected' => $primaryNiche, 'name' => 'primary_niche'])
        </fieldset>
        <fieldset class="zazu-fieldset">
            <legend class="zazu-fieldset-legend">Calendar dates that matter to you</legend>
            <p class="zazu-fieldset-help">South African public holidays stay visible. Optional faith and cultural dates can be muted without affecting jobs or schedules.</p>
            <div class="zazu-form-grid">
                @foreach($holidayCategories as $key => $label)
                    <label class="zazu-panel p-5 cursor-pointer">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="holiday_categories[]" value="{{ $key }}" @checked($holidayPreferences[$key] ?? true) class="mt-1" @disabled($key === 'public')>
                            <span>
                                <strong class="block">{{ $label }}</strong>
                                @if($key === 'public')
                                    <span class="zazu-field-help block mt-2">Always shown as an official South African public holiday.</span>
                                @else
                                    <span class="zazu-field-help block mt-2">Show these dates in the calendar.</span>
                                @endif
                            </span>
                        </div>
                    </label>
                @endforeach
            </div>
            <input type="hidden" name="holiday_categories[]" value="public">
        </fieldset>
        <details class="zazu-disclosure" open>
            <summary>
                <span><strong>Experience level</strong><small>Selected: {{ ucfirst($level) }} · Workspace tier: {{ $maximumLevelLabel }}</small></span>
                <span aria-hidden="true">−</span>
            </summary>
            <div class="zazu-disclosure-body">
                <div class="zazu-form-grid">
                    @foreach($options as $key => $option)
                        @php($isAvailable = in_array($key, $availableLevels, true))
                        <label class="zazu-panel p-5 {{ $isAvailable ? 'cursor-pointer' : 'opacity-60' }}">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="experience_level" value="{{ $key }}" @checked($isAvailable && old('experience_level', $level) === $key) @disabled(!$isAvailable) class="mt-1">
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
            <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-secondary">Back to dashboard</a>
            <button type="submit" class="zazu-btn zazu-btn-primary">Save workspace preferences</button>
        </div>
    </form>
</x-app-layout>
