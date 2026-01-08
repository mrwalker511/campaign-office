<?php
/**
 * Font Loading Strategy for CampaignPress
 *
 * This theme uses self-hosted fonts for GDPR compliance and performance.
 * Font files should be placed in assets/fonts/ directory.
 *
 * Required fonts (see theme.json):
 * - BricolageGrotesque-Variable.woff2 (Display font)
 * - PlusJakartaSans-Variable.woff2 (Body font)
 * - JetBrainsMono-Variable.woff2 (Monospace font)
 *
 * Font declarations are handled via theme.json fontFamilies.
 *
 * Note: Google Fonts preconnect has been removed to ensure
 * 100% self-hosted fonts for privacy compliance.
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// Fonts are loaded via theme.json - no preconnect needed for self-hosted fonts
// If you wish to use Google Fonts instead, uncomment below and remove self-hosted fonts:
/*
function campaignpress_preconnect_fonts() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
}
add_action('wp_head', 'campaignpress_preconnect_fonts', 1);
*/
