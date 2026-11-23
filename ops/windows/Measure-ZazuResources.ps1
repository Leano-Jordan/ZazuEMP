[CmdletBinding()]
param(
    [ValidateRange(1, 86400)]
    [int]$DurationSeconds = 300,

    [ValidateRange(1, 60)]
    [int]$IntervalSeconds = 5,

    [string]$ProjectPath = (Join-Path $PSScriptRoot '..\..'),

    [int[]]$ListenPorts = @(8000, 8443),

    [string]$OutputDirectory = (Join-Path $env:USERPROFILE 'Documents\Zazu\Performance')
)

$ErrorActionPreference = 'Stop'

function Get-CimSafe {
    param([string]$ClassName, [string]$Filter)
    try {
        if ([string]::IsNullOrWhiteSpace($Filter)) {
            return @(Get-CimInstance -ClassName $ClassName -ErrorAction Stop)
        }
        return @(Get-CimInstance -ClassName $ClassName -Filter $Filter -ErrorAction Stop)
    } catch {
        return @()
    }
}

function Get-ZazuProcessIds {
    param([string]$RootPath, [int[]]$Ports)

    $processes = Get-CimSafe -ClassName 'Win32_Process'
    $listenerIds = @()
    if (Get-Command Get-NetTCPConnection -ErrorAction SilentlyContinue) {
        try {
            $listenerIds = @(Get-NetTCPConnection -State Listen -LocalPort $Ports -ErrorAction Stop |
                Select-Object -ExpandProperty OwningProcess -Unique)
        } catch {
            $listenerIds = @()
        }
    }

    $roots = @($processes | Where-Object {
        $_.Name -ieq 'php.exe' -and (
            $_.ProcessId -in $listenerIds -or
            $_.CommandLine -like "*$RootPath*"
        )
    } | ForEach-Object { [int]$_.ProcessId })

    $ids = @($roots)
    $changed = $true
    while ($changed) {
        $changed = $false
        foreach ($process in $processes) {
            if ($process.ParentProcessId -in $ids -and [int]$process.ProcessId -notin $ids) {
                $ids += [int]$process.ProcessId
                $changed = $true
            }
        }
    }

    $databaseListenerIds = @()
    if (Get-Command Get-NetTCPConnection -ErrorAction SilentlyContinue) {
        try {
            $databaseListenerIds = @(Get-NetTCPConnection -State Listen -LocalPort @(3306, 3307) -ErrorAction Stop |
                Select-Object -ExpandProperty OwningProcess -Unique)
        } catch {
            $databaseListenerIds = @()
        }
    }

    foreach ($process in $processes) {
        if ($process.Name -match '^(mysqld|mariadbd)\.exe$' -and
            ($process.ProcessId -in $databaseListenerIds -or $process.CommandLine -like "*$RootPath*")) {
            if ([int]$process.ProcessId -notin $ids) {
                $ids += [int]$process.ProcessId
            }
        }
        if ($process.Name -ieq 'caddy.exe' -and $process.ProcessId -in $listenerIds) {
            if ([int]$process.ProcessId -notin $ids) {
                $ids += [int]$process.ProcessId
            }
        }
    }

    return @($ids | Select-Object -Unique)
}

function Get-ZazuDataBytes {
    param([string]$RootPath)
    $total = [int64]0
    foreach ($relative in @('database', 'storage\app\private', 'storage\app\backups')) {
        $path = Join-Path $RootPath $relative
        if (Test-Path -LiteralPath $path) {
            try {
                $sum = Get-ChildItem -LiteralPath $path -File -Recurse -Force -ErrorAction SilentlyContinue |
                    Measure-Object -Property Length -Sum
                if ($sum.Sum) { $total += [int64]$sum.Sum }
            } catch { }
        }
    }
    return $total
}

