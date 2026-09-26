<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Zazu' }} · Zazu EMP</title>
    <script>
        (() => {
            const saved = localStorage.getItem('zazu-theme');
            const theme = saved === 'light' || saved === 'dark'
                ? saved
                : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

            document.documentElement.dataset.theme = theme;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="zazu-shell">
        <aside class="zazu-sidebar">
            <div class="zazu-brand">
                <a href="{{ route('work.index') }}" class="zazu-brand-word">zazu<span class="zazu-brand-dot">.</span></a>
            </div>

            <nav class="zazu-nav" aria-label="Primary">
                <div class="zazu-nav-group">
                    <div class="zazu-nav-label">Overview</div>
                    <a href="{{ route('dashboard') }}" class="zazu-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="4" y="4" width="7" height="7" rx="1"></rect><rect x="13" y="4" width="7" height="4" rx="1"></rect><rect x="13" y="10" width="7" height="10" rx="1"></rect><rect x="4" y="13" width="7" height="7" rx="1"></rect></svg>
                        <span>Dashboard</span>
                    </a>
                </div>

                <div class="zazu-nav-group">
                    <div class="zazu-nav-label">Operations</div>
                    <a href="{{ route('work.index') }}" class="zazu-nav-link {{ request()->routeIs('work.*') ? 'active' : '' }}" @if (request()->routeIs('work.*')) aria-current="page" @endif>
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="4" y="4" width="6" height="6" rx="1"></rect><rect x="14" y="4" width="6" height="6" rx="1"></rect><rect x="4" y="14" width="6" height="6" rx="1"></rect><rect x="14" y="14" width="6" height="6" rx="1"></rect></svg>
                        <span>Work</span>
                    </a>
                    <a href="{{ route('calendar.index') }}" class="zazu-nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}" @if (request()->routeIs('calendar.*')) aria-current="page" @endif>
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2"></rect><path d="M8 3v4M16 3v4M4 10h16"></path></svg>
                        <span>Calendar</span>
                    </a>
                </div>

                <div class="zazu-nav-group">
                    <div class="zazu-nav-label">Relationships</div>
                    <a href="{{ route('customers.index') }}" class="zazu-nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" @if (request()->routeIs('customers.*')) aria-current="page" @endif>
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="9" cy="8" r="3"></circle><path d="M4 19c.7-3 2.3-4.5 5-4.5s4.3 1.5 5 4.5"></path><path d="M16 11.5c2.2.2 3.5 1.6 4 4"></path><path d="M15.5 5.4a3 3 0 0 1 0 5.1"></path></svg>
                        <span>Customers</span>
                    </a>
                </div>

                <div class="zazu-nav-group">
                    <div class="zazu-nav-label">Commercial</div>
                    <a href="{{ route('quotes.index') }}" class="zazu-nav-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}" @if (request()->routeIs('quotes.*')) aria-current="page" @endif>
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M6 4h12v16H6z"></path><path d="M9 8h6M9 12h6M9 16h4"></path></svg>
                        <span>Quotes</span>
                    </a>
                </div>

                <div class="zazu-nav-group">
                    <div class="zazu-nav-label">Resources</div>
                    <a href="{{ route('suppliers.index') }}" class="zazu-nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" @if (request()->routeIs('suppliers.*')) aria-current="page" @endif>
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M5 9h14v10H5z"></path><path d="M8 9V6h8v3M9 13h6"></path></svg>
                        <span>Suppliers</span>
                    </a>
                    <a href="{{ route('inventory.index') }}" class="zazu-nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" @if (request()->routeIs('inventory.*')) aria-current="page" @endif>
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M4 7h16M6 4h12v16H6z"></path><path d="M9 11h6M9 15h6"></path></svg>
                        <span>Inventory</span>
                    </a>
                    <a href="{{ route('assets.index') }}" class="zazu-nav-link {{ request()->routeIs('assets.*') ? 'active' : '' }}" @if (request()->routeIs('assets.*')) aria-current="page" @endif>
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="7"></circle><path d="M12 8v8M8 12h8"></path></svg>
                        <span>Assets</span>
                    </a>
                </div>

                <div class="zazu-nav-group">
                    <div class="zazu-nav-label">Insights</div>
                    <a href="{{ route('reports.index') }}" class="zazu-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" @if (request()->routeIs('reports.*')) aria-current="page" @endif>
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M5 19V9M12 19V5M19 19V12"></path></svg>
                        <span>Reports</span>
                    </a>
                </div>

                <div class="zazu-nav-group">
                    <div class="zazu-nav-label">Catalogue</div>
                    <a href="{{ route('capabilities.index') }}" class="zazu-nav-link {{ request()->routeIs('capabilities.*') ? 'active' : '' }}" @if (request()->routeIs('capabilities.*')) aria-current="page" @endif>
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M6 5.5A2.5 2.5 0 0 1 8.5 3H20v15.5A2.5 2.5 0 0 1 17.5 21H8.5A2.5 2.5 0 0 1 6 18.5z"></path><path d="M6 6h10.5A2.5 2.5 0 0 1 19 8.5V21"></path><path d="M10 8.5h5M10 12h5"></path></svg>
                        <span>Capabilities</span>
                    </a>
                </div>

                <div class="zazu-nav-group">
                    <div class="zazu-nav-label">System</div>
                    <a href="{{ route('settings.index') }}" class="zazu-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" @if (request()->routeIs('settings.*')) aria-current="page" @endif>
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M19 12a7 7 0 0 0-.3-2l2-1.3-2-3.4-2.3 1a7 7 0 0 0-3.4-2L12.7 2h-1.4L11 4.3a7 7 0 0 0-3.4 2l-2.3-1-2 3.4 2 1.3a7 7 0 0 0 0 4L3.3 15.3l2 3.4 2.3-1a7 7 0 0 0 3.4 2l.3 2.3h1.4l.3-2.3a7 7 0 0 0 3.4-2l2.3 1 2-3.4-2-1.3a7 7 0 0 0 .3-2z"></path></svg>
                        <span>Settings</span>
                    </a>
                </div>
            </nav>

            <div class="zazu-sidebar-footer">
                <div class="zazu-footer-card">
                    <div class="zazu-footer-title">Zazu EMP</div>
                    <div class="zazu-footer-copy">Event operations workspace</div>
                </div>
            </div>
        </aside>

        <main class="zazu-main">
            <header class="zazu-topbar">
                <div class="zazu-topbar-inner">
                    <div>
                        <div class="zazu-eyebrow">Zazu EMP</div>
                        <h1 class="zazu-page-title">{{ $heading ?? $title ?? 'Workspace' }}</h1>
                    </div>

                    <div class="zazu-topbar-actions">
                        @isset($headerAction)
                            {{ $headerAction }}
                        @endisset

                        <button type="button" class="zazu-theme-toggle" data-theme-toggle aria-pressed="false">
                            <span data-theme-icon aria-hidden="true">◐</span>
                            <span data-theme-label>Dark</span>
                        </button>
                    </div>
                </div>
            </header>

            <nav class="zazu-mobile-nav" aria-label="Mobile primary">
                <a href="{{ route('dashboard') }}" class="zazu-mobile-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
                <a href="{{ route('work.index') }}" class="zazu-mobile-link {{ request()->routeIs('work.*') ? 'active' : '' }}" @if (request()->routeIs('work.*')) aria-current="page" @endif>Work</a>
                <a href="{{ route('customers.index') }}" class="zazu-mobile-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" @if (request()->routeIs('customers.*')) aria-current="page" @endif>Customers</a>
                <a href="{{ route('quotes.index') }}" class="zazu-mobile-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}" @if (request()->routeIs('quotes.*')) aria-current="page" @endif>Quotes</a>
                <a href="{{ route('calendar.index') }}" class="zazu-mobile-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}" @if (request()->routeIs('calendar.*')) aria-current="page" @endif>Calendar</a>
                <a href="{{ route('suppliers.index') }}" class="zazu-mobile-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" @if (request()->routeIs('suppliers.*')) aria-current="page" @endif>Suppliers</a>
                <a href="{{ route('inventory.index') }}" class="zazu-mobile-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" @if (request()->routeIs('inventory.*')) aria-current="page" @endif>Inventory</a>
                <a href="{{ route('assets.index') }}" class="zazu-mobile-link {{ request()->routeIs('assets.*') ? 'active' : '' }}" @if (request()->routeIs('assets.*')) aria-current="page" @endif>Assets</a>
                <a href="{{ route('reports.index') }}" class="zazu-mobile-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" @if (request()->routeIs('reports.*')) aria-current="page" @endif>Reports</a>
                <a href="{{ route('capabilities.index') }}" class="zazu-mobile-link {{ request()->routeIs('capabilities.*') ? 'active' : '' }}" @if (request()->routeIs('capabilities.*')) aria-current="page" @endif>Capabilities</a>
                <a href="{{ route('settings.index') }}" class="zazu-mobile-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" @if (request()->routeIs('settings.*')) aria-current="page" @endif>Settings</a>
            </nav>

            <div class="zazu-content">
                @if (session('success'))
                    <div class="zazu-alert zazu-alert-success" role="status">
                        <strong>Saved.</strong>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="zazu-alert zazu-alert-error" role="alert">
                        <strong>Action needed.</strong>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="zazu-alert zazu-alert-error" role="alert">
                        <strong>Check this record.</strong>
                        <span>Please correct the highlighted information and try again.</span>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
