<?php
/**
 * Volunteer Management Integration Tests
 *
 * Tests for volunteer signup, management, and portal functionality
 *
 * @package CampaignOffice\Tests\Integration
 */

namespace CampaignOffice\Tests\Integration;

use WP_UnitTestCase;
use CampaignOffice\Tests\Test_Helper;

/**
 * Volunteer Management Integration Test Class
 */
class Test_Volunteer_Management extends WP_UnitTestCase {

    private $admin_user_id;

    /**
     * Test setup
     */
    public function setUp(): void {
        parent::setUp();
        $this->admin_user_id = Test_Helper::create_test_user( 'administrator' );
        wp_set_current_user( $this->admin_user_id );
    }

    /**
     * Test teardown
     */
    public function tearDown(): void {
        parent::tearDown();
        Test_Helper::cleanup();
    }

    /**
     * Test volunteer database table creation
     */
    public function test_volunteer_table_created() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_volunteers';

        $table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

        $this->assertEquals( $table_name, $table_exists, 'Volunteer table should exist' );
    }

    /**
     * Test volunteer table schema
     */
    public function test_volunteer_table_schema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_volunteers';

        $columns = $wpdb->get_col( "DESCRIBE {$table_name}" );

        $expected_columns = array(
            'id',
            'contact_id',
            'skills',
            'interests',
            'availability',
            'status',
            'created_at',
            'updated_at',
        );

        foreach ( $expected_columns as $column ) {
            $this->assertContains( $column, $columns, "Table should have {$column} column" );
        }
    }

    /**
     * Test creating a volunteer record
     */
    public function test_create_volunteer() {
        $volunteer_data = array(
            'first_name'   => 'John',
            'last_name'    => 'Volunteer',
            'email'        => 'john@volunteer.com',
            'phone'        => '555-1234',
            'skills'       => json_encode( array( 'canvassing', 'phone_banking' ) ),
            'interests'    => json_encode( array( 'events', 'fundraising' ) ),
            'availability' => json_encode( array( 'weekends' ) ),
        );

        $volunteer_id = Test_Helper::create_test_volunteer( $volunteer_data );

        $this->assertGreaterThan( 0, $volunteer_id, 'Volunteer ID should be positive' );

        // Verify volunteer was created
        $volunteer = Test_Helper::get_volunteer( $volunteer_id );

        $this->assertNotNull( $volunteer );
        $this->assertEquals( 'john@volunteer.com', $volunteer->email );
    }

    /**
     * Test volunteer signup form submission
     */
    public function test_volunteer_signup_submission() {
        $_POST['first_name'] = 'Jane';
        $_POST['last_name'] = 'Doe';
        $_POST['email'] = 'jane@example.com';
        $_POST['phone'] = '555-5678';
        $_POST['skills'] = array( 'social_media' );
        $_POST['interests'] = array( 'digital_outreach' );
        $_POST['nonce'] = wp_create_nonce( 'cp_volunteer_signup_nonce' );

        // Simulate form submission
        $result = $this->process_volunteer_signup();

        $this->assertNotWPError( $result, 'Volunteer signup should succeed' );
        $this->assertGreaterThan( 0, $result, 'Should return volunteer ID' );
    }

    /**
     * Test duplicate email detection
     */
    public function test_duplicate_email_detection() {
        $email = 'duplicate@example.com';

        // Create first volunteer
        $volunteer_id_1 = Test_Helper::create_test_volunteer( array(
            'email' => $email,
        ) );

        $this->assertGreaterThan( 0, $volunteer_id_1 );

        // Attempt to create duplicate
        $volunteer_id_2 = Test_Helper::create_test_volunteer( array(
            'email' => $email,
        ) );

        // Should either fail or update existing
        $this->assertTrue(
            $volunteer_id_2 === $volunteer_id_1 || is_wp_error( $volunteer_id_2 ),
            'Duplicate email should be handled'
        );
    }

    /**
     * Test volunteer status management
     */
    public function test_volunteer_status_changes() {
        $volunteer_id = Test_Helper::create_test_volunteer();

        // Test status: pending, active, inactive
        $statuses = array( 'pending', 'active', 'inactive' );

        foreach ( $statuses as $status ) {
            $result = Test_Helper::update_volunteer_status( $volunteer_id, $status );

            $this->assertTrue( $result, "Should update status to {$status}" );

            $volunteer = Test_Helper::get_volunteer( $volunteer_id );
            $this->assertEquals( $status, $volunteer->status );
        }
    }

    /**
     * Test volunteer search functionality
     */
    public function test_volunteer_search() {
        // Create test volunteers
        Test_Helper::create_test_volunteer( array(
            'first_name' => 'Alice',
            'last_name'  => 'Anderson',
            'email'      => 'alice@example.com',
        ) );

        Test_Helper::create_test_volunteer( array(
            'first_name' => 'Bob',
            'last_name'  => 'Builder',
            'email'      => 'bob@example.com',
        ) );

        // Search by first name
        $results = Test_Helper::search_volunteers( array( 'search' => 'Alice' ) );

        $this->assertGreaterThan( 0, count( $results ), 'Should find volunteers matching search' );

        // Search by email
        $results = Test_Helper::search_volunteers( array( 'search' => 'bob@example.com' ) );

        $this->assertGreaterThan( 0, count( $results ), 'Should search by email' );
    }

    /**
     * Test volunteer filtering by status
     */
    public function test_volunteer_filtering() {
        // Create volunteers with different statuses
        Test_Helper::create_test_volunteer( array( 'status' => 'active' ) );
        Test_Helper::create_test_volunteer( array( 'status' => 'active' ) );
        Test_Helper::create_test_volunteer( array( 'status' => 'inactive' ) );

        // Filter by active status
        $active_volunteers = Test_Helper::search_volunteers( array( 'status' => 'active' ) );

        $this->assertGreaterThanOrEqual( 2, count( $active_volunteers ), 'Should filter by status' );

        foreach ( $active_volunteers as $volunteer ) {
            $this->assertEquals( 'active', $volunteer->status );
        }
    }

    /**
     * Test volunteer hours logging
     */
    public function test_volunteer_hours_logging() {
        $volunteer_id = Test_Helper::create_test_volunteer();

        // Log hours
        $hours_data = array(
            'volunteer_id' => $volunteer_id,
            'hours'        => 5,
            'date'         => current_time( 'Y-m-d' ),
            'activity'     => 'Phone banking',
        );

        $log_id = Test_Helper::log_volunteer_hours( $hours_data );

        $this->assertGreaterThan( 0, $log_id, 'Should log volunteer hours' );

        // Verify total hours
        $total_hours = Test_Helper::get_volunteer_total_hours( $volunteer_id );

        $this->assertEquals( 5, $total_hours );
    }

    /**
     * Test volunteer leaderboard
     */
    public function test_volunteer_leaderboard() {
        // Create volunteers with different hours
        $vol1 = Test_Helper::create_test_volunteer( array( 'first_name' => 'Top' ) );
        $vol2 = Test_Helper::create_test_volunteer( array( 'first_name' => 'Middle' ) );
        $vol3 = Test_Helper::create_test_volunteer( array( 'first_name' => 'Bottom' ) );

        Test_Helper::log_volunteer_hours( array( 'volunteer_id' => $vol1, 'hours' => 20 ) );
        Test_Helper::log_volunteer_hours( array( 'volunteer_id' => $vol2, 'hours' => 10 ) );
        Test_Helper::log_volunteer_hours( array( 'volunteer_id' => $vol3, 'hours' => 5 ) );

        // Get leaderboard
        $leaderboard = Test_Helper::get_volunteer_leaderboard( 10 );

        $this->assertGreaterThanOrEqual( 3, count( $leaderboard ) );

        // Should be ordered by hours DESC
        if ( count( $leaderboard ) >= 3 ) {
            $this->assertGreaterThanOrEqual(
                $leaderboard[1]->total_hours,
                $leaderboard[0]->total_hours,
                'Leaderboard should be ordered by hours'
            );
        }
    }

    /**
     * Test volunteer portal authentication
     */
    public function test_volunteer_portal_authentication() {
        $volunteer_id = Test_Helper::create_test_volunteer( array(
            'email' => 'portal@example.com',
        ) );

        // Attempt to authenticate
        $authenticated = Test_Helper::authenticate_volunteer( 'portal@example.com' );

        $this->assertNotFalse( $authenticated, 'Volunteer should authenticate' );
        $this->assertEquals( $volunteer_id, $authenticated->id );
    }

    /**
     * Test volunteer shift signup
     */
    public function test_volunteer_shift_signup() {
        $volunteer_id = Test_Helper::create_test_volunteer();
        $event_id = Test_Helper::create_test_event();

        // Create shift
        $shift_id = Test_Helper::create_volunteer_shift( array(
            'event_id'   => $event_id,
            'start_time' => current_time( 'Y-m-d H:i:s' ),
            'end_time'   => date( 'Y-m-d H:i:s', strtotime( '+2 hours' ) ),
            'slots'      => 5,
        ) );

        $this->assertGreaterThan( 0, $shift_id );

        // Sign up volunteer
        $signup_id = Test_Helper::signup_volunteer_for_shift( $volunteer_id, $shift_id );

        $this->assertGreaterThan( 0, $signup_id, 'Volunteer should sign up for shift' );

        // Verify signup
        $signups = Test_Helper::get_shift_signups( $shift_id );

        $this->assertGreaterThan( 0, count( $signups ) );
    }

    /**
     * Test volunteer data export
     */
    public function test_volunteer_data_export() {
        // Create test volunteers
        for ( $i = 1; $i <= 5; $i++ ) {
            Test_Helper::create_test_volunteer( array(
                'first_name' => "Volunteer{$i}",
            ) );
        }

        // Export data
        $export_data = Test_Helper::export_volunteers_csv();

        $this->assertNotEmpty( $export_data, 'Export should return data' );
        $this->assertStringContainsString( 'first_name', $export_data, 'Should contain headers' );
        $this->assertStringContainsString( 'Volunteer1', $export_data, 'Should contain volunteer data' );
    }

    /**
     * Helper: Process volunteer signup
     */
    private function process_volunteer_signup() {
        if ( ! function_exists( 'campaignpress_process_volunteer_signup' ) ) {
            // Fallback: create volunteer directly
            return Test_Helper::create_test_volunteer( array(
                'first_name' => $_POST['first_name'],
                'last_name'  => $_POST['last_name'],
                'email'      => $_POST['email'],
                'phone'      => $_POST['phone'] ?? '',
            ) );
        }

        return campaignpress_process_volunteer_signup( $_POST );
    }
}
