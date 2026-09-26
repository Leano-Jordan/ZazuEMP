@props([
    'eyebrow',
    'title',
    'description',
])

<x-app-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:heading>{{ $title }}</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-ghost">← Dashboard</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">{{ $eyebrow }}</div>
            <h2 class="zazu-command-title">{{ $title }}</h2>
            <p class="zazu-command-copy">{{ $description }}</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Stage</div>
            <div class="zazu-command-meta-value">Skeleton</div>
        </div>
    </section>

    {{ $slot }}
</x-app-layout>
