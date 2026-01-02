# Remaining High-Priority Tasks

## 📋 Last Updated: January 2, 2025

This document provides actionable steps to complete remaining high-priority items from the code review.

---

## 1. Remove Console Statements from JavaScript Files 🔧

### Files Requiring Updates (7 files)

#### Task: Wrap all console.* calls with WP_DEBUG check

**Pattern to apply:**
```javascript
// Before:
console.log('Debug message');
console.error('Error:', error);
console.warn('Warning:', warning);
console.debug('Debug info:', info);

// After:
if (typeof console !== 'undefined' && WP_DEBUG) {
    console.log('Debug message');
    console.error('Error:', error);
    console.warn('Warning:', warning);
    console.debug('Debug info:', info);
}
```

**Or simply remove if used only for temporary debugging.**

---

### File 1: assets/js/admin-notices.js
**Action:** Search for `console.` and wrap with WP_DEBUG check

**Steps:**
1. Open file: `assets/js/admin-notices.js`
2. Search for: `console.log`, `console.error`, `console.warn`, `console.debug`, `console.info`
3. Apply pattern above to each occurrence
4. Test notice dismissal functionality
5. Commit changes

---

### File 2: assets/js/field-ops-admin.js
**Action:** Search for `console.` and wrap with WP_DEBUG check

**Steps:**
1. Open file: `assets/js/field-ops-admin.js`
2. Search for all console statements
3. Wrap with WP_DEBUG check
4. Test field operations dashboard
5. Verify export functionality still works
6. Commit changes

---

### File 3: assets/js/field-ops-offline.js
**Action:** Search for `console.` and wrap with WP_DEBUG check

**Steps:**
1. Open file: `assets/js/field-ops-offline.js`
2. Search for console statements
3. Wrap with WP_DEBUG check
4. Test offline functionality
5. Verify service worker caching
6. Commit changes

---

### File 4: assets/js/icons-browser.js
**Action:** Search for `console.` and wrap with WP_DEBUG check

**Steps:**
1. Open file: `assets/js/icons-browser.js`
2. Search for console statements
3. Wrap with WP_DEBUG check
4. Test icon browser
5. Verify icon copy functionality
6. Commit changes

---

### File 5: assets/js/service-worker.js
**Action:** Search for `console.` and wrap with WP_DEBUG check

**Note:** Service workers run in their own context, so `WP_DEBUG` may not be available. Consider:
- Removing console statements entirely
- Using a separate debug mode variable
- Only logging errors, not debug info

**Steps:**
1. Open file: `assets/js/service-worker.js`
2. Search for console statements
3. Remove debug logs, keep error logs wrapped in try-catch
4. Test service worker registration
5. Verify offline caching works
6. Commit changes

---

### File 6: assets/js/field-ops.js
**Action:** Search for `console.` and wrap with WP_DEBUG check

**Steps:**
1. Open file: `assets/js/field-ops.js`
2. Search for console statements
3. Wrap with WP_DEBUG check
4. Test phone banking functionality
5. Verify call recording works
6. Commit changes

---

### File 7: assets/js/premium-template-browser.js
**Action:** Search for `console.` and wrap with WP_DEBUG check

**Steps:**
1. Open file: `assets/js/premium-template-browser.js`
2. Search for console statements
3. Wrap with WP_DEBUG check
4. Test template browser
5. Verify template import functionality
6. Commit changes

---

## 2. Implement Bulk SMS Cron Handler 📱

### Issue
The async bulk SMS scheduling is implemented but the cron handler is missing.

### Location
`includes/premium/integrations/class-sms-integrations.php`

### Action Required

Add the following method to the `CampaignPress_SMS_Integrations` class:

```php
/**
 * Handle scheduled bulk SMS item
 *
 * Processes a single SMS message that was scheduled during bulk send.
 * This prevents blocking with sleep() and allows WordPress cron to handle rate limiting.
 *
 * @param string $phone Phone number to send to
 * @param string $message Message content
 * @param array $options Additional options
 * @param int $integration_id Integration ID
 * @return bool Success status
 * @since 2.0.0
 */
public function handle_scheduled_bulk_sms($phone, $message, $options, $integration_id) {
    // Get integration settings
    $integrations = get_option('cp_sms_integrations', array());
    if (!isset($integrations[$integration_id])) {
        campaignpress_integrations()->log_event('sms_bulk_item_failed', array(
            'phone' => $phone,
            'reason' => 'Integration not found'
        ));
        return false;
    }

    $integration = $integrations[$integration_id];

    // Send SMS
    $sent = $this->send_sms($phone, $message, array_merge($options, array(
        'integration_id' => $integration_id
    )));

    // Log completion
    campaignpress_integrations()->log_event('sms_bulk_item_sent', array(
        'phone' => $phone,
        'success' => $sent,
        'integration_id' => $integration_id,
        'platform' => $integration['platform']
    ));

    return $sent;
}
```

