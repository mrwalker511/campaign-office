# Developer Console Troubleshooting Guide

## Issue: Console Not Working After Demo Import

If the Developer Console is not working after importing demo content, follow these steps:

### Option 1: Automatic Fix (Recommended)

Add this code to your `functions.php` temporarily, then visit any admin page:

```php
// TEMPORARY: Fix Developer Console after demo import
add_action('admin_init', function() {
    if (current_user_can('manage_options') && isset($_GET['fix_dev_console'])) {
        $console = CampaignPress_Developer_Console::get_instance();
        $result = $console->manual_reinit();

        echo '<div class="notice notice-success"><p><strong>Developer Console Re-initialized</strong></p><pre>';
        print_r($result);
        echo '</pre></div>';
    }
});
```

Then visit: `yourdomain.com/wp-admin/?fix_dev_console=1`

**IMPORTANT**: Remove this code from `functions.php` after fixing!

### Option 2: WP-CLI Command

If you have WP-CLI access:

```bash
wp eval 'CampaignPress_Developer_Console::get_instance()->manual_reinit();'
```

### Option 3: Database Manual Fix

Run this SQL in phpMyAdmin or your database tool:

```sql
-- Check if tables exist
SHOW TABLES LIKE '%cp_dev_console%';

-- If tables don't exist, they'll be created automatically on next admin visit

-- Check if settings exist
SELECT * FROM wp_cp_dev_console_settings;

-- If no settings, manually insert:
INSERT INTO wp_cp_dev_console_settings
(creator_user_id, creator_email, enabled, security_level, session_timeout, allowed_actions, created_at)
VALUES
(1, 'your-email@example.com', 1, 'high', 3600, '["all"]', NOW());

-- Replace:
-- - 1 with your WordPress user ID
-- - 'your-email@example.com' with your email address
```

### Option 4: Code in Plugin

Create a temporary plugin with this code:

**File**: `wp-content/plugins/fix-dev-console.php`

```php
<?php
/*
Plugin Name: Fix Developer Console
Description: Temporary plugin to fix Developer Console after demo import
Version: 1.0.0
*/

add_action('admin_notices', function() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['run_fix'])) {
        if (class_exists('CampaignPress_Developer_Console')) {
            $console = CampaignPress_Developer_Console::get_instance();
            $result = $console->manual_reinit();

            echo '<div class="notice notice-success is-dismissible">';
            echo '<h2>Developer Console Fixed!</h2>';
            echo '<p><strong>Actions Taken:</strong></p>';
            echo '<ul>';
            foreach ($result['actions_taken'] as $action) {
                echo '<li>' . esc_html($action) . '</li>';
            }
            echo '</ul>';
            echo '<p><strong>Creator Email:</strong> ' . esc_html($result['creator_email'] ?? 'Not set') . '</p>';
            echo '<p><strong>Creator User ID:</strong> ' . esc_html($result['creator_user_id'] ?? 'Not set') . '</p>';
            echo '<p><strong>You can now deactivate and delete this plugin.</strong></p>';
            echo '</div>';
        }
    } else {
        echo '<div class="notice notice-warning">';
        echo '<p><strong>Developer Console Fix Available</strong></p>';
        echo '<p><a href="' . admin_url('?run_fix=1') . '" class="button button-primary">Click Here to Fix Developer Console</a></p>';
        echo '</div>';
    }
});
```

**Steps:**
1. Create the file above
2. Activate the plugin in WordPress
3. Click the button in the admin notice
4. Deactivate and delete the plugin

## Common Issues and Solutions

### Issue: "Access Denied" Message

**Cause**: Creator settings not initialized correctly

**Solution**: Use one of the options above to reinitialize

### Issue: Tables Don't Exist

**Cause**: Theme activation interrupted during demo import

**Solution**: Tables will auto-create on next admin page visit, or use Option 1 above

### Issue: Wrong Creator Email

**Cause**: License email not set, defaulted to first admin

**Fix**: Update manually in database:

```sql
UPDATE wp_cp_dev_console_settings
SET creator_email = 'your-correct-email@example.com',
    creator_user_id = YOUR_USER_ID
WHERE id = 1;
```

### Issue: Console Menu Not Showing

**Causes**:
1. Not logged in as administrator
2. Settings not initialized
3. Premium features not loaded

**Solutions**:
1. Ensure you're logged in with administrator role
2. Run manual reinit (Option 1 above)
3. Check that CampaignPress Premium is active

## Verify Console is Working

After fixing, verify:

1. ✅ Console menu appears at top of admin sidebar
2. ✅ Clicking "Dev Console" loads the interface
3. ✅ No "Access Denied" message
4. ✅ Tabs are clickable
5. ✅ System Health tab loads data

## Prevention

To prevent issues after demo import:

1. **Activate theme BEFORE importing demo content**
2. **Set license key BEFORE importing demo content**
3. **Clear all caches after demo import**
4. **Visit admin area immediately after import**

## Still Having Issues?

If none of the above work:

1. Check PHP error logs for specific errors
2. Ensure PHP 7.4+ (8.2 recommended)
3. Check database user has CREATE TABLE permissions
4. Verify WordPress version is 6.4+
5. Check for plugin conflicts (disable other plugins temporarily)

## Contact Information

If issues persist, provide:
- PHP version
- WordPress version
- Error messages from debug.log
- Result of running Option 1 above
- Screenshot of database tables (SHOW TABLES LIKE '%cp_dev%')
