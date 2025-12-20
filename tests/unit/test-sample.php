<?php
/**
 * Sample Unit Test
 *
 * This is an example test file. Replace with actual tests.
 *
 * @package CampaignOffice\Tests
 */

namespace CampaignOffice\Tests\Unit;

use WP_UnitTestCase;
use CampaignOffice\Tests\Test_Helper;

/**
 * Sample test class
 */
class Test_Sample extends WP_UnitTestCase {

    /**
     * Test setup
     */
    public function setUp(): void {
        parent::setUp();
        // Setup code here
    }

    /**
     * Test teardown
     */
    public function tearDown(): void {
        parent::tearDown();
        Test_Helper::cleanup();
    }

    /**
     * Test WordPress is loaded
     */
    public function test_wordpress_is_loaded() {
        $this->assertTrue( function_exists( 'do_action' ) );
    }

    /**
     * Test theme is active
     */
    public function test_theme_is_active() {
        $theme = wp_get_theme();
        $this->assertEquals( 'Campaign Office', $theme->get( 'Name' ) );
    }

    /**
     * Test basic assertion
     */
    public function test_basic_assertion() {
        $this->assertTrue( true );
        $this->assertFalse( false );
        $this->assertEquals( 1, 1 );
        $this->assertNotEquals( 1, 2 );
    }

    /**
     * Test post creation
     */
    public function test_create_post() {
        $post_id = Test_Helper::create_test_post( array(
            'post_title' => 'Test Post Title',
        ) );

        $this->assertGreaterThan( 0, $post_id );

        $post = get_post( $post_id );
        $this->assertEquals( 'Test Post Title', $post->post_title );
        $this->assertEquals( 'publish', $post->post_status );
    }

    /**
     * Test user creation
     */
    public function test_create_user() {
        $user_id = Test_Helper::create_test_user( 'editor' );
        $this->assertGreaterThan( 0, $user_id );

        $user = get_user_by( 'id', $user_id );
        $this->assertTrue( in_array( 'editor', $user->roles, true ) );
    }
}
