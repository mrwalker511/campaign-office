# WordPress Built-in Libraries Reference

This document provides a comprehensive reference for all libraries and scripts that WordPress includes by default. Using these built-in libraries instead of loading your own versions reduces page weight, prevents version conflicts, and ensures compatibility with plugins.

## How to Use This Guide

When adding new scripts to the theme, always check this guide first to see if WordPress already provides the library you need.

### Basic Usage

```php
// Instead of loading your own jQuery:
wp_enqueue_script('my-script', get_template_directory_uri() . '/assets/js/my-script.js', array('jquery'), '1.0.0', true);

// WordPress will automatically load its jQuery version
```

---

## Core JavaScript Libraries

### jQuery

**Handle**: `jquery`
**WordPress Version**: 3.9+ (jQuery Migrate included)
**Description**: WordPress's version of jQuery runs in no-conflict mode

```php
wp_enqueue_script('my-script', $url, array('jquery'), '1.0.0', true);
```

**Important Notes**:
- WordPress jQuery runs in no-conflict mode (`jQuery` instead of `$`)
- Use `jQuery` or wrap in: `(function($) { /* code */ })(jQuery);`
- Do NOT deregister and re-register jQuery unless absolutely necessary

---

## jQuery UI Components

All jQuery UI components are available separately. Load only what you need!

### jQuery UI Core

**Handle**: `jquery-ui-core`
**Dependencies**: `jquery`

```php
wp_enqueue_script('my-admin-script', $url, array('jquery-ui-core'), '1.0.0', true);
```

### jQuery UI Widgets

| Handle | Description | Use Case |
|--------|-------------|----------|
| `jquery-ui-accordion` | Accordion widget | Collapsible content sections |
| `jquery-ui-autocomplete` | Autocomplete input | Search suggestions, tag input |
| `jquery-ui-button` | Button widget | Enhanced buttons |
| `jquery-ui-datepicker` | Date picker | Date selection forms |
| `jquery-ui-dialog` | Modal dialogs | Pop-up windows, confirmations |
| `jquery-ui-menu` | Menu widget | Dropdown menus |
| `jquery-ui-mouse` | Mouse interactions | Required for draggable/sortable |
| `jquery-ui-progressbar` | Progress bar | Loading indicators |
| `jquery-ui-selectmenu` | Enhanced select | Styled dropdowns |
| `jquery-ui-slider` | Slider control | Range inputs, volume controls |
| `jquery-ui-spinner` | Number spinner | Numeric input with +/- buttons |
| `jquery-ui-tabs` | Tab panels | Tabbed content |
| `jquery-ui-tooltip` | Tooltips | Help text on hover |

### jQuery UI Interactions

| Handle | Description | Use Case |
|--------|-------------|----------|
| `jquery-ui-draggable` | Drag elements | Drag-and-drop interfaces |
| `jquery-ui-droppable` | Drop zones | Drag-and-drop targets |
| `jquery-ui-resizable` | Resize elements | Resizable panels |
| `jquery-ui-selectable` | Select multiple items | Multi-select lists |
| `jquery-ui-sortable` | Sort lists | **Currently used in Field Ops & Integrations** |

### jQuery UI Effects

| Handle | Description |
|--------|-------------|
| `jquery-effects-core` | Core effects engine |
| `jquery-effects-blind` | Blind effect |
| `jquery-effects-bounce` | Bounce effect |
| `jquery-effects-clip` | Clip effect |
| `jquery-effects-drop` | Drop effect |
| `jquery-effects-explode` | Explode effect |
| `jquery-effects-fade` | Fade effect |
| `jquery-effects-fold` | Fold effect |
| `jquery-effects-highlight` | Highlight effect |
| `jquery-effects-puff` | Puff effect |
| `jquery-effects-pulsate` | Pulsate effect |
| `jquery-effects-scale` | Scale effect |
| `jquery-effects-shake` | Shake effect |
| `jquery-effects-size` | Size effect |
| `jquery-effects-slide` | Slide effect |
| `jquery-effects-transfer` | Transfer effect |

