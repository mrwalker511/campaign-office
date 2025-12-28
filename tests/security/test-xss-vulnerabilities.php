<?php
/**
 * XSS (Cross-Site Scripting) Security Tests
 *
 * Tests for XSS vulnerabilities in output escaping
 *
 * @package CampaignOffice\Tests\Security
 */

namespace CampaignOffice\Tests\Security;

use WP_UnitTestCase;
use CampaignOffice\Tests\Test_Helper;

/**
 * XSS Vulnerability Test Class
 */
class Test_XSS_Vulnerabilities extends WP_UnitTestCase {

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
     * XSS payloads for testing
     */
    private function get_xss_payloads() {
        return array(
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
            '<svg onload=alert("XSS")>',
            'javascript:alert("XSS")',
            '<iframe src="javascript:alert(\'XSS\')">',
            '"><script>alert(String.fromCharCode(88,83,83))</script>',
            '<body onload=alert("XSS")>',
            '<input onfocus=alert("XSS") autofocus>',
            '<a href="javascript:alert(\'XSS\')">Click</a>',
        );
    }

    /**
     * Test esc_html() properly escapes XSS
     */
    public function test_esc_html_escapes_xss() {
        foreach ( $this->get_xss_payloads() as $payload ) {
            $escaped = esc_html( $payload );

            // Should not contain executable script tags
            $this->assertStringNotContainsString( '<script>', $escaped );
            $this->assertStringNotContainsString( 'onerror=', $escaped );
            $this->assertStringNotContainsString( 'onload=', $escaped );
            $this->assertStringNotContainsString( 'javascript:', $escaped );

            // Should contain HTML entities
            $this->assertStringContainsString( '&lt;', $escaped );
        }
    }

    /**
     * Test esc_attr() properly escapes attributes
     */
    public function test_esc_attr_escapes_xss() {
        foreach ( $this->get_xss_payloads() as $payload ) {
            $escaped = esc_attr( $payload );

            // Should not contain quotes that could break out of attribute
            $this->assertStringNotContainsString( '"><', $escaped );
            $this->assertStringNotContainsString( "javascript:", $escaped );
        }
    }

    /**
     * Test esc_url() properly sanitizes URLs
     */
    public function test_esc_url_sanitizes_javascript() {
        $malicious_urls = array(
            'javascript:alert("XSS")',
            'data:text/html,<script>alert("XSS")</script>',
            'vbscript:alert("XSS")',
        );

        foreach ( $malicious_urls as $url ) {
            $escaped = esc_url( $url );

            // Should remove javascript: protocol
            $this->assertStringNotContainsString( 'javascript:', $escaped );
            $this->assertStringNotContainsString( 'vbscript:', $escaped );
        }

        // Valid URLs should pass through
        $valid_url = 'https://example.com/page?param=value';
        $this->assertEquals( $valid_url, esc_url( $valid_url ) );
    }

    /**
     * Test volunteer data output escaping
     */
    public function test_volunteer_data_escaping() {
        // Create test volunteer with XSS payload
        $xss_payload = '<script>alert("XSS")</script>';

        $volunteer_id = Test_Helper::create_test_volunteer( array(
            'first_name' => $xss_payload,
            'last_name'  => $xss_payload,
            'email'      => 'test@example.com',
            'interests'  => json_encode( array( $xss_payload, 'Valid Interest' ) ),
        ) );

        $this->assertGreaterThan( 0, $volunteer_id );

        // Get volunteer data
        $volunteer = Test_Helper::get_volunteer( $volunteer_id );

        // When outputting, it should be escaped
        $output_first_name = esc_html( $volunteer->first_name );
        $output_last_name = esc_html( $volunteer->last_name );

        $this->assertStringNotContainsString( '<script>', $output_first_name );
        $this->assertStringNotContainsString( '<script>', $output_last_name );

        // Test JSON decoded interests
        $interests = json_decode( $volunteer->interests, true );
        if ( is_array( $interests ) ) {
            $escaped_interests = array_map( 'esc_html', $interests );
            $output = implode( ', ', $escaped_interests );

            $this->assertStringNotContainsString( '<script>', $output );
        }
    }

