#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Pack maca Njuvs for WordPress upload.

.DESCRIPTION
    Creates maca-njuvs-{version}.zip ready for Plugins -> Upload Plugin.
    By default bumps the patch version in maca-njuvs.php and readme.txt.

.PARAMETER NoBump
    Build the ZIP without changing the version number.

.PARAMETER Version
    Set an explicit version (major.minor.patch) before building.

.EXAMPLE
    .\pack-plugin.ps1

.EXAMPLE
    .\pack-plugin.ps1 -NoBump
#>
param(
    [switch]$NoBump,
    [string]$Version
)

$ErrorActionPreference = 'Stop'
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$target = Join-Path $scriptDir 'create-zip.ps1'

if (-not (Test-Path $target)) {
    throw "Missing script: create-zip.ps1"
}

if ($PSBoundParameters.ContainsKey('Version')) {
    & $target -NoBump:$NoBump -Version $Version
} else {
    & $target -NoBump:$NoBump
}

exit $LASTEXITCODE
