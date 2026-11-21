[CmdletBinding()]
param([string]$ProjectPath)

$ErrorActionPreference = 'Stop'
if ([string]::IsNullOrWhiteSpace($ProjectPath)) {
    $ProjectPath = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
}
$ProjectPath = [System.IO.Path]::GetFullPath($ProjectPath).TrimEnd('\')
$runner = Join-Path $PSScriptRoot 'Invoke-ZazuScheduledBackup.ps1'
if (-not (Test-Path (Join-Path $ProjectPath 'artisan') -PathType Leaf) -or -not (Test-Path $runner -PathType Leaf)) {
    throw 'Cannot register the Zazu backup task because the installation or backup runner is missing.'
}

$sha = [System.Security.Cryptography.SHA256]::Create()
try {
    $digest = $sha.ComputeHash([System.Text.Encoding]::UTF8.GetBytes($ProjectPath.ToLowerInvariant()))
} finally {
    $sha.Dispose()
}
$suffix = ([BitConverter]::ToString($digest) -replace '-', '').Substring(0, 10)
$taskName = 'ZazuEMP-DailyBackup-' + $suffix
$powerShell = Join-Path $PSHOME 'powershell.exe'
$arguments = '-NoProfile -NonInteractive -ExecutionPolicy Bypass -File "{0}" -ProjectPath "{1}"' -f $runner, $ProjectPath
$action = New-ScheduledTaskAction -Execute $powerShell -Argument $arguments -WorkingDirectory $ProjectPath
$trigger = New-ScheduledTaskTrigger -Daily -At '2:00AM'
$principal = New-ScheduledTaskPrincipal -UserId ([System.Security.Principal.WindowsIdentity]::GetCurrent().Name) -LogonType S4U -RunLevel Limited
$settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -WakeToRun -MultipleInstances IgnoreNew -ExecutionTimeLimit (New-TimeSpan -Hours 2) -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries

Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Principal $principal -Settings $settings -Description 'Creates a daily verified Zazu EMP installation backup and retains the latest 14 archives.' -Force | Out-Null
Write-Output ("Registered daily Zazu backup task '{0}'." -f $taskName)
