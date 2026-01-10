# Campaign Office Theme - Build Instructions

This document explains how to create production-ready distribution packages of the Campaign Office theme.

## Quick Start

### Windows (PowerShell)

```powershell
# Run from theme directory
.\build-production.ps1
```

### Linux/macOS/Git Bash

```bash
# Make script executable (first time only)
chmod +x build-production.sh

# Run from theme directory
./build-production.sh
```

## What Gets Included

The production build includes:
- ✅ All theme PHP files
- ✅ Templates and template parts
- ✅ Compiled assets in `assets/dist/` (must run `npm run build` first)
- ✅ Critical CSS files in `assets/css/critical/`
- ✅ Images and fonts
- ✅ Language files
- ✅ README and documentation
- ✅ License file
- ✅ Composer vendor files (production only)

## What Gets Excluded

The build script automatically excludes:
- ❌ `.git` directory and git files
- ❌ `node_modules` and package files (`package.json`, `package-lock.json`)
- ❌ Composer files (`composer.json`, `composer.lock`)
- ❌ Build configuration and scripts (`build/`, `scripts/`)
- ❌ Tests (`tests/`, `phpunit.xml`, etc.)
- ❌ Documentation (`docs/`, `.distignore`, `.github/`)
- ❌ IDE files (`.vscode`, `.idea`, etc.)
- ❌ Environment files (`.env`, `.env.*`)
- ❌ Log files (`*.log`, `debug.log`, etc.)
- ❌ OS files (`.DS_Store`, `Thumbs.db`, etc.)
- ❌ Temporary files (`*.tmp`, `.cache`, etc.)
- ❌ Development dependencies from `vendor/*/tests`
- ❌ Source files (`assets/react/`, `assets/js/`)
- ❌ Block source JS files (`blocks/*/index.js`, `blocks/*/view.js`)
- ❌ Non-critical CSS (keeps only `assets/css/critical/`)
- ❌ The build scripts themselves

## Advanced Usage

### Specify Output Directory

**PowerShell:**
```powershell
.\build-production.ps1 -OutputDir "C:\releases"
```

**Bash:**
```bash
./build-production.sh /path/to/output
```

### Specify Version

**PowerShell:**
```powershell
.\build-production.ps1 -Version "2.1.0"
```

**Bash:**
```bash
./build-production.sh . 2.1.0
```

### Both Directory and Version

**PowerShell:**
```powershell
.\build-production.ps1 -OutputDir "C:\releases" -Version "2.1.0"
```

**Bash:**
```bash
./build-production.sh /path/to/output 2.1.0
```

## Output

The script creates a ZIP file with the naming format:
```
campaign-office-{VERSION}.zip
```

For example:
- `campaign-office-2.0.0.zip`
- `campaign-office-dev.zip` (if version can't be detected)

## Before Building

### 1. Update Version Number

Update the version in `style.css`:
```css
/*
Theme Name: Campaign Office
Version: 2.0.0
*/
```

### 2. Compile Assets (if applicable)

If you're using build tools, compile your assets first:

```bash
# For npm/webpack/vite builds
npm run build

# Or if using composer
composer install --no-dev --optimize-autoloader
```

**Important**: The production build script expects compiled assets in `assets/dist/`. If this directory doesn't exist, the zip will include source files instead. Always run `npm run build` before creating the production zip.

### 3. Test the Theme

Make sure everything works before building:
- Test on a local WordPress installation
- Check for PHP errors
- Verify all templates load correctly
- Test in different browsers

## Troubleshooting

### PowerShell Execution Policy Error

If you get an execution policy error on Windows:

```powershell
# Option 1: Allow for current session
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process

# Option 2: Run with bypass flag
powershell -ExecutionPolicy Bypass -File .\build-production.ps1
```

### Bash: Permission Denied

Make the script executable:
```bash
chmod +x build-production.sh
```

### Bash: zip command not found

Install zip utility:

```bash
# Ubuntu/Debian
sudo apt-get install zip

# macOS
brew install zip

# Windows Git Bash
# zip should be included, but if not, install via scoop:
scoop install zip
```

## Customizing the Build

To customize what files are included/excluded, edit the `$ExcludePatterns` array in the PowerShell script or the `--exclude` flags in the Bash script.

### Example: Include Source Files

If you want to include your source files (React, SCSS, etc.), comment out these lines:

**PowerShell (`build-production.ps1`):**
```powershell
# Comment out these lines:
# "assets/src",
# "assets/react",
# "assets/scss",
```

**Bash (`build-production.sh`):**
```bash
# Comment out these lines:
# --exclude='assets/src' \
# --exclude='assets/react' \
# --exclude='assets/scss' \
```

### Example: Exclude Documentation

To exclude README files, add to the exclude list:

**PowerShell:**
```powershell
"README.md",
"BUILD.md",
```

**Bash:**
```bash
--exclude='README.md' \
--exclude='BUILD.md' \
```

## WordPress.org Submission

If submitting to WordPress.org, the build output is ready to upload. However, note:

1. **Free Version Only**: The current theme contains premium features which cannot be submitted to WordPress.org
2. **Use Core Plugin**: Install the Campaign Office Core plugin separately
3. **Theme Review**: The theme may still need modifications to pass theme review

For WordPress.org submission, you may want to create a separate "free" version that excludes premium features.

## Distribution Checklist

Before distributing your production build:

- [ ] Version number updated in `style.css`
- [ ] Assets compiled/optimized
- [ ] Tested on fresh WordPress install
- [ ] README.txt includes accurate info
- [ ] License files included
- [ ] Screenshots up to date
- [ ] No development/debug code
- [ ] No sensitive information (API keys, etc.)
- [ ] Changelog updated

## Questions?

For issues or questions about the build process, see:
- [GitHub Issues](https://github.com/mrwalker511/campaign-office/issues)
- [Theme Documentation](https://github.com/mrwalker511/campaign-office)
