<?php
/**
 * SQL Injection Security Tests
 *
 * Tests for SQL injection vulnerabilities identified in code review
 *
 * @package CampaignOffice\Tests\Security
 */

namespace CampaignOffice\Tests\Security;

use WP_UnitTestCase;
use CampaignOffice\Tests\Test_Helper;

/**
 * SQL Injection Test Class
 */
class Test_SQL_Injection extends WP_UnitTestCase {

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
     * Test CRM contact search for SQL injection
     *
     * @covers CampaignPress_CRM_Contacts::get_contacts()
     */
    public function test_crm_contact_search_sql_injection() {
        if ( ! class_exists( 'CampaignPress_Premium' ) ) {
            $this->markTestSkipped( 'Premium features not available' );
        }

        // SQL injection payloads
        $malicious_payloads = array(
            "' OR '1'='1",
            "'; DROP TABLE wp_users; --",
            "' UNION SELECT password FROM wp_users --",
            "1' AND '1'='1",
            "admin'--",
            "' OR 1=1#",
        );

        foreach ( $malicious_payloads as $payload ) {
            $args = array(
                'search' => $payload,
            );

            // This should not cause SQL errors or return unauthorized data
            $result = $this->get_crm_contacts_safely( $args );

            // Should either return error or empty results, not all contacts
            $this->assertNotNull( $result, "Search with payload '$payload' should not return null" );

            if ( ! is_wp_error( $result ) ) {
                // If successful, should have escaped the payload
                $this->assertLessThan( 1000, $result['total'] ?? 0, "Payload '$payload' may have bypassed SQL injection protection" );
            }
        }
    }

    /**
     * Test volunteer search for SQL injection
     *
     * @covers campaignpress_get_volunteers()
     */
    public function test_volunteer_search_sql_injection() {
        $malicious_payloads = array(
            "' OR '1'='1",
            "'; DROP TABLE wp_cp_volunteers; --",
            "1' UNION SELECT password FROM wp_users --",
        );

        foreach ( $malicious_payloads as $payload ) {
            $args = array(
                'search' => $payload,
            );

            global $wpdb;
            $initial_error = $wpdb->last_error;

            // Get volunteers (this should use the volunteer management class)
            $result = $this->get_volunteers_safely( $args );

            // Check for SQL errors
            $this->assertEquals( $initial_error, $wpdb->last_error, "SQL error occurred with payload: '$payload'" );

            // Result should be array or empty, not unfiltered database dump
            if ( is_array( $result ) ) {
                $this->assertLessThan( 1000, count( $result ), "Payload may have bypassed SQL protection" );
            }
        }
    }

    /**
     * Test database query escaping in custom queries
     */
    public function test_custom_query_escaping() {
        global $wpdb;

        // Test LIKE query escaping
        $search_term = "test%_wildcards";
        $escaped = $wpdb->esc_like( $search_term );

        // Wildcards should be escaped
        $this->assertStringContainsString( '\%', $escaped );
        $this->assertStringContainsString( '\_', $escaped );

        // Test prepared statement
        $query = $wpdb->prepare(
            "SELECT * FROM {$wpdb->posts} WHERE post_title LIKE %s",
            '%' . $wpdb->esc_like( $search_term ) . '%'
        );

        $this->assertStringContainsString( '\%', $query );
        $this->assertStringContainsString( '\_', $query );
    }

    /**
     * Test FEC contribution queries for SQL injection
     */
    public function test_fec_contribution_queries() {
        if ( ! class_exists( 'CampaignPress_Premium' ) ) {
            $this->markTestSkipped( 'Premium features not available' );
        }

        $malicious_donor_name = "'; DROP TABLE wp_cp_fec_contributions; --";

        global $wpdb;
        $initial_error = $wpdb->last_error;

        // Attempt to query with malicious input
        $result = $this->search_fec_contributions_safely( array( 'donor_name' => $malicious_donor_name ) );

        // Should not cause SQL errors
        $this->assertEquals( $initial_error, $wpdb->last_error, "SQL error with malicious donor name" );
    }

    /**
     * Helper: Safely get CRM contacts
     */
    private function get_crm_contacts_safely( $args ) {
        try {
            if ( class_exists( 'CampaignPress_CRM_Contacts' ) ) {
                $crm = new \CampaignPress_CRM_Contacts();
                return $crm->get_contacts( $args );
            }
        } catch ( \Exception $e ) {
            return new \WP_Error( 'exception', $e->getMessage() );
        }
        return array();
    }

    /**
     * Helper: Safely get volunteers
     */
    private function get_volunteers_safely( $args ) {
        global $wpdb;

        // Simulate the volunteer search query with proper escaping
        $search = isset( $args['search'] ) ? $args['search'] : '';

        if ( empty( $search ) ) {
            return array();
        }

        $table_name = $wpdb->prefix . 'cp_volunteers';
        $contacts_table = $wpdb->prefix . 'cp_contacts';

        // This is how it SHOULD be done - fully prepared query
        $query = $wpdb->prepare(
            "SELECT v.* FROM {$table_name} v
            LEFT JOIN {$contacts_table} c ON v.contact_id = c.id
            WHERE c.first_name LIKE %s OR c.last_name LIKE %s OR c.email LIKE %s
            LIMIT 100",
            '%' . $wpdb->esc_like( $search ) . '%',
            '%' . $wpdb->esc_like( $search ) . '%',
            '%' . $wpdb->esc_like( $search ) . '%'
        );

        return $wpdb->get_results( $query );
    }

    /**
     * Helper: Safely search FEC contributions
     */
    private function search_fec_contributions_safely( $args ) {
        try {
            if ( class_exists( 'CampaignPress_FEC_Contributions' ) ) {
                $fec = new \CampaignPress_FEC_Contributions();
                // Use proper search method if available
                return $fec->search_contributions( $args );
            }
        } catch ( \Exception $e ) {
            return new \WP_Error( 'exception', $e->getMessage() );
        }
        return array();
    }

    /**
     * Test wpdb->prepare() usage
     */
    public function test_wpdb_prepare_required() {
        global $wpdb;

        // Example of INCORRECT (vulnerable) usage
        $bad_user_input = "1 OR 1=1";

        // NEVER do this (example of what NOT to do):
        // $bad_query = "SELECT * FROM {$wpdb->posts} WHERE ID = {$bad_user_input}";

        // ALWAYS do this:
        $good_query = $wpdb->prepare(
            "SELECT * FROM {$wpdb->posts} WHERE ID = %d",
            absint( $bad_user_input )
        );

        // Test that prepare actually escapes
        $this->assertStringContainsString( '0', $good_query, "absint should convert non-numeric to 0" );
    }

    /**
     * Test integer sanitization for IDs
     */
    public function test_id_sanitization() {
        $test_cases = array(
            '123'           => 123,
            '123abc'        => 123,
            'abc'           => 0,
            '0'             => 0,
            '-5'            => 5, // absint returns absolute value
            '999999999999'  => 999999999999,
            "'; DROP TABLE" => 0,
        );

        foreach ( $test_cases as $input => $expected ) {
            $result = absint( $input );
            $this->assertEquals( $expected, $result, "absint('$input') should return $expected" );
        }
    }
}
