<?php
/**
 * Test Helper Utilities
 *
 * @package CampaignOffice\Tests
 */

namespace CampaignOffice\Tests;

/**
 * Helper class for tests
 */
class Test_Helper {

    /**
     * Create a test post
     *
     * @param array $args Post arguments.
     * @return int Post ID.
     */
    public static function create_test_post( $args = array() ) {
        $defaults = array(
            'post_title'   => 'Test Post',
            'post_content' => 'Test content',
            'post_status'  => 'publish',
            'post_type'    => 'post',
        );

        $args = wp_parse_args( $args, $defaults );
        return wp_insert_post( $args );
    }

    /**
     * Create a test user
     *
     * @param string $role User role.
     * @return int User ID.
     */
    public static function create_test_user( $role = 'subscriber' ) {
        $user_id = wp_create_user(
            'testuser_' . uniqid(),
            'password',
            'testuser_' . uniqid() . '@example.com'
        );

        if ( ! is_wp_error( $user_id ) ) {
            $user = new \WP_User( $user_id );
            $user->set_role( $role );
        }

        return $user_id;
    }

    /**
     * Create test term
     *
     * @param string $taxonomy Taxonomy name.
     * @param array  $args     Term arguments.
     * @return int Term ID.
     */
    public static function create_test_term( $taxonomy = 'category', $args = array() ) {
        $defaults = array(
            'name' => 'Test Term ' . uniqid(),
            'slug' => 'test-term-' . uniqid(),
        );

        $args = wp_parse_args( $args, $defaults );
        $term = wp_insert_term( $args['name'], $taxonomy, $args );

        return is_wp_error( $term ) ? 0 : $term['term_id'];
    }

    /**
     * Clean up test data
     */
    public static function cleanup() {
        global $wpdb;

        // Delete all posts
        $wpdb->query( "DELETE FROM $wpdb->posts WHERE post_type != 'revision'" );
        $wpdb->query( "DELETE FROM $wpdb->postmeta" );

        // Delete all terms
        $wpdb->query( "DELETE FROM $wpdb->terms WHERE term_id > 1" );
        $wpdb->query( "DELETE FROM $wpdb->term_taxonomy WHERE term_id > 1" );
        $wpdb->query( "DELETE FROM $wpdb->term_relationships" );

        // Delete all users except admin
        $wpdb->query( "DELETE FROM $wpdb->users WHERE ID > 1" );
        $wpdb->query( "DELETE FROM $wpdb->usermeta WHERE user_id > 1" );
    }

    /**
     * Get private/protected property value
     *
     * @param object $object   Object instance.
     * @param string $property Property name.
     * @return mixed Property value.
     */
    public static function get_private_property( $object, $property ) {
        $reflection = new \ReflectionClass( $object );
        $property   = $reflection->getProperty( $property );
        $property->setAccessible( true );
        return $property->getValue( $object );
    }

    /**
     * Call private/protected method
     *
     * @param object $object Object instance.
     * @param string $method Method name.
     * @param array  $args   Method arguments.
     * @return mixed Method return value.
     */
    public static function call_private_method( $object, $method, $args = array() ) {
        $reflection = new \ReflectionClass( $object );
        $method     = $reflection->getMethod( $method );
        $method->setAccessible( true );
        return $method->invokeArgs( $object, $args );
    }
}
