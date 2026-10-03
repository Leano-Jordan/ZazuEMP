<x-app-layout>
    <x-slot:title>Workspace focus</x-slot:title>
    <x-slot:heading>Workspace focus</x-slot:heading>

    <section class="zazu-page-intro">
        <div>
            <span class="zazu-eyebrow">Setup · Workspace preference</span>
            <h2>What do you mainly provide?</h2>
            <p>Choose the work you want Zazu to emphasise first. This changes presentation and guidance, not permissions or what your business can use.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('onboarding.experience.store') }}" class="zazu-form">
        @csrf

        <fieldset class="zazu-fieldset">
            <legend class="zazu-fieldset-legend">Primary business focus</legend>
            <p class="zazu-fieldset-help">Pick the closest fit. You can change it later without losing any data.</p>
            @include('components.niche-focus-options', ['options' => $nicheOptions, 'selected' => $selectedNiche, 'name' => 'primary_niche', 'required' => true])
        </fieldset>

        <details class="zazu-disclosure" {{ $selectedLevel ? '' : 'open' }}>
            <summary>
                <span>
                    <strong>How much of Zazu should be visible?</strong>
                    <small>Current level: {{ $selectedLevel ? ucfirst($selectedLevel) : 'Not selected' }}</small>
                </span>
                <span aria-hidden="true">+</span>
            </summary>
            <div class="zazu-disclosure-body">
                <p class="zazu-fieldset-help">Basic, Intermediate and Advanced change how much detail and guidance is surfaced. They never change access permissions.</p>
                <div class="zazu-form-grid">
                    @foreach($options as $key => $option)
                        <label class="zazu-panel p-5 cursor-pointer">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="experience_level" value="{{ $key }}" @checked(old('experience_level', $selectedLevel) === $key) class="mt-1" required>
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
            <a href="{{ route('onboarding.index') }}" class="zazu-btn zazu-btn-secondary">Back to setup</a>
            <button type="submit" class="zazu-btn zazu-btn-primary">Continue setup</button>
        </div>
    </form>
</x-app-layout>
