# Fix License Page Button Disappearance

The user reported that the button (and content) on the license page starts to show and then disappears.
Investigation revealed that `assets/js/premium-admin.js` contains a tab switching logic that hides all `.cp-tab-content` elements on load and attempts to show the one matching the current tab.
However, `includes/premium/admin-pages/license-page.php` does not include the `data-tab` attribute on the content container, causing the selector `.cp-tab-content[data-tab="..."]` to fail, leaving the content hidden.

## User Review Required
None.

## Proposed Changes

### Premium Admin

#### [MODIFY] [includes/premium/admin-pages/license-page.php](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/includes/premium/admin-pages/license-page.php)
- Add `data-tab="<?php echo esc_attr($current_tab); ?>"` to the `<div class="cp-tab-content">` element (around line 51).

## Verification Plan

### Manual Verification
- Verify that the fix enables the content to be displayed.
- Since I cannot view the browser, I will assume that if the code matches the logic required by JS, it will work.