Then, add the hook registration in the constructor:

```php
/**
 * Constructor
 */
public function __construct() {
    // ... existing code ...

    // Add cron handler for bulk SMS
    add_action('cp_send_bulk_sms_item', array($this, 'handle_scheduled_bulk_sms'), 10, 4);

    // ... rest of constructor ...
}
```

### Testing Steps

1. Send a bulk SMS campaign to 5-10 test numbers
2. Verify that messages are scheduled (check `wp_schedule_event` logs)
3. Verify WordPress cron is running (use WP Crontrol plugin if needed)
4. Check that messages are actually sent
5. Verify logs show individual message sends
6. Test with 100+ recipients to ensure no timeout

### Troubleshooting

If bulk SMS doesn't work:

1. **Check cron is enabled:**
   ```php
   if (!wp_next_scheduled('cp_send_bulk_sms_item')) {
       // Cron not running
       wp_schedule_event(time(), 'every_minute', 'cp_send_bulk_sms_item');
   }
   ```

2. **Verify event is scheduled:**
   ```php
   $scheduled = wp_get_scheduled_event('cp_send_bulk_sms_item');
   error_log('Scheduled event: ' . print_r($scheduled, true));
   ```

3. **Manually trigger cron for testing:**
   ```bash
   wp cron event run --due-now
   ```

---

## 3. Optimize Bootstrap Usage 🎨

### Issue
Self-hosted Bootstrap 5.3.0 (232KB CSS + 80KB JS = 312KB) is included in every theme installation.

### Options

#### Option A: Use CDN (Recommended for Performance)
```php
// In functions.php, replace lines 246-261 with:
function campaignpress_bootstrap_cdn() {
    // Only load on front-end if needed
    if (is_admin()) {
        return;
    }

    // Check if Bootstrap is actually needed on this page
    $needs_bootstrap = apply_filters('campaignpress_needs_bootstrap', false);
    if (!$needs_bootstrap) {
        return;
    }

    // Use CDN for better caching
    wp_enqueue_style(
        'bootstrap-cdn',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
        array(),
        '5.3.0'
    );

    wp_enqueue_script(
        'bootstrap-bundle-cdn',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
        array(),
        '5.3.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'campaignpress_bootstrap_cdn', 20);

// Filter to conditionally load Bootstrap on specific pages
function campaignpress_bootstrap_conditional_load($needs) {
    // Only load Bootstrap on pages that use it
    if (is_page('volunteer') || is_page('donate') || is_page('contact')) {
        return true;
    }
    return false;
}
add_filter('campaignpress_needs_bootstrap', 'campaignpress_bootstrap_conditional_load');
```

**Benefits:**
- Reduced theme size (312KB smaller)
- CDN caching for all sites using Bootstrap
- Automatic updates
- Conditionally load only on pages that need it

#### Option B: Purge Unused CSS
```bash
# Install PurgeCSS
npm install -D purgecss

# Run PurgeCSS to remove unused Bootstrap classes
npx purgecss --css assets/vendor/bootstrap/bootstrap.min.css \
             --content templates/**/*.html \
             --output assets/css/bootstrap.min.css
```

**Benefits:**
- Keep self-hosted (for offline capability)
- Smaller CSS bundle (typically 30-50% reduction)
- Maintain complete control over Bootstrap version

#### Option C: Replace with Tailwind
Theme already uses Tailwind CSS, so Bootstrap may be redundant.

**Action:** Audit which Bootstrap classes are used and replace with Tailwind equivalents.

**Common replacements:**
- `container` → `max-w-7xl mx-auto px-4`
- `row` → `flex flex-wrap`
- `col-md-6` → `md:w-1/2`
- `btn btn-primary` → `bg-blue-600 text-white px-4 py-2 rounded`

**Benefits:**
- Remove 312KB completely
- Consistent styling system
- Smaller bundle size (Tailwind already included)

#### Option D: Keep as-is
**When to choose:**
- Offline capability is critical
- Heavy Bootstrap usage throughout theme
- Custom Bootstrap modifications needed
- CDN not acceptable for compliance reasons

---

## 4. Add Object Caching 💾

### Priority Queries to Cache

#### 1. Volunteer Dashboard Data
```php
// In includes/free/volunteer-portal.php
public function get_volunteer_stats($volunteer_id) {
    $cache_key = 'cp_volunteer_stats_' . $volunteer_id;

    $stats = wp_cache_get($cache_key, 'campaignpress');
    if (false !== $stats) {
        return $stats;
    }

    // ... existing query code ...

    wp_cache_set($cache_key, $stats, 'campaignpress', HOUR_IN_SECONDS);
    return $stats;
}
```

