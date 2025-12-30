#Requires -Version 5.1
<#
.SYNOPSIS
    Build production-ready ZIP file for Campaign Office theme

.DESCRIPTION
    Creates a clean production ZIP excluding development files, tests, and git history.
    
    Includes only files necessary for theme distribution.

.PARAMETER OutputDir
    Directory where the ZIP file will be created (default: parent directory)

.PARAMETER Version
    Version number to append to filename (default: reads from style.css)

.EXAMPLE
    .\build-production.ps1

.EXAMPLE
    .\build-production.ps1 -OutputDir "C:\releases" -Version "2.0.0"
#>

param(
    [string]$OutputDir = "..",
    [string]$Version = ""
)

# Colors for output
$ErrorColor = "Red"
$SuccessColor = "Green"
$InfoColor = "Cyan"
$WarningColor = "Yellow"

Write-Host "`n========================================" -ForegroundColor $InfoColor
Write-Host "Campaign Office Theme - Production Build" -ForegroundColor $InfoColor
Write-Host "========================================`n" -ForegroundColor $InfoColor

# Get theme directory (script location)
$ThemeDir = $PSScriptRoot

Write-Host "[1/5] Validating theme directory..." -ForegroundColor $InfoColor
if (-not (Test-Path "$ThemeDir\style.css")) {
    Write-Host "Error: style.css not found. Are you in the theme directory?" -ForegroundColor $ErrorColor
    exit 1
}

# Get theme slug and version from style.css
Write-Host "[2/5] Reading theme information from style.css..." -ForegroundColor $InfoColor
$StyleContent = Get-Content "$ThemeDir\style.css" -Raw

# Extract theme slug from Text Domain
if ($StyleContent -match "Text Domain:\s*([a-z0-9_-]+)") {
    $ThemeSlug = $matches[1]
    Write-Host "    Theme slug: $ThemeSlug" -ForegroundColor $SuccessColor
} else {
    # Fallback to directory name if Text Domain not found
    $ThemeSlug = Split-Path $ThemeDir -Leaf
    Write-Host "    Warning: Text Domain not found in style.css, using directory name: $ThemeSlug" -ForegroundColor $WarningColor
}

# For backward compatibility
$ThemeName = $ThemeSlug

# Get version from style.css if not provided
if ([string]::IsNullOrEmpty($Version)) {
    if ($StyleContent -match "Version:\s*([0-9.]+)") {
        $Version = $matches[1]
        Write-Host "    Version: $Version" -ForegroundColor $SuccessColor
    } else {
        $Version = "dev"
        Write-Host "    Warning: Version not found, using 'dev'" -ForegroundColor $WarningColor
    }
} else {
    Write-Host "    Version: $Version (provided)" -ForegroundColor $SuccessColor
}

# Create temp directory
$TempDir = Join-Path $env:TEMP "campaign-office-build-$(Get-Random)"
$BuildDir = Join-Path $TempDir $ThemeName
Write-Host "[3/5] Creating temporary build directory..." -ForegroundColor $InfoColor
New-Item -ItemType Directory -Path $BuildDir -Force | Out-Null

Write-Host "[4/5] Copying production files..." -ForegroundColor $InfoColor

# Define excluded directories
$ExcludeDirs = @(
    ".git",
    "node_modules",
    "vendor",
    "build",
    "scripts",
    "tests",
    "playwright-report",
    "test-results",
    "docs",
    ".github",
    ".vscode",
    ".idea",
    ".claude",
    ".sass-cache"
)

# Define excluded files
$ExcludeFiles = @(
    ".gitignore",
    ".gitattributes",
    "package.json",
    "package-lock.json",
    ".npmrc",
    "composer.json",
    "composer.lock",
    "build-production.ps1",
    "build-production.sh",
    "build-testing.ps1",
    "phpunit.xml",
    ".phpunit.result.cache",
    "playwright.config.js",
    ".distignore",
    ".editorconfig",
    "*.code-workspace",
    ".env*",
    "*.log",
    "debug.log",
    "error_log",
    ".DS_Store",
    "Thumbs.db",
    "desktop.ini",
    "*.tmp",
    "*.temp",
    "*.cache",
    ".eslintrc*",
    ".stylelintrc*",
    ".prettierrc*",
    "*.zip"
)

