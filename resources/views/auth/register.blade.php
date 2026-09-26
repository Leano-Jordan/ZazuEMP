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
        <section class="zazu-auth-card" aria-labelledby="register-heading">
            <div class="zazu-auth-brand">
                <div class="zazu-auth-mark">Z</div>
                <div>
                    <div class="zazu-auth-name">Zazu</div>
                    <div class="zazu-auth-subtitle">Event & catering operations</div>
                </div>
            </div>

            <div class="zazu-page-kicker">Create workspace</div>
            <h1 id="register-heading">Set up your Zazu workspace</h1>
            <p class="zazu-auth-copy">Create your owner account and choose the username you will use to sign in.</p>

            <form method="POST" action="{{ route('register.store') }}" class="zazu-form">
                @csrf

                <div class="zazu-field">
                    <label for="name">Your name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
                    @error('name') <div class="zazu-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="zazu-field">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" autocapitalize="none" spellcheck="false" maxlength="32" pattern="[A-Za-z0-9][A-Za-z0-9._-]{2,31}" required>
                    <div class="zazu-field-help">3–32 characters. Letters, numbers, dots, underscores and hyphens.</div>
                    @error('username') <div class="zazu-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="zazu-field">
                    <label for="business_name">Business name</label>
                    <input id="business_name" name="business_name" type="text" value="{{ old('business_name') }}" autocomplete="organization" required>
                    @error('business_name') <div class="zazu-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="zazu-field">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email') <div class="zazu-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="zazu-field">
                    <label for="password">Password</label>
                    <div class="zazu-login-password">
                        <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                        <button type="button" class="zazu-login-password-toggle" data-password-toggle aria-controls="password" aria-label="Show password">Show</button>
                    </div>
                    @error('password') <div class="zazu-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="zazu-field">
                    <label for="password_confirmation">Confirm password</label>
                    <div class="zazu-login-password">
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                        <button type="button" class="zazu-login-password-toggle" data-password-toggle="password_confirmation" aria-controls="password_confirmation" aria-label="Show password">Show</button>
                    </div>
                    @error('password_confirmation') <div class="zazu-field-error">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="zazu-btn zazu-btn-primary zazu-auth-submit">Create workspace</button>
            </form>

            <div class="zazu-auth-switch">
                Already have an account?
                <a href="{{ route('login') }}">Sign in</a>
            </div>

            <a href="{{ route('owner.login') }}" class="zazu-owner-door-link">Owner / Administrator sign in</a>
        </section>

        <aside class="zazu-auth-visual" aria-hidden="true">
            <div class="zazu-auth-visual-inner"></div>
        </aside>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
                const id = toggle.dataset.passwordToggle || toggle.getAttribute('aria-controls');
                const password = document.getElementById(id);

                if (!password) return;

                toggle.addEventListener('click', () => {
                    const showing = password.type === 'text';
                    password.type = showing ? 'password' : 'text';
                    toggle.textContent = showing ? 'Show' : 'Hide';
                    toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
                });
            });
        });
    </script>
</body>
</html>
