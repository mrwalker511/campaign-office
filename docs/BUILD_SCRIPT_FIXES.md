# Build Script Fixes - Theme ZIP Production Build

## Problem Summary

The production build scripts (`build-production.sh` and `build-production.ps1`) were incorrectly including development files that should not be distributed with the WordPress theme. This resulted in bloated ZIP files containing source code, build configurations, and development dependencies.

## Issues Fixed

### 1. Build Configuration Files Were Being Included

**Problem**: The scripts were excluding individual config files (e.g., `vite.config.js`, `tailwind.config.js`) at the root level, but these files are actually located in the `build/` subdirectory.

**Fix**: Added `build/` directory to the exclusion list to prevent all build configuration files from being included.

### 2. Source Files Were Being Included Instead of Compiled Assets

**Problem**: The scripts were not excluding:
- `assets/react/` - React source files
- `assets/js/` - JavaScript source files
- `blocks/*/index.js` - Block editor scripts
- `blocks/*/view.js` - Block view scripts
- `assets/css/*` - CSS source files (excluding critical CSS)

**Fix**: Added exclusions for all source directories and files. The scripts now only include:
- Compiled assets in `assets/dist/` (created by `npm run build`)
- Critical CSS files in `assets/css/critical/`

### 3. Build and Optimization Scripts Were Being Included

**Problem**: The `scripts/` directory containing optimization scripts was not being excluded.

**Fix**: Added `scripts/` directory to the exclusion list.

### 4. Package Management Files Were Being Included

**Problem**: The bash script excluded `package-lock.json` but not `package.json`. It also didn't exclude Composer files.

**Fix**: Added `package.json`, `package-lock.json`, `composer.json`, and `composer.lock` to the exclusion list in both scripts.

### 5. Documentation and CI/CD Files Were Being Included

**Problem**: The `.distignore` file and `.github/` directory (containing CI/CD workflows) were not being excluded.

**Fix**: Added `.distignore` and `.github/` to the exclusion list.

### 6. Glob Pattern Support for Config Files

**Problem**: Individual config file patterns (`.eslintrc`, `.stylelintrc`, etc.) didn't match files with extensions like `.eslintrc.json`.

**Fix**: Changed patterns to use wildcards: `.eslintrc*`, `.stylelintrc*`, `.prettierrc*`

## Changes Made

### build-production.sh

Updated the rsync exclusion list to:
- Exclude entire directories: `build/`, `scripts/`, `assets/react/`, `assets/js/`
- Exclude package management files: `package.json`, `package-lock.json`, `composer.json`, `composer.lock`
- Exclude block source files using pattern: `blocks/**/index.js`, `blocks/**/view.js`
- Exclude CSS source files but include critical: `--exclude='assets/css/*'` with `--include='assets/css/critical/'`
- Exclude all config file variants with wildcards: `.eslintrc*`, `.stylelintrc*`, `.prettierrc*`
- Exclude additional dev files: `.distignore`, `.github/`, `build-testing.ps1`

Updated the fallback cp exclusion list with equivalent changes using `rm -rf` and `find` commands.

### build-production.ps1

Updated the `$ExcludePatterns` array to:
- Exclude entire directories: `build`, `scripts`, `assets/react`, `assets/js`
- Exclude package management files: `package.json`, `package-lock.json`, `composer.json`, `composer.lock`
- Exclude block source files: `blocks/*/index.js`, `blocks/*/view.js`
- Exclude CSS source files: `assets/css/*`
- Exclude all config file variants with wildcards: `.eslintrc*`, `.stylelintrc*`, `.prettierrc*`
- Exclude additional dev files: `.distignore`, `.github`, `build-testing.ps1`

Added special case in `Should-Exclude` function to allow critical CSS files even though `assets/css/*` is excluded.

### BUILD.md

Updated documentation to:
- Clarify that `assets/dist/` must exist (created by `npm run build`)
- Specify that only critical CSS files are included from `assets/css/`
- Add warning that ZIP will include source files if assets/dist doesn't exist
- Update exclusion list to reflect actual exclusions made by scripts

## Important Notes

### Before Running Build Script

You **must** run `npm run build` first to compile assets:

```bash
npm run build
```

This creates the `assets/dist/` directory with compiled JavaScript and CSS files. Without this step, the ZIP will be missing production assets.

### What Gets Included

After these fixes, the production ZIP includes:

✅ All theme PHP files
✅ Templates and template parts
✅ Compiled assets in `assets/dist/` (blocks.js, crm.js, main.js, tailwind.css)
✅ Critical CSS files in `assets/css/critical/`
✅ Images and fonts
✅ Language files
✅ Block PHP files and block.json configurations
✅ README and documentation
✅ License file
✅ Composer vendor files (production dependencies)

### What Gets Excluded

After these fixes, the production ZIP excludes:

❌ Source files (`assets/react/`, `assets/js/`, `assets/css/` except critical)
❌ Block source JS files (`blocks/*/index.js`, `blocks/*/view.js`)
❌ Build configuration (`build/`, `scripts/`)
❌ Package files (`package.json`, `package-lock.json`, `composer.json`, `composer.lock`)
❌ Development dependencies (`node_modules`, `vendor/*/tests`)
❌ Tests (`tests/`, `phpunit.xml`, etc.)
❌ Documentation for developers (`docs/`, `.distignore`)
❌ CI/CD files (`.github/`)
❌ IDE files (`.vscode/`, `.idea/`, etc.)
❌ Environment and log files (`.env`, `*.log`, etc.)

## Testing the Build

To verify the build is working correctly:

```bash
# Compile assets first
npm run build

# Run the build script
./build-production.sh

# Verify the ZIP contents
unzip -l campaign-office-*.zip
```

The output should show:
- No `node_modules/`, `build/`, `scripts/`, `assets/react/`, `assets/js/` directories
- No individual config files like `vite.config.js`, `tailwind.config.js`
- No package management files like `package.json`, `composer.json`
- Compiled files in `assets/dist/` directory
- Critical CSS files in `assets/css/critical/` directory

## Related Files

The `.distignore` file already had the correct exclusion patterns, which were used to update both build scripts to ensure consistency across all build processes.

## Date Fixed

2025-01-02
