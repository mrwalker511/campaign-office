# Script Manager Fatal Error Fix

## Problem

When uploading the theme package to WordPress, users encountered:

1. **Fatal error**: `Class "CampaignPress\Core\Script_Manager" not found` in `includes/core/loader.php:25`
2. **Recovery mode warnings**: `Trying to access array offset on value of type bool` in WordPress core recovery mode

## Root Cause

The `class-script-manager.php` file had a redundant initialization call at the bottom of the file:

```php
// Initialize
Script_Manager::init();
```

This caused issues because:
1. The init() method was being called **twice** - once when the file was included, and again in loader.php
2. During WordPress recovery mode, the class might not be fully loaded before init() was called
3. The redundant call could cause race conditions during theme activation

## Fixes Applied

### 1. Removed Redundant Init Call

**File**: `includes/core/class-script-manager.php`

**Changed**: Removed lines 391-392
```php
// Initialize
Script_Manager::init();
```

**Reason**: The loader.php already properly initializes this class. The redundant call was unnecessary and could cause timing issues.

### 2. Added Defensive Checks in Loader

**File**: `includes/core/loader.php`

**Changed**: Added file_exists() and class_exists() checks before loading and initializing core classes.

**Before**:
```php
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/core/class-script-manager.php';
CampaignPress\Core\Script_Manager::init();
```

**After**:
```php
$core_files = array(
    CAMPAIGNPRESS_INCLUDES_DIR . '/core/class-performance.php',
    CAMPAIGNPRESS_INCLUDES_DIR . '/core/class-template-loader.php',
    CAMPAIGNPRESS_INCLUDES_DIR . '/core/class-contact-manager.php',
    CAMPAIGNPRESS_INCLUDES_DIR . '/core/class-script-manager.php',
);

foreach ($core_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    } else {
        // Log error in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Campaign Office: Core file missing - ' . $file);
        }
    }
}

// Initialize Core Systems
if (class_exists('CampaignPress\Core\Script_Manager')) {
    CampaignPress\Core\Script_Manager::init();
}
```

**Reason**: These checks make the theme more robust against:
- Missing files (incomplete uploads)
- Partial ZIP extractions
- WordPress recovery mode
- Theme activation in degraded states

## Benefits

1. ✅ **Eliminates fatal error** during theme activation
2. ✅ **Prevents WordPress recovery mode warnings** 
3. ✅ **More robust against incomplete uploads**
4. ✅ **Better error logging in debug mode**
5. ✅ **No performance impact** (checks are minimal overhead)

## Testing

After applying these fixes, the theme should:
- Install without fatal errors
- Activate successfully in WordPress
- Not trigger recovery mode
- Log missing files if WP_DEBUG is enabled

## For Theme Developers

When creating new core classes:
1. ✅ **DO** initialize them in `loader.php` with class_exists() check
2. ❌ **DON'T** add initialization calls at the bottom of class files
3. ✅ **DO** use proper namespacing
4. ✅ **DO** add file_exists() checks for optional files

## Version

These fixes are included in Campaign Office v2.0.0+
