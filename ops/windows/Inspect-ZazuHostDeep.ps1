# Read-only deep host forensics for Zazu EMP.
# Does not start/stop services, edit files, inspect credentials, or change network settings.
[CmdletBinding()]
param(
    [string]$ProjectPath = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path,
    [int]$KnownPhpPid = 10268
)

$ErrorActionPreference = 'Continue'

function Section([string]$Name) {
    Write-Output ""
    Write-Output "===== $Name ====="
}

function Read-EnvValue([string]$Name) {
    $envPath = Join-Path $ProjectPath '.env'
    if (-not (Test-Path $envPath)) { return $null }
    $line = Get-Content $envPath | Where-Object { $_ -match "^\s*$([regex]::Escape($Name))\s*=" } | Select-Object -Last 1
    if (-not $line) { return $null }
    $value = ($line -replace "^\s*$([regex]::Escape($Name))\s*=", '').Trim()
    if ($value.Length -ge 2 -and (($value.StartsWith('"') -and $value.EndsWith('"')) -or ($value.StartsWith("'") -and $value.EndsWith("'")))) {
        $value = $value.Substring(1, $value.Length - 2)
    }
    return $value
}

function Safe-CommandLine([string]$Value) {
    if (-not $Value) { return '(unavailable)' }
    return ($Value -replace '(?i)(--?(?:password|passwd|token|secret|key)(?:=|\s+))("[^"]*"|\S+)', '$1[REDACTED]')
}

Write-Output "ZAZU DEEP LOCAL HOST FORENSICS"
Write-Output "Timestamp: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss zzz')"
Write-Output "Computer: $env:COMPUTERNAME"
Write-Output "Project: $ProjectPath"
Write-Output "READ ONLY: no services, files, databases, firewall, certificates or network settings will be changed."
Write-Output "Credential values are not collected."

Section "WINDOWS BUILD AND UPDATE INDICATORS"
$cv = Get-ItemProperty 'HKLM:\SOFTWARE\Microsoft\Windows NT\CurrentVersion' -ErrorAction SilentlyContinue
[pscustomobject]@{
    ProductName = $cv.ProductName
    DisplayVersion = $cv.DisplayVersion
    CurrentBuild = $cv.CurrentBuild
    UBR = $cv.UBR
    EditionID = $cv.EditionID
    BuildLabEx = $cv.BuildLabEx
} | Format-List | Out-String | Write-Output
Write-Output "Recent installed hotfixes (not proof of ESU enrollment):"
Get-HotFix -ErrorAction SilentlyContinue |
    Sort-Object InstalledOn -Descending |
    Select-Object -First 8 HotFixID, InstalledOn, Description |
    Format-Table -AutoSize | Out-String | Write-Output

Section "PHP WEB LISTENER PROCESS"
$connections = Get-NetTCPConnection -State Listen -LocalPort 8000 -ErrorAction SilentlyContinue
if (-not $connections) { Write-Output "No listener currently detected on TCP 8000." }
foreach ($connection in $connections) {
    $pidValue = [int]$connection.OwningProcess
    $proc = Get-CimInstance Win32_Process -Filter "ProcessId = $pidValue" -ErrorAction SilentlyContinue
    [pscustomobject]@{
        Address = $connection.LocalAddress
        Port = $connection.LocalPort
        PID = $pidValue
        ProcessName = if ($proc) { [IO.Path]::GetFileName($proc.ExecutablePath) } else { '(unavailable)' }
        ExecutablePath = if ($proc) { $proc.ExecutablePath } else { '(unavailable)' }
        ParentPID = if ($proc) { $proc.ParentProcessId } else { $null }
        CommandLine = if ($proc) { Safe-CommandLine $proc.CommandLine } else { '(unavailable)' }
    } | Format-List | Out-String | Write-Output
}
if ($KnownPhpPid -gt 0 -and -not ($connections | Where-Object { $_.OwningProcess -eq $KnownPhpPid })) {
    $proc = Get-CimInstance Win32_Process -Filter "ProcessId = $KnownPhpPid" -ErrorAction SilentlyContinue
    if ($proc) {
        Write-Output "Previously reported PID $KnownPhpPid still exists but is not listening on TCP 8000:"
        [pscustomobject]@{
            ExecutablePath = $proc.ExecutablePath
            ParentPID = $proc.ParentProcessId
            CommandLine = Safe-CommandLine $proc.CommandLine
        } | Format-List | Out-String | Write-Output
    } else {
        Write-Output "Previously reported PID $KnownPhpPid is no longer running."
    }
}

Section "WEB AND DATABASE SERVICES"
Get-CimInstance Win32_Service -ErrorAction SilentlyContinue |
    Where-Object {
        $_.Name -match 'apache|httpd|mysql|maria|nginx' -or
        $_.DisplayName -match 'apache|httpd|mysql|maria|nginx'
    } |
    Select-Object Name, DisplayName, State, StartMode, StartName, PathName |
    Format-List | Out-String | Write-Output

Section "AMPPS INSTALLATION AND EXECUTABLE VERSIONS"
$roots = @('C:\Program Files\Ampps', 'C:\Program Files (x86)\Ampps', 'C:\Ampps', 'D:\Ampps') |
    Where-Object { Test-Path $_ }
if (-not $roots) { Write-Output "No standard AMPPS directory found." }
foreach ($root in $roots) {
    Write-Output "AMPPS root: $root"
    $candidates = @(
        (Join-Path $root 'Ampps.exe'),
        (Join-Path $root 'apache\bin\httpd.exe'),
        (Join-Path $root 'php\php.exe'),
        (Join-Path $root 'mysql\bin\mysqld.exe'),
        (Join-Path $root 'mariadb\bin\mariadbd.exe')
    )
    foreach ($file in $candidates) {
        if (Test-Path $file) {
            $item = Get-Item $file
            [pscustomobject]@{
                Path = $file
                FileVersion = $item.VersionInfo.FileVersion
                ProductVersion = $item.VersionInfo.ProductVersion
                SizeMB = [math]::Round($item.Length / 1MB, 2)
            } | Format-List | Out-String | Write-Output
        }
    }
}

