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
$statusPath = Join-Path $backupDirectory '.secondary-copy-status.json'
$environmentPath = Join-Path $ProjectPath '.env'
$secondaryPath = $null
$lastSecondarySuccess = $null
$lastSecondaryArchive = $null
$exitCode = 1

function Read-ZazuSecondaryBackupPath([string]$EnvironmentFile) {
    if (-not (Test-Path -LiteralPath $EnvironmentFile -PathType Leaf)) {
        return $null
    }

    $environmentText = Get-Content -LiteralPath $EnvironmentFile -Raw
    $match = [regex]::Match($environmentText, '(?m)^ZAZU_BACKUP_SECONDARY_PATH=(.*)$')
    if (-not $match.Success) {
        return $null
    }

    $value = $match.Groups[1].Value.Trim()
    if ($value.Length -ge 2) {
        if (($value.StartsWith('"') -and $value.EndsWith('"')) -or ($value.StartsWith("'") -and $value.EndsWith("'"))) {
            $value = $value.Substring(1, $value.Length - 2)
        }
    }

    if ([string]::IsNullOrWhiteSpace($value)) {
        return $null
    }

    return $value
}

function Write-ZazuSecondaryStatus(
    [string]$Status,
    [string]$Message,
    [string]$ArchiveName,
    [string]$LastSuccessfulAt
) {
    $payload = [ordered]@{
        status = $Status
        attempted_at = [DateTimeOffset]::UtcNow.ToString('o')
        last_successful_at = $LastSuccessfulAt
        archive = $ArchiveName
        message = $Message
    }
    $temporaryPath = $statusPath + '.tmp'
    $json = $payload | ConvertTo-Json -Depth 4
    [System.IO.File]::WriteAllText($temporaryPath, $json, (New-Object System.Text.UTF8Encoding($false)))
    Move-Item -LiteralPath $temporaryPath -Destination $statusPath -Force
}

