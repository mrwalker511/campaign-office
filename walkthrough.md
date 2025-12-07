# Premium Upgrade Page Fix

I have resolved the error on the Premium Upgrade page. The issue was caused by a function definition (`render_feature_check`) inside a template file (`upgrade-page.php`), which is susceptible to naming collisions and fatal errors if the file is included multiple times or if another plugin defines a function with the same name.

## Changes

### 1. Refactored Helper Function
Moved the feature check rendering logic to the main premium initialization file and gave it a unique prefix.

#### [premium-init.php](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/includes/premium/premium-init.php)
```php
/**
 * Render feature check icon/text
 *
 * @param bool|string $value Feature value
 * @return string HTML output
 */
function cp_render_feature_check($value) {
    if ($value === true) {
        return '<span class="cp-check-yes"><span class="dashicons dashicons-yes-alt"></span></span>';
    } elseif ($value === false) {
        return '<span class="cp-check-no"><span class="dashicons dashicons-minus"></span></span>';
    } else {
        return '<span class="cp-check-text">' . esc_html($value) . '</span>';
    }
}
```

### 2. Updated Template
Updated the upgrade page template to use the new global helper function and removed the local definition.

#### [upgrade-page.php](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/includes/premium/admin-pages/upgrade-page.php)
```diff
-<td><?php echo render_feature_check($feature['free']); ?></td>
+<td><?php echo cp_render_feature_check($feature['free']); ?></td>
```

## Verification Results

### Automated Test
Ran `test_upgrade_page.php` which simulated the WordPress environment and loaded the upgrade page template.
- **Result**: Passed. The page rendered successfully without fatal errors.
