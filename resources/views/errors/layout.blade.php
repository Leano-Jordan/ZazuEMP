<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>{{ $title ?? 'Zazu · Something went wrong' }}</title>
    <style>
        :root {
            color-scheme: light;
            --page: #E7EEF5;
            --surface: #FFFFFF;
            --ink: #142B40;
            --muted: #456077;
            --border: #AEBECB;
            --border-strong: #73899D;
            --primary: #1B63C9;
            --primary-hover: #164A95;
            --primary-ink: #FFFFFF;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                color-scheme: dark;
                --page: #091827;
                --surface: #0F2235;
                --ink: #F2F7FB;
                --muted: #B7C9D8;
                --border: #3A5870;
                --border-strong: #5D7A93;
                --primary: #4E91EA;
                --primary-hover: #74ADF0;
                --primary-ink: #0D1F3A;
            }
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Segoe UI Variable Text", "Segoe UI", Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, sans-serif; }
        a { color: inherit; }
        .zazu-error-body { min-height: 100vh; display: grid; place-items: center; padding: 24px; background: radial-gradient(circle at 85% 0%, rgba(27,99,201,.12), transparent 28rem), linear-gradient(180deg, var(--page), #CFDDEA); color: var(--ink); }
        .zazu-error-shell { width: min(100%, 720px); }
        .zazu-error-card { padding: 34px; border: 1px solid var(--border-strong); border-left: 5px solid var(--primary); border-radius: 6px; background: var(--surface); box-shadow: 0 8px 24px rgba(16,24,20,.06); }
        .zazu-error-brand { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 26px; font-weight: 800; font-size: 20px; letter-spacing: -.02em; }
        .zazu-brand-mark { display: grid; width: 34px; height: 34px; place-items: center; border-radius: 4px; background: linear-gradient(145deg, var(--primary), #347DDF); color: var(--primary-ink); box-shadow: 0 7px 16px rgba(27,99,201,.22); }
        .zazu-eyebrow { color: var(--primary); font-size: 10px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
        .zazu-error-code { margin-top: 14px; color: var(--muted); font-size: 11px; font-weight: 800; letter-spacing: .18em; }
        .zazu-error-title { margin: 7px 0 0; font-size: clamp(28px, 5vw, 44px); line-height: 1.05; letter-spacing: -.03em; }
        .zazu-error-copy { max-width: 590px; margin: 14px 0 0; color: var(--muted); line-height: 1.65; }
        .zazu-error-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 26px; }
        .zazu-btn { display: inline-flex; min-height: 40px; align-items: center; justify-content: center; padding: 0 14px; border-radius: 6px; border: 1px solid var(--border); font-size: 13px; font-weight: 700; text-decoration: none; }
        .zazu-btn-primary { background: var(--primary); border-color: var(--primary); color: var(--primary-ink); box-shadow: 0 5px 14px rgba(27,99,201,.22); }
        .zazu-btn-secondary { background: var(--surface); color: var(--ink); border-color: var(--border-strong); }
        .zazu-error-reference { margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border); color: var(--muted); font: 600 11px/1.5 ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; overflow-wrap: anywhere; }

        @media (prefers-color-scheme: dark) {
            .zazu-error-body {
                background:
                    radial-gradient(circle at 85% 0%, rgba(78,145,234,.18), transparent 28rem),
                    linear-gradient(180deg, #091827, #07121E);
            }
        }
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