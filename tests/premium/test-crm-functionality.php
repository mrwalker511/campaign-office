<?php
/**
 * CRM Premium Features Tests
 *
 * Tests for CRM contact management, segmentation, and interactions
 *
 * @package CampaignOffice\Tests\Premium
 */

namespace CampaignOffice\Tests\Premium;

use WP_UnitTestCase;
use CampaignOffice\Tests\Test_Helper;

/**
 * CRM Functionality Test Class
 */
class Test_CRM_Functionality extends WP_UnitTestCase {

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
     * Test CRM database tables exist
     */
    public function test_crm_tables_exist() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'cp_crm_contacts',
            $wpdb->prefix . 'cp_crm_interactions',
            $wpdb->prefix . 'cp_crm_segments',
            $wpdb->prefix . 'cp_crm_tags',
        );

        foreach ( $tables as $table ) {
            $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
            $this->assertEquals( $table, $exists, "Table {$table} should exist" );
        }
    }

    /**
     * Test creating a CRM contact
     */
    public function test_create_crm_contact() {
        $contact_data = array(
            'first_name' => 'Sarah',
            'last_name'  => 'Voter',
            'email'      => 'sarah@example.com',
            'phone'      => '555-9999',
            'address'    => '123 Main St',
            'city'       => 'Springfield',
            'state'      => 'IL',
            'zip'        => '62701',
        );

        $contact_id = Test_Helper::create_test_crm_contact( $contact_data );

        $this->assertGreaterThan( 0, $contact_id, 'Contact should be created' );

        $contact = Test_Helper::get_crm_contact( $contact_id );

        $this->assertEquals( 'sarah@example.com', $contact->email );
        $this->assertEquals( 'Springfield', $contact->city );
    }

    /**
     * Test contact interaction logging
     */
    public function test_log_contact_interaction() {
        $contact_id = Test_Helper::create_test_crm_contact();

        $interaction_data = array(
            'contact_id' => $contact_id,
            'type'       => 'phone_call',
            'notes'      => 'Discussed healthcare policy',
            'outcome'    => 'supportive',
            'duration'   => 5,
        );

        $interaction_id = Test_Helper::log_crm_interaction( $interaction_data );

        $this->assertGreaterThan( 0, $interaction_id, 'Interaction should be logged' );

        // Verify engagement score updated
        $contact = Test_Helper::get_crm_contact( $contact_id );
        $this->assertGreaterThan( 0, $contact->interaction_count );
    }

    /**
     * Test contact segmentation
     */
    public function test_contact_segmentation() {
        // Create contacts with different attributes
        Test_Helper::create_test_crm_contact( array(
            'city'  => 'Springfield',
            'state' => 'IL',
        ) );

        Test_Helper::create_test_crm_contact( array(
            'city'  => 'Chicago',
            'state' => 'IL',
        ) );

        // Create segment
        $segment_data = array(
            'name'     => 'Springfield Voters',
            'criteria' => json_encode( array(
                'city' => 'Springfield',
            ) ),
        );

        $segment_id = Test_Helper::create_crm_segment( $segment_data );

        $this->assertGreaterThan( 0, $segment_id );

        // Get segment contacts
        $contacts = Test_Helper::get_segment_contacts( $segment_id );

        $this->assertGreaterThan( 0, count( $contacts ) );

        foreach ( $contacts as $contact ) {
            $this->assertEquals( 'Springfield', $contact->city );
        }
    }

    /**
     * Test contact tagging
     */
    public function test_contact_tagging() {
        $contact_id = Test_Helper::create_test_crm_contact();

        // Create tag
        $tag_id = Test_Helper::create_crm_tag( array(
            'name'  => 'Donor',
            'color' => '#28a745',
        ) );

        $this->assertGreaterThan( 0, $tag_id );

        // Assign tag to contact
        $result = Test_Helper::assign_tag_to_contact( $contact_id, $tag_id );

        $this->assertTrue( $result );

        // Verify tag assigned
        $tags = Test_Helper::get_contact_tags( $contact_id );

        $this->assertCount( 1, $tags );
        $this->assertEquals( 'Donor', $tags[0]->name );
    }

    /**
     * Test bulk contact import
     */
    public function test_bulk_contact_import() {
        $csv_data = "first_name,last_name,email\nJohn,Doe,john@example.com\nJane,Smith,jane@example.com";

        $imported = Test_Helper::import_crm_contacts_csv( $csv_data );

        $this->assertGreaterThanOrEqual( 2, $imported, 'Should import 2 contacts' );
    }

    /**
     * Test contact search functionality
     */
    public function test_crm_contact_search() {
        Test_Helper::create_test_crm_contact( array(
            'first_name' => 'Michael',
            'last_name'  => 'Johnson',
            'email'      => 'michael@example.com',
        ) );

        // Search by name
        $results = Test_Helper::search_crm_contacts( 'Michael' );

        $this->assertGreaterThan( 0, count( $results ) );
        $this->assertEquals( 'Michael', $results[0]->first_name );

        // Search by email
        $results = Test_Helper::search_crm_contacts( 'michael@example.com' );

        $this->assertGreaterThan( 0, count( $results ) );
    }

    /**
     * Test contact engagement scoring
     */
    public function test_contact_engagement_scoring() {
        $contact_id = Test_Helper::create_test_crm_contact();

        // Log multiple interactions
        for ( $i = 0; $i < 5; $i++ ) {
            Test_Helper::log_crm_interaction( array(
                'contact_id' => $contact_id,
                'type'       => 'email',
            ) );
        }

        $contact = Test_Helper::get_crm_contact( $contact_id );

        $this->assertEquals( 5, $contact->interaction_count );
        $this->assertGreaterThan( 0, $contact->engagement_score );
    }

    /**
     * Test contact deduplication
     */
    public function test_contact_deduplication() {
        $email = 'duplicate@example.com';

        $contact_id_1 = Test_Helper::create_test_crm_contact( array(
            'email' => $email,
        ) );

        // Attempt to create duplicate
        $contact_id_2 = Test_Helper::create_test_crm_contact( array(
            'email' => $email,
        ) );

        // Should return same contact or update existing
        $this->assertEquals( $contact_id_1, $contact_id_2, 'Should handle duplicate emails' );
    }
}
