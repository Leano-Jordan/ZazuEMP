<x-app-layout>
    <x-slot:title>Assets</x-slot:title>
    <x-slot:heading>Assets</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('inventory.index') }}" class="zazu-btn zazu-btn-secondary">Inventory</a></x-slot:headerAction>

    <section class="zazu-command-band">
        <div><div class="zazu-eyebrow">Resources</div><h2 class="zazu-command-title">Assets</h2><p class="zazu-command-copy">Keep reusable equipment visible, accountable and ready to attach to a job.</p></div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Stage</div><div class="zazu-command-meta-value">Foundation</div></div>
    </section>

    <section class="zazu-module-grid">
        @foreach ([
            ['title'=>'Owned equipment','copy'=>'Reusable equipment with condition and accountability.'],
            ['title'=>'Catering equipment','copy'=>'Items that move between catering jobs.'],
            ['title'=>'Transport assets','copy'=>'Vehicles and transport resources used operationally.'],
            ['title'=>'Other accountable assets','copy'=>'Anything valuable that should have a responsible record.'],
        ] as $item)
            <div class="zazu-module"><div class="zazu-module-top"><span class="zazu-module-group">Asset register</span><span class="zazu-chip zazu-chip-neutral">Planned</span></div><div class="zazu-module-title">{{ $item['title'] }}</div><div class="zazu-module-copy">{{ $item['copy'] }}</div></div>
        @endforeach
    </section>

    <section class="zazu-card mt-5">
        <div class="zazu-card-header"><div class="zazu-card-title">Asset register</div><div class="zazu-card-description">Asset records are not yet live. No fake stock or availability numbers are displayed.</div></div>
        <div class="zazu-placeholder"><div class="zazu-placeholder-title">Asset management is being prepared</div><div class="zazu-placeholder-copy">The finished workflow will cover availability, allocation, condition and accountability rather than acting as another static list.</div></div>
    </section>
</x-app-layout>