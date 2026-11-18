<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Sentinel Local Recovery</title>
</head>
<body>
    <main>
        <h1>Sentinel Local Recovery</h1>
        <p>This recovery screen runs only on the Zazu host PC. It does not use your business login or database-backed session, and it does not expose business records or the founder Control Centre.</p>

        @if($error)
            <p role="alert">{{ $error }}</p>
        @endif

        @if(!$verified)
            <h2>Unlock a local health check</h2>
            <p>On this PC, open the Zazu application folder in PowerShell and run:</p>
            <pre>php artisan zazu:sentinel-recovery-code</pre>
            <p>Enter the one-use code shown in that PowerShell window. It expires after five minutes.</p>
            <form method="post" action="/api/sentinel/recovery">
                <label for="code">Local recovery code</label>
                <input id="code" name="code" type="password" minlength="64" maxlength="64" pattern="[a-f0-9]{64}" autocomplete="off" required>
                <button type="submit">Run local health check</button>
            </form>
        @else
            <h2>Installation health</h2>
            <p>Checked: {{ $health['checked_at'] ?? 'Unknown' }}</p>
            <p>Overall status: {{ strtoupper($health['status'] ?? 'UNKNOWN') }}</p>
            <ul>
                @foreach($health['checks'] ?? [] as $check)
                    <li>
                        <strong>{{ $check['label'] ?? $check['key'] ?? 'Health check' }}</strong>
                        — {{ strtoupper($check['status'] ?? 'UNKNOWN') }}
                        <p>{{ $check['message'] ?? 'No additional detail is available.' }}</p>
                        @if(!empty($check['error_code']))
                            <p>Error code: {{ $check['error_code'] }}</p>
                        @endif
                        @if(!empty($check['reference']))
                            <p>Reference: {{ $check['reference'] }}</p>
                        @endif
                        @if(isset($check['pending_count']))
                            <p>Pending migrations: {{ $check['pending_count'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
            <p>This report is local to this installation. It has not been uploaded or sent anywhere.</p>
            <p><a href="/api/sentinel/recovery">Close report</a></p>
        @endif
    </main>
</body>
</html>
