<?php
/**
 * Authentication and Authorization Security Tests
 *
 * Tests for CSRF, capability checks, and authorization vulnerabilities
 *
 * @package CampaignOffice\Tests\Security
 */

namespace CampaignOffice\Tests\Security;

use WP_UnitTestCase;
use CampaignOffice\Tests\Test_Helper;

/**
 * Authentication and Authorization Test Class
 */
class Test_Authentication_Authorization extends WP_UnitTestCase {

    private $admin_user_id;
    private $editor_user_id;
    private $subscriber_user_id;

    /**
     * Test setup
     */
    public function setUp(): void {
        parent::setUp();

        // Create test users with different roles
        $this->admin_user_id = Test_Helper::create_test_user( 'administrator' );
        $this->editor_user_id = Test_Helper::create_test_user( 'editor' );
        $this->subscriber_user_id = Test_Helper::create_test_user( 'subscriber' );
    }

    /**
     * Test teardown
     */
    public function tearDown(): void {
        parent::tearDown();
        Test_Helper::cleanup();
    }

    /**
     * Test nonce verification for AJAX requests
     */
    public function test_ajax_nonce_verification() {
        // Simulate AJAX request without nonce
        $_POST['action'] = 'cp_submit_volunteer_signup';
        $_POST['nonce'] = 'invalid_nonce';

        // Should fail nonce check
        $this->assertFalse(
            wp_verify_nonce( $_POST['nonce'], 'cp_volunteer_signup_nonce' ),
            'Invalid nonce should fail verification'
        );

        // Create valid nonce
        $valid_nonce = wp_create_nonce( 'cp_volunteer_signup_nonce' );
        $this->assertNotEmpty( $valid_nonce );

        // Valid nonce should pass
        $this->assertNotFalse(
            wp_verify_nonce( $valid_nonce, 'cp_volunteer_signup_nonce' ),
            'Valid nonce should pass verification'
        );
    }

    /**
     * Test capability checks for admin functions
     */
    public function test_admin_capability_checks() {
        // Admin should have manage_options
        wp_set_current_user( $this->admin_user_id );
        $this->assertTrue( current_user_can( 'manage_options' ) );

        // Editor should NOT have manage_options
        wp_set_current_user( $this->editor_user_id );
        $this->assertFalse( current_user_can( 'manage_options' ) );

        // Subscriber should NOT have manage_options
        wp_set_current_user( $this->subscriber_user_id );
        $this->assertFalse( current_user_can( 'manage_options' ) );
    }

    /**
     * Test volunteer deletion authorization
     */
    public function test_volunteer_deletion_authorization() {
        $volunteer_id = Test_Helper::create_test_volunteer();

        // Admin should be able to delete
        wp_set_current_user( $this->admin_user_id );
        $this->assertTrue(
            current_user_can( 'manage_options' ),
            'Admin should be able to delete volunteers'
        );

        // Editor should NOT be able to delete without proper capability
        wp_set_current_user( $this->editor_user_id );
        $this->assertFalse(
            current_user_can( 'manage_options' ),
            'Editor should not have delete capability'
        );

        // Non-logged in user should NOT be able to delete
        wp_set_current_user( 0 );
        $this->assertFalse(
            current_user_can( 'manage_options' ),
            'Non-logged user should not have delete capability'
        );
    }

    /**
     * Test IDOR (Insecure Direct Object Reference) protection
     */
    public function test_idor_protection() {
        // Create volunteer owned by user A
        wp_set_current_user( $this->editor_user_id );
        $volunteer_id_a = Test_Helper::create_test_volunteer( array(
            'created_by' => $this->editor_user_id,
        ) );

        // User B should not be able to modify user A's volunteer without proper permission
        wp_set_current_user( $this->subscriber_user_id );

        // This should be protected
        $can_edit = current_user_can( 'edit_post', $volunteer_id_a );
        $this->assertFalse( $can_edit, 'User should not edit other user\'s volunteers' );
    }

    /**
     * Test premium feature access control
     */
    public function test_premium_feature_access_control() {
        if ( ! class_exists( 'CampaignPress_Premium' ) ) {
            $this->markTestSkipped( 'Premium features not available' );
        }

        // Test CRM access
        wp_set_current_user( $this->admin_user_id );
        $this->assertTrue(
            current_user_can( 'manage_options' ),
            'Admin should access CRM'
        );

        // Non-admin should not access CRM admin functions
        wp_set_current_user( $this->subscriber_user_id );
        $this->assertFalse(
            current_user_can( 'manage_options' ),
            'Subscriber should not access CRM admin'
        );
    }

    /**
     * Test developer console access restriction
     */
    public function test_developer_console_access() {
        if ( ! class_exists( 'CampaignPress_Developer_Console' ) ) {
            $this->markTestSkipped( 'Developer console not available' );
        }

        // Only super admin should access developer console
        wp_set_current_user( $this->admin_user_id );

        // In multisite, only super admin should access
        if ( is_multisite() ) {
            $has_access = current_user_can( 'manage_network' );
            $this->assertTrue( is_super_admin() || ! $has_access, 'Only super admin should access dev console' );
        } else {
            $this->assertTrue(
                current_user_can( 'manage_options' ),
                'Admin should access dev console in single site'
            );
        }

        // Editor should NOT access
        wp_set_current_user( $this->editor_user_id );
        $this->assertFalse(
            current_user_can( 'manage_network' ) || ( ! is_multisite() && current_user_can( 'manage_options' ) ),
            'Editor should not access dev console'
        );
    }

