[CmdletBinding()]
param(
    [string]$ProjectPath,
    [ValidateRange(1024, 65535)]
    [int]$AppPort = 8000
)

$ErrorActionPreference = 'Stop'

# Resolve paths after parameter binding. In some Windows PowerShell 5.1
# launch contexts, $PSScriptRoot is empty during default parameter evaluation,
# causing this stop routine to fail before cleanup begins.
$scriptPath = $MyInvocation.MyCommand.Path
if ([string]::IsNullOrWhiteSpace($scriptPath)) {
    $scriptPath = $PSCommandPath
}
if ([string]::IsNullOrWhiteSpace($scriptPath)) {
    throw 'Cannot determine the stop script path. Run Stop-Zazu-LAN-HTTPS.cmd from the repository checkout.'
}
$scriptDirectory = Split-Path -Parent $scriptPath
if ([string]::IsNullOrWhiteSpace($ProjectPath)) {
    $ProjectPath = (Resolve-Path (Join-Path $scriptDirectory '..\..')).Path
}
$ProjectPath = [System.IO.Path]::GetFullPath($ProjectPath).TrimEnd('\')
$stateDirectory = Join-Path $ProjectPath 'storage\framework\zazu-lan-https'
$pidPath = Join-Path $stateDirectory 'caddy.pid'
$configPath = Join-Path $stateDirectory 'Caddyfile'

if (Test-Path $pidPath) {
    $rawPid = (Get-Content $pidPath -Raw).Trim()
    $processId = 0
    if ([int]::TryParse($rawPid, [ref]$processId) -and $processId -gt 0) {
        $process = Get-CimInstance Win32_Process -Filter "ProcessId = $processId" -ErrorAction SilentlyContinue
        if ($process) {
            if ($process.Name -ne 'caddy.exe' -or $process.CommandLine -notlike "*$configPath*") {
                throw 'The saved LAN HTTPS PID does not match the expected Caddy process/configuration. Nothing was stopped.'
            }
            Stop-Process -Id $processId -Force
            Write-Host 'Stopped the verified Zazu Caddy HTTPS proxy.'
        } else {
            Write-Host 'The saved Caddy process is no longer running.'
        }
    }
    Remove-Item $pidPath -Force -ErrorAction SilentlyContinue
} else {
    Write-Host 'No saved Zazu LAN HTTPS proxy process was found.'
}

$stopServer = Join-Path $scriptDirectory 'Start-ZazuDev.ps1'
& $stopServer -ProjectPath $ProjectPath -Stop -Port $AppPort
