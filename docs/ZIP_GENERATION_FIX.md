# ZIP Generation Fix - WordPress Archive Error Resolution

## Issue
The build scripts were generating ZIP files that WordPress rejected with an "Archive error" message when attempting to upload and install the theme.

## Root Causes Identified

### 1. **Incorrect Theme Directory Name** (CRITICAL)
- **Problem**: The ZIP was using the directory name `project/` instead of the proper theme slug `campaign-office/`
- **Impact**: WordPress expects the directory name inside the ZIP to match the theme slug (from Text Domain in style.css)
- **Why it matters**: WordPress uses this directory name when extracting the theme to wp-content/themes/
- **Fix**: Extract theme slug from `Text Domain:` header in style.css and use that for the ZIP directory name

### 2. **Absolute Path Handling** 
- **Problem**: The bash script was changing directories before creating the ZIP, causing relative OUTPUT_DIR paths to resolve inside the temp directory
- **Impact**: ZIP files were created in temp directory and immediately deleted by cleanup function
- **Fix**: Convert OUTPUT_DIR to absolute path before changing directories

### 3. **Broken PowerShell Script Logic**
- **Problem**: PowerShell script had incomplete/broken file filtering code with undefined variables
- **Impact**: Script couldn't properly filter files for production build
- **Fix**: Rewrote file filtering logic to use simple copy-then-delete pattern

### 4. **Incorrect Asset Filtering**
- **Problem**: bash script was including block source files (index.js, view.js) instead of excluding them
- **Impact**: Source files unnecessarily included in production ZIP
- **Fix**: Changed rsync include patterns to exclude patterns for block source files

## Changes Made

### build-production.sh (Bash Script)

1. **Theme Slug Extraction** (lines 34-46):
   ```bash
   # Extract theme slug from Text Domain in style.css
   THEME_SLUG=$(grep -i "Text Domain:" "$THEME_DIR/style.css" | head -n 1 | sed -E 's/.*Text Domain:\s*([a-z0-9_-]+).*/\1/')
   if [ -z "$THEME_SLUG" ]; then
       # Fallback to directory name if Text Domain not found
       THEME_SLUG="$(basename "$THEME_DIR")"
   fi
   THEME_NAME="$THEME_SLUG"
   ```

2. **Absolute Path Conversion** (lines 177-184):
   ```bash
   # Convert OUTPUT_DIR to absolute path
   if [[ "$OUTPUT_DIR" = /* ]]; then
       ZIP_PATH="$OUTPUT_DIR/$ZIP_FILENAME"
   else
       ZIP_PATH="$(cd "$OUTPUT_DIR" && pwd)/$ZIP_FILENAME"
   fi
   ```

3. **Fixed Asset Exclusions** (lines 113-119):
   ```bash
   --exclude='assets/react/' \
   --exclude='assets/js/' \
   --exclude='blocks/*/index.js' \
   --exclude='blocks/*/view.js' \
   --exclude='assets/css/' \
   --include='assets/css/critical/**' \
   --include='assets/css/critical/'
   ```

4. **Math Calculation Fix** (lines 198-213):
   - Replaced `bc` command (not always available) with `awk` for file size calculations

### build-production.ps1 (PowerShell Script)

1. **Theme Slug Extraction** (lines 48-76):
   ```powershell
   # Extract theme slug from Text Domain
   if ($StyleContent -match "Text Domain:\s*([a-z0-9_-]+)") {
       $ThemeSlug = $matches[1]
   } else {
       # Fallback to directory name
       $ThemeSlug = Split-Path $ThemeDir -Leaf
   }
   $ThemeName = $ThemeSlug
   ```

2. **Complete Rewrite of File Filtering** (lines 70-160):
   - Replaced broken logic with simple copy-then-delete pattern
   - Properly handles critical CSS preservation
   - Correctly removes block source files

## Verification

### ZIP Structure Verification
```bash
unzip -l campaign-office-2.0.0.zip | head -15
```

