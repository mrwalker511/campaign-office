# Campaign Office Theme - Testing Build Script (PowerShell)
# Creates a lightweight package for testing on live hosts

# Detect script directory and set working directory
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
if (-not $ScriptDir) {
    $ScriptDir = Get-Location
}

# Determine theme root (go up one level if we're in build directory)
if ($ScriptDir -like "*\build") {
    $ThemeRoot = Split-Path -Parent $ScriptDir
    Set-Location $ScriptDir
} else {
    $ThemeRoot = $ScriptDir
    if (-not (Test-Path "build")) {
        New-Item -Path "build" -ItemType Directory -Force | Out-Null
    }
    Set-Location "build"
}

# Configuration
$ThemeName = "campaign-office"
$BuildDir = "testing"
$DistDir = Join-Path $BuildDir $ThemeName
$DistFile = "$ThemeName-testing.zip"

# Get version from style.css
$StylePath = Join-Path $ThemeRoot "style.css"
if (Test-Path $StylePath) {
    $StyleContent = Get-Content -Path $StylePath -Raw
    if ($StyleContent -match "Version:\s*(.+)") {
        $Version = $matches[1].Trim()
    }
    else {
        $Version = "unknown"
    }
} else {
    Write-Host "Warning: style.css not found at $StylePath" -ForegroundColor Red
    $Version = "unknown"
}

Write-Host "========================================" -ForegroundColor Blue
Write-Host "Campaign Office Testing Build" -ForegroundColor Blue
Write-Host "Version: $Version" -ForegroundColor Blue
Write-Host "========================================`n" -ForegroundColor Blue

# Clean previous builds
Write-Host "-> Cleaning previous testing builds..." -ForegroundColor Yellow
if (Test-Path $BuildDir) {
    Remove-Item -Path $BuildDir -Recurse -Force
}
New-Item -Path $DistDir -ItemType Directory -Force | Out-Null

# Define exclusion patterns (matching .distignore)
Write-Host "-> Preparing exclusion patterns..." -ForegroundColor Yellow
$ExcludePatterns = @(
    "node_modules"
    "wp-cli.phar"
    "package.json"
    "package-lock.json"
    "composer.json"
    "composer.lock"
    "vendor"
    ".git"
    ".github"
    ".gitignore"
    ".gitattributes"
    ".claude"
    ".vscode"
    ".idea"
    "docs"
    "tests"
    "build"
    "testing"
    "lighthouse-reports"
    "build-testing.ps1"
    "Screenshot.png"
    "screenshot.png"
    "nul"
    "NUL"
    "*.log"
    "*.tmp"
    "*.bak"
    ".DS_Store"
    "Thumbs.db"
    ".env"
    ".env.local"
)

# Copy theme files
Write-Host "-> Copying theme files..." -ForegroundColor Yellow
$SourcePath = Resolve-Path $ThemeRoot
$ItemCount = 0

Get-ChildItem -Path $SourcePath -Recurse -Force | ForEach-Object {
    $item = $_
    $relativePath = $item.FullName.Substring($SourcePath.Path.Length + 1)

    # Check if should be excluded
    $shouldExclude = $false
    foreach ($pattern in $ExcludePatterns) {
        if ($relativePath -like "*$pattern*") {
            $shouldExclude = $true
            break
        }
    }

    if (-not $shouldExclude -and -not $item.PSIsContainer) {
        $destPath = Join-Path $DistDir $relativePath
        $destFolder = Split-Path -Path $destPath -Parent

        if (-not (Test-Path $destFolder)) {
            New-Item -Path $destFolder -ItemType Directory -Force | Out-Null
        }

        Copy-Item -Path $item.FullName -Destination $destPath -Force
        $ItemCount++
    }
}

Write-Host "Copied $ItemCount files" -ForegroundColor Green

# Check for sensitive files
Write-Host "-> Checking for sensitive files..." -ForegroundColor Yellow
$sensitiveFiles = @(".env", ".env.local", "secrets.json", "credentials.json")
$foundSensitive = $false

foreach ($file in $sensitiveFiles) {
    $sensitivePath = Join-Path $DistDir $file
    if (Test-Path $sensitivePath) {
        Write-Host "Warning: Removing sensitive file: $file" -ForegroundColor Yellow
        Remove-Item -Path $sensitivePath -Force
        $foundSensitive = $true
    }
}

if (-not $foundSensitive) {
    Write-Host "No sensitive files found" -ForegroundColor Green
}

# Calculate size
Write-Host "-> Calculating package size..." -ForegroundColor Yellow
$sizeCalc = Get-ChildItem -Path $DistDir -Recurse -Force | Measure-Object -Property Length -Sum
$sizeMB = [math]::Round($sizeCalc.Sum / 1MB, 2)
Write-Host "Distribution size: $sizeMB MB" -ForegroundColor Green

# Create zip file
Write-Host "-> Creating zip archive..." -ForegroundColor Yellow
$zipPath = Join-Path $BuildDir $DistFile
if (Test-Path $zipPath) {
    Remove-Item -Path $zipPath -Force
}

Compress-Archive -Path $DistDir -DestinationPath $zipPath -CompressionLevel Optimal -Force

$finalSizeItem = Get-Item $zipPath
$finalSizeMB = [math]::Round($finalSizeItem.Length / 1MB, 2)

$outputPath = Join-Path (Get-Location) $DistFile

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "Testing Build Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "File: $outputPath" -ForegroundColor Green
Write-Host "Size: $finalSizeMB MB (uncompressed: $sizeMB MB)" -ForegroundColor Green
Write-Host "Version: $Version`n" -ForegroundColor Green

Write-Host "Upload Instructions:" -ForegroundColor Cyan
Write-Host "  1. Upload $DistFile to your host" -ForegroundColor White
Write-Host "  2. Extract to wp-content/themes/" -ForegroundColor White
Write-Host "  3. Activate theme in WordPress admin" -ForegroundColor White
Write-Host "  4. Test appearance and functionality`n" -ForegroundColor White

Write-Host "Note: This is a testing build" -ForegroundColor Yellow
Write-Host "Includes: All theme files + premium features" -ForegroundColor Yellow
Write-Host "Excludes: Dev dependencies, docs, git files`n" -ForegroundColor Yellow
