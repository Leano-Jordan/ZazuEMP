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
                    <div class="zazu-nav-label">Operations</div>
                    <a href="{{ route('work.index') }}" class="zazu-nav-link {{ request()->routeIs('work.*') ? 'active' : '' }}">
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <rect x="4" y="4" width="6" height="6" rx="1"></rect>
                            <rect x="14" y="4" width="6" height="6" rx="1"></rect>
                            <rect x="4" y="14" width="6" height="6" rx="1"></rect>
                            <rect x="14" y="14" width="6" height="6" rx="1"></rect>
                        </svg>
                        <span>Work</span>
                    </a>
                </div>

                <div class="zazu-nav-group">
                    <div class="zazu-nav-label">Relationships</div>
                    <a href="{{ route('customers.index') }}" class="zazu-nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <circle cx="9" cy="8" r="3"></circle>
                            <path d="M4 19c.7-3 2.3-4.5 5-4.5s4.3 1.5 5 4.5"></path>
                            <path d="M16 11.5c2.2.2 3.5 1.6 4 4"></path>
                            <path d="M15.5 5.4a3 3 0 0 1 0 5.1"></path>
                        </svg>
                        <span>Customers</span>
                    </a>
                </div>

                <div class="zazu-nav-group">
                    <div class="zazu-nav-label">Catalogue</div>
                    <a href="{{ route('capabilities.index') }}" class="zazu-nav-link {{ request()->routeIs('capabilities.*') ? 'active' : '' }}">
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path d="M6 5.5A2.5 2.5 0 0 1 8.5 3H20v15.5A2.5 2.5 0 0 1 17.5 21H8.5A2.5 2.5 0 0 1 6 18.5z"></path>
                            <path d="M6 6h10.5A2.5 2.5 0 0 1 19 8.5V21"></path>
                            <path d="M10 8.5h5"></path>
                            <path d="M10 12h5"></path>
                        </svg>
                        <span>Capabilities</span>
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
                <a href="{{ route('work.index') }}" class="zazu-mobile-link {{ request()->routeIs('work.*') ? 'active' : '' }}">Work</a>
                <a href="{{ route('customers.index') }}" class="zazu-mobile-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">Customers</a>
                <a href="{{ route('capabilities.index') }}" class="zazu-mobile-link {{ request()->routeIs('capabilities.*') ? 'active' : '' }}">Capabilities</a>
            </nav>

            <div class="zazu-content">
                @if (session('success'))
                    <div class="zazu-alert zazu-alert-success" role="status">
                        <strong>Saved.</strong>
                        <span>{{ session('success') }}</span>
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
