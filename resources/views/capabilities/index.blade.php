<x-app-layout>
    <x-slot:title>Business capabilities</x-slot:title>
    <x-slot:heading>Business capabilities</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('capabilities.create') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
            Add capability
        </a>
    </x-slot:headerAction>

    <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-5">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Foundation</p>
        <h2 class="mt-1 text-lg font-semibold text-slate-950">What this business can deliver</h2>
        <p class="mt-1 max-w-2xl text-sm text-slate-500">
            Build the reusable capability catalogue first. These entries can later be selected inside Work, requirements and quotes without duplicating the same service definition.
        </p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="grid grid-cols-[1fr_auto_auto] gap-4 border-b border-slate-200 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
            <span>Capability</span>
            <span>Pricing</span>
            <span>Status</span>
        </div>

        @forelse ($capabilities as $capability)
            <a href="{{ route('capabilities.edit', $capability) }}" class="grid grid-cols-[1fr_auto_auto] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0 hover:bg-slate-50">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-semibold text-slate-950">{{ $capability->name }}</span>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ ucfirst($capability->capability_type) }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $capability->category ?: 'Uncategorised' }}
                        @if ($capability->default_unit) · {{ $capability->default_unit }} @endif
                    </p>
                    @if ($capability->description)
                        <p class="mt-1 truncate text-xs text-slate-400">{{ $capability->description }}</p>
                    @endif
                </div>
                <span class="text-sm text-slate-600">{{ str_replace('_', ' ', ucfirst($capability->pricing_basis)) }}</span>
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $capability->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $capability->is_active ? 'Active' : 'Inactive' }}
                </span>
            </a>
        @empty
            <div class="px-5 py-16 text-center">
                <p class="text-lg font-semibold text-slate-950">No capabilities yet</p>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Add the services, rentals, products or packages this business can deliver.</p>
                <a href="{{ route('capabilities.create') }}" class="mt-5 inline-flex rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Add first capability</a>
            </div>
        @endforelse

        @if ($capabilities->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">{{ $capabilities->links() }}</div>
        @endif
    </div>
</x-app-layout>
