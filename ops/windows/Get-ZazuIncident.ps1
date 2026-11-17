[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [ValidatePattern('^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$')]
    [string]$Reference,

    [string]$ProjectPath = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path,

    [string]$OutputPath = (Join-Path ([Environment]::GetFolderPath('Desktop')) "Zazu-Incident-$Reference.txt")
)

$ErrorActionPreference = 'Stop'
$logDirectory = Join-Path $ProjectPath 'storage\logs'
if (-not (Test-Path $logDirectory -PathType Container)) {
    throw "Zazu log directory was not found: $logDirectory"
}

$allowedFields = @(
    'request_id',
    'error_code',
    'category',
    'severity',
    'http_status',
    'exception_class',
    'originating_component',
    'route_name',
    'http_method',
    'http_path',
    'timestamp',
    'file',
    'line',
    'database_connection',
    'database_driver',
    'database_sqlstate',
    'database_driver_code',
    'database_operation'
)

$matches = @()
$logFiles = @(Get-ChildItem -Path $logDirectory -Filter 'laravel*.log' -File -ErrorAction SilentlyContinue)
foreach ($logFile in $logFiles) {
    foreach ($line in (Select-String -Path $logFile.FullName -SimpleMatch $Reference -ErrorAction SilentlyContinue)) {
        $jsonStart = $line.Line.IndexOf('{')
        if ($jsonStart -lt 0) { continue }

        try {
            $context = $line.Line.Substring($jsonStart) | ConvertFrom-Json
        } catch {
            continue
        }

        if ($context.request_id -ne $Reference) { continue }

        $safeRecord = [ordered]@{}
        foreach ($field in $allowedFields) {
            $property = $context.PSObject.Properties[$field]
            if ($null -ne $property) {
                $safeRecord[$field] = $property.Value
            }
        }
        $matches += [pscustomobject]$safeRecord
    }
}

if ($matches.Count -eq 0) {
    $report = @(
        "No matching structured incident record found for $Reference."
        'The installation may not yet have the diagnostic update, or the incident has not been reproduced since that update.'
        'Restart the updated Zazu installation and reproduce the issue once before running this capture again.'
    ) -join [Environment]::NewLine
    Set-Content -LiteralPath $OutputPath -Value $report -Encoding UTF8
    Write-Output $report
    Write-Output "Report saved to: $OutputPath"
    Start-Process -FilePath 'notepad.exe' -ArgumentList @($OutputPath)
    exit 2
}

$report = $matches | ConvertTo-Json -Depth 4
Set-Content -LiteralPath $OutputPath -Value $report -Encoding UTF8
Write-Output $report
Write-Output "Safe report saved to: $OutputPath"
Start-Process -FilePath 'notepad.exe' -ArgumentList @($OutputPath)