# Use robocopy for fast, selective copying
$ExcludeDirArgs = $ExcludeDirs | ForEach-Object { "/XD `"$_`"" }
$ExcludeFileArgs = $ExcludeFiles | ForEach-Object { "/XF `"$_`"" }
$RobocopyArgs = @(
    "`"$ThemeDir`"",
    "`"$BuildDir`"",
    "/E",  # Copy subdirectories including empty ones
    "/NFL", # No file list
    "/NDL", # No directory list
    "/NJH", # No job header
    "/NJS", # No job summary
    "/NP"   # No progress
) + $ExcludeDirArgs + $ExcludeFileArgs

$RobocopyCmd = "robocopy $($RobocopyArgs -join ' ')"
Invoke-Expression $RobocopyCmd | Out-Null

# Robocopy exit codes: 0-7 are success (0=no files, 1=files copied, etc.)
if ($LASTEXITCODE -gt 7) {
    Write-Host "Error: File copy failed (robocopy exit code: $LASTEXITCODE)" -ForegroundColor $ErrorColor
    exit 1
}

# Remove assets/react and assets/js (source files)
$SourceDirs = @("assets\react", "assets\js")
foreach ($SourceDir in $SourceDirs) {
    $FullPath = Join-Path $BuildDir $SourceDir
    if (Test-Path $FullPath) {
        Remove-Item $FullPath -Recurse -Force -ErrorAction SilentlyContinue
    }
}

# Keep only critical CSS - remove all other CSS
if (Test-Path "$BuildDir\assets\css") {
    $CriticalCssPath = "$BuildDir\assets\css\critical"
    $TempCriticalPath = "$TempDir\critical-backup"

    if (Test-Path $CriticalCssPath) {
        Copy-Item -Path $CriticalCssPath -Destination $TempCriticalPath -Recurse -Force
    }

    Remove-Item "$BuildDir\assets\css" -Recurse -Force -ErrorAction SilentlyContinue

    if (Test-Path $TempCriticalPath) {
        New-Item -ItemType Directory -Path "$BuildDir\assets\css" -Force | Out-Null
        Copy-Item -Path $TempCriticalPath -Destination "$BuildDir\assets\css\critical" -Recurse -Force
    }
}

# Remove block source files (index.js and view.js)
Get-ChildItem -Path "$BuildDir\blocks" -Recurse -File -Include @("index.js", "view.js") -ErrorAction SilentlyContinue | Remove-Item -Force -ErrorAction SilentlyContinue

# Count files
$ItemCount = (Get-ChildItem -Path $BuildDir -Recurse -File -ErrorAction SilentlyContinue | Measure-Object).Count
Write-Host "    Copied $ItemCount files" -ForegroundColor $SuccessColor

# Create ZIP file
$OutputPath = Resolve-Path $OutputDir
$ZipFileName = "$ThemeName-$Version.zip"
$ZipPath = Join-Path $OutputPath $ZipFileName

Write-Host "[5/5] Creating ZIP archive..." -ForegroundColor $InfoColor

# Remove existing ZIP if it exists
if (Test-Path $ZipPath) {
    try {
        Remove-Item $ZipPath -Force -ErrorAction Stop
        Write-Host "    Removed existing ZIP file" -ForegroundColor $WarningColor
    } catch {
        # File is locked, try to find a new name
        $Counter = 1
        while (Test-Path $ZipPath) {
            $ZipFileName = "$ThemeName-$Version-build$Counter.zip"
            $ZipPath = Join-Path $OutputPath $ZipFileName
            $Counter++
            if ($Counter -gt 10) {
                Write-Host "    Error: Cannot create ZIP file, too many existing files" -ForegroundColor $ErrorColor
                exit 1
            }
        }
        Write-Host "    Warning: Original ZIP in use, creating: $ZipFileName" -ForegroundColor $WarningColor
    }
}

# Create ZIP using .NET for better performance
try {
    Add-Type -Assembly "System.IO.Compression.FileSystem"
    [System.IO.Compression.ZipFile]::CreateFromDirectory($BuildDir, $ZipPath, [System.IO.Compression.CompressionLevel]::Optimal, $true)

    $ZipSize = (Get-Item $ZipPath).Length
    $ZipSizeKB = [math]::Round($ZipSize / 1KB, 2)
    $ZipSizeMB = [math]::Round($ZipSize / 1MB, 2)

    Write-Host "`n========================================" -ForegroundColor $SuccessColor
    Write-Host "Build Complete!" -ForegroundColor $SuccessColor
    Write-Host "========================================" -ForegroundColor $SuccessColor
    Write-Host "`nProduction ZIP created:" -ForegroundColor $InfoColor
    Write-Host "  File: $ZipFileName" -ForegroundColor $SuccessColor
    Write-Host "  Location: $ZipPath" -ForegroundColor $SuccessColor
    if ($ZipSizeMB -gt 1) {
        Write-Host "  Size: $ZipSizeMB MB" -ForegroundColor $SuccessColor
    } else {
        Write-Host "  Size: $ZipSizeKB KB" -ForegroundColor $SuccessColor
    }
    Write-Host "  Files: $ItemCount" -ForegroundColor $SuccessColor
    Write-Host "`nReady for distribution!`n" -ForegroundColor $SuccessColor

} catch {
    Write-Host "`nError creating ZIP file: $_" -ForegroundColor $ErrorColor
    exit 1
} finally {
    # Cleanup temp directory
    if (Test-Path $TempDir) {
        Remove-Item $TempDir -Recurse -Force -ErrorAction SilentlyContinue
    }
}

# Open file location (optional)
$OpenLocation = Read-Host "`nOpen file location? (Y/N)"
if ($OpenLocation -eq "Y" -or $OpenLocation -eq "y") {
    Start-Process explorer.exe "/select,$ZipPath"
}
