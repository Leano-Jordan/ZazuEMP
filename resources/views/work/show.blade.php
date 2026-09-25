<x-app-layout>
    <x-slot:title>{{ $event->name ?? 'Work' }}</x-slot:title>
    <x-slot:heading>{{ $event->name ?? 'Work' }}</x-slot:heading>
    <x-slot:headerAction>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('work.edit', $event) }}" class="inline-flex rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Edit work</a>
            <a href="{{ route('work.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">← All work</a>
        </div>
    </x-slot:headerAction>

    @php
        $status = $event->status ?? 'draft';
        $statusLabel = ucfirst(str_replace('_', ' ', $status));
        $statusClasses = match ($status) {
            'confirmed' => 'bg-emerald-50 text-emerald-700',
            'in_progress' => 'bg-sky-50 text-sky-700',
            'completed' => 'bg-slate-100 text-slate-700',
            'cancelled' => 'bg-rose-50 text-rose-700',
            default => 'bg-amber-50 text-amber-700',
        };
    @endphp

    <div class="mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">{{ $event->reference ?? 'No reference' }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $statusLabel }} · {{ $event->event_type ?: 'Work' }}</p>
        </div>
        <div class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $statusClasses }}">{{ $statusLabel }}</div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.5fr_1fr]">
        <div class="space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-semibold text-slate-950">Customer</h2>
                <p class="mt-1 text-sm text-slate-500">The relationship behind this work.</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Customer</p>
                        <p class="mt-1 font-medium">{{ $event->customer?->name ?? $event->customer_name ?? 'No customer' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Event-day contact</p>
                        <p class="mt-1 font-medium">{{ $event->eventDayContact?->name ?? 'Not selected' }}</p>
                        @if ($event->eventDayContact?->phone)
                            <p class="mt-1 text-sm text-slate-500">{{ $event->eventDayContact->phone }}</p>
                        @endif
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-semibold text-slate-950">Requirements & services</h2>
                <p class="mt-1 text-sm text-slate-500">This is where the work becomes specific to catering, hire, sound, funeral services, or another business capability.</p>
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

            @if ($event->notes)
                <section class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="font-semibold text-slate-950">Notes</h2>
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $event->notes }}</p>
                </section>
            @endif
        </div>

        <aside class="space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-semibold text-slate-950">Work details</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-400">Date</dt>
                        <dd class="mt-1 font-medium">{{ $event->event_date?->format('l, d F Y') ?? 'Not set' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Location</dt>
                        <dd class="mt-1 font-medium">{{ $event->event_address ?: 'Not set' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Created</dt>
                        <dd class="mt-1 font-medium">{{ $event->created_at?->format('d M Y, H:i') ?? 'Not available' }}</dd>
                    </div>
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
