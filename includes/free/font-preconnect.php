<?php
/**
 * Font Loading Strategy for CampaignPress
 *
 * CampaignPress uses SYSTEM FONTS for optimal performance and privacy.
 * No font files are needed - fonts are already on the user's device!
 *
 * Current font stack (configured in theme.json):
 * - Display/Body: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, etc.
 * - Monospace: ui-monospace, 'Cascadia Code', Menlo, Consolas, etc.
 *
 * Benefits:
 * - Zero load time (fonts already available)
 * - 100% GDPR compliant (no external requests)
 * - Better performance (no font downloads)
 * - Native feel (matches user's OS)
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// System fonts are used by default - no preconnect needed!
//
// If you want to use Google Fonts instead:
// 1. Uncomment the function below
// 2. Update theme.json fontFamilies to reference your chosen Google Fonts
// 3. Note: This will add external requests and may affect GDPR compliance
/*
function campaignpress_preconnect_fonts() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
}
add_action('wp_head', 'campaignpress_preconnect_fonts', 1);
*/
