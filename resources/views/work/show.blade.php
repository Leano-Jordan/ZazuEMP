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
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="font-semibold text-slate-950">Requirements</h2>
                        <p class="mt-1 text-sm text-slate-500">Capture what this work needs before pricing or buying.</p>
                    </div>
                    <span class="text-xs font-medium text-slate-400">{{ $event->requirements->count() }} items</span>
                </div>

                @if ($event->requirements->isNotEmpty())
                    <div class="mt-5 space-y-2">
                        @foreach ($event->requirements as $requirement)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="font-medium text-slate-800">{{ $requirement->description }}</p>
                                    @if ($requirement->quantity)
                                        <p class="text-sm text-slate-500">{{ rtrim(rtrim(number_format((float) $requirement->quantity, 2), '0'), '.') }} {{ $requirement->unit }}</p>
                                    @endif
                                </div>
                                @if ($requirement->category || $requirement->notes)
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $requirement->category ?: 'General' }}
                                        @if ($requirement->notes) · {{ $requirement->notes }} @endif
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('work.requirements.store', $event) }}" class="mt-5 grid gap-3 sm:grid-cols-2">
                    @csrf
                    <input name="description" required class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="e.g. 100 chairs">
                    <input name="category" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="Category (optional)">
                    <input type="number" step="0.01" min="0" name="quantity" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="Quantity">
                    <input name="unit" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="Unit (chairs, people, hours...)">
                    <input name="notes" class="sm:col-span-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="Notes (optional)">
                    <div class="sm:col-span-2 flex justify-end">
                        <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Add requirement</button>
                    </div>
                </form>
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
