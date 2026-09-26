<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Zazu · Something went wrong' }}</title>
    <style>
        :root {
            color-scheme: light;
            --page: #f6f7f9;
            --surface: #ffffff;
            --ink: #121722;
            --muted: #6a7280;
            --border: #e2e6ec;
            --primary: #175cd3;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        a { color: inherit; }
        .zazu-error-body { min-height: 100vh; display: grid; place-items: center; padding: 24px; background: var(--page); color: var(--ink); }
        .zazu-error-shell { width: min(100%, 720px); }
        .zazu-error-card { padding: 34px; border: 1px solid var(--border); border-radius: 18px; background: var(--surface); box-shadow: 0 20px 60px rgba(18,23,34,.08); }
        .zazu-error-brand { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 26px; font-weight: 800; font-size: 20px; letter-spacing: -.02em; }
        .zazu-brand-mark { display: grid; width: 34px; height: 34px; place-items: center; border-radius: 10px; background: var(--ink); color: white; }
        .zazu-eyebrow { color: #7a8391; font-size: 10px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
        .zazu-error-code { margin-top: 14px; color: var(--muted); font-size: 11px; font-weight: 800; letter-spacing: .18em; }
        .zazu-error-title { margin: 7px 0 0; font-size: clamp(28px, 5vw, 44px); line-height: 1.05; letter-spacing: -.03em; }
        .zazu-error-copy { max-width: 590px; margin: 14px 0 0; color: var(--muted); line-height: 1.65; }
        .zazu-error-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 26px; }
        .zazu-btn { display: inline-flex; min-height: 40px; align-items: center; justify-content: center; padding: 0 14px; border-radius: 10px; border: 1px solid var(--border); font-size: 12px; font-weight: 700; text-decoration: none; }
        .zazu-btn-primary { background: var(--primary); border-color: var(--primary); color: white; }
        .zazu-btn-secondary { background: var(--surface); color: var(--ink); }
        .zazu-error-reference { margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border); color: var(--muted); font: 600 11px/1.5 ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; overflow-wrap: anywhere; }
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

            @if(request()->attributes->get('zazu_request_id'))
                <div class="zazu-error-reference">
                    Reference {{ request()->attributes->get('zazu_request_id') }}
                </div>
            @endif
        </div>
    </main>
</body>
</html>