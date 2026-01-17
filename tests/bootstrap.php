<?php
/**
 * PHPUnit Bootstrap for Campaign Office Theme
 *
 * @package CampaignOffice\Tests
 */

// Define test environment
define( 'CAMPAIGN_OFFICE_TESTING', true );

// Composer autoloader
$campaign_office_autoloader = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $campaign_office_autoloader ) ) {
    require_once $campaign_office_autoloader;
}

// WordPress test configuration
$_tests_dir = getenv( 'WP_TESTS_DIR' );

// If WP_TESTS_DIR not set, try to find it
if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// If WordPress test library doesn't exist, provide instructions
if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
    echo "\n\033[31mError: WordPress test library not found.\033[0m\n\n";
    echo "To install WordPress test library:\n\n";
    echo "bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest\n\n";
    exit( 1 );
}

// Load WordPress test library
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the theme being tested
 */
function _manually_load_theme() {
    // Set theme directory
    register_theme_directory( dirname( __DIR__ ) );

    // Switch to our theme
    switch_theme( 'campaignpress' );

    // Load theme files
    require dirname( __DIR__ ) . '/functions.php';
}

// Load theme before tests
tests_add_filter( 'muplugins_loaded', '_manually_load_theme' );

// Start WordPress test environment
require $_tests_dir . '/includes/bootstrap.php';

// Load test utilities
require __DIR__ . '/utilities/class-test-helper.php';
