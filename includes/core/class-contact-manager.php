<?php
/**
 * Core Contact Manager
 *
 * Handles the master contact table (cp_contacts) which serves as the
 * single source of truth for all people interacting with the campaign.
 *
 * @package CampaignPress
 * @subpackage Core
 * @since 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CampaignPress Contact Manager Class
 */
class CampaignPress_Contact_Manager {

    /**
     * Database table name
     *
     * @var string
     */
    private $table_name;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'cp_contacts';

        // Register hooks
        add_action( 'after_setup_theme', array( $this, 'create_table' ) );
    }

    /**
     * Create the master contacts table
     */
    public function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            external_id varchar(100) DEFAULT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            middle_name varchar(100) DEFAULT '',
            suffix varchar(20) DEFAULT '',
            email varchar(100) DEFAULT NULL,
            phone varchar(20) DEFAULT NULL,
            mobile_phone varchar(20) DEFAULT NULL,
            address_line1 varchar(255) DEFAULT NULL,
            address_line2 varchar(255) DEFAULT NULL,
            city varchar(100) DEFAULT NULL,
            state varchar(50) DEFAULT NULL,
            zip_code varchar(20) DEFAULT NULL,
            country varchar(50) DEFAULT 'US',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email),
            KEY name_lookup (last_name, first_name),
            KEY zip_lookup (zip_code),
            KEY external_id (external_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
        
        update_option( 'cp_contacts_db_version', '1.0.0' );
    }

    /**
     * Find or create a contact by email or other identifiers
     *
     * @param array $data Contact data
     * @return int|WP_Error Contact ID
     */
    public function find_or_create( $data ) {
        global $wpdb;

        if ( empty( $data['email'] ) && empty( $data['first_name'] ) && empty( $data['last_name'] ) ) {
            return new WP_Error( 'missing_data', __( 'Email or Name is required to identify a contact.', 'campaignpress' ) );
        }

        // Try to find by email first
        if ( ! empty( $data['email'] ) ) {
            $contact_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE email = %s",
                sanitize_email( $data['email'] )
            ) );

            if ( $contact_id ) {
                return (int) $contact_id;
            }
        }

        // Try to find by exact name and zip if email is missing or not found
        if ( ! empty( $data['first_name'] ) && ! empty( $data['last_name'] ) && ! empty( $data['zip_code'] ) ) {
            $contact_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE first_name = %s AND last_name = %s AND zip_code = %s",
                sanitize_text_field( $data['first_name'] ),
                sanitize_text_field( $data['last_name'] ),
                sanitize_text_field( $data['zip_code'] )
            ) );

            if ( $contact_id ) {
                return (int) $contact_id;
            }
        }

        // Create New Contact
        $insert_data = array(
            'first_name'    => sanitize_text_field( $data['first_name'] ?? '' ),
            'last_name'     => sanitize_text_field( $data['last_name'] ?? '' ),
            'middle_name'   => sanitize_text_field( $data['middle_name'] ?? '' ),
            'suffix'        => sanitize_text_field( $data['suffix'] ?? '' ),
            'email'         => ! empty( $data['email'] ) ? sanitize_email( $data['email'] ) : null,
            'phone'         => sanitize_text_field( $data['phone'] ?? '' ),
            'mobile_phone'  => sanitize_text_field( $data['mobile_phone'] ?? '' ),
            'address_line1' => sanitize_text_field( $data['address_line1'] ?? '' ),
            'address_line2' => sanitize_text_field( $data['address_line2'] ?? '' ),
            'city'          => sanitize_text_field( $data['city'] ?? '' ),
            'state'         => sanitize_text_field( $data['state'] ?? '' ),
            'zip_code'      => sanitize_text_field( $data['zip_code'] ?? '' ),
            'country'       => sanitize_text_field( $data['country'] ?? 'US' ),
        );

        $result = $wpdb->insert( $this->table_name, $insert_data );

        if ( false === $result ) {
            return new WP_Error( 'db_error', __( 'Failed to create contact record.', 'campaignpress' ) );
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * Get a contact by ID
     *
     * @param int $id Contact ID
     * @return object|null Contact object or null
     */
    public function get_contact( $id ) {
        $cache_key = 'cp_contact_' . $id;
        $contact = wp_cache_get( $cache_key, 'campaignpress' );

        if ( false === $contact ) {
            global $wpdb;
            $contact = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", $id ) );
            wp_cache_set( $cache_key, $contact, 'campaignpress', HOUR_IN_SECONDS );
        }

        return $contact;
    }

    /**
     * Update a contact's core info
     *
     * @param int   $id   Contact ID
     * @param array $data Contact data to update
     * @return bool True on success
     */
    public function update_contact( $id, $data ) {
        global $wpdb;

        $update_fields = array(
            'first_name', 'last_name', 'middle_name', 'suffix', 'email', 'phone', 
            'mobile_phone', 'address_line1', 'address_line2', 'city', 'state', 'zip_code', 'country'
        );

        $update_data = array();
        foreach ( $update_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $update_data[ $field ] = $data[ $field ];
            }
        }

        if ( empty( $update_data ) ) {
            return true;
        }

        $result = $wpdb->update( 
            $this->table_name, 
            $update_data, 
            array( 'id' => $id ) 
        );

        if ( false !== $result ) {
            wp_cache_delete( 'cp_contact_' . $id, 'campaignpress' );
            return true;
        }

        return false;
    }
}
