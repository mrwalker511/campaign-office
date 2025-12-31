# Campaign Office Theme - Quick Testing Build
# Run from theme root: powershell -ExecutionPolicy Bypass .\build-testing.ps1

$ErrorActionPreference = "Stop"

Write-Host "`nCampaign Office - Testing Build" -ForegroundColor Cyan
Write-Host "================================`n" -ForegroundColor Cyan

# Get version
$version = (Select-String -Path "style.css" -Pattern "Version:\s*(.+)" | Select-Object -First 1).Matches.Groups[1].Value.Trim()
Write-Host "Version: $version" -ForegroundColor Green

# Setup paths
$tempDir = Join-Path $env:TEMP "campaign-office-build-$(Get-Date -Format 'yyyyMMddHHmmss')"
$outputZip = "campaign-office-testing.zip"
$themeName = "campaign-office"
$themeDir = Join-Path $tempDir $themeName

Write-Host "Creating temporary build directory..." -ForegroundColor Yellow
New-Item -Path $themeDir -ItemType Directory -Force | Out-Null

# Directories to exclude (matched as path segments)
$excludeDirs = @(
    "node_modules", ".git", ".github", ".claude", ".vscode", ".idea",
    "docs", "tests", "build", "lighthouse-reports", "vendor"
)

# Files/patterns to exclude
$excludeFiles = @(
    "*.log", "*.tmp", "*.bak", "*.zip",
    ".gitignore", ".gitattributes", ".DS_Store", "Thumbs.db",
    "package.json", "package-lock.json", "composer.json", "composer.lock",
    "wp-cli.phar", ".env", ".env.local", "nul", "NUL",
    "build-testing.ps1", "build-production.ps1"
)

Write-Host "Copying theme files..." -ForegroundColor Yellow
$fileCount = 0

Get-ChildItem -Path . -Recurse -Force -File | ForEach-Object {
    $file = $_
    $relativePath = $file.FullName.Substring((Get-Location).Path.Length + 1)
    $pathParts = $relativePath -split '\\'

    # Check if in excluded directory
    $exclude = $false
    foreach ($dir in $excludeDirs) {
        if ($pathParts -contains $dir) {
            $exclude = $true
            break
        }
    }

    # Check if file matches exclude pattern
    if (-not $exclude) {
        foreach ($pattern in $excludeFiles) {
            if ($file.Name -like $pattern) {
                $exclude = $true
                break
            }
        }
    }

    if (-not $exclude) {
        $destPath = Join-Path $themeDir $relativePath
        $destFolder = Split-Path $destPath -Parent

        if (-not (Test-Path $destFolder)) {
            New-Item -Path $destFolder -ItemType Directory -Force | Out-Null
        }

        Copy-Item -Path $file.FullName -Destination $destPath -Force
        $fileCount++
    }
}

Write-Host "Copied $fileCount files" -ForegroundColor Green

# Calculate size
$size = (Get-ChildItem $themeDir -Recurse | Measure-Object -Property Length -Sum).Sum
$sizeMB = [math]::Round($size / 1MB, 2)
Write-Host "Uncompressed: $sizeMB MB" -ForegroundColor Green

# Remove old zip if exists
if (Test-Path $outputZip) {
    Remove-Item $outputZip -Force
}

# Create zip with forward slashes (required for WordPress/PHP compatibility)
Write-Host "Creating zip archive..." -ForegroundColor Yellow

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

# Create ZIP with forward slashes for cross-platform compatibility
$zipFullPath = Join-Path (Get-Location) $outputZip
$zip = [System.IO.Compression.ZipFile]::Open($zipFullPath, 'Create')

# Get resolved paths for accurate relative path calculation
$resolvedThemeDir = (Get-Item $themeDir).FullName
$parentOfThemeDir = (Get-Item $themeDir).Parent.FullName

Get-ChildItem -Path $themeDir -Recurse -File | ForEach-Object {
    # Get path relative to theme dir and prepend theme name with forward slashes
    $fileRelativePath = $_.FullName.Substring($resolvedThemeDir.Length + 1) -replace '\\', '/'
    $entryName = "$themeName/$fileRelativePath"
    [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $_.FullName, $entryName, [System.IO.Compression.CompressionLevel]::Optimal) | Out-Null
}

$zip.Dispose()

# Cleanup temp directory
Remove-Item $tempDir -Recurse -Force

$zipSize = [math]::Round((Get-Item $outputZip).Length / 1MB, 2)
$zipPath = Resolve-Path $outputZip

Write-Host "`n================================" -ForegroundColor Green
Write-Host "BUILD COMPLETE!" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Green
Write-Host "Location: $zipPath" -ForegroundColor Cyan
Write-Host "Size: $zipSize MB" -ForegroundColor Cyan
Write-Host "Files: $fileCount" -ForegroundColor Cyan
Write-Host "`nUpload this file to your host!" -ForegroundColor Yellow
Write-Host "Extract to: wp-content/themes/`n" -ForegroundColor Yellow
