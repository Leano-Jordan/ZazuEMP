<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#F8FAFC">
    @php
        $business = app(\App\Support\CurrentBusiness::class)->resolve(auth()->user());
        $businesses = auth()->user()->businesses()->where('businesses.status', 'active')->orderBy('businesses.name')->get();
        $currentMembership = $businesses->firstWhere('id', $business?->id);
        $currentRole = $currentMembership?->pivot?->role;
        $can = fn (string $permission): bool => $currentRole === 'owner'
            || in_array($permission, config('zazu.permissions.roles.'.$currentRole, []), true);
        $isOwner = $currentRole === 'owner';
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
    @vite(['resources/css/app.css', 'resources/css/zazu-responsive-theme.css', 'resources/css/zazu-final-visual-sweep.css', 'resources/js/app.js'])
</head>
<body data-zazu-route="{{ request()->route()?->getName() ?? '' }}" class="{{ $business?->wallpaper_path ? 'zazu-has-wallpaper' : '' }}" data-business-currency="{{ $business?->currency ?? 'ZAR' }}" @if($business?->wallpaper_path) style="--zazu-wallpaper: url('{{ e(route('business.media', ['type' => 'wallpaper']).'?v='.$brandingVersion) }}')" @endif>
    <a class="zazu-skip-link" href="#main-content">Skip to main content</a>
    <div class="zazu-shell">
        <aside class="zazu-sidebar">
            <div class="zazu-brand">
                <a href="{{ route('dashboard') }}" class="zazu-brand-link" aria-label="Zazu EMP dashboard">
                    <span class="zazu-brand-mark" aria-hidden="true">Z</span>
                    <span class="zazu-brand-lockup">
                        <strong>ZAZU</strong>
                        <span>EMP</span>
                    </span>
                </a>
            </div>

            <nav class="zazu-nav" aria-label="Primary">
                <div class="zazu-sidebar-workspace">
                    <div class="zazu-sidebar-workspace-label">Workspace</div>
                    <div class="zazu-sidebar-workspace-name">{{ $business?->name ?? 'Zazu EMP' }}</div>
                    <div class="zazu-sidebar-workspace-meta"><span class="zazu-sidebar-workspace-dot" aria-hidden="true"></span><span>Online · Event Operations</span></div>
                </div>
                <div class="zazu-nav-stack zazu-nav-primary">
                    <a href="{{ route('dashboard') }}" title="Dashboard" class="zazu-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif><span class="zazu-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="4" y="4" width="6" height="6" rx="1"></rect><rect x="14" y="4" width="6" height="6" rx="1"></rect><rect x="4" y="14" width="6" height="6" rx="1"></rect><rect x="14" y="14" width="6" height="6" rx="1"></rect></svg></span><span class="zazu-nav-label">Dashboard</span></a>
                    @if($can('work.view') || $can('capabilities.view') || $can('quotes.view') || $can('calendar.view'))
                        <a href="{{ $can('work.view') ? route('work.index') : ($can('capabilities.view') ? route('capabilities.index') : ($can('quotes.view') ? route('quotes.index') : route('calendar.index'))) }}" class="zazu-nav-link {{ request()->routeIs('work.*','capabilities.*','quotes.*','calendar.*') ? 'active' : '' }}"><span class="zazu-nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19V9m7 10V5m7 14v-7"></path><path d="M3 19h18"></path></svg></span><span class="zazu-nav-label">Operations</span></a>
                    @endif
                    @if($can('customers.view'))
                        <a href="{{ route('customers.index') }}" class="zazu-nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"><span class="zazu-nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"></circle><path d="M3.5 19a5.5 5.5 0 0 1 11 0"></path><path d="M16 11a3 3 0 0 1 4 2.8M16 16.5a5 5 0 0 1 4.5 2.5"></path></svg></span><span class="zazu-nav-label">Customers</span></a>
                    @endif
                    @if($can('finance.view'))
                        <a href="{{ route('finance.index') }}" class="zazu-nav-link {{ request()->routeIs('finance.*') ? 'active' : '' }}"><span class="zazu-nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16v12H4z"></path><path d="M8 7V5h8v2M4 11h16"></path><path d="M9 15h6"></path></svg></span><span class="zazu-nav-label">Finance</span></a>
                    @endif
                    @if($can('purchasing.view') || $can('suppliers.view') || $can('inventory.view') || $can('assets.view'))
                        <a href="{{ $can('purchasing.view') ? route('purchasing.index') : ($can('suppliers.view') ? route('suppliers.index') : ($can('inventory.view') ? route('inventory.index') : route('assets.index'))) }}" class="zazu-nav-link {{ request()->routeIs('purchasing.*','suppliers.*','inventory.*','assets.*') ? 'active' : '' }}"><span class="zazu-nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 8 8-4 8 4-8 4-8-4Z"></path><path d="m4 12 8 4 8-4M4 16l8 4 8-4"></path></svg></span><span class="zazu-nav-label">Resources</span></a>
                    @endif
                    @if($can('reports.view'))
                        <a href="{{ route('reports.index') }}" class="zazu-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><span class="zazu-nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19V9M12 19V5M19 19v-7"></path></svg></span><span class="zazu-nav-label">Reports</span></a>
                    @endif
                    @if($isOwner)
                        <a href="{{ route('settings.index') }}" class="zazu-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"><span class="zazu-nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M19 12a7 7 0 0 0-.3-2l-2-1.3-2-3.4-2.3 1a7 7 0 0 0-3.4-2L11 4.3 12 2l1 2.3a7 7 0 0 0 3.4 2l2.3-1 2 3.4-2 1.3a7 7 0 0 0 0 4l2 1.3-2 3.4-2.3-1a7 7 0 0 0-3.4 2L13 22l-2-2.3a7 7 0 0 0-3.4-2l-2.3 1-2-3.4 2-1.3a7 7 0 0 0 0-4L3.3 8.7l2-3.4 2.3 1a7 7 0 0 0 3.4-2L11 2"></path></svg></span><span class="zazu-nav-label">Settings</span></a>
                    @endif
                </div>
            </nav>

            <div class="zazu-sidebar-footer">
                <div class="zazu-footer-card">
                    <div class="zazu-footer-title">{{ $business?->name ?? 'Zazu EMP' }}</div>
                    <div class="zazu-footer-copy">Event operations workspace</div>
                </div>
            </div>
        </aside>

        <main id="main-content" class="zazu-main" tabindex="-1">
            <header class="zazu-topbar">
                <div class="zazu-topbar-inner">
                    <div class="zazu-header-context">
                        <div class="zazu-workspace-switcher" title="Active workspace">
                            <span class="zazu-workspace-pulse" aria-hidden="true"></span>
                            <span>{{ $business?->name ?? 'Zazu EMP' }} <span aria-hidden="true">›</span> Event Operations</span>
                            <span class="zazu-workspace-online">Online</span>
                        </div>
                        <div class="zazu-header-title-row">
                            <h1 class="zazu-page-title">{{ $heading ?? $title ?? 'Workspace' }}</h1>
                            @if($business)<span class="zazu-workspace-name" title="Active business workspace">{{ $business->name }}</span>@endif
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
                        <button type="button" class="zazu-command-trigger" data-zazu-command-open aria-haspopup="dialog" aria-controls="zazu-command-palette">
                            <span class="zazu-command-search-icon" aria-hidden="true">⌕</span>
                            <span class="zazu-command-placeholder">Search jobs, gear, or quotes…</span>
                            <kbd>⌘K</kbd>
                        </button>

                        @if($can('work.create'))
                            <a href="{{ route('work.create') }}" class="zazu-btn zazu-btn-primary zazu-header-create"><span aria-hidden="true">+</span> Create work</a>
                        @endif

                        @isset($headerAction)
                            {{ $headerAction }}
                        @endisset

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
                                        <strong>{{ '@'.auth()->user()->username }}</strong>
                                        <span>{{ auth()->user()->name }}</span>
                                    </span>
                                    <svg class="zazu-user-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m7 9 5 5 5-5"></path></svg>
                                </button>

                                <div class="zazu-user-popover" id="zazu-user-menu" data-user-popover hidden>
                                    <div class="zazu-user-popover-head">
                                        <x-profile-avatar :name="auth()->user()->name" :path="auth()->user()->profile_photo_path" media-type="user" :media-id="auth()->id()" size="md" />
                                        <div class="min-w-0">
                                            <strong class="zazu-user-popover-name">{{ '@'.auth()->user()->username }}</strong>
                                            <span class="zazu-user-popover-email">{{ auth()->user()->name }}</span>
                                            <span class="zazu-user-popover-email">{{ auth()->user()->email }}</span>
                                        </div>
                                    </div>
                                    @if($businesses->count() > 1)
                                        <div class="zazu-user-popover-section">
                                            <span class="zazu-user-popover-label">Workspace</span>
                                            <form method="POST" action="{{ route('business.switch') }}" class="zazu-user-switch-form">
                                                @csrf
                                                <label class="sr-only" for="zazu-business-switch">Current workspace</label>
                                                <select id="zazu-business-switch" name="business_id" class="zazu-user-switch-select" data-business-switch>
                                                    @foreach($businesses as $availableBusiness)
                                                        <option value="{{ $availableBusiness->id }}" @selected((int) $availableBusiness->id === (int) $business->id)>{{ $availableBusiness->name }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </div>
                                    @endif
                                    <div class="zazu-user-popover-divider"></div>
                                    @if($isOwner)
                                    <a href="{{ route('onboarding.index') }}" class="zazu-user-signout zazu-user-link">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M19 12a7 7 0 0 0-.3-2l-2-1.3-2-3.4-2.3 1a7 7 0 0 0-3.4-2L12.7 2h-1.4L11 4.3a7 7 0 0 0-3.4 2l-2.3-1-2 3.4 2 1.3a7 7 0 0 0 0 4l-2 1.3 2 3.4 2.3-1a7 7 0 0 0 3.4 2l.3 2.3h1.4l.3-2.3a7 7 0 0 0 3.4-2l2.3 1 2-3.4-2-1.3A7 7 0 0 0 19 12z"></path></svg>
                                        <span>Setup centre</span>
                                    </a>

                                    @endif
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="zazu-user-signout">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 6V4h9v16h-9v-2"></path><path d="M4 12h11m-4-4 4 4-4 4"></path></svg>
                                            <span>Sign out</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endauth

                        <button type="button" class="zazu-theme-toggle" data-theme-toggle aria-pressed="false">
                            <svg data-theme-icon-sun viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path>
                            </svg>
                            <svg data-theme-icon-moon viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" hidden>
                                <path d="M20 15.5A8 8 0 0 1 8.5 4 8.5 8.5 0 1 0 20 15.5Z"></path>
                            </svg>
                        </button>

                        @if($isOwner)
                            <a href="{{ route('settings.index') }}" class="zazu-header-settings" aria-label="Open settings" title="Settings">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M19 12a7 7 0 0 0-.3-2l2-1.3-2-3.4-2.3 1a7 7 0 0 0-3.4-2L12.7 2h-1.4L11 4.3a7 7 0 0 0-3.4 2l-2.3-1-2 3.4 2 1.3a7 7 0 0 0 0 4L3.3 15.3l2 3.4 2.3-1a7 7 0 0 0 3.4 2l.3 2.3h1.4l.3-2.3a7 7 0 0 0 3.4-2l2.3 1 2-3.4-2-1.3a7 7 0 0 0 .3-2z"></path></svg>
                            </a>
                        @endif
                    </div>
                </div>
            </header>

            <nav class="zazu-mobile-nav" aria-label="Mobile primary" data-mobile-nav>
                <button type="button" class="zazu-mobile-toggle" data-mobile-nav-toggle aria-expanded="false" aria-controls="zazu-mobile-links">
                    <span>Open section navigation</span>
                    <svg class="zazu-mobile-toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                </button>
                <div class="zazu-mobile-links" id="zazu-mobile-links">
                    @if(request()->routeIs('work.*','capabilities.*','quotes.*','calendar.*'))
                        <div class="zazu-mobile-group">Operations</div>
                        @if($can('work.view'))<a href="{{ route('work.index') }}" class="zazu-mobile-link {{ request()->routeIs('work.*') ? 'active' : '' }}">Jobs</a>@endif
                        @if($can('capabilities.view'))<a href="{{ route('capabilities.index') }}" class="zazu-mobile-link {{ request()->routeIs('capabilities.*') ? 'active' : '' }}">Services & prices</a>@endif
                        @if($can('quotes.view'))<a href="{{ route('quotes.index') }}" class="zazu-mobile-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}">Quotes</a>@endif
                        @if($can('calendar.view'))<a href="{{ route('calendar.index') }}" class="zazu-mobile-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}">Calendar</a>@endif
                    @elseif(request()->routeIs('customers.*'))
                        <div class="zazu-mobile-group">Customers</div><a href="{{ route('customers.index') }}" class="zazu-mobile-link active">Customers</a>
                    @elseif(request()->routeIs('finance.*'))
                        <div class="zazu-mobile-group">Finance</div><a href="{{ route('finance.index') }}" class="zazu-mobile-link active">Finance</a>
                    @elseif(request()->routeIs('purchasing.*','suppliers.*','inventory.*','assets.*'))
                        <div class="zazu-mobile-group">Resources</div>
                        @if($can('purchasing.view'))<a href="{{ route('purchasing.index') }}" class="zazu-mobile-link {{ request()->routeIs('purchasing.*') ? 'active' : '' }}">Purchasing</a>@endif
                        @if($can('suppliers.view'))<a href="{{ route('suppliers.index') }}" class="zazu-mobile-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">Suppliers</a>@endif
                        @if($can('inventory.view'))<a href="{{ route('inventory.index') }}" class="zazu-mobile-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">Inventory</a>@endif
                        @if($can('assets.view'))<a href="{{ route('assets.index') }}" class="zazu-mobile-link {{ request()->routeIs('assets.*') ? 'active' : '' }}">Assets</a>@endif
                    @elseif(request()->routeIs('reports.*'))
                        <div class="zazu-mobile-group">Insights</div><a href="{{ route('reports.index') }}" class="zazu-mobile-link active">Reports</a>
                    @elseif(request()->routeIs('settings.*'))
                        <div class="zazu-mobile-group">System</div><a href="{{ route('settings.index') }}" class="zazu-mobile-link {{ request()->routeIs('settings.index') ? 'active' : '' }}">Business settings</a>
                        @if($isOwner)<a href="{{ route('settings.compliance') }}" class="zazu-mobile-link {{ request()->routeIs('settings.compliance*') ? 'active' : '' }}">Compliance</a><a href="{{ route('settings.audit') }}" class="zazu-mobile-link {{ request()->routeIs('settings.audit') ? 'active' : '' }}">Audit</a>@endif
                    @else
                        <div class="zazu-mobile-group">Workspace</div>
                        <a href="{{ route('dashboard') }}" class="zazu-mobile-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                        @if($can('customers.view'))<a href="{{ route('customers.index') }}" class="zazu-mobile-link">Customers</a>@endif
                        @if($can('finance.view'))<a href="{{ route('finance.index') }}" class="zazu-mobile-link">Finance</a>@endif
                        @if($can('reports.view'))<a href="{{ route('reports.index') }}" class="zazu-mobile-link">Reports</a>@endif
                    @endif
                </div>
            </nav>

            <div class="zazu-command-palette" id="zazu-command-palette" data-zazu-command hidden>
                <div class="zazu-command-backdrop" data-zazu-command-close></div>
                <div class="zazu-command-dialog" role="dialog" aria-modal="true" aria-labelledby="zazu-command-title">
                    <div class="zazu-command-head">
                        <div>
                            <div class="zazu-eyebrow">Workspace command</div>
                            <h2 id="zazu-command-title">Jump to a workspace surface</h2>
                        </div>
                        <button type="button" class="zazu-command-close" data-zazu-command-close aria-label="Close command search">×</button>
                    </div>
                    <label class="zazu-command-input-wrap">
                        <span aria-hidden="true">⌕</span>
                        <input type="search" data-zazu-command-input placeholder="Search jobs, services, quotes, customers…" autocomplete="off">
                    </label>
                    <div class="zazu-command-results" data-zazu-command-results>
                        @if($can('work.view'))<a href="{{ route('work.index') }}" data-command-item><span>Jobs</span><small>Event operations</small></a>@endif
                        @if($can('capabilities.view'))<a href="{{ route('capabilities.index') }}" data-command-item><span>Services & prices</span><small>Capability catalogue</small></a>@endif
                        @if($can('quotes.view'))<a href="{{ route('quotes.index') }}" data-command-item><span>Quotes</span><small>Commercial documents</small></a>@endif
                        @if($can('customers.view'))<a href="{{ route('customers.index') }}" data-command-item><span>Customers</span><small>Client records</small></a>@endif
                        @if($can('calendar.view'))<a href="{{ route('calendar.index') }}" data-command-item><span>Calendar</span><small>Planning</small></a>@endif
                        @if($can('finance.view'))<a href="{{ route('finance.index') }}" data-command-item><span>Finance</span><small>Ledger and payments</small></a>@endif
                        @if($can('assets.view'))<a href="{{ route('assets.index') }}" data-command-item><span>Assets</span><small>Equipment register</small></a>@endif
                        @if($can('reports.view'))<a href="{{ route('reports.index') }}" data-command-item><span>Reports</span><small>Business intelligence</small></a>@endif
                    </div>
                </div>
            </div>
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
        </main>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mobileNav = document.querySelector('[data-mobile-nav]');
        const mobileNavToggle = document.querySelector('[data-mobile-nav-toggle]');

        if (mobileNav && mobileNavToggle) {
            mobileNavToggle.addEventListener('click', () => {
                const open = mobileNav.classList.toggle('is-open');
                mobileNavToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });

            mobileNav.querySelectorAll('.zazu-mobile-link').forEach((link) => {
                link.addEventListener('click', () => {
                    mobileNav.classList.remove('is-open');
                    mobileNavToggle.setAttribute('aria-expanded', 'false');
                });
            });
        }

        const command = document.querySelector('[data-zazu-command]');
        const commandInput = command?.querySelector('[data-zazu-command-input]');
        const commandItems = [...(command?.querySelectorAll('[data-command-item]') || [])];
        const commandOpeners = document.querySelectorAll('[data-zazu-command-open]');
        const commandClosers = command?.querySelectorAll('[data-zazu-command-close]') || [];
        let commandReturnFocus = null;

        const closeCommand = () => {
            if (!command) return;
            command.hidden = true;
            document.body.classList.remove('zazu-command-open');
            commandReturnFocus?.focus();
        };

        const openCommand = () => {
            if (!command) return;
            commandReturnFocus = document.activeElement;
            command.hidden = false;
            document.body.classList.add('zazu-command-open');
            window.setTimeout(() => commandInput?.focus(), 0);
        };

        commandOpeners.forEach((button) => button.addEventListener('click', openCommand));
        commandClosers.forEach((button) => button.addEventListener('click', closeCommand));
        commandInput?.addEventListener('input', () => {
            const query = commandInput.value.trim().toLowerCase();
            commandItems.forEach((item) => {
                item.hidden = query !== '' && !item.textContent.toLowerCase().includes(query);
            });
        });

        document.addEventListener('keydown', (event) => {
            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                openCommand();
            }
            if (event.key === 'Escape' && command && !command.hidden) closeCommand();
        });

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
