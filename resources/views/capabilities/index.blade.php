<x-app-layout>
    <x-slot:title>Services &amp; Prices</x-slot:title>
    <x-slot:heading>Services &amp; Prices</x-slot:heading>
    <x-slot:headerAction>
        @if($isOwner)
            <button type="button" class="zazu-btn zazu-btn-primary" data-catalogue-drawer-open>+ Add catalogue item</button>
        @endif
    </x-slot:headerAction>

    <div class="zazu-catalogue-shell" data-zazu-catalogue>
        <section class="zazu-command-band zazu-catalogue-command">
            <div>
                <div class="zazu-eyebrow">Rosco ICT &gt; Capability Catalogue</div>
                <h2 class="zazu-command-title">Services &amp; Prices Catalogue</h2>
                <p class="zazu-command-copy">A single commercial catalogue for hireable equipment, catering packages and reusable services. Catalogue prices flow into future requirements and quote revisions.</p>
            </div>
            <div class="zazu-command-meta">
                <div class="zazu-command-meta-label">Catalogue</div>
                <div class="zazu-command-meta-value font-mono">{{ $capabilities->total() }}</div>
            </div>
        </section>

        <section class="zazu-catalogue-toolbar">
            <div class="zazu-catalogue-tabs" role="tablist" aria-label="Catalogue streams">
                <button type="button" class="zazu-catalogue-tab is-active" role="tab" aria-selected="true" data-catalogue-tab="equipment">
                    <span>Equipment Hire Catalog</span><span class="zazu-tab-count font-mono">{{ $equipment->count() }}</span>
                </button>
                <button type="button" class="zazu-catalogue-tab" role="tab" aria-selected="false" data-catalogue-tab="catering">
                    <span>Catering &amp; Food Packages</span><span class="zazu-tab-count font-mono">{{ $catering->count() }}</span>
                </button>
            </div>

            <form method="GET" class="zazu-catalogue-filters">
                <label class="zazu-field zazu-catalogue-search">
                    <span class="sr-only">Search catalogue</span>
                    <input name="search" value="{{ request('search') }}" class="zazu-input" placeholder="Search gear, SKUs, or menu packages... (Cmd+K)" aria-label="Search catalogue">
                </label>
                <label class="zazu-field zazu-catalogue-filter">
                    <span class="sr-only">Filter category</span>
                    <select name="category" class="zazu-select" aria-label="Filter category">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="zazu-btn zazu-btn-secondary">Filter</button>
                @if(request()->hasAny(['search','category']))
                    <a href="{{ route('capabilities.index') }}" class="zazu-btn zazu-btn-ghost">Clear</a>
                @endif
            </form>
        </section>

        <section data-catalogue-panel="equipment" class="zazu-catalogue-panel is-active">
            <div class="zazu-section-heading">
                <div>
                    <div class="zazu-eyebrow">Physical revenue stream</div>
                    <h3>Equipment Hire</h3>
                    <p>Reusable rental capabilities linked to the asset ledger where equipment records exist.</p>
                </div>
                @if($isOwner)<a href="{{ route('capabilities.create') }}" class="zazu-btn zazu-btn-secondary">Add equipment</a>@endif
            </div>

            @if($equipment->isNotEmpty())
                <div class="zazu-catalogue-register">
                    <div class="zazu-register-head">
                        <span>SKU / CODE</span><span>ASSET &amp; CATEGORY</span><span>STOCK LEDGER</span><span>DAILY RATE</span><span>ACTIONS</span>
                    </div>
                    @foreach($equipment as $capability)
                        @php
                            $assetTotal = (int) $capability->assets_count;
                            $activeAssets = $capability->assets()->whereNotIn('status', ['maintenance', 'retired'])->count();
                            $rate = $capability->default_price !== null ? number_format((float) $capability->default_price, 2) : '—';
                            $code = 'EQ-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $capability->name), 0, 8));
                        @endphp
                        <article class="zazu-catalogue-row">
                            <div class="font-mono text-xs font-semibold text-[var(--zazu-text-muted)]">{{ $code }}</div>
                            <div>
                                <div class="zazu-catalogue-row-title">{{ $capability->name }}</div>
                                <div class="zazu-catalogue-row-meta">{{ $capability->category }} · <span class="{{ $capability->is_active ? 'zazu-status-active' : 'zazu-status-muted' }}">{{ $capability->is_active ? 'ACTIVE' : 'HIDDEN' }}</span></div>
                            </div>
                            <div class="font-mono text-xs">
                                @if($assetTotal)
                                    <strong>{{ $assetTotal }}</strong> Total · {{ $activeAssets }} Available
                                @else
                                    Asset ledger not linked
                                @endif
                            </div>
                            <div class="font-mono text-sm font-semibold">{{ $capability->currency ?? 'ZAR' }} {{ $rate }} <span class="zazu-catalogue-unit">/ {{ str_replace('_', ' ', $capability->pricing_basis) }}</span></div>
                            <div class="zazu-catalogue-actions">
                                @if($isOwner)<a href="{{ route('capabilities.edit', $capability) }}" class="zazu-btn zazu-btn-ghost zazu-btn-sm">Edit</a>@endif
                                <a href="{{ route('assets.index') }}" class="zazu-btn zazu-btn-secondary zazu-btn-sm">Allocate</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="zazu-empty zazu-card">
                    <div class="zazu-empty-title">No equipment capabilities yet</div>
                    <p class="zazu-empty-copy">Create a capability as Hire / rental, then connect physical assets through the Assets ledger.</p>
                </div>
            @endif
        </section>

        <section data-catalogue-panel="catering" class="zazu-catalogue-panel" hidden>
            <div class="zazu-section-heading">
                <div>
                    <div class="zazu-eyebrow">Hospitality revenue stream</div>
                    <h3>Catering &amp; Food Packages</h3>
                    <p>Per-person and hospitality capabilities that can become reusable quote lines.</p>
                </div>
                @if($isOwner)<a href="{{ route('capabilities.create') }}" class="zazu-btn zazu-btn-secondary">Add package</a>@endif
            </div>

            @if($catering->isNotEmpty())
                <div class="zazu-catering-grid">
                    @foreach($catering as $capability)
                        @php
                            $tags = [];
                            foreach (['halaal' => 'Halaal', 'vegan' => 'Vegan', 'gluten' => 'Gluten-Free', 'vegetarian' => 'Vegetarian'] as $needle => $label) {
                                if (str_contains(strtolower((string) $capability->description), $needle)) $tags[] = $label;
                            }
                        @endphp
                        <article class="zazu-catering-card">
                            <div class="zazu-catering-card-head">
                                <div>
                                    <div class="zazu-catalogue-category">{{ $capability->category }}</div>
                                    <h4>{{ $capability->name }}</h4>
                                </div>
                                <div class="zazu-rate-badge font-mono">{{ $capability->currency ?? 'ZAR' }} {{ $capability->default_price !== null ? number_format((float) $capability->default_price, 2) : '—' }} <span>/ pax</span></div>
                            </div>
                            @if($capability->description)
                                <p class="zazu-catering-description">{{ $capability->description }}</p>
                            @endif
                            <div class="zazu-catering-meta">
                                <span class="font-mono">Pricing: {{ str_replace('_', ' ', $capability->pricing_basis) }}</span>
                                <span class="{{ $capability->is_active ? 'zazu-status-active' : 'zazu-status-muted' }}">{{ $capability->is_active ? 'ACTIVE' : 'HIDDEN' }}</span>
                            </div>
                            @if($tags)
                                <div class="zazu-tag-row">
                                    @foreach($tags as $tag)
                                        <span class="zazu-dietary-tag zazu-dietary-{{ strtolower(str_replace('-', '', $tag)) }}">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @else
                                <div class="zazu-field-help">Dietary profile not recorded.</div>
                            @endif
                            <div class="zazu-catering-actions">
                                @if($isOwner)<a href="{{ route('capabilities.edit', $capability) }}" class="zazu-btn zazu-btn-secondary zazu-btn-sm">Edit package</a>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="zazu-empty zazu-card">
                    <div class="zazu-empty-title">No catering packages yet</div>
                    <p class="zazu-empty-copy">Create a package and choose Per person pricing so it appears in this hospitality stream.</p>
                </div>
            @endif
        </section>

        @if($isOwner)
            <aside class="zazu-catalogue-drawer" data-catalogue-drawer hidden aria-hidden="true">
                <div class="zazu-catalogue-drawer-backdrop" data-catalogue-drawer-close></div>
                <div class="zazu-catalogue-drawer-panel" role="dialog" aria-modal="true" aria-labelledby="catalogue-drawer-title">
                    <header class="zazu-catalogue-drawer-head">
                        <div><div class="zazu-eyebrow">Catalogue item</div><h3 id="catalogue-drawer-title">Add catalogue item</h3></div>
                        <button type="button" class="zazu-icon-btn" data-catalogue-drawer-close aria-label="Close">×</button>
                    </header>
                    <div class="zazu-catalogue-drawer-body">
                        <p class="zazu-panel-copy">Use the full catalogue editor when you need to save a new capability. This drawer keeps the operational decision point close to the catalogue.</p>
                        <a href="{{ route('capabilities.create') }}" class="zazu-btn zazu-btn-primary w-full">Open catalogue editor →</a>
                        <div class="zazu-catalogue-drawer-rule"></div>
                        <div class="zazu-eyebrow">Pricing structure</div>
                        <div class="zazu-drawer-specs">
                            <div><span>Hire</span><strong class="font-mono">Per day</strong></div>
                            <div><span>Catering</span><strong class="font-mono">Per pax</strong></div>
                            <div><span>Tax</span><strong class="font-mono">15% VAT</strong></div>
                        </div>
                    </div>
                </div>
            </aside>
        @endif
    </div>
</x-app-layout>