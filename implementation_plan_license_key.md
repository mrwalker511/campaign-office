# Fix Blank Page & Enable Test License

The user reports the page goes blank again after activation, and requests a valid license key for testing.
1.  **Blank Page:** The JS tab logic relies on `data-tab` attributes on the navigation links if the URL parameter is missing. These attributes are currently missing in `license-page.php`.
2.  **Test License:** To allow the user to test premium features without a real license, I will modify the validation logic to accept a specific "magic" key (`TEST-KEY-123`).

## User Review Required
None.

## Proposed Changes

### Admin Templates

#### [MODIFY] [includes/premium/admin-pages/license-page.php](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/includes/premium/admin-pages/license-page.php)
- Add `data-tab="<?php echo esc_attr($tab_key); ?>"` to the `<a>` tags in the navigation tab loop.

### Premium Core

#### [MODIFY] [includes/premium/premium-init.php](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/includes/premium/premium-init.php)
- Update `validate_license_key` method to check if `$license_key === 'TEST-KEY-123'`.
- If matched, return a successful mock response similar to the developer mode bypass.

## Verification Plan

### Manual Verification
- **Blank Page:** The user should be able to reload the page or navigate without the explicitly `tab` parameter and still see content.
- **License Activation:** User will enter `TEST-KEY-123` and `test@example.com`.
- **Result:** The license should activate successfully, showing the "Active" state with premium features enabled.
