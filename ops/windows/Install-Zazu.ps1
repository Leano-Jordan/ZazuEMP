[CmdletBinding()]
param(
    [string]$ProjectPath
)

$ErrorActionPreference = 'Stop'

if ([string]::IsNullOrWhiteSpace($ProjectPath)) {
    $ProjectPath = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
}

$ProjectPath = [System.IO.Path]::GetFullPath($ProjectPath).TrimEnd('\')

function Stop-ZazuInstall([string]$Message) {
    Write-Error $Message
    exit 1
}

function Set-ZazuRestrictedAcl([string]$Path) {
    $currentUserSid = [System.Security.Principal.WindowsIdentity]::GetCurrent().User
    if (-not $currentUserSid) {
        Stop-ZazuInstall 'Could not identify the Windows account that owns this Zazu installation.'
    }

    $allowedSids = @(
        $currentUserSid,
        [System.Security.Principal.SecurityIdentifier]::new('S-1-5-18'),
        [System.Security.Principal.SecurityIdentifier]::new('S-1-5-32-544')
    )

    $root = Get-Item -LiteralPath $Path -Force
    if (($root.Attributes -band [System.IO.FileAttributes]::ReparsePoint) -ne 0) {
        Stop-ZazuInstall 'A protected Zazu data path cannot be a symbolic link or reparse point.'
    }

    if ($root.PSIsContainer) {
        $items = @($root) + @(Get-ChildItem -LiteralPath $Path -Force -Recurse -ErrorAction Stop)
    } else {
        $items = @($root)
    }

    foreach ($item in $items) {
        if (($item.Attributes -band [System.IO.FileAttributes]::ReparsePoint) -ne 0) {
            Stop-ZazuInstall "A protected Zazu data path contains an unsupported link or reparse point: $($item.FullName)"
        }

        $acl = Get-Acl -LiteralPath $item.FullName
        $acl.SetAccessRuleProtection($true, $false)
        foreach ($existingRule in @($acl.Access)) {
            [void]$acl.RemoveAccessRuleSpecific($existingRule)
        }
        $acl.SetOwner($currentUserSid)

        $inheritance = [System.Security.AccessControl.InheritanceFlags]::None
        if ($item.PSIsContainer) {
            $inheritance = [System.Security.AccessControl.InheritanceFlags]::ContainerInherit -bor [System.Security.AccessControl.InheritanceFlags]::ObjectInherit
        }

        foreach ($sid in $allowedSids) {
            $rule = [System.Security.AccessControl.FileSystemAccessRule]::new(
                $sid,
                [System.Security.AccessControl.FileSystemRights]::FullControl,
                $inheritance,
                [System.Security.AccessControl.PropagationFlags]::None,
                [System.Security.AccessControl.AccessControlType]::Allow
            )
            [void]$acl.AddAccessRule($rule)
        }

        Set-Acl -LiteralPath $item.FullName -AclObject $acl
    }
}

function Invoke-ZazuPhp([string[]]$Arguments) {
    Push-Location $ProjectPath
    try {
        & $script:PhpPath (Join-Path $ProjectPath 'artisan') @Arguments
        if ($LASTEXITCODE -ne 0) {
            Stop-ZazuInstall "Zazu setup command failed (exit code $LASTEXITCODE): php artisan $($Arguments -join ' ')"
        }
    } finally {
        Pop-Location
    }
}

$requiredFiles = @(
    'artisan',
    'public\index.php',
    'public\build\manifest.json',
    '.env.example',
    'vendor\autoload.php'
)

foreach ($relativePath in $requiredFiles) {
    $absolutePath = Join-Path $ProjectPath $relativePath
    if (-not (Test-Path $absolutePath -PathType Leaf)) {
        Stop-ZazuInstall "The Zazu installation package is incomplete. Missing: $relativePath. Extract the complete package and run Install-Zazu.cmd from its root folder."
    }
}

$phpCandidates = @()
$phpCommand = Get-Command php.exe -ErrorAction SilentlyContinue | Select-Object -First 1
if ($phpCommand) {
    $phpCandidates += $phpCommand.Source
}
if (Test-Path 'C:\PHP84\php.exe' -PathType Leaf) {
    $phpCandidates += 'C:\PHP84\php.exe'
}

$script:PhpPath = $null
foreach ($candidate in ($phpCandidates | Select-Object -Unique)) {
    $versionText = (& $candidate -r 'echo PHP_VERSION;' 2>$null | Out-String).Trim()
    try {
        $version = [version]$versionText
    } catch {
        continue
    }

    if ($version -ge [version]'8.4') {
        $script:PhpPath = $candidate
        break
    }
}

if (-not $script:PhpPath) {
    Stop-ZazuInstall 'PHP 8.4 or newer is required. Install a supported PHP 8.4+ runtime, add php.exe to PATH, or place it at C:\PHP84\php.exe. No system settings were changed.'
}

$phpModules = @(& $script:PhpPath -m 2>&1 | ForEach-Object { "$_".Trim().ToLowerInvariant() })
$requiredExtensions = @(
    'ctype',
    'curl',
    'dom',
    'fileinfo',
    'filter',
    'hash',
    'mbstring',
    'openssl',
    'pdo',
    'pdo_sqlite',
    'session',
    'tokenizer',
    'xml',
    'zip'
)
$missingExtensions = @($requiredExtensions | Where-Object { $phpModules -notcontains $_ })
if ($missingExtensions.Count -gt 0) {
    Stop-ZazuInstall "The selected PHP runtime is missing required extensions: $($missingExtensions -join ', '). Enable these extensions in the active PHP configuration, then run the installer again."
}

$environmentPath = Join-Path $ProjectPath '.env'
$environmentCreated = $false
if (-not (Test-Path $environmentPath -PathType Leaf)) {
    Copy-Item (Join-Path $ProjectPath '.env.example') $environmentPath
    $environmentCreated = $true
}

if ($environmentCreated) {
    $environmentText = Get-Content $environmentPath -Raw
    $initialValues = [ordered]@{
        APP_NAME = '"Zazu EMP"'
        APP_ENV = 'production'
        APP_DEBUG = 'false'
        APP_URL = 'http://127.0.0.1:8000'
        LOG_LEVEL = 'warning'
        DB_CONNECTION = 'sqlite'
        DB_DATABASE = 'database/database.sqlite'
        SESSION_SECURE_COOKIE = 'false'
        FILESYSTEM_DISK = 'local'
        QUEUE_CONNECTION = 'database'
        CACHE_STORE = 'database'
    }

    foreach ($entry in $initialValues.GetEnumerator()) {
        $pattern = '(?m)^#?\s*' + [regex]::Escape($entry.Key) + '=.*$'
        $replacement = $entry.Key + '=' + $entry.Value
        if ([regex]::IsMatch($environmentText, $pattern)) {
            $environmentText = [regex]::Replace(
                $environmentText,
                $pattern,
                [System.Text.RegularExpressions.MatchEvaluator]{ param($match) $replacement }
            )
        } else {
            $environmentText = $environmentText.TrimEnd() + [Environment]::NewLine + $replacement + [Environment]::NewLine
        }
    }

    Set-Content -Path $environmentPath -Value $environmentText -Encoding UTF8
}

$requiredDirectories = @(
    'database',
    'storage\app\private',
    'storage\framework\cache\data',
    'storage\framework\sessions',
    'storage\framework\views',
    'storage\logs',
    'bootstrap\cache'
)
foreach ($relativePath in $requiredDirectories) {
    New-Item -ItemType Directory -Path (Join-Path $ProjectPath $relativePath) -Force | Out-Null
}

# Protect business data, secrets, sessions, logs, cache and private attachments
# from broad inherited access on shared Windows folders.
$restrictedPaths = @(
    (Join-Path $ProjectPath 'storage\app\private'),
    (Join-Path $ProjectPath 'storage\framework'),
    (Join-Path $ProjectPath 'storage\logs'),
    (Join-Path $ProjectPath 'database'),
    (Join-Path $ProjectPath 'bootstrap\cache'),
    $environmentPath
)
foreach ($restrictedPath in $restrictedPaths) {
    Set-ZazuRestrictedAcl $restrictedPath
}

$databasePath = Join-Path $ProjectPath 'database\database.sqlite'
if (-not (Test-Path $databasePath -PathType Leaf)) {
    New-Item -ItemType File -Path $databasePath -Force | Out-Null
}

$keyLine = Get-Content $environmentPath | Where-Object { $_ -match '^APP_KEY=' } | Select-Object -First 1
if ([string]::IsNullOrWhiteSpace($keyLine) -or $keyLine -eq 'APP_KEY=') {
    Invoke-ZazuPhp @('key:generate', '--force')
}

Write-Host 'Preparing the local Zazu database...'
Invoke-ZazuPhp @('migrate', '--force')

Write-Host ''
Write-Host 'Zazu EMP installation preparation completed.'
Write-Host 'Application data will be stored in this installation folder.'
Write-Host 'Next step: double-click Start-Zazu-Dev.cmd and open http://127.0.0.1:8000.'
Write-Host 'For phone/tablet access, use the documented private-LAN HTTPS setup; do not expose the HTTP development port to the internet.'
