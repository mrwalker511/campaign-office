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

// Load Core Classes
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/core/class-performance.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/core/class-template-loader.php';

// Initialize Core Systems
CampaignPress\Core\Performance::init();
CampaignPress\Core\Template_Loader::init();


// Load Free Features (Legacy functional style)
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/font-preconnect.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/class-bootstrap-navwalker.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/custom-post-types.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/gutenberg-blocks.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/customizer.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/template-functions.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/integrations.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/demo-content.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/admin-notices.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/volunteer-management.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/event-management.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/event-calendar-enhancements.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/accessibility.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/translation-support.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/donation-enhancements.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/social-media-feeds.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/volunteer-portal.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/campaign-communications.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/analytics-dashboard.php';

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
