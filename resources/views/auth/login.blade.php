<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $ownerAccess ? 'Owner sign in' : 'Sign in' }} · Zazu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="zazu-auth-shell">
    <main class="zazu-auth-frame">
        <section class="zazu-auth-card">
            <div class="zazu-auth-brand">
                <div class="zazu-auth-mark">Z</div>
                <div>
                    <div class="zazu-auth-name">Zazu</div>
                    <div class="zazu-auth-subtitle">Event Management Platform</div>
                </div>
            </div>

            <div class="zazu-page-kicker">{{ $ownerAccess ? 'Owner access' : 'Workspace access' }}</div>
            <h1>{{ $ownerAccess ? 'Owner sign in' : 'Sign in' }}</h1>
            <p class="zazu-auth-copy">
                {{ $ownerAccess
                    ? 'Sign in with an owner account to open protected administration.'
                    : 'Sign in to open your business workspace.' }}
            </p>

            @if($ownerAccess)
                <div class="zazu-auth-notice">
                    Owner access is checked after authentication. Staff credentials cannot elevate themselves.
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="zazu-form">
                @csrf
                @if($ownerAccess)
                    <input type="hidden" name="owner_access" value="1">
                @endif

                <div class="zazu-field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                    @error('email') <div class="zazu-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="zazu-field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                    @error('password') <div class="zazu-field-error">{{ $message }}</div> @enderror
                </div>

                <label class="zazu-check">
                    <input type="checkbox" name="remember" value="1">
                    <span>Keep me signed in</span>
                </label>

                <button type="submit" class="zazu-btn zazu-btn-primary zazu-auth-submit">{{ $ownerAccess ? 'Open owner area' : 'Sign in' }}</button>
            </form>

            <div class="zazu-auth-switch">
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

        <aside class="zazu-auth-visual" aria-hidden="true">
            <div class="zazu-auth-visual-inner"></div>
        </aside>
    </main>
</body>
</html>
