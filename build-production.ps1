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

# Files and directories to EXCLUDE
$ExcludePatterns = @(
    # Version control
    ".git",
    ".gitignore",
    ".gitattributes",

    # Node/NPM
    "node_modules",
    "package.json",
    "package-lock.json",
    ".npmrc",

    # Composer
    "composer.json",
    "composer.lock",

    # Build tools and scripts
    "build-production.ps1",
    "build-production.sh",
    "build-testing.ps1",
    "build",
    "scripts",

    # Testing
    "tests",
    "phpunit.xml",
    ".phpunit.result.cache",
    "playwright.config.js",
    "playwright-report",
    "test-results",

    # Documentation
    "docs",
    ".distignore",
    ".github",

    # IDE files
    ".vscode",
    ".idea",
    "*.code-workspace",

    # Environment files
    ".env",
    ".env.*",

    # Logs
    "*.log",
    "debug.log",
    "error_log",

    # OS files
    ".DS_Store",
    "Thumbs.db",
    "desktop.ini",

    # Temporary files
    "*.tmp",
    "*.temp",
    "*.cache",
    ".sass-cache",

    # Development files
    ".editorconfig",
    ".eslintrc*",
    ".stylelintrc*",
    ".prettierrc*",

    # Claude/AI files
    ".claude",

    # ZIP files
    "*.zip",

    # Source files (keep only compiled assets)
    "assets/react",
    "assets/js",

    # Block source JS files
    "blocks/*/index.js",
    "blocks/*/view.js",

    # CSS source (keep only critical)
    "assets/css/*",

    # Composer dev dependencies
    "vendor/bin",
    "vendor/*/*/tests",
    "vendor/*/*/test",
    "vendor/*/*/Tests",
    "vendor/*/*/Test"
)

Write-Host "[4/5] Copying production files..." -ForegroundColor $InfoColor

# Function to check if path should be excluded
function Should-Exclude {
    param([string]$Path)

    $RelativePath = $Path.Replace("$ThemeDir\", "").Replace("$ThemeDir/", "")

    # Special case: include critical CSS files even though assets/css/* is excluded
    if ($RelativePath -like "assets/css/critical*") {
        return $false
    }

    foreach ($Pattern in $ExcludePatterns) {
        # Handle wildcards
        if ($Pattern -like "*`**") {
            if ($RelativePath -like $Pattern) {
                return $true
            }
        }
        # Handle directory/file names
        else {
            $PathParts = $RelativePath -split '[\\/]'
            if ($PathParts -contains $Pattern) {
                return $true
            }
            if ($RelativePath -eq $Pattern) {
                return $true
            }
        }
    }

    return $false
}

# Copy files recursively
$ItemCount = 0
$ExcludedCount = 0

Get-ChildItem -Path $ThemeDir -Recurse -Force | ForEach-Object {
    $SourcePath = $_.FullName

    if (Should-Exclude $SourcePath) {
        $ExcludedCount++
        return
    }

    $RelativePath = $SourcePath.Substring($ThemeDir.Length + 1)
    $DestPath = Join-Path $BuildDir $RelativePath

    if ($_.PSIsContainer) {
        New-Item -ItemType Directory -Path $DestPath -Force | Out-Null
    } else {
        $DestDir = Split-Path $DestPath -Parent
        if (-not (Test-Path $DestDir)) {
            New-Item -ItemType Directory -Path $DestDir -Force | Out-Null
        }
        Copy-Item $SourcePath $DestPath -Force
        $ItemCount++
    }
}

Write-Host "    Copied $ItemCount files (excluded $ExcludedCount items)" -ForegroundColor $SuccessColor

# Create ZIP file
$OutputPath = Resolve-Path $OutputDir
$ZipFileName = "$ThemeName-$Version.zip"
$ZipPath = Join-Path $OutputPath $ZipFileName

Write-Host "[5/5] Creating ZIP archive..." -ForegroundColor $InfoColor

# Remove existing ZIP if it exists
if (Test-Path $ZipPath) {
    Remove-Item $ZipPath -Force
    Write-Host "    Removed existing ZIP file" -ForegroundColor $WarningColor
}

# Create ZIP (requires PowerShell 5.0+)
try {
    Compress-Archive -Path $BuildDir -DestinationPath $ZipPath -CompressionLevel Optimal

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
        Remove-Item $TempDir -Recurse -Force
    }
}

# Open file location (optional)
$OpenLocation = Read-Host "`nOpen file location? (Y/N)"
if ($OpenLocation -eq "Y" -or $OpenLocation -eq "y") {
    Start-Process explorer.exe "/select,$ZipPath"
}
