<x-app-layout>
    <x-slot:title>Service catalogue</x-slot:title>
    <x-slot:heading>Service catalogue</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('capabilities.create') }}" class="zazu-btn zazu-btn-primary">Add service</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Your services and products</div>
            <h2 class="zazu-command-title">Choose from your catalogue instead of typing every time</h2>
            <p class="zazu-command-copy">Save the things you regularly provide once. Zazu can then show them as choices when you build a job and can carry their usual price into a quote.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Items</div>
            <div class="zazu-command-meta-value">{{ $capabilities->total() }}</div>
        </div>
    </section>

    <section class="zazu-card p-4 mb-5">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <label class="zazu-field min-w-[220px] flex-1">
                <span class="zazu-label">Find a service</span>
                <input name="search" value="{{ request('search') }}" class="zazu-input" aria-label="Find a service">
            </label>
            <label class="zazu-field min-w-[190px]">
                <span class="zazu-label">Service group</span>
                <select name="category" class="zazu-select">
                    <option value="">All service groups</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </label>
            <button class="zazu-btn zazu-btn-secondary">Find</button>
            @if(request()->hasAny(['search','category']))
                <a href="{{ route('capabilities.index') }}" class="zazu-btn zazu-btn-ghost">Clear</a>
            @endif
        </form>
    </section>

    <section class="zazu-catalogue-grid">
        @forelse ($capabilities as $capability)
            <a href="{{ route('capabilities.edit', $capability) }}" class="zazu-catalogue-card">
                <div class="zazu-catalogue-image">
                    @if ($capability->image_path)
                        <img src="{{ Storage::disk('public')->url($capability->image_path) }}" alt="{{ $capability->name }}">
                    @else
                        <span aria-hidden="true">{{ strtoupper(substr($capability->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="zazu-catalogue-body">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="zazu-catalogue-category">{{ $capability->category }}</div>
                            <div class="zazu-catalogue-title">{{ $capability->name }}</div>
                        </div>
                        <span class="zazu-chip {{ $capability->is_active ? 'zazu-chip-success' : 'zazu-chip-neutral' }}">{{ $capability->is_active ? 'Available' : 'Hidden' }}</span>
                    </div>
                    @if ($capability->description)
                        <div class="zazu-catalogue-copy">{{ $capability->description }}</div>
                    @endif
                    <div class="zazu-catalogue-price">
                        @if ($capability->default_price !== null)
                            {{ 'ZAR '.number_format((float) $capability->default_price, 2) }}
                            <span>{{ str_replace('_', ' ', $capability->pricing_basis) }}</span>
                        @else
                            <span>Price set when quoting</span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <section class="zazu-empty zazu-card">
                <div class="zazu-empty-title">Your catalogue is empty</div>
                <p class="zazu-empty-copy">Start with the services you sell most often, such as Catering, Decor, Sound & Entertainment, Furniture & Equipment, Photography & Video, Baking or Transport.</p>
                <a href="{{ route('capabilities.create') }}" class="zazu-btn zazu-btn-primary mt-5">Add your first service</a>
            </section>
        @endforelse
    </section>

    @if ($capabilities->hasPages())
        <div class="mt-5">{{ $capabilities->links() }}</div>
    @endif
</x-app-layout>