<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $ownerAccess ? 'Owner sign in' : 'Sign in' }} · Zazu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="zazu-auth-shell zazu-login-page">
    <main class="zazu-auth-frame zazu-login-frame">
        <section class="zazu-auth-card zazu-login-panel" aria-labelledby="login-heading">
            <div class="zazu-auth-brand zazu-login-brand">
                <div class="zazu-auth-mark zazu-login-mark" aria-hidden="true">Z</div>
                <div>
                    <div class="zazu-auth-name">Zazu</div>
                    <div class="zazu-auth-subtitle">Event & catering operations</div>
                </div>
            </div>

            <div class="zazu-login-kicker">{{ $ownerAccess ? 'Owner access' : 'Workspace access' }}</div>
            <h1 id="login-heading">{{ $ownerAccess ? 'Welcome back, owner.' : 'Welcome back.' }}</h1>
            <p class="zazu-auth-copy zazu-login-copy">
                {{ $ownerAccess
                    ? 'Use an owner account to enter protected administration.'
                    : 'Sign in to continue managing the work behind every event.' }}
            </p>

            @if($ownerAccess)
                <div class="zazu-auth-notice zazu-login-notice" role="note">
                    Owner authority is verified on the server. Staff credentials cannot elevate themselves.
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="zazu-form zazu-login-form">
                @csrf
                @if($ownerAccess)
                    <input type="hidden" name="owner_access" value="1">
                @endif

                <div class="zazu-field">
                    <label for="email">Email address</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        inputmode="email"
                        spellcheck="false"
                        autocapitalize="none"
                        required
                        autofocus
                        aria-describedby="@error('email')email-error @enderror"
                    >
                    @error('email')
                        <div id="email-error" class="zazu-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="zazu-field">
                    <label for="password">Password</label>
                    <div class="zazu-login-password">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            aria-describedby="@error('password')password-error @enderror"
                        >
                        <button type="button" class="zazu-login-password-toggle" data-password-toggle aria-controls="password" aria-label="Show password">
                            Show
                        </button>
                    </div>
                    @error('password')
                        <div id="password-error" class="zazu-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="zazu-login-options">
                    <label class="zazu-check">
                        <input type="checkbox" name="remember" value="1">
                        <span>Keep me signed in</span>
                    </label>
                </div>

                <button type="submit" class="zazu-btn zazu-btn-primary zazu-auth-submit zazu-login-submit">
                    <span>{{ $ownerAccess ? 'Open owner area' : 'Sign in' }}</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                        <path d="M5 12h12"></path>
                        <path d="m13 7 5 5-5 5"></path>
                    </svg>
                </button>
            </form>

            <div class="zazu-auth-switch zazu-login-switch">
                Need a workspace?
                <a href="{{ route('register') }}">Create one</a>
            </div>

            @if(!$ownerAccess)
                <details class="zazu-owner-door">
                    <summary>Owner / Administrator access</summary>
                    <div class="zazu-owner-door-body">
                        <strong>Protected owner entry</strong>
                        <p>Use this for owner-level administration. Authority is verified server-side.</p>
                        <a href="{{ route('login', ['owner' => 1]) }}" class="zazu-owner-door-link">Open owner sign-in</a>
                    </div>
                </details>
            @endif
        </section>

        <aside class="zazu-auth-visual zazu-login-visual" aria-label="Zazu workspace overview">
            <div class="zazu-login-visual-scene" aria-hidden="true">
                <div class="zazu-login-window"></div>
                <div class="zazu-login-table"></div>
                <div class="zazu-login-plate"></div>
                <div class="zazu-login-glass"></div>
                <div class="zazu-login-sprig"></div>
            </div>

            <div class="zazu-auth-visual-inner zazu-login-visual-inner">
                <div class="zazu-auth-visual-label">{{ $ownerAccess ? 'Owner control' : 'Business control' }}</div>
                <div class="zazu-login-visual-title">The work behind the event, in one calm workspace.</div>
                <div class="zazu-auth-visual-copy">
                    Plan the job, keep the customer record close, track the commercial detail, and move the work forward.
                </div>

                <div class="zazu-login-points">
                    <span>Plan</span>
                    <span>Coordinate</span>
                    <span>Deliver</span>
                </div>
            </div>
        </aside>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.querySelector('[data-password-toggle]');
            const password = document.getElementById('password');

            if (!toggle || !password) return;

            toggle.addEventListener('click', () => {
                const showing = password.type === 'text';
                password.type = showing ? 'password' : 'text';
                toggle.textContent = showing ? 'Show' : 'Hide';
                toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            });
        });
    </script>
</body>
</html>
