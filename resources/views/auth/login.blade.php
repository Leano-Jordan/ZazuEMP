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
    <main class="zazu-auth-frame zazu-auth-dual zazu-auth-state-login" data-auth-frame>
        <section class="zazu-auth-card zazu-auth-form-panel" aria-labelledby="login-heading">
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
                    ? 'Use your owner account to enter protected administration.'
                    : 'Use the username you chose for Zazu, or your email address.' }}
            </p>

            @if($ownerAccess)
                <div class="zazu-auth-notice zazu-login-notice" role="note">
                    Owner authority is checked on the server. Staff credentials cannot elevate themselves.
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="zazu-form zazu-login-form">
                @csrf
                @if($ownerAccess)
                    <input type="hidden" name="owner_access" value="1">
                @endif

                <div class="zazu-field">
                    <label for="identifier">Username or email</label>
                    <input
                        id="identifier"
                        name="identifier"
                        type="text"
                        value="{{ old('identifier') }}"
                        autocomplete="username"
                        inputmode="text"
                        spellcheck="false"
                        autocapitalize="none"
                        required
                        autofocus
                        aria-describedby="@error('identifier')identifier-error @enderror"
                    >
                    @error('identifier')
                        <div id="identifier-error" class="zazu-field-error">{{ $message }}</div>
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
                    <a href="{{ route('password.request') }}" class="zazu-login-recovery">Forgot password?</a>
                </div>

                <button type="submit" class="zazu-btn zazu-btn-primary zazu-auth-submit zazu-login-submit">
                    <span>{{ $ownerAccess ? 'Open owner area' : 'Sign in' }}</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                        <path d="M5 12h12"></path>
                        <path d="m13 7 5 5-5 5"></path>
                    </svg>
                </button>
            </form>

            @if(!$ownerAccess)
                <a href="{{ route('owner.login') }}" class="zazu-owner-door-link">Owner / Administrator sign in</a>
            @else
                <div class="zazu-auth-switch">
                    Regular workspace access?
                    <a href="{{ route('login') }}">Sign in normally</a>
                </div>
            @endif
        </section>

        <aside class="zazu-auth-visual zazu-auth-switch-panel" aria-label="Workspace account options">
            <div class="zazu-auth-visual-inner">
                @if($ownerAccess)
                    <span class="zazu-auth-visual-label">Workspace access</span>
                    <strong class="zazu-auth-switch-title">Need the regular workspace sign-in?</strong>
                    <p class="zazu-auth-visual-copy">Return to the standard workspace entry point. Owner administration remains protected separately.</p>
                    <a href="{{ route('login') }}" class="zazu-btn zazu-btn-secondary zazu-auth-switch-button">Workspace sign in</a>
                @else
                    <span class="zazu-auth-visual-label">New to Zazu?</span>
                    <strong class="zazu-auth-switch-title">Create a workspace built around your events.</strong>
                    <p class="zazu-auth-visual-copy">Set up your business, services and daily operations in one place.</p>
                    <a href="{{ route('register') }}" data-auth-switch class="zazu-btn zazu-btn-secondary zazu-auth-switch-button">Create workspace</a>
                @endif
            </div>
        </aside>
    </main>
</body>
</html>
