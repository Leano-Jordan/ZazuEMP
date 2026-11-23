[CmdletBinding()]
param(
    [string]$ProjectPath,
    [string]$BindAddress = '127.0.0.1',
    [string]$PhpPath,
    [switch]$Lan,
    [switch]$BuiltAssetsOnly,
    [switch]$SecureCookies,
    [ValidateRange(1024, 65535)]
    [int]$Port = 8000,
    [switch]$Stop
)

$ErrorActionPreference = 'Stop'

# Script automatic variables are not reliably populated while parameter defaults are evaluated.
# Resolve the default project root in the script body, where PSCommandPath is available.
if ([string]::IsNullOrWhiteSpace($ProjectPath)) {
    $scriptDirectory = Split-Path -Parent $PSCommandPath
    $ProjectPath = (Resolve-Path (Join-Path $scriptDirectory '..\\..')).Path
}

$ProjectPath = [System.IO.Path]::GetFullPath($ProjectPath).TrimEnd('\\')

if ($Lan -and -not $Stop) {
    $wifiProfile = Get-NetConnectionProfile -ErrorAction Stop |
        Where-Object {
            $_.NetworkCategory -eq 'Private' -and
            $_.IPv4Connectivity -ne 'Disconnected' -and
            $_.InterfaceAlias -match '(?i)wi-?fi|wireless|wlan'
        } |
        Select-Object -First 1

    if (-not $wifiProfile) {
        throw 'LAN mode requires an active Wi-Fi connection classified as a Private network. No firewall or network settings were changed.'
    }

    $wifiAddress = Get-NetIPAddress -InterfaceIndex $wifiProfile.InterfaceIndex -AddressFamily IPv4 -ErrorAction Stop |
        Where-Object {
            $_.AddressState -eq 'Preferred' -and
            $_.IPAddress -notmatch '^(127\\.|169\\.254\\.)'
        } |
        Select-Object -First 1

    if (-not $wifiAddress) {
        throw "No usable IPv4 address was found on the private Wi-Fi interface '$($wifiProfile.InterfaceAlias)'."
    }

    $BindAddress = $wifiAddress.IPAddress
}

$parsedAddress = $null
if (-not [System.Net.IPAddress]::TryParse($BindAddress, [ref]$parsedAddress)) {
    throw 'BindAddress must be an IP address such as 127.0.0.1 or 0.0.0.0.'
}
$artisan = Join-Path $ProjectPath 'artisan'
$autoload = Join-Path $ProjectPath 'vendor\\autoload.php'
$environment = Join-Path $ProjectPath '.env'
$logDirectory = Join-Path $ProjectPath 'storage\\logs'

function Get-ZazuListener {
    return @(Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue)
}

function Get-ProcessDetails([int]$ProcessId) {
    return Get-CimInstance Win32_Process -Filter "ProcessId = $ProcessId" -ErrorAction SilentlyContinue
}

