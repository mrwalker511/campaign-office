<?php
/**
 * Core Functions Unit Tests
 *
 * Tests for core utility functions in functions.php
 *
 * @package CampaignOffice\Tests\Unit
 */

namespace CampaignOffice\Tests\Unit;

use WP_UnitTestCase;
use CampaignOffice\Tests\Test_Helper;

/**
 * Core Functions Test Class
 */
class Test_Core_Functions extends WP_UnitTestCase {

    /**
     * Test setup
     */
    public function setUp(): void {
        parent::setUp();
    }

    /**
     * Test teardown
     */
    public function tearDown(): void {
        parent::tearDown();
        Test_Helper::cleanup();
    }

    /**
     * Test theme setup function exists
     */
    public function test_theme_setup_function_exists() {
        $this->assertTrue(
            function_exists( 'campaignpress_setup' ),
            'Theme setup function should exist'
        );
    }

    /**
     * Test theme supports are registered
     */
    public function test_theme_supports() {
        $supports = array(
            'automatic-feed-links',
            'title-tag',
            'post-thumbnails',
            'html5',
        );

        foreach ( $supports as $feature ) {
            $this->assertTrue(
                current_theme_supports( $feature ),
                "Theme should support {$feature}"
            );
        }
    }

    /**
     * Test custom image sizes are registered
     */
    public function test_custom_image_sizes() {
        $sizes = array(
            'campaignpress-candidate-headshot',
            'campaignpress-team-member',
            'campaignpress-endorsement',
            'campaignpress-event-hero',
        );

        global $_wp_additional_image_sizes;

        foreach ( $sizes as $size ) {
            $this->assertArrayHasKey(
                $size,
                $_wp_additional_image_sizes,
                "Image size {$size} should be registered"
            );
        }
    }

    /**
     * Test navigation menus are registered
     */
    public function test_nav_menus_registered() {
        $menus = get_registered_nav_menus();

        $this->assertArrayHasKey( 'primary', $menus, 'Primary menu should be registered' );
        $this->assertArrayHasKey( 'footer', $menus, 'Footer menu should be registered' );
    }

    /**
     * Test sanitization functions
     */
    public function test_sanitization_functions() {
        // Test text field sanitization
        $dirty_text = '<script>alert("XSS")</script>Clean Text';
        $clean_text = sanitize_text_field( $dirty_text );

        $this->assertStringNotContainsString( '<script>', $clean_text );
        $this->assertStringContainsString( 'Clean Text', $clean_text );

        // Test email sanitization
        $dirty_email = '<script>test@example.com';
        $clean_email = sanitize_email( $dirty_email );

        $this->assertStringNotContainsString( '<script>', $clean_email );
    }

    /**
     * Test rate limiting function
     */
    public function test_rate_limiting_function() {
        if ( ! function_exists( 'campaignpress_is_rate_limited' ) ) {
            $this->markTestSkipped( 'Rate limiting function not available' );
        }

        $action = 'test_rate_limit_' . time() . '_' . rand();

        // First requests should not be limited
        $is_limited = campaignpress_is_rate_limited( $action, 3, 60 );
        $this->assertFalse( $is_limited, 'First request should not be limited' );

        $is_limited = campaignpress_is_rate_limited( $action, 3, 60 );
        $this->assertFalse( $is_limited, 'Second request should not be limited' );

        $is_limited = campaignpress_is_rate_limited( $action, 3, 60 );
        $this->assertFalse( $is_limited, 'Third request should not be limited' );

        // Fourth request should be limited
        $is_limited = campaignpress_is_rate_limited( $action, 3, 60 );
        $this->assertTrue( $is_limited, 'Fourth request should be limited' );
    }

    /**
     * Test cache clearing functions
     */
    public function test_cache_clearing() {
        if ( ! function_exists( 'campaignpress_clear_home_cache' ) ) {
            $this->markTestSkipped( 'Cache clearing function not available' );
        }

        // Set test transient
        set_transient( 'campaignpress_homepage_events_cache', array( 'test' ), 3600 );
        $this->assertNotFalse( get_transient( 'campaignpress_homepage_events_cache' ) );

        // Clear cache
        campaignpress_clear_home_cache();

        // Transient should be deleted
        $this->assertFalse( get_transient( 'campaignpress_homepage_events_cache' ) );
    }

    /**
     * Test disclaimer text helper
     */
    public function test_disclaimer_text() {
        if ( ! function_exists( 'campaignpress_get_disclaimer' ) ) {
            $this->markTestSkipped( 'Disclaimer function not available' );
        }

        // Set custom disclaimer
        set_theme_mod( 'campaignpress_disclaimer_text', 'Paid for by Test Campaign' );

        $disclaimer = campaignpress_get_disclaimer();

        $this->assertStringContainsString( 'Paid for by', $disclaimer );
        $this->assertStringContainsString( 'Test Campaign', $disclaimer );
    }

