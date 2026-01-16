# CampaignPress Troubleshooting Guide

This guide helps you diagnose and fix common issues with CampaignPress.

## Table of Contents
1. [License Server Issues](#license-server-issues)
2. [Button Not Working Issues](#button-not-working-issues)
3. [Common Admin Issues](#common-admin-issues)
4. [Development Setup Issues](#development-setup-issues)
5. [Performance Issues](#performance-issues)

---

## License Server Issues

### Problem: "License server not configured" Error

**Symptoms:**
- When trying to activate a license, you see: "License server not configured. Please set up your license server or use the campaignpress_license_server_url filter."
- Premium features remain locked despite having a license key.

**Root Cause:**
The license server URL is still set to the default placeholder URL `https://api.campaignpress.com/v1/` and hasn't been configured for your environment.

**Solution 1: Configure License Server (Recommended)**

1. Edit `/includes/premium/config.php` (or create it if it doesn't exist)
2. Update the `CAMPAIGNPRESS_LICENSE_SERVER_URL` constant:

```php
define('CAMPAIGNPRESS_LICENSE_SERVER_URL', 'https://your-license-server.com/v1/');
```

**Important Notes:**
- The URL must end with a trailing slash (`/`)
- The URL should point to your license validation API endpoint
- Ensure your license server is accessible from your WordPress site

**Solution 2: Use Development Mode (For Testing Only)**

1. Add this to your `wp-config.php`:

```php
define('CAMPAIGNPRESS_DEV_MODE', true);
```

2. Enable mock license server (optional):
```php
define('CAMPAIGNPRESS_MOCK_LICENSE_SERVER', true);
```

**WARNING: Never enable `CAMPAIGNPRESS_DEV_MODE` on production sites!**

**Solution 3: Use Filter (Programmatic Override)**

Add this to your theme's `functions.php` or a custom plugin:

```php
add_filter('campaignpress_license_server_url', function($url) {
    return 'https://your-license-server.com/v1/';
});
```

### Problem: License Validation Fails with Connection Error

**Symptoms:**
- Error message: "Connection error: could not establish connection" or similar
- License key validation times out

**Root Causes:**
1. License server is down or unreachable
2. Firewall blocking the request
3. SSL/TLS certificate issues
4. Timeout too short for slow servers

**Solutions:**

1. **Check License Server Status**
   - Verify your license server is running
   - Check server logs for errors
   - Test with curl or Postman

2. **Configure Longer Timeout**
   Edit `/includes/premium/config.php`:
   ```php
   define('CAMPAIGNPRESS_LICENSE_TIMEOUT', 30); // Increase to 30 seconds
   ```

3. **Check Firewall Rules**
   - Ensure outgoing HTTPS connections are allowed
   - Allow connections to your license server's port

4. **Verify SSL Certificate**
   - Ensure your license server has a valid SSL certificate
   - Check that the certificate chain is complete

---

## Button Not Working Issues

### Problem: Campaign Creation Buttons Don't Work

**Symptoms:**
- Clicking "Add New Issue", "Add New Event", etc. does nothing
- No error message shown
- Page doesn't navigate to the new post form

**Root Causes:**
1. JavaScript errors preventing button clicks
2. Invalid or missing href attributes
3. Form submission handlers not working
4. AJAX requests failing silently

**Diagnostic Steps:**

1. **Open Browser Console**
   - Press F12 or right-click → Inspect
   - Go to Console tab
   - Click the button
   - Look for red error messages

2. **Check Button HTML**
   - Right-click the button → Inspect
   - Verify it has a valid `href` attribute
   - Example: `href="/wp-admin/post-new.php?post_type=cp_issue"`

3. **Monitor Network Requests**
   - Go to Network tab in DevTools
   - Click the button
   - Check if any requests are made
   - Check for failed requests (red status)

**Solutions:**

The theme includes automatic fixes in `/includes/admin-dashboard-fixes.php`. Ensure this file is loaded (it should be automatically loaded via `includes/core/loader.php`).

1. **Clear Browser Cache**
   - Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
   - Clear browser cache completely

2. **Disable Conflicting Plugins**
   - Temporarily disable all plugins except CampaignPress
   - Test if buttons work
   - Re-enable plugins one by one to identify conflicts

3. **Check for JavaScript Conflicts**
   - Look for other plugins loading jQuery or similar libraries
   - Check for console errors from other plugins
   - Disable custom JavaScript in themes/plugins

4. **Verify WordPress URLs**
   - Go to Settings → General
   - Check WordPress Address (URL) and Site Address (URL)
   - Ensure they match your site's actual URL

5. **Flush Rewrite Rules**
   - Go to Settings → Permalinks
   - Click "Save Changes" (no need to change anything)
   - This refreshes WordPress rewrite rules

### Problem: Forms Don't Submit

**Symptoms:**
- Clicking form submit buttons does nothing
- Form shows loading state but never completes
- No error message displayed

**Root Causes:**
1. AJAX endpoint not responding
2. Nonce verification failing
3. Form action attribute missing or invalid
4. JavaScript errors in form handler

**Solutions:**

1. **Check AJAX Endpoints**
   - Ensure `admin-ajax.php` is accessible
   - Test with: `https://yoursite.com/wp-admin/admin-ajax.php?action=test`
   - Check server error logs

2. **Verify Nonce**
   - Forms include a nonce for security
   - If nonce expires, form submission fails
   - Refresh the page to get a fresh nonce

3. **Check Form Action**
   - Forms should have `action` attribute pointing to a valid URL
   - AJAX forms may have `data-ajax="true"` attribute
   - Check browser console for JavaScript errors

---

## Common Admin Issues

### Problem: Admin Pages Not Loading

**Symptoms:**
- White screen when accessing admin pages
- Partially loaded admin interface
- "Fatal error" messages

**Root Causes:**
1. PHP memory limit exceeded
2. Plugin conflicts
3. Theme/plugin compatibility issues
4. Corrupted WordPress core files

**Solutions:**

1. **Increase PHP Memory Limit**
   Add to `wp-config.php`:
   ```php
   define('WP_MEMORY_LIMIT', '256M');
   ```

2. **Enable Debug Mode**
   Add to `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```
   Check `wp-content/debug.log` for errors

3. **Check Error Logs**
   - WordPress: `wp-content/debug.log`
   - Server error log: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
   - PHP error log: `/var/log/php_errors.log`

### Problem: Menu Items Missing

**Symptoms:**
- Campaign Data menu not visible
- Premium menu items not showing
- Custom post types missing from admin

**Root Causes:**
1. User permissions insufficient
2. Plugin not activated
3. CPTs not registered
4. Menu conflicts with other plugins

**Solutions:**

1. **Check User Capabilities**
   - Ensure user has `edit_posts` or `manage_options` capability
   - Try with admin account
   - Check role capabilities in User Management

2. **Verify Plugin Activation**
   - CampaignPress Premium features require premium license
   - Campaign Office Core plugin provides some features
   - Check Plugins page for deactivated plugins

3. **Flush Rewrite Rules**
   - Go to Settings → Permalinks
   - Click "Save Changes"

---

## Development Setup Issues

### Problem: Premium Features Not Working in Dev Mode

**Symptoms:**
- Enabled `CAMPAIGNPRESS_DEV_MODE` but premium features still locked
- License validation still attempts to connect to server

**Root Causes:**
1. `CAMPAIGNPRESS_DEV_MODE` not set before premium system loads
2. Constant defined in wrong file
3. File permissions preventing `wp-config.php` changes

**Solutions:**

1. **Add Dev Mode Constant Correctly**
   Edit `wp-config.php` (above `/* That's all, stop editing! */`):
   ```php
   define('CAMPAIGNPRESS_DEV_MODE', true);
   ```

2. **Verify Constant is Defined**
   Add to theme's `functions.php` temporarily:
   ```php
   if (defined('CAMPAIGNPRESS_DEV_MODE')) {
       error_log('CAMPAIGNPRESS_DEV_MODE is set to: ' . CAMPAIGNPRESS_DEV_MODE);
   } else {
       error_log('CAMPAIGNPRESS_DEV_MODE is NOT defined');
   }
   ```
   Check `wp-content/debug.log`

3. **Clear Cache**
   - Clear all caches (browser, server, CDN)
   - Deactivate/reactivate theme

---

## Performance Issues

### Problem: Slow Admin Dashboard

**Symptoms:**
- Dashboard takes long time to load
- Clicking buttons causes long delays
- High server CPU usage

**Root Causes:**
1. Too many plugins active
2. Large database
3. Heavy JavaScript/CSS loading
4. Slow server or database connection

**Solutions:**

1. **Optimize Database**
   - Use WP-Optimize or similar plugin
   - Clean up post revisions
   - Remove spam comments
   - Optimize database tables

2. **Disable Unnecessary Plugins**
   - Deactivate plugins not in use
   - Use plugin alternatives that are lighter

3. **Enable Caching**
   - Install a caching plugin (WP Rocket, W3 Total Cache, etc.)
   - Enable server-side caching (Redis, Memcached)
   - Use CDN for static assets

4. **Optimize Images**
   - Compress images before uploading
   - Use image optimization plugin
   - Serve images in next-gen formats (WebP)

---

## Getting Help

If you can't resolve your issue:

1. **Enable Debug Mode**
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

2. **Collect Information**
   - WordPress version
   - PHP version
   - Active plugins list
   - Theme version
   - Browser and version
   - Error messages from console and debug log

3. **Check Documentation**
   - Read `docs/DEVELOPER-GUIDE.md`
   - Read `docs/TECH_STACK.md`
   - Review `CLAUDE.md` for architecture details

4. **Report Issue**
   - Provide detailed steps to reproduce
   - Include error messages and screenshots
   - Share your system information

---

## Quick Fixes Checklist

- [ ] Clear browser cache and hard refresh
- [ ] Clear WordPress cache (if caching plugin installed)
- [ ] Deactivate conflicting plugins
- [ ] Flush rewrite rules (Settings → Permalinks → Save)
- [ ] Check browser console for JavaScript errors
- [ ] Check WordPress debug log for PHP errors
- [ ] Verify file permissions (755 for directories, 644 for files)
- [ ] Ensure PHP memory limit is at least 128M
- [ ] Update WordPress to latest version
- [ ] Update theme to latest version
- [ ] Update all plugins to latest versions

---

Last Updated: 2025-01-17
Version: 2.1.0
