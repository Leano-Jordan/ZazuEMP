<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#E3EBF3">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    @php
        $business = app(\App\Support\CurrentBusiness::class)->resolve(auth()->user());
        $businesses = auth()->user()->businesses()->where('businesses.status', 'active')->orderBy('businesses.name')->get();
        $currentMembership = $businesses->firstWhere('id', $business?->id);
        $currentRole = $currentMembership?->pivot?->role;
        $can = fn (string $permission): bool => $currentRole === 'owner'
            || in_array($permission, config('zazu.permissions.roles.'.$currentRole, []), true);
        $isOwner = $currentRole === 'owner';
        $isPlatformAdmin = in_array(strtolower((string) auth()->user()->email), array_map('strtolower', config('zazu.platform_admin_emails', [])), true);
        $experienceLevel = app(\App\Support\ExperienceLevel::class)->for(auth()->user(), $business);
        $experienceLabel = app(\App\Support\ExperienceLevel::class)->label($experienceLevel);
        $brandingVersion = $business?->updated_at?->timestamp ?? 0;
    @endphp
    <title>{{ $title ?? 'Zazu' }} · {{ $business?->name ?? 'Zazu EMP' }}</title>
    <script>
        (() => {
            const saved = localStorage.getItem('zazu-theme');
            const theme = saved === 'light' || saved === 'dark'
                ? saved
                : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

            document.documentElement.dataset.theme = theme;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/css/zazu-responsive-theme.css', 'resources/css/zazu-final-visual-sweep.css', 'resources/css/zazu-mobile-refinement.css', 'resources/js/app.js'])
</head>
<body data-zazu-route="{{ request()->route()?->getName() ?? '' }}" class="{{ $business?->wallpaper_path ? 'zazu-has-wallpaper' : '' }}" data-business-currency="{{ $business?->currency ?? 'ZAR' }}" @if($business?->wallpaper_path) style="--zazu-wallpaper: url('{{ e(route('business.media', ['type' => 'wallpaper']).'?v='.$brandingVersion) }}')" @endif>
    <a class="zazu-skip-link" href="#main-content">Skip to main content</a>
    <div class="zazu-shell">
        <button type="button" class="zazu-mobile-nav-backdrop" data-mobile-sidebar-close aria-label="Close navigation"></button>
        <aside class="zazu-sidebar" id="zazu-mobile-sidebar" data-mobile-sidebar>
            <div class="zazu-brand">
                <a href="{{ route('dashboard') }}" class="zazu-brand-link" aria-label="Zazu EMP dashboard">
                    @if($business?->logo_path)
                        <img
                            class="zazu-brand-logo"
                            src="{{ route('business.media', ['type' => 'logo']) }}?v={{ $brandingVersion }}"
                            alt="{{ $business->name }} logo"
                        >
                    @else
                        <span class="zazu-brand-mark" aria-hidden="true">Z</span>
                    @endif
                    <span class="zazu-brand-lockup">
                        <strong>ZAZU</strong>
                        <span>EMP</span>
                    </span>
                </a>
                <button type="button" class="zazu-mobile-sidebar-close" data-mobile-sidebar-close aria-label="Close navigation">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <nav class="zazu-nav" aria-label="Primary">
                <div class="zazu-nav-quick" aria-label="Quick access">
                    <div class="zazu-nav-quick-head">
                        <span>Quick access</span>
                        <span class="zazu-nav-quick-hint">Jump straight in</span>
                    </div>
                    <div class="zazu-nav-quick-grid">
                        <a href="{{ route('work.index') }}" class="zazu-nav-quick-link {{ request()->routeIs('work.*') ? 'active' : '' }}" title="Jobs" @if(request()->routeIs('work.*')) aria-current="page" @endif><span class="zazu-nav-quick-icon" aria-hidden="true">J</span><span>Jobs</span></a>
                        @if($can('customers.view'))<a href="{{ route('customers.index') }}" class="zazu-nav-quick-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" title="Customers" @if(request()->routeIs('customers.*')) aria-current="page" @endif><span class="zazu-nav-quick-icon" aria-hidden="true">C</span><span>Customers</span></a>@endif
                        @if($can('quotes.view'))<a href="{{ route('quotes.index') }}" class="zazu-nav-quick-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}" title="Quotes" @if(request()->routeIs('quotes.*')) aria-current="page" @endif><span class="zazu-nav-quick-icon" aria-hidden="true">Q</span><span>Quotes</span></a>@endif
                        @if($can('purchasing.view'))<a href="{{ route('purchasing.index') }}" class="zazu-nav-quick-link {{ request()->routeIs('purchasing.*') ? 'active' : '' }}" title="Purchasing" @if(request()->routeIs('purchasing.*')) aria-current="page" @endif><span class="zazu-nav-quick-icon" aria-hidden="true">P</span><span>Purchasing</span></a>@endif
                        @if($can('suppliers.view'))<a href="{{ route('suppliers.index') }}" class="zazu-nav-quick-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" title="Suppliers" @if(request()->routeIs('suppliers.*')) aria-current="page" @endif><span class="zazu-nav-quick-icon" aria-hidden="true">S</span><span>Suppliers</span></a>@endif
                        @if($can('calendar.view'))<a href="{{ route('calendar.index') }}" class="zazu-nav-quick-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}" title="Calendar" @if(request()->routeIs('calendar.*')) aria-current="page" @endif><span class="zazu-nav-quick-icon" aria-hidden="true">K</span><span>Calendar</span></a>@endif
                    </div>
                </div>

                <div class="zazu-nav-all">
                    <div class="zazu-nav-all-head">
                        <span>All areas</span>
                        <button type="button" class="zazu-nav-jump" data-zazu-nav-jump aria-label="Open navigation search">⌕ <span>Find</span></button>
                    </div>

                    <div class="zazu-nav-group">
                        <div class="zazu-nav-group-label">Workspace</div>
                        <div class="zazu-nav-stack zazu-nav-primary">
                            <a href="{{ route('dashboard') }}" class="zazu-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif><span class="zazu-nav-label">Dashboard</span></a>
                            @if($can('work.view'))<a href="{{ route('work.index') }}" class="zazu-nav-link {{ request()->routeIs('work.*') ? 'active' : '' }}"><span class="zazu-nav-label">Jobs</span></a>@endif
                            @if($can('customers.view'))<a href="{{ route('customers.index') }}" class="zazu-nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"><span class="zazu-nav-label">Customers</span></a>@endif
                        </div>
                    </div>

                    <div class="zazu-nav-group">
                        <div class="zazu-nav-group-label">Sales & operations</div>
                        <div class="zazu-nav-stack">
                            @if($can('capabilities.view'))<a href="{{ route('capabilities.index') }}" class="zazu-nav-link {{ request()->routeIs('capabilities.*') ? 'active' : '' }}"><span class="zazu-nav-label">Services & prices</span></a>@endif
                            @if($can('quotes.view'))<a href="{{ route('quotes.index') }}" class="zazu-nav-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}"><span class="zazu-nav-label">Quotes</span></a>@endif
                            @if($can('calendar.view'))<a href="{{ route('calendar.index') }}" class="zazu-nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}"><span class="zazu-nav-label">Calendar</span></a>@endif
                        </div>
                    </div>

                    <div class="zazu-nav-group">
                        <div class="zazu-nav-group-label">Purchasing & resources</div>
                        <div class="zazu-nav-stack">
                            @if($can('purchasing.view'))<a href="{{ route('purchasing.index') }}" class="zazu-nav-link {{ request()->routeIs('purchasing.*') ? 'active' : '' }}"><span class="zazu-nav-label">Purchasing</span></a>@endif
                            @if($can('suppliers.view'))<a href="{{ route('suppliers.index') }}" class="zazu-nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><span class="zazu-nav-label">Suppliers</span></a>@endif
                            @if($can('inventory.view'))<a href="{{ route('inventory.index') }}" class="zazu-nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}"><span class="zazu-nav-label">Inventory</span></a>@endif
                            @if($can('assets.view'))<a href="{{ route('assets.index') }}" class="zazu-nav-link {{ request()->routeIs('assets.*') ? 'active' : '' }}"><span class="zazu-nav-label">Assets</span></a>@endif
                        </div>
                    </div>

                    <div class="zazu-nav-group">
                        <div class="zazu-nav-group-label">Money & control</div>
                        <div class="zazu-nav-stack">
                            @if($can('finance.view'))<a href="{{ route('finance.index') }}" class="zazu-nav-link {{ request()->routeIs('finance.*') ? 'active' : '' }}"><span class="zazu-nav-label">Finance</span></a>@endif
                            <a href="{{ route('search.index') }}" class="zazu-nav-link {{ request()->routeIs('search.*') ? 'active' : '' }}" @if(request()->routeIs('search.*')) aria-current="page" @endif><span class="zazu-nav-label">Search</span></a>
                            @if($can('reports.view'))<a href="{{ route('reports.index') }}" class="zazu-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><span class="zazu-nav-label">Reports</span></a>@endif
                        </div>
                    </div>

                    @if($isOwner)
                        <div class="zazu-nav-group">
                            <div class="zazu-nav-group-label">System</div>
                            <div class="zazu-nav-stack">
                                <a href="{{ route('settings.index') }}" class="zazu-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"><span class="zazu-nav-label">Settings</span></a>
                                @if($isPlatformAdmin)
                                    <a href="{{ route('admin.landing.settings') }}" class="zazu-nav-link {{ request()->routeIs('admin.landing.*') ? 'active' : '' }}"><span class="zazu-nav-label">Platform admin</span></a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </nav>

            <div class="zazu-sidebar-footer">
                <div class="zazu-footer-workspace">
                    <span class="zazu-footer-workspace-label">Workspace</span>
                    <strong>{{ $business?->name ?? 'Zazu EMP' }}</strong>
                    <span>Event operations</span>
                </div>
                @auth
                    <div class="zazu-user-menu" data-user-menu>
                        <button
                            type="button"
                            class="zazu-user-trigger"
                            data-user-trigger
                            aria-expanded="false"
                            aria-controls="zazu-user-menu"
                            aria-label="Open account menu for {{ '@'.auth()->user()->username }}"
                        >
                            <x-profile-avatar :name="auth()->user()->name" :path="auth()->user()->profile_photo_path" media-type="user" :media-id="auth()->id()" size="sm" />
                            <span class="zazu-user-identity">
                                <span class="zazu-user-kicker">Signed in</span>
                                <strong>{{ auth()->user()->name }}</strong>
                                <span>{{ '@'.auth()->user()->username }} · {{ ucfirst(str_replace('_', ' ', $currentRole ?? 'member')) }}</span>
                            </span>
                            <svg class="zazu-user-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m7 9 5 5 5-5"></path></svg>
                        </button>

                        <div class="zazu-user-popover" id="zazu-user-menu" data-user-popover hidden>
                            <div class="zazu-user-popover-head">
                                <x-profile-avatar :name="auth()->user()->name" :path="auth()->user()->profile_photo_path" media-type="user" :media-id="auth()->id()" size="md" />
                                <div class="min-w-0">
                                    <span class="zazu-user-popover-label">Account</span>
                                    <strong class="zazu-user-popover-name">{{ auth()->user()->name }}</strong>
                                    <span class="zazu-user-popover-email">{{ '@'.auth()->user()->username }}</span>
                                    <span class="zazu-user-popover-email">{{ auth()->user()->email }}</span>
                                    <span class="zazu-user-role">{{ ucfirst(str_replace('_', ' ', $currentRole ?? 'member')) }} · {{ $experienceLabel }}</span>
                                </div>
                            </div>

                            <div class="zazu-user-popover-section">
                                <span class="zazu-user-popover-label">Workspace</span>
                                @if($businesses->count() > 1)
                                    <form method="POST" action="{{ route('business.switch') }}" class="zazu-user-switch-form">
                                        @csrf
                                        <label class="sr-only" for="zazu-business-switch">Current workspace</label>
                                        <select id="zazu-business-switch" name="business_id" class="zazu-user-switch-select" data-business-switch>
                                            @foreach($businesses as $availableBusiness)
                                                <option value="{{ $availableBusiness->id }}" @selected((int) $availableBusiness->id === (int) $business->id)>{{ $availableBusiness->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    <strong class="zazu-user-workspace-name">{{ $business?->name ?? 'Zazu EMP' }}</strong>
                                @endif
                            </div>

                            <div class="zazu-user-popover-divider"></div>

                            <a href="{{ route('preferences.experience') }}" class="zazu-user-menu-link">
                                <span>Experience preference</span>
                                <small>{{ $experienceLabel }}</small>
                            </a>
                            @if($isOwner)
                                <a href="{{ route('onboarding.index') }}" class="zazu-user-menu-link">
                                    <span>Setup centre</span>
                                    <small>Business and catalogue foundation</small>
                                </a>
                                <a href="{{ route('settings.index') }}" class="zazu-user-menu-link">
                                    <span>Business settings</span>
                                    <small>Workspace configuration</small>
                                </a>
                            @endif

                            <div class="zazu-user-popover-divider"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="zazu-user-signout">
                                    <span>Sign out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </aside>

        <main id="main-content" class="zazu-main" tabindex="-1">
            <header class="zazu-topbar">
                <div class="zazu-topbar-inner">
                    <button type="button" class="zazu-mobile-menu-button" data-mobile-sidebar-toggle aria-expanded="false" aria-controls="zazu-mobile-sidebar" aria-label="Open navigation">
                        <span class="zazu-mobile-menu-icon" aria-hidden="true"><i></i><i></i><i></i></span>
                    </button>
                    <div class="zazu-header-context">
                        <div class="zazu-header-title-row">
                            <h1 class="zazu-page-title">{{ $heading ?? $title ?? 'Workspace' }}</h1>
                        </div>
                        <nav class="zazu-section-tabs" aria-label="Section navigation">
                            @if(request()->routeIs('work.*','capabilities.*','quotes.*','calendar.*'))
                                <span class="zazu-section-name">Operations</span>
                                @if($can('work.view'))<a href="{{ route('work.index') }}" class="{{ request()->routeIs('work.*') ? 'active' : '' }}">Jobs</a>@endif
                                @if($can('capabilities.view'))<a href="{{ route('capabilities.index') }}" class="{{ request()->routeIs('capabilities.*') ? 'active' : '' }}">Services & prices</a>@endif
                                @if($can('quotes.view'))<a href="{{ route('quotes.index') }}" class="{{ request()->routeIs('quotes.*') ? 'active' : '' }}">Quotes</a>@endif
                                @if($can('calendar.view'))<a href="{{ route('calendar.index') }}" class="{{ request()->routeIs('calendar.*') ? 'active' : '' }}">Calendar</a>@endif
                            @elseif(request()->routeIs('customers.*'))
                                <span class="zazu-section-name">Customers</span><a href="{{ route('customers.index') }}" class="active">Customers</a>
                            @elseif(request()->routeIs('finance.*'))
                                <span class="zazu-section-name">Finance</span><a href="{{ route('finance.index') }}" class="active">Overview</a>
                                @if($can('finance.invoice.create'))<a href="{{ route('finance.invoices.create') }}">New invoice</a>@endif
                                @if($can('finance.payment.create'))<a href="{{ route('finance.payments.create') }}">Payment</a>@endif
                                @if($can('finance.expense.create'))<a href="{{ route('finance.expenses.create') }}">Expense</a>@endif
                            @elseif(request()->routeIs('purchasing.*','suppliers.*','inventory.*','assets.*'))
                                <span class="zazu-section-name">Resources</span>
                                @if($can('purchasing.view'))<a href="{{ route('purchasing.index') }}" class="{{ request()->routeIs('purchasing.*') ? 'active' : '' }}">Purchasing</a>@endif
                                @if($can('suppliers.view'))<a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">Suppliers</a>@endif
                                @if($can('inventory.view'))<a href="{{ route('inventory.index') }}" class="{{ request()->routeIs('inventory.*') ? 'active' : '' }}">Inventory</a>@endif
                                @if($can('assets.view'))<a href="{{ route('assets.index') }}" class="{{ request()->routeIs('assets.*') ? 'active' : '' }}">Assets</a>@endif
                            @elseif(request()->routeIs('reports.*'))
                                <span class="zazu-section-name">Insights</span><a href="{{ route('reports.index') }}" class="active">Reports</a>
                            @elseif(request()->routeIs('settings.*'))
                                <span class="zazu-section-name">System</span><a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.index') ? 'active' : '' }}">Business</a>
                                @if($isOwner)<a href="{{ route('settings.compliance') }}" class="{{ request()->routeIs('settings.compliance*') ? 'active' : '' }}">Compliance</a><a href="{{ route('settings.audit') }}" class="{{ request()->routeIs('settings.audit') ? 'active' : '' }}">Audit</a>@endif
                            @endif
                        </nav>
                    </div>

                    <div class="zazu-topbar-actions">
                        <div class="zazu-command-search" data-zazu-command-search>
                            <label class="zazu-command-trigger" for="zazu-command-input">
                                <span class="zazu-command-search-icon" aria-hidden="true">⌕</span>
                                <span class="sr-only">Search the workspace</span>
                                <input
                                    id="zazu-command-input"
                                    type="search"
                                    data-zazu-command-input
                                    data-search-url="{{ route('search.index') }}"
                                    placeholder="Search the workspace…"
                                    autocomplete="off"
                                    spellcheck="false"
                                    role="combobox"
                                    aria-autocomplete="list"
                                    aria-controls="zazu-command-palette"
                                    aria-expanded="false"
                                >
                                <kbd>⌘K</kbd>
                            </label>
            <div class="zazu-command-palette" id="zazu-command-palette" data-zazu-command hidden>
                <div class="zazu-command-results-head">
                    <span>Quick access</span>
                    <small data-zazu-command-count>Workspace destinations</small>
                </div>
                <div class="zazu-command-results" data-zazu-command-results role="listbox" aria-label="Quick access and workspace search">
                    <a href="{{ route('search.index') }}" data-command-item data-search-terms="search find records workspace customers jobs suppliers quotes invoices purchasing finance advanced filters" data-command-search-action role="option"><span data-command-search-label>Search workspace</span><small data-command-search-meta>Find business records · filters and date range</small></a>
                    @if($can('work.view'))<a href="{{ route('work.index') }}" data-command-item data-search-terms="jobs work events operations event management schedule run sheet" role="option"><span>Jobs</span><small>Event operations · work register</small></a>@endif
                    @if($can('capabilities.view'))<a href="{{ route('capabilities.index') }}" data-command-item data-search-terms="services prices catalogue capabilities catering food packages equipment hire rates" role="option"><span>Services & prices</span><small>Capability catalogue · rates and packages</small></a>@endif
                    @if($can('quotes.view'))<a href="{{ route('quotes.index') }}" data-command-item data-search-terms="quotes quotation quotations commercial documents proposals" role="option"><span>Quotes</span><small>Commercial documents · quote queue</small></a>@endif
                    @if($can('customers.view'))<a href="{{ route('customers.index') }}" data-command-item data-search-terms="customers clients contacts people organisations companies" role="option"><span>Customers</span><small>Client records · people and organisations</small></a>@endif
                    @if($can('calendar.view'))<a href="{{ route('calendar.index') }}" data-command-item data-search-terms="calendar schedule planning dates events" role="option"><span>Calendar</span><small>Planning · scheduled work</small></a>@endif
                    @if($can('finance.view'))<a href="{{ route('finance.index') }}" data-command-item data-search-terms="finance invoices payments expenses ledger money accounting" role="option"><span>Finance</span><small>Ledger · invoices, payments and expenses</small></a>@endif
                    @if($can('purchasing.view'))<a href="{{ route('purchasing.index') }}" data-command-item data-search-terms="purchasing purchase orders procurement buying suppliers supply" role="option"><span>Purchasing</span><small>Purchase orders · procurement</small></a>@endif
                    @if($can('suppliers.view'))<a href="{{ route('suppliers.index') }}" data-command-item data-search-terms="suppliers vendors procurement contacts supply" role="option"><span>Suppliers</span><small>Supplier register · procurement contacts</small></a>@endif
                    @if($can('inventory.view'))<a href="{{ route('inventory.index') }}" data-command-item data-search-terms="inventory stock warehouse quantities items goods" role="option"><span>Inventory</span><small>Stock control · warehouse records</small></a>@endif
                    @if($can('assets.view'))<a href="{{ route('assets.index') }}" data-command-item data-search-terms="assets equipment hire resources allocation availability register" role="option"><span>Assets</span><small>Equipment register · resource readiness</small></a>@endif
                    @if($can('reports.view'))<a href="{{ route('reports.index') }}" data-command-item data-search-terms="reports reporting analytics insights business intelligence performance" role="option"><span>Reports</span><small>Insights · business reporting</small></a>@endif
                    @if($isOwner)<a href="{{ route('settings.index') }}" data-command-item data-search-terms="settings business configuration company profile tax branding system" role="option"><span>Business settings</span><small>Workspace configuration · business identity</small></a>
                    <a href="{{ route('settings.compliance') }}" data-command-item data-search-terms="compliance tax vat legal documents statutory finance" role="option"><span>Compliance</span><small>Compliance pack · controls and records</small></a>
                    <a href="{{ route('settings.audit') }}" data-command-item data-search-terms="audit log activity history accountability changes security" role="option"><span>Audit</span><small>Audit trail · system activity</small></a>
                    <a href="{{ route('onboarding.index') }}" data-command-item data-search-terms="setup onboarding business setup catalogue setup foundation" role="option"><span>Setup centre</span><small>Workspace foundation · business and catalogue setup</small></a>@endif
                </div>
                <div class="zazu-command-empty" data-zazu-command-empty hidden>
                    No matching destinations. Press Enter to search workspace records.
                </div>
            </div>
                        </div>
@isset($headerAction)
                            <div class="zazu-header-slot-actions">
                                {{ $headerAction }}
                            </div>
                        @endisset



                        <button type="button" class="zazu-theme-toggle" data-theme-toggle aria-pressed="false">
                            <svg data-theme-icon-sun viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path>
                            </svg>
                            <svg data-theme-icon-moon viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" hidden>
                                <path d="M20 15.5A8 8 0 0 1 8.5 4 8.5 8.5 0 1 0 20 15.5Z"></path>
                            </svg>
                        </button>

                    </div>
                </div>
            </header>

<div class="zazu-content">
                @if ($errors->any())
                    <div class="zazu-error-summary" role="alert" tabindex="-1" data-error-summary>
                        <div class="zazu-error-summary-title">Please check the highlighted fields.</div>
                        <ul class="zazu-error-summary-list" data-error-summary-list></ul>
                    </div>
                @endif

                @if (session('success') || session('error') || session('info'))
                    @php
                        $toastType = session('error') ? 'error' : (session('info') ? 'info' : 'success');
                        $toastTitle = $toastType === 'error' ? 'Action needed' : ($toastType === 'info' ? 'Zazu' : 'Saved');
                        $toastMessage = session('error') ?? session('info') ?? session('success');
                    @endphp
                    <div class="zazu-toast zazu-toast-{{ $toastType }}" role="{{ $toastType === 'error' ? 'alert' : 'status' }}" data-zazu-toast>
                        <div class="zazu-toast-icon" aria-hidden="true">
                            @if ($toastType === 'success') ✓ @elseif ($toastType === 'error') ! @else i @endif
                        </div>
                        <div class="zazu-toast-content">
                            <div class="zazu-toast-title">{{ $toastTitle }}</div>
                            <div class="zazu-toast-message">{{ $toastMessage }}</div>
                        </div>
                        @if (session('toast_action_url') && session('toast_action_label'))
                            <a href="{{ session('toast_action_url') }}" class="zazu-toast-action">{{ session('toast_action_label') }}</a>
                        @endif
                        <button type="button" class="zazu-toast-close" data-zazu-toast-close aria-label="Dismiss notification">×</button>
                    </div>
                @endif

                <x-zazu-helper />

                {{ $slot }}
            </div>

            <footer class="zazu-app-footer {{ $isOwner ? 'has-system-links' : 'without-system-links' }}">
                <div class="zazu-app-footer-inner">
                    <div class="zazu-app-footer-brand">
                        <div class="zazu-app-footer-lockup">
                            <span class="zazu-app-footer-mark" aria-hidden="true">Z</span>
                            <div>
                                <strong>ZAZU EMP</strong>
                                <span>Event &amp; catering management</span>
                            </div>
                        </div>
                        <p>One operational workspace for jobs, resources, purchasing and finance.</p>
                    </div>

                    <nav class="zazu-app-footer-nav" aria-label="Operations navigation">
                        <h2 class="zazu-app-footer-heading">Operate</h2>
                        <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
                        @if($can('work.view'))<a href="{{ route('work.index') }}" @if(request()->routeIs('work.*')) aria-current="page" @endif>Jobs</a>@endif
                        @if($can('quotes.view'))<a href="{{ route('quotes.index') }}" @if(request()->routeIs('quotes.*')) aria-current="page" @endif>Quotes</a>@endif
                        @if($can('customers.view'))<a href="{{ route('customers.index') }}" @if(request()->routeIs('customers.*')) aria-current="page" @endif>Customers</a>@endif
                        @if($can('calendar.view'))<a href="{{ route('calendar.index') }}" @if(request()->routeIs('calendar.*')) aria-current="page" @endif>Calendar</a>@endif
                    </nav>

                    <nav class="zazu-app-footer-nav" aria-label="Resources navigation">
                        <h2 class="zazu-app-footer-heading">Resources</h2>
                        @if($can('finance.view'))<a href="{{ route('finance.index') }}" @if(request()->routeIs('finance.*')) aria-current="page" @endif>Finance</a>@endif
                        @if($can('purchasing.view'))<a href="{{ route('purchasing.index') }}" @if(request()->routeIs('purchasing.*')) aria-current="page" @endif>Purchasing</a>@endif
                        @if($can('suppliers.view'))<a href="{{ route('suppliers.index') }}" @if(request()->routeIs('suppliers.*')) aria-current="page" @endif>Suppliers</a>@endif
                        @if($can('inventory.view'))<a href="{{ route('inventory.index') }}" @if(request()->routeIs('inventory.*')) aria-current="page" @endif>Inventory</a>@endif
                        @if($can('assets.view'))<a href="{{ route('assets.index') }}" @if(request()->routeIs('assets.*')) aria-current="page" @endif>Assets</a>@endif
                    </nav>

                    <nav class="zazu-app-footer-nav zazu-app-footer-control" aria-label="Control navigation">
                        <h2 class="zazu-app-footer-heading">Control</h2>
                        @if($can('reports.view'))<a href="{{ route('reports.index') }}" @if(request()->routeIs('reports.*')) aria-current="page" @endif>Reports</a>@endif
                        <a href="{{ route('search.index') }}" @if(request()->routeIs('search.index')) aria-current="page" @endif>Search</a>
                        @if($isOwner)
                            <a href="{{ route('settings.index') }}" @if(request()->routeIs('settings.index')) aria-current="page" @endif>Business settings</a>
                            <a href="{{ route('settings.audit') }}" @if(request()->routeIs('settings.audit')) aria-current="page" @endif>Activity audit</a>
                            <a href="{{ route('onboarding.index') }}" @if(request()->routeIs('onboarding.*')) aria-current="page" @endif>Setup centre</a>
                        @endif
                    </nav>

                    <div class="zazu-app-footer-meta">
                        <span class="zazu-app-footer-heading">Workspace</span>
                        <strong>{{ $business?->name ?? 'Zazu EMP' }}</strong>
                        <span>{{ ucfirst(str_replace('_', ' ', $currentRole ?? 'member')) }} · {{ auth()->user()->name }}</span>
                    </div>
                </div>
                <div class="zazu-app-footer-bottom">
                    <span>&copy; {{ now()->year }} Zazu EMP. All rights reserved.</span>
                    <span>Search: <kbd>Ctrl</kbd>/<kbd>⌘</kbd> K</span>
                    <span>Built for practical event operations.</span>
                </div>
            </footer>
        </main>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const command = document.querySelector('[data-zazu-command]');
        const commandInput = document.querySelector('[data-zazu-command-input]');
        const commandItems = [...(command?.querySelectorAll('[data-command-item]') || [])];
        const commandCount = command?.querySelector('[data-zazu-command-count]');
        const commandEmpty = command?.querySelector('[data-zazu-command-empty]');
        const commandSearchAction = command?.querySelector('[data-command-search-action]');
        const commandSearchLabel = command?.querySelector('[data-command-search-label]');
        const commandSearchMeta = command?.querySelector('[data-command-search-meta]');
        const commandSearch = document.querySelector('[data-zazu-command-search]');
        let commandOpen = false;

        const setCommandOpen = (open) => {
            if (!command || !commandInput) return;
            commandOpen = open;
            command.hidden = !open;
            commandInput.setAttribute('aria-expanded', open ? 'true' : 'false');
            commandSearch?.classList.toggle('is-open', open);
        };

        const filterCommandItems = () => {
            if (!command || !commandInput) return;
            const rawQuery = commandInput.value.trim();
            const query = rawQuery.toLowerCase();
            let visible = 0;

            commandItems.forEach((item) => {
                const haystack = (item.dataset.searchTerms || item.textContent || '').toLowerCase();
                const match = !query || haystack.includes(query);
                item.hidden = !match;
                if (match) visible += 1;
            });

            if (commandCount) {
                commandCount.textContent = query
                    ? visible + ' matching destination' + (visible === 1 ? '' : 's')
                    : 'Workspace destinations';
            }
            if (commandSearchLabel) {
                commandSearchLabel.textContent = rawQuery
                    ? 'Search workspace for “' + rawQuery + '”'
                    : 'Search workspace';
            }
            if (commandSearchMeta) {
                commandSearchMeta.textContent = query
                    ? 'Open full results and refine with filters'
                    : 'Find business records · filters and date range';
            }
            if (commandSearchAction) {
                commandSearchAction.hidden = false;
            }
            if (commandEmpty) commandEmpty.hidden = visible !== 0;
        };

        const openCommand = () => {
            if (!commandInput) return;
            setCommandOpen(true);
            filterCommandItems();
        };

        const closeCommand = (clear = false) => {
            if (!commandInput) return;
            setCommandOpen(false);
            if (clear) {
                commandInput.value = '';
                filterCommandItems();
            }
        };

        commandInput?.addEventListener('focus', openCommand);
        commandInput?.addEventListener('input', () => {
            openCommand();
            filterCommandItems();
        });

        commandItems.forEach((item) => item.addEventListener('click', () => {
            window.setTimeout(() => closeCommand(true), 0);
        }));

        commandInput?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                const query = commandInput.value.trim();
                const searchUrl = commandInput.dataset.searchUrl;

                if (searchUrl) {
                    event.preventDefault();
                    const target = new URL(searchUrl, window.location.origin);
                    if (query) target.searchParams.set('q', query);
                    window.location.assign(target.toString());
                }
            } else if (event.key === 'ArrowDown') {
                const first = commandItems.find((item) => !item.hidden);
                if (first) {
                    event.preventDefault();
                    first.focus();
                }
            } else if (event.key === 'Escape') {
                event.preventDefault();
                closeCommand();
                commandInput.blur();
            }
        });

        document.addEventListener('click', (event) => {
            if (commandOpen && commandSearch && !commandSearch.contains(event.target) && !command?.contains(event.target)) {
                closeCommand();
            }
        });

        document.addEventListener('keydown', (event) => {
            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                commandInput?.focus();
                openCommand();
            }
        });

        filterCommandItems();

        const serverErrors = @json($errors->toArray());

        document.querySelectorAll('.zazu-field').forEach((field) => {
            const control = field.querySelector('input, select, textarea');
            if (!control) return;

            const name = (control.getAttribute('name') || '').toLowerCase();
            const dottedName = name.replace(/\[([^\]]+)\]/g, '.$1');
            const baseName = name.replace(/\[.*?\]/g, '');
            const messages = serverErrors[name] || serverErrors[dottedName] || serverErrors[baseName] || [];

            if (control.required) {
                control.setAttribute('aria-required', 'true');

                const label = field.querySelector('.zazu-label');
                if (label && !label.querySelector('.zazu-required')) {
                    const marker = document.createElement('span');
                    marker.className = 'zazu-required';
                    marker.setAttribute('aria-hidden', 'true');
                    marker.textContent = ' *';
                    label.appendChild(marker);
                }
            }

            if (messages.length && !field.querySelector('.zazu-field-error')) {
                const error = document.createElement('span');
                error.className = 'zazu-field-error';
                error.textContent = messages[0];
                field.appendChild(error);
            }

            const error = field.querySelector('.zazu-field-error');
            if (error) {
                const errorId = control.id ? control.id + '-error' : 'zazu-error-' + baseName.replace(/[^a-z0-9_-]/g, '-');
                error.id = errorId;
                control.setAttribute('aria-invalid', 'true');
                control.setAttribute('aria-describedby', errorId);
            }

            if (name.includes('phone')) {
                control.setAttribute('inputmode', 'tel');
                control.setAttribute('autocomplete', 'tel');
            } else if (control.type === 'email' || name.includes('email')) {
                control.setAttribute('inputmode', 'email');
                control.setAttribute('autocomplete', 'email');
            } else if (control.type === 'number') {
                control.setAttribute('inputmode', 'decimal');
            }
        });

        const summary = document.querySelector('[data-error-summary]');
        const list = document.querySelector('[data-error-summary-list]');

        if (summary && list) {
            document.querySelectorAll('.zazu-field-error').forEach((error) => {
                const field = error.closest('.zazu-field');
                const control = field?.querySelector('input, select, textarea');
                if (!control || !error.textContent.trim()) return;

                if (!control.id) {
                    const name = control.getAttribute('name') || 'field';
                    control.id = 'zazu-field-' + name.replace(/[^a-z0-9_-]/gi, '-');
                }

                if ([...list.children].some((item) => item.dataset.forField === control.id)) return;

                const item = document.createElement('li');
                item.dataset.forField = control.id;

                const link = document.createElement('a');
                link.href = '#' + control.id;
                link.textContent = error.textContent.trim();

                item.appendChild(link);
                list.appendChild(item);
            });

            if (list.children.length) {
                summary.hidden = false;
                summary.focus();
            }
        }
    });
</script>
</body>
</html>
