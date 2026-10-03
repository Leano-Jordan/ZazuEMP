<x-app-layout>
    <x-slot:title>Workspace preferences</x-slot:title>
    <x-slot:heading>Workspace preferences</x-slot:heading>

    <section class="zazu-page-intro">
        <div>
            <span class="zazu-eyebrow">Personal workspace preference</span>
            <h2>Keep Zazu at the level and focus that fit your work.</h2>
            <p>These settings change presentation, emphasis and guidance only. They do not change your role, permissions or access to business data.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('preferences.experience.update') }}" class="zazu-form">
        @csrf
        @method('PUT')

        <fieldset class="zazu-fieldset">
            <legend class="zazu-fieldset-legend">Primary business focus</legend>
            <p class="zazu-fieldset-help">This is a presentation lens for your workspace. Pick Sound & DJ, Catering & baking, Chairs & tents, or keep the shared view.</p>
            @include('components.niche-focus-options', ['options' => $nicheOptions, 'selected' => $primaryNiche, 'name' => 'primary_niche'])
        </fieldset>

        <details class="zazu-disclosure" open>
            <summary>
                <span>
                    <strong>How much of Zazu should be visible?</strong>
                    <small>Current level: {{ ucfirst($level) }}</small>
                </span>
                <span aria-hidden="true">−</span>
            </summary>
            <div class="zazu-disclosure-body">
                <div class="zazu-form-grid">
                    @foreach($options as $key => $option)
                        <label class="zazu-panel p-5 cursor-pointer">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="experience_level" value="{{ $key }}" @checked(old('experience_level', $level) === $key) class="mt-1">
                                <span>
                                    <strong class="block">{{ $option['label'] }}</strong>
                                    <span class="zazu-field-help block mt-2">{{ $option['description'] }}</span>
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
