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

    /* ==================== VOLUNTEER HELPERS ==================== */

    /**
     * Create test volunteer
     *
     * @param array $args Volunteer data.
     * @return int Volunteer ID.
     */
    public static function create_test_volunteer( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'first_name'   => 'Test',
            'last_name'    => 'Volunteer',
            'email'        => 'volunteer_' . uniqid() . '@example.com',
            'phone'        => '555-0000',
            'skills'       => json_encode( array( 'canvassing' ) ),
            'interests'    => json_encode( array( 'events' ) ),
            'availability' => json_encode( array( 'weekdays' ) ),
            'status'       => 'active',
        );

        $data = wp_parse_args( $args, $defaults );

        // Create contact first
        $contact_id = self::create_test_contact( $data );

        $table_name = $wpdb->prefix . 'cp_volunteers';

        $wpdb->insert(
            $table_name,
            array(
                'contact_id'   => $contact_id,
                'skills'       => $data['skills'],
                'interests'    => $data['interests'],
                'availability' => $data['availability'],
                'status'       => $data['status'],
                'created_at'   => current_time( 'mysql' ),
                'updated_at'   => current_time( 'mysql' ),
            ),
            array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        return $wpdb->insert_id;
    }

    /**
     * Get volunteer by ID
     *
     * @param int $volunteer_id Volunteer ID.
     * @return object|null Volunteer object.
     */
    public static function get_volunteer( $volunteer_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_volunteers';
        $contact_table = $wpdb->prefix . 'cp_contacts';

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT v.*, c.first_name, c.last_name, c.email, c.phone
            FROM {$table_name} v
            LEFT JOIN {$contact_table} c ON v.contact_id = c.id
            WHERE v.id = %d",
            $volunteer_id
        ) );
    }

    /**
     * Update volunteer status
     *
     * @param int    $volunteer_id Volunteer ID.
     * @param string $status       New status.
     * @return bool Success.
     */
    public static function update_volunteer_status( $volunteer_id, $status ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_volunteers';

        return $wpdb->update(
            $table_name,
            array( 'status' => $status ),
            array( 'id' => $volunteer_id ),
            array( '%s' ),
            array( '%d' )
        ) !== false;
    }

    /**
     * Search volunteers
     *
     * @param array $args Search arguments.
     * @return array Volunteers.
     */
    public static function search_volunteers( $args = array() ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_volunteers';
        $contact_table = $wpdb->prefix . 'cp_contacts';

        $where = array( '1=1' );
        $params = array();

        if ( ! empty( $args['search'] ) ) {
            $where[] = '(c.first_name LIKE %s OR c.last_name LIKE %s OR c.email LIKE %s)';
            $search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if ( ! empty( $args['status'] ) ) {
            $where[] = 'v.status = %s';
            $params[] = $args['status'];
        }

        $where_clause = implode( ' AND ', $where );
        $query = "SELECT v.*, c.first_name, c.last_name, c.email FROM {$table_name} v LEFT JOIN {$contact_table} c ON v.contact_id = c.id WHERE {$where_clause}";

        if ( ! empty( $params ) ) {
            $query = $wpdb->prepare( $query, $params );
        }

        return $wpdb->get_results( $query );
    }

    /**
     * Log volunteer hours
     *
     * @param array $data Hours data.
     * @return int Log ID.
     */
    public static function log_volunteer_hours( $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_volunteer_hours';

        $wpdb->insert(
            $table_name,
            array(
                'volunteer_id' => $data['volunteer_id'],
                'hours'        => $data['hours'] ?? 1,
                'date'         => $data['date'] ?? current_time( 'Y-m-d' ),
                'activity'     => $data['activity'] ?? 'General volunteering',
                'logged_at'    => current_time( 'mysql' ),
            )
        );

        return $wpdb->insert_id;
    }

    /**
     * Get volunteer total hours
     *
     * @param int $volunteer_id Volunteer ID.
     * @return int Total hours.
     */
    public static function get_volunteer_total_hours( $volunteer_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_volunteer_hours';

        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(hours) FROM {$table_name} WHERE volunteer_id = %d",
            $volunteer_id
        ) );
    }

    /**
     * Get volunteer leaderboard
     *
     * @param int $limit Number of volunteers.
     * @return array Leaderboard.
     */
    public static function get_volunteer_leaderboard( $limit = 10 ) {
        global $wpdb;
        $hours_table = $wpdb->prefix . 'cp_volunteer_hours';
        $volunteer_table = $wpdb->prefix . 'cp_volunteers';
        $contact_table = $wpdb->prefix . 'cp_contacts';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT v.id, c.first_name, c.last_name, SUM(h.hours) as total_hours
            FROM {$hours_table} h
            JOIN {$volunteer_table} v ON h.volunteer_id = v.id
            JOIN {$contact_table} c ON v.contact_id = c.id
            GROUP BY v.id
            ORDER BY total_hours DESC
            LIMIT %d",
            $limit
        ) );
    }

    /**
     * Authenticate volunteer
     *
     * @param string $email Email address.
     * @return object|false Volunteer object or false.
     */
    public static function authenticate_volunteer( $email ) {
        global $wpdb;
        $contact_table = $wpdb->prefix . 'cp_contacts';
        $volunteer_table = $wpdb->prefix . 'cp_volunteers';

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT v.* FROM {$volunteer_table} v
            JOIN {$contact_table} c ON v.contact_id = c.id
            WHERE c.email = %s",
            $email
        ) );
    }

    /**
     * Create volunteer shift
     *
     * @param array $data Shift data.
     * @return int Shift ID.
     */
    public static function create_volunteer_shift( $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_volunteer_shifts';

        $wpdb->insert(
            $table_name,
            array(
                'event_id'   => $data['event_id'],
                'start_time' => $data['start_time'],
                'end_time'   => $data['end_time'],
                'slots'      => $data['slots'] ?? 10,
                'created_at' => current_time( 'mysql' ),
            )
        );

        return $wpdb->insert_id;
    }

    /**
     * Sign up volunteer for shift
     *
     * @param int $volunteer_id Volunteer ID.
     * @param int $shift_id     Shift ID.
     * @return int Signup ID.
     */
    public static function signup_volunteer_for_shift( $volunteer_id, $shift_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_volunteer_shift_signups';

        $wpdb->insert(
            $table_name,
            array(
                'volunteer_id' => $volunteer_id,
                'shift_id'     => $shift_id,
                'signed_up_at' => current_time( 'mysql' ),
            )
        );

        return $wpdb->insert_id;
    }

    /**
     * Get shift signups
     *
     * @param int $shift_id Shift ID.
     * @return array Signups.
     */
    public static function get_shift_signups( $shift_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_volunteer_shift_signups';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE shift_id = %d",
            $shift_id
        ) );
    }

    /**
     * Export volunteers to CSV
     *
     * @return string CSV data.
     */
    public static function export_volunteers_csv() {
        $volunteers = self::search_volunteers();

        $csv = "first_name,last_name,email,phone,status\n";

        foreach ( $volunteers as $volunteer ) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s\n",
                $volunteer->first_name,
                $volunteer->last_name,
                $volunteer->email,
                $volunteer->phone ?? '',
                $volunteer->status
            );
        }

        return $csv;
    }

    /* ==================== EVENT HELPERS ==================== */

    /**
     * Create test event
     *
     * @param array $args Event arguments.
     * @return int Event ID.
     */
    public static function create_test_event( $args = array() ) {
        $defaults = array(
            'title'          => 'Test Event',
            'event_date'     => date( 'Y-m-d', strtotime( '+7 days' ) ),
            'event_time'     => '18:00',
            'event_location' => 'Test Venue',
            'rsvp_enabled'   => true,
            'max_attendees'  => 100,
        );

        $data = wp_parse_args( $args, $defaults );

        $event_id = self::create_test_post( array(
            'post_type'  => 'cp_event',
            'post_title' => $data['title'],
        ) );

        update_post_meta( $event_id, 'event_date', $data['event_date'] );
        update_post_meta( $event_id, 'event_time', $data['event_time'] );
        update_post_meta( $event_id, 'event_location', $data['event_location'] );
        update_post_meta( $event_id, 'event_rsvp_enabled', $data['rsvp_enabled'] );
        update_post_meta( $event_id, 'event_max_attendees', $data['max_attendees'] );

        return $event_id;
    }

    /**
     * Create test RSVP
     *
     * @param array $args RSVP data.
     * @return int RSVP ID.
     */
    public static function create_test_rsvp( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'event_id'             => 0,
            'first_name'           => 'Test',
            'last_name'            => 'Attendee',
            'email'                => 'attendee_' . uniqid() . '@example.com',
            'guests'               => 1,
            'dietary_restrictions' => '',
            'notes'                => '',
            'rsvp_status'          => 'confirmed',
        );

        $data = wp_parse_args( $args, $defaults );

        // Create contact
        $contact_id = self::create_test_contact( $data );

        $table_name = $wpdb->prefix . 'cp_event_rsvps';

        $wpdb->insert(
            $table_name,
            array(
                'event_id'             => $data['event_id'],
                'contact_id'           => $contact_id,
                'guests'               => $data['guests'],
                'rsvp_status'          => $data['rsvp_status'],
                'dietary_restrictions' => $data['dietary_restrictions'],
                'notes'                => $data['notes'],
                'created_at'           => current_time( 'mysql' ),
            )
        );

        return $wpdb->insert_id;
    }

    /**
     * Get event RSVP
     *
     * @param int $rsvp_id RSVP ID.
     * @return object|null RSVP object.
     */
    public static function get_event_rsvp( $rsvp_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_event_rsvps';
        $contact_table = $wpdb->prefix . 'cp_contacts';

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT r.*, c.first_name, c.last_name, c.email
            FROM {$table_name} r
            LEFT JOIN {$contact_table} c ON r.contact_id = c.id
            WHERE r.id = %d",
            $rsvp_id
        ) );
    }

    /**
     * Get event attendance count
     *
     * @param int $event_id Event ID.
     * @return int Attendance count.
     */
    public static function get_event_attendance_count( $event_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_event_rsvps';

        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE event_id = %d AND rsvp_status = 'confirmed'",
            $event_id
        ) );
    }

    /**
     * Check if event is full
     *
     * @param int $event_id Event ID.
     * @return bool Is full.
     */
    public static function is_event_full( $event_id ) {
        $max = (int) get_post_meta( $event_id, 'event_max_attendees', true );
        $count = self::get_event_attendance_count( $event_id );

        return $max > 0 && $count >= $max;
    }

    /**
     * Get events by month
     *
     * @param int $year  Year.
     * @param int $month Month.
     * @return array Events.
     */
    public static function get_events_by_month( $year, $month ) {
        $start_date = sprintf( '%04d-%02d-01', $year, $month );
        $end_date = date( 'Y-m-t', strtotime( $start_date ) );

        $args = array(
            'post_type'      => 'cp_event',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => 'event_date',
                    'value'   => array( $start_date, $end_date ),
                    'compare' => 'BETWEEN',
                    'type'    => 'DATE',
                ),
            ),
        );

        $query = new \WP_Query( $args );
        return $query->posts;
    }

    /**
     * Get upcoming events
     *
     * @param int $limit Number of events.
     * @return array Events.
     */
    public static function get_upcoming_events( $limit = 10 ) {
        $args = array(
            'post_type'      => 'cp_event',
            'posts_per_page' => $limit,
            'meta_query'     => array(
                array(
                    'key'     => 'event_date',
                    'value'   => current_time( 'Y-m-d' ),
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
            ),
            'meta_key'       => 'event_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
        );

        $query = new \WP_Query( $args );
        return $query->posts;
    }

    /* ==================== CRM HELPERS ==================== */

    /**
     * Create test contact
     *
     * @param array $args Contact data.
     * @return int Contact ID.
     */
    public static function create_test_contact( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'first_name' => 'Test',
            'last_name'  => 'Contact',
            'email'      => 'contact_' . uniqid() . '@example.com',
            'phone'      => '',
            'address'    => '',
            'city'       => '',
            'state'      => '',
            'zip'        => '',
        );

        $data = wp_parse_args( $args, $defaults );

        $table_name = $wpdb->prefix . 'cp_contacts';

        $wpdb->insert(
            $table_name,
            array(
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'phone'      => $data['phone'],
                'address'    => $data['address'],
                'city'       => $data['city'],
                'state'      => $data['state'],
                'zip'        => $data['zip'],
                'created_at' => current_time( 'mysql' ),
                'updated_at' => current_time( 'mysql' ),
            )
        );

        return $wpdb->insert_id;
    }

    /**
     * Create test CRM contact
     *
     * @param array $args Contact data.
     * @return int Contact ID.
     */
    public static function create_test_crm_contact( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'first_name'        => 'CRM',
            'last_name'         => 'Contact',
            'email'             => 'crm_' . uniqid() . '@example.com',
            'phone'             => '',
            'address'           => '',
            'city'              => '',
            'state'             => '',
            'zip'               => '',
            'notes'             => '',
            'engagement_score'  => 0,
            'interaction_count' => 0,
        );

        $data = wp_parse_args( $args, $defaults );

        $table_name = $wpdb->prefix . 'cp_crm_contacts';

        $wpdb->insert(
            $table_name,
            array(
                'first_name'        => $data['first_name'],
                'last_name'         => $data['last_name'],
                'email'             => $data['email'],
                'phone'             => $data['phone'],
                'address'           => $data['address'],
                'city'              => $data['city'],
                'state'             => $data['state'],
                'zip'               => $data['zip'],
                'notes'             => $data['notes'],
                'engagement_score'  => $data['engagement_score'],
                'interaction_count' => $data['interaction_count'],
                'created_at'        => current_time( 'mysql' ),
                'updated_at'        => current_time( 'mysql' ),
            )
        );

        return $wpdb->insert_id;
    }

    /**
     * Get CRM contact
     *
     * @param int $contact_id Contact ID.
     * @return object|null Contact.
     */
    public static function get_crm_contact( $contact_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_crm_contacts';

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $contact_id
        ) );
    }

    /**
     * Log CRM interaction
     *
     * @param array $data Interaction data.
     * @return int Interaction ID.
     */
    public static function log_crm_interaction( $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_crm_interactions';

        $wpdb->insert(
            $table_name,
            array(
                'contact_id' => $data['contact_id'],
                'type'       => $data['type'] ?? 'note',
                'notes'      => $data['notes'] ?? '',
                'outcome'    => $data['outcome'] ?? '',
                'duration'   => $data['duration'] ?? 0,
                'user_id'    => get_current_user_id(),
                'created_at' => current_time( 'mysql' ),
            )
        );

        $interaction_id = $wpdb->insert_id;

        // Update contact interaction count
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}cp_crm_contacts SET interaction_count = interaction_count + 1, last_interaction = NOW() WHERE id = %d",
            $data['contact_id']
        ) );

        return $interaction_id;
    }

    /**
     * Create CRM segment
     *
     * @param array $data Segment data.
     * @return int Segment ID.
     */
    public static function create_crm_segment( $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_crm_segments';

        $wpdb->insert(
            $table_name,
            array(
                'name'       => $data['name'],
                'criteria'   => $data['criteria'],
                'created_at' => current_time( 'mysql' ),
            )
        );

        return $wpdb->insert_id;
    }

    /**
     * Get segment contacts
     *
     * @param int $segment_id Segment ID.
     * @return array Contacts.
     */
    public static function get_segment_contacts( $segment_id ) {
        global $wpdb;
        $segment_table = $wpdb->prefix . 'cp_crm_segments';
        $contact_table = $wpdb->prefix . 'cp_crm_contacts';

        $segment = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$segment_table} WHERE id = %d",
            $segment_id
        ) );

        if ( ! $segment ) {
            return array();
        }

        $criteria = json_decode( $segment->criteria, true );

        $where = array();
        $params = array();

        foreach ( $criteria as $key => $value ) {
            $where[] = "$key = %s";
            $params[] = $value;
        }

        $where_clause = implode( ' AND ', $where );
        $query = "SELECT * FROM {$contact_table} WHERE {$where_clause}";

        return $wpdb->get_results( $wpdb->prepare( $query, $params ) );
    }

    /**
     * Create CRM tag
     *
     * @param array $data Tag data.
     * @return int Tag ID.
     */
    public static function create_crm_tag( $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_crm_tags';

        $wpdb->insert(
            $table_name,
            array(
                'name'       => $data['name'],
                'color'      => $data['color'] ?? '#000000',
                'created_at' => current_time( 'mysql' ),
            )
        );

        return $wpdb->insert_id;
    }

    /**
     * Assign tag to contact
     *
     * @param int $contact_id Contact ID.
     * @param int $tag_id     Tag ID.
     * @return bool Success.
     */
    public static function assign_tag_to_contact( $contact_id, $tag_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_crm_contact_tags';

        $wpdb->insert(
            $table_name,
            array(
                'contact_id' => $contact_id,
                'tag_id'     => $tag_id,
                'created_at' => current_time( 'mysql' ),
            )
        );

        return $wpdb->insert_id > 0;
    }

    /**
     * Get contact tags
     *
     * @param int $contact_id Contact ID.
     * @return array Tags.
     */
    public static function get_contact_tags( $contact_id ) {
        global $wpdb;
        $ct_table = $wpdb->prefix . 'cp_crm_contact_tags';
        $tag_table = $wpdb->prefix . 'cp_crm_tags';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT t.* FROM {$tag_table} t
            JOIN {$ct_table} ct ON t.id = ct.tag_id
            WHERE ct.contact_id = %d",
            $contact_id
        ) );
    }

    /**
     * Import CRM contacts from CSV
     *
     * @param string $csv_data CSV data.
     * @return int Number of imported contacts.
     */
    public static function import_crm_contacts_csv( $csv_data ) {
        $lines = explode( "\n", trim( $csv_data ) );
        $headers = str_getcsv( array_shift( $lines ) );

        $imported = 0;

        foreach ( $lines as $line ) {
            if ( empty( trim( $line ) ) ) {
                continue;
            }

            $data = array_combine( $headers, str_getcsv( $line ) );

            self::create_test_crm_contact( $data );
            $imported++;
        }

        return $imported;
    }

    /**
     * Search CRM contacts
     *
     * @param string $search Search term.
     * @return array Contacts.
     */
    public static function search_crm_contacts( $search ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_crm_contacts';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table_name}
            WHERE first_name LIKE %s OR last_name LIKE %s OR email LIKE %s
            LIMIT 100",
            '%' . $wpdb->esc_like( $search ) . '%',
            '%' . $wpdb->esc_like( $search ) . '%',
            '%' . $wpdb->esc_like( $search ) . '%'
        ) );
    }

    /* ==================== FEC HELPERS ==================== */

    /**
     * Record FEC contribution
     *
     * @param array $data Contribution data.
     * @return int Contribution ID.
     */
    public static function record_fec_contribution( $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_fec_contributions';

        $defaults = array(
            'amount'              => 0,
            'donor_name'          => 'Test Donor',
            'donor_email'         => 'donor_' . uniqid() . '@example.com',
            'donor_address'       => '',
            'donor_city'          => '',
            'donor_state'         => '',
            'donor_zip'           => '',
            'donor_occupation'    => '',
            'donor_employer'      => '',
            'contribution_date'   => current_time( 'Y-m-d' ),
            'federal_election_id' => 'P2024',
        );

        $data = wp_parse_args( $data, $defaults );

        $wpdb->insert(
            $table_name,
            array(
                'amount'              => $data['amount'],
                'donor_name'          => $data['donor_name'],
                'donor_email'         => $data['donor_email'],
                'donor_address'       => $data['donor_address'],
                'donor_city'          => $data['donor_city'],
                'donor_state'         => $data['donor_state'],
                'donor_zip'           => $data['donor_zip'],
                'donor_occupation'    => $data['donor_occupation'],
                'donor_employer'      => $data['donor_employer'],
                'contribution_date'   => $data['contribution_date'],
                'federal_election_id' => $data['federal_election_id'],
                'created_at'          => current_time( 'mysql' ),
            )
        );

        return $wpdb->insert_id;
    }

    /**
     * Get FEC contribution
     *
     * @param int $contribution_id Contribution ID.
     * @return object|null Contribution.
     */
    public static function get_fec_contribution( $contribution_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_fec_contributions';

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $contribution_id
        ) );
    }

    /**
     * Get donor total contributions
     *
     * @param string $donor_email Donor email.
     * @return float Total amount.
     */
    public static function get_donor_total_contributions( $donor_email ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_fec_contributions';

        return (float) $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(amount) FROM {$table_name} WHERE donor_email = %s",
            $donor_email
        ) );
    }

    /**
     * Validate FEC contribution
     *
     * @param array $data Contribution data.
     * @return bool|\WP_Error Validation result.
     */
    public static function validate_fec_contribution( $data ) {
        // Validate amount > 0
        if ( empty( $data['amount'] ) || $data['amount'] <= 0 ) {
            return new \WP_Error( 'invalid_amount', 'Amount must be greater than zero' );
        }

        // Validate occupation/employer for $200+
        if ( $data['amount'] >= 200 ) {
            if ( empty( $data['donor_occupation'] ) || empty( $data['donor_employer'] ) ) {
                return new \WP_Error( 'missing_info', 'Occupation and employer required for contributions over $200' );
            }
        }

        // Prohibit foreign contributions
        if ( ! empty( $data['donor_country'] ) && $data['donor_country'] !== 'USA' ) {
            return new \WP_Error( 'foreign_contribution', 'Foreign contributions are prohibited' );
        }

        return true;
    }

    /**
     * Get FEC audit logs
     *
     * @param array $args Query arguments.
     * @return array Audit logs.
     */
    public static function get_fec_audit_logs( $args = array() ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_fec_audit_log';

        $where = array( '1=1' );
        $params = array();

        if ( ! empty( $args['contribution_id'] ) ) {
            $where[] = 'contribution_id = %d';
            $params[] = $args['contribution_id'];
        }

        $where_clause = implode( ' AND ', $where );
        $query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY created_at DESC LIMIT 100";

        if ( ! empty( $params ) ) {
            $query = $wpdb->prepare( $query, $params );
        }

        return $wpdb->get_results( $query );
    }

    /**
     * Generate FEC report
     *
     * @param array $args Report arguments.
     * @return array Report data.
     */
    public static function generate_fec_report( $args = array() ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_fec_contributions';

        $where = array( '1=1' );
        $params = array();

        if ( ! empty( $args['start_date'] ) ) {
            $where[] = 'contribution_date >= %s';
            $params[] = $args['start_date'];
        }

        if ( ! empty( $args['end_date'] ) ) {
            $where[] = 'contribution_date <= %s';
            $params[] = $args['end_date'];
        }

        $where_clause = implode( ' AND ', $where );
        $query = "SELECT COUNT(*) as total_contributions, SUM(amount) as total_amount FROM {$table_name} WHERE {$where_clause}";

        if ( ! empty( $params ) ) {
            $query = $wpdb->prepare( $query, $params );
        }

        return (array) $wpdb->get_row( $query );
    }

    /**
     * Refund FEC contribution
     *
     * @param int    $contribution_id Contribution ID.
     * @param float  $amount          Refund amount.
     * @param string $reason          Refund reason.
     * @return int Refund ID.
     */
    public static function refund_fec_contribution( $contribution_id, $amount, $reason ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_fec_contributions';

        // Mark contribution as refunded
        $wpdb->update(
            $table_name,
            array( 'refunded' => 1, 'refund_reason' => $reason ),
            array( 'id' => $contribution_id )
        );

        return $contribution_id;
    }

    /* ==================== GENERAL HELPERS ==================== */

    /**
     * Create test attachment
     *
     * @param int $parent_id Parent post ID.
     * @return int Attachment ID.
     */
    public static function create_test_attachment( $parent_id = 0 ) {
        $filename = 'test-image.jpg';
        $upload_dir = wp_upload_dir();
        $image_path = $upload_dir['path'] . '/' . $filename;

        // Create a simple image
        $image = imagecreatetruecolor( 100, 100 );
        imagejpeg( $image, $image_path );
        imagedestroy( $image );

        $filetype = wp_check_filetype( $filename, null );

        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title'     => 'Test Image',
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        $attachment_id = wp_insert_attachment( $attachment, $image_path, $parent_id );

        require_once ABSPATH . 'wp-admin/includes/image.php';
        $metadata = wp_generate_attachment_metadata( $attachment_id, $image_path );
        wp_update_attachment_metadata( $attachment_id, $metadata );

        return $attachment_id;
    }
}
