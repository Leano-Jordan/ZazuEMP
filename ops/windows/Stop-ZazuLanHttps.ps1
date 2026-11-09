[CmdletBinding()]
param(
    [string]$ProjectPath = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path,
    [ValidateRange(1024, 65535)]
    [int]$AppPort = 8000
)

$ErrorActionPreference = 'Stop'
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

$stopServer = Join-Path $PSScriptRoot 'Start-ZazuDev.ps1'
& $stopServer -Stop -Port $AppPort
