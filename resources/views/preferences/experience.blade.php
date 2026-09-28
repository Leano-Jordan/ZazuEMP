<x-app-layout>
    <x-slot:title>Experience level</x-slot:title>
    <x-slot:heading>Workspace experience</x-slot:heading>

    <section class="zazu-page-intro">
        <div>
            <span class="zazu-eyebrow">Personal workspace preference</span>
            <h2>Choose how much of Zazu is surfaced at once.</h2>
            <p>This changes presentation and guidance only. It does not change your role, permissions or access to business data.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('preferences.experience.update') }}" class="zazu-form">
        @csrf
        @method('PUT')

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

        <div class="zazu-actionbar mt-6">
            <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-secondary">Back to dashboard</a>
            <button type="submit" class="zazu-btn zazu-btn-primary">Save experience level</button>
        </div>
    </form>
</x-app-layout>
