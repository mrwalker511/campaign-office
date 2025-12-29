# External Libraries Optimization Guide

This document provides step-by-step instructions for optimizing the external libraries currently loaded from CDNs in the Campaign Office theme.

## Current External Dependencies

The theme currently loads these libraries from external CDNs:

1. **Chart.js** - Analytics charts and visualizations
2. **Leaflet** - Interactive maps
3. **Twitter Widgets** - Social media integration
4. **Bootstrap 5** - Already self-hosted ✓

---

## 1. Chart.js Optimization

**Current Status**: Loaded from CDN
**File**: `includes/premium/analytics/analytics-init.php` (Line 115-121)
**Priority**: HIGH

### Current Implementation

```php
wp_enqueue_script(
    'chartjs',
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
    array(),
    '4.4.0',
    true
);
```

### Option A: Self-Host Chart.js (Recommended)

**Benefits**:
- Faster load times (no external DNS lookup)
- Works offline/in local development
- GDPR compliance (no data sent to CDN)
- Version control

**Steps**:

1. **Download Chart.js**:
   ```bash
   cd /home/user/campaign-office/assets/vendor
   mkdir -p chart.js
   cd chart.js
   wget https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js
   wget https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js # Unminified for debugging
   ```

2. **Update enqueue in `includes/premium/analytics/analytics-init.php`**:
   ```php
   // Replace lines 115-121 with:
   wp_enqueue_script(
       'chartjs',
       get_template_directory_uri() . '/assets/vendor/chart.js/chart.umd.min.js',
       array(),
       '4.4.0',
       true
   );
   ```

3. **Add to version control**:
   ```bash
   git add assets/vendor/chart.js/
   git commit -m "feat: Self-host Chart.js for better performance"
   ```

### Option B: Use WordPress Helper (Advanced)

Use the Script_Manager class for automatic CDN fallback:

```php
use CampaignOffice\Core\Script_Manager;

Script_Manager::enqueue_selfhosted_or_cdn(
    'chartjs',
    'assets/vendor/chart.js/chart.umd.min.js',
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
    array(),
    '4.4.0'
);
```

This will use the local version if it exists, otherwise fall back to CDN with a debug warning.

---

## 2. Leaflet Maps Optimization

**Current Status**: Loaded from CDN
**File**: `includes/premium/analytics/analytics-init.php` (Line 124-130)
**Priority**: HIGH

### Current Implementation

```php
wp_enqueue_script(
    'leaflet',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
    array(),
    '1.9.4',
    true
);
```

### Option A: Self-Host Leaflet (Recommended)

**Steps**:

1. **Download Leaflet**:
   ```bash
   cd /home/user/campaign-office/assets/vendor
   mkdir -p leaflet
   cd leaflet

   # Download JS
   wget https://unpkg.com/leaflet@1.9.4/dist/leaflet.js
   wget https://unpkg.com/leaflet@1.9.4/dist/leaflet.min.js

   # Download CSS
   wget https://unpkg.com/leaflet@1.9.4/dist/leaflet.css

   # Download images (required by Leaflet CSS)
   mkdir -p images
   cd images
   wget https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png
   wget https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png
   wget https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png
   ```

2. **Update enqueue in `includes/premium/analytics/analytics-init.php`**:
   ```php
   // Replace lines 124-130 with:
   wp_enqueue_style(
       'leaflet',
       get_template_directory_uri() . '/assets/vendor/leaflet/leaflet.css',
       array(),
       '1.9.4'
   );

   wp_enqueue_script(
       'leaflet',
       get_template_directory_uri() . '/assets/vendor/leaflet/leaflet.min.js',
       array(),
       '1.9.4',
       true
   );
   ```

3. **Update CSS image paths** (if needed):

   The Leaflet CSS references images using relative paths. If you have issues, create a custom override:

   ```php
   wp_add_inline_style('leaflet', '
       .leaflet-default-icon-path {
           background-image: url(' . get_template_directory_uri() . '/assets/vendor/leaflet/images/marker-icon.png);
       }
   ');
   ```

### Option B: Use npm Package (Alternative)

If you prefer using npm:

```bash
npm install leaflet@1.9.4 --save-dev
```

Then copy from `node_modules` to vendor directory during build:

```javascript
// Add to build/vite.config.js or a custom build script
import { copyFileSync } from 'fs';

copyFileSync(
    'node_modules/leaflet/dist/leaflet.min.js',
    'assets/vendor/leaflet/leaflet.min.js'
);
```

---

## 3. Twitter/X Widgets

**Current Status**: External CDN (required)
**File**: `includes/free/social-media-feeds.php` (Line 354)
**Priority**: LOW (Keep as-is)

### Current Implementation

```php
wp_enqueue_script(
    'twitter-widgets',
    'https://platform.twitter.com/widgets.js',
    array(),
    null,
    true
);
```

### Recommendation: Keep External

**Why?**
- Twitter widgets MUST be loaded from Twitter's CDN to function
- Self-hosting is not supported by Twitter
- Updates automatically when Twitter changes their API

**Optional Optimization**: Add async loading

```php
add_filter('script_loader_tag', function($tag, $handle) {
    if ('twitter-widgets' !== $handle) {
        return $tag;
    }

    return str_replace(' src', ' async src', $tag);
}, 10, 2);
```

---

## 4. Bootstrap 5

**Current Status**: Self-hosted ✓
**File**: `functions.php` (Line 137-143)

