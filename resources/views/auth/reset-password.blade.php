<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Choose a new password · Zazu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="zazu-auth-shell">
    <main class="zazu-auth-frame zazu-auth-frame-single">
        <section class="zazu-auth-card" aria-labelledby="reset-password-heading">
            <div class="zazu-auth-brand">
                <div class="zazu-auth-mark" aria-hidden="true">Z</div>
                <div>
                    <div class="zazu-auth-name">Zazu</div>
                    <div class="zazu-auth-subtitle">Event & catering operations</div>
                </div>
            </div>

            <div class="zazu-page-kicker">Account recovery</div>
            <h1 id="reset-password-heading">Choose a new password</h1>
            <p class="zazu-auth-copy">Set a new password below. After it succeeds, Zazu will sign you in automatically.</p>

            @if ($errors->any())
                <div class="zazu-auth-notice zazu-auth-notice-error" role="alert">
                    Please correct the highlighted field and try again.
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="zazu-form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="zazu-field">
                    <label for="email">Account email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $email) }}"
                        autocomplete="email"
                        required
                    >
                    @error('email')
                        <div class="zazu-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="zazu-field">
                    <label for="password">New password</label>
                    <div class="zazu-login-password">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >
                        <button type="button" class="zazu-login-password-toggle" data-password-toggle aria-controls="password" aria-label="Show password">Show</button>
                    </div>
                    @error('password')
                        <div class="zazu-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="zazu-field">
                    <label for="password_confirmation">Confirm new password</label>
                    <div class="zazu-login-password">
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >
                        <button type="button" class="zazu-login-password-toggle" data-password-toggle="password_confirmation" aria-controls="password_confirmation" aria-label="Show password">Show</button>
                    </div>
                </div>

                <button type="submit" class="zazu-btn zazu-btn-primary zazu-auth-submit">
                    Reset password
                </button>
            </form>

            <div class="zazu-auth-switch">
                Need a new link?
                <a href="{{ route('password.request') }}">Start recovery again</a>
            </div>
        </section>
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
