# Development License Testing Guide

This guide explains how to test the CampaignPress license system during development.

## Quick Start

Your theme is already in **Development Mode**, which bypasses all license checks. However, if you want to test the actual license activation/validation flow, use the test license keys below.

## Test License Keys

### 📦 Starter License
```
License Key: CP-DEV-STARTER-2024-A1B2C3D4E5F6
Email: dev@campaignpress.test
Tier: Starter
Site Limit: 1 site
Expires: 1 year from activation
```

### 💼 Professional License
```
License Key: CP-DEV-PROFESSIONAL-2024-X1Y2Z3W4V5U6
Email: dev@campaignpress.test
Tier: Professional
Site Limit: 5 sites
Expires: 1 year from activation
```

### 🏢 Enterprise License
```
License Key: CP-DEV-ENTERPRISE-2024-Q1W2E3R4T5Y6
Email: dev@campaignpress.test
Tier: Enterprise
Site Limit: Unlimited
Expires: 2 years from activation
```

### ❌ Expired License (for testing)
```
License Key: CP-DEV-EXPIRED-2024-M1N2O3P4Q5R6
Email: dev@campaignpress.test
Status: Expired 30 days ago
```

### 🚫 Invalid License (for testing)
```
License Key: CP-DEV-INVALID-2024-FAKEFAKEFAKE
Email: dev@campaignpress.test
Status: Invalid/Unknown
```

## How to Use

### Option 1: Development Mode (Currently Active)

Development mode is **already enabled** in `functions.php` (line 24):

```php
define('CAMPAIGNPRESS_DEV_MODE', true);
```

This gives you:
- ✅ All premium features unlocked (Professional tier)
- ✅ No license validation required
- ✅ All modules enabled
- ✅ Perfect for development and testing

**No additional setup needed!** Just start building.

### Option 2: Test License Validation Flow

If you want to test the actual license activation UI and validation:

1. **Enable Mock License Server:**

   Add this line to your `wp-config.php` (before "That's all, stop editing!"):
   ```php
   require_once __DIR__ . '/wp-content/themes/campaign-office/dev-license-helper.php';
   ```

2. **Temporarily Disable Dev Mode Bypass** (optional):

   In `functions.php`, comment out line 24:
   ```php
   // define('CAMPAIGNPRESS_DEV_MODE', true);
   ```

3. **Go to License Page:**

   Navigate to **Appearance → License** in WordPress admin

4. **Activate a Test License:**

   Use any of the test license keys above with the test email `dev@campaignpress.test`

5. **Test Different Scenarios:**
   - Activate Starter license → See limited features
   - Activate Professional license → See all features
   - Try expired license → See expiration notice
   - Try invalid license → See error message
   - Deactivate and reactivate → Test the full flow

## Current Status

✅ **Development Mode: ACTIVE**

Your current setup in `functions.php`:
```php
define('CAMPAIGNPRESS_DEV_MODE', true);
```

This means:
- You have access to **ALL premium features**
- License validation is **bypassed**
- Perfect for development
- Shows as "Professional" tier with 10-year expiry in admin

## License Tiers & Features

### Starter
- Premium Templates
- Custom Blocks
- Email Integration

### Professional (Your Current Dev Mode Tier)
- Everything in Starter, plus:
- Advanced Analytics
- Donor Management
- Volunteer Portal
- Compliance Tools
- Field Operations
- Auto Updates

### Enterprise
- Everything in Professional, plus:
- White Label
- Priority Support
- Custom Development

## Testing Checklist

Use this checklist to test the license system:

- [ ] Activate Starter license → Verify limited features shown
- [ ] Activate Professional license → Verify all standard features shown
- [ ] Activate Enterprise license → Verify all features + enterprise shown
- [ ] Try expired license → Verify expiration notice and grace period
- [ ] Try invalid license → Verify error message
- [ ] Deactivate license → Verify features are disabled
- [ ] Check license status display in admin
- [ ] Test auto-update check (Professional/Enterprise only)
- [ ] Test license renewal notice
- [ ] Test site limit warnings

## Notes

- **Never commit** `dev-license-helper.php` to production
- **Never deploy** with `CAMPAIGNPRESS_DEV_MODE` enabled
- Test license keys only work with the mock server (dev-license-helper.php)
- In production, you'll need a real license server at the URL specified in `premium-init.php`

## Production Deployment

Before deploying to production:

1. Remove or comment out `CAMPAIGNPRESS_DEV_MODE` in functions.php
2. Remove the `require_once` for `dev-license-helper.php` from wp-config.php
3. Set up your actual license server
4. Update `LICENSE_SERVER` constant or use the `campaignpress_license_server_url` filter

## Questions?

- Check `includes/premium/premium-init.php` for license system code
- Check `includes/premium/admin-pages/license-page.php` for the license UI
- Enable `WP_DEBUG` to see license validation logs