if ($Stop) {
    $listeners = Get-ZazuListener
    if ($listeners.Count -eq 0) {
        Write-Host "Zazu development server is not listening on port $Port."
        exit 0
    }

    $stopped = @{}
    foreach ($listener in $listeners) {
        $server = Get-ProcessDetails ([int]$listener.OwningProcess)
        if (-not $server -or $server.Name -ne 'php.exe' -or $server.CommandLine -notlike "*$ProjectPath*" -or $server.CommandLine -notlike '*server.php*') {
            throw "Port $Port is not owned by a verifiable Zazu PHP server for $ProjectPath. Nothing was stopped."
        }

        $parent = Get-ProcessDetails ([int]$server.ParentProcessId)
        if ($parent -and $parent.Name -eq 'php.exe' -and $parent.ExecutablePath -eq $server.ExecutablePath -and $parent.CommandLine -like '*artisan serve*') {
            if (-not $stopped.ContainsKey([string]$server.ProcessId)) {
                Stop-Process -Id ([int]$server.ProcessId) -Force
                $stopped[[string]$server.ProcessId] = $true
            }
            if (-not $stopped.ContainsKey([string]$parent.ProcessId)) {
                Stop-Process -Id ([int]$parent.ProcessId) -Force
                $stopped[[string]$parent.ProcessId] = $true
            }
        } elseif ($server.CommandLine -like "*$ProjectPath*" -and $server.CommandLine -like '*server.php*') {
            # Laravel's PHP listener can outlive its Artisan launcher if a
            # previous startup attempt is interrupted. The listener itself is
            # still safe to stop when its executable and full command line
            # identify this exact Zazu project. Do not require its parent to
            # remain alive or retain the original Artisan command line.
            if (-not $stopped.ContainsKey([string]$server.ProcessId)) {
                Stop-Process -Id ([int]$server.ProcessId) -Force
                $stopped[[string]$server.ProcessId] = $true
            }
        } elseif ($server.CommandLine -like "*$ProjectPath*" -and $server.CommandLine -like '*artisan serve*') {
            if (-not $stopped.ContainsKey([string]$server.ProcessId)) {
                Stop-Process -Id ([int]$server.ProcessId) -Force
                $stopped[[string]$server.ProcessId] = $true
            }
        } else {
            throw "Could not verify listener PID $($server.ProcessId) as a Zazu Artisan/server.php process for $ProjectPath. Nothing further was stopped."
        }
    }

    Write-Host "Stopped the verified Zazu development server on port $Port."
    exit 0
}

if (-not (Test-Path $artisan -PathType Leaf) -or -not (Test-Path $autoload -PathType Leaf)) {
    throw "Zazu project files or Composer dependencies are missing under $ProjectPath."
}
if (-not (Test-Path $environment -PathType Leaf)) {
    throw "The .env file is missing. Complete the existing local setup before starting Zazu."
}

# Migrate existing owner-PC installations from the default single-file log
# channel without touching custom stack configurations.
$environmentText = Get-Content $environment -Raw
$logStackMatch = [regex]::Match($environmentText, '(?m)^LOG_STACK=(.*)$')
if (-not $logStackMatch.Success) {
    $environmentText = $environmentText.TrimEnd() + [Environment]::NewLine + 'LOG_STACK=daily' + [Environment]::NewLine
} elseif ([string]::IsNullOrWhiteSpace($logStackMatch.Groups[1].Value) -or $logStackMatch.Groups[1].Value.Trim() -eq 'single') {
    $environmentText = [regex]::Replace($environmentText, '(?m)^LOG_STACK=.*$', 'LOG_STACK=daily')
}
if ($environmentText -notmatch '(?m)^LOG_DAILY_DAYS=') {
    $environmentText = $environmentText.TrimEnd() + [Environment]::NewLine + 'LOG_DAILY_DAYS=14' + [Environment]::NewLine
}
Set-Content -Path $environment -Value $environmentText -Encoding UTF8

# The local launcher must use the environment's selected log stack. A cached
# Laravel config takes precedence over .env, so its presence is handled below.
$logStackAfterUpdate = [regex]::Match($environmentText, '(?m)^LOG_STACK=(.*)$')
$usesDailyLogStack = $logStackAfterUpdate.Success -and ($logStackAfterUpdate.Groups[1].Value.Trim() -eq 'daily')