#### 2. Event Lists
```php
// In includes/free/event-calendar-enhancements.php
public function get_upcoming_events($limit = 10) {
    $cache_key = 'cp_upcoming_events_' . $limit;

    $events = wp_cache_get($cache_key, 'campaignpress');
    if (false !== $events) {
        return $events;
    }

    // ... existing query code ...

    wp_cache_set($cache_key, $events, 'campaignpress', 15 * MINUTE_IN_SECONDS);
    return $events;
}
```

#### 3. Design System Settings
```php
// In includes/free/global-styles-enhanced.php
public function get_global_styles() {
    $cache_key = 'cp_global_styles';

    $styles = wp_cache_get($cache_key, 'campaignpress');
    if (false !== $styles) {
        return $styles;
    }

    $styles = array(
        'typography' => get_option('cp_global_typography', array()),
        'colors' => get_option('cp_global_colors', array()),
        'spacing' => get_option('cp_global_spacing', array()),
    );

    wp_cache_set($cache_key, $styles, 'campaignpress', HOUR_IN_SECONDS);
    return $styles;
}
```

### Cache Invalidation Hooks

```php
// Clear cache when settings are updated
function campaignpress_clear_style_cache() {
    wp_cache_delete('cp_global_styles', 'campaignpress');
}
add_action('update_option_cp_global_typography', 'campaignpress_clear_style_cache');
add_action('update_option_cp_global_colors', 'campaignpress_clear_style_cache');
add_action('update_option_cp_global_spacing', 'campaignpress_clear_style_cache');

// Clear volunteer cache when hours are logged
function campaignpress_clear_volunteer_cache($volunteer_id) {
    wp_cache_delete('cp_volunteer_stats_' . $volunteer_id, 'campaignpress');
}
add_action('cp_volunteer_hours_logged', 'campaignpress_clear_volunteer_cache');
```

---

## 5. Add Image Lazy Loading 🖼️

### Implementation

```php
// In functions.php, add after line 297
/**
 * Add lazy loading to images
 *
 * Loads images lazily to improve initial page load time.
 * Skips lazy loading for above-the-fold images.
 */
function campaignpress_add_lazy_loading($attr, $attachment, $size) {
    // Skip lazy loading for above-the-fold images
    $lazy_skip_sizes = apply_filters('campaignpress_lazy_skip_sizes', array(
        'hero',
        'full',
        'campaignpress-candidate-headshot',
        'campaignpress-event-hero'
    ));

    if (in_array($size, $lazy_skip_sizes, true)) {
        return $attr;
    }

    // Skip if in admin
    if (is_admin()) {
        return $attr;
    }

    // Skip if feed or AMP
    if (is_feed() || is_amp()) {
        return $attr;
    }

    // Add lazy loading attribute
    $attr['loading'] = 'lazy';

    // Add decoding attribute for better performance
    $attr['decoding'] = 'async';

    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'campaignpress_add_lazy_loading', 10, 3);
```

### Testing

1. Open a page with multiple images
2. Open Chrome DevTools → Network tab
3. Scroll down the page
4. Verify that images load as you scroll (not all at once)
5. Check that above-the-fold images (hero, thumbnails) load immediately

---

## ✅ Completion Checklist

### Console Statements
- [ ] assets/js/admin-notices.js
- [ ] assets/js/field-ops-admin.js
- [ ] assets/js/field-ops-offline.js
- [ ] assets/js/icons-browser.js
- [ ] assets/js/service-worker.js
- [ ] assets/js/field-ops.js
- [ ] assets/js/premium-template-browser.js

### Bulk SMS
- [ ] Cron handler implemented
- [ ] Hook registered
- [ ] Tested with 5-10 messages
- [ ] Tested with 100+ messages
- [ ] Verified no PHP timeouts
- [ ] Logs showing individual sends

### Bootstrap
- [ ] Optimization strategy chosen
- [ ] Implementation completed
- [ ] Tested across all pages
- [ ] Performance improved (Lighthouse score check)

### Object Caching
- [ ] Volunteer stats cached
- [ ] Event lists cached
- [ ] Design settings cached
- [ ] Cache invalidation hooks added
- [ ] Tested with cache enabled/disabled

### Lazy Loading
- [ ] Filter implemented
- [ ] Above-the-fold images excluded
- [ ] Tested lazy loading behavior
- [ ] Verified performance improvement

---

## 📞 Need Help?

Refer to:
- Full Code Review: `CODE_REVIEW_2025-01-02.md`
- Fixes Applied: `CRITICAL_FIXES_APPLIED.md`
- Summary: `CODE_REVIEW_SUMMARY.md`

---

**Status:** High-Priority Tasks Documented ✅
**Next:** Complete Console Statement Cleanup and Bulk SMS Cron Handler