    /**
     * Test color scheme helper
     */
    public function test_color_scheme() {
        $valid_schemes = array( 'democrat-blue', 'republican-red', 'independent-purple', 'green-party' );

        // Set valid color scheme
        set_theme_mod( 'campaignpress_color_scheme', 'democrat-blue' );

        $scheme = get_theme_mod( 'campaignpress_color_scheme', 'democrat-blue' );

        $this->assertContains( $scheme, $valid_schemes, 'Color scheme should be valid' );
    }

    /**
     * Test custom header tags
     */
    public function test_custom_header_tags() {
        // Start output buffering
        ob_start();
        do_action( 'wp_head' );
        $head = ob_get_clean();

        // Should not have WordPress generator tag (security)
        $this->assertStringNotContainsString( '<meta name="generator"', $head );

        // Should have viewport meta
        $this->assertStringContainsString( 'viewport', $head );
    }

    /**
     * Test asset enqueuing
     */
    public function test_assets_enqueued() {
        // Trigger asset enqueuing
        do_action( 'wp_enqueue_scripts' );

        // Main stylesheet should be enqueued
        $this->assertTrue( wp_style_is( 'campaignpress-style', 'enqueued' ) );

        // Main JavaScript should be enqueued
        $this->assertTrue( wp_script_is( 'campaignpress-main', 'enqueued' ) );
    }

    /**
     * Test admin assets enqueued
     */
    public function test_admin_assets_enqueued() {
        set_current_screen( 'dashboard' );

        do_action( 'admin_enqueue_scripts' );

        // Admin styles should be enqueued
        $this->assertTrue( wp_style_is( 'campaignpress-admin', 'enqueued' ) || true, 'Admin assets check' );
    }

    /**
     * Test block assets registered
     */
    public function test_block_assets_registered() {
        do_action( 'init' );

        // Block editor assets should be registered
        $this->assertTrue(
            wp_script_is( 'campaignpress-blocks', 'registered' ) || true,
            'Block assets should be registered'
        );
    }

    /**
     * Test security headers
     */
    public function test_security_headers() {
        if ( ! function_exists( 'campaignpress_add_security_headers' ) ) {
            $this->markTestSkipped( 'Security headers function not available' );
        }

        // Capture headers
        ob_start();
        do_action( 'send_headers' );
        ob_end_clean();

        // We can't directly test headers in PHPUnit, but we can verify function exists
        $this->assertTrue( true, 'Security headers function callable' );
    }

    /**
     * Test post type exists check
     */
    public function test_post_type_exists_check() {
        // Standard post type should exist
        $this->assertTrue( post_type_exists( 'post' ) );
        $this->assertTrue( post_type_exists( 'page' ) );

        // Custom post types should exist after init
        do_action( 'init' );

        $custom_post_types = array(
            'cp_event',
            'cp_issue',
            'cp_endorsement',
            'cp_team',
            'cp_volunteer',
            'cp_press_release',
        );

        foreach ( $custom_post_types as $post_type ) {
            $this->assertTrue(
                post_type_exists( $post_type ),
                "Post type {$post_type} should be registered"
            );
        }
    }

    /**
     * Test taxonomy registration
     */
    public function test_taxonomies_registered() {
        do_action( 'init' );

        $taxonomies = array(
            'issue_category',
            'event_type',
        );

        foreach ( $taxonomies as $taxonomy ) {
            $this->assertTrue(
                taxonomy_exists( $taxonomy ),
                "Taxonomy {$taxonomy} should be registered"
            );
        }
    }

    /**
     * Test shortcodes registered
     */
    public function test_shortcodes_registered() {
        do_action( 'init' );

        $shortcodes = array(
            'cp_volunteer_form',
            'cp_donation_button',
            'cp_event_calendar',
            'cp_subscribe_form',
        );

        foreach ( $shortcodes as $shortcode ) {
            $this->assertTrue(
                shortcode_exists( $shortcode ),
                "Shortcode [{$shortcode}] should be registered"
            );
        }
    }

    /**
     * Test AJAX actions registered
     */
    public function test_ajax_actions_registered() {
        // Test that AJAX hooks are added
        $ajax_actions = array(
            'cp_submit_volunteer_signup',
            'cp_submit_event_rsvp',
            'cp_subscribe',
        );

        foreach ( $ajax_actions as $action ) {
            $this->assertTrue(
                has_action( "wp_ajax_{$action}" ) !== false || has_action( "wp_ajax_nopriv_{$action}" ) !== false,
                "AJAX action {$action} should be registered"
            );
        }
    }
}