    /**
     * Test event RSVP data output escaping
     */
    public function test_event_rsvp_escaping() {
        $event_id = Test_Helper::create_test_event();

        $xss_payload = '<img src=x onerror=alert("XSS")>';

        // Create RSVP with malicious data
        $rsvp_id = Test_Helper::create_test_rsvp( array(
            'event_id'             => $event_id,
            'dietary_restrictions' => $xss_payload,
            'notes'                => $xss_payload,
        ) );

        $this->assertGreaterThan( 0, $rsvp_id );

        // Get RSVP
        $rsvp = Test_Helper::get_event_rsvp( $rsvp_id );

        // Should escape output
        $escaped_dietary = esc_html( $rsvp->dietary_restrictions );
        $escaped_notes = esc_html( $rsvp->notes );

        $this->assertStringNotContainsString( 'onerror=', $escaped_dietary );
        $this->assertStringNotContainsString( 'onerror=', $escaped_notes );
    }

    /**
     * Test CRM contact output escaping
     */
    public function test_crm_contact_escaping() {
        if ( ! class_exists( 'CampaignPress_Premium' ) ) {
            $this->markTestSkipped( 'Premium features not available' );
        }

        $xss_payload = '<svg onload=alert("XSS")>';

        $contact_id = Test_Helper::create_test_crm_contact( array(
            'first_name' => $xss_payload,
            'last_name'  => 'Test',
            'notes'      => $xss_payload,
        ) );

        $this->assertGreaterThan( 0, $contact_id );

        // Retrieve and escape
        $contact = Test_Helper::get_crm_contact( $contact_id );

        $escaped_name = esc_html( $contact->first_name );
        $escaped_notes = wp_kses_post( $contact->notes );

        $this->assertStringNotContainsString( 'onload=', $escaped_name );
        $this->assertStringNotContainsString( '<svg', $escaped_notes );
    }

    /**
     * Test wp_kses_post() allows safe HTML
     */
    public function test_wp_kses_post_allows_safe_html() {
        $safe_html = '<p>This is <strong>safe</strong> HTML with <a href="https://example.com">a link</a></p>';
        $cleaned = wp_kses_post( $safe_html );

        // Should preserve safe tags
        $this->assertStringContainsString( '<p>', $cleaned );
        $this->assertStringContainsString( '<strong>', $cleaned );
        $this->assertStringContainsString( '<a href=', $cleaned );

        // But remove dangerous attributes
        $dangerous = '<p onclick="alert(\'XSS\')">Click me</p>';
        $cleaned_dangerous = wp_kses_post( $dangerous );

        $this->assertStringNotContainsString( 'onclick=', $cleaned_dangerous );
    }

    /**
     * Test sanitize_text_field() removes line breaks and scripts
     */
    public function test_sanitize_text_field() {
        $input = "<script>alert('XSS')</script>\nLine 2\tTabbed";
        $sanitized = sanitize_text_field( $input );

        $this->assertStringNotContainsString( '<script>', $sanitized );
        $this->assertStringNotContainsString( "\n", $sanitized );
        $this->assertStringNotContainsString( "\t", $sanitized );
    }

    /**
     * Test sanitize_email() properly validates emails
     */
    public function test_sanitize_email() {
        $valid_emails = array(
            'test@example.com'      => 'test@example.com',
            'user+tag@example.com'  => 'user+tag@example.com',
        );

        foreach ( $valid_emails as $input => $expected ) {
            $this->assertEquals( $expected, sanitize_email( $input ) );
        }

        $invalid_emails = array(
            '<script>alert("XSS")</script>@example.com',
            'test@<script>alert("XSS")</script>.com',
            'javascript:alert("XSS")',
        );

        foreach ( $invalid_emails as $email ) {
            $sanitized = sanitize_email( $email );
            $this->assertStringNotContainsString( '<script>', $sanitized );
            $this->assertStringNotContainsString( 'javascript:', $sanitized );
        }
    }

    /**
     * Test shortcode output escaping
     */
    public function test_shortcode_output_escaping() {
        // Test volunteer form shortcode with XSS in attributes
        $malicious_attr = '" onclick="alert(\'XSS\')" data-test="';

        $shortcode_output = do_shortcode( "[cp_volunteer_form title=\"{$malicious_attr}\"]" );

        // Should escape quotes and remove event handlers
        $this->assertStringNotContainsString( 'onclick=', $shortcode_output );
    }

    /**
     * Test admin notice output escaping
     */
    public function test_admin_notice_escaping() {
        $xss_payload = '<script>alert("XSS")</script>';

        // Set a notice with XSS payload
        set_transient( 'cp_admin_notice', $xss_payload, 60 );

        $notice = get_transient( 'cp_admin_notice' );
        $escaped_notice = esc_html( $notice );

        $this->assertStringNotContainsString( '<script>', $escaped_notice );
        $this->assertStringContainsString( '&lt;script&gt;', $escaped_notice );
    }
}
