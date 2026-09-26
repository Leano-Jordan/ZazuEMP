<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Zazu · Something went wrong' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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