# Build a WordPress-ready release ZIP (maca-njuvs/maca-njuvs.php inside)
param(
    [switch]$NoBump,
    [string]$Version
)

Add-Type -AssemblyName System.IO.Compression.FileSystem

$ErrorActionPreference = 'Stop'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$sourceDir = $scriptDir

function Get-IncrementedPatchVersion {
    param([string]$InputVersion)

    if ($InputVersion -match '^(\d+)\.(\d+)\.(\d+)$') {
        $major = [int]$matches[1]
        $minor = [int]$matches[2]
        $patch = [int]$matches[3] + 1
        return "$major.$minor.$patch"
    }

    throw "Invalid version format in plugin header: $InputVersion (expected major.minor.patch)"
}

function Set-PluginVersion {
    param(
        [string]$PluginFile,
        [string]$TargetVersion
    )

    $content = Get-Content $PluginFile -Raw
    $updated = $content -replace '(?m)^(\s*\*\s*Version:\s*)[\d.]+(\s*)$', "`${1}$TargetVersion`${2}"
    $updated = $updated -replace "define\('MACA_NJUVS_VERSION',\s*'[\d.]+'\);", "define('MACA_NJUVS_VERSION', '$TargetVersion');"

    if ($updated -eq $content) {
        throw "Could not update version in $PluginFile"
    }

    Set-Content -Path $PluginFile -Value $updated -NoNewline
}

function Set-ReadmeStableTag {
    param(
        [string]$ReadmeFile,
        [string]$TargetVersion
    )

    if (-not (Test-Path $ReadmeFile)) {
        return
    }

    $content = Get-Content $ReadmeFile -Raw
    $updated = $content -replace '(?m)^Stable tag:\s*[\d.]+(\s*)$', "Stable tag: $TargetVersion`${1}"

    if ($updated -ne $content) {
        Set-Content -Path $ReadmeFile -Value $updated -NoNewline
    }
}

function Set-PoProjectVersion {
    param(
        [string]$LanguagesDir,
        [string]$TargetVersion
    )

    if (-not (Test-Path $LanguagesDir)) {
        return
    }

    Get-ChildItem -Path $LanguagesDir -Filter "*.po" | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        $updated = $content -replace 'Project-Id-Version: maca Njuvs [\d.]+', "Project-Id-Version: maca Njuvs $TargetVersion"

        if ($updated -ne $content) {
            Set-Content -Path $_.FullName -Value $updated -NoNewline
        }
    }
}

$pluginFile = Join-Path $scriptDir "maca-njuvs.php"
$readmeFile = Join-Path $scriptDir "readme.txt"
$languagesDir = Join-Path $scriptDir "languages"

if (-not (Test-Path $pluginFile)) {
    throw "Missing plugin file: $pluginFile"
}

$content = Get-Content $pluginFile -Raw
if ($content -notmatch 'Version:\s*([\d.]+)') {
    throw "Could not read Version from plugin header in maca-njuvs.php"
}

$currentVersion = $matches[1]

if ($PSBoundParameters.ContainsKey('Version') -and $Version -ne '') {
    if ($Version -notmatch '^\d+\.\d+\.\d+$') {
        throw "Invalid -Version value: $Version (expected major.minor.patch)"
    }

    $version = $Version
    Set-PluginVersion -PluginFile $pluginFile -TargetVersion $version
    Set-ReadmeStableTag -ReadmeFile $readmeFile -TargetVersion $version
    Set-PoProjectVersion -LanguagesDir $languagesDir -TargetVersion $version
    Write-Host "Version set: $currentVersion -> $version" -ForegroundColor Cyan
} elseif (-not $NoBump) {
    $version = Get-IncrementedPatchVersion -InputVersion $currentVersion
    Set-PluginVersion -PluginFile $pluginFile -TargetVersion $version
    Set-ReadmeStableTag -ReadmeFile $readmeFile -TargetVersion $version
    Set-PoProjectVersion -LanguagesDir $languagesDir -TargetVersion $version
    Write-Host "Version bumped: $currentVersion -> $version" -ForegroundColor Cyan
} else {
    $version = $currentVersion
    Write-Host "Building release without version bump: $version" -ForegroundColor Cyan
}

$distignoreFile = Join-Path $scriptDir ".distignore"
$excludePatterns = @()
if (Test-Path $distignoreFile) {
    $excludePatterns = Get-Content $distignoreFile | Where-Object {
        $_ -and $_ -notmatch '^\s*#'
    } | ForEach-Object { $_.Trim() }
}

function Test-ExcludedPath {
    param([string]$RelativePath)

    $normalized = $RelativePath.Replace('\', '/')

    foreach ($pattern in $excludePatterns) {
        $raw = $pattern.Replace('\', '/').Trim()
        $isFolderPattern = $raw.EndsWith('/')
        $p = $raw.TrimEnd('/')

        if ($p -match '[\*\?]') {
            if ($normalized -like $p) {
                return $true
            }
            continue
        }

        if ($normalized -eq $p) {
            return $true
        }

        if (-not $isFolderPattern -and $p -notmatch '/') {
            continue
        }

        if ($normalized -like "$p/*") {
            return $true
        }

        if ($normalized -like "*/$p") {
            return $true
        }

        if ($normalized -like "*/$p/*") {
            return $true
        }
    }

    return $false
}

$msgfmtPath = $null
if (Get-Command msgfmt -ErrorAction SilentlyContinue) {
    $msgfmtPath = "msgfmt"
} elseif (Test-Path "$env:LOCALAPPDATA\Programs\gettext-iconv\bin\msgfmt.exe") {
    $msgfmtPath = "$env:LOCALAPPDATA\Programs\gettext-iconv\bin\msgfmt.exe"
}

if (Test-Path $languagesDir) {
    Get-ChildItem -Path $languagesDir -Filter "*.po" | ForEach-Object {
        $moPath = [System.IO.Path]::ChangeExtension($_.FullName, ".mo")
        if ($msgfmtPath) {
            & $msgfmtPath -o $moPath $_.FullName
            Write-Host "Compiled translation: $($_.Name)" -ForegroundColor DarkGray
        } else {
            Write-Host "Warning: msgfmt not found; skipped $($_.Name)" -ForegroundColor Yellow
        }
    }
}

$zipFolderName = "maca-njuvs"
$bootstrapEntry = "maca-njuvs.php"
$zipFile = Join-Path $scriptDir "maca-njuvs-$version.zip"

if (Test-Path $zipFile) {
    Remove-Item $zipFile
}

$zip = [System.IO.Compression.ZipFile]::Open($zipFile, 'Create')
$fileCount = 0
$requiredEntries = @(
    "$zipFolderName/$bootstrapEntry",
    "$zipFolderName/readme.txt",
    "$zipFolderName/uninstall.php",
    "$zipFolderName/index.php"
)

Get-ChildItem -Path $sourceDir -Recurse -File | ForEach-Object {
    $relativePath = $_.FullName.Substring($sourceDir.Length + 1).Replace('\', '/')

    if (Test-ExcludedPath -RelativePath $relativePath) {
        return
    }

    $entryPath = "$zipFolderName/$relativePath"
    [void][System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $_.FullName, $entryPath)
    $fileCount++
}

$zip.Dispose()

$validationZip = [System.IO.Compression.ZipFile]::OpenRead($zipFile)
foreach ($required in $requiredEntries) {
    $entry = $validationZip.Entries | Where-Object { $_.FullName -eq $required } | Select-Object -First 1
    if (-not $entry) {
        $validationZip.Dispose()
        Write-Host "ZIP validation failed: missing $required" -ForegroundColor Red
        exit 1
    }
}

$mainZipEntry = $validationZip.Entries | Where-Object { $_.FullName -eq "$zipFolderName/$bootstrapEntry" } | Select-Object -First 1
$entryStream = $mainZipEntry.Open()
$entryReader = New-Object System.IO.StreamReader($entryStream)
$mainContent = $entryReader.ReadToEnd()
$entryReader.Close()
$entryStream.Close()
$validationZip.Dispose()

if ($mainContent -notmatch 'Plugin Name:\s*.+') {
    Write-Host "ZIP validation failed: $bootstrapEntry has no Plugin Name header" -ForegroundColor Red
    exit 1
}

if ($mainContent -notmatch 'Version:\s*' + [regex]::Escape($version)) {
    Write-Host "ZIP validation failed: header Version does not match $version" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Release ZIP created: $zipFile" -ForegroundColor Green
Write-Host "Files included: $fileCount" -ForegroundColor Green
Write-Host "Plugin header: OK ($zipFolderName/$bootstrapEntry)" -ForegroundColor Green
Write-Host ""
Write-Host "Upload to WordPress:" -ForegroundColor Cyan
Write-Host "  Plugins -> Add New -> Upload Plugin -> choose maca-njuvs-$version.zip" -ForegroundColor White
Write-Host "  Or unzip to wp-content/plugins/maca-njuvs/" -ForegroundColor White
