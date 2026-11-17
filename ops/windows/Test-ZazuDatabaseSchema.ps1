[CmdletBinding()]
param(
    [string]$ProjectPath = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path,
    [string]$PhpPath,
    [string]$OutputPath = (Join-Path ([Environment]::GetFolderPath('Desktop')) 'Zazu-Database-Schema-Check.json')
)

$ErrorActionPreference = 'Stop'
$ProjectPath = [System.IO.Path]::GetFullPath($ProjectPath)
$artisan = Join-Path $ProjectPath 'artisan'
$autoload = Join-Path $ProjectPath 'vendor\autoload.php'

if (-not (Test-Path $artisan -PathType Leaf) -or -not (Test-Path $autoload -PathType Leaf)) {
    throw "Zazu project files or Composer dependencies were not found under $ProjectPath."
}

if ([string]::IsNullOrWhiteSpace($PhpPath)) {
    $command = Get-Command php.exe -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($command) { $PhpPath = $command.Source }
    elseif (Test-Path 'C:\PHP84\php.exe' -PathType Leaf) { $PhpPath = 'C:\PHP84\php.exe' }
    else { throw 'PHP 8.4+ was not found. Supply -PhpPath with the existing Zazu PHP executable.' }
}
if (-not (Test-Path $PhpPath -PathType Leaf)) { throw "PHP executable was not found: $PhpPath" }

$phpVersion = (& $PhpPath -r 'echo PHP_VERSION;' 2>$null | Out-String).Trim()
try { $parsedVersion = [version]$phpVersion } catch { throw "Could not read PHP version from $PhpPath." }
if ($parsedVersion -lt [version]'8.4') { throw "Zazu requires PHP 8.4+. Found $phpVersion." }

Push-Location $ProjectPath
$tempPhp = Join-Path ([IO.Path]::GetTempPath()) ("zazu-db-check-" + [guid]::NewGuid().ToString('N') + '.php')
try {
    Write-Host 'Reading Laravel migration status (no database writes)...'
    $migrationOutput = @(& $PhpPath $artisan 'migrate:status' '--no-ansi' 2>&1 | ForEach-Object { "$_" })
    $migrationExit = $LASTEXITCODE

    $php = @'
<?php
require getcwd() . '/vendor/autoload.php';
$app = require getcwd() . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$connection = DB::connection();
$driver = $connection->getDriverName();
$result = [
    'driver' => $driver,
    'migration_table_exists' => Schema::hasTable('migrations'),
    'schedule_migration_recorded' => false,
    'events_table_exists' => Schema::hasTable('events'),
    'events_required_columns' => [],
    'events_missing_columns' => [],
    'business_user_table_exists' => Schema::hasTable('business_user'),
    'business_user_primary_niche_exists' => false,
    'integrity_check' => null,
];

if ($driver !== 'sqlite') {
    throw new RuntimeException('This diagnostic is for the local SQLite installation; detected driver: ' . $driver);
}

if ($result['migration_table_exists']) {
    $result['schedule_migration_recorded'] = DB::table('migrations')
        ->where('migration', '2026_11_17_000001_add_event_schedule_fields')
        ->exists();
}

$required = ['event_end_date', 'event_start_time', 'event_end_time', 'setup_date', 'collection_date'];
if ($result['events_table_exists']) {
    $columns = Schema::getColumnListing('events');
    $result['events_required_columns'] = array_values(array_intersect($required, $columns));
    $result['events_missing_columns'] = array_values(array_diff($required, $columns));
}

if ($result['business_user_table_exists']) {
    $result['business_user_primary_niche_exists'] = Schema::hasColumn('business_user', 'primary_niche');
}

$result['integrity_check'] = DB::selectOne('PRAGMA integrity_check')->integrity_check ?? null;

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), PHP_EOL;
'@
    [IO.File]::WriteAllText($tempPhp, $php, [Text.UTF8Encoding]::new($false))

    $schemaOutput = @(& $PhpPath $tempPhp 2>&1 | ForEach-Object { "$_" })
    $schemaExit = $LASTEXITCODE
    if ($schemaExit -ne 0) {
        throw ("Read-only SQLite inspection failed:" + [Environment]::NewLine + ($schemaOutput -join [Environment]::NewLine))
    }

    try { $schema = ($schemaOutput -join [Environment]::NewLine) | ConvertFrom-Json }
    catch { throw ("SQLite inspection did not return valid JSON:" + [Environment]::NewLine + ($schemaOutput -join [Environment]::NewLine)) }

    $report = [ordered]@{
        checked_at = (Get-Date).ToString('o')
        php_version = $phpVersion
        migration_status_exit_code = $migrationExit
        migration_status_output = $migrationOutput
        sqlite_schema = $schema
        interpretation = [ordered]@{
            schedule_migration_missing = (-not [bool]$schema.schedule_migration_recorded)
            schedule_columns_missing = (@($schema.events_missing_columns).Count -gt 0)
            sqlite_integrity_failed = ($schema.integrity_check -ne 'ok')
        }
    }

    $json = $report | ConvertTo-Json -Depth 8
    Set-Content -LiteralPath $OutputPath -Value $json -Encoding UTF8
    Write-Output $json
    Write-Host "Read-only report saved to: $OutputPath"
    Write-Host 'No migrations were run and no database records or schema were changed.'
    Start-Process -FilePath 'notepad.exe' -ArgumentList @($OutputPath)
} finally {
    Remove-Item -LiteralPath $tempPhp -Force -ErrorAction SilentlyContinue
    Pop-Location
}
