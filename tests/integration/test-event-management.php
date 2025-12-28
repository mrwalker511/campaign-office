<?php
/**
 * Event Management Integration Tests
 *
 * Tests for event creation, RSVP, and calendar functionality
 *
 * @package CampaignOffice\Tests\Integration
 */

namespace CampaignOffice\Tests\Integration;

use WP_UnitTestCase;
use CampaignOffice\Tests\Test_Helper;

/**
 * Event Management Integration Test Class
 */
class Test_Event_Management extends WP_UnitTestCase {

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
     * Test event RSVP table creation
     */
    public function test_event_rsvp_table_created() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_event_rsvps';

        $table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

        $this->assertEquals( $table_name, $table_exists, 'Event RSVP table should exist' );
    }

    /**
     * Test creating an event with metadata
     */
    public function test_create_event_with_metadata() {
        $event_id = Test_Helper::create_test_event( array(
            'title'            => 'Campaign Rally',
            'event_date'       => '2025-07-04',
            'event_time'       => '14:00',
            'event_location'   => 'City Park',
            'rsvp_enabled'     => true,
            'max_attendees'    => 500,
        ) );

        $this->assertGreaterThan( 0, $event_id );

        // Verify metadata
        $this->assertEquals( '2025-07-04', get_post_meta( $event_id, 'event_date', true ) );
        $this->assertEquals( '14:00', get_post_meta( $event_id, 'event_time', true ) );
        $this->assertEquals( 'City Park', get_post_meta( $event_id, 'event_location', true ) );
    }

    /**
     * Test event RSVP submission
     */
    public function test_event_rsvp_submission() {
        $event_id = Test_Helper::create_test_event();

        $rsvp_data = array(
            'event_id'             => $event_id,
            'first_name'           => 'John',
            'last_name'            => 'Attendee',
            'email'                => 'john@example.com',
            'guests'               => 2,
            'dietary_restrictions' => 'Vegetarian',
        );

        $rsvp_id = Test_Helper::create_test_rsvp( $rsvp_data );

        $this->assertGreaterThan( 0, $rsvp_id, 'RSVP should be created' );

        // Verify RSVP
        $rsvp = Test_Helper::get_event_rsvp( $rsvp_id );

        $this->assertNotNull( $rsvp );
        $this->assertEquals( $event_id, $rsvp->event_id );
        $this->assertEquals( 'john@example.com', $rsvp->email );
        $this->assertEquals( 2, $rsvp->guests );
    }

    /**
     * Test event capacity management
     */
    public function test_event_capacity_management() {
        $event_id = Test_Helper::create_test_event( array(
            'max_attendees' => 5,
        ) );

        // Create RSVPs up to capacity
        for ( $i = 1; $i <= 3; $i++ ) {
            Test_Helper::create_test_rsvp( array(
                'event_id' => $event_id,
                'email'    => "attendee{$i}@example.com",
                'guests'   => 1,
            ) );
        }

        // Get current attendance
        $attendance = Test_Helper::get_event_attendance_count( $event_id );

        $this->assertEquals( 3, $attendance, 'Attendance count should match RSVPs' );

        // Check if event is full
        $is_full = Test_Helper::is_event_full( $event_id );

        $this->assertFalse( $is_full, 'Event should not be full yet' );
    }

    /**
     * Test event calendar view
     */
    public function test_event_calendar_view() {
        // Create events in different months
        Test_Helper::create_test_event( array(
            'event_date' => '2025-06-15',
            'title'      => 'June Event',
        ) );

        Test_Helper::create_test_event( array(
            'event_date' => '2025-07-20',
            'title'      => 'July Event',
        ) );

        // Get events for June
        $june_events = Test_Helper::get_events_by_month( 2025, 6 );

        $this->assertGreaterThan( 0, count( $june_events ) );
        $this->assertStringContainsString( 'June Event', $june_events[0]->post_title );
    }

    /**
     * Test upcoming events query
     */
    public function test_upcoming_events_query() {
        // Create past event
        Test_Helper::create_test_event( array(
            'event_date' => '2020-01-01',
            'title'      => 'Past Event',
        ) );

        // Create future event
        Test_Helper::create_test_event( array(
            'event_date' => '2030-12-31',
            'title'      => 'Future Event',
        ) );

        // Get upcoming events
        $upcoming = Test_Helper::get_upcoming_events( 10 );

        // Should only return future events
        foreach ( $upcoming as $event ) {
            $event_date = get_post_meta( $event->ID, 'event_date', true );
            $this->assertGreaterThan(
                current_time( 'Y-m-d' ),
                $event_date,
                'Upcoming events should be in the future'
            );
        }
    }

    /**
     * Test event RSVP confirmation email
     */
    public function test_event_rsvp_confirmation() {
        // Reset email log
        reset_phpmailer_instance();

        $event_id = Test_Helper::create_test_event();

        $rsvp_id = Test_Helper::create_test_rsvp( array(
            'event_id' => $event_id,
            'email'    => 'test@example.com',
        ) );

        // Trigger confirmation email (if implemented)
        do_action( 'campaignpress_event_rsvp_created', $rsvp_id, $event_id );

        // Check if email was sent
        $mailer = tests_retrieve_phpmailer_instance();

        if ( $mailer->mock_sent ) {
            $this->assertStringContainsString( 'test@example.com', $mailer->get_recipient( 'to' )->address );
        }
    }

    /**
     * Test event types taxonomy
     */
    public function test_event_types_taxonomy() {
        $taxonomy = get_taxonomy( 'event_type' );

        $this->assertNotNull( $taxonomy );
        $this->assertContains( 'cp_event', $taxonomy->object_type );
    }

    /**
     * Test assigning event type
     */
    public function test_assign_event_type() {
        $event_id = Test_Helper::create_test_event();

        // Create event type
        $term = wp_insert_term( 'Town Hall', 'event_type' );
        $this->assertNotWPError( $term );

        // Assign to event
        $result = wp_set_object_terms( $event_id, array( $term['term_id'] ), 'event_type' );
        $this->assertNotWPError( $result );

        // Verify
        $terms = wp_get_object_terms( $event_id, 'event_type' );
        $this->assertEquals( 'Town Hall', $terms[0]->name );
    }

    /**
     * Test event shortcode rendering
     */
    public function test_event_calendar_shortcode() {
        $event_id = Test_Helper::create_test_event();

        $output = do_shortcode( '[cp_event_calendar]' );

        $this->assertNotEmpty( $output, 'Event calendar shortcode should produce output' );
        $this->assertStringContainsString( 'event', strtolower( $output ) );
    }

    /**
     * Test event RSVP form shortcode
     */
    public function test_event_rsvp_form_shortcode() {
        $event_id = Test_Helper::create_test_event();

        $output = do_shortcode( "[cp_event_rsvp event_id=\"{$event_id}\"]" );

        $this->assertNotEmpty( $output );
        $this->assertStringContainsString( 'form', strtolower( $output ) );
    }

    /**
     * Test duplicate RSVP prevention
     */
    public function test_duplicate_rsvp_prevention() {
        $event_id = Test_Helper::create_test_event();
        $email = 'duplicate@example.com';

        // Create first RSVP
        $rsvp_id_1 = Test_Helper::create_test_rsvp( array(
            'event_id' => $event_id,
            'email'    => $email,
        ) );

        $this->assertGreaterThan( 0, $rsvp_id_1 );

        // Attempt duplicate RSVP
        $rsvp_id_2 = Test_Helper::create_test_rsvp( array(
            'event_id' => $event_id,
            'email'    => $email,
        ) );

        // Should either update existing or return error
        $this->assertTrue(
            $rsvp_id_2 === $rsvp_id_1 || is_wp_error( $rsvp_id_2 ),
            'Duplicate RSVPs should be handled'
        );
    }

    /**
     * Test event map integration
     */
    public function test_event_map_shortcode() {
        $event_id = Test_Helper::create_test_event( array(
            'event_location' => '123 Main St, Springfield',
        ) );

        $output = do_shortcode( "[cp_event_map event_id=\"{$event_id}\"]" );

        $this->assertNotEmpty( $output, 'Event map should produce output' );
    }
}
