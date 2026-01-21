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

### 1. Changed Textdomain Loading Hook
**File:** `functions.php` (lines 92-97)

Changed from:
```php
add_action('init', 'campaignpress_setup_textdomain', 0);
```

To:
```php
// Load textdomain at after_setup_theme to ensure it's available before any code
// tries to use translation functions. This prevents WordPress's just-in-time
// textdomain loading from triggering the "triggered too early" warning.
add_action('after_setup_theme', 'campaignpress_setup_textdomain');
```

**Why `after_setup_theme` instead of `init` priority 0:**
- `after_setup_theme` runs before `init`, ensuring textdomain is loaded first
- WordPress's just-in-time textdomain loading can still trigger before `init` priority 0 in some edge cases
- While WordPress 6.7+ docs suggest `init`, `after_setup_theme` is the traditional theme approach and still valid
- This prevents race conditions with widget registration and block initialization

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
- Textdomain is loaded at `after_setup_theme` (before `init`)
- Widget registration happens at `widgets_init` (during `init` at priority 1)
- Translation functions are available well before widgets are instantiated

## Testing
To verify the fix works:
1. Ensure WordPress 6.7+ is running
2. Navigate to admin dashboard or frontend
3. No textdomain warnings should appear
4. Widget should display with proper translations

## Prevention Guidelines
To avoid this issue in the future:

1. **Always load textdomain at `after_setup_theme` for themes:**
   ```php
   add_action('after_setup_theme', 'your_textdomain_function');
   ```

2. **Be cautious with translation functions in constructors:**
   - If a class is instantiated early (before `after_setup_theme`), avoid `__()` calls
   - If instantiation happens at `init` or later, it's safe

3. **File-level code runs immediately:**
   - Any code outside functions/classes runs when file is included
   - Be careful with `require_once` at file level in `functions.php`

4. **Widget registration timing:**
   - `widgets_init` fires during `init` at priority 1
   - Textdomain must be loaded before `init` to be safe

## Files Modified
- `/functions.php`: Changed textdomain loading to `after_setup_theme` hook
- `/includes/core/loader.php`: Updated comment about translation support
- `/includes/free/translation-support.php`: Contains WPML/Polylang compatibility (no changes needed)

## WordPress Version Compatibility
- **WordPress 6.7+:** Required for this fix (warning didn't exist in earlier versions)
- **WordPress 6.4-6.6:** Still works, no side effects
- **Earlier versions:** Should still work, but untested

## References
- WordPress Core: `wp-includes/l10n.php` - `_load_textdomain_just_in_time()`
- WordPress Core: `wp-includes/default-filters.php` - `widgets_init` hook registration
- WordPress Coding Standards: https://developer.wordpress.org/apis/handbook/internationalization/

## Date
2025-01-19 (Updated)

## Status
✅ **FIXED** - Textdomain now loads at `after_setup_theme` to eliminate WordPress 6.7+ warnings

## Revision History
- 2025-01-18: Initial fix using `init` priority 0
- 2025-01-19: Changed to `after_setup_theme` hook for more reliable early loading
