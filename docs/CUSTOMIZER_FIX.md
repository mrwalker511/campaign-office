# WordPress Customizer Live Preview Fix

**Date:** January 16, 2025
**Issue:** Customizer not updating page components in live preview
**Status:** ✅ Fixed

---

## Problem Description

The WordPress Customizer was not updating the preview pane when users changed settings. The customizer interface would load, but changes to colors, layouts, hero settings, and other options were not reflected in real-time in the preview iframe.

## Root Cause Analysis

### 1. Missing jQuery Dependency
The customizer preview JavaScript (`assets/js/customizer.js`) was being enqueued without jQuery as a dependency:

```php
// ❌ BEFORE (line 582 in customizer.php)
wp_enqueue_script(
    'campaignpress-customizer',
    CAMPAIGNPRESS_ASSETS_URI . '/js/customizer.js',
    array('customize-preview'),  // Missing jQuery!
    CAMPAIGNPRESS_VERSION,
    true
);
```

The script uses jQuery (`$`) throughout but wasn't guaranteed to be loaded before the script executed.

### 2. Improper Initialization Timing
The customizer preview script was not wrapped in a `wp.customize.bind('ready', ...)` event handler. This meant the script could execute before the WordPress Customizer API was fully initialized, causing bindings to fail silently.

```javascript
// ❌ BEFORE (customizer.js)
(function ($) {
  'use strict';

  // Direct bindings - might execute too early!
  wp.customize('blogname', function (value) {
    value.bind(function (newval) {
      $('.wp-block-site-title a, .site-title a').text(newval);
    });
  });

})(jQuery);
```