**Example - Using Sortable (Already in theme)**:
```php
// In field-ops-init.php (line 206)
wp_enqueue_script('cp-field-ops-admin',
    get_template_directory_uri() . '/assets/js/field-ops-admin.js',
    array('jquery', 'jquery-ui-sortable', 'wp-api'), // ✓ Correct!
    VERSION,
    true
);
```

---

## WordPress-Specific Scripts

### Backbone.js & Underscore.js

**Handles**: `backbone`, `underscore`
**WordPress Version**: Since WP 3.5
**Description**: Backbone.js MVC framework and Underscore.js utility library

```php
wp_enqueue_script('my-app', $url, array('backbone', 'underscore'), '1.0.0', true);
```

**JavaScript Usage**:
```javascript
// Underscore.js is available globally as _
var filtered = _.filter([1, 2, 3, 4], function(num) {
    return num % 2 === 0;
});

// Backbone is available globally
var MyModel = Backbone.Model.extend({
    defaults: {
        title: ''
    }
});
```

### React & ReactDOM

**Handles**: `react`, `react-dom`
**WordPress Version**: Since WP 5.0 (Gutenberg)
**Description**: React library (used by Gutenberg)

**IMPORTANT**: Use `@wordpress/element` instead of direct React imports!

```php
wp_enqueue_script('my-react-app', $url, array('wp-element'), '1.0.0', true);
```

**JavaScript Usage**:
```javascript
// Don't import React directly
// import React from 'react'; // ❌ WRONG

// Use WordPress element instead
const { createElement, Component } = wp.element; // ✓ CORRECT
```

**For JSX in build process**:
```javascript
// In your source files, you can import from @wordpress/element
import { createElement, Component } from '@wordpress/element';

// Or destructure from wp.element global
const { createElement } = wp.element;
```

### Lodash

**Handle**: `lodash`
**WordPress Version**: Since WP 5.0
**Description**: Lodash utility library (modern alternative to Underscore)

```php
wp_enqueue_script('my-script', $url, array('lodash'), '1.0.0', true);
```

**JavaScript Usage**:
```javascript
// Lodash is available globally
const chunked = _.chunk(['a', 'b', 'c', 'd'], 2);
// [['a', 'b'], ['c', 'd']]
```

### Moment.js

**Handle**: `moment`
**WordPress Version**: Since WP 4.9
**Description**: Date/time manipulation library

```php
wp_enqueue_script('my-calendar', $url, array('moment'), '1.0.0', true);
```

**JavaScript Usage**:
```javascript
// Available globally as moment
var now = moment();
var formatted = moment().format('MMMM Do YYYY, h:mm:ss a');
```

---

## WordPress Package Scripts (Gutenberg)

### Core Packages

These are modern ES6 modules used by the block editor:

| Handle | Description | Common Use |
|--------|-------------|------------|
| `wp-element` | React wrapper | **Use instead of React/ReactDOM** |
| `wp-components` | UI components | Buttons, panels, modals |
| `wp-blocks` | Block registration | Custom Gutenberg blocks |
| `wp-block-editor` | Block editor | Block editing interface |
| `wp-data` | State management | Data stores (like Redux) |
| `wp-api-fetch` | API requests | WordPress REST API calls |
| `wp-i18n` | Internationalization | Translations |
| `wp-hooks` | Filter/action system | WordPress hooks in JS |
| `wp-compose` | Higher-order components | Component utilities |
| `wp-date` | Date utilities | Format dates |
| `wp-dom-ready` | DOM ready | Like jQuery(document).ready() |
| `wp-url` | URL utilities | Parse and build URLs |

**Example - Custom Block (Already in theme)**:
```php
// In functions.php (line 375-381)
wp_enqueue_script(
    'campaignpress-editor-ux',
    get_template_directory_uri() . '/assets/js/editor-overrides.js',
    array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-plugins', 'wp-edit-post'), // ✓ Correct!
    CAMPAIGNPRESS_VERSION,
    true
);
```

---

## Media & Utilities

