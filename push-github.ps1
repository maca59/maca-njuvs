# Push maca Njuvs to GitHub (maca59/maca-njuvs)
#
# First-time setup (if gh is not logged in):
#   gh auth login
#
# Or create an empty public repo manually: https://github.com/new
#   Name: maca-njuvs — do NOT add README, .gitignore, or license
# Then run: .\push-github.ps1

$ErrorActionPreference = 'Stop'
$PluginRoot = $PSScriptRoot
Set-Location $PluginRoot

if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    throw 'git not found in PATH'
}

$remoteUrl = 'https://github.com/maca59/maca-njuvs.git'

if (-not (git rev-parse --verify HEAD 2>$null)) {
    git add -A
    git commit -m "Initial public source for maca Njuvs (WordPress plugin)."
}

$branch = git branch --show-current
if ($branch -ne 'main') {
    git branch -M main
}

$remotes = git remote
if ($remotes -notcontains 'origin') {
    git remote add origin $remoteUrl
    Write-Host "Added remote: $remoteUrl"
} else {
    git remote set-url origin $remoteUrl
}

Write-Host ''
Write-Host 'Pushing to GitHub (sign in if prompted)...' -ForegroundColor Cyan
git push -u origin main

Write-Host ''
Write-Host 'Done: https://github.com/maca59/maca-njuvs' -ForegroundColor Green
