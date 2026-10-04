<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>{{ $title ?? 'Zazu · Something went wrong' }}</title>
    <style>
:root{color-scheme:light dark;--page:#0B3A66;--surface:#102C44;--ink:#F1F7FC;--muted:#A4BACC;--border:#365A75;--border-strong:#577A95;--primary:#0B63CE;--primary-hover:#1D78E5;--primary-ink:#FFFFFF}
@media(prefers-color-scheme:dark){:root{--page:#071B2C;--surface:#102C44;--ink:#F1F7FC;--muted:#A4BACC;--border:#365A75;--border-strong:#577A95;--primary:#63AEFF;--primary-hover:#8CC5FF;--primary-ink:#061522}}
*{box-sizing:border-box}body{margin:0;font-family:Inter,"Segoe UI Variable Text","Segoe UI",system-ui,sans-serif;color:var(--ink);background:radial-gradient(circle at 88% 0%,color-mix(in srgb,var(--primary) 10%,transparent),transparent 24rem),var(--page);-webkit-font-smoothing:antialiased}a{color:inherit}.zazu-error-body{min-height:100vh;display:grid;place-items:center;padding:24px}.zazu-error-shell{width:min(100%,720px)}.zazu-error-card{padding:clamp(24px,5vw,36px);border:1px solid var(--border);border-left:4px solid var(--primary);border-radius:18px;background:var(--surface);box-shadow:0 18px 48px rgba(15,23,42,.08)}.zazu-error-brand{display:inline-flex;align-items:center;gap:10px;margin-bottom:26px;font-weight:800;font-size:20px;letter-spacing:-.03em}.zazu-brand-mark{display:grid;width:34px;height:34px;place-items:center;border-radius:9px;background:var(--primary);color:var(--primary-ink);box-shadow:0 7px 18px color-mix(in srgb,var(--primary) 25%,transparent)}.zazu-eyebrow{color:var(--primary);font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.zazu-error-code{margin-top:14px;color:var(--muted);font:800 11px/1.2 "JetBrains Mono",ui-monospace,monospace;letter-spacing:.15em}.zazu-error-title{margin:7px 0 0;font-family:"Cabinet Grotesk",Inter,system-ui,sans-serif;font-size:clamp(30px,6vw,46px);line-height:1.04;letter-spacing:-.05em}.zazu-error-copy{max-width:590px;margin:14px 0 0;color:var(--muted);line-height:1.65}.zazu-error-actions{display:flex;flex-wrap:wrap;gap:9px;margin-top:26px}.zazu-error-signout-form{display:inline-flex;margin:0}.zazu-error-signout-form button{font:inherit;cursor:pointer}.zazu-btn{display:inline-flex;min-height:40px;align-items:center;justify-content:center;padding:0 14px;border:1px solid var(--border);border-radius:10px;font-size:12px;font-weight:750;text-decoration:none}.zazu-btn-primary{background:var(--primary);border-color:var(--primary);color:var(--primary-ink)}.zazu-btn-secondary{background:var(--surface);color:var(--ink);border-color:var(--border-strong)}.zazu-error-reference{margin-top:24px;padding-top:16px;border-top:1px solid var(--border);color:var(--muted);font:600 11px/1.5 "JetBrains Mono",ui-monospace,monospace;overflow-wrap:anywhere}
</style>
</head>
<body class="zazu-error-body">
    @php
        $signedIn = auth()->check();
        $isWorkspaceMissing = ($code ?? '') === 'AUTHZ-002';
        $displayHeadline = $isWorkspaceMissing
            ? "You're signed in, but no workspace is active."
            : ((!$signedIn && ($code ?? '') === 'AUTHZ-001') ? 'Sign in to continue.' : $headline);
        $displayMessage = $isWorkspaceMissing
            ? 'Your account is recognised, but Zazu cannot open a business workspace for it yet. Return to the public site to check your account state, or sign out and use another account.'
            : ((!$signedIn && ($code ?? '') === 'AUTHZ-001') ? 'This area is part of a protected Zazu workspace. Sign in to continue.' : $messageText);
    @endphp
    <main class="zazu-error-shell">
        <div class="zazu-error-card">
            <div class="zazu-brand zazu-error-brand">
                <span class="zazu-brand-mark">Z</span>
                <span class="zazu-brand-name">Zazu</span>
            </div>
            <div class="zazu-eyebrow">{{ $signedIn ? 'Account access' : 'Workspace access' }}</div>
            <div class="zazu-error-code">{{ $code ?? 'ERROR' }}</div>
            <h1 class="zazu-error-title">{{ $displayHeadline }}</h1>
            <p class="zazu-error-copy">{{ $displayMessage }}</p>

            <div class="zazu-error-actions">
                <a href="{{ url('/') }}" class="zazu-btn zazu-btn-secondary">Public site</a>
                @if($signedIn)
                    @if(!$isWorkspaceMissing)
                        <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-primary">Go to workspace</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="zazu-error-signout-form">
                        @csrf
                        <button type="submit" class="zazu-btn zazu-btn-secondary">Sign out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="zazu-btn zazu-btn-primary">Sign in</a>
                @endif
            </div>

            <div class="zazu-error-reference">
                Reference {{ $requestId ?? 'available in the request header' }}
            </div>
        </div>
    </main>
</body>
</html>