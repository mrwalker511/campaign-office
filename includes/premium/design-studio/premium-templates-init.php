<?php
/**
 * Premium Templates Initialization
 *
 * Loads and initializes the Premium Templates system for Design Studio.
 *
 * @package CampaignPress
 * @subpackage Premium/DesignStudio
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if premium is active
if (!function_exists('cp_is_premium_active') || !cp_is_premium_active()) {
    return;
}

// Load Premium Templates class
require_once dirname(__FILE__) . '/class-premium-templates.php';

// Initialize Premium Templates
add_action('init', function() {
    if (class_exists('CP_Premium_Templates')) {
        // Premium Templates is initialized in its constructor
        // This ensures templates are loaded after WordPress is fully loaded
    }
}, 5);

/**
 * Helper function to check if premium templates are available
 *
 * @return bool True if premium templates are available
 */
function cp_has_premium_templates() {
    return function_exists('cp_is_premium_active') && cp_is_premium_active();
}

/**
 * Helper function to get template categories
 *
 * @return array Template categories
 */
function cp_get_template_categories() {
    if (!cp_has_premium_templates()) {
        return array();
    }

    $templates = new CP_Premium_Templates();
    return $templates->get_categories();
}
