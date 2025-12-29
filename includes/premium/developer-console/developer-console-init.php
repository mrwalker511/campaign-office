<?php
/**
 * Developer Console Initialization
 *
 * Loads and initializes the developer console feature
 *
 * @package CampaignPress
 * @subpackage DeveloperConsole
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Load required classes
require_once __DIR__ . '/class-developer-console-database.php';
require_once __DIR__ . '/class-developer-console.php';
require_once __DIR__ . '/class-system-health.php';
require_once __DIR__ . '/class-database-manager.php';
require_once __DIR__ . '/class-api-tester.php';
require_once __DIR__ . '/class-data-exporter.php';

// Initialize developer console
$campaignpress_developer_console = CampaignPress_Developer_Console::get_instance();

// Enqueue assets for developer console
add_action('admin_enqueue_scripts', function($hook) {
    // Only load on developer console page
    if ($hook !== 'toplevel_page_campaignpress-developer-console') {
        return;
    }

    // Enqueue CSS
    wp_enqueue_style(
        'cp-developer-console',
        get_template_directory_uri() . '/includes/premium/developer-console/assets/developer-console.css',
        array(),
        '1.0.1'
    );

    // Enqueue JavaScript
    wp_enqueue_script(
        'cp-developer-console',
        get_template_directory_uri() . '/includes/premium/developer-console/assets/developer-console.js',
        array('jquery'),
        '1.0.1',
        true
    );

    // Localize script with AJAX URL and nonce
    wp_localize_script('cp-developer-console', 'cpDevConsole', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('cp_dev_console_nonce'),
        'tablePrefix' => $GLOBALS['wpdb']->prefix
    ));
});
