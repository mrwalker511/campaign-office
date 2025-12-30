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

    # Composer dev dependencies
    "vendor/bin",
    "vendor/*/*/tests",
    "vendor/*/*/test",
    "vendor/*/*/Tests",
    "vendor/*/*/Test"
)

Write-Host "[4/5] Copying production files..." -ForegroundColor $InfoColor

# Function to recursively copy files with exclusions
function Copy-FilesWithExclusions {
    param(
        [string]$SourcePath,
        [string]$DestPath,
        [string]$RelativeBase = ""
    )

    # Get items in current directory
    $Items = Get-ChildItem -Path $SourcePath -ErrorAction SilentlyContinue

    foreach ($Item in $Items) {
        $RelativePath = if ($RelativeBase) { "$RelativeBase/$($Item.Name)" } else { $Item.Name }
        $RelativePath = $RelativePath.Replace("\", "/")

        # Check if directory should be excluded
        if ($Item.PSIsContainer) {
            $ExcludeDir = $false
            foreach ($ExcludeDirPattern in $ExcludeDirs) {
                if ($Item.Name -eq $ExcludeDirPattern -or $RelativePath -eq $ExcludeDirPattern -or $RelativePath -like "$ExcludeDirPattern/*") {
                    $ExcludeDir = $true
                    break
                }
            }

            if (-not $ExcludeDir) {
                # Recurse into directory
                $NewDest = Join-Path $DestPath $Item.Name
                Copy-FilesWithExclusions -SourcePath $Item.FullName -DestPath $NewDest -RelativeBase $RelativePath
            }
        } else {
            # Check if file should be excluded
            $ExcludeFile = $false

            # Special includes (override exclusions)
            # Include block scripts (index.js and view.js in blocks/)
            if ($RelativePath -match "^blocks/.+/(index|view)\.js$") {
                $ExcludeFile = $false
            }
            # Include critical CSS files
            elseif ($RelativePath -like "assets/css/critical/*") {
                $ExcludeFile = $false
            }
            # Exclude non-critical CSS (assets/css/* except critical/)
            elseif ($RelativePath -like "assets/css/*" -and $RelativePath -notlike "assets/css/critical/*") {
                $ExcludeFile = $true
            }
            else {
                # Check file exclusion patterns
                foreach ($Pattern in $ExcludeFiles) {
                    if ($Item.Name -eq $Pattern -or $RelativePath -eq $Pattern) {
                        $ExcludeFile = $true
                        break
                    }
                    if ($Pattern -like "*`**") {
                        if ($Item.Name -like $Pattern -or $RelativePath -like $Pattern) {
                            $ExcludeFile = $true
                            break
                        }
                    }
                }
            }

            if (-not $ExcludeFile) {
                # Copy file
                if (-not (Test-Path $DestPath)) {
                    New-Item -ItemType Directory -Path $DestPath -Force | Out-Null
                }
                Copy-Item $Item.FullName (Join-Path $DestPath $Item.Name) -Force
                $script:CopiedCount++

                # Show progress every 50 files
                if ($script:CopiedCount % 50 -eq 0) {
                    Write-Host "    Copied $($script:CopiedCount) files..." -ForegroundColor $InfoColor
                }
            }
        }
    }
}

# Copy files
$script:CopiedCount = 0
Copy-FilesWithExclusions -SourcePath $ThemeDir -DestPath $BuildDir

$ItemCount = $script:CopiedCount
Write-Host "    Total: $ItemCount files copied" -ForegroundColor $SuccessColor

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
