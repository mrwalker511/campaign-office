<?php
/**
 * Custom Post Types Unit Tests
 *
 * Tests for custom post type registration and functionality
 *
 * @package CampaignOffice\Tests\Unit
 */

namespace CampaignOffice\Tests\Unit;

use WP_UnitTestCase;
use CampaignOffice\Tests\Test_Helper;

/**
 * Custom Post Types Test Class
 */
class Test_Custom_Post_Types extends WP_UnitTestCase {

    /**
     * Test setup
     */
    public function setUp(): void {
        parent::setUp();
        do_action( 'init' ); // Ensure CPTs are registered
    }

    /**
     * Test teardown
     */
    public function tearDown(): void {
        parent::tearDown();
        Test_Helper::cleanup();
    }

    /**
     * Test event post type registration
     */
    public function test_event_post_type_registered() {
        $post_type = get_post_type_object( 'cp_event' );

        $this->assertNotNull( $post_type, 'Event post type should be registered' );
        $this->assertEquals( 'cp_event', $post_type->name );
        $this->assertTrue( $post_type->public, 'Event should be public' );
        $this->assertTrue( $post_type->show_in_rest, 'Event should show in REST API' );
        $this->assertTrue( $post_type->has_archive, 'Event should have archive' );
    }

    /**
     * Test issue post type registration
     */
    public function test_issue_post_type_registered() {
        $post_type = get_post_type_object( 'cp_issue' );

        $this->assertNotNull( $post_type, 'Issue post type should be registered' );
        $this->assertEquals( 'cp_issue', $post_type->name );
        $this->assertTrue( $post_type->public );
        $this->assertTrue( $post_type->show_in_rest );
    }

    /**
     * Test endorsement post type registration
     */
    public function test_endorsement_post_type_registered() {
        $post_type = get_post_type_object( 'cp_endorsement' );

        $this->assertNotNull( $post_type, 'Endorsement post type should be registered' );
        $this->assertEquals( 'cp_endorsement', $post_type->name );
    }

    /**
     * Test team post type registration
     */
    public function test_team_post_type_registered() {
        $post_type = get_post_type_object( 'cp_team' );

        $this->assertNotNull( $post_type, 'Team post type should be registered' );
        $this->assertEquals( 'cp_team', $post_type->name );
    }

    /**
     * Test volunteer opportunity post type registration
     */
    public function test_volunteer_post_type_registered() {
        $post_type = get_post_type_object( 'cp_volunteer' );

        $this->assertNotNull( $post_type, 'Volunteer post type should be registered' );
        $this->assertEquals( 'cp_volunteer', $post_type->name );
    }

    /**
     * Test press release post type registration
     */
    public function test_press_release_post_type_registered() {
        $post_type = get_post_type_object( 'cp_press_release' );

        $this->assertNotNull( $post_type, 'Press release post type should be registered' );
        $this->assertEquals( 'cp_press_release', $post_type->name );
    }

    /**
     * Test creating an event
     */
    public function test_create_event() {
        $event_id = Test_Helper::create_test_post( array(
            'post_type'  => 'cp_event',
            'post_title' => 'Test Campaign Rally',
        ) );

        $this->assertGreaterThan( 0, $event_id );

        $event = get_post( $event_id );
        $this->assertEquals( 'cp_event', $event->post_type );
        $this->assertEquals( 'Test Campaign Rally', $event->post_title );
    }

    /**
     * Test event meta data
     */
    public function test_event_meta_data() {
        $event_id = Test_Helper::create_test_event();

        // Add event meta
        update_post_meta( $event_id, 'event_date', '2025-06-15' );
        update_post_meta( $event_id, 'event_time', '18:00' );
        update_post_meta( $event_id, 'event_location', 'Town Hall, Main Street' );
        update_post_meta( $event_id, 'event_rsvp_enabled', true );

        // Retrieve meta
        $date = get_post_meta( $event_id, 'event_date', true );
        $time = get_post_meta( $event_id, 'event_time', true );
        $location = get_post_meta( $event_id, 'event_location', true );
        $rsvp_enabled = get_post_meta( $event_id, 'event_rsvp_enabled', true );

        $this->assertEquals( '2025-06-15', $date );
        $this->assertEquals( '18:00', $time );
        $this->assertEquals( 'Town Hall, Main Street', $location );
        $this->assertTrue( (bool) $rsvp_enabled );
    }

    /**
     * Test creating an issue
     */
    public function test_create_issue() {
        $issue_id = Test_Helper::create_test_post( array(
            'post_type'    => 'cp_issue',
            'post_title'   => 'Healthcare Reform',
            'post_content' => 'Our healthcare reform policy...',
        ) );

        $this->assertGreaterThan( 0, $issue_id );

        $issue = get_post( $issue_id );
        $this->assertEquals( 'cp_issue', $issue->post_type );
        $this->assertEquals( 'Healthcare Reform', $issue->post_title );
    }

