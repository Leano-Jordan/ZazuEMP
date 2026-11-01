# Read-only Zazu host inspection. This script makes no configuration changes.
[CmdletBinding()]
param(
    [string]$ProjectPath = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
)

$ErrorActionPreference = 'Continue'

function Write-Section([string]$Name) {
    Write-Output ""
    Write-Output "===== $Name ====="
}

Write-Output "ZAZU LOCAL PC HOST INSPECTION"
Write-Output "Timestamp: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss zzz')"
Write-Output "Computer: $env:COMPUTERNAME"
Write-Output "Project path: $ProjectPath"
Write-Output "READ ONLY: no services, firewall rules, files, databases or network settings will be changed."

Write-Section "WINDOWS"
Get-CimInstance Win32_OperatingSystem |
    Select-Object Caption, Version, BuildNumber, OSArchitecture |
    Format-List | Out-String | Write-Output

Write-Section "NETWORK"
Get-NetIPConfiguration |
    Where-Object { $_.IPv4Address -and $_.NetAdapter.Status -eq 'Up' } |
    ForEach-Object {
        [pscustomobject]@{
            Interface = $_.InterfaceAlias
            IPv4      = ($_.IPv4Address.IPAddress -join ', ')
            Gateway   = ($_.IPv4DefaultGateway.NextHop -join ', ')
            DNS       = ($_.DNSServer.ServerAddresses -join ', ')
        }
    } | Format-Table -AutoSize | Out-String | Write-Output

Write-Section "RUNTIME COMMANDS"
foreach ($name in @('php', 'composer', 'node', 'npm')) {
    $command = Get-Command $name -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($command) {
        Write-Output "$name path: $($command.Source)"
        if ($name -eq 'php') {
            & $command.Source -v 2>&1 | Select-Object -First 2 | ForEach-Object { Write-Output "  $_" }
            Write-Output "PHP extensions:"
            & $command.Source -m 2>&1 | ForEach-Object { Write-Output "  $_" }
        } elseif ($name -eq 'node' -or $name -eq 'npm') {
            & $command.Source --version 2>&1 | ForEach-Object { Write-Output "  $_" }
        } elseif ($name -eq 'composer') {
            & $command.Source --version 2>&1 | Select-Object -First 1 | ForEach-Object { Write-Output "  $_" }
        }
    } else {
        Write-Output "$name : not found on PATH"
    }
}

Write-Section "COMMON AMPPS LOCATIONS"
$amppsCandidates = @(
    'C:\Program Files\Ampps',
    'C:\Program Files (x86)\Ampps',
    'C:\Ampps',
    'D:\Ampps'
)
foreach ($candidate in $amppsCandidates) {
    if (Test-Path $candidate) {
        Write-Output "Found: $candidate"
        foreach ($relative in @('php', 'apache', 'mysql')) {
            $path = Join-Path $candidate $relative
            if (Test-Path $path) { Write-Output "  $relative directory: $path" }
        }
    }
}

Write-Section "WEB AND DATABASE SERVICES"
Get-CimInstance Win32_Service |
    Where-Object {
        $_.Name -match 'apache|httpd|mysql|maria|nginx' -or
        $_.DisplayName -match 'apache|httpd|mysql|maria|nginx'
    } |
    Select-Object Name, DisplayName, State, StartMode, StartName, PathName |
    Format-List | Out-String | Write-Output

Write-Section "LISTENING WEB/DATABASE PORTS"
$listeners = Get-NetTCPConnection -State Listen -ErrorAction SilentlyContinue |
    Where-Object { $_.LocalPort -in @(80, 443, 3306, 8000, 8080) } |
    Select-Object LocalAddress, LocalPort, OwningProcess
$listenerRows = foreach ($listener in $listeners) {
    $process = Get-Process -Id $listener.OwningProcess -ErrorAction SilentlyContinue
    [pscustomobject]@{
        Address = $listener.LocalAddress
        Port    = $listener.LocalPort
        PID     = $listener.OwningProcess
        Process = $process.ProcessName
    }
}
$listenerRows | Format-Table -AutoSize | Out-String | Write-Output

Write-Section "PROJECT CHECK"
if (Test-Path $ProjectPath) {
    foreach ($relative in @('artisan', 'public\index.php', 'vendor\autoload.php', 'public\build\manifest.json', '.env')) {
        $path = Join-Path $ProjectPath $relative
        Write-Output ("{0}: {1}" -f $relative, (Test-Path $path))
    }
    if (Test-Path (Join-Path $ProjectPath 'composer.json')) {
        $composer = Get-Content (Join-Path $ProjectPath 'composer.json') -Raw | ConvertFrom-Json
        Write-Output "Required PHP: $($composer.require.php)"
        Write-Output "Laravel constraint: $($composer.require.'laravel/framework')"
    }
} else {
    Write-Output "Project path does not exist: $ProjectPath"
}

Write-Section "DISK SPACE"
Get-CimInstance Win32_LogicalDisk -Filter "DriveType=3" |
    Select-Object DeviceID,
        @{Name='SizeGB';Expression={[math]::Round($_.Size / 1GB, 1)}},
        @{Name='FreeGB';Expression={[math]::Round($_.FreeSpace / 1GB, 1)}} |
    Format-Table -AutoSize | Out-String | Write-Output

Write-Section "END"
Write-Output "Send the output back for review. It contains local network and software configuration details; remove anything you do not want to share."
