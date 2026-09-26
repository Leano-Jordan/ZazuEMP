<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create workspace · Zazu</title>
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

            <div class="zazu-page-kicker">Create workspace</div>
            <h1>Set up your Zazu workspace</h1>
            <p class="zazu-auth-copy">Your account becomes the owner of the business workspace you create.</p>

            <form method="POST" action="{{ route('register.store') }}" class="zazu-form">
                @csrf

                <div class="zazu-field">
                    <label for="name">Your name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required>
                    @error('name') <div class="zazu-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="zazu-field">
                    <label for="business_name">Business name</label>
                    <input id="business_name" name="business_name" type="text" value="{{ old('business_name') }}" autocomplete="organization" required>
                    @error('business_name') <div class="zazu-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="zazu-field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email') <div class="zazu-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="zazu-field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                    @error('password') <div class="zazu-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="zazu-field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                    @error('password_confirmation') <div class="zazu-field-error">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="zazu-btn zazu-btn-primary zazu-auth-submit">Create workspace</button>
            </form>

            <div class="zazu-auth-switch">
                Already have an account?
                <a href="{{ route('login') }}">Sign in</a>
            </div>

            <details class="zazu-owner-door">
                <summary>Owner / Administrator access</summary>
                <div class="zazu-owner-door-body">
                    <strong>Protected owner entry</strong>
                    <p>Owner authority is verified on the server. This door does not grant access by itself.</p>
                    <a href="{{ route('login', ['owner' => 1]) }}" class="zazu-owner-door-link">Open owner sign-in</a>
                </div>
            </details>
        </section>

        <aside class="zazu-auth-visual" aria-hidden="true">
            <div class="zazu-auth-visual-inner"></div>
        </aside>
    </main>
</body>
</html>
