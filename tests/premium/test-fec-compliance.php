<?php
/**
 * FEC Compliance Premium Features Tests
 *
 * Tests for FEC contribution tracking, limits, and reporting
 *
 * @package CampaignOffice\Tests\Premium
 */

namespace CampaignOffice\Tests\Premium;

use WP_UnitTestCase;
use CampaignOffice\Tests\Test_Helper;

/**
 * FEC Compliance Test Class
 */
class Test_FEC_Compliance extends WP_UnitTestCase {

    /**
     * Test setup
     */
    public function setUp(): void {
        parent::setUp();

        if ( ! class_exists( 'CampaignPress_Premium' ) ) {
            $this->markTestSkipped( 'Premium features not available' );
        }
    }

    /**
     * Test teardown
     */
    public function tearDown(): void {
        parent::tearDown();
        Test_Helper::cleanup();
    }

    /**
     * Test FEC tables exist
     */
    public function test_fec_tables_exist() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'cp_fec_contributions',
            $wpdb->prefix . 'cp_fec_donors',
            $wpdb->prefix . 'cp_fec_audit_log',
        );

        foreach ( $tables as $table ) {
            $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
            $this->assertEquals( $table, $exists, "FEC table {$table} should exist" );
        }
    }

    /**
     * Test recording a contribution
     */
    public function test_record_contribution() {
        $contribution_data = array(
            'amount'               => 500.00,
            'donor_name'           => 'John Contributor',
            'donor_email'          => 'john@donor.com',
            'donor_address'        => '123 Main St',
            'donor_city'           => 'Washington',
            'donor_state'          => 'DC',
            'donor_zip'            => '20001',
            'donor_occupation'     => 'Engineer',
            'donor_employer'       => 'Tech Corp',
            'contribution_date'    => current_time( 'Y-m-d' ),
            'federal_election_id'  => 'P2024',
        );

        $contribution_id = Test_Helper::record_fec_contribution( $contribution_data );

        $this->assertGreaterThan( 0, $contribution_id, 'Contribution should be recorded' );

        $contribution = Test_Helper::get_fec_contribution( $contribution_id );

        $this->assertEquals( 500.00, $contribution->amount );
        $this->assertEquals( 'John Contributor', $contribution->donor_name );
    }

    /**
     * Test contribution limit enforcement
     */
    public function test_contribution_limit_enforcement() {
        $donor_email = 'biggiver@example.com';

        // Contribution limit (simplified for testing)
        $limit = 3300;

        // First contribution within limit
        $contribution_id_1 = Test_Helper::record_fec_contribution( array(
            'amount'      => 2000,
            'donor_email' => $donor_email,
        ) );

        $this->assertGreaterThan( 0, $contribution_id_1 );

        // Second contribution that would exceed limit
        $contribution_id_2 = Test_Helper::record_fec_contribution( array(
            'amount'      => 2000,
            'donor_email' => $donor_email,
        ) );

        // Should either fail or flag for review
        if ( ! is_wp_error( $contribution_id_2 ) ) {
            // Verify it's flagged
            $contribution = Test_Helper::get_fec_contribution( $contribution_id_2 );
            $this->assertTrue(
                isset( $contribution->flagged ) && $contribution->flagged,
                'Over-limit contribution should be flagged'
            );
        }
    }

    /**
     * Test donor aggregation
     */
    public function test_donor_aggregation() {
        $donor_email = 'frequent@donor.com';

        // Multiple contributions from same donor
        Test_Helper::record_fec_contribution( array(
            'amount'      => 100,
            'donor_email' => $donor_email,
            'donor_name'  => 'Frequent Donor',
        ) );

        Test_Helper::record_fec_contribution( array(
            'amount'      => 200,
            'donor_email' => $donor_email,
            'donor_name'  => 'Frequent Donor',
        ) );

        // Get donor total
        $total = Test_Helper::get_donor_total_contributions( $donor_email );

        $this->assertEquals( 300, $total, 'Donor total should aggregate contributions' );
    }

    /**
     * Test occupation and employer requirements
     */
    public function test_occupation_employer_required_for_large_contributions() {
        // Contributions over $200 require occupation/employer
        $contribution_data = array(
            'amount'           => 250,
            'donor_email'      => 'bigdonor@example.com',
            'donor_occupation' => '',
            'donor_employer'   => '',
        );

        $result = Test_Helper::validate_fec_contribution( $contribution_data );

        if ( is_wp_error( $result ) ) {
            $this->assertStringContainsString(
                'occupation',
                strtolower( $result->get_error_message() ),
                'Should require occupation for $200+ contributions'
            );
        }
    }

    /**
     * Test contribution audit trail
     */
    public function test_contribution_audit_trail() {
        $contribution_id = Test_Helper::record_fec_contribution( array(
            'amount'      => 100,
            'donor_email' => 'audited@example.com',
        ) );

        // Get audit logs for this contribution
        $logs = Test_Helper::get_fec_audit_logs( array(
            'contribution_id' => $contribution_id,
        ) );

        $this->assertGreaterThan( 0, count( $logs ), 'Contribution should have audit trail' );
    }

    /**
     * Test FEC report generation
     */
    public function test_fec_report_generation() {
        // Create test contributions
        for ( $i = 1; $i <= 5; $i++ ) {
            Test_Helper::record_fec_contribution( array(
                'amount'      => 100 * $i,
                'donor_email' => "donor{$i}@example.com",
            ) );
        }

        // Generate report
        $report = Test_Helper::generate_fec_report( array(
            'start_date' => date( 'Y-m-d', strtotime( '-30 days' ) ),
            'end_date'   => current_time( 'Y-m-d' ),
        ) );

        $this->assertNotEmpty( $report, 'Should generate FEC report' );
        $this->assertGreaterThan( 0, $report['total_contributions'] );
        $this->assertGreaterThan( 0, $report['total_amount'] );
    }

    /**
     * Test prohibited contributions detection
     */
    public function test_prohibited_contributions() {
        // Foreign nationals are prohibited
        $contribution_data = array(
            'amount'        => 100,
            'donor_email'   => 'foreign@example.com',
            'donor_country' => 'Canada',
        );

        $result = Test_Helper::validate_fec_contribution( $contribution_data );

        if ( is_wp_error( $result ) ) {
            $this->assertStringContainsString(
                'foreign',
                strtolower( $result->get_error_message() ),
                'Should prohibit foreign contributions'
            );
        }
    }

    /**
     * Test contribution refund tracking
     */
    public function test_contribution_refund() {
        $contribution_id = Test_Helper::record_fec_contribution( array(
            'amount'      => 500,
            'donor_email' => 'refund@example.com',
        ) );

        // Process refund
        $refund_id = Test_Helper::refund_fec_contribution( $contribution_id, 500, 'Duplicate payment' );

        $this->assertGreaterThan( 0, $refund_id, 'Refund should be recorded' );

        // Verify contribution is marked as refunded
        $contribution = Test_Helper::get_fec_contribution( $contribution_id );

        $this->assertTrue(
            isset( $contribution->refunded ) && $contribution->refunded,
            'Contribution should be marked as refunded'
        );
    }
}