function Prepare-ZazuBuiltAssets {
    $publicDirectory = Join-Path $ProjectPath 'public'
    $hotFile = Join-Path $publicDirectory 'hot'
    $buildDirectory = Join-Path $publicDirectory 'build'
    $manifestFile = Join-Path $buildDirectory 'manifest.json'

    # This launcher serves compiled assets; it never starts Vite. A leftover
    # hot marker would send phones/other devices to an unreachable port 5173.
    if (Test-Path $hotFile -PathType Leaf) {
        Remove-Item $hotFile -Force
        Write-Host 'Removed the stale Vite development marker; Zazu will use same-origin compiled assets.'
    }

    $needsBuild = -not (Test-Path $manifestFile -PathType Leaf)
    if (-not $needsBuild) {
        try {
            $existingManifest = Get-Content $manifestFile -Raw | ConvertFrom-Json
            $existingEntries = @($existingManifest.PSObject.Properties)
            if ($existingEntries.Count -eq 0) {
                $needsBuild = $true
            } else {
                foreach ($entry in $existingEntries) {
                    $assetFiles = @()
                    if (-not [string]::IsNullOrWhiteSpace($entry.Value.file)) { $assetFiles += $entry.Value.file }
                    foreach ($cssFile in @($entry.Value.css)) {
                        if (-not [string]::IsNullOrWhiteSpace($cssFile)) { $assetFiles += $cssFile }
                    }
                    foreach ($asset in @($entry.Value.assets)) {
                        if (-not [string]::IsNullOrWhiteSpace($asset)) { $assetFiles += $asset }
                    }
                    foreach ($assetFile in ($assetFiles | Select-Object -Unique)) {
                        $assetPath = Join-Path $buildDirectory ($assetFile -replace '/', [System.IO.Path]::DirectorySeparatorChar)
                        if (-not (Test-Path $assetPath -PathType Leaf)) {
                            $needsBuild = $true
                            break
                        }
                    }
                    if ($needsBuild) { break }
                }
            }

            # A pulled CSS/JS source newer than the tracked manifest means the
            # ignored local bundle may be stale even when its old files exist.
            if (-not $needsBuild) {
                $manifestTime = (Get-Item $manifestFile).LastWriteTimeUtc
                $sourceFiles = @()
                $cssDirectory = Join-Path $ProjectPath 'resources\css'
                $jsEntry = Join-Path $ProjectPath 'resources\js\app.js'
                if (Test-Path $cssDirectory -PathType Container) {
                    $sourceFiles += @(Get-ChildItem $cssDirectory -Filter '*.css' -File -Recurse)
                }
                if (Test-Path $jsEntry -PathType Leaf) { $sourceFiles += Get-Item $jsEntry }
                if (@($sourceFiles | Where-Object { $_.LastWriteTimeUtc -gt $manifestTime.AddSeconds(2) }).Count -gt 0) {
                    $needsBuild = $true
                }
            }
        } catch {
            Write-Warning "The existing frontend manifest needs rebuilding: $($_.Exception.Message)"
            $needsBuild = $true
        }
    }

    if ($needsBuild) {
        $nodeModules = Join-Path $ProjectPath 'node_modules'
        if (-not (Test-Path $nodeModules -PathType Container)) {
            throw 'Zazu frontend assets are missing or stale and node_modules is not installed. From the project folder, run npm ci once, then start Zazu again.'
        }

        $npmCommand = Get-Command npm.cmd -ErrorAction SilentlyContinue | Select-Object -First 1
        if (-not $npmCommand) {
            $npmCommand = Get-Command npm.exe -ErrorAction SilentlyContinue | Select-Object -First 1
        }
        if (-not $npmCommand) {
            throw 'Zazu frontend assets are missing or stale and npm was not found. Install Node.js/npm, run npm ci once, then start Zazu again.'
        }

        Write-Host 'Building the Zazu frontend bundle because its manifest or compiled assets are missing or stale.'
        Push-Location $ProjectPath
        try {
            & $npmCommand.Source run build
            if ($LASTEXITCODE -ne 0) {
                throw "Zazu frontend build failed with exit code $LASTEXITCODE. Review the build output before starting Zazu."
            }
        } finally {
            Pop-Location
        }
    }

    if (-not (Test-Path $manifestFile -PathType Leaf)) {
        throw 'The frontend build completed without creating public/build/manifest.json. Zazu was not started.'
    }

    try {
        $manifest = Get-Content $manifestFile -Raw | ConvertFrom-Json
    } catch {
        throw "The compiled frontend manifest is invalid after build: $manifestFile"
    }

    $entries = @($manifest.PSObject.Properties)
    if ($entries.Count -eq 0) {
        throw 'The compiled frontend manifest is empty. Zazu was not started.'
    }

    foreach ($entry in $entries) {
        $assetFiles = @()
        if (-not [string]::IsNullOrWhiteSpace($entry.Value.file)) { $assetFiles += $entry.Value.file }
        foreach ($cssFile in @($entry.Value.css)) {
            if (-not [string]::IsNullOrWhiteSpace($cssFile)) { $assetFiles += $cssFile }
        }
        foreach ($asset in @($entry.Value.assets)) {
            if (-not [string]::IsNullOrWhiteSpace($asset)) { $assetFiles += $asset }
        }
        foreach ($assetFile in ($assetFiles | Select-Object -Unique)) {
            $assetPath = Join-Path $buildDirectory ($assetFile -replace '/', [System.IO.Path]::DirectorySeparatorChar)
            if (-not (Test-Path $assetPath -PathType Leaf)) {
                throw "A compiled frontend asset referenced by the manifest is still missing after build: $assetPath"
            }
        }
    }

    Write-Host 'Compiled Zazu CSS/JavaScript manifest and every referenced asset are ready.'
}

