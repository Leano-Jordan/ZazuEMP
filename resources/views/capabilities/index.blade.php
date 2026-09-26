<x-app-layout>
    <x-slot:title>Business capabilities</x-slot:title>
    <x-slot:heading>Capabilities</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('capabilities.create') }}" class="zazu-btn zazu-btn-primary">Add capability</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Catalogue</div>
            <h2 class="zazu-command-title">What this business can deliver</h2>
            <p class="zazu-command-copy">Reusable services, rentals, products and packages belong here first. Workspaces can then select them without redefining the same capability.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Capabilities</div>
            <div class="zazu-command-meta-value">{{ $capabilities->total() }}</div>
        </div>
    </section>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header grid grid-cols-[minmax(0,1fr)_auto_auto] gap-4">
            <div>
                <div class="zazu-card-title">Capability catalogue</div>
                <div class="zazu-card-description">Open an item to edit its reusable business definition.</div>
            </div>
            <span class="zazu-eyebrow self-center">Pricing</span>
            <span class="zazu-eyebrow self-center">State</span>
        </div>

        @forelse ($capabilities as $capability)
            <a href="{{ route('capabilities.edit', $capability) }}" class="zazu-list-item grid grid-cols-[minmax(0,1fr)_auto_auto]">
                <div class="zazu-list-main">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="zazu-list-title">{{ $capability->name }}</span>
                        <span class="zazu-chip zazu-chip-neutral">{{ ucfirst($capability->capability_type) }}</span>
                    </div>
                    <div class="zazu-list-meta">
                        {{ $capability->category ?: 'Uncategorised' }}
                        @if ($capability->default_unit) · {{ $capability->default_unit }} @endif
                        @if ($capability->description) · {{ $capability->description }} @endif
                    </div>
                </div>
                <div class="zazu-side-primary">{{ str_replace('_', ' ', ucfirst($capability->pricing_basis)) }}</div>
                <div>
                    <span class="zazu-chip {{ $capability->is_active ? 'zazu-chip-success' : 'zazu-chip-neutral' }}">{{ $capability->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
            </a>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No capabilities yet</div>
                <p class="zazu-empty-copy">Define the services, rentals, products or packages this business can deliver.</p>
                <a href="{{ route('capabilities.create') }}" class="zazu-btn zazu-btn-primary mt-5">Add capability</a>
            </div>
        @endforelse

        @if ($capabilities->hasPages())
            <div class="border-t border-[var(--zazu-border)] px-5 py-4">{{ $capabilities->links() }}</div>
        @endif
    </section>
</x-app-layout>
