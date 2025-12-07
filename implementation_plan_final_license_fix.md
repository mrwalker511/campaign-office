# Fix License Page & Test Key Issues

Addressing three issues:
1.  **Input Formatting:** The license key input automatically reformats text into 4-character chunks, mangling the requested `TEST-KEY-123` key.
2.  **Test Key Support:** The backend needs to manually validate the `TEST-KEY-123` key since it won't pass the remote server check.
3.  **Blank Page Navigation:** The JavaScript tab switcher requires `data-tab` attributes on navigation links, which are currently missing.

## User Review Required
None.

## Proposed Changes

### JavaScript (Admin Assets)

#### [MODIFY] [assets/js/premium-admin.js](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/assets/js/premium-admin.js)
- Modify `formatLicenseKey` to check if the input starts with `TEST-`. If so, disable the auto-formatter or allow the specific format.
- Alternatively, simply allow existing hyphens to remain if they match the user's intent, or just check for `TEST` and skip formatting.

### PHP (Premium Core)

#### [MODIFY] [includes/premium/premium-init.php](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/includes/premium/premium-init.php)
- Update `validate_license_key` to checking for `TEST-KEY-123`.
- Return success mock data if matched.

### PHP (Admin Templates)

#### [MODIFY] [includes/premium/admin-pages/license-page.php](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/includes/premium/admin-pages/license-page.php)
- Add `data-tab="<?php echo esc_attr($tab_key); ?>"` to the navigation `<a>` tags.

## Verification Plan

### Manual Verification
1.  **Input:** Type/Paste `TEST-KEY-123` into the license field. Confirm it remains `TEST-KEY-123`.
2.  **Navigation:** Click tabs and reload page without query params. Confirm content is visible.
3.  **Activation:** Click "Activate". Confirm success message and dashboard update.