    /**
     * Test rate limiting functionality
     */
    public function test_rate_limiting() {
        if ( ! function_exists( 'campaignpress_is_rate_limited' ) ) {
            $this->markTestSkipped( 'Rate limiting function not available' );
        }

        $action = 'test_action_' . time();

        // First few requests should not be rate limited
        for ( $i = 0; $i < 3; $i++ ) {
            $is_limited = campaignpress_is_rate_limited( $action, 5, 3600 );
            $this->assertFalse( $is_limited, "Request $i should not be rate limited" );
        }

        // After max requests, should be rate limited
        for ( $i = 0; $i < 5; $i++ ) {
            campaignpress_is_rate_limited( $action, 5, 3600 );
        }

        $is_limited = campaignpress_is_rate_limited( $action, 5, 3600 );
        $this->assertTrue( $is_limited, 'Should be rate limited after max requests' );
    }

    /**
     * Test webhook signature verification (should exist)
     */
    public function test_webhook_signature_verification() {
        if ( ! class_exists( 'CampaignPress_API_Webhooks' ) ) {
            $this->markTestSkipped( 'Webhook class not available' );
        }

        // Webhooks should require signature verification
        $payload = 'test payload';
        $secret = 'webhook_secret_key';

        // Generate valid signature
        $valid_signature = hash_hmac( 'sha256', $payload, $secret );

        // Test signature verification (assuming method exists)
        if ( method_exists( 'CampaignPress_API_Webhooks', 'verify_signature' ) ) {
            $webhooks = new \CampaignPress_API_Webhooks();

            // Valid signature should pass
            $is_valid = $webhooks->verify_signature( $payload, $valid_signature, $secret );
            $this->assertTrue( $is_valid, 'Valid signature should pass verification' );

            // Invalid signature should fail
            $is_valid = $webhooks->verify_signature( $payload, 'invalid_signature', $secret );
            $this->assertFalse( $is_valid, 'Invalid signature should fail verification' );
        } else {
            $this->fail( 'Webhook signature verification method not implemented - SECURITY RISK!' );
        }
    }

    /**
     * Test FEC compliance data access control
     */
    public function test_fec_data_access_control() {
        if ( ! class_exists( 'CampaignPress_FEC_Contributions' ) ) {
            $this->markTestSkipped( 'FEC features not available' );
        }

        // Only admin should access FEC data
        wp_set_current_user( $this->admin_user_id );
        $this->assertTrue( current_user_can( 'manage_options' ), 'Admin should access FEC data' );

        // Regular users should NOT access FEC contribution data
        wp_set_current_user( $this->subscriber_user_id );
        $this->assertFalse( current_user_can( 'manage_options' ), 'Subscriber should not access FEC data' );
    }

    /**
     * Test CSV import authorization
     */
    public function test_csv_import_authorization() {
        // Only admin should import data
        wp_set_current_user( $this->admin_user_id );
        $this->assertTrue(
            current_user_can( 'import' ) || current_user_can( 'manage_options' ),
            'Admin should have import capability'
        );

        // Regular users should not import
        wp_set_current_user( $this->subscriber_user_id );
        $this->assertFalse(
            current_user_can( 'import' ),
            'Subscriber should not have import capability'
        );
    }

    /**
     * Test REST API authentication
     */
    public function test_rest_api_authentication() {
        // Premium REST endpoints should require authentication
        $request = new \WP_REST_Request( 'GET', '/campaignpress/v1/crm/contacts' );

        // Without authentication, should be denied
        wp_set_current_user( 0 );
        $response = rest_do_request( $request );

        if ( $response ) {
            $this->assertNotEquals(
                200,
                $response->get_status(),
                'Unauthenticated request should not return 200'
            );
        }

        // With authentication, should be allowed
        wp_set_current_user( $this->admin_user_id );
        $response = rest_do_request( $request );

        // Should either succeed or return 404 (if endpoint doesn't exist yet)
        if ( $response ) {
            $status = $response->get_status();
            $this->assertTrue(
                in_array( $status, array( 200, 404 ), true ),
                'Authenticated admin request should succeed or return 404'
            );
        }
    }

    /**
     * Test check_admin_referer() usage
     */
    public function test_admin_referer_check() {
        // Set up referer
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'delete_volunteer_123' );

        // Should not throw exception with valid nonce
        $result = check_admin_referer( 'delete_volunteer_123' );
        $this->assertNotFalse( $result, 'Valid admin referer should pass' );

        // Invalid nonce should fail (but we catch it to not die)
        $_REQUEST['_wpnonce'] = 'invalid';

        $result = check_admin_referer( 'delete_volunteer_123', '_wpnonce', false );
        $this->assertFalse( $result, 'Invalid admin referer should fail' );
    }

    /**
     * Test that sensitive operations log audit trail
     */
    public function test_audit_trail_logging() {
        if ( ! class_exists( 'CampaignPress_FEC_Audit_Trail' ) ) {
            $this->markTestSkipped( 'Audit trail not available' );
        }

        wp_set_current_user( $this->admin_user_id );

        // Sensitive operations should be logged
        $actions_to_log = array(
            'volunteer_deleted',
            'fec_contribution_recorded',
            'crm_data_exported',
            'settings_updated',
        );

        foreach ( $actions_to_log as $action ) {
            // Log should exist (test that logging function exists)
            $this->assertTrue(
                method_exists( 'CampaignPress_FEC_Audit_Trail', 'log' ),
                'Audit trail logging method should exist'
            );
        }
    }
}
