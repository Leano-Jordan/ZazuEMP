<x-app-layout>
    <x-slot:title>Customers</x-slot:title>
    <x-slot:heading>Customers</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('customers.create') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
            New customer
        </a>
    </x-slot:headerAction>

    <div class="mb-5 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="font-semibold text-slate-950">Customer directory</p>
            <p class="mt-1 text-sm text-slate-500">Customers are the starting point for operational work.</p>
        </div>
        <p class="text-sm text-slate-400">{{ $customers->total() }} total</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        @forelse ($customers as $customer)
            <a href="{{ route('work.create', ['customer_id' => $customer->id]) }}" class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 last:border-b-0 transition hover:bg-slate-100 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold text-slate-950">{{ $customer->name }}</p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $customer->primaryContact?->phone ?? 'No phone' }}
                        @if ($customer->primaryContact?->email) · {{ $customer->primaryContact->email }} @endif
                    </p>
                </div>
                <div class="text-sm text-slate-500">
                    {{ $customer->events_count }} {{ $customer->events_count === 1 ? 'work item' : 'work items' }}
                </div>
            </div>
            </a>
        @empty
            <div class="px-5 py-16 text-center">
                <p class="text-lg font-semibold text-slate-950">No customers yet</p>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Add your first customer. Their primary contact will be stored with them.</p>
                <a href="{{ route('customers.create') }}" class="mt-5 inline-flex rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Add customer</a>
            </div>
        @endforelse

        @if ($customers->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">{{ $customers->links() }}</div>
        @endif
    </div>
</x-app-layout>
