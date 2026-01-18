<?php
/**
 * CRM Contacts Management Class
 *
 * Handles all contact CRUD operations, search, filtering, and bulk actions
 * for the CampaignPress CRM system. Optimized for large datasets with
 * pagination and performance considerations.
 *
 * @package CampaignPress
 * @subpackage CRM
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CRM Contacts Class
 *
 * @since 1.0.0
 */
class CampaignPress_CRM_Contacts {

	/**
	 * Database object
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Contacts table name
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * CRM Database instance
	 *
	 * @var CampaignPress_CRM_Database
	 */
	private $db;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->db   = new CampaignPress_CRM_Database();
		$this->table_name = $this->db->get_table_name( 'contacts' );
	}

	/**
	 * Create a new contact
	 *
	 * @since 1.0.0
	 * @param array $data Contact data
	 * @return int|WP_Error Contact ID on success, WP_Error on failure
	 */
	public function create_contact( $data ) {
		// Identify or Create Central Contact
		global $cp_contact_manager;
		$contact_id = $cp_contact_manager->find_or_create( $data );

		if ( is_wp_error( $contact_id ) ) {
			return $contact_id;
		}

		// Check if this contact already has CRM data
		$existing_crm = $this->wpdb->get_var( $this->wpdb->prepare(
			"SELECT id FROM {$this->table_name} WHERE contact_id = %d",
			$contact_id
		) );

		if ( $existing_crm ) {
			return new WP_Error( 'duplicate_crm', __( 'This contact already exists in the CRM.', 'campaignpress' ) );
		}

		// Sanitize CRM-specific data
		$sanitized_data = $this->sanitize_contact_data( $data );
		
		// Remove core contact fields from CRM-only insert
		$core_fields = array( 'first_name', 'last_name', 'email', 'phone', 'mobile_phone', 'address_line1', 'address_line2', 'city', 'state', 'zip_code', 'country', 'external_id' );
		foreach ( $core_fields as $field ) {
			unset( $sanitized_data[ $field ] );
		}

		// Add contact_id link
		$sanitized_data['contact_id'] = $contact_id;

		// Add created_by if not set
		if ( ! isset( $sanitized_data['created_by'] ) ) {
			$sanitized_data['created_by'] = get_current_user_id();
		}

		// Calculate age from date of birth if provided
		if ( ! empty( $sanitized_data['date_of_birth'] ) && empty( $sanitized_data['age'] ) ) {
			$sanitized_data['age'] = $this->calculate_age( $sanitized_data['date_of_birth'] );
		}

		// Insert CRM-specific record
		$result = $this->wpdb->insert(
			$this->table_name,
			$sanitized_data
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to create CRM record.', 'campaignpress' ), $this->wpdb->last_error );
		}

		$crm_contact_id = $this->wpdb->insert_id;

		// Update household and districts (these now use the link to master)
		$this->assign_household( $crm_contact_id );

		// Log action
		do_action( 'cp_crm_contact_created', $crm_contact_id, $sanitized_data );

		return $crm_contact_id;
	}

	/**
	 * Update a contact
	 *
	 * @since 1.0.0
	 * @param int   $contact_id Contact ID
	 * @param array $data Contact data to update
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function update_contact( $crm_contact_id, $data ) {
		// Verify CRM contact exists
		$crm_contact = $this->wpdb->get_row( $this->wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE id = %d",
			$crm_contact_id
		) );
		
		if ( ! $crm_contact ) {
			return new WP_Error( 'not_found', __( 'CRM record not found.', 'campaignpress' ) );
		}

		// Update Central Contact Info
		global $cp_contact_manager;
		$cp_contact_manager->update_contact( $crm_contact->contact_id, $data );

		// Sanitize CRM-specific data
		$sanitized_data = $this->sanitize_contact_data( $data );
		
		// Remove core contact fields from CRM update
		$core_fields = array( 'first_name', 'last_name', 'email', 'phone', 'mobile_phone', 'address_line1', 'address_line2', 'city', 'state', 'zip_code', 'country', 'external_id' );
		foreach ( $core_fields as $field ) {
			unset( $sanitized_data[ $field ] );
		}

		// Calculate age if date of birth updated
		if ( ! empty( $sanitized_data['date_of_birth'] ) ) {
			$sanitized_data['age'] = $this->calculate_age( $sanitized_data['date_of_birth'] );
		}

		// Update CRM-specific data
		if ( ! empty( $sanitized_data ) ) {
			$result = $this->wpdb->update(
				$this->table_name,
				$sanitized_data,
				array( 'id' => $crm_contact_id )
			);

			if ( false === $result ) {
				return new WP_Error( 'db_error', __( 'Failed to update CRM record.', 'campaignpress' ), $this->wpdb->last_error );
			}
		}

		// Update household and districts if needed
		$this->assign_household( $crm_contact_id );

		// Log action
		do_action( 'cp_crm_contact_updated', $crm_contact_id, $sanitized_data );

		return true;
	}

	/**
	 * Delete a contact
	 *
	 * @since 1.0.0
	 * @param int $contact_id Contact ID
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function delete_contact( $contact_id ) {
		// Verify contact exists
		$contact = $this->get_contact( $contact_id );
		if ( ! $contact ) {
			return new WP_Error( 'not_found', __( 'Contact not found.', 'campaignpress' ) );
		}

		// Delete related data
		$this->delete_contact_relationships( $contact_id );

		// Delete CRM record
		$result = $this->wpdb->delete(
			$this->table_name,
			array( 'id' => $contact_id ),
			array( '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to delete CRM record.', 'campaignpress' ), $this->wpdb->last_error );
		}

		// Note: We typically don't delete the central contact here as they might have other links (RSVP, Donor)
		// but we could delete if this was the only reference.

		// Log action
		do_action( 'cp_crm_contact_deleted', $contact_id );

		return true;
	}

	/**
	 * Get a single contact by ID
	 *
	 * @since 1.0.0
	 * @param int $crm_contact_id CRM Contact ID
	 * @return object|null Contact object or null if not found
	 */
	public function get_contact( $crm_contact_id ) {
		$master_table = $this->wpdb->prefix . 'cp_contacts';
		$contact = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT v.*, c.first_name, c.last_name, c.email, c.phone, c.mobile_phone, c.address_line1, c.address_line2, c.city, c.state, c.zip_code, c.country, c.external_id
				 FROM {$this->table_name} v
				 JOIN {$master_table} c ON v.contact_id = c.id
				 WHERE v.id = %d",
				$crm_contact_id
			)
		);

		return $contact;
	}

	/**
	 * Get contact by email
	 *
	 * @since 1.0.0
	 * @param string $email Email address
	 * @return object|null Contact object or null if not found
	 */
	public function get_contact_by_email( $email ) {
		$master_table = $this->wpdb->prefix . 'cp_contacts';
		$contact = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT v.*, c.first_name, c.last_name, c.email, c.phone, c.mobile_phone, c.address_line1, c.address_line2, c.city, c.state, c.zip_code, c.country, c.external_id
				 FROM {$this->table_name} v
				 JOIN {$master_table} c ON v.contact_id = c.id
				 WHERE c.email = %s",
				sanitize_email( $email )
			)
		);

		return $contact;
	}

	/**
	 * Get contact by voter ID
	 *
	 * @since 1.0.0
	 * @param string $voter_id Voter ID
	 * @return object|null Contact object or null if not found
	 */
	public function get_contact_by_voter_id( $voter_id ) {
		$master_table = $this->wpdb->prefix . 'cp_contacts';
		$contact = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT v.*, c.first_name, c.last_name, c.email, c.phone, c.mobile_phone, c.address_line1, c.address_line2, c.city, c.state, c.zip_code, c.country, c.external_id
				 FROM {$this->table_name} v
				 JOIN {$master_table} c ON v.contact_id = c.id
				 WHERE v.voter_id = %s",
				sanitize_text_field( $voter_id )
			)
		);

		return $contact;
	}

	/**
	 * Get total contact count
	 *
	 * @since 1.0.0
	 * @return int Total number of contacts
	 */
	public function get_contact_count() {
		$master_table = $this->wpdb->prefix . 'cp_contacts';
		$count = $this->wpdb->get_var(
			"SELECT COUNT(*) FROM {$this->table_name} v JOIN {$master_table} c ON v.contact_id = c.id"
		);
		return (int) $count;
	}

	/**
	 * Get contacts with pagination and filtering
	 *
	 * @since 1.0.0
	 * @param array $args Query arguments
	 * @return array Array with 'contacts' and 'total' keys
	 */
	public function get_contacts( $args = array() ) {
		$defaults = array(
			'page'              => 1,
			'per_page'          => 50,
			'orderby'           => 'last_name',
			'order'             => 'ASC',
			'search'            => '',
			'state'             => '',
			'city'              => '',
			'zip_code'          => '',
			'party_affiliation' => '',
			'min_engagement'    => null,
			'max_engagement'    => null,
			'tags'              => array(),
			'segment_id'        => null,
			'is_volunteer'      => null,
			'is_donor'          => null,
			'is_likely_supporter' => null,
			'do_not_contact'    => null,
			'has_email'         => null,
			'has_phone'         => null,
			'created_after'     => null,
			'created_before'    => null,
			'last_contact_after' => null,
			'last_contact_before' => null,
			'congressional_district' => '',
			'state_house_district' => '',
			'state_senate_district' => '',
			'household_id'      => null,
		);

		$args = wp_parse_args( $args, $defaults );

		// Build WHERE clause
		$where = $this->build_where_clause( $args );

		// Calculate offset
		$offset = ( $args['page'] - 1 ) * $args['per_page'];

		// Validate orderby
		$allowed_orderby = array( 'id', 'first_name', 'last_name', 'email', 'city', 'state', 'engagement_score', 'last_contact_date', 'created_at' );
		$orderby_field = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'last_name';
		
		// Map orderby field to table alias
		$core_fields = array( 'first_name', 'last_name', 'email', 'city', 'state' );
		$alias = in_array( $orderby_field, $core_fields ) ? 'c' : 'v';
		$orderby = "{$alias}.{$orderby_field}";

		// Validate order
		$order = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		$master_table = $this->wpdb->prefix . 'cp_contacts';

		// Get total count
		$total = $this->wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name} v JOIN {$master_table} c ON v.contact_id = c.id WHERE 1=1 {$where}" );

		// Get contacts
		$contacts = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT v.*, c.first_name, c.last_name, c.email, c.phone, c.mobile_phone, c.address_line1, c.address_line2, c.city, c.state, c.zip_code, c.country, c.external_id
				 FROM {$this->table_name} v
				 JOIN {$master_table} c ON v.contact_id = c.id
				 WHERE 1=1 {$where}
				 ORDER BY {$orderby} {$order}
				 LIMIT %d OFFSET %d",
				$args['per_page'],
				$offset
			)
		);

		return array(
			'contacts' => $contacts,
			'total'    => (int) $total,
			'pages'    => ceil( $total / $args['per_page'] ),
		);
	}

	/**
	 * Build WHERE clause for contact queries
	 *
	 * @since 1.0.0
	 * @param array $args Query arguments
	 * @return string WHERE clause SQL
	 */
	private function build_where_clause( $args ) {
		$where = '';

		// Search across multiple fields
		if ( ! empty( $args['search'] ) ) {
			$search = '%' . $this->wpdb->esc_like( $args['search'] ) . '%';
			$where .= $this->wpdb->prepare(
				" AND (c.first_name LIKE %s OR c.last_name LIKE %s OR c.email LIKE %s OR c.phone LIKE %s OR c.mobile_phone LIKE %s OR c.address_line1 LIKE %s OR c.city LIKE %s OR v.voter_id LIKE %s)",
				$search, $search, $search, $search, $search, $search, $search, $search
			);
		}

		// Filter by location
		if ( ! empty( $args['state'] ) ) {
			$where .= $this->wpdb->prepare( " AND c.state = %s", $args['state'] );
		}
		if ( ! empty( $args['city'] ) ) {
			$where .= $this->wpdb->prepare( " AND c.city = %s", $args['city'] );
		}
		if ( ! empty( $args['zip_code'] ) ) {
			$where .= $this->wpdb->prepare( " AND c.zip_code = %s", $args['zip_code'] );
		}

		// Filter by districts
		if ( ! empty( $args['congressional_district'] ) ) {
			$where .= $this->wpdb->prepare( " AND v.congressional_district = %s", $args['congressional_district'] );
		}
		if ( ! empty( $args['state_house_district'] ) ) {
			$where .= $this->wpdb->prepare( " AND v.state_house_district = %s", $args['state_house_district'] );
		}
		if ( ! empty( $args['state_senate_district'] ) ) {
			$where .= $this->wpdb->prepare( " AND v.state_senate_district = %s", $args['state_senate_district'] );
		}

		// Filter by party
		if ( ! empty( $args['party_affiliation'] ) ) {
			$where .= $this->wpdb->prepare( " AND v.party_affiliation = %s", $args['party_affiliation'] );
		}

		// Filter by engagement score
		if ( null !== $args['min_engagement'] ) {
			$where .= $this->wpdb->prepare( " AND v.engagement_score >= %d", $args['min_engagement'] );
		}
		if ( null !== $args['max_engagement'] ) {
			$where .= $this->wpdb->prepare( " AND v.engagement_score <= %d", $args['max_engagement'] );
		}

		// Filter by flags
		if ( null !== $args['is_volunteer'] ) {
			$where .= $this->wpdb->prepare( " AND v.is_volunteer = %d", (int) $args['is_volunteer'] );
		}
		if ( null !== $args['is_donor'] ) {
			$where .= $this->wpdb->prepare( " AND v.is_donor = %d", (int) $args['is_donor'] );
		}
		if ( null !== $args['is_likely_supporter'] ) {
			$where .= $this->wpdb->prepare( " AND v.is_likely_supporter = %d", (int) $args['is_likely_supporter'] );
		}
		if ( null !== $args['do_not_contact'] ) {
			$where .= $this->wpdb->prepare( " AND v.do_not_contact = %d", (int) $args['do_not_contact'] );
		}

		// Filter by contact info availability
		if ( null !== $args['has_email'] ) {
			$where .= $args['has_email'] ? " AND c.email IS NOT NULL AND c.email != ''" : " AND (c.email IS NULL OR c.email = '')";
		}
		if ( null !== $args['has_phone'] ) {
			$where .= $args['has_phone'] ? " AND (c.phone IS NOT NULL AND c.phone != '' OR c.mobile_phone IS NOT NULL AND c.mobile_phone != '')" : " AND (c.phone IS NULL OR c.phone = '') AND (c.mobile_phone IS NULL OR c.mobile_phone = '')";
		}

		// Filter by dates
		if ( ! empty( $args['created_after'] ) ) {
			$where .= $this->wpdb->prepare( " AND v.created_at >= %s", $args['created_after'] );
		}
		if ( ! empty( $args['created_before'] ) ) {
			$where .= $this->wpdb->prepare( " AND v.created_at <= %s", $args['created_before'] );
		}
		if ( ! empty( $args['last_contact_after'] ) ) {
			$where .= $this->wpdb->prepare( " AND v.last_contact_date >= %s", $args['last_contact_after'] );
		}
		if ( ! empty( $args['last_contact_before'] ) ) {
			$where .= $this->wpdb->prepare( " AND v.last_contact_date <= %s", $args['last_contact_before'] );
		}

		// Filter by household
		if ( ! empty( $args['household_id'] ) ) {
			$where .= $this->wpdb->prepare( " AND v.household_id = %d", $args['household_id'] );
		}

		// Filter by tags
		if ( ! empty( $args['tags'] ) && is_array( $args['tags'] ) ) {
			$tag_ids = implode( ',', array_map( 'intval', $args['tags'] ) );
			$contact_tags_table = $this->db->get_table_name( 'contact_tags' );
			$where .= " AND v.id IN (SELECT contact_id FROM {$contact_tags_table} WHERE tag_id IN ({$tag_ids}))";
		}

		// Filter by segment
		if ( ! empty( $args['segment_id'] ) ) {
			$segment_contacts_table = $this->db->get_table_name( 'segment_contacts' );
			$where .= $this->wpdb->prepare(
				" AND v.id IN (SELECT contact_id FROM {$segment_contacts_table} WHERE segment_id = %d)",
				$args['segment_id']
			);
		}

		return $where;
	}

	/**
	 * Search contacts by various criteria
	 *
	 * @since 1.0.0
	 * @param string $query Search query
	 * @param int    $limit Results limit
	 * @return array Array of contacts
	 */
	public function search_contacts( $query, $limit = 20 ) {
		$search = '%' . $this->wpdb->esc_like( $query ) . '%';
		$master_table = $this->wpdb->prefix . 'cp_contacts';

		$contacts = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT v.*, c.first_name, c.last_name, c.email, c.phone, c.mobile_phone, c.address_line1, c.address_line2, c.city, c.state, c.zip_code, c.country, c.external_id
				 FROM {$this->table_name} v
				 JOIN {$master_table} c ON v.contact_id = c.id
				 WHERE c.first_name LIKE %s
				 OR c.last_name LIKE %s
				 OR c.email LIKE %s
				 OR c.phone LIKE %s
				 OR c.address_line1 LIKE %s
				 OR c.city LIKE %s
				 OR v.voter_id LIKE %s
				 ORDER BY c.last_name, c.first_name
				 LIMIT %d",
				$search, $search, $search, $search, $search, $search, $search, $limit
			)
		);

		return $contacts;
	}

	/**
	 * Bulk update contacts
	 *
	 * @since 1.0.0
	 * @param array $contact_ids Array of contact IDs
	 * @param array $data Data to update
	 * @return int|WP_Error Number of contacts updated or WP_Error on failure
	 */
	public function bulk_update( $contact_ids, $data ) {
		if ( empty( $contact_ids ) || ! is_array( $contact_ids ) ) {
			return new WP_Error( 'invalid_ids', __( 'Invalid contact IDs.', 'campaignpress' ) );
		}

		// Sanitize data
		$sanitized_data = $this->sanitize_contact_data( $data );

		$updated = 0;
		foreach ( $contact_ids as $contact_id ) {
			$result = $this->update_contact( $contact_id, $sanitized_data );
			if ( ! is_wp_error( $result ) ) {
				$updated++;
			}
		}

		// Log bulk action
		do_action( 'cp_crm_contacts_bulk_updated', $contact_ids, $data, $updated );

		return $updated;
	}

	/**
	 * Bulk delete contacts
	 *
	 * @since 1.0.0
	 * @param array $contact_ids Array of contact IDs
	 * @return int|WP_Error Number of contacts deleted or WP_Error on failure
	 */
	public function bulk_delete( $contact_ids ) {
		if ( empty( $contact_ids ) || ! is_array( $contact_ids ) ) {
			return new WP_Error( 'invalid_ids', __( 'Invalid contact IDs.', 'campaignpress' ) );
		}

		$deleted = 0;
		foreach ( $contact_ids as $contact_id ) {
			$result = $this->delete_contact( $contact_id );
			if ( ! is_wp_error( $result ) ) {
				$deleted++;
			}
		}

		// Log bulk action
		do_action( 'cp_crm_contacts_bulk_deleted', $contact_ids, $deleted );

		return $deleted;
	}

	/**
	 * Merge duplicate contacts
	 *
	 * @since 1.0.0
	 * @param int $primary_id Primary contact ID to keep
	 * @param int $duplicate_id Duplicate contact ID to merge and delete
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function merge_contacts( $primary_id, $duplicate_id ) {
		// Verify both contacts exist
		$primary = $this->get_contact( $primary_id );
		$duplicate = $this->get_contact( $duplicate_id );

		if ( ! $primary || ! $duplicate ) {
			return new WP_Error( 'not_found', __( 'One or both contacts not found.', 'campaignpress' ) );
		}

		// Merge data - keep primary, fill in missing fields from duplicate
		$merged_data = array();
		$contact_fields = array( 'first_name', 'last_name', 'email', 'phone', 'mobile_phone', 'address_line1', 'address_line2', 'city', 'state', 'zip_code', 'date_of_birth', 'party_affiliation', 'voter_id' );

		foreach ( $contact_fields as $field ) {
			if ( empty( $primary->$field ) && ! empty( $duplicate->$field ) ) {
				$merged_data[ $field ] = $duplicate->$field;
			}
		}

		// Update primary with merged data
		if ( ! empty( $merged_data ) ) {
			$this->update_contact( $primary_id, $merged_data );
		}

		// Reassign interactions from duplicate to primary
		$interactions_table = $this->db->get_table_name( 'interactions' );
		$this->wpdb->update(
			$interactions_table,
			array( 'contact_id' => $primary_id ),
			array( 'contact_id' => $duplicate_id ),
			array( '%d' ),
			array( '%d' )
		);

		// Reassign tags from duplicate to primary
		$contact_tags_table = $this->db->get_table_name( 'contact_tags' );
		$this->wpdb->update(
			$contact_tags_table,
			array( 'contact_id' => $primary_id ),
			array( 'contact_id' => $duplicate_id ),
			array( '%d' ),
			array( '%d' )
		);

		// Delete duplicate contact
		$this->delete_contact( $duplicate_id );

		// Log merge action
		do_action( 'cp_crm_contacts_merged', $primary_id, $duplicate_id );

		return true;
	}

	/**
	 * Check for potential duplicates
	 *
	 * @since 1.0.0
	 * @param int $contact_id Contact ID to check
	 * @return array Array of potential duplicate contact IDs
	 */
	private function check_duplicates( $contact_id ) {
		$contact = $this->get_contact( $contact_id );
		if ( ! $contact ) {
			return array();
		}

		$duplicates = array();

		// Check for exact email match
		if ( ! empty( $contact->email ) ) {
			$email_duplicates = $this->wpdb->get_col(
				$this->wpdb->prepare(
					"SELECT id FROM {$this->table_name} WHERE email = %s AND id != %d",
					$contact->email,
					$contact_id
				)
			);
			$duplicates = array_merge( $duplicates, $email_duplicates );
		}

		// Check for name + address match
		if ( ! empty( $contact->first_name ) && ! empty( $contact->last_name ) && ! empty( $contact->address_line1 ) ) {
			$name_address_duplicates = $this->wpdb->get_col(
				$this->wpdb->prepare(
					"SELECT id FROM {$this->table_name}
					WHERE first_name = %s
					AND last_name = %s
					AND address_line1 = %s
					AND id != %d",
					$contact->first_name,
					$contact->last_name,
					$contact->address_line1,
					$contact_id
				)
			);
			$duplicates = array_merge( $duplicates, $name_address_duplicates );
		}

		// Remove duplicates from array
		$duplicates = array_unique( $duplicates );

		// Create duplicate group if duplicates found
		if ( ! empty( $duplicates ) ) {
			$this->create_duplicate_group( $contact_id, $duplicates );
		}

		return $duplicates;
	}

	/**
	 * Create duplicate group
	 *
	 * @since 1.0.0
	 * @param int   $contact_id Primary contact ID
	 * @param array $duplicate_ids Duplicate contact IDs
	 * @return int|false Duplicate group ID or false on failure
	 */
	private function create_duplicate_group( $contact_id, $duplicate_ids ) {
		$duplicate_groups_table = $this->db->get_table_name( 'duplicate_groups' );

		// Create group
		$result = $this->wpdb->insert(
			$duplicate_groups_table,
			array(
				'match_type'       => 'auto_detected',
				'confidence_score' => 85,
				'is_resolved'      => 0,
			),
			array( '%s', '%d', '%d' )
		);

		if ( false === $result ) {
			return false;
		}

		$group_id = $this->wpdb->insert_id;

		// Update contacts with group ID
		$all_ids = array_merge( array( $contact_id ), $duplicate_ids );
		$ids_placeholder = implode( ',', array_fill( 0, count( $all_ids ), '%d' ) );

		$this->wpdb->query(
			$this->wpdb->prepare(
				"UPDATE {$this->table_name}
				SET duplicate_group_id = %d,
				is_primary_in_duplicate_group = CASE WHEN id = %d THEN 1 ELSE 0 END
				WHERE id IN ({$ids_placeholder})",
				array_merge( array( $group_id, $contact_id ), $all_ids )
			)
		);

		return $group_id;
	}

	/**
	 * Assign contact to household
	 *
	 * @since 1.0.0
	 * @param int $contact_id Contact ID
	 * @return int|false Household ID or false on failure
	 */
	private function assign_household( $contact_id ) {
		$contact = $this->get_contact( $contact_id );
		if ( ! $contact || empty( $contact->address_line1 ) || empty( $contact->zip_code ) ) {
			return false;
		}

		$households_table = $this->db->get_table_name( 'households' );

		// Check if household exists
		$household_id = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT id FROM {$households_table}
				WHERE address_line1 = %s
				AND city = %s
				AND zip_code = %s",
				$contact->address_line1,
				$contact->city,
				$contact->zip_code
			)
		);

		// Create household if doesn't exist
		if ( ! $household_id ) {
			$result = $this->wpdb->insert(
				$households_table,
				array(
					'address_line1' => $contact->address_line1,
					'address_line2' => $contact->address_line2,
					'city'          => $contact->city,
					'state'         => $contact->state,
					'zip_code'      => $contact->zip_code,
					'county'        => $contact->county,
				),
				array( '%s', '%s', '%s', '%s', '%s', '%s' )
			);

			if ( false === $result ) {
				return false;
			}

			$household_id = $this->wpdb->insert_id;
		}

		// Update contact with household ID
		$this->wpdb->update(
			$this->table_name,
			array( 'household_id' => $household_id ),
			array( 'id' => $contact_id ),
			array( '%d' ),
			array( '%d' )
		);

		// Update household statistics
		$this->update_household_stats( $household_id );

		return $household_id;
	}

	/**
	 * Update household statistics
	 *
	 * @since 1.0.0
	 * @param int $household_id Household ID
	 * @return bool True on success
	 */
	private function update_household_stats( $household_id ) {
		$households_table = $this->db->get_table_name( 'households' );

		// Count household members
		$stats = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT
					COUNT(*) as household_size,
					SUM(CASE WHEN voter_registration_status = 'active' THEN 1 ELSE 0 END) as registered_voters
				FROM {$this->table_name}
				WHERE household_id = %d",
				$household_id
			)
		);

		$this->wpdb->update(
			$households_table,
			array(
				'household_size'    => $stats->household_size,
				'registered_voters' => $stats->registered_voters,
			),
			array( 'id' => $household_id ),
			array( '%d', '%d' ),
			array( '%d' )
		);

		return true;
	}

	/**
	 * Delete contact relationships
	 *
	 * @since 1.0.0
	 * @param int $contact_id Contact ID
	 * @return void
	 */
	private function delete_contact_relationships( $contact_id ) {
		// Delete interactions
		$interactions_table = $this->db->get_table_name( 'interactions' );
		$this->wpdb->delete( $interactions_table, array( 'contact_id' => $contact_id ), array( '%d' ) );

		// Delete tags
		$contact_tags_table = $this->db->get_table_name( 'contact_tags' );
		$this->wpdb->delete( $contact_tags_table, array( 'contact_id' => $contact_id ), array( '%d' ) );

		// Delete custom field values
		$custom_field_values_table = $this->db->get_table_name( 'custom_field_values' );
		$this->wpdb->delete( $custom_field_values_table, array( 'contact_id' => $contact_id ), array( '%d' ) );

		// Delete segment relationships
		$segment_contacts_table = $this->db->get_table_name( 'segment_contacts' );
		$this->wpdb->delete( $segment_contacts_table, array( 'contact_id' => $contact_id ), array( '%d' ) );

		// Delete engagement scores
		$engagement_scores_table = $this->db->get_table_name( 'engagement_scores' );
		$this->wpdb->delete( $engagement_scores_table, array( 'contact_id' => $contact_id ), array( '%d' ) );
	}

	/**
	 * Sanitize contact data
	 *
	 * @since 1.0.0
	 * @param array $data Contact data
	 * @return array Sanitized data
	 */
	private function sanitize_contact_data( $data ) {
		$sanitized = array();

		$text_fields = array( 'external_id', 'first_name', 'last_name', 'address_line1', 'address_line2', 'city', 'state', 'zip_code', 'county', 'congressional_district', 'state_house_district', 'state_senate_district', 'precinct', 'gender', 'party_affiliation', 'voter_registration_status', 'voter_id', 'email_status', 'phone_status', 'source' );

		foreach ( $text_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $data[ $field ] );
			}
		}

		// Email
		if ( isset( $data['email'] ) ) {
			$sanitized['email'] = sanitize_email( $data['email'] );
		}

		// Phone numbers
		$phone_fields = array( 'phone', 'mobile_phone' );
		foreach ( $phone_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = preg_replace( '/[^0-9+\-\(\) ]/', '', $data[ $field ] );
			}
		}

		// Integer fields
		$int_fields = array( 'age', 'engagement_score', 'contact_count', 'support_level', 'turnout_score', 'partisan_score', 'household_id', 'duplicate_group_id', 'created_by' );
		foreach ( $int_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = absint( $data[ $field ] );
			}
		}

		// Boolean fields
		$bool_fields = array( 'do_not_contact', 'is_volunteer', 'is_donor', 'is_likely_supporter', 'is_primary_in_duplicate_group' );
		foreach ( $bool_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = (int) (bool) $data[ $field ];
			}
		}

		// Decimal fields
		if ( isset( $data['latitude'] ) ) {
			$sanitized['latitude'] = floatval( $data['latitude'] );
		}
		if ( isset( $data['longitude'] ) ) {
			$sanitized['longitude'] = floatval( $data['longitude'] );
		}

		// Text area
		if ( isset( $data['notes'] ) ) {
			$sanitized['notes'] = sanitize_textarea_field( $data['notes'] );
		}

		// Dates
		if ( isset( $data['date_of_birth'] ) ) {
			$sanitized['date_of_birth'] = sanitize_text_field( $data['date_of_birth'] );
		}
		if ( isset( $data['last_contact_date'] ) ) {
			$sanitized['last_contact_date'] = sanitize_text_field( $data['last_contact_date'] );
		}

		return $sanitized;
	}

	/**
	 * Get field formats for wpdb operations
	 *
	 * @since 1.0.0
	 * @return array Field formats
	 */
	private function get_field_formats() {
		return array(
			'external_id'        => '%s',
			'first_name'         => '%s',
			'last_name'          => '%s',
			'email'              => '%s',
			'phone'              => '%s',
			'mobile_phone'       => '%s',
			'address_line1'      => '%s',
			'address_line2'      => '%s',
			'city'               => '%s',
			'state'              => '%s',
			'zip_code'           => '%s',
			'county'             => '%s',
			'congressional_district' => '%s',
			'state_house_district' => '%s',
			'state_senate_district' => '%s',
			'precinct'           => '%s',
			'date_of_birth'      => '%s',
			'age'                => '%d',
			'gender'             => '%s',
			'party_affiliation'  => '%s',
			'voter_registration_status' => '%s',
			'voter_id'           => '%s',
			'household_id'       => '%d',
			'latitude'           => '%f',
			'longitude'          => '%f',
			'engagement_score'   => '%d',
			'last_contact_date'  => '%s',
			'contact_count'      => '%d',
			'email_status'       => '%s',
			'phone_status'       => '%s',
			'do_not_contact'     => '%d',
			'is_volunteer'       => '%d',
			'is_donor'           => '%d',
			'is_likely_supporter' => '%d',
			'support_level'      => '%d',
			'turnout_score'      => '%d',
			'partisan_score'     => '%d',
			'notes'              => '%s',
			'source'             => '%s',
			'duplicate_group_id' => '%d',
			'is_primary_in_duplicate_group' => '%d',
			'created_by'         => '%d',
		);
	}

	/**
	 * Calculate age from date of birth
	 *
	 * @since 1.0.0
	 * @param string $date_of_birth Date of birth (Y-m-d format)
	 * @return int Age in years
	 */
	private function calculate_age( $date_of_birth ) {
		$dob = new DateTime( $date_of_birth );
		$now = new DateTime();
		$age = $now->diff( $dob )->y;
		return $age;
	}

	/**
	 * Update contact engagement score
	 *
	 * @since 1.0.0
	 * @param int $contact_id Contact ID
	 * @return int New engagement score
	 */
	public function update_engagement_score( $contact_id ) {
		$score = $this->calculate_engagement_score( $contact_id );

		$this->wpdb->update(
			$this->table_name,
			array( 'engagement_score' => $score ),
			array( 'id' => $contact_id ),
			array( '%d' ),
			array( '%d' )
		);

		// Store historical score
		$engagement_scores_table = $this->db->get_table_name( 'engagement_scores' );
		$breakdown = $this->get_engagement_breakdown( $contact_id );

		$this->wpdb->insert(
			$engagement_scores_table,
			array(
				'contact_id'       => $contact_id,
				'score'            => $score,
				'score_breakdown'  => wp_json_encode( $breakdown ),
				'recency_score'    => $breakdown['recency'],
				'frequency_score'  => $breakdown['frequency'],
				'interaction_quality_score' => $breakdown['quality'],
				'response_rate'    => $breakdown['response_rate'],
				'calculation_date' => current_time( 'mysql' ),
				'algorithm_version' => '1.0',
			),
			array( '%d', '%d', '%s', '%d', '%d', '%d', '%f', '%s', '%s' )
		);

		return $score;
	}

	/**
	 * Calculate engagement score algorithm
	 *
	 * @since 1.0.0
	 * @param int $contact_id Contact ID
	 * @return int Engagement score (0-100)
	 */
	private function calculate_engagement_score( $contact_id ) {
		$breakdown = $this->get_engagement_breakdown( $contact_id );

		// Weighted average
		$score = (
			( $breakdown['recency'] * 0.3 ) +
			( $breakdown['frequency'] * 0.3 ) +
			( $breakdown['quality'] * 0.25 ) +
			( $breakdown['response_rate'] * 0.15 )
		);

		return min( 100, max( 0, round( $score ) ) );
	}

	/**
	 * Get engagement score breakdown
	 *
	 * @since 1.0.0
	 * @param int $contact_id Contact ID
	 * @return array Score breakdown components
	 */
	private function get_engagement_breakdown( $contact_id ) {
		$interactions_table = $this->db->get_table_name( 'interactions' );

		// Get interaction statistics
		$stats = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT
					COUNT(*) as total_interactions,
					MAX(interaction_date) as last_interaction,
					SUM(CASE WHEN result IN ('contacted', 'strong_support', 'support') THEN 1 ELSE 0 END) as positive_interactions,
					SUM(CASE WHEN result = 'no_answer' THEN 1 ELSE 0 END) as no_answer
				FROM {$interactions_table}
				WHERE contact_id = %d",
				$contact_id
			)
		);

		// Recency score (0-100) - based on days since last contact
		$recency_score = 0;
		if ( $stats->last_interaction ) {
			$days_since = ( strtotime( current_time( 'mysql' ) ) - strtotime( $stats->last_interaction ) ) / DAY_IN_SECONDS;
			$recency_score = max( 0, 100 - ( $days_since * 2 ) ); // Decay 2 points per day
		}

		// Frequency score (0-100) - based on number of interactions
		$frequency_score = min( 100, $stats->total_interactions * 10 );

		// Quality score (0-100) - based on positive vs total interactions
		$quality_score = $stats->total_interactions > 0 ? ( $stats->positive_interactions / $stats->total_interactions ) * 100 : 0;

		// Response rate (0-100) - based on answered vs total attempts
		$total_attempts = $stats->total_interactions;
		$answered = $total_attempts - $stats->no_answer;
		$response_rate = $total_attempts > 0 ? ( $answered / $total_attempts ) * 100 : 0;

		return array(
			'recency'       => round( $recency_score ),
			'frequency'     => round( $frequency_score ),
			'quality'       => round( $quality_score ),
			'response_rate' => round( $response_rate, 2 ),
		);
	}
}