    /**
     * Test issue category taxonomy
     */
    public function test_issue_category_taxonomy() {
        $taxonomy = get_taxonomy( 'issue_category' );

        $this->assertNotNull( $taxonomy, 'Issue category taxonomy should exist' );
        $this->assertTrue( $taxonomy->hierarchical, 'Issue category should be hierarchical' );
        $this->assertContains( 'cp_issue', $taxonomy->object_type );
    }

    /**
     * Test assigning category to issue
     */
    public function test_assign_category_to_issue() {
        $issue_id = Test_Helper::create_test_post( array(
            'post_type'  => 'cp_issue',
            'post_title' => 'Education Policy',
        ) );

        // Create category
        $term = wp_insert_term( 'Domestic Policy', 'issue_category' );
        $this->assertNotWPError( $term );

        // Assign category
        $result = wp_set_object_terms( $issue_id, array( $term['term_id'] ), 'issue_category' );
        $this->assertNotWPError( $result );

        // Verify assignment
        $terms = wp_get_object_terms( $issue_id, 'issue_category' );
        $this->assertCount( 1, $terms );
        $this->assertEquals( 'Domestic Policy', $terms[0]->name );
    }

    /**
     * Test creating endorsement
     */
    public function test_create_endorsement() {
        $endorsement_id = Test_Helper::create_test_post( array(
            'post_type'  => 'cp_endorsement',
            'post_title' => 'John Smith Endorsement',
        ) );

        $this->assertGreaterThan( 0, $endorsement_id );

        // Add endorsement meta
        update_post_meta( $endorsement_id, 'endorser_name', 'John Smith' );
        update_post_meta( $endorsement_id, 'endorser_title', 'Mayor of Springfield' );
        update_post_meta( $endorsement_id, 'endorsement_date', '2025-03-15' );

        $name = get_post_meta( $endorsement_id, 'endorser_name', true );
        $this->assertEquals( 'John Smith', $name );
    }

    /**
     * Test creating team member
     */
    public function test_create_team_member() {
        $team_id = Test_Helper::create_test_post( array(
            'post_type'  => 'cp_team',
            'post_title' => 'Jane Doe',
        ) );

        $this->assertGreaterThan( 0, $team_id );

        // Add team member meta
        update_post_meta( $team_id, 'team_member_position', 'Campaign Manager' );
        update_post_meta( $team_id, 'team_member_email', 'jane@campaign.com' );

        $position = get_post_meta( $team_id, 'team_member_position', true );
        $this->assertEquals( 'Campaign Manager', $position );
    }

    /**
     * Test post type capabilities
     */
    public function test_post_type_capabilities() {
        $post_type = get_post_type_object( 'cp_event' );

        // Should have standard capabilities
        $this->assertNotEmpty( $post_type->cap->edit_post );
        $this->assertNotEmpty( $post_type->cap->delete_post );
        $this->assertNotEmpty( $post_type->cap->publish_posts );
    }

    /**
     * Test post type supports
     */
    public function test_post_type_supports() {
        $this->assertTrue( post_type_supports( 'cp_event', 'title' ) );
        $this->assertTrue( post_type_supports( 'cp_event', 'editor' ) );
        $this->assertTrue( post_type_supports( 'cp_event', 'thumbnail' ) );
    }

    /**
     * Test post type REST API support
     */
    public function test_post_type_rest_api() {
        $post_type = get_post_type_object( 'cp_event' );

        $this->assertTrue( $post_type->show_in_rest, 'Should show in REST API' );
        $this->assertNotEmpty( $post_type->rest_base, 'Should have REST base' );
    }

    /**
     * Test querying custom post types
     */
    public function test_query_custom_post_types() {
        // Create multiple events
        for ( $i = 1; $i <= 3; $i++ ) {
            Test_Helper::create_test_post( array(
                'post_type'  => 'cp_event',
                'post_title' => "Event {$i}",
            ) );
        }

        // Query events
        $query = new \WP_Query( array(
            'post_type'      => 'cp_event',
            'posts_per_page' => -1,
        ) );

        $this->assertGreaterThanOrEqual( 3, $query->post_count );
        $this->assertEquals( 'cp_event', $query->posts[0]->post_type );
    }

    /**
     * Test featured image support
     */
    public function test_featured_image_support() {
        $event_id = Test_Helper::create_test_event();

        // Create test attachment
        $attachment_id = Test_Helper::create_test_attachment( $event_id );

        // Set as featured image
        set_post_thumbnail( $event_id, $attachment_id );

        // Verify featured image
        $this->assertTrue( has_post_thumbnail( $event_id ) );
        $this->assertEquals( $attachment_id, get_post_thumbnail_id( $event_id ) );
    }
}
