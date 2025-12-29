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

# Define what to exclude
$excludePatterns = @(
    "node_modules", ".git", ".github", ".claude", ".vscode", ".idea",
    "docs", "tests", "build", "lighthouse-reports", "vendor",
    "*.log", "*.tmp", "*.bak", ".gitignore", ".gitattributes",
    "package.json", "package-lock.json", "composer.json", "composer.lock",
    "wp-cli.phar", ".DS_Store", "Thumbs.db", ".env", ".env.local",
    "build-testing.ps1", "nul", "NUL"
)

Write-Host "Copying theme files..." -ForegroundColor Yellow
$fileCount = 0

Get-ChildItem -Path . -Recurse -Force -File | ForEach-Object {
    $file = $_
    $relativePath = $file.FullName.Substring((Get-Location).Path.Length + 1)

    # Check if should exclude
    $exclude = $false
    foreach ($pattern in $excludePatterns) {
        if ($relativePath -like "*$pattern*") {
            $exclude = $true
            break
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

# Create zip
Write-Host "Creating zip archive..." -ForegroundColor Yellow
# Compress theme directory (includes the folder in the ZIP as required by WordPress)
Compress-Archive -Path $themeDir -DestinationPath $outputZip -CompressionLevel Optimal -Force

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
