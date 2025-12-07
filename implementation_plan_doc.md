# Fix Premium Upgrade Page Error

The "Upgrade to Premium" page is reportedly erroring out and not showing content. A likely cause is a PHP Fatal Error due to a function redeclaration or a naming collision. The function `render_feature_check` is defined inside the template file `includes/premium/admin-pages/upgrade-page.php`. This is a generic name and defining functions in included templates is a bad practice that can lead to collisions.

## Proposed Changes

### Includes Layer

#### [MODIFY] [premium-init.php](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/includes/premium/premium-init.php)
- Add `cp_render_feature_check($value)` to the helper functions section.
- This ensures the function is defined once and is available globally with a prefixed name.

#### [MODIFY] [upgrade-page.php](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/includes/premium/admin-pages/upgrade-page.php)
- Rename calls from `render_feature_check` to `cp_render_feature_check`.
- Remove the definition of `render_feature_check` at the bottom of the file.
