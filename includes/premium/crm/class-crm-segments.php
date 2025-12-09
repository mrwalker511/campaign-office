<?php
/**
 * CRM Segments and Tagging Class
 *
 * Handles contact segmentation, tagging, and smart grouping based on
 * various criteria including location, demographics, engagement, and
 * custom fields. Supports both static and dynamic segments.
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
 * CRM Segments Class
 *
 * @since 1.0.0
 */
class CampaignPress_CRM_Segments {

	/**
	 * Database object
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Segments table name
	 *
	 * @var string
	 */
	private $segments_table;

	/**
	 * Tags table name
	 *
	 * @var string
	 */
	private $tags_table;

	/**
	 * Segment contacts table name
	 *
	 * @var string
	 */
	private $segment_contacts_table;

	/**
	 * Contact tags table name
	 *
	 * @var string
	 */
	private $contact_tags_table;

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

		$this->segments_table          = $this->db->get_table_name( 'segments' );
		$this->tags_table              = $this->db->get_table_name( 'tags' );
		$this->segment_contacts_table  = $this->db->get_table_name( 'segment_contacts' );
		$this->contact_tags_table      = $this->db->get_table_name( 'contact_tags' );
	}

	/**
	 * Create a new segment
	 *
	 * @since 1.0.0
	 * @param array $data Segment data
	 * @return int|WP_Error Segment ID on success, WP_Error on failure
	 */
	public function create_segment( $data ) {
		// Validate required fields
		if ( empty( $data['name'] ) ) {
			return new WP_Error( 'missing_name', __( 'Segment name is required.', 'campaign-office' ) );
		}

		// Sanitize data
		$sanitized_data = array(
			'name'        => sanitize_text_field( $data['name'] ),
			'description' => isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '',
			'segment_type' => isset( $data['segment_type'] ) ? sanitize_text_field( $data['segment_type'] ) : 'dynamic',
			'criteria'    => isset( $data['criteria'] ) ? wp_json_encode( $data['criteria'] ) : null,
			'is_active'   => isset( $data['is_active'] ) ? (int) $data['is_active'] : 1,
			'created_by'  => get_current_user_id(),
		);

		// Insert segment
		$result = $this->wpdb->insert(
			$this->segments_table,
			$sanitized_data,
			array( '%s', '%s', '%s', '%s', '%d', '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to create segment.', 'campaign-office' ), $this->wpdb->last_error );
		}

		$segment_id = $this->wpdb->insert_id;

		// Calculate segment membership if dynamic
		if ( $sanitized_data['segment_type'] === 'dynamic' && ! empty( $data['criteria'] ) ) {
			$this->recalculate_segment( $segment_id );
		}

		// Log action
		do_action( 'cp_crm_segment_created', $segment_id, $sanitized_data );

		return $segment_id;
	}

	/**
	 * Update a segment
	 *
	 * @since 1.0.0
	 * @param int   $segment_id Segment ID
	 * @param array $data Segment data to update
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function update_segment( $segment_id, $data ) {
		// Verify segment exists
		$segment = $this->get_segment( $segment_id );
		if ( ! $segment ) {
			return new WP_Error( 'not_found', __( 'Segment not found.', 'campaign-office' ) );
		}

		// Sanitize data
		$sanitized_data = array();

		if ( isset( $data['name'] ) ) {
			$sanitized_data['name'] = sanitize_text_field( $data['name'] );
		}
		if ( isset( $data['description'] ) ) {
			$sanitized_data['description'] = sanitize_textarea_field( $data['description'] );
		}
		if ( isset( $data['segment_type'] ) ) {
			$sanitized_data['segment_type'] = sanitize_text_field( $data['segment_type'] );
		}
		if ( isset( $data['criteria'] ) ) {
			$sanitized_data['criteria'] = wp_json_encode( $data['criteria'] );
		}
		if ( isset( $data['is_active'] ) ) {
			$sanitized_data['is_active'] = (int) $data['is_active'];
		}

		// Update segment
		$result = $this->wpdb->update(
			$this->segments_table,
			$sanitized_data,
			array( 'id' => $segment_id ),
			array( '%s', '%s', '%s', '%s', '%d' ),
			array( '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to update segment.', 'campaign-office' ), $this->wpdb->last_error );
		}

		// Recalculate if criteria changed
		if ( isset( $data['criteria'] ) && $segment->segment_type === 'dynamic' ) {
			$this->recalculate_segment( $segment_id );
		}

		// Log action
		do_action( 'cp_crm_segment_updated', $segment_id, $sanitized_data );

		return true;
	}

	/**
	 * Delete a segment
	 *
	 * @since 1.0.0
	 * @param int $segment_id Segment ID
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function delete_segment( $segment_id ) {
		// Verify segment exists
		$segment = $this->get_segment( $segment_id );
		if ( ! $segment ) {
			return new WP_Error( 'not_found', __( 'Segment not found.', 'campaign-office' ) );
		}

		// Delete segment contacts
		$this->wpdb->delete(
			$this->segment_contacts_table,
			array( 'segment_id' => $segment_id ),
			array( '%d' )
		);

		// Delete segment
		$result = $this->wpdb->delete(
			$this->segments_table,
			array( 'id' => $segment_id ),
			array( '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to delete segment.', 'campaign-office' ), $this->wpdb->last_error );
		}

		// Log action
		do_action( 'cp_crm_segment_deleted', $segment_id );

		return true;
	}

	/**
	 * Get a single segment by ID
	 *
	 * @since 1.0.0
	 * @param int $segment_id Segment ID
	 * @return object|null Segment object or null if not found
	 */
	public function get_segment( $segment_id ) {
		$segment = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->segments_table} WHERE id = %d",
				$segment_id
			)
		);

		if ( $segment && ! empty( $segment->criteria ) ) {
			$segment->criteria = json_decode( $segment->criteria, true );
		}

		return $segment;
	}

	/**
	 * Get all segments
	 *
	 * @since 1.0.0
	 * @param array $args Query arguments
	 * @return array Array of segments
	 */
	public function get_segments( $args = array() ) {
		$defaults = array(
			'segment_type' => '',
			'is_active'    => null,
			'orderby'      => 'name',
			'order'        => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );

		// Build WHERE clause
		$where = 'WHERE 1=1';

		if ( ! empty( $args['segment_type'] ) ) {
			$where .= $this->wpdb->prepare( ' AND segment_type = %s', $args['segment_type'] );
		}

		if ( null !== $args['is_active'] ) {
			$where .= $this->wpdb->prepare( ' AND is_active = %d', (int) $args['is_active'] );
		}

		// Validate orderby
		$allowed_orderby = array( 'id', 'name', 'segment_type', 'contact_count', 'created_at' );
		$orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'name';

		// Validate order
		$order = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		// Get segments
		$segments = $this->wpdb->get_results(
			"SELECT * FROM {$this->segments_table}
			{$where}
			ORDER BY {$orderby} {$order}"
		);

		// Decode criteria
		foreach ( $segments as $segment ) {
			if ( ! empty( $segment->criteria ) ) {
				$segment->criteria = json_decode( $segment->criteria, true );
			}
		}

		return $segments;
	}

	/**
	 * Recalculate segment membership
	 *
	 * @since 1.0.0
	 * @param int $segment_id Segment ID
	 * @return int|WP_Error Number of contacts in segment or WP_Error on failure
	 */
	public function recalculate_segment( $segment_id ) {
		$segment = $this->get_segment( $segment_id );
		if ( ! $segment ) {
			return new WP_Error( 'not_found', __( 'Segment not found.', 'campaign-office' ) );
		}

		if ( $segment->segment_type !== 'dynamic' ) {
			return new WP_Error( 'invalid_type', __( 'Can only recalculate dynamic segments.', 'campaign-office' ) );
		}

		// Clear existing segment contacts
		$this->wpdb->delete(
			$this->segment_contacts_table,
			array( 'segment_id' => $segment_id ),
			array( '%d' )
		);

		// Get contacts matching criteria
		$contacts = $this->get_contacts_by_criteria( $segment->criteria );

		// Add contacts to segment
		$added = 0;
		foreach ( $contacts as $contact_id ) {
			$result = $this->wpdb->insert(
				$this->segment_contacts_table,
				array(
					'segment_id' => $segment_id,
					'contact_id' => $contact_id,
				),
				array( '%d', '%d' )
			);

			if ( false !== $result ) {
				$added++;
			}
		}

		// Update segment contact count and last calculated time
		$this->wpdb->update(
			$this->segments_table,
			array(
				'contact_count'   => $added,
				'last_calculated' => current_time( 'mysql' ),
			),
			array( 'id' => $segment_id ),
			array( '%d', '%s' ),
			array( '%d' )
		);

		// Log action
		do_action( 'cp_crm_segment_recalculated', $segment_id, $added );

		return $added;
	}

	/**
	 * Get contacts by segment criteria
	 *
	 * @since 1.0.0
	 * @param array $criteria Segment criteria
	 * @return array Array of contact IDs
	 */
	private function get_contacts_by_criteria( $criteria ) {
		if ( empty( $criteria ) ) {
			return array();
		}

		// Use the contacts class to build query
		$contacts_obj = new CampaignPress_CRM_Contacts();

		// Convert criteria to get_contacts args
		$args = $this->criteria_to_query_args( $criteria );

		// Get all matching contacts (no pagination)
		$args['per_page'] = 999999;
		$result = $contacts_obj->get_contacts( $args );

		// Extract IDs
		$contact_ids = array();
		foreach ( $result['contacts'] as $contact ) {
			$contact_ids[] = $contact->id;
		}

		return $contact_ids;
	}

	/**
	 * Convert segment criteria to query args
	 *
	 * @since 1.0.0
	 * @param array $criteria Segment criteria
	 * @return array Query args for get_contacts
	 */
	private function criteria_to_query_args( $criteria ) {
		$args = array();

		// Map criteria fields to query args
		$field_map = array(
			'state'                  => 'state',
			'city'                   => 'city',
			'zip_code'               => 'zip_code',
			'party_affiliation'      => 'party_affiliation',
			'congressional_district' => 'congressional_district',
			'state_house_district'   => 'state_house_district',
			'state_senate_district'  => 'state_senate_district',
			'is_volunteer'           => 'is_volunteer',
			'is_donor'               => 'is_donor',
			'is_likely_supporter'    => 'is_likely_supporter',
			'do_not_contact'         => 'do_not_contact',
			'has_email'              => 'has_email',
			'has_phone'              => 'has_phone',
			'min_engagement'         => 'min_engagement',
			'max_engagement'         => 'max_engagement',
			'tags'                   => 'tags',
		);

		foreach ( $field_map as $criteria_key => $arg_key ) {
			if ( isset( $criteria[ $criteria_key ] ) ) {
				$args[ $arg_key ] = $criteria[ $criteria_key ];
			}
		}

		// Handle date ranges
		if ( isset( $criteria['created_after'] ) ) {
			$args['created_after'] = $criteria['created_after'];
		}
		if ( isset( $criteria['created_before'] ) ) {
			$args['created_before'] = $criteria['created_before'];
		}
		if ( isset( $criteria['last_contact_after'] ) ) {
			$args['last_contact_after'] = $criteria['last_contact_after'];
		}
		if ( isset( $criteria['last_contact_before'] ) ) {
			$args['last_contact_before'] = $criteria['last_contact_before'];
		}

		// Handle age range
		if ( isset( $criteria['min_age'] ) || isset( $criteria['max_age'] ) ) {
			// Note: This would require additional WHERE clause building
			// For now, we'll handle this in a future enhancement
		}

		return $args;
	}

	/**
	 * Add contact to segment (static segments)
	 *
	 * @since 1.0.0
	 * @param int $segment_id Segment ID
	 * @param int $contact_id Contact ID
	 * @return int|WP_Error Relationship ID on success, WP_Error on failure
	 */
	public function add_contact_to_segment( $segment_id, $contact_id ) {
		$segment = $this->get_segment( $segment_id );
		if ( ! $segment ) {
			return new WP_Error( 'segment_not_found', __( 'Segment not found.', 'campaign-office' ) );
		}

		if ( $segment->segment_type === 'dynamic' ) {
			return new WP_Error( 'invalid_type', __( 'Cannot manually add contacts to dynamic segments.', 'campaign-office' ) );
		}

		// Check if already in segment
		$exists = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT id FROM {$this->segment_contacts_table}
				WHERE segment_id = %d AND contact_id = %d",
				$segment_id,
				$contact_id
			)
		);

		if ( $exists ) {
			return new WP_Error( 'already_exists', __( 'Contact already in segment.', 'campaign-office' ) );
		}

		// Add to segment
		$result = $this->wpdb->insert(
			$this->segment_contacts_table,
			array(
				'segment_id' => $segment_id,
				'contact_id' => $contact_id,
			),
			array( '%d', '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to add contact to segment.', 'campaign-office' ), $this->wpdb->last_error );
		}

		// Update segment contact count
		$this->update_segment_count( $segment_id );

		return $this->wpdb->insert_id;
	}

	/**
	 * Remove contact from segment
	 *
	 * @since 1.0.0
	 * @param int $segment_id Segment ID
	 * @param int $contact_id Contact ID
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function remove_contact_from_segment( $segment_id, $contact_id ) {
		$segment = $this->get_segment( $segment_id );
		if ( ! $segment ) {
			return new WP_Error( 'segment_not_found', __( 'Segment not found.', 'campaign-office' ) );
		}

		if ( $segment->segment_type === 'dynamic' ) {
			return new WP_Error( 'invalid_type', __( 'Cannot manually remove contacts from dynamic segments.', 'campaign-office' ) );
		}

		$result = $this->wpdb->delete(
			$this->segment_contacts_table,
			array(
				'segment_id' => $segment_id,
				'contact_id' => $contact_id,
			),
			array( '%d', '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to remove contact from segment.', 'campaign-office' ), $this->wpdb->last_error );
		}

		// Update segment contact count
		$this->update_segment_count( $segment_id );

		return true;
	}

	/**
	 * Get contacts in segment
	 *
	 * @since 1.0.0
	 * @param int   $segment_id Segment ID
	 * @param array $args Pagination args
	 * @return array Array with 'contacts' and 'total' keys
	 */
	public function get_segment_contacts( $segment_id, $args = array() ) {
		$defaults = array(
			'page'     => 1,
			'per_page' => 50,
			'orderby'  => 'last_name',
			'order'    => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );

		$contacts_table = $this->db->get_table_name( 'contacts' );
		$offset = ( $args['page'] - 1 ) * $args['per_page'];

		// Validate orderby
		$allowed_orderby = array( 'id', 'first_name', 'last_name', 'email', 'engagement_score', 'created_at' );
		$orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'last_name';

		// Validate order
		$order = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		// Get total count
		$total = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->segment_contacts_table}
				WHERE segment_id = %d",
				$segment_id
			)
		);

		// Get contacts
		$contacts = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT c.* FROM {$contacts_table} c
				INNER JOIN {$this->segment_contacts_table} sc ON c.id = sc.contact_id
				WHERE sc.segment_id = %d
				ORDER BY c.{$orderby} {$order}
				LIMIT %d OFFSET %d",
				$segment_id,
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
	 * Update segment contact count
	 *
	 * @since 1.0.0
	 * @param int $segment_id Segment ID
	 * @return bool True on success
	 */
	private function update_segment_count( $segment_id ) {
		$count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->segment_contacts_table}
				WHERE segment_id = %d",
				$segment_id
			)
		);

		$this->wpdb->update(
			$this->segments_table,
			array( 'contact_count' => $count ),
			array( 'id' => $segment_id ),
			array( '%d' ),
			array( '%d' )
		);

		return true;
	}

	/**
	 * Create a new tag
	 *
	 * @since 1.0.0
	 * @param array $data Tag data
	 * @return int|WP_Error Tag ID on success, WP_Error on failure
	 */
	public function create_tag( $data ) {
		// Validate required fields
		if ( empty( $data['name'] ) ) {
			return new WP_Error( 'missing_name', __( 'Tag name is required.', 'campaign-office' ) );
		}

		// Generate slug
		$slug = isset( $data['slug'] ) ? sanitize_title( $data['slug'] ) : sanitize_title( $data['name'] );

		// Check for duplicate slug
		$exists = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT id FROM {$this->tags_table} WHERE slug = %s",
				$slug
			)
		);

		if ( $exists ) {
			return new WP_Error( 'duplicate_slug', __( 'A tag with this slug already exists.', 'campaign-office' ) );
		}

		// Sanitize data
		$sanitized_data = array(
			'name'          => sanitize_text_field( $data['name'] ),
			'slug'          => $slug,
			'description'   => isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '',
			'color'         => isset( $data['color'] ) ? sanitize_hex_color( $data['color'] ) : '#3498db',
			'icon'          => isset( $data['icon'] ) ? sanitize_text_field( $data['icon'] ) : '',
			'tag_type'      => isset( $data['tag_type'] ) ? sanitize_text_field( $data['tag_type'] ) : 'general',
			'is_system'     => isset( $data['is_system'] ) ? (int) $data['is_system'] : 0,
			'display_order' => isset( $data['display_order'] ) ? absint( $data['display_order'] ) : 0,
			'created_by'    => get_current_user_id(),
		);

		// Insert tag
		$result = $this->wpdb->insert(
			$this->tags_table,
			$sanitized_data,
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to create tag.', 'campaign-office' ), $this->wpdb->last_error );
		}

		$tag_id = $this->wpdb->insert_id;

		// Log action
		do_action( 'cp_crm_tag_created', $tag_id, $sanitized_data );

		return $tag_id;
	}

	/**
	 * Update a tag
	 *
	 * @since 1.0.0
	 * @param int   $tag_id Tag ID
	 * @param array $data Tag data to update
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function update_tag( $tag_id, $data ) {
		// Verify tag exists
		$tag = $this->get_tag( $tag_id );
		if ( ! $tag ) {
			return new WP_Error( 'not_found', __( 'Tag not found.', 'campaign-office' ) );
		}

		// Prevent editing system tags
		if ( $tag->is_system ) {
			return new WP_Error( 'system_tag', __( 'Cannot edit system tags.', 'campaign-office' ) );
		}

		// Sanitize data
		$sanitized_data = array();

		if ( isset( $data['name'] ) ) {
			$sanitized_data['name'] = sanitize_text_field( $data['name'] );
		}
		if ( isset( $data['slug'] ) ) {
			$sanitized_data['slug'] = sanitize_title( $data['slug'] );
		}
		if ( isset( $data['description'] ) ) {
			$sanitized_data['description'] = sanitize_textarea_field( $data['description'] );
		}
		if ( isset( $data['color'] ) ) {
			$sanitized_data['color'] = sanitize_hex_color( $data['color'] );
		}
		if ( isset( $data['icon'] ) ) {
			$sanitized_data['icon'] = sanitize_text_field( $data['icon'] );
		}
		if ( isset( $data['tag_type'] ) ) {
			$sanitized_data['tag_type'] = sanitize_text_field( $data['tag_type'] );
		}
		if ( isset( $data['display_order'] ) ) {
			$sanitized_data['display_order'] = absint( $data['display_order'] );
		}

		// Update tag
		$result = $this->wpdb->update(
			$this->tags_table,
			$sanitized_data,
			array( 'id' => $tag_id ),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%d' ),
			array( '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to update tag.', 'campaign-office' ), $this->wpdb->last_error );
		}

		// Log action
		do_action( 'cp_crm_tag_updated', $tag_id, $sanitized_data );

		return true;
	}

	/**
	 * Delete a tag
	 *
	 * @since 1.0.0
	 * @param int $tag_id Tag ID
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function delete_tag( $tag_id ) {
		// Verify tag exists
		$tag = $this->get_tag( $tag_id );
		if ( ! $tag ) {
			return new WP_Error( 'not_found', __( 'Tag not found.', 'campaign-office' ) );
		}

		// Prevent deleting system tags
		if ( $tag->is_system ) {
			return new WP_Error( 'system_tag', __( 'Cannot delete system tags.', 'campaign-office' ) );
		}

		// Delete contact tags
		$this->wpdb->delete(
			$this->contact_tags_table,
			array( 'tag_id' => $tag_id ),
			array( '%d' )
		);

		// Delete tag
		$result = $this->wpdb->delete(
			$this->tags_table,
			array( 'id' => $tag_id ),
			array( '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to delete tag.', 'campaign-office' ), $this->wpdb->last_error );
		}

		// Log action
		do_action( 'cp_crm_tag_deleted', $tag_id );

		return true;
	}

	/**
	 * Get a single tag by ID
	 *
	 * @since 1.0.0
	 * @param int $tag_id Tag ID
	 * @return object|null Tag object or null if not found
	 */
	public function get_tag( $tag_id ) {
		$tag = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->tags_table} WHERE id = %d",
				$tag_id
			)
		);

		return $tag;
	}

	/**
	 * Get all tags
	 *
	 * @since 1.0.0
	 * @param array $args Query arguments
	 * @return array Array of tags
	 */
	public function get_tags( $args = array() ) {
		$defaults = array(
			'tag_type' => '',
			'orderby'  => 'display_order',
			'order'    => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );

		// Build WHERE clause
		$where = 'WHERE 1=1';

		if ( ! empty( $args['tag_type'] ) ) {
			$where .= $this->wpdb->prepare( ' AND tag_type = %s', $args['tag_type'] );
		}

		// Validate orderby
		$allowed_orderby = array( 'id', 'name', 'tag_type', 'display_order', 'created_at' );
		$orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'display_order';

		// Validate order
		$order = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		// Get tags
		$tags = $this->wpdb->get_results(
			"SELECT * FROM {$this->tags_table}
			{$where}
			ORDER BY {$orderby} {$order}, name ASC"
		);

		return $tags;
	}

	/**
	 * Add tag to contact
	 *
	 * @since 1.0.0
	 * @param int $contact_id Contact ID
	 * @param int $tag_id Tag ID
	 * @return int|WP_Error Relationship ID on success, WP_Error on failure
	 */
	public function add_tag_to_contact( $contact_id, $tag_id ) {
		// Verify tag exists
		$tag = $this->get_tag( $tag_id );
		if ( ! $tag ) {
			return new WP_Error( 'tag_not_found', __( 'Tag not found.', 'campaign-office' ) );
		}

		// Check if already tagged
		$exists = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT id FROM {$this->contact_tags_table}
				WHERE contact_id = %d AND tag_id = %d",
				$contact_id,
				$tag_id
			)
		);

		if ( $exists ) {
			return new WP_Error( 'already_exists', __( 'Contact already has this tag.', 'campaign-office' ) );
		}

		// Add tag
		$result = $this->wpdb->insert(
			$this->contact_tags_table,
			array(
				'contact_id' => $contact_id,
				'tag_id'     => $tag_id,
				'added_by'   => get_current_user_id(),
			),
			array( '%d', '%d', '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to add tag to contact.', 'campaign-office' ), $this->wpdb->last_error );
		}

		// Log action
		do_action( 'cp_crm_tag_added_to_contact', $contact_id, $tag_id );

		return $this->wpdb->insert_id;
	}

	/**
	 * Remove tag from contact
	 *
	 * @since 1.0.0
	 * @param int $contact_id Contact ID
	 * @param int $tag_id Tag ID
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function remove_tag_from_contact( $contact_id, $tag_id ) {
		$result = $this->wpdb->delete(
			$this->contact_tags_table,
			array(
				'contact_id' => $contact_id,
				'tag_id'     => $tag_id,
			),
			array( '%d', '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to remove tag from contact.', 'campaign-office' ), $this->wpdb->last_error );
		}

		// Log action
		do_action( 'cp_crm_tag_removed_from_contact', $contact_id, $tag_id );

		return true;
	}

	/**
	 * Get contact tags
	 *
	 * @since 1.0.0
	 * @param int $contact_id Contact ID
	 * @return array Array of tag objects
	 */
	public function get_contact_tags( $contact_id ) {
		$tags = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT t.* FROM {$this->tags_table} t
				INNER JOIN {$this->contact_tags_table} ct ON t.id = ct.tag_id
				WHERE ct.contact_id = %d
				ORDER BY t.display_order ASC, t.name ASC",
				$contact_id
			)
		);

		return $tags;
	}

	/**
	 * Bulk add tags to contacts
	 *
	 * @since 1.0.0
	 * @param array $contact_ids Array of contact IDs
	 * @param int   $tag_id Tag ID
	 * @return int|WP_Error Number of contacts tagged or WP_Error on failure
	 */
	public function bulk_add_tag( $contact_ids, $tag_id ) {
		if ( empty( $contact_ids ) || ! is_array( $contact_ids ) ) {
			return new WP_Error( 'invalid_ids', __( 'Invalid contact IDs.', 'campaign-office' ) );
		}

		$tagged = 0;
		foreach ( $contact_ids as $contact_id ) {
			$result = $this->add_tag_to_contact( $contact_id, $tag_id );
			if ( ! is_wp_error( $result ) ) {
				$tagged++;
			}
		}

		// Log bulk action
		do_action( 'cp_crm_tag_bulk_added', $contact_ids, $tag_id, $tagged );

		return $tagged;
	}

	/**
	 * Bulk remove tags from contacts
	 *
	 * @since 1.0.0
	 * @param array $contact_ids Array of contact IDs
	 * @param int   $tag_id Tag ID
	 * @return int|WP_Error Number of contacts untagged or WP_Error on failure
	 */
	public function bulk_remove_tag( $contact_ids, $tag_id ) {
		if ( empty( $contact_ids ) || ! is_array( $contact_ids ) ) {
			return new WP_Error( 'invalid_ids', __( 'Invalid contact IDs.', 'campaign-office' ) );
		}

		$untagged = 0;
		foreach ( $contact_ids as $contact_id ) {
			$result = $this->remove_tag_from_contact( $contact_id, $tag_id );
			if ( ! is_wp_error( $result ) ) {
				$untagged++;
			}
		}

		// Log bulk action
		do_action( 'cp_crm_tag_bulk_removed', $contact_ids, $tag_id, $untagged );

		return $untagged;
	}
}
