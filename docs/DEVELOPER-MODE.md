# CampaignPress Developer Mode

**Version:** 2.0.0
**Last Updated:** December 6, 2025

## Overview

Developer Mode is a built-in testing feature that allows theme developers to bypass license validation and test all premium features without a valid license key.

## Enabling Developer Mode

### Option 1: Using CAMPAIGNPRESS_DEV_MODE Constant (Recommended)

Add this line to your `wp-config.php` file:

```php
define('CAMPAIGNPRESS_DEV_MODE', true);
```

**Best Practice:** Place this constant right before the line that says `/* That's all, stop editing! */`

### Option 2: Using WP_DEBUG (Fallback)

If `CAMPAIGNPRESS_DEV_MODE` is not defined, the system will automatically use `WP_DEBUG` as a fallback:

```php
define('WP_DEBUG', true);
```

## What Developer Mode Does

When developer mode is enabled, the premium system will:

✅ **Unlock All Premium Features**
- All premium modules are automatically enabled
- No license key required
- Enterprise tier access (highest level)

✅ **Bypass License Checks**
- `is_premium_active()` always returns `true`
- `is_license_expired()` always returns `false`
- `get_license_data()` returns fake enterprise license

✅ **Enable All Modules**
- CRM System
- Field Operations
- Compliance Tools
- Analytics Dashboard
- REST API Access
- Developer Console
- Auto-updates
- Priority Support (UI only)
- White Label (UI only)

✅ **Show Developer License in Admin**
- License Type: Enterprise
- Status: Active
- Expiry: 10 years from current date
- License Key: DEV-MODE-[random]

## Testing Premium Features

Once developer mode is enabled:

1. **Navigate to Admin Menu**
   - `CampaignPress Pro` menu will appear in WordPress admin
   - All submenu items will be accessible

2. **Access Premium Features**
   - License page shows fake enterprise license
   - Features page shows all features enabled
   - System Status page displays full system info
   - Upgrade page is hidden (since premium is "active")

3. **Use Premium Modules**
   - CRM: `CampaignPress Pro → CRM` (if menu exists)
   - Field Operations: Tools for canvassing/phone banking
   - Analytics: Performance dashboards
   - Developer Console: Database tools, API tester

## Important Notes

⚠️ **Production Warning**
- Never enable developer mode on production sites
- Only use on local development or staging environments
- Remove the constant before deploying to production

⚠️ **Data Persistence**
- All data created in developer mode is real and will persist
- Database changes are permanent
- Test data should be cleaned up before production

⚠️ **Security Implications**
- Developer mode bypasses all license security checks
- Relaxes Content Security Policy (CSP) on localhost
- Should only be used in trusted development environments

## Troubleshooting

### Developer Mode Not Working

**Check wp-config.php:**
```php
// Make sure this line exists and is set to true
define('CAMPAIGNPRESS_DEV_MODE', true);
```

**Clear WordPress Cache:**
- Delete transients: `Tools → Site Health → Clear Transients`
- Flush object cache if using Redis/Memcached
- Refresh the admin page (Ctrl+F5 / Cmd+Shift+R)

**Verify in System Status:**
- Go to `CampaignPress Pro → System Status`
- Look for "Development Mode: Enabled" in Environment section

### Premium Features Not Showing

**Check File Existence:**
```bash
# Verify premium modules exist
ls includes/premium/crm/
ls includes/premium/field-operations/
ls includes/premium/analytics/
```

**Check Error Logs:**
```bash
# View WordPress debug log
tail -f wp-content/debug.log
```

**Verify Hooks:**
- Premium features load on `init` hook priority 1
- Check that no other plugins are blocking hook execution

## Disabling Developer Mode

To disable developer mode and return to normal license validation:

1. Open `wp-config.php`
2. Remove or comment out the line:
   ```php
   // define('CAMPAIGNPRESS_DEV_MODE', true);
   ```
3. Save the file
4. Refresh WordPress admin

The system will immediately return to normal license validation mode.

## Related Files

- **Premium System:** `includes/premium/premium-init.php`
- **License Page:** `includes/premium/admin-pages/license-page.php`
- **Features Page:** `includes/premium/admin-pages/features-page.php`
- **System Status:** `includes/premium/admin-pages/system-status-page.php`

## Support

For issues with developer mode or premium features:
- Check error logs in `wp-content/debug.log`
- Review system status in admin
- Consult `CLAUDE.md` for architecture details
- File issues at theme repository

---

**Remember:** Developer mode is a powerful testing tool. Use responsibly and never on production sites.