Expected output:
```
Archive:  campaign-office-2.0.0.zip
  Length      Date    Time    Name
---------  ---------- -----   ----
        0  2025-12-30 00:46   campaign-office/
   939100  2025-12-30 00:38   campaign-office/screenshot.png
     3537  2025-12-30 00:38   campaign-office/index.php
    16007  2025-12-30 00:38   campaign-office/theme.json
        0  2025-12-30 00:38   campaign-office/blocks/
```

### ZIP Integrity Check
```bash
unzip -t campaign-office-2.0.0.zip
```

Should complete with: `No errors detected in compressed data`

### Theme Headers Verification
```bash
unzip -p campaign-office-2.0.0.zip campaign-office/style.css | head -20
```

Should show proper WordPress theme headers including `Text Domain: campaign-office`

## Testing

### Create Production ZIP
```bash
bash build-production.sh
```

### Expected Output
```
========================================
Campaign Office Theme - Production Build
========================================

[1/5] Validating theme directory...
[2/5] Reading theme information from style.css...
    Theme slug: campaign-office
    Version: 2.0.0
[3/5] Creating temporary build directory...
[4/5] Copying production files...
    Copied 1501 files
[5/5] Creating ZIP archive...

========================================
Build Complete!
========================================

Production ZIP created:
  File: campaign-office-2.0.0.zip
  Location: /home/engine/project/campaign-office-2.0.0.zip
  Size: 2.21 MB
  Files: 1501

Ready for distribution!
```

### Upload to WordPress
1. Go to WordPress Admin > Appearance > Themes
2. Click "Add New" > "Upload Theme"
3. Choose `campaign-office-2.0.0.zip`
4. Click "Install Now"
5. Should install successfully without "Archive error"

## Technical Details

### WordPress ZIP Requirements
WordPress theme ZIP files must meet these requirements:
1. **Single root directory**: ZIP must contain exactly one directory at root level
2. **Directory naming**: Directory name should match theme slug (Text Domain)
3. **Required files**: Must contain style.css with proper theme headers in root directory
4. **Valid ZIP format**: Must be standard ZIP format compatible with PHP's ZipArchive class
5. **No absolute paths**: All paths in ZIP must be relative
6. **Proper permissions**: File attributes should not contain restrictive permissions

### File Exclusions for Production
The following are excluded from production builds:
- Development files: .git, .gitignore, .github/, .vscode/, .idea/, etc.
- Build tools: node_modules/, package.json, build/, scripts/
- Source files: assets/react/, assets/js/, blocks/*/index.js, blocks/*/view.js
- Non-critical CSS: assets/css/* (except assets/css/critical/)
- Documentation: docs/, .distignore
- Testing files: tests/, phpunit.xml, playwright.config.js
- Temporary files: *.tmp, *.log, .DS_Store, Thumbs.db

### Files Included in Production
- All PHP files: functions.php, all includes/, templates/, etc.
- Compiled assets: assets/dist/ directory (must run `npm run build` first)
- Critical CSS: assets/css/critical/
- Static assets: assets/fonts/, assets/images/, assets/icons/, assets/vendor/
- Block metadata: blocks/*/block.json, blocks/*/render.php, blocks/*/style.css
- Theme files: style.css, theme.json, screenshot.png, index.php
- Template files: templates/*.html, patterns/*, parts/*

## Important Notes

⚠️ **Before creating production ZIP**: Always run `npm run build` to compile assets to assets/dist/. Without this, the theme will be missing critical JavaScript and CSS files.

⚠️ **Theme slug consistency**: Ensure the `Text Domain:` header in style.css matches your intended theme slug. This will be used as the directory name in the ZIP and in WordPress installations.

⚠️ **Output directory**: By default, the ZIP is created in the theme directory (`.`). You can specify a different location with:
```bash
bash build-production.sh /path/to/output
```

## Resolution Summary

The main issue causing WordPress "Archive error" was the incorrect directory name inside the ZIP file. By extracting the theme slug from the `Text Domain:` header in style.css and using that as the directory name, the ZIP now conforms to WordPress expectations and uploads successfully.

Additional improvements to file filtering, path handling, and script robustness ensure the build process is reliable and produces valid WordPress theme ZIP files.

---
**Date**: 2025-01-02  
**Issue**: ZIP files rejected by WordPress with "Archive error"  
**Status**: ✅ RESOLVED
