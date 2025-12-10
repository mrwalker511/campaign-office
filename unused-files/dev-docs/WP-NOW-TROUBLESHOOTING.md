# WP Now Troubleshooting Guide for CampaignPress

## Issue: Site tries to load but doesn't load (white screen/infinite loading)

### Quick Fixes (Try these in order):

## Fix #1: Restart WP Now

1. **Stop WP Now:**
   - Open VS Code Command Palette (`Ctrl+Shift+P`)
   - Type: `WP Now: Stop WordPress Server`
   - Press Enter

2. **Start WP Now:**
   - Command Palette (`Ctrl+Shift+P`)
   - Type: `WP Now: Start WordPress Server`
   - Press Enter

3. **Open in browser:**
   - Command Palette (`Ctrl+Shift+P`)
   - Type: `WP Now: Open WordPress in Browser`
   - Or go to `http://localhost:8881` (or whatever port WP Now shows)

---

## Fix #2: Check WP Now Output Panel

1. **Open Output panel:**
   - View → Output (or `Ctrl+Shift+U`)
   - Select "WP Now" from the dropdown

2. **Look for errors:**
   - PHP errors
   - Port conflicts (8881 already in use)
   - File permission issues

3. **Common errors and fixes:**

   **Error: "Port 8881 is already in use"**
   - Solution: Stop other WP Now instances or change port in settings

   **Error: "PHP Fatal error"**
   - Solution: Check the specific error and fix the PHP file mentioned

   **Error: "Cannot connect to database"**
   - Solution: Restart WP Now (it should auto-create the database)

---

## Fix #3: Revert CSS Changes (If needed)

The CSS variable changes might be causing issues. Let's revert them:

```bash
# In terminal (from theme directory)
git restore style.css
```

Or manually remove the :root block I added (lines 7-24 in style.css).

---

## Fix #4: Check theme.json for errors

WP Now is sensitive to theme.json errors:

1. Open `theme.json` in VS Code
2. Check for JSON syntax errors (VS Code will underline them)
3. If there are errors, fix them or restore from git:
   ```bash
   git restore theme.json
   ```

---

## Fix #5: Enable WP_DEBUG

WP Now creates a temporary wp-config.php. To see actual errors:

1. **Open WP Now settings:**
   - File → Preferences → Settings
   - Search for "WP Now"

2. **Enable debug mode:**
   - Check "WP Now: Debug"

3. **Restart WP Now:**
   - Stop and start the server again

4. **Check errors:**
   - Look in the Output panel for specific PHP errors

---

## Fix #6: Check for Circular CSS Variable References

The CSS variables I added might have circular references:

**Problem in style.css:**
```css
--cp-border-radius: var(--cp-radius-md);  /* Refers to --cp-radius-md */
--cp-border-radius-lg: var(--cp-radius-lg);  /* Refers to --cp-radius-lg */
```

But `--cp-radius-md` and `--cp-radius-lg` are defined in design-system-wp69.css, which loads AFTER style.css!

**Fix:** Update style.css lines 18-19 to:
```css
--cp-border-radius: 0.5rem;
--cp-border-radius-lg: 0.75rem;
```

---

## Fix #7: Clear WP Now Cache

1. **Stop WP Now**

2. **Delete WP Now data directory:**
   - Windows: `%LOCALAPPDATA%\Automattic\studio\sites\`
   - Look for a folder matching your theme name
   - Delete it

3. **Restart WP Now** - it will recreate everything fresh

---

## Fix #8: Check Browser Console

1. Open browser Developer Tools (F12)
2. Go to Console tab
3. Look for JavaScript errors
4. Look for CSS errors

Common issues:
- **Circular dependency in CSS variables**
- **JavaScript infinite loop**
- **Failed to load CSS file (404 error)**

---

## Fix #9: Test with Twenty Twenty-Three

To confirm it's a theme issue:

1. Create a new folder on your Desktop: `test-theme`
2. Put any basic WordPress theme there
3. Open that folder in VS Code with WP Now
4. If it works, the problem is in CampaignPress theme
5. If it doesn't work, WP Now installation has issues

---

## Fix #10: Reinstall WP Now

If nothing works:

1. **Uninstall WP Now:**
   - Extensions panel in VS Code
   - Find "WP Now" or "Studio"
   - Click Uninstall

2. **Restart VS Code**

3. **Reinstall WP Now:**
   - Extensions panel
   - Search "WP Now"
   - Install the official Automattic extension

4. **Restart VS Code**

5. **Try again**

---

## Most Likely Fix for Your Issue

Based on the CSS changes I made, try this:

### Option A: Revert my changes
```bash
cd "C:\Users\Matt Walker\Desktop\wp\campaign-office"
git restore style.css
```

### Option B: Fix the circular reference
Edit `style.css` and change lines 18-19:

**FROM:**
```css
--cp-border-radius: var(--cp-radius-md);
--cp-border-radius-lg: var(--cp-radius-lg);
```

**TO:**
```css
--cp-border-radius: 0.5rem;
--cp-border-radius-lg: 0.75rem;
```

Then restart WP Now.

---

## To Enable Premium Features with WP Now

WP Now creates a temporary wp-config.php that you can't easily edit. Instead:

**Option 1: Use the test license key**
- Once the site loads, go to WordPress admin
- Navigate to CampaignPress Pro → License
- Enter: `TEST-KEY-123`
- Activate

**Option 2: Add to theme's functions.php temporarily**
Add at the top of functions.php (line 12, after `exit;`):
```php
// Temporary: Enable premium features in WP Now
define('CAMPAIGNPRESS_DEV_MODE', true);
```

---

## Next Steps

1. Try Fix #1 (restart WP Now) first
2. If that doesn't work, try Fix #6 (fix circular CSS reference)
3. Check WP Now Output panel for specific errors
4. Report back what error messages you see

Let me know what you find!
