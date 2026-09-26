<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · Zazu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="zazu-auth-shell">
    <main class="zazu-auth-card">
        <div class="zazu-auth-brand">
            <div class="zazu-auth-mark">Z</div>
            <div>
                <div class="zazu-auth-name">Zazu</div>
                <div class="zazu-auth-subtitle">Event & Catering Management</div>
            </div>
        </div>

        <div class="zazu-page-kicker">Workspace access</div>
        <h1>Sign in</h1>
        <p class="zazu-auth-copy">Sign in to open your business workspace.</p>

        <form method="POST" action="{{ route('login.store') }}" class="zazu-form">
            @csrf

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

            <button type="submit" class="zazu-button zazu-button-primary">Sign in</button>
        </form>
    </main>
</body>
</html>