Section "PHP CLI AND AMPPS PHP RUNTIMES"
$phpCandidates = @()
$pathPhp = Get-Command php -ErrorAction SilentlyContinue | Select-Object -First 1
if ($pathPhp) { $phpCandidates += $pathPhp.Source }
foreach ($root in $roots) {
    $candidate = Join-Path $root 'php\php.exe'
    if (Test-Path $candidate) { $phpCandidates += $candidate }
}
foreach ($php in ($phpCandidates | Select-Object -Unique)) {
    Write-Output "PHP executable: $php"
    & $php -v 2>&1 | Select-Object -First 2 | ForEach-Object { Write-Output "  $_" }
    Write-Output "PHP configuration:"
    & $php --ini 2>&1 | ForEach-Object { Write-Output "  $_" }
    Write-Output "Loaded extensions:"
    & $php -m 2>&1 | ForEach-Object { Write-Output "  $_" }
}

Section "APACHE CONFIGURATION REFERENCES"
foreach ($root in $roots) {
    $confRoot = Join-Path $root 'apache\conf'
    if (Test-Path $confRoot) {
        Write-Output "Apache config directory: $confRoot"
        Get-ChildItem $confRoot -File -Include 'httpd.conf','httpd-ssl.conf' -Recurse -ErrorAction SilentlyContinue |
            Select-Object -ExpandProperty FullName | ForEach-Object { Write-Output "  $_" }
        $mainConf = Join-Path $confRoot 'httpd.conf'
        if (Test-Path $mainConf) {
            Write-Output "Relevant Apache directives (paths/modules only):"
            Select-String -Path $mainConf -Pattern '^\s*(LoadModule\s+php|PHPIniDir|LoadModule\s+ssl|Include|ServerRoot|Listen)\b' |
                ForEach-Object { Write-Output "  $($_.Line.Trim())" }
        }
    }
}

Section "ACTIVE ZAZU DATABASE CONFIGURATION (NO CREDENTIALS)"
$dbConnection = Read-EnvValue 'DB_CONNECTION'
$dbDatabase = Read-EnvValue 'DB_DATABASE'
$dbHost = Read-EnvValue 'DB_HOST'
$dbPort = Read-EnvValue 'DB_PORT'
[pscustomobject]@{
    DB_CONNECTION = if ($dbConnection) { $dbConnection } else { '(not set; Laravel default applies)' }
    DB_DATABASE = if ($dbDatabase) { $dbDatabase } else { '(not set)' }
    DB_HOST = if ($dbHost) { $dbHost } else { '(not set)' }
    DB_PORT = if ($dbPort) { $dbPort } else { '(not set)' }
} | Format-List | Out-String | Write-Output
if ($dbConnection -eq 'sqlite') {
    $sqlitePath = if ($dbDatabase) {
        if ([IO.Path]::IsPathRooted($dbDatabase)) { $dbDatabase } else { Join-Path $ProjectPath $dbDatabase }
    } else {
        Join-Path $ProjectPath 'database\database.sqlite'
    }
    if (Test-Path $sqlitePath) {
        $dbFile = Get-Item $sqlitePath
        [pscustomobject]@{
            SQLitePath = $sqlitePath
            Exists = $true
            SizeMB = [math]::Round($dbFile.Length / 1MB, 2)
            LastWriteTime = $dbFile.LastWriteTime
        } | Format-List | Out-String | Write-Output
    } else {
        Write-Output "Resolved SQLite file does not exist: $sqlitePath"
    }
}

Section "DATABASE CONFIG FILE LOCATIONS"
foreach ($root in $roots) {
    foreach ($relative in @('mysql\my.ini','mysql\my.cnf','mariadb\my.ini','my.ini')) {
        $config = Join-Path $root $relative
        if (Test-Path $config) {
            Write-Output "Database config found: $config"
            Select-String -Path $config -Pattern '^\s*(datadir|basedir|port)\s*=' |
                ForEach-Object { Write-Output "  $($_.Line.Trim())" }
        }
    }
}

Section "ZAZU PRIVATE STORAGE FOOTPRINT"
$privateRoots = @(
    (Join-Path $ProjectPath 'storage\app\private'),
    (Join-Path $ProjectPath 'storage\app')
)
foreach ($path in ($privateRoots | Select-Object -Unique)) {
    if (Test-Path $path) {
        $files = Get-ChildItem $path -File -Recurse -Force -ErrorAction SilentlyContinue
        $bytes = ($files | Measure-Object -Property Length -Sum).Sum
        [pscustomobject]@{
            Path = $path
            FileCount = @($files).Count
            TotalMB = [math]::Round(($bytes / 1MB), 2)
        } | Format-List | Out-String | Write-Output
    }
}

Section "DISK SPACE"
Get-CimInstance Win32_LogicalDisk -Filter "DriveType=3" |
    Select-Object DeviceID,
        @{Name='SizeGB';Expression={[math]::Round($_.Size / 1GB, 1)}},
        @{Name='FreeGB';Expression={[math]::Round($_.FreeSpace / 1GB, 1)}} |
    Format-Table -AutoSize | Out-String | Write-Output

Section "END"
Write-Output "Send output for review. It includes local paths, process command lines, database name/path and network-adjacent configuration; redact anything you do not want to share."