### MediaElement.js

**Handles**: `mediaelement`, `wp-mediaelement`
**Description**: HTML5 audio/video player

```php
wp_enqueue_script('wp-mediaelement');
wp_enqueue_style('wp-mediaelement');
```

### Masonry

**Handle**: `masonry`
**Description**: Grid layout library

```php
wp_enqueue_script('my-grid', $url, array('masonry'), '1.0.0', true);
```

**JavaScript Usage**:
```javascript
jQuery(function($) {
    $('.grid').masonry({
        itemSelector: '.grid-item',
        columnWidth: 200
    });
});
```

### imagesLoaded

**Handle**: `imagesloaded`
**Description**: Detect when images have loaded

```php
wp_enqueue_script('my-script', $url, array('imagesloaded'), '1.0.0', true);
```

### Hoverintent

**Handle**: `hoverIntent`
**Description**: Detects user intent for hover events

```php
wp_enqueue_script('my-menu', $url, array('hoverIntent'), '1.0.0', true);
```

---

## WordPress Admin Scripts

### Color Picker

**Handle**: `wp-color-picker`
**Description**: WordPress color picker (Iris)
**Currently Used**: Design Studio

```php
// Example usage in admin scripts
wp_enqueue_style('wp-color-picker');
wp_enqueue_script('my-script', $url, array('wp-color-picker'), '1.0.0', true);
```

**JavaScript Usage**:
```javascript
jQuery(document).ready(function($) {
    $('.color-picker').wpColorPicker();
});
```

### Code Editor

**Handle**: `code-editor`
**Description**: CodeMirror-based code editor

```php
wp_enqueue_code_editor(array('type' => 'text/css'));
wp_enqueue_script('my-editor', $url, array('code-editor'), '1.0.0', true);
```

### Media Upload

**Handle**: `media-upload`, `thickbox`
**Description**: WordPress media uploader (legacy)

**Modern Alternative**: Use `wp-media` instead!

```php
wp_enqueue_media(); // Enqueues modern media library
```

---

## Utility Scripts

### Clipboard.js

**Handle**: `clipboard`
**WordPress Version**: Since WP 5.2
**Description**: Copy to clipboard functionality

```php
wp_enqueue_script('my-script', $url, array('clipboard'), '1.0.0', true);
```

### Twemoji

**Handle**: `twemoji`
**Description**: Twitter emoji library

```php
wp_enqueue_script('twemoji');
```

---

## Chart & Data Visualization

**⚠️ WordPress does NOT include Chart.js or similar libraries by default**

### Current Implementation (Self-Hosted)

**File**: `includes/premium/analytics/analytics-init.php`

```php
// Bundled locally for WordPress.org compliance
$chartjs_url = apply_filters(
    'campaignpress_chartjs_url',
    get_template_directory_uri() . '/assets/vendor/chartjs/chart.umd.min.js'
);
wp_enqueue_script('chartjs', $chartjs_url, array(), '4.4.0', true);
```

### Self-hosting Chart.js (Active)
- ✅ Downloaded to `/assets/vendor/chartjs/`
- ✅ Enqueued from local file
- ✅ Better performance, GDPR compliance

---

## Map Libraries

**⚠️ WordPress does NOT include Leaflet or Google Maps by default**

### Current Implementation (Self-Hosted)

**File**: `includes/premium/analytics/analytics-init.php`

```php
// Bundled locally for WordPress.org compliance
$leaflet_js_url = apply_filters(
    'campaignpress_leaflet_js_url',
    get_template_directory_uri() . '/assets/vendor/leaflet/leaflet.js'
);
wp_enqueue_script('leaflet', $leaflet_js_url, array(), '1.9.4', true);
```

### Self-hosting Leaflet (Active)
- ✅ Downloaded to `/assets/vendor/leaflet/`
- ✅ Better performance, offline capability

2. **Use Google Maps API**
   - Requires API key
   - More features, but costs apply

---

## Best Practices

### 1. Always Check WordPress First