Already optimized! No changes needed.

```php
wp_enqueue_script(
    'bootstrap-bundle',
    get_template_directory_uri() . '/assets/vendor/bootstrap/bootstrap.bundle.min.js',
    array(),
    '5.3.0',
    true
);
```

---

## Implementation Checklist

Use this checklist to track your optimization progress:

### Chart.js
- [ ] Download Chart.js files to `assets/vendor/chart.js/`
- [ ] Update `includes/premium/analytics/analytics-init.php` enqueue
- [ ] Test analytics dashboard charts
- [ ] Verify no console errors
- [ ] Test in different browsers
- [ ] Add to git and commit

### Leaflet
- [ ] Download Leaflet JS to `assets/vendor/leaflet/`
- [ ] Download Leaflet CSS to `assets/vendor/leaflet/`
- [ ] Download Leaflet images to `assets/vendor/leaflet/images/`
- [ ] Update `includes/premium/analytics/analytics-init.php` enqueue
- [ ] Test map functionality
- [ ] Verify markers display correctly
- [ ] Test in different browsers
- [ ] Add to git and commit

### Twitter Widgets
- [ ] Add async loading (optional)
- [ ] Test Twitter embeds
- [ ] Verify functionality

---

## Testing After Changes

### 1. Clear All Caches

```bash
# WordPress cache (if using caching plugin)
wp cache flush

# Browser cache
# Use Ctrl+Shift+R or Cmd+Shift+R

# CDN cache (if applicable)
# Purge via CDN dashboard
```

### 2. Test Analytics Dashboard

1. Navigate to `/wp-admin/admin.php?page=campaign-office-analytics`
2. Verify charts load correctly
3. Check browser console for errors
4. Test chart interactions (hover, click, etc.)

### 3. Test Map Functionality

1. Navigate to page with map
2. Verify map loads and displays
3. Test markers, popups, zoom
4. Check for broken images

### 4. Performance Testing

Before optimization:
```bash
# Using Lighthouse
npm run lighthouse
```

After optimization:
```bash
# Compare results
npm run lighthouse
```

Expected improvements:
- Reduced external requests: 2 fewer
- Faster First Contentful Paint: ~100-200ms improvement
- Better cache hit rate
- Improved Lighthouse score

---

## File Size Comparison

| Library | CDN Size | Self-Hosted Size | Savings |
|---------|----------|------------------|---------|
| Chart.js 4.4.0 (min) | ~250KB | ~250KB | 0 KB (but faster load) |
| Leaflet 1.9.4 (min) | ~150KB | ~150KB | 0 KB (but faster load) |
| **Total** | ~400KB | ~400KB | **DNS lookup time saved** |

**Note**: The file sizes are the same, but you save:
- DNS lookup time (~20-50ms per domain)
- SSL negotiation time (~50-100ms per domain)
- CDN routing time (~20-50ms)
- **Total potential savings: 90-200ms per page load**

---

## Rollback Plan

If you encounter issues after self-hosting:

### Quick Rollback

1. **Revert changes in git**:
   ```bash
   git checkout HEAD -- includes/premium/analytics/analytics-init.php
   ```

2. **Clear caches**:
   ```bash
   wp cache flush
   ```

3. **Test** - Back to CDN versions

### Keep Files for Future

Even if you rollback, keep the self-hosted files in place for future use or testing.

---

## Maintenance

### Updating Library Versions

When Chart.js or Leaflet releases updates:

1. **Check release notes**:
   - Chart.js: https://github.com/chartjs/Chart.js/releases
   - Leaflet: https://github.com/Leaflet/Leaflet/releases

2. **Download new version**:
   ```bash
   cd /home/user/campaign-office/assets/vendor/chart.js
   wget https://cdn.jsdelivr.net/npm/chart.js@NEW_VERSION/dist/chart.umd.min.js
   ```

3. **Update version number** in enqueue:
   ```php
   wp_enqueue_script('chartjs', $url, array(), 'NEW_VERSION', true);
   ```

4. **Test thoroughly**

5. **Commit changes**:
   ```bash
   git add assets/vendor/chart.js/
   git commit -m "chore: Update Chart.js to vNEW_VERSION"
   ```

---

## Additional Optimizations

### 1. Conditional Loading

Only load libraries on pages that need them:

```php
// Only load Chart.js on analytics page
if (isset($_GET['page']) && $_GET['page'] === 'campaign-office-analytics') {
    wp_enqueue_script('chartjs', ...);
}
```

### 2. Defer/Async Loading

For non-critical scripts:

```php
add_filter('script_loader_tag', function($tag, $handle) {
    if ('chartjs' !== $handle) {
        return $tag;
    }

    return str_replace(' src', ' defer src', $tag);
}, 10, 2);
```

### 3. Combine Scripts

Consider combining small custom scripts to reduce HTTP requests:

```bash
# Create combined file
cat analytics.js field-ops.js > combined-admin.js
uglifyjs combined-admin.js -o combined-admin.min.js
```

---

## Resources

- **Chart.js Documentation**: https://www.chartjs.org/docs/latest/
- **Leaflet Documentation**: https://leafletjs.com/reference.html
- **WordPress Script API**: https://developer.wordpress.org/reference/functions/wp_enqueue_script/
- **Web Performance**: https://web.dev/performance/

---

**Last Updated**: 2025-12-29
**Campaign Office Version**: 2.0.0
**Optimization Status**: In Progress
