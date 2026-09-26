@props([
    'name' => '',
    'path' => null,
    'size' => 'md',
])

@php
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $initials = collect($parts)
        ->filter()
        ->take(2)
        ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('');

    $sizeClasses = match ($size) {
        'lg' => 'h-20 w-20 text-xl',
        'sm' => 'h-9 w-9 text-[10px]',
        default => 'h-12 w-12 text-sm',
    };
@endphp

<div class="grid {{ $sizeClasses }} shrink-0 place-items-center overflow-hidden rounded-full border border-[var(--zazu-border-strong)] bg-[var(--zazu-primary-soft)] font-extrabold text-[var(--zazu-primary)]">
    @if ($path)
        <img
            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}"
            alt="{{ $name }}"
            class="h-full w-full object-cover"
        >
    @else
        <span aria-hidden="true">{{ $initials ?: '?' }}</span>
        <span class="sr-only">{{ $name }}</span>
    @endif
</div>