Before adding a new library:
```bash
# Search WordPress core for the library
grep -r "wp_register_script.*library-name" /path/to/wordpress/wp-includes/
```

### 2. Use Proper Dependencies

```php
// ✓ CORRECT - Declare dependencies
wp_enqueue_script('my-script', $url, array('jquery', 'underscore'), '1.0', true);

// ❌ WRONG - Missing dependencies
wp_enqueue_script('my-script', $url, array(), '1.0', true);
// Then manually loading jQuery in footer
```

### 3. Don't Deregister Core Scripts

```php
// ❌ VERY BAD - Never do this!
wp_deregister_script('jquery');
wp_register_script('jquery', 'https://code.jquery.com/jquery-3.6.0.min.js');

// ✓ CORRECT - Use WordPress version
wp_enqueue_script('my-script', $url, array('jquery'), '1.0', true);
```

### 4. Use WordPress Packages for React

```php
// ❌ WRONG - Bundling React separately
wp_enqueue_script('my-react-bundle', $url, array(), '1.0', true);
// This includes React 18.2.0 bundled in

// ✓ CORRECT - Use WordPress React
wp_enqueue_script('my-react-app', $url, array('wp-element', 'wp-components'), '1.0', true);
```

### 5. Minimize External CDN Usage

```php
// ❌ SUBOPTIMAL - External CDN
wp_enqueue_script('library', 'https://cdn.example.com/library.js');

// ✓ BETTER - Self-hosted
wp_enqueue_script('library', get_template_directory_uri() . '/assets/vendor/library/library.min.js');
```

**Exceptions**: Twitter widgets, Google Fonts (where CDN is the intended delivery method)

---

## Quick Reference Commands

### Check What Scripts Are Loaded

Add this to your theme for debugging:

```php
add_action('wp_print_scripts', function() {
    global $wp_scripts;
    echo '<pre>';
    foreach ($wp_scripts->queue as $handle) {
        echo $handle . "\n";
    }
    echo '</pre>';
});
```

### List All Registered Scripts

```php
add_action('wp_footer', function() {
    global $wp_scripts;
    echo '<pre>';
    print_r(array_keys($wp_scripts->registered));
    echo '</pre>';
});
```

---

## Current Theme Usage Summary

### ✅ Correctly Using WordPress Libraries

1. **jQuery** - Used throughout theme
2. **jQuery UI Sortable** - Field Operations, Integrations
3. **wp-color-picker** - Mega Menu, Design Studio
4. **wp-api** - Field Operations
5. **wp-element, wp-blocks, wp-components** - Block editor
6. **comment-reply** - Comments

### ⚠️ Can Be Optimized

1. **Chart.js** - Self-hosted in `/assets/vendor/chartjs/`
2. **Leaflet** - Self-hosted in `/assets/vendor/leaflet/`
3. **React/ReactDOM** - Currently bundled in Vite build, should use wp-element

### ✅ Not Needed (Not Using)

- Backbone.js
- Underscore.js (use Lodash if needed)
- Moment.js
- Masonry
- MediaElement.js

---

## Migration Checklist

When updating to use WordPress libraries:

- [ ] Check if WordPress provides the library
- [ ] Update `package.json` to mark as `devDependency` or remove
- [ ] Update Vite/Webpack config to externalize
- [ ] Update `wp_enqueue_script()` calls with proper dependencies
- [ ] Update JavaScript to use WordPress globals
- [ ] Test functionality
- [ ] Check for console errors
- [ ] Verify no duplicate library loading

---

## Resources

- [WordPress Default Scripts](https://developer.wordpress.org/reference/functions/wp_enqueue_script/#default-scripts-and-js-libraries-included-and-registered-by-wordpress)
- [WordPress JavaScript Packages](https://developer.wordpress.org/block-editor/reference-guides/packages/)
- [WordPress Script API](https://developer.wordpress.org/apis/scripts/)
- [Block Editor Handbook](https://developer.wordpress.org/block-editor/)

---

**Last Updated**: 2025-12-29
**Theme Version**: 2.0.0
**WordPress Compatibility**: 6.0+
