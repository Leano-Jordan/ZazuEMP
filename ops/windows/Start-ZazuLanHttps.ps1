[CmdletBinding()]
param(
    [string]$ProjectPath = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path,
    [string]$PhpPath,
    [ValidateRange(1024, 65535)]
    [int]$AppPort = 8000,
    [ValidateRange(1024, 65535)]
    [int]$HttpsPort = 8443
)

$ErrorActionPreference = 'Stop'
$ProjectPath = [System.IO.Path]::GetFullPath($ProjectPath).TrimEnd('\')

$caddyCommand = Get-Command caddy.exe -ErrorAction SilentlyContinue | Select-Object -First 1
if (-not $caddyCommand) {
    throw 'Caddy was not found on PATH. Install the official Caddy Windows executable, then reopen this launcher. No download or system change was attempted.'
}
$caddy = $caddyCommand.Source

$wifiProfile = Get-NetConnectionProfile -ErrorAction Stop |
    Where-Object {
        $_.NetworkCategory -eq 'Private' -and
        $_.IPv4Connectivity -ne 'Disconnected' -and
        $_.InterfaceAlias -match '(?i)wi-?fi|wireless|wlan'
    } |
    Select-Object -First 1

if (-not $wifiProfile) {
    throw 'HTTPS LAN mode requires an active Wi-Fi connection classified as Private. No firewall or network settings were changed.'
}

$wifiAddress = Get-NetIPAddress -InterfaceIndex $wifiProfile.InterfaceIndex -AddressFamily IPv4 -ErrorAction Stop |
    Where-Object {
        $_.AddressState -eq 'Preferred' -and
        $_.IPAddress -notmatch '^(127\.|169\.254\.)'
    } |
    Select-Object -First 1

if (-not $wifiAddress) {
    throw "No usable IPv4 address was found on '$($wifiProfile.InterfaceAlias)'."
}

$lanIp = $wifiAddress.IPAddress
$stateDirectory = Join-Path $ProjectPath 'storage\framework\zazu-lan-https'
$configPath = Join-Path $stateDirectory 'Caddyfile'
$pidPath = Join-Path $stateDirectory 'caddy.pid'
$stdoutPath = Join-Path $stateDirectory 'caddy.out.log'
$stderrPath = Join-Path $stateDirectory 'caddy.err.log'
$startServer = Join-Path $PSScriptRoot 'Start-ZazuDev.ps1'

if (-not (Test-Path $startServer -PathType Leaf)) {
    throw "The Zazu host launcher is missing: $startServer"
}

New-Item -ItemType Directory -Path $stateDirectory -Force | Out-Null

$existing = @(Get-NetTCPConnection -LocalPort $HttpsPort -State Listen -ErrorAction SilentlyContinue)
if ($existing.Count -gt 0) {
    $knownPid = 0
    if (Test-Path $pidPath) {
        $rawPid = (Get-Content $pidPath -Raw).Trim()
        [void][int]::TryParse($rawPid, [ref]$knownPid)
    }
    $knownProcess = if ($knownPid -gt 0) { Get-CimInstance Win32_Process -Filter "ProcessId = $knownPid" -ErrorAction SilentlyContinue } else { $null }
    if ($knownProcess -and $knownProcess.Name -eq 'caddy.exe' -and $knownProcess.CommandLine -like "*$configPath*") {
        $currentAddressListener = $existing | Where-Object { $_.LocalAddress -eq $lanIp } | Select-Object -First 1
        if ($currentAddressListener) {
            Write-Host "Zazu LAN HTTPS is already running at https://$($lanIp):$HttpsPort"
            exit 0
        }
        throw "Caddy is still running for a previous Wi-Fi address. Stop it with Stop-Zazu-LAN-HTTPS.cmd, then start it again for $lanIp."
    }
    throw "TCP $HttpsPort is already occupied by an unverified listener. Nothing was stopped."
}

# Ensure Laravel remains loopback-only behind the TLS proxy.
$startArguments = @{
    BindAddress = '127.0.0.1'
    Port = $AppPort
}
if (-not [string]::IsNullOrWhiteSpace($PhpPath)) {
    $startArguments.PhpPath = $PhpPath
}

$config = @"
{
    admin 127.0.0.1:2019
}

https://$($lanIp):$HttpsPort {
    bind $lanIp
    tls internal
    reverse_proxy 127.0.0.1:$AppPort
}
"@
Set-Content -Path $configPath -Value $config -Encoding ASCII

