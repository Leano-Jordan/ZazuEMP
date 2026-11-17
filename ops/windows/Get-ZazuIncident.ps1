[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [ValidatePattern('^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$')]
    [string]$Reference,

    [string]$ProjectPath = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
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
    Write-Output "No matching structured incident record found for $Reference."
    Write-Output 'Confirm Zazu was restarted on the updated installation after the diagnostic update, then reproduce the issue once.'
    exit 2
}

$matches | ConvertTo-Json -Depth 4