if (($Lan -or $BuiltAssetsOnly) -and -not $Stop) {
    Prepare-ZazuBuiltAssets
}

$php = $null
if (-not [string]::IsNullOrWhiteSpace($PhpPath)) {
    if (-not (Test-Path $PhpPath -PathType Leaf)) {
        throw "The requested PHP executable does not exist: $PhpPath"
    }
    $php = (Resolve-Path $PhpPath).Path
} else {
    $phpCandidates = @()
    $phpCommand = Get-Command php.exe -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($phpCommand) { $phpCandidates += $phpCommand.Source }
    if (Test-Path 'C:\PHP84\php.exe' -PathType Leaf) {
        # Known standalone PHP location used by the documented owner-PC setup.
        $phpCandidates += 'C:\PHP84\php.exe'
    }

    foreach ($candidate in ($phpCandidates | Select-Object -Unique)) {
        $candidateVersion = (& $candidate -r 'echo PHP_VERSION;' 2>$null | Out-String).Trim()
        try { $candidateParsedVersion = [version]$candidateVersion } catch { continue }
        if ($candidateParsedVersion -ge [version]'8.4') {
            $php = $candidate
            break
        }
    }
}
if (-not $php) {
    throw 'PHP 8.4+ was not found on PATH or at the documented C:\PHP84\php.exe location. Supply -PhpPath if installed elsewhere.'
}
$phpVersion = (& $php -r 'echo PHP_VERSION;' 2>$null | Out-String).Trim()
try { $parsedVersion = [version]$phpVersion } catch { throw "Could not read PHP version from $php." }
if ($parsedVersion -lt [version]'8.4') {
    throw "Zazu requires PHP 8.4+. Found $phpVersion at $php."
}

# Idempotently keep the daily owner-PC backup task aligned with this installation path.
$backupScheduleInstaller = Join-Path $PSScriptRoot 'Install-ZazuBackupSchedule.ps1'
if (Test-Path $backupScheduleInstaller -PathType Leaf) {
    try {
        & $backupScheduleInstaller -ProjectPath $ProjectPath
    } catch {
        Write-Warning ("The daily backup task could not be confirmed: {0}" -f $_.Exception.Message)
    }
}

# A cached Laravel config takes precedence over .env. Clear it for the
# daily-log owner-PC configuration as well as HTTPS-only session-cookie mode.
$configCache = Join-Path $ProjectPath 'bootstrap\cache\config.php'
if (($usesDailyLogStack -or $SecureCookies) -and (Test-Path $configCache -PathType Leaf)) {
    Push-Location $ProjectPath
    try {
        & $php $artisan 'config:clear' '--no-interaction'
        if ($LASTEXITCODE -ne 0) {
            throw 'Could not clear stale Laravel configuration cache before applying owner-PC log/security settings.'
        }
    } finally {
        Pop-Location
    }
}