$validation = & $caddy validate --config $configPath --adapter caddyfile 2>&1
if ($LASTEXITCODE -ne 0) {
    throw "Caddy rejected the generated LAN configuration: $($validation | Out-String)"
}

# Validate the proxy before starting Laravel so a bad Caddy configuration
# cannot leave an otherwise-unreachable application process running.
& $startServer @startArguments

Remove-Item $pidPath -Force -ErrorAction SilentlyContinue
$arguments = 'run --config "{0}" --adapter caddyfile' -f $configPath
$caddyProcess = $null
try {
    $caddyProcess = Start-Process -FilePath $caddy -ArgumentList $arguments -WorkingDirectory $ProjectPath -RedirectStandardOutput $stdoutPath -RedirectStandardError $stderrPath -WindowStyle Hidden -PassThru
    Set-Content -Path $pidPath -Value $caddyProcess.Id -Encoding ASCII
} catch {
    $startupError = $_
    if ($caddyProcess -and -not $caddyProcess.HasExited) {
        Stop-Process -Id $caddyProcess.Id -Force -ErrorAction SilentlyContinue
    }
    Remove-Item $pidPath -Force -ErrorAction SilentlyContinue
    try {
        & $startServer -Stop -Port $AppPort
    } catch {
        Write-Warning "Laravel cleanup also failed after the Caddy startup error: $($_.Exception.Message)"
    }
    throw $startupError
}

$ready = $false
for ($attempt = 0; $attempt -lt 20; $attempt++) {
    Start-Sleep -Seconds 1
    if ($caddyProcess.HasExited) {
        $details = if (Test-Path $stderrPath) { Get-Content $stderrPath -Raw } else { '(no Caddy stderr log)' }
        Remove-Item $pidPath -Force -ErrorAction SilentlyContinue
        & $startServer -Stop -Port $AppPort
        throw "Caddy exited during startup. Review $stderrPath. $details"
    }
    $listener = Get-NetTCPConnection -LocalPort $HttpsPort -State Listen -ErrorAction SilentlyContinue |
        Where-Object { $_.LocalAddress -eq $lanIp } |
        Select-Object -First 1
    if ($listener) {
        $ready = $true
        break
    }
}
if (-not $ready) {
    Stop-Process -Id $caddyProcess.Id -Force -ErrorAction SilentlyContinue
    Remove-Item $pidPath -Force -ErrorAction SilentlyContinue
    & $startServer -Stop -Port $AppPort
    throw "Caddy did not bind to $lanIp on TCP $HttpsPort within 20 seconds. Review $stderrPath."
}

$appDataDirectory = Join-Path $env:APPDATA 'Caddy'
$environmentOutput = & $caddy environ 2>&1 | Out-String
if ($environmentOutput -match '(?m)^caddy\.AppDataDir\s*=\s*(.+)$') {
    $appDataDirectory = $Matches[1].Trim()
}
$rootCertificate = Join-Path $appDataDirectory 'pki\authorities\local\root.crt'

Write-Host ''
Write-Host 'Zazu LAN HTTPS proxy is running.'
Write-Host "Phone/tablet URL: https://$($lanIp):$HttpsPort"
Write-Host "Laravel app:      http://127.0.0.1:$AppPort (loopback only)"
Write-Host "Caddy root CA:    $rootCertificate"
Write-Host "Caddy logs:       $stateDirectory"
Write-Host ''
Write-Host 'Trust only the public root.crt certificate on each test device. Never copy or share the CA private key.'
Write-Host 'If Windows Firewall blocks access, allow inbound TCP 8443 on the Private profile only.'
Write-Host 'No firewall or router settings were changed. Do not use this on public/guest Wi-Fi or expose the port to the internet.'
Write-Host 'Stop both processes with Stop-Zazu-LAN-HTTPS.cmd.'