try {
    $secondaryPath = Read-ZazuSecondaryBackupPath -EnvironmentFile $environmentPath

    if (Test-Path -LiteralPath $statusPath -PathType Leaf) {
        try {
            $previousStatus = Get-Content -LiteralPath $statusPath -Raw | ConvertFrom-Json
            if ($previousStatus.last_successful_at) {
                $lastSecondarySuccess = [string]$previousStatus.last_successful_at
            }
            if ($previousStatus.archive) {
                $lastSecondaryArchive = [string]$previousStatus.archive
            }
        } catch {
            $lines.Add('Previous secondary-copy status could not be read; a new verified result will replace it.')
        }
    }

    $phpCandidates = @()
    $phpCommand = Get-Command php.exe -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($phpCommand) {
        $phpCandidates += $phpCommand.Source
    }
    if (Test-Path 'C:\PHP84\php.exe' -PathType Leaf) {
        $phpCandidates += 'C:\PHP84\php.exe'
    }

    $php = $null
    foreach ($candidate in ($phpCandidates | Select-Object -Unique)) {
        $versionText = (& $candidate -r 'echo PHP_VERSION;' 2>$null | Out-String).Trim()
        try {
            $version = [version]$versionText
        } catch {
            continue
        }
        if ($version -ge [version]'8.4') {
            $php = $candidate
            break
        }
    }
    if (-not $php) {
        throw 'PHP 8.4+ was not found on PATH or C:\PHP84\php.exe.'
    }

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
    foreach ($line in $output) {
        $lines.Add($line)
    }

    $created = @(Get-ChildItem -Path $backupDirectory -Filter 'zazu-backup-*.zip' -File -ErrorAction SilentlyContinue |
        Where-Object { -not $before.ContainsKey($_.FullName) -and $_.Length -gt 0 })
    if ($commandExit -ne 0 -or $created.Count -ne 1) {
        throw ("Backup command did not produce exactly one new non-empty archive (exit {0}, archives {1})." -f $commandExit, $created.Count)
    }

    $localArchives = @(Get-ChildItem -Path $backupDirectory -Filter 'zazu-backup-*.zip' -File |
        Sort-Object LastWriteTimeUtc -Descending)
    foreach ($oldArchive in @($localArchives | Select-Object -Skip 14)) {
        Remove-Item -LiteralPath $oldArchive.FullName -Force
    }

    if (-not $secondaryPath) {
        $secondaryMessage = 'No independent backup destination is configured. The only archive copy remains on this PC.'
        $lines.Add('WARNING: no independent backup destination is configured; the archive remains on this PC only.')
        Write-ZazuSecondaryStatus -Status 'not_configured' -Message $secondaryMessage -ArchiveName $created[0].Name -LastSuccessfulAt $lastSecondarySuccess
    } else {
        if (-not [System.IO.Path]::IsPathRooted($secondaryPath)) {
            throw 'ZAZU_BACKUP_SECONDARY_PATH must be an absolute path outside the Zazu installation.'
        }

        $secondaryDirectory = [System.IO.Path]::GetFullPath($secondaryPath).TrimEnd('\')
        $projectPrefix = $ProjectPath.TrimEnd('\') + [System.IO.Path]::DirectorySeparatorChar
        if (
            $secondaryDirectory.Equals($ProjectPath, [System.StringComparison]::OrdinalIgnoreCase) -or
            $secondaryDirectory.StartsWith($projectPrefix, [System.StringComparison]::OrdinalIgnoreCase)
        ) {
            throw 'The independent backup destination must be outside the Zazu installation folder.'
        }

        $volumeRoot = [System.IO.Path]::GetPathRoot($secondaryDirectory)
        if (-not (Test-Path -LiteralPath $volumeRoot -PathType Container)) {
            throw 'The independent backup destination drive is not available. Connect the USB drive or restore access to the synced folder.'
        }

        if (-not (Test-Path -LiteralPath $secondaryDirectory -PathType Container)) {
            New-Item -ItemType Directory -Path $secondaryDirectory -Force | Out-Null
        }

        $sourceArchive = $created[0]
        $destinationArchive = Join-Path $secondaryDirectory $sourceArchive.Name
        $temporaryArchive = $destinationArchive + '.partial'
        if (Test-Path -LiteralPath $temporaryArchive) {
            Remove-Item -LiteralPath $temporaryArchive -Force
        }

        Copy-Item -LiteralPath $sourceArchive.FullName -Destination $temporaryArchive -Force
        $sourceHash = (Get-FileHash -LiteralPath $sourceArchive.FullName -Algorithm SHA256).Hash
        $copyHash = (Get-FileHash -LiteralPath $temporaryArchive -Algorithm SHA256).Hash
        if ($sourceHash -ne $copyHash) {
            Remove-Item -LiteralPath $temporaryArchive -Force -ErrorAction SilentlyContinue
            throw 'The independent backup copy failed SHA-256 verification.'
        }

        Move-Item -LiteralPath $temporaryArchive -Destination $destinationArchive -Force
        $lastSecondarySuccess = [DateTimeOffset]::UtcNow.ToString('o')
        $lastSecondaryArchive = $sourceArchive.Name
        $secondaryMessage = 'The independent backup copy was copied and SHA-256 verified.'
        Write-ZazuSecondaryStatus -Status 'verified' -Message $secondaryMessage -ArchiveName $lastSecondaryArchive -LastSuccessfulAt $lastSecondarySuccess

        try {
            $secondaryArchives = @(Get-ChildItem -Path $secondaryDirectory -Filter 'zazu-backup-*.zip' -File |
                Sort-Object LastWriteTimeUtc -Descending)
            foreach ($oldArchive in @($secondaryArchives | Select-Object -Skip 14)) {
                Remove-Item -LiteralPath $oldArchive.FullName -Force
            }
        } catch {
            $lines.Add(("WARNING: old independent backup archives could not all be pruned: {0}" -f $_.Exception.Message))
        }

        $lines.Add(("SECONDARY COPY VERIFIED: {0}" -f $sourceArchive.Name))
    }

    $lines.Add(("SUCCESS: {0}; retained up to 14 on-PC archives." -f $created[0].Name))
    $exitCode = 0
} catch {
    $lines.Add(("FAILURE: {0}" -f $_.Exception.Message))
    $exitCode = 1

    try {
        if ($secondaryPath) {
            $secondaryMessage = 'The independent backup copy failed on the last scheduled run. Check the USB drive or synced-folder destination.'
            Write-ZazuSecondaryStatus -Status 'failed' -Message $secondaryMessage -ArchiveName $lastSecondaryArchive -LastSuccessfulAt $lastSecondarySuccess
        } elseif (Test-Path -LiteralPath $backupDirectory -PathType Container) {
            $secondaryMessage = 'No independent backup destination is configured. The only archive copy remains on this PC.'
            Write-ZazuSecondaryStatus -Status 'not_configured' -Message $secondaryMessage -ArchiveName $lastSecondaryArchive -LastSuccessfulAt $lastSecondarySuccess
        }
    } catch {
        $lines.Add('WARNING: secondary-copy status could not be persisted for the dashboard.')
    }
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

foreach ($line in $lines) {
    Write-Output $line
}
exit $exitCode
