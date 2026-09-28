<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>{{ $title ?? 'Zazu · Something went wrong' }}</title>
    <style>
:root{color-scheme:light dark;--page:#F8FAFC;--surface:#FFF;--ink:#0F172A;--muted:#64748B;--border:#E2E8F0;--border-strong:#CBD5E1;--primary:#1E40AF;--primary-hover:#2563EB;--primary-ink:#FFF}
@media(prefers-color-scheme:dark){:root{--page:#090D16;--surface:#111827;--ink:#F8FAFC;--muted:#94A3B8;--border:#1E293B;--border-strong:#334155;--primary:#3B82F6;--primary-hover:#60A5FA;--primary-ink:#07111F}}
*{box-sizing:border-box}body{margin:0;font-family:Inter,"Segoe UI Variable Text","Segoe UI",system-ui,sans-serif;color:var(--ink);background:radial-gradient(circle at 88% 0%,color-mix(in srgb,var(--primary) 10%,transparent),transparent 24rem),var(--page);-webkit-font-smoothing:antialiased}a{color:inherit}.zazu-error-body{min-height:100vh;display:grid;place-items:center;padding:24px}.zazu-error-shell{width:min(100%,720px)}.zazu-error-card{padding:clamp(24px,5vw,36px);border:1px solid var(--border);border-left:4px solid var(--primary);border-radius:18px;background:var(--surface);box-shadow:0 18px 48px rgba(15,23,42,.08)}.zazu-error-brand{display:inline-flex;align-items:center;gap:10px;margin-bottom:26px;font-weight:800;font-size:20px;letter-spacing:-.03em}.zazu-brand-mark{display:grid;width:34px;height:34px;place-items:center;border-radius:9px;background:var(--primary);color:var(--primary-ink);box-shadow:0 7px 18px color-mix(in srgb,var(--primary) 25%,transparent)}.zazu-eyebrow{color:var(--primary);font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.zazu-error-code{margin-top:14px;color:var(--muted);font:800 11px/1.2 "JetBrains Mono",ui-monospace,monospace;letter-spacing:.15em}.zazu-error-title{margin:7px 0 0;font-family:"Cabinet Grotesk",Inter,system-ui,sans-serif;font-size:clamp(30px,6vw,46px);line-height:1.04;letter-spacing:-.05em}.zazu-error-copy{max-width:590px;margin:14px 0 0;color:var(--muted);line-height:1.65}.zazu-error-actions{display:flex;flex-wrap:wrap;gap:9px;margin-top:26px}.zazu-btn{display:inline-flex;min-height:40px;align-items:center;justify-content:center;padding:0 14px;border:1px solid var(--border);border-radius:10px;font-size:12px;font-weight:750;text-decoration:none}.zazu-btn-primary{background:var(--primary);border-color:var(--primary);color:var(--primary-ink)}.zazu-btn-secondary{background:var(--surface);color:var(--ink);border-color:var(--border-strong)}.zazu-error-reference{margin-top:24px;padding-top:16px;border-top:1px solid var(--border);color:var(--muted);font:600 11px/1.5 "JetBrains Mono",ui-monospace,monospace;overflow-wrap:anywhere}
</style>
</head>
<body class="zazu-error-body">
    <main class="zazu-error-shell">
        <div class="zazu-error-card">
            <div class="zazu-brand zazu-error-brand">
                <span class="zazu-brand-mark">Z</span>
                <span class="zazu-brand-name">Zazu</span>
            </div>
            <div class="zazu-eyebrow">Workspace protection</div>
            <div class="zazu-error-code">{{ $code ?? 'ERROR' }}</div>
            <h1 class="zazu-error-title">{{ $headline }}</h1>
            <p class="zazu-error-copy">{{ $messageText }}</p>

            <div class="zazu-error-actions">
                <a href="{{ url()->previous() }}" class="zazu-btn zazu-btn-secondary">Go back</a>
                <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-primary">Go to workspace</a>
            </div>

            <div class="zazu-error-reference">
                Reference {{ $requestId ?? 'available in the request header' }}
            </div>
        </div>
    </main>
</body>
</html>