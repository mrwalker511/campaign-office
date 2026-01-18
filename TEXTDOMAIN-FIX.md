# Textdomain Loading Fix for WordPress 6.7+

## Issue
WordPress 6.7.0+ shows the following notice:
```
Notice: Function _load_textdomain_just_in_time was called incorrectly. 
Translation loading for the campaignpress domain was triggered too early. 
This is usually an indicator for some code in the plugin or theme running too early. 
Translations should be loaded at the init action or later.
```

## Root Cause
The error occurred because:

1. `functions.php` loads `includes/core/loader.php` immediately (file-level code)
2. `loader.php` loads `includes/free/translation-support.php` immediately (line 17)
3. `translation-support.php` instantiates `new CP_Translation_Support()` immediately (line 533)
4. The constructor registers `CP_Language_Switcher_Widget` at the `widgets_init` hook
5. When `widgets_init` fires (during `init` at priority 1), `register_widget()` is called
6. This instantiates the widget class, which calls `__()` in its constructor
7. But the textdomain was being loaded at `init` priority 1, which runs at the same time as `widgets_init`

## WordPress Hook Order
According to WordPress core:
- `init` hook fires with various priorities
- `widgets_init` action fires during `init` at priority 1
- Widget registration during `widgets_init` causes immediate class instantiation

## Fix Applied

### 1. Changed Textdomain Loading Priority
**File:** `functions.php` (line 94)

Changed from:
```php
add_action('init', 'campaignpress_setup_textdomain', 1);
```

To:
```php
// Use priority 0 to ensure translations are available before any init-priority-1
// callbacks (including core widgets initialization) call translation functions.
add_action('init', 'campaignpress_setup_textdomain', 0);
```

**Why this works:**
- Priority 0 runs before priority 1
- Textdomain is now loaded BEFORE `widgets_init` fires
- Translation functions are available when widget class is instantiated

### 2. Widget Constructor Continues to Use Translation Functions
**File:** `includes/free/translation-support.php` (lines 448-454)

The widget constructor can now safely use `__()` functions:
```php
public function __construct() {
    parent::__construct(
        'cp_language_switcher',
        __( 'CampaignPress Language Switcher', 'campaignpress' ),
        array( 'description' => __( 'Display language switcher for multilingual campaigns', 'campaignpress' ) )
    );
}
```

**Why this works:**
- Textdomain is loaded at `init` priority 0
- Widget registration happens at `init` priority 1 (via `widgets_init`)
- Translation functions work correctly when widget is instantiated

## Testing
To verify the fix works:
1. Ensure WordPress 6.7+ is running
2. Navigate to admin dashboard or frontend
3. No textdomain warnings should appear
4. Widget should display with proper translations

## Prevention Guidelines
To avoid this issue in the future:

1. **Always load textdomain early:**
   ```php
   add_action('init', 'your_textdomain_function', 0); // Priority 0!
   ```

2. **Be cautious with translation functions in constructors:**
   - If a class is instantiated early (before `init`), avoid `__()` calls
   - If instantiation happens at `init` priority 1 or later, it's safe

3. **File-level code runs immediately:**
   - Any code outside functions/classes runs when file is included
   - Be careful with `require_once` at file level in `functions.php`

4. **Widget registration timing:**
   - `widgets_init` fires during `init` at priority 1
   - Textdomain must be loaded at priority 0 or earlier

## Files Modified
- `/functions.php`: Changed textdomain loading to `init` priority 0
- `/includes/free/translation-support.php`: Updated comments to reflect new priority

## WordPress Version Compatibility
- **WordPress 6.7+:** Required for this fix (warning didn't exist in earlier versions)
- **WordPress 6.4-6.6:** Still works, no side effects
- **Earlier versions:** Should still work, but untested

## References
- WordPress Core: `wp-includes/l10n.php` - `_load_textdomain_just_in_time()`
- WordPress Core: `wp-includes/default-filters.php` - `widgets_init` hook registration
- WordPress Coding Standards: https://developer.wordpress.org/apis/handbook/internationalization/

## Date
2025-01-18

## Status
✅ **FIXED** - Textdomain loading properly ordered to eliminate WordPress 6.7+ warnings
