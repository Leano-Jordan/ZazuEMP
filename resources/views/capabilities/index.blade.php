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
                <div class="zazu-eyebrow">Business catalogue</div>
                <h2 class="zazu-command-title">Services &amp; Prices Catalogue</h2>
                <p class="zazu-command-copy">Manage what this business actually provides. Hire, services, products and packages retain their own pricing and flow into future job requirements and quotes.</p>
            </div>
            <div class="zazu-command-meta">
                <div class="zazu-command-meta-label">Catalogue</div>
                <div class="zazu-command-meta-value font-mono">{{ $capabilities->total() }}</div>
            </div>
        </section>

        <section class="zazu-catalogue-toolbar">
            <div class="zazu-catalogue-tabs" role="tablist" aria-label="Business catalogue">
                @if($equipment->isNotEmpty())
                    <button type="button" class="zazu-catalogue-tab {{ $equipment->isNotEmpty() ? 'is-active' : '' }}" role="tab" aria-selected="{{ $equipment->isNotEmpty() ? 'true' : 'false' }}" aria-controls="catalogue-panel-equipment" data-catalogue-tab="equipment">
                        <span>Hire &amp; rental</span><span class="zazu-tab-count font-mono">{{ $equipment->count() }}</span>
                    </button>
                @endif
                @if($services->isNotEmpty())
                    <button type="button" class="zazu-catalogue-tab {{ $equipment->isEmpty() ? 'is-active' : '' }}" role="tab" aria-selected="{{ $equipment->isEmpty() ? 'true' : 'false' }}" aria-controls="catalogue-panel-services" data-catalogue-tab="services">
                        <span>Services &amp; products</span><span class="zazu-tab-count font-mono">{{ $services->count() }}</span>
                    </button>
                @endif
                @if($equipment->isEmpty() && $services->isEmpty())
                    <span class="zazu-field-help">No catalogue items yet. Add the services or products this business actually provides.</span>
                @endif
            </div>

            <form method="GET" class="zazu-catalogue-filters">
                <label class="zazu-field zazu-catalogue-search">
                    <span class="sr-only">Search catalogue</span>
                    <input name="search" value="{{ request('search') }}" class="zazu-input" placeholder="Search catalogue..." aria-label="Search catalogue">
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

        @if($equipment->isNotEmpty())
        <section id="catalogue-panel-equipment" role="tabpanel" tabindex="0" data-catalogue-panel="equipment" class="zazu-catalogue-panel is-active">
            <div class="zazu-section-heading">
                <div>
                    <div class="zazu-eyebrow">Hire and rental</div>
                    <h3>Hire &amp; Rental</h3>
                    <p>Track hire quantities, rates and availability against the assets this business owns or manages.</p>
                </div>
                @if($isOwner)<a href="{{ route('capabilities.create') }}" class="zazu-btn zazu-btn-secondary">Add hire item</a>@endif
            </div>

            @if($equipment->isNotEmpty())
                <div class="zazu-catalogue-register">
                    <div class="zazu-register-head">
                        <span>SKU / CODE</span><span>ASSET &amp; CATEGORY</span><span>STOCK LEDGER</span><span>DAILY RATE</span><span>ACTIONS</span>
                    </div>
                    @foreach($equipment as $capability)
                        @php
                            $assetTotal = (int) $capability->assets_count;
                            $activeAssets = (int) $capability->available_assets_count;
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
                                @if($canViewAssets)<a href="{{ route('assets.index') }}" class="zazu-btn zazu-btn-secondary zazu-btn-sm">Allocate</a>@endif
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
        @endif

        @if($services->isNotEmpty())
        <section id="catalogue-panel-services" role="tabpanel" tabindex="0" data-catalogue-panel="services" class="zazu-catalogue-panel {{ $equipment->isEmpty() ? 'is-active' : '' }}" @if($equipment->isNotEmpty()) hidden @endif>
            <div class="zazu-section-heading">
                <div>
                    <div class="zazu-eyebrow">Business capability stream</div>
                    <h3>Services &amp; Products</h3>
                    <p>Services, products and packages your business sells, with pricing flowing into quote lines.</p>
                </div>
                @if($isOwner)<a href="{{ route('capabilities.create') }}" class="zazu-btn zazu-btn-secondary">Add service or product</a>@endif
            </div>

            @if($services->isNotEmpty())
                <div class="zazu-catering-grid">
                    @foreach($services as $capability)
                        <article class="zazu-catering-card">
                            <div class="zazu-catering-card-head">
                                <div>
                                    <div class="zazu-catalogue-category">{{ $capability->category }}</div>
                                    <h4>{{ $capability->name }}</h4>
                                </div>
                                <div class="zazu-rate-badge font-mono">{{ $capability->currency ?? 'ZAR' }} {{ $capability->default_price !== null ? number_format((float) $capability->default_price, 2) : '—' }} <span>/ {{ str_replace('_', ' ', $capability->pricing_basis) }}</span></div>
                            </div>
                            @if($capability->description)
                                <p class="zazu-catering-description">{{ $capability->description }}</p>
                            @endif
                            <div class="zazu-catering-meta">
                                <span class="zazu-chip zazu-chip-info">{{ ucfirst($capability->capability_type) }}</span>
                                <span class="font-mono">Pricing: {{ str_replace('_', ' ', $capability->pricing_basis) }}</span>
                                <span class="{{ $capability->is_active ? 'zazu-status-active' : 'zazu-status-muted' }}">{{ $capability->is_active ? 'ACTIVE' : 'HIDDEN' }}</span>
                            </div>
                            <div class="zazu-catering-actions">
                                @if($isOwner)<a href="{{ route('capabilities.edit', $capability) }}" class="zazu-btn zazu-btn-secondary zazu-btn-sm">Edit item</a>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="zazu-empty zazu-card">
                    <div class="zazu-empty-title">No services or products yet</div>
                    <p class="zazu-empty-copy">Add a service, product or package with its correct pricing basis so it appears here.</p>
                </div>
            @endif
        </section>
        @endif

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
                        <div class="zazu-eyebrow">Your current catalogue</div>
                        <div class="zazu-drawer-specs">
                            <div><span>Hire &amp; rental</span><strong class="font-mono">{{ $equipment->count() }} items</strong></div>
                            <div><span>Services &amp; products</span><strong class="font-mono">{{ $services->count() }} items</strong></div>
                            <div><span>Pricing</span><strong class="font-mono">Business-defined</strong></div>
                        </div>
                    </div>
                </div>
            </aside>
        @endif
    </div>
</x-app-layout>