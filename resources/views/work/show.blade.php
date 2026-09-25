<x-app-layout>
    <x-slot:title>{{ $event->name }}</x-slot:title>
    <x-slot:heading>{{ $event->name }}</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('work.edit', $event) }}" class="mr-4 inline-flex rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Edit work</a>
        <a href="{{ route('work.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">← All work</a>
    </x-slot:headerAction>

    <div class="mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">{{ $event->reference }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ ucfirst($event->status) }} · {{ $event->event_type ?: 'Work' }}</p>
        </div>
        <div class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">Draft workspace</div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.5fr_1fr]">
        <div class="space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold text-slate-950">Customer</h2>
                        <p class="mt-1 text-sm text-slate-500">The relationship behind this work.</p>
                    </div>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Customer</p>
                        <p class="mt-1 font-medium">{{ $event->customer?->name ?? $event->customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Event-day contact</p>
                        <p class="mt-1 font-medium">{{ $event->eventDayContact?->name ?? 'Not selected' }}</p>
                        @if ($event->eventDayContact?->phone)<p class="mt-1 text-sm text-slate-500">{{ $event->eventDayContact->phone }}</p>@endif
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-semibold text-slate-950">Requirements & services</h2>
                <p class="mt-1 text-sm text-slate-500">This is where the work will become specific to catering, hire, sound, funeral services, or another business capability.</p>
                <div class="mt-5 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center">
                    <p class="font-medium text-slate-700">Requirements layer comes next</p>
                    <p class="mt-1 text-sm text-slate-400">No quote is created until the work is understood.</p>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-semibold text-slate-950">Travel & location</h2>
                <div class="mt-4 rounded-xl bg-slate-50 p-4">
                    <p class="text-sm font-medium text-slate-700">{{ $event->event_address ?: 'No event location yet' }}</p>
                    <p class="mt-1 text-xs text-slate-400">Route options, distance, travel time and fuel costing will attach here.</p>
                </div>
            </section>
        </div>

        <aside class="space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-semibold text-slate-950">Work details</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div><dt class="text-slate-400">Date</dt><dd class="mt-1 font-medium">{{ $event->event_date?->format('l, d F Y') ?? 'Not set' }}</dd></div>
                    <div><dt class="text-slate-400">Location</dt><dd class="mt-1 font-medium">{{ $event->event_address ?: 'Not set' }}</dd></div>
                    <div><dt class="text-slate-400">Created</dt><dd class="mt-1 font-medium">{{ $event->created_at->format('d M Y, H:i') }}</dd></div>
                </dl>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-semibold text-slate-950">Next operational layers</h2>
                <div class="mt-4 space-y-2 text-sm">
                    <div class="rounded-xl bg-slate-50 px-3 py-3">Requirements</div>
                    <div class="rounded-xl bg-slate-50 px-3 py-3">Quote & versions</div>
                    <div class="rounded-xl bg-slate-50 px-3 py-3">Buying & preparation</div>
                    <div class="rounded-xl bg-slate-50 px-3 py-3">Execution & accountability</div>
                </div>
            </section>
        </aside>
    </div>
</x-app-layout>