$listeners = Get-ZazuListener
if ($listeners.Count -gt 0) {
    $alreadyZazu = $true
    $wrongBinding = @()

    foreach ($listener in $listeners) {
        $process = Get-ProcessDetails ([int]$listener.OwningProcess)
        if (-not $process -or $process.Name -ne 'php.exe' -or $process.CommandLine -notlike "*$ProjectPath*") {
            $alreadyZazu = $false
            continue
        }

        if ($listener.LocalAddress -ne $BindAddress) {
            $wrongBinding += [string]$listener.LocalAddress
            $alreadyZazu = $false
        }
    }

    if ($alreadyZazu) {
        # Source files can be updated while the existing PHP process remains alive.
        # Never silently report a healthy launch when that process is serving code
        # whose database migrations have not been applied.
        Push-Location $ProjectPath
        try {
            $statusOutput = @(& $php $artisan 'migrate:status' '--no-ansi' 2>&1 | ForEach-Object { "$_" })
            $statusExitCode = $LASTEXITCODE
            $statusText = $statusOutput -join [Environment]::NewLine
            $migrationTableMissing = $statusText -match '(?i)migration table.*not found'

            if ($statusExitCode -ne 0 -and -not $migrationTableMissing) {
                throw ("Zazu is already running, but its database update status could not be checked. No changes were made." + [Environment]::NewLine + $statusText)
            }

            $pendingStatusRows = @($statusOutput | Where-Object { $_.Trim() -eq 'Pending' })
            $hasPendingMigrations = $migrationTableMissing -or ($pendingStatusRows.Count -gt 0)
            if ($hasPendingMigrations) {
                throw 'Zazu is already running with pending database migrations. No migration was applied while the server was active. Double-click Stop-Zazu-Dev.cmd, then Start-Zazu-Dev.cmd to run the verified backup-first update before reopening Zazu.'
            }
        } finally {
            Pop-Location
        }

        Write-Host ("Zazu is already listening at http://{0}:{1}; migration status is current." -f $BindAddress, $Port)
        return
    }

    if ($wrongBinding.Count -gt 0 -and $listeners.Count -eq $wrongBinding.Count) {
        $existingAddresses = ($wrongBinding | Sort-Object -Unique) -join ', '
        throw "Zazu is already running on port $Port but is bound to '$existingAddresses', not '$BindAddress'. Stop it with Stop-Zazu-Dev.cmd, then start the requested mode. Nothing was changed."
    }

    throw "Port $Port is already in use by another process or an incompatible listener. Nothing was changed."
}


