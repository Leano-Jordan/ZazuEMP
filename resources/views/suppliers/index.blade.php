<x-app-layout>
    <x-slot:title>Suppliers</x-slot:title>
    <x-slot:heading>Suppliers</x-slot:heading>
    <x-slot:headerAction><a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-secondary">Jobs</a></x-slot:headerAction>

    <section class="zazu-command-band">
        <div><div class="zazu-eyebrow">Resources</div><h2 class="zazu-command-title">Suppliers</h2><p class="zazu-command-copy">The future supplier register will connect buying, hired resources and preparation to the jobs that need them.</p></div>
        <div class="zazu-command-meta"><div class="zazu-command-meta-label">Stage</div><div class="zazu-command-meta-value">Foundation</div></div>
    </section>

    <section class="zazu-module-grid">
        @foreach ([
            ['title'=>'Catering suppliers','copy'=>'Food, drinks and consumables for jobs.','route'=>'capabilities.index'],
            ['title'=>'Equipment suppliers','copy'=>'Hired equipment and specialist items.','route'=>'assets.index'],
            ['title'=>'Decor suppliers','copy'=>'Decor, furniture and event setup resources.','route'=>'capabilities.index'],
            ['title'=>'Transport suppliers','copy'=>'Vehicles, delivery and equipment movement.','route'=>'work.index'],
        ] as $item)
            <a href="{{ route($item['route']) }}" class="zazu-module">
                <div class="zazu-module-top"><span class="zazu-module-group">Supplier type</span><span class="zazu-module-arrow">→</span></div>
                <div class="zazu-module-title">{{ $item['title'] }}</div>
                <div class="zazu-module-copy">{{ $item['copy'] }}</div>
            </a>
        @endforeach
    </section>

    <section class="zazu-card mt-5">
        <div class="zazu-card-header"><div class="zazu-card-title">Supplier register</div><div class="zazu-card-description">Supplier profiles, contacts, terms, purchase history and job-linked buying are not yet live. No sample supplier records are presented as real data.</div></div>
        <div class="zazu-placeholder"><div class="zazu-placeholder-title">Supplier workflow is next</div><div class="zazu-placeholder-copy">The existing Jobs, Services & prices and Assets areas remain usable while supplier management is built.</div></div>
    </section>

    <div class="zazu-skeleton-actions"><a href="{{ route('capabilities.index') }}" class="zazu-btn zazu-btn-primary">Services & prices</a><a href="{{ route('assets.index') }}" class="zazu-btn zazu-btn-ghost">Assets →</a></div>
</x-app-layout>