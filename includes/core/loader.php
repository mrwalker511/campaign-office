<?php
/**
 * Core Loader
 *
 * Handles file inclusions and class initialization.
 *
 * @package CampaignPress
 * @subpackage Core
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load translation support (registers textdomain loading on after_setup_theme hook)
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/translation-support.php';

// Load Core Classes
$core_files = array(
    CAMPAIGNPRESS_INCLUDES_DIR . '/core/class-performance.php',
    CAMPAIGNPRESS_INCLUDES_DIR . '/core/class-template-loader.php',
    CAMPAIGNPRESS_INCLUDES_DIR . '/core/class-contact-manager.php',
    CAMPAIGNPRESS_INCLUDES_DIR . '/core/class-script-manager.php',
);

foreach ($core_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    } else {
        // Log error in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Campaign Office: Core file missing - ' . $file);
        }
    }
}

// Initialize Core Systems
if (class_exists('CampaignPress\Core\Performance')) {
    CampaignPress\Core\Performance::init();
}

if (class_exists('CampaignPress\Core\Template_Loader')) {
    CampaignPress\Core\Template_Loader::init();
}

if (class_exists('CampaignPress\Core\Script_Manager')) {
    CampaignPress\Core\Script_Manager::init();
}

if (class_exists('CampaignPress_Contact_Manager')) {
    $GLOBALS['cp_contact_manager'] = new CampaignPress_Contact_Manager();
}


// Load Free Features (Legacy functional style)
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/font-preconnect.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/class-bootstrap-navwalker.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/admin-dashboard-fixes.php';
// Custom post types - provides fallback if CampaignPress Core plugin is not active
if (!class_exists('CampaignPress_Core') && !class_exists('Campaign_Office_Core')) {
    require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/custom-post-types.php';
}
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/gutenberg-blocks.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/customizer.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/template-functions.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/block-templates.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/integrations.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/demo-content.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/admin-notices.php';
// Event calendar enhancements moved to Campaign Office Core plugin
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/accessibility.php';
// Note: translation-support.php is loaded at the top of this file (FIRST)
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/donation-enhancements.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/social-media-feeds.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/volunteer-portal.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/campaign-communications.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/analytics-dashboard.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/class-theme-json-helper.php';

// Load Block Logic
if ( file_exists( CAMPAIGNPRESS_THEME_DIR . '/blocks/registration.php' ) ) {
    require_once CAMPAIGNPRESS_THEME_DIR . '/blocks/registration.php';
}
if ( file_exists( CAMPAIGNPRESS_THEME_DIR . '/blocks/block-view-loader.php' ) ) {
    require_once CAMPAIGNPRESS_THEME_DIR . '/blocks/block-view-loader.php';
}

// Load Premium Features
if (file_exists(CAMPAIGNPRESS_INCLUDES_DIR . '/premium/premium-init.php')) {
    require_once CAMPAIGNPRESS_INCLUDES_DIR . '/premium/premium-init.php';
}

require_once get_template_directory() . '/includes/admin-menu-reorganization.php';