function Prepare-ZazuDatabase([string]$PhpExecutable) {
    Push-Location $ProjectPath
    try {
        Write-Host 'Checking for Zazu database updates...'
        $statusOutput = @(& $PhpExecutable $artisan 'migrate:status' '--no-ansi' 2>&1 | ForEach-Object { "$_" })
        $statusExitCode = $LASTEXITCODE
        $statusText = $statusOutput -join [Environment]::NewLine
        $migrationTableMissing = $statusText -match '(?i)migration table.*not found'

        if ($statusExitCode -ne 0 -and -not $migrationTableMissing) {
            throw ("Zazu could not check the database update status. The server was not started." + [Environment]::NewLine + $statusText)
        }

        $hasPendingMigrations = $migrationTableMissing -or ($statusText -match '(?im)\bPending\s*$')
        if (-not $hasPendingMigrations) {
            Write-Host 'The Zazu database is up to date.'
            return
        }

        # A database update can change how existing records are stored. Make
        # and verify a full installation backup (database + private files)
        # before running any migration.
        $backupParent = Split-Path -Parent $ProjectPath
        $projectName = Split-Path -Leaf $ProjectPath
        $backupStamp = Get-Date -Format 'yyyyMMdd-HHmmss'
        $backupDirectory = Join-Path $backupParent (Join-Path ($projectName + '-backups') ('before-update-' + $backupStamp))
        New-Item -ItemType Directory -Path $backupDirectory -Force | Out-Null

        Write-Host 'Database updates are needed. Creating a backup of the database and private files first...'
        $backupOutput = @(& $PhpExecutable $artisan 'zazu:backup' "--output=$backupDirectory" 2>&1 | ForEach-Object { "$_" })
        $backupExitCode = $LASTEXITCODE
        $backupArchives = @(Get-ChildItem -Path $backupDirectory -Filter 'zazu-backup-*.zip' -File -ErrorAction SilentlyContinue)

        if ($backupExitCode -ne 0 -or $backupArchives.Count -ne 1 -or $backupArchives[0].Length -le 0) {
            throw ("Zazu could not create a complete backup, so no database updates were run and the server was not started." + [Environment]::NewLine + "Backup output:" + [Environment]::NewLine + ($backupOutput -join [Environment]::NewLine))
        }

        $backupPath = $backupArchives[0].FullName
        Write-Host ("Backup ready: {0}" -f $backupPath)
        Write-Host 'Applying Zazu database updates...'

        $migrationOutput = @(& $PhpExecutable $artisan 'migrate' '--force' '--no-interaction' 2>&1 | ForEach-Object { "$_" })
        $migrationExitCode = $LASTEXITCODE
        if ($migrationExitCode -ne 0) {
            throw ("Zazu could not finish the database update. The server was not started. The backup is available at:" + [Environment]::NewLine + $backupPath + [Environment]::NewLine + "Update output:" + [Environment]::NewLine + ($migrationOutput -join [Environment]::NewLine))
        }

        $verifyOutput = @(& $PhpExecutable $artisan 'migrate:status' '--no-ansi' 2>&1 | ForEach-Object { "$_" })
        $verifyExitCode = $LASTEXITCODE
        $verifyText = $verifyOutput -join [Environment]::NewLine
        if ($verifyExitCode -ne 0 -or $verifyText -match '(?im)\bPending\s*$' -or $verifyText -match '(?i)migration table.*not found') {
            throw ("Zazu could not confirm that all database updates finished. The server was not started. The backup is available at:" + [Environment]::NewLine + $backupPath + [Environment]::NewLine + "Check output:" + [Environment]::NewLine + $verifyText)
        }

        Write-Host 'Zazu database updates are complete.'
        Write-Host ("Recovery backup: {0}" -f $backupPath)
    } finally {
        Pop-Location
    }
}

Prepare-ZazuDatabase $php

$dailyBackupDirectory = Join-Path $ProjectPath 'storage\app\zazu-backups\daily'
$latestDailyBackup = Get-ChildItem -Path $dailyBackupDirectory -Filter 'zazu-backup-*.zip' -File -ErrorAction SilentlyContinue |
    Sort-Object LastWriteTimeUtc -Descending | Select-Object -First 1
if (-not $latestDailyBackup) {
    Write-Warning 'No routine installation backup was found. The daily task should create one at 02:00; keep a separate copy on BitLocker-protected USB or in an encrypted, access-controlled cloud vault.'
} else {
    $backupAge = (Get-Date).ToUniversalTime() - $latestDailyBackup.LastWriteTimeUtc
    Write-Host ("Latest installation backup: {0} ({1} hours ago)" -f $latestDailyBackup.Name, [math]::Floor($backupAge.TotalHours))
    if ($backupAge.TotalHours -gt 26) {
        Write-Warning 'The latest installation backup is overdue. Check the Zazu EMP Daily Backup task and copy a current archive to separate USB/cloud storage.'
    }
}