$resolvedProject = (Resolve-Path -LiteralPath $ProjectPath).Path
$projectDrive = [IO.Path]::GetPathRoot($resolvedProject).TrimEnd('\')
if (-not (Test-Path -LiteralPath $OutputDirectory)) {
    New-Item -ItemType Directory -Path $OutputDirectory -Force | Out-Null
}
$stamp = (Get-Date).ToUniversalTime().ToString('yyyyMMdd-HHmmssZ')
$csvPath = Join-Path $OutputDirectory "zazu-resource-samples-$stamp.csv"
$manifestPath = Join-Path $OutputDirectory "zazu-resource-manifest-$stamp.json"

$computer = Get-CimSafe -ClassName 'Win32_ComputerSystem' | Select-Object -First 1
$operatingSystem = Get-CimSafe -ClassName 'Win32_OperatingSystem' | Select-Object -First 1
$processors = Get-CimSafe -ClassName 'Win32_Processor'
$logicalDisk = Get-CimSafe -ClassName 'Win32_LogicalDisk' -Filter "DeviceID='$projectDrive'" | Select-Object -First 1
$physicalDisks = Get-CimSafe -ClassName 'Win32_DiskDrive' | ForEach-Object {
    [pscustomobject]@{
        Model = $_.Model
        MediaType = $_.MediaType
        InterfaceType = $_.InterfaceType
        SizeGB = if ($_.Size) { [math]::Round(([double]$_.Size / 1GB), 1) } else { $null }
    }
}

$manifest = [ordered]@{
    capturedAtUtc = (Get-Date).ToUniversalTime().ToString('o')
    durationSeconds = $DurationSeconds
    intervalSeconds = $IntervalSeconds
    osCaption = $operatingSystem.Caption
    osVersion = $operatingSystem.Version
    cpuModel = ($processors | Select-Object -First 1 -ExpandProperty Name)
    logicalProcessors = [Environment]::ProcessorCount
    physicalCores = ($processors | Measure-Object -Property NumberOfCores -Sum).Sum
    installedMemoryGB = if ($computer.TotalPhysicalMemory) { [math]::Round(([double]$computer.TotalPhysicalMemory / 1GB), 1) } else { $null }
    projectDrive = $projectDrive
    projectDriveSizeGB = if ($logicalDisk.Size) { [math]::Round(([double]$logicalDisk.Size / 1GB), 1) } else { $null }
    diskDevices = @($physicalDisks)
    projectDataBytesAtStart = Get-ZazuDataBytes -RootPath $resolvedProject
    processScope = 'Zazu PHP listener/descendants and matching local MariaDB/MySQL/Caddy processes; host metrics are system-wide'
    networkScope = 'System-wide network adapter throughput; application-specific network latency must be measured by browser request timings'
    privacy = 'Local-only capture. No telemetry is uploaded. No usernames, IP addresses, URLs, file names, database contents or credentials are recorded.'
}
$manifest | ConvertTo-Json -Depth 5 | Set-Content -LiteralPath $manifestPath -Encoding UTF8

$previousCpu = @{}
$previousSampleAt = $null
$sampleCount = 0
$deadline = (Get-Date).AddSeconds($DurationSeconds)

Write-Host "Zazu resource capture started."
Write-Host "Project: $resolvedProject"
Write-Host "CSV: $csvPath"
Write-Host "Manifest: $manifestPath"
Write-Host "Duration: $DurationSeconds seconds; interval: $IntervalSeconds seconds"
Write-Host "This capture stays on this PC. It does not upload data."

while ((Get-Date) -le $deadline) {
    $sampleAt = Get-Date
    $elapsed = if ($previousSampleAt) { [math]::Max(0.1, ($sampleAt - $previousSampleAt).TotalSeconds) } else { $null }

    $cpuCounter = Get-CimSafe -ClassName 'Win32_PerfFormattedData_PerfOS_Processor' -Filter "Name='_Total'" |
        Select-Object -First 1
    $memoryCounter = Get-CimSafe -ClassName 'Win32_PerfFormattedData_PerfOS_Memory' | Select-Object -First 1
    $diskCounter = Get-CimSafe -ClassName 'Win32_PerfFormattedData_PerfDisk_PhysicalDisk' |
        Where-Object { $_.Name -eq '_Total' } | Select-Object -First 1
    $networkCounters = Get-CimSafe -ClassName 'Win32_PerfFormattedData_Tcpip_NetworkInterface'

    $receivedBytes = [double](($networkCounters | Measure-Object -Property BytesReceivedPersec -Sum).Sum)
    $sentBytes = [double](($networkCounters | Measure-Object -Property BytesSentPersec -Sum).Sum)
    $processIds = @(Get-ZazuProcessIds -RootPath $resolvedProject -Ports $ListenPorts)
    $zazuProcesses = @()
    $zazuCpu = 0.0
    $zazuWorkingSet = [int64]0
    $zazuPrivateBytes = [int64]0
    $zazuReadBytesPerSec = 0.0
    $zazuWriteBytesPerSec = 0.0

    if ($processIds.Count -gt 0) {
        $processPerf = Get-CimSafe -ClassName 'Win32_PerfFormattedData_PerfProc_Process'
        foreach ($processId in $processIds) {
            $process = Get-Process -Id $processId -ErrorAction SilentlyContinue
            if (-not $process) { continue }

            $cpuPercent = 0.0
            if ($null -ne $elapsed -and $previousCpu.ContainsKey([string]$processId)) {
                $cpuDelta = [math]::Max(0, ([double]$process.CPU - [double]$previousCpu[[string]$processId]))
                $cpuPercent = ($cpuDelta / $elapsed / [math]::Max(1, [Environment]::ProcessorCount)) * 100
                $zazuCpu += $cpuPercent
            }
            $previousCpu[[string]$processId] = [double]$process.CPU
            $zazuWorkingSet += [int64]$process.WorkingSet64
            $zazuPrivateBytes += [int64]$process.PrivateMemorySize64

            $perf = $processPerf | Where-Object { [int]$_.IDProcess -eq [int]$processId } | Select-Object -First 1
            if ($perf) {
                $zazuReadBytesPerSec += [double]$perf.IOReadBytesPersec
                $zazuWriteBytesPerSec += [double]$perf.IOWriteBytesPersec
            }

            $zazuProcesses += $process.ProcessName
        }
    }

    $availableMB = if ($memoryCounter.AvailableMBytes) { [double]$memoryCounter.AvailableMBytes } else { $null }
    $totalMemoryGB = if ($computer.TotalPhysicalMemory) { [double]$computer.TotalPhysicalMemory / 1GB } else { $null }
    $memoryUsedPercent = if ($null -ne $availableMB -and $totalMemoryGB -gt 0) {
        [math]::Round((1 - ($availableMB / ($totalMemoryGB * 1024))) * 100, 2)
    } else { $null }

    $row = [pscustomobject][ordered]@{
        TimestampUtc = $sampleAt.ToUniversalTime().ToString('o')
        HostCpuPercent = if ($cpuCounter) { [double]$cpuCounter.PercentProcessorTime } else { $null }
        HostMemoryUsedPercent = $memoryUsedPercent
        HostMemoryAvailableMB = $availableMB
        HostDiskReadBytesPerSec = if ($diskCounter) { [double]$diskCounter.DiskReadBytesPersec } else { $null }
        HostDiskWriteBytesPerSec = if ($diskCounter) { [double]$diskCounter.DiskWriteBytesPersec } else { $null }
        HostNetworkReceivedBytesPerSec = $receivedBytes
        HostNetworkSentBytesPerSec = $sentBytes
        ZazuProcessCount = $processIds.Count
        ZazuProcessNames = (@($zazuProcesses | Select-Object -Unique) -join ';')
        ZazuCpuPercentOfWholeMachine = [math]::Round($zazuCpu, 2)
        ZazuWorkingSetMB = [math]::Round(($zazuWorkingSet / 1MB), 2)
        ZazuPrivateMemoryMB = [math]::Round(($zazuPrivateBytes / 1MB), 2)
        ZazuProcessIoReadBytesPerSec = [math]::Round($zazuReadBytesPerSec, 0)
        ZazuProcessIoWriteBytesPerSec = [math]::Round($zazuWriteBytesPerSec, 0)
        ZazuProcessMatched = ($processIds.Count -gt 0)
    }

    if (Test-Path -LiteralPath $csvPath) {
        $row | Export-Csv -LiteralPath $csvPath -NoTypeInformation -Append
    } else {
        $row | Export-Csv -LiteralPath $csvPath -NoTypeInformation
    }

    $sampleCount++
    $previousSampleAt = $sampleAt
    if ((Get-Date) -lt $deadline) {
        Start-Sleep -Seconds ([math]::Min($IntervalSeconds, [math]::Max(1, ($deadline - (Get-Date)).TotalSeconds)))
    }
}

$manifest.projectDataBytesAtEnd = Get-ZazuDataBytes -RootPath $resolvedProject
$manifest.sampleCount = $sampleCount
$manifest.completedAtUtc = (Get-Date).ToUniversalTime().ToString('o')
$manifest | ConvertTo-Json -Depth 5 | Set-Content -LiteralPath $manifestPath -Encoding UTF8

Write-Host "Capture complete: $sampleCount samples."
Write-Host "CSV: $csvPath"
Write-Host "Manifest: $manifestPath"
Write-Host "If ZazuProcessMatched is false in every row, start Zazu with its normal launcher and repeat."
