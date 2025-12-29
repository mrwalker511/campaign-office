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
$ThemeName = Split-Path $ThemeDir -Leaf

Write-Host "[1/5] Validating theme directory..." -ForegroundColor $InfoColor
if (-not (Test-Path "$ThemeDir\style.css")) {
    Write-Host "Error: style.css not found. Are you in the theme directory?" -ForegroundColor $ErrorColor
    exit 1
}

# Get version from style.css if not provided
if ([string]::IsNullOrEmpty($Version)) {
    Write-Host "[2/5] Reading version from style.css..." -ForegroundColor $InfoColor
    $StyleContent = Get-Content "$ThemeDir\style.css" -Raw
    if ($StyleContent -match "Version:\s*([0-9.]+)") {
        $Version = $matches[1]
        Write-Host "    Found version: $Version" -ForegroundColor $SuccessColor
    } else {
        $Version = "dev"
        Write-Host "    Warning: Version not found, using 'dev'" -ForegroundColor $WarningColor
    }
} else {
    Write-Host "[2/5] Using provided version: $Version" -ForegroundColor $InfoColor
}

# Create temp directory
$TempDir = Join-Path $env:TEMP "campaign-office-build-$(Get-Random)"
$BuildDir = Join-Path $TempDir $ThemeName
Write-Host "[3/5] Creating temporary build directory..." -ForegroundColor $InfoColor
New-Item -ItemType Directory -Path $BuildDir -Force | Out-Null

# Files and directories to EXCLUDE (simple names only, no wildcards for robocopy)
$ExcludeDirs = @(
    ".git",
    "node_modules",
    "tests",
    "docs",
    ".vscode",
    ".idea",
    ".claude",
    "playwright-report",
    "test-results",
    ".sass-cache",
    "vendor\bin"
)

$ExcludeFiles = @(
    ".gitignore",
    ".gitattributes",
    "package-lock.json",
    ".npmrc",
    "build-production.ps1",
    "build-production.sh",
    "webpack.config.js",
    "vite.config.js",
    "tsconfig.json",
    "postcss.config.js",
    "tailwind.config.js",
    "phpunit.xml",
    ".phpunit.result.cache",
    "playwright.config.js",
    "CONTRIBUTING.md",
    ".editorconfig",
    ".eslintrc",
    ".stylelintrc",
    ".prettierrc",
    "*.code-workspace",
    ".env",
    ".env.*",
    "*.log",
    "debug.log",
    "error_log",
    ".DS_Store",
    "Thumbs.db",
    "desktop.ini",
    "*.tmp",
    "*.temp",
    "*.cache",
    "*.zip"
)

Write-Host "[4/5] Copying production files..." -ForegroundColor $InfoColor

# Build robocopy command
$XD = ($ExcludeDirs | ForEach-Object { Join-Path $ThemeDir $_ }) -join '" "'
$XF = $ExcludeFiles -join '" "'

# Use robocopy for fast copying
$RoboCopyArgs = "`"$ThemeDir`" `"$BuildDir`" /E /NJH /NJS /NP /NFL /NDL /R:1 /W:1 /XD `"$XD`" /XF `"$XF`""

$null = Invoke-Expression "robocopy $RoboCopyArgs"

# Robocopy exit codes: 0-7 are success (1=files copied, 2=extra files found, etc.)
$ExitCode = $LASTEXITCODE
if ($ExitCode -ge 8) {
    Write-Host "    Error: Robocopy failed with exit code $ExitCode" -ForegroundColor $ErrorColor
    Write-Host "    Attempting fallback copy method..." -ForegroundColor $WarningColor

    # Fallback: Use simple copy with filter
    $Filter = {
        $path = $_.FullName
        $skip = $false
        foreach ($dir in $ExcludeDirs) {
            if ($path -like "*\$dir\*" -or $path -like "*\$dir") {
                $skip = $true
                break
            }
        }
        -not $skip
    }

    Get-ChildItem -Path $ThemeDir -Recurse -File | Where-Object $Filter | ForEach-Object {
        $dest = $_.FullName.Replace($ThemeDir, $BuildDir)
        $destDir = Split-Path $dest
        if (!(Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        Copy-Item $_.FullName $dest -Force
    }
}

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
    [System.IO.Compression.ZipFile]::CreateFromDirectory($BuildDir, $ZipPath, [System.IO.Compression.CompressionLevel]::Optimal, $false)

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