New-Item -ItemType Directory -Path $logDirectory -Force | Out-Null
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$stdout = Join-Path $logDirectory "zazu-dev-$stamp.out.log"
$stderr = Join-Path $logDirectory "zazu-dev-$stamp.err.log"

$previousSecureCookieValue = [Environment]::GetEnvironmentVariable('SESSION_SECURE_COOKIE', 'Process')
try {
    if ($SecureCookies) {
        # Override the installer’s localhost-HTTP default only for this HTTPS-hosted PHP process.
        $env:SESSION_SECURE_COOKIE = 'true'
    }

    $process = Start-Process -FilePath $php -ArgumentList @('artisan', 'serve', "--host=$BindAddress", "--port=$Port") -WorkingDirectory $ProjectPath -RedirectStandardOutput $stdout -RedirectStandardError $stderr -WindowStyle Hidden -PassThru
} finally {
    if ($SecureCookies) {
        [Environment]::SetEnvironmentVariable('SESSION_SECURE_COOKIE', $previousSecureCookieValue, 'Process')
    }
}

function Test-ZazuTcpEndpoint {
    # Get-NetTCPConnection can return an empty result on some Windows 10
    # PowerShell/network-stack combinations even while PHP reports its
    # listener is running. A real loopback TCP handshake is the readiness
    # check that matters to the reverse proxy.
    $client = New-Object System.Net.Sockets.TcpClient
    try {
        $connect = $client.BeginConnect($BindAddress, $Port, $null, $null)
        if (-not $connect.AsyncWaitHandle.WaitOne(750, $false)) {
            return $false
        }
        $client.EndConnect($connect)
        return $client.Connected
    } catch {
        return $false
    } finally {
        $client.Close()
    }
}

function Get-ZazuStartupLogTail {
    $sections = @()
    foreach ($log in @(@{ Label = 'STDERR'; Path = $stderr }, @{ Label = 'STDOUT'; Path = $stdout })) {
        if (Test-Path $log.Path -PathType Leaf) {
            $tail = Get-Content $log.Path -Tail 40 -ErrorAction SilentlyContinue
            if ($tail) {
                $sections += ("--- {0} ({1}) ---" -f $log.Label, $log.Path)
                $sections += $tail
            }
        }
    }
    if ($sections.Count -eq 0) {
        return '(Laravel has not written any startup output yet.)'
    }
    return ($sections -join [Environment]::NewLine)
}

$ready = $false
# Allow a bounded cold boot on the owner's local HDD rather than timing out after 15 seconds.
for ($attempt = 0; $attempt -lt 45; $attempt++) {
    Start-Sleep -Seconds 1
    if ($process.HasExited) {
        $details = Get-ZazuStartupLogTail
        throw ("Zazu server exited during startup. Startup output:`n{0}" -f $details)
    }
    if (Test-ZazuTcpEndpoint) {
        $ready = $true
        break
    }
}

if (-not $ready) {
    $details = Get-ZazuStartupLogTail
    Stop-Process -Id $process.Id -Force -ErrorAction SilentlyContinue
    throw ("Zazu did not open port $Port within 45 seconds. Startup output:`n{0}" -f $details)
}

Write-Host "Zazu development server is running independently of VS Code."
Write-Host ("URL: http://{0}:{1}" -f $BindAddress, $Port)
Write-Host "PHP: $phpVersion ($php)"
Write-Host "Logs: $logDirectory"
Write-Host "Stop it by double-clicking Stop-Zazu-Dev.cmd."
if ($Lan) {
    Write-Host "LAN mode: bound only to the active private Wi-Fi address."
    Write-Host "Use this URL only from devices on the same trusted Wi-Fi network."
    Write-Host "The server is HTTP-only; installed PWA service-worker/offline acceptance requires a secure HTTPS origin."
    Write-Host "No Windows Firewall or router settings were changed."
} else {
    Write-Host "Loopback development only: this HTTP server is not a production host and does not provide a secure PWA origin to other devices."
}
