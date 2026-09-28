<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restore workspace access · Zazu</title>
    @vite(['resources/css/app.css', 'resources/css/zazu-final-visual-sweep.css', 'resources/js/app.js'])
</head>
<body class="zazu-auth-shell">
    <main class="zazu-auth-frame zazu-auth-dual" data-auth-frame>
        <section class="zazu-auth-card zazu-auth-form-panel" aria-labelledby="workspace-recovery-heading">
            <div class="zazu-auth-brand">
                <div class="zazu-auth-mark">Z</div>
                <div>
                    <div class="zazu-auth-name">Zazu</div>
                    <div class="zazu-auth-subtitle">Event & catering operations</div>
                </div>
            </div>

            <div class="zazu-auth-entry-state"><span class="zazu-status-dot" aria-hidden="true"></span><span>Workspace access</span></div>
            <h1 id="workspace-recovery-heading">Restore your workspace access</h1>
            <p class="zazu-auth-copy">Your account is signed in, but it is not connected to an active workspace. Create the workspace for this account and continue into setup.</p>

            <form method="POST" action="{{ route('workspace.recovery.store') }}" class="zazu-form">
                @csrf
                <div class="zazu-field">
                    <label for="business_name">Business name</label>
                    <input id="business_name" name="business_name" type="text" value="{{ old('business_name') }}" autocomplete="organization" required autofocus>
                    @error('business_name') <div class="zazu-field-error">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="zazu-btn zazu-btn-primary zazu-auth-submit"><span>Create workspace & continue</span><span aria-hidden="true">→</span></button>
            </form>

            <div class="zazu-auth-switch">
                This creates an owner workspace for the account you are already signed into.
                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button type="submit" class="zazu-auth-inline-button">Sign out</button>
                </form>
            </div>
        </section>

        <aside class="zazu-auth-visual zazu-auth-switch-panel" aria-label="Workspace recovery information">
            <div class="zazu-auth-visual-inner">
                <span class="zazu-auth-visual-label">No data is moved</span>
                <strong class="zazu-auth-switch-title">Only the missing workspace link is restored.</strong>
                <p class="zazu-auth-visual-copy">Your existing account remains the same. Zazu creates a new active business workspace and makes you its owner.</p>
            </div>
        </aside>
    </main>
</body>
</html>