### 3. Conflicting Customizer Preview Files
There was an obsolete `customizer-preview.js` file that referenced non-existent setting names:
- `cp_color_primary` (should be `campaignpress_primary_color`)
- `cp_color_secondary` (should be `campaignpress_secondary_color`)
- `cp_color_scheme` (should be `campaignpress_color_scheme`)
- `cp_heading_font`, `cp_body_font`, `cp_container_width` (don't exist)

While this file wasn't being enqueued, its presence could cause confusion and potential conflicts if accidentally loaded.

## Solution

### 1. Added jQuery Dependency
Updated the script enqueue to include jQuery:

```php
// ✅ AFTER (line 582 in customizer.php)
wp_enqueue_script(
    'campaignpress-customizer',
    CAMPAIGNPRESS_ASSETS_URI . '/js/customizer.js',
    array('customize-preview', 'jquery'),  // jQuery added!
    CAMPAIGNPRESS_VERSION,
    true
);
```

### 2. Wrapped Bindings in Ready Event
Wrapped all customizer bindings in the `wp.customize.bind('ready', ...)` event:

```javascript
// ✅ AFTER (customizer.js)
(function ($) {
  'use strict';

  // Wait for customizer to be ready
  wp.customize.bind('ready', function () {

    // All bindings now inside this wrapper
    wp.customize('blogname', function (value) {
      value.bind(function (newval) {
        $('.wp-block-site-title a, .site-title a').text(newval);
      });
    });

    // ... all other bindings ...

  });
})(jQuery);
```

### 3. Removed Conflicting File
Deleted the obsolete `assets/js/customizer-preview.js` file to eliminate potential conflicts.

## Files Modified

| File | Changes |
|------|---------|
| `/includes/free/customizer.php` | Added 'jquery' to wp_enqueue_script() dependencies |
| `/assets/js/customizer.js` | Wrapped all bindings in wp.customize.bind('ready', ...) |

## Files Removed

| File | Reason |
|------|--------|
| `/assets/js/customizer-preview.js` | Obsolete, referenced non-existent settings |

## Testing

### Manual Testing Steps

1. **Navigate to Customizer:**
   - Go to **Appearance** → **Customize**

2. **Test Color Scheme:**
   - Navigate to **Colors** section
   - Change "Party Color Scheme" to "Republican Red"
   - **Expected:** Preview updates immediately with red accents

3. **Test Primary Color:**
   - In **Colors** section, change "Primary Color Override"
   - **Expected:** Preview updates with new primary color immediately

4. **Test Hero Settings:**
   - Navigate to **Hero Section**
   - Change overlay opacity using the slider
   - **Expected:** Hero section overlay darkness updates in real-time

5. **Test Site Title:**
   - In **Site Identity** section, change the site title
   - **Expected:** Site title in header updates immediately

6. **Test Menu Layout:**
   - Navigate to **Navigation** section
   - Toggle between "Inline (Horizontal)" and "Vertical (Stacked)"
   - **Expected:** Menu layout changes in the preview

### Automated Testing

The E2E test suite at `/tests/e2e/customizer.spec.js` includes comprehensive tests for:
- Customizer interface loading
- Color scheme changes
- Preview updates
- Save and publish functionality

Run tests with:
```bash
npm run test:e2e -- tests/e2e/customizer.spec.js
```

## Customizer Settings Reference

### Color Settings
| Setting Name | Default | Transport |
|--------------|---------|-----------|
| `campaignpress_color_scheme` | democrat-blue | postMessage |
| `campaignpress_primary_color` | #0053c3 | postMessage |
| `campaignpress_secondary_color` | #ff8800 | postMessage |

### Hero Settings
| Setting Name | Default | Transport |
|--------------|---------|-----------|
| `campaignpress_hero_media_type` | image | postMessage |
| `campaignpress_hero_image` | (empty) | postMessage |
| `campaignpress_hero_video` | (empty) | postMessage |
| `campaignpress_hero_overlay_opacity` | 50 | postMessage |

### Navigation Settings
| Setting Name | Default | Transport |
|--------------|---------|-----------|
| `campaignpress_primary_menu_layout` | inline | postMessage |

### Site Identity Settings
| Setting Name | Default | Transport |
|--------------|---------|-----------|
| `blogname` | (from WP settings) | postMessage |
| `blogdescription` | (from WP settings) | postMessage |
| `campaignpress_disclaimer_text` | "Paid for by Friends of the Candidate" | postMessage |

## Technical Details

### How the Customizer Works

1. **PHP Registration** (`/includes/free/customizer.php`):
   - Registers settings with `add_setting()`
   - Creates controls with `add_control()`
   - Uses `transport => 'postMessage'` for live preview (no page refresh)

2. **JavaScript Live Preview** (`/assets/js/customizer.js`):
   - Binds to setting changes with `wp.customize('setting_name', function(value) { value.bind(callback) })`
   - Updates DOM elements in preview frame when settings change
   - Uses inline `<style>` tags for color updates to avoid CSS cascade issues

3. **Customizer Controls** (`/assets/js/customizer-controls.js`):
   - Runs in the customizer panel (not the preview)
   - Handles control-specific interactions (e.g., color scheme picker updates individual color controls)

### Why `postMessage` Transport Matters

Settings with `transport => 'postMessage'`:
- ✅ Live preview without page refresh
- ✅ Instant visual feedback
- ✅ Smooth user experience
- ✅ Requires JavaScript bindings to work

Settings without transport or with `transport => 'refresh'`:
- ❌ Full page refresh required to see changes
- ❌ Slower user experience
- ❌ No JavaScript bindings needed

## Best Practices for Customizer Development

1. **Always use `postMessage` transport** for settings that can be previewed
2. **Include jQuery as a dependency** if your script uses it
3. **Wrap bindings in `wp.customize.bind('ready', ...)`** to ensure proper initialization
4. **Use CSS variables** (`--wp--preset--*`) for color updates to avoid specificity issues
5. **Test on multiple devices** (desktop, tablet, mobile) using device switcher
6. **Keep preview scripts lightweight** - they load in the preview iframe

## Troubleshooting

### Preview Not Updating
- Check browser console for JavaScript errors
- Verify jQuery is loaded (should have no `$ is not defined` errors)
- Ensure `wp.customize` is available (check `typeof wp.customize === 'object'`)
- Verify setting names match between PHP and JavaScript

### Settings Not Saving
- Check for `transport` parameter in `add_setting()`
- Verify `sanitize_callback` is set and working correctly
- Check capability requirements (`capability` parameter)

### Selective Refresh Not Working
- Verify `wp.customize.selective_refresh` exists
- Check that selectors in partials match actual DOM elements
- Ensure render callbacks return valid HTML

## Related Files

- `/includes/free/customizer.php` - Customizer PHP registration
- `/assets/js/customizer.js` - Live preview JavaScript
- `/assets/js/customizer-controls.js` - Control panel JavaScript
- `/tests/e2e/customizer.spec.js` - E2E tests
- `theme.json` - Design tokens (colors, fonts, spacing)

## Changelog

**2025-01-16: Fixed customizer live preview**
- Added jQuery dependency to customizer script
- Wrapped bindings in `wp.customize.bind('ready', ...)`
- Removed obsolete customizer-preview.js file
- Verified all color scheme, hero, and navigation settings update correctly

---

**Fix Status:** ✅ Complete and tested
**Branch:** `fix/wp-customizer-live-preview-not-updating`
