# Enable CampaignPress Development Mode

## Location of wp-config.php

The `wp-config.php` file is located in your **WordPress root directory**, not in the theme folder.

Common WordPress installation locations:
- **Local by Flywheel:** `C:\Users\[Username]\Local Sites\[site-name]\app\public\wp-config.php`
- **XAMPP:** `C:\xampp\htdocs\[site-name]\wp-config.php`
- **WAMP:** `C:\wamp\www\[site-name]\wp-config.php`
- **Server/Hosting:** Usually `/public_html/wp-config.php` or `/www/wp-config.php`

## Steps to Enable Development Mode

### 1. Locate your wp-config.php file
Navigate to your WordPress root directory (one level above `wp-content/themes/campaign-office`).

### 2. Open wp-config.php in a text editor
- Use a code editor like VS Code, Notepad++, or Sublime Text
- **DO NOT use Microsoft Word or other word processors**

### 3. Find this line:
```php
/* That's all, stop editing! Happy publishing. */
```

### 4. Add this code ABOVE that line:
```php
/**
 * CampaignPress Development Mode
 * Enables all premium features without license validation
 */
define('CAMPAIGNPRESS_DEV_MODE', true);

/* That's all, stop editing! Happy publishing. */
```

### 5. Save the file

### 6. Verify activation
1. Go to your WordPress admin panel
2. Navigate to **CampaignPress Pro** in the left menu
3. You should now see:
   - **License** page showing "Development mode - Enterprise tier"
   - **Features** submenu
   - **System Status** submenu
4. Go to **Features** page
5. Enable the features you want to use (CRM, Field Operations, etc.)

## Example: Complete wp-config.php Section

```php
// ... existing database and authentication settings above ...

/**
 * For developers: WordPress debugging mode.
 */
define('WP_DEBUG', false);

/**
 * CampaignPress Development Mode
 * Enables all premium features without license validation
 */
define('CAMPAIGNPRESS_DEV_MODE', true);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-includes/load.php';
```

## Troubleshooting

### Issue: Can't find wp-config.php
- Make sure you're looking in the WordPress ROOT directory, not the theme directory
- The file is at the same level as `wp-admin`, `wp-content`, and `wp-includes` folders

### Issue: Changes not taking effect
1. Clear your browser cache
2. In WordPress admin, clear any caching plugins
3. Verify the constant was added correctly (no typos)
4. Refresh your WordPress admin page

### Issue: White screen / site broken
- You likely have a PHP syntax error
- Check that you added the code ABOVE the "stop editing" comment
- Make sure you didn't accidentally delete any existing code
- Restore from backup if needed

## Alternative: Using WP_DEBUG

If you prefer, you can also enable debugging mode which CampaignPress will detect:

```php
define('WP_DEBUG', true);
```

This will activate premium features AND enable WordPress debugging output.

## Security Note

⚠️ **Important:** Development mode bypasses license validation and should ONLY be used on:
- Local development environments
- Staging sites
- Testing environments

**DO NOT enable dev mode on production/live sites without a valid license.**

## What Development Mode Enables

When `CAMPAIGNPRESS_DEV_MODE` is enabled:
- ✅ All premium features unlocked (Enterprise tier)
- ✅ No license key required
- ✅ No expiration date
- ✅ All modules available:
  - Advanced CRM System
  - Field Operations
  - FEC Compliance Tools
  - Advanced Analytics
  - REST API Access
  - Developer Console
  - Integrations (Email/SMS)
- ✅ Relaxed Content Security Policy for local development
- ✅ Event logging enabled

---

**Need help?** Check the FIXES_NEEDED.md file for additional troubleshooting steps.
