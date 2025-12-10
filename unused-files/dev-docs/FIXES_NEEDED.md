# CampaignPress Bug Fixes Required

## Issue #1: Hero Section Styling Broken

### Root Cause
style.css uses undefined CSS custom properties (`--cp-*`) while design-system-wp69.css uses WordPress 6.9 variables (`--wp--preset--*`).

### Fix Options

**Option A (Recommended): Define CSS variable mappings**
Add to style.css or design-system-wp69.css:
```css
:root {
  /* Map old variables to WordPress 6.9 variables */
  --cp-primary: var(--wp--preset--color--primary);
  --cp-secondary: var(--wp--preset--color--accent);
  --cp-spacing-sm: var(--wp--preset--spacing--4);
  --cp-spacing-md: var(--wp--preset--spacing--6);
  --cp-spacing-lg: var(--wp--preset--spacing--10);
  --cp-spacing-xl: var(--wp--preset--spacing--16);
  --cp-border-radius: var(--cp-radius-md);
  --cp-transition: var(--cp-transition-base);
  --cp-background: var(--wp--preset--color--neutral-50);
  --cp-border: var(--wp--preset--color--neutral-200);
  --cp-text: var(--wp--preset--color--neutral-900);
}
```

**Option B: Update style.css to use WordPress 6.9 variables**
Find/replace in style.css:
- `var(--cp-primary)` → `var(--wp--preset--color--primary)`
- `var(--cp-spacing-md)` → `var(--wp--preset--spacing--6)`
- etc.

**Option C: Remove duplicate hero styles from style.css**
Since design-system-wp69.css has complete hero styles, remove lines 9-150 from style.css.

---

## Issue #2: Premium Features Not Loading

### Root Cause
Premium activation requires either:
1. Valid license key in database, OR
2. `CAMPAIGNPRESS_DEV_MODE` constant set to true in wp-config.php

Neither condition is met.

### Fix Options

**Option A (Development): Enable dev mode**
Add to wp-config.php (before "That's all, stop editing!"):
```php
define('CAMPAIGNPRESS_DEV_MODE', true);
```

**Option B (Production): Activate license**
1. Go to WordPress admin → CampaignPress Pro → License
2. Enter valid license key and email
3. Click "Activate License"

**Option C (Development): Use test license key**
The code has a built-in test key: `TEST-KEY-123`
1. Go to WordPress admin → CampaignPress Pro → License
2. Enter license key: `TEST-KEY-123`
3. Enter any email address
4. Click "Activate License"

---

## Files to Modify

### For Hero Section Fix (Option A):
- `style.css` (add :root block at top)

### For Premium Features Fix (Option A):
- `wp-config.php` (add CAMPAIGNPRESS_DEV_MODE constant)

---

## Testing Steps

### Test Hero Section:
1. Apply CSS variable mapping fix
2. Clear browser cache
3. Refresh front page
4. Verify hero section displays with:
   - Correct colors (primary blue)
   - Proper spacing between elements
   - Smooth animations on load
   - Working hover effects on buttons

### Test Premium Features:
1. Enable dev mode or activate license
2. Go to WordPress admin → CampaignPress Pro
3. Verify menu shows: License, Features, System Status
4. Go to Features page
5. Enable CRM feature
6. Verify CRM appears in admin menu
7. Check that database tables are created

---

## Recommended Action

**Start with Premium Features fix first:**
1. Add `define('CAMPAIGNPRESS_DEV_MODE', true);` to wp-config.php
2. Verify premium features become accessible

**Then fix Hero Section:**
1. Add CSS variable mapping to style.css (Option A)
2. Test front page hero section
3. Verify all styling works correctly
