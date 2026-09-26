<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0d4f43">
    @php($business = app(\App\Support\CurrentBusiness::class)->resolve(auth()->user()))
    @php($businesses = auth()->user()->businesses()->where('businesses.status', 'active')->orderBy('businesses.name')->get())
    @php($isOwner = app(\App\Support\CurrentBusiness::class)->hasRole('owner', auth()->user(), $business))
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="{{ $business?->wallpaper_path ? 'zazu-has-wallpaper' : '' }}" data-business-currency="{{ $business?->currency ?? 'ZAR' }}" @if($business?->wallpaper_path) style="--zazu-wallpaper: url('{{ e(Storage::disk('public')->url($business->wallpaper_path)) }}')" @endif>
    <a class="zazu-skip-link" href="#main-content">Skip to main content</a>
    <div class="zazu-shell">
        <aside class="zazu-sidebar">
            <div class="zazu-brand">
                <a href="{{ route('dashboard') }}" class="zazu-brand-link">
                    @if ($business?->logo_path)
                        <img src="{{ Storage::disk('public')->url($business->logo_path) }}" alt="{{ $business->name }} logo" class="zazu-brand-logo">
                    @endif
                    <span class="zazu-brand-word">{{ $business?->name ?? 'zazu' }}</span>
                </a>
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
                        <span>Jobs</span>
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
                    <div class="zazu-nav-label">Services</div>
                    <a href="{{ route('capabilities.index') }}" class="zazu-nav-link {{ request()->routeIs('capabilities.*') ? 'active' : '' }}" @if (request()->routeIs('capabilities.*')) aria-current="page" @endif>
                        <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M6 5.5A2.5 2.5 0 0 1 8.5 3H20v15.5A2.5 2.5 0 0 1 17.5 21H8.5A2.5 2.5 0 0 1 6 18.5z"></path><path d="M6 6h10.5A2.5 2.5 0 0 1 19 8.5V21"></path><path d="M10 8.5h5M10 12h5"></path></svg>
                        <span>Services & prices</span>
                    </a>
                </div>

                @if($isOwner)
                    <div class="zazu-nav-group">
                        <div class="zazu-nav-label">System</div>
                        <a href="{{ route('settings.index') }}" class="zazu-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" @if (request()->routeIs('settings.*')) aria-current="page" @endif>
                            <svg class="zazu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M19 12a7 7 0 0 0-.3-2l2-1.3-2-3.4-2.3 1a7 7 0 0 0-3.4-2L12.7 2h-1.4L11 4.3a7 7 0 0 0-3.4 2l-2.3-1-2 3.4 2 1.3a7 7 0 0 0 0 4L3.3 15.3l2 3.4 2.3-1a7 7 0 0 0 3.4 2l.3 2.3h1.4l.3-2.3a7 7 0 0 0 3.4-2l2.3 1 2-3.4-2-1.3a7 7 0 0 0 .3-2z"></path></svg>
                            <span>Settings</span>
                        </a>
                    </div>
                @endif
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
                    <div>
                        <div class="zazu-eyebrow">{{ $business?->name ?? 'Zazu EMP' }}</div>
                        <h1 class="zazu-page-title">{{ $heading ?? $title ?? 'Workspace' }}</h1>
                    </div>

                    <div class="zazu-topbar-actions">
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
                                                <select id="zazu-business-switch" name="business_id" class="zazu-user-switch-select" onchange="this.form.submit()">
                                                    @foreach($businesses as $availableBusiness)
                                                        <option value="{{ $availableBusiness->id }}" @selected((int) $availableBusiness->id === (int) $business->id)>{{ $availableBusiness->name }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </div>
                                    @endif
                                    <div class="zazu-user-popover-divider"></div>
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
                @if($isOwner)
                    <a href="{{ route('settings.index') }}" class="zazu-mobile-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" @if (request()->routeIs('settings.*')) aria-current="page" @endif>Settings</a>
                @endif
            </nav>

            <div class="zazu-content">
                @if ($errors->any())
                    <div class="zazu-error-summary" role="alert" tabindex="-1" data-error-summary>
                        <div class="zazu-error-summary-title">Please check the highlighted fields.</div>
                        <ul class="zazu-error-summary-list" data-error-summary-list></ul>
                    </div>
                @endif

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

                {{ $slot }}
            </div>
        </main>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const serverErrors = @json($errors->toArray());

        document.querySelectorAll('.zazu-field').forEach((field) => {
            const control = field.querySelector('input, select, textarea');
            if (!control) return;

            const name = (control.getAttribute('name') || '').toLowerCase();
            const baseName = name.replace(/\[.*?\]/g, '');
            const messages = serverErrors[name] || serverErrors[baseName] || [];

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
