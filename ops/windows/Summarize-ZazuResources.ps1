[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [ValidateScript({ Test-Path -LiteralPath $_ -PathType Leaf })]
    [string]$CsvPath,

    [Parameter(Mandatory = $true)]
    [ValidateScript({ Test-Path -LiteralPath $_ -PathType Leaf })]
    [string]$ManifestPath,

    [string]$OutputPath = ''
)

$ErrorActionPreference = 'Stop'
$culture = [System.Globalization.CultureInfo]::InvariantCulture
$rows = @(Import-Csv -LiteralPath $CsvPath)
if ($rows.Count -lt 1) {
    throw 'The resource CSV contains no samples.'
}

$manifest = Get-Content -LiteralPath $ManifestPath -Raw | ConvertFrom-Json
if ([string]::IsNullOrWhiteSpace($OutputPath)) {
    $OutputPath = [System.IO.Path]::ChangeExtension($CsvPath, 'report.md')
}

function ConvertTo-ResourceNumber {
    param([AllowNull()][string]$Value)
    $number = 0.0
    if ([string]::IsNullOrWhiteSpace($Value)) { return $null }
    if ([double]::TryParse($Value, [System.Globalization.NumberStyles]::Float, $culture, [ref]$number)) {
        return $number
    }
    return $null
}

function Get-ResourcePercentile {
    param(
        [double[]]$Values,
        [double]$Percentile
    )
    if (-not $Values -or $Values.Count -eq 0) { return $null }
    $ordered = @($Values | Sort-Object)
    $index = [math]::Max(0, [math]::Min($ordered.Count - 1, ([math]::Ceiling($Percentile * $ordered.Count) - 1)))
    return [double]$ordered[$index]
}

function Format-ResourceValue {
    param([AllowNull()][object]$Value)
    if ($null -eq $Value) { return 'n/a' }
    return $Value.ToString('0.00', $culture)
}

$metricDefinitions = @(
    @{ Label = 'Host CPU'; Field = 'HostCpuPercent'; Unit = '%' },
    @{ Label = 'Zazu process CPU (whole-machine share)'; Field = 'ZazuCpuPercentOfWholeMachine'; Unit = '%' },
    @{ Label = 'Host memory used'; Field = 'HostMemoryUsedPercent'; Unit = '%' },
    @{ Label = 'Zazu working set'; Field = 'ZazuWorkingSetMB'; Unit = 'MB' },
    @{ Label = 'Zazu private memory'; Field = 'ZazuPrivateMemoryMB'; Unit = 'MB' },
    @{ Label = 'Host disk active time'; Field = 'HostDiskActivePercent'; Unit = '%' },
    @{ Label = 'Host disk read'; Field = 'HostDiskReadBytesPerSec'; Unit = 'bytes/s' },
    @{ Label = 'Host disk write'; Field = 'HostDiskWriteBytesPerSec'; Unit = 'bytes/s' },
    @{ Label = 'Project volume used'; Field = 'ProjectDriveUsedPercent'; Unit = '%' },
    @{ Label = 'Project volume free'; Field = 'ProjectDriveFreeGB'; Unit = 'GB' },
    @{ Label = 'Host network receive'; Field = 'HostNetworkReceivedBytesPerSec'; Unit = 'bytes/s' },
    @{ Label = 'Host network send'; Field = 'HostNetworkSentBytesPerSec'; Unit = 'bytes/s' },
    @{ Label = 'Zazu process I/O read'; Field = 'ZazuProcessIoReadBytesPerSec'; Unit = 'bytes/s' },
    @{ Label = 'Zazu process I/O write'; Field = 'ZazuProcessIoWriteBytesPerSec'; Unit = 'bytes/s' }
)

