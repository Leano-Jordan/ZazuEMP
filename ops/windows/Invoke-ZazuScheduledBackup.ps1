[CmdletBinding()]
param([string]$ProjectPath)

$ErrorActionPreference = 'Stop'
if ([string]::IsNullOrWhiteSpace($ProjectPath)) {
    $ProjectPath = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
}
$ProjectPath = [System.IO.Path]::GetFullPath($ProjectPath).TrimEnd('\')
$lines = New-Object System.Collections.Generic.List[string]
$logDirectory = Join-Path $ProjectPath 'storage\logs'
$backupDirectory = Join-Path $ProjectPath 'storage\app\zazu-backups\daily'
$exitCode = 1

try {
    $phpCandidates = @()
    $phpCommand = Get-Command php.exe -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($phpCommand) { $phpCandidates += $phpCommand.Source }
    if (Test-Path 'C:\PHP84\php.exe' -PathType Leaf) { $phpCandidates += 'C:\PHP84\php.exe' }

    $php = $null
    foreach ($candidate in ($phpCandidates | Select-Object -Unique)) {
        $versionText = (& $candidate -r 'echo PHP_VERSION;' 2>$null | Out-String).Trim()
        try { $version = [version]$versionText } catch { continue }
        if ($version -ge [version]'8.4') { $php = $candidate; break }
    }
    if (-not $php) { throw 'PHP 8.4+ was not found on PATH or C:\PHP84\php.exe.' }

    New-Item -ItemType Directory -Path $backupDirectory -Force | Out-Null
    New-Item -ItemType Directory -Path $logDirectory -Force | Out-Null
    $before = @{}
    foreach ($file in @(Get-ChildItem -Path $backupDirectory -Filter 'zazu-backup-*.zip' -File -ErrorAction SilentlyContinue)) {
        $before[$file.FullName] = $true
    }

    $lines.Add('Starting scheduled Zazu installation backup.')
    Push-Location $ProjectPath
    try {
        $output = @(& $php (Join-Path $ProjectPath 'artisan') 'zazu:backup' "--output=$backupDirectory" 2>&1 | ForEach-Object { "$_" })
        $commandExit = $LASTEXITCODE
    } finally {
        Pop-Location
    }
    foreach ($line in $output) { $lines.Add($line) }

    $created = @(Get-ChildItem -Path $backupDirectory -Filter 'zazu-backup-*.zip' -File -ErrorAction SilentlyContinue |
        Where-Object { -not $before.ContainsKey($_.FullName) -and $_.Length -gt 0 })
    if ($commandExit -ne 0 -or $created.Count -ne 1) {
        throw ("Backup command did not produce exactly one new non-empty archive (exit {0}, archives {1})." -f $commandExit, $created.Count)
    }

    $retained = @(Get-ChildItem -Path $backupDirectory -Filter 'zazu-backup-*.zip' -File |
        Sort-Object LastWriteTimeUtc -Descending)
    foreach ($oldArchive in @($retained | Select-Object -Skip 14)) {
        Remove-Item -LiteralPath $oldArchive.FullName -Force
    }

    $lines.Add(("SUCCESS: {0}; retained {1} archives." -f $created[0].Name, [Math]::Min($retained.Count, 14)))
    $exitCode = 0
} catch {
    $lines.Add(("FAILURE: {0}" -f $_.Exception.Message))
    $exitCode = 1
} finally {
    try {
        New-Item -ItemType Directory -Path $logDirectory -Force | Out-Null
        $logPath = Join-Path $logDirectory ("zazu-backup-scheduler-" + (Get-Date -Format 'yyyyMMdd') + ".log")
        Add-Content -LiteralPath $logPath -Value $lines
        Get-ChildItem -Path $logDirectory -Filter 'zazu-backup-scheduler-*.log' -File -ErrorAction SilentlyContinue |
            Where-Object { $_.LastWriteTimeUtc -lt (Get-Date).ToUniversalTime().AddDays(-30) } |
            Remove-Item -Force
    } catch {
        Write-Warning 'The backup result could not be written to the local scheduler log.'
    }
}
foreach ($line in $lines) { Write-Output $line }
exit $exitCode
