<x-app-layout>
    <x-slot:title>Experience level</x-slot:title>
    <x-slot:heading>Choose your workspace level</x-slot:heading>

    <section class="zazu-page-intro">
        <div>
            <span class="zazu-eyebrow">Setup · Step 2</span>
            <h2>Choose how much of Zazu you want surfaced.</h2>
            <p>This is a presentation preference, not a permission level. Your role still controls what you can access and what you can change.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('onboarding.experience.store') }}" class="zazu-form">
        @csrf

        <div class="zazu-form-grid">
            @foreach($options as $key => $option)
                <label class="zazu-panel p-5 cursor-pointer">
                    <div class="flex items-start gap-3">
                        <input type="radio" name="experience_level" value="{{ $key }}" @checked(old('experience_level') === $key) class="mt-1" required>
                        <span>
                            <strong class="block">{{ $option['label'] }}</strong>
                            <span class="zazu-field-help block mt-2">{{ $option['description'] }}</span>
                        </span>
                    </div>
                </label>
            @endforeach
        </div>

        <div class="zazu-actionbar mt-6">
            <a href="{{ route('onboarding.index') }}" class="zazu-btn zazu-btn-secondary">Back to setup</a>
            <button type="submit" class="zazu-btn zazu-btn-primary">Continue setup</button>
        </div>
    </form>
</x-app-layout>
