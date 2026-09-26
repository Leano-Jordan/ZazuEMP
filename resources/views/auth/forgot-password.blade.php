<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Reset password · Zazu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="zazu-auth-shell">
    <main class="zazu-auth-frame zazu-auth-frame-single">
        <section class="zazu-auth-card" aria-labelledby="forgot-password-heading">
            <div class="zazu-auth-brand">
                <div class="zazu-auth-mark" aria-hidden="true">Z</div>
                <div>
                    <div class="zazu-auth-name">Zazu</div>
                    <div class="zazu-auth-subtitle">Event & catering operations</div>
                </div>
            </div>

            <div class="zazu-page-kicker">Account recovery</div>
            <h1 id="forgot-password-heading">Forgot your password?</h1>
            <p class="zazu-auth-copy">Enter your username or email address. Zazu will send the reset link to the email address on the account.</p>

            @if (session('status'))
                <div class="zazu-auth-status" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="zazu-auth-notice zazu-auth-notice-error" role="alert">
                    Please check the highlighted field and try again.
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="zazu-form">
                @csrf
                <div class="zazu-field">
                    <label for="identifier">Username or email</label>
                    <input
                        id="identifier"
                        name="identifier"
                        type="text"
                        value="{{ old('identifier') }}"
                        autocomplete="username"
                        autocapitalize="none"
                        spellcheck="false"
                        required
                        autofocus
                    >
                    @error('identifier')
                        <div class="zazu-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="zazu-btn zazu-btn-primary zazu-auth-submit">
                    Send reset link
                </button>
            </form>

            <div class="zazu-auth-switch">
                Remembered your password?
                <a href="{{ route('login') }}">Back to sign in</a>
            </div>
        </section>
    </main>
</body>
</html>
