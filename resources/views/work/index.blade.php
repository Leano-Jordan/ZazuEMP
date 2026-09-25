<x-app-layout>
    <x-slot:title>Work</x-slot:title>
    <x-slot:heading>Work</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.create') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
            New work
        </a>
    </x-slot:headerAction>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Active work</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $activeWorkCount }}</p>
            <p class="mt-1 text-xs text-slate-400">Not completed or cancelled</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Upcoming</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $upcomingWorkCount }}</p>
            <p class="mt-1 text-xs text-slate-400">Active work with a future date</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Customers</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $customerCount }}</p>
            <p class="mt-1 text-xs text-slate-400">People or organisations in Zazu</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-900 p-5 text-white">
            <p class="text-sm text-slate-300">Workspace model</p>
            <p class="mt-2 text-lg font-semibold">One job, one context</p>
            <p class="mt-1 text-xs text-slate-400">Requirements and commercial layers attach to the work record.</p>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-slate-950">Recent work</h2>
                <p class="mt-1 text-sm text-slate-500">Every job becomes an operational workspace.</p>
            </div>
            <a href="{{ route('customers.index') }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">Customers →</a>
        </div>

        @forelse ($events as $event)
            <a href="{{ route('work.show', $event) }}" class="block border-b border-slate-100 px-5 py-4 last:border-b-0 hover:bg-slate-50">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold text-slate-950">{{ $event->name }}</span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ ucfirst(str_replace('_', ' ', $event->status ?? 'draft')) }}</span>
                        </div>
                        <p class="mt-1 truncate text-sm text-slate-500">
                            {{ $event->customer?->name ?? $event->customer_name ?? 'No customer' }}
                            · {{ $event->reference ?? 'No reference' }}
                        </p>
                    </div>
                    <div class="shrink-0 text-left sm:text-right">
                        <p class="text-sm font-medium text-slate-700">{{ $event->event_date?->format('d M Y') ?? 'Date not set' }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $event->event_type ?: 'Work' }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="px-5 py-16 text-center">
                <p class="text-lg font-semibold text-slate-950">No work yet</p>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Create a customer first, then turn that customer into a job or event.</p>
                <div class="mt-5 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('customers.create') }}" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Add customer</a>
                    <a href="{{ route('work.create') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700">Create work</a>
                </div>
            </div>
        @endforelse

        @if ($events->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">{{ $events->links() }}</div>
        @endif
    </div>
</x-app-layout>
