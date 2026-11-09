[CmdletBinding()]
param(
    [string]$ProjectPath = (Resolve-Path (Join-Path $PSScriptRoot '..\\..')).Path,
    [string]$BindAddress = '127.0.0.1',
    [string]$PhpPath,
    [switch]$Lan,
    [ValidateRange(1024, 65535)]
    [int]$Port = 8000,
    [switch]$Stop
)

$ErrorActionPreference = 'Stop'
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
        } elseif ($server.CommandLine -like "*$ProjectPath*" -and $server.CommandLine -like '*artisan serve*') {
            if (-not $stopped.ContainsKey([string]$server.ProcessId)) {
                Stop-Process -Id ([int]$server.ProcessId) -Force
                $stopped[[string]$server.ProcessId] = $true
            }
        } else {
            throw "Could not verify the Zazu Artisan parent for listener PID $($server.ProcessId). Nothing further was stopped."
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

$php = $null
if (-not [string]::IsNullOrWhiteSpace($PhpPath)) {
    if (-not (Test-Path $PhpPath -PathType Leaf)) {
        throw "The requested PHP executable does not exist: $PhpPath"
    }
    $php = (Resolve-Path $PhpPath).Path
} else {
    $phpCommand = Get-Command php.exe -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($phpCommand) {
        $php = $phpCommand.Source
    } elseif (Test-Path 'C:\PHP84\php.exe' -PathType Leaf) {
        # Known standalone PHP location used by the documented owner-PC setup.
        $php = 'C:\PHP84\php.exe'
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
        Write-Host ("Zazu is already listening at http://{0}:{1}" -f $BindAddress, $Port)
        exit 0
    }

    if ($wrongBinding.Count -gt 0 -and $listeners.Count -eq $wrongBinding.Count) {
        $existingAddresses = ($wrongBinding | Sort-Object -Unique) -join ', '
        throw "Zazu is already running on port $Port but is bound to '$existingAddresses', not '$BindAddress'. Stop it with Stop-Zazu-Dev.cmd, then start the requested mode. Nothing was changed."
    }

    throw "Port $Port is already in use by another process or an incompatible listener. Nothing was changed."
}

New-Item -ItemType Directory -Path $logDirectory -Force | Out-Null
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$stdout = Join-Path $logDirectory "zazu-dev-$stamp.out.log"
$stderr = Join-Path $logDirectory "zazu-dev-$stamp.err.log"

$process = Start-Process -FilePath $php -ArgumentList @('artisan', 'serve', "--host=$BindAddress", "--port=$Port") -WorkingDirectory $ProjectPath -RedirectStandardOutput $stdout -RedirectStandardError $stderr -WindowStyle Hidden -PassThru

$ready = $false
for ($attempt = 0; $attempt -lt 15; $attempt++) {
    Start-Sleep -Seconds 1
    if ($process.HasExited) {
        throw "Zazu server exited during startup. Review $stderr and $stdout."
    }
    if ((Get-ZazuListener).Count -gt 0) {
        $ready = $true
        break
    }
}

if (-not $ready) {
    Stop-Process -Id $process.Id -Force -ErrorAction SilentlyContinue
    throw "Zazu did not open port $Port within 15 seconds. Review $stderr and $stdout."
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