$matchedSamples = @($rows | Where-Object { $_.ZazuProcessMatched -eq 'True' }).Count
$lines = New-Object 'System.Collections.Generic.List[string]'
$lines.Add('# Zazu device performance report')
$lines.Add('')
$lines.Add(('- Captured: {0}' -f $manifest.capturedAtUtc))
$lines.Add(('- Device cohort: {0}' -f $manifest.deviceTier))
$lines.Add(('- CPU: {0}' -f $manifest.cpuModel))
$lines.Add(('- Logical processors: {0}; physical cores: {1}' -f $manifest.logicalProcessors, $manifest.physicalCores))
$lines.Add(('- Installed RAM: {0} GB' -f $manifest.installedMemoryGB))
$lines.Add(('- Zazu project storage type: {0}' -f $manifest.projectStorageType))
$storageSummary = @($manifest.diskDevices | ForEach-Object {
    '{0} ({1} GB; {2})' -f $_.Model, $_.SizeGB, $_.InterfaceType
}) -join '; '
$lines.Add(('- Storage devices: {0}' -f $(if ($storageSummary) { $storageSummary } else { 'not detected' })))
$startDataMB = if ($null -ne $manifest.projectDataBytesAtStart) { [math]::Round(([double]$manifest.projectDataBytesAtStart / 1MB), 2) } else { $null }
$endDataMB = if ($null -ne $manifest.projectDataBytesAtEnd) { [math]::Round(([double]$manifest.projectDataBytesAtEnd / 1MB), 2) } else { $null }
$dataDeltaMB = if ($null -ne $startDataMB -and $null -ne $endDataMB) { [math]::Round(($endDataMB - $startDataMB), 2) } else { $null }
$lines.Add(('- Zazu data footprint: {0} MB at start; {1} MB at end; {2} MB change' -f $startDataMB, $endDataMB, $dataDeltaMB))
$lines.Add(('- OS: {0} ({1})' -f $manifest.osCaption, $manifest.osVersion))
$lines.Add(('- Source commit: {0}' -f $(if ($manifest.buildCommitSha) { $manifest.buildCommitSha } else { 'not supplied' })))
$lines.Add(('- Package SHA-256: {0}' -f $(if ($manifest.packageSha256) { $manifest.packageSha256 } else { 'not supplied' })))
$lines.Add(('- Samples: {0}; Zazu process matched: {1}' -f $rows.Count, $matchedSamples))
$lines.Add('')
$lines.Add('| Metric | Unit | Samples | p50 | p95 | Minimum | Maximum |')
$lines.Add('|---|---:|---:|---:|---:|---:|---:|')

foreach ($metric in $metricDefinitions) {
    $values = @(
        foreach ($row in $rows) {
            $value = ConvertTo-ResourceNumber -Value ([string]$row.($metric.Field))
            if ($null -ne $value) { $value }
        }
    )
    $values = @($values | ForEach-Object { [double]$_ })
    if ($values.Count -eq 0) {
        $lines.Add(('| {0} | {1} | 0 | n/a | n/a | n/a | n/a |' -f $metric.Label, $metric.Unit))
        continue
    }
    $p50 = Get-ResourcePercentile -Values $values -Percentile 0.50
    $p95 = Get-ResourcePercentile -Values $values -Percentile 0.95
    $minimum = ($values | Measure-Object -Minimum).Minimum
    $maximum = ($values | Measure-Object -Maximum).Maximum
    $lines.Add(('| {0} | {1} | {2} | {3} | {4} | {5} | {6} |' -f $metric.Label, $metric.Unit, $values.Count, (Format-ResourceValue $p50), (Format-ResourceValue $p95), (Format-ResourceValue $minimum), (Format-ResourceValue $maximum)))
}

$lines.Add('')
$lines.Add('## Interpretation')
$lines.Add('')
$lines.Add('- Host CPU, memory, disk and network values describe the whole Windows device, not only Zazu.')
$lines.Add('- Zazu process CPU, working set, private memory and process I/O are scoped to the matched Zazu process tree.')
$lines.Add('- Process I/O includes more than disk activity; do not label it as disk-only.')
$lines.Add('- Blank or unavailable values mean missing/unmatched measurements, not zero usage.')
$lines.Add('- This local report contains no customer records and is not uploaded.')
$lines | Set-Content -LiteralPath $OutputPath -Encoding UTF8
Write-Host "Zazu performance report written: $OutputPath"
