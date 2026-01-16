<?php
/**
 * CampaignPress Premium Local Configuration
 *
 * This file contains local configuration settings for CampaignPress Premium.
 * Use this file to configure your license server and other premium settings
 * without modifying the core plugin files.
 *
 * SETUP INSTRUCTIONS:
 *
 * 1. LICENSE SERVER CONFIGURATION:
 *    - Set CAMPAIGNPRESS_LICENSE_SERVER_URL to your license server endpoint
 *    - The URL should point to your license validation API
 *    - Example: 'https://your-license-server.com/api/v1/'
 *    - If using a local development server: 'http://localhost:3000/api/v1/'
 *
 * 2. DEVELOPMENT MODE:
 *    - Set CAMPAIGNPRESS_DEV_MODE to true in wp-config.php to bypass license checks
 *    - This will unlock all premium features for testing
 *    - NEVER enable on production sites
 *
 * 3. MOCK LICENSE SERVER:
 *    - Set CAMPAIGNPRESS_MOCK_LICENSE_SERVER to true to use a mock server
 *    - This is useful for testing without a real license server
 *    - Only works when DEV_MODE is enabled
 *
 * @package CampaignPress
 * @subpackage Premium
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * License Server URL Configuration
 *
 * IMPORTANT: Update this URL to point to your license server.
 * This is the endpoint where license validation requests are sent.
 *
 * Examples:
 * - Production: 'https://api.yourdomain.com/v1/'
 * - Staging: 'https://staging.yourdomain.com/v1/'
 * - Local: 'http://localhost:3000/api/v1/'
 *
 * NOTE: The URL must end with a trailing slash (/)
 */
if (!defined('CAMPAIGNPRESS_LICENSE_SERVER_URL')) {
    define('CAMPAIGNPRESS_LICENSE_SERVER_URL', 'https://api.campaignpress.com/v1/');
}

/**
 * Development Mode
 *
 * When enabled, all premium features are unlocked without a license key.
 * This is intended for development and testing purposes only.
 *
 * To enable, add this to your wp-config.php:
 * define('CAMPAIGNPRESS_DEV_MODE', true);
 *
 * WARNING: Never enable this on production sites.
 */
if (!defined('CAMPAIGNPRESS_DEV_MODE')) {
    define('CAMPAIGNPRESS_DEV_MODE', false);
}

/**
 * Mock License Server
 *
 * When enabled, uses a mock license server for testing.
 * This allows you to test premium features without setting up a real server.
 *
 * This only works when CAMPAIGNPRESS_DEV_MODE is true.
 */
if (!defined('CAMPAIGNPRESS_MOCK_LICENSE_SERVER')) {
    define('CAMPAIGNPRESS_MOCK_LICENSE_SERVER', false);
}

/**
 * License Validation Timeout
 *
 * How long (in seconds) to wait for the license server to respond
 * before timing out. Increase this if your license server is slow.
 */
if (!defined('CAMPAIGNPRESS_LICENSE_TIMEOUT')) {
    define('CAMPAIGNPRESS_LICENSE_TIMEOUT', 15);
}

/**
 * Enable Premium Logging
 *
 * When enabled, logs premium system events for debugging.
 * Logs are stored in wp_options table under 'campaignpress_premium_logs'
 */
if (!defined('CAMPAIGNPRESS_ENABLE_LOGGING')) {
    define('CAMPAIGNPRESS_ENABLE_LOGGING', false);
}

/**
 * License Grace Period (in days)
 *
 * How many days premium features continue to work after license expires.
 * This gives you time to renew without losing access.
 */
if (!defined('CAMPAIGNPRESS_GRACE_PERIOD_DAYS')) {
    define('CAMPAIGNPRESS_GRACE_PERIOD_DAYS', 7);
}

/**
 * Auto-Check License Interval (in hours)
 *
 * How often to automatically check license status.
 * Set to 0 to disable automatic checks.
 */
if (!defined('CAMPAIGNPRESS_LICENSE_CHECK_INTERVAL')) {
    define('CAMPAIGNPRESS_LICENSE_CHECK_INTERVAL', 24);
}

/**
 * API Key for License Server
 *
 * If your license server requires an API key for authentication,
 * set it here. Leave empty if not needed.
 */
if (!defined('CAMPAIGNPRESS_LICENSE_API_KEY')) {
    define('CAMPAIGNPRESS_LICENSE_API_KEY', '');
}
