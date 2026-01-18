<?php
/**
 * CRM Interactions Tracking Class
 *
 * Handles logging and management of all contact interactions including
 * phone calls, text messages, door knocks, emails, and other contact methods.
 * Integrates with engagement scoring and contact history.
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
 * CRM Interactions Class
 *
 * @since 1.0.0
 */
class CampaignPress_CRM_Interactions {

	/**
	 * Database object
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Interactions table name
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
	 * Supported interaction types
	 *
	 * @var array
	 */
	private $interaction_types = array(
		'phone_call',
		'text_message',
		'door_knock',
		'email',
		'event',
		'donation',
		'volunteer_signup',
		'petition_signature',
		'survey_response',
		'social_media',
		'other',
	);

	/**
	 * Interaction results
	 *
	 * @var array
	 */
	private $interaction_results = array(
		'contacted',
		'strong_support',
		'support',
		'lean_support',
		'undecided',
		'lean_against',
		'against',
		'strong_against',
		'no_answer',
		'wrong_number',
		'do_not_contact',
		'moved',
		'deceased',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->db   = new CampaignPress_CRM_Database();
		$this->table_name = $this->db->get_table_name( 'interactions' );
	}

	/**
	 * Log a new interaction
	 *
	 * @since 1.0.0
	 * @param array $data Interaction data
	 * @return int|WP_Error Interaction ID on success, WP_Error on failure
	 */
	public function log_interaction( $data ) {
		// Validate required fields
		if ( empty( $data['contact_id'] ) ) {
			return new WP_Error( 'missing_contact', __( 'Contact ID is required.', 'campaignpress' ) );
		}

		if ( empty( $data['interaction_type'] ) ) {
			return new WP_Error( 'missing_type', __( 'Interaction type is required.', 'campaignpress' ) );
		}

		// Validate contact exists
		$contacts = new CampaignPress_CRM_Contacts();
		$contact = $contacts->get_contact( $data['contact_id'] );
		if ( ! $contact ) {
			return new WP_Error( 'invalid_contact', __( 'Contact not found.', 'campaignpress' ) );
		}

		// Validate interaction type
		if ( ! in_array( $data['interaction_type'], $this->interaction_types, true ) ) {
			return new WP_Error( 'invalid_type', __( 'Invalid interaction type.', 'campaignpress' ) );
		}

		// Sanitize data
		$sanitized_data = $this->sanitize_interaction_data( $data );

		// Set defaults
		if ( ! isset( $sanitized_data['interaction_date'] ) ) {
			$sanitized_data['interaction_date'] = current_time( 'mysql' );
		}

		if ( ! isset( $sanitized_data['user_id'] ) ) {
			$sanitized_data['user_id'] = get_current_user_id();
		}

		// Insert interaction
		$result = $this->wpdb->insert(
			$this->table_name,
			$sanitized_data,
			$this->get_field_formats()
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to log interaction.', 'campaignpress' ), $this->wpdb->last_error );
		}

		$interaction_id = $this->wpdb->insert_id;

		// Update contact statistics
		$this->update_contact_statistics( $data['contact_id'], $sanitized_data );

		// Update engagement score
		$contacts->update_engagement_score( $data['contact_id'] );

		// Handle special interaction results
		$this->handle_interaction_result( $data['contact_id'], $sanitized_data );

		// Log action
		do_action( 'cp_crm_interaction_logged', $interaction_id, $sanitized_data );

		return $interaction_id;
	}

	/**
	 * Update an interaction
	 *
	 * @since 1.0.0
	 * @param int   $interaction_id Interaction ID
	 * @param array $data Interaction data to update
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function update_interaction( $interaction_id, $data ) {
		// Verify interaction exists
		$interaction = $this->get_interaction( $interaction_id );
		if ( ! $interaction ) {
			return new WP_Error( 'not_found', __( 'Interaction not found.', 'campaignpress' ) );
		}

		// Sanitize data
		$sanitized_data = $this->sanitize_interaction_data( $data );

		// Update interaction
		$result = $this->wpdb->update(
			$this->table_name,
			$sanitized_data,
			array( 'id' => $interaction_id ),
			$this->get_field_formats(),
			array( '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to update interaction.', 'campaignpress' ), $this->wpdb->last_error );
		}

		// Update contact statistics
		$this->update_contact_statistics( $interaction->contact_id );

		// Recalculate engagement score
		$contacts = new CampaignPress_CRM_Contacts();
		$contacts->update_engagement_score( $interaction->contact_id );

		// Log action
		do_action( 'cp_crm_interaction_updated', $interaction_id, $sanitized_data );

		return true;
	}

	/**
	 * Delete an interaction
	 *
	 * @since 1.0.0
	 * @param int $interaction_id Interaction ID
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function delete_interaction( $interaction_id ) {
		// Verify interaction exists
		$interaction = $this->get_interaction( $interaction_id );
		if ( ! $interaction ) {
			return new WP_Error( 'not_found', __( 'Interaction not found.', 'campaignpress' ) );
		}

		$contact_id = $interaction->contact_id;

		// Delete interaction
		$result = $this->wpdb->delete(
			$this->table_name,
			array( 'id' => $interaction_id ),
			array( '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to delete interaction.', 'campaignpress' ), $this->wpdb->last_error );
		}

		// Update contact statistics
		$this->update_contact_statistics( $contact_id );

		// Recalculate engagement score
		$contacts = new CampaignPress_CRM_Contacts();
		$contacts->update_engagement_score( $contact_id );

		// Log action
		do_action( 'cp_crm_interaction_deleted', $interaction_id, $contact_id );

		return true;
	}

	/**
	 * Get total interaction count
	 *
	 * @since 1.0.0
	 * @return int Total number of interactions
	 */
	public function get_interaction_count() {
		$count = $this->wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
		return (int) $count;
	}

	/**
	 * Get a single interaction by ID
	 *
	 * @since 1.0.0
	 * @param int $interaction_id Interaction ID
	 * @return object|null Interaction object or null if not found
	 */
	public function get_interaction( $interaction_id ) {
		$interaction = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table_name} WHERE id = %d",
				$interaction_id
			)
		);

		return $interaction;
	}

	/**
	 * Get interactions for a contact
	 *
	 * @since 1.0.0
	 * @param int   $contact_id Contact ID
	 * @param array $args Query arguments
	 * @return array Array with 'interactions' and 'total' keys
	 */
	public function get_contact_interactions( $contact_id, $args = array() ) {
		$defaults = array(
			'page'             => 1,
			'per_page'         => 50,
			'orderby'          => 'interaction_date',
			'order'            => 'DESC',
			'interaction_type' => '',
			'result'           => '',
			'user_id'          => null,
			'campaign_id'      => null,
			'date_from'        => null,
			'date_to'          => null,
		);

		$args = wp_parse_args( $args, $defaults );

		// Build WHERE clause
		$where = $this->wpdb->prepare( 'WHERE contact_id = %d', $contact_id );
		$where .= $this->build_where_clause( $args );

		// Calculate offset
		$offset = ( $args['page'] - 1 ) * $args['per_page'];

		// Validate orderby
		$allowed_orderby = array( 'id', 'interaction_date', 'interaction_type', 'result', 'created_at' );
		$orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'interaction_date';

		// Validate order
		$order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

		// Get total count
		$total = $this->wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name} {$where}" );

		// Get interactions
		$interactions = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table_name}
				{$where}
				ORDER BY {$orderby} {$order}
				LIMIT %d OFFSET %d",
				$args['per_page'],
				$offset
			)
		);

		return array(
			'interactions' => $interactions,
			'total'        => (int) $total,
			'pages'        => ceil( $total / $args['per_page'] ),
		);
	}

	/**
	 * Get all interactions with pagination and filtering
	 *
	 * @since 1.0.0
	 * @param array $args Query arguments
	 * @return array Array with 'interactions' and 'total' keys
	 */
	public function get_interactions( $args = array() ) {
		$defaults = array(
			'page'             => 1,
			'per_page'         => 50,
			'orderby'          => 'interaction_date',
			'order'            => 'DESC',
			'interaction_type' => '',
			'result'           => '',
			'user_id'          => null,
			'campaign_id'      => null,
			'contact_id'       => null,
			'date_from'        => null,
			'date_to'          => null,
		);

		$args = wp_parse_args( $args, $defaults );

		// Build WHERE clause
		$where = 'WHERE 1=1';
		if ( ! empty( $args['contact_id'] ) ) {
			$where .= $this->wpdb->prepare( ' AND contact_id = %d', $args['contact_id'] );
		}
		$where .= $this->build_where_clause( $args );

		// Calculate offset
		$offset = ( $args['page'] - 1 ) * $args['per_page'];

		// Validate orderby
		$allowed_orderby = array( 'id', 'interaction_date', 'interaction_type', 'result', 'created_at' );
		$orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'interaction_date';

		// Validate order
		$order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

		// Get total count
		$total = $this->wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name} {$where}" );

		// Get interactions
		$interactions = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table_name}
				{$where}
				ORDER BY {$orderby} {$order}
				LIMIT %d OFFSET %d",
				$args['per_page'],
				$offset
			)
		);

		return array(
			'interactions' => $interactions,
			'total'        => (int) $total,
			'pages'        => ceil( $total / $args['per_page'] ),
		);
	}

	/**
	 * Build WHERE clause for interaction queries
	 *
	 * @since 1.0.0
	 * @param array $args Query arguments
	 * @return string WHERE clause SQL
	 */
	private function build_where_clause( $args ) {
		$where = '';

		// Filter by interaction type
		if ( ! empty( $args['interaction_type'] ) ) {
			$where .= $this->wpdb->prepare( ' AND interaction_type = %s', $args['interaction_type'] );
		}

		// Filter by result
		if ( ! empty( $args['result'] ) ) {
			$where .= $this->wpdb->prepare( ' AND result = %s', $args['result'] );
		}

		// Filter by user
		if ( ! empty( $args['user_id'] ) ) {
			$where .= $this->wpdb->prepare( ' AND user_id = %d', $args['user_id'] );
		}

		// Filter by campaign
		if ( ! empty( $args['campaign_id'] ) ) {
			$where .= $this->wpdb->prepare( ' AND campaign_id = %d', $args['campaign_id'] );
		}

		// Filter by date range
		if ( ! empty( $args['date_from'] ) ) {
			$where .= $this->wpdb->prepare( ' AND interaction_date >= %s', $args['date_from'] );
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where .= $this->wpdb->prepare( ' AND interaction_date <= %s', $args['date_to'] );
		}

		return $where;
	}

	/**
	 * Get interaction statistics for a contact
	 *
	 * @since 1.0.0
	 * @param int $contact_id Contact ID
	 * @return array Statistics array
	 */
	public function get_contact_statistics( $contact_id ) {
		$stats = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT
					COUNT(*) as total_interactions,
					MAX(interaction_date) as last_interaction,
					MIN(interaction_date) as first_interaction,
					SUM(CASE WHEN interaction_type = 'phone_call' THEN 1 ELSE 0 END) as phone_calls,
					SUM(CASE WHEN interaction_type = 'text_message' THEN 1 ELSE 0 END) as texts,
					SUM(CASE WHEN interaction_type = 'door_knock' THEN 1 ELSE 0 END) as door_knocks,
					SUM(CASE WHEN interaction_type = 'email' THEN 1 ELSE 0 END) as emails,
					SUM(CASE WHEN result IN ('contacted', 'strong_support', 'support') THEN 1 ELSE 0 END) as positive_interactions,
					SUM(CASE WHEN result = 'no_answer' THEN 1 ELSE 0 END) as no_answer,
					SUM(CASE WHEN will_volunteer = 1 THEN 1 ELSE 0 END) as volunteer_commitments,
					SUM(CASE WHEN will_donate = 1 THEN 1 ELSE 0 END) as donation_commitments,
					SUM(CASE WHEN will_vote = 1 THEN 1 ELSE 0 END) as vote_commitments
				FROM {$this->table_name}
				WHERE contact_id = %d",
				$contact_id
			)
		);

		// Calculate response rate
		$total = (int) $stats->total_interactions;
		$answered = $total - (int) $stats->no_answer;
		$stats->response_rate = $total > 0 ? round( ( $answered / $total ) * 100, 2 ) : 0;

		return $stats;
	}

	/**
	 * Get interaction type breakdown
	 *
	 * @since 1.0.0
	 * @param array $args Optional date range filters
	 * @return array Type breakdown
	 */
	public function get_interaction_type_breakdown( $args = array() ) {
		$where = 'WHERE 1=1';

		if ( ! empty( $args['date_from'] ) ) {
			$where .= $this->wpdb->prepare( ' AND interaction_date >= %s', $args['date_from'] );
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where .= $this->wpdb->prepare( ' AND interaction_date <= %s', $args['date_to'] );
		}
		if ( ! empty( $args['campaign_id'] ) ) {
			$where .= $this->wpdb->prepare( ' AND campaign_id = %d', $args['campaign_id'] );
		}

		$results = $this->wpdb->get_results(
			"SELECT
				interaction_type,
				COUNT(*) as count,
				SUM(CASE WHEN result IN ('contacted', 'strong_support', 'support') THEN 1 ELSE 0 END) as positive_count
			FROM {$this->table_name}
			{$where}
			GROUP BY interaction_type
			ORDER BY count DESC"
		);

		return $results;
	}

	/**
	 * Get interaction results breakdown
	 *
	 * @since 1.0.0
	 * @param array $args Optional filters
	 * @return array Results breakdown
	 */
	public function get_interaction_results_breakdown( $args = array() ) {
		$where = 'WHERE 1=1';

		if ( ! empty( $args['interaction_type'] ) ) {
			$where .= $this->wpdb->prepare( ' AND interaction_type = %s', $args['interaction_type'] );
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where .= $this->wpdb->prepare( ' AND interaction_date >= %s', $args['date_from'] );
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where .= $this->wpdb->prepare( ' AND interaction_date <= %s', $args['date_to'] );
		}
		if ( ! empty( $args['campaign_id'] ) ) {
			$where .= $this->wpdb->prepare( ' AND campaign_id = %d', $args['campaign_id'] );
		}

		$results = $this->wpdb->get_results(
			"SELECT
				result,
				COUNT(*) as count,
				ROUND((COUNT(*) * 100.0 / SUM(COUNT(*)) OVER()), 2) as percentage
			FROM {$this->table_name}
			{$where}
			GROUP BY result
			ORDER BY count DESC"
		);

		return $results;
	}

	/**
	 * Get daily interaction counts
	 *
	 * @since 1.0.0
	 * @param array $args Date range and filters
	 * @return array Daily counts
	 */
	public function get_daily_interaction_counts( $args = array() ) {
		$defaults = array(
			'date_from'   => date( 'Y-m-d', strtotime( '-30 days' ) ),
			'date_to'     => date( 'Y-m-d' ),
			'campaign_id' => null,
		);

		$args = wp_parse_args( $args, $defaults );

		$where = $this->wpdb->prepare( 'WHERE interaction_date >= %s AND interaction_date <= %s', $args['date_from'], $args['date_to'] );

		if ( ! empty( $args['campaign_id'] ) ) {
			$where .= $this->wpdb->prepare( ' AND campaign_id = %d', $args['campaign_id'] );
		}

		$results = $this->wpdb->get_results(
			"SELECT
				DATE(interaction_date) as date,
				COUNT(*) as count,
				COUNT(DISTINCT contact_id) as unique_contacts
			FROM {$this->table_name}
			{$where}
			GROUP BY DATE(interaction_date)
			ORDER BY date ASC"
		);

		return $results;
	}

	/**
	 * Update contact statistics after interaction
	 *
	 * @since 1.0.0
	 * @param int   $contact_id Contact ID
	 * @param array $interaction_data Optional interaction data
	 * @return bool True on success
	 */
	private function update_contact_statistics( $contact_id, $interaction_data = array() ) {
		$contacts_table = $this->db->get_table_name( 'contacts' );

		// Get latest interaction date and count
		$stats = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT
					MAX(interaction_date) as last_contact_date,
					COUNT(*) as contact_count
				FROM {$this->table_name}
				WHERE contact_id = %d",
				$contact_id
			)
		);

		// Update contact record
		$this->wpdb->update(
			$contacts_table,
			array(
				'last_contact_date' => $stats->last_contact_date,
				'contact_count'     => $stats->contact_count,
			),
			array( 'id' => $contact_id ),
			array( '%s', '%d' ),
			array( '%d' )
		);

		return true;
	}

	/**
	 * Handle special interaction results
	 *
	 * Updates contact flags based on interaction results
	 *
	 * @since 1.0.0
	 * @param int   $contact_id Contact ID
	 * @param array $interaction_data Interaction data
	 * @return bool True on success
	 */
	private function handle_interaction_result( $contact_id, $interaction_data ) {
		$contacts_table = $this->db->get_table_name( 'contacts' );
		$updates = array();

		// Update support level if provided
		if ( isset( $interaction_data['support_level'] ) ) {
			$updates['support_level'] = (int) $interaction_data['support_level'];

			// Update likely supporter flag based on support level
			if ( $interaction_data['support_level'] >= 4 ) {
				$updates['is_likely_supporter'] = 1;
			} elseif ( $interaction_data['support_level'] <= 2 ) {
				$updates['is_likely_supporter'] = 0;
			}
		}

		// Update volunteer flag
		if ( isset( $interaction_data['will_volunteer'] ) && $interaction_data['will_volunteer'] ) {
			$updates['is_volunteer'] = 1;
		}

		// Handle do not contact
		if ( isset( $interaction_data['result'] ) && $interaction_data['result'] === 'do_not_contact' ) {
			$updates['do_not_contact'] = 1;
		}

		// Update contact if there are changes
		if ( ! empty( $updates ) ) {
			$this->wpdb->update(
				$contacts_table,
				$updates,
				array( 'id' => $contact_id ),
				array_fill( 0, count( $updates ), '%d' ),
				array( '%d' )
			);
		}

		return true;
	}

	/**
	 * Sanitize interaction data
	 *
	 * @since 1.0.0
	 * @param array $data Interaction data
	 * @return array Sanitized data
	 */
	private function sanitize_interaction_data( $data ) {
		$sanitized = array();

		// Integer fields
		$int_fields = array( 'contact_id', 'duration', 'support_level', 'campaign_id', 'user_id', 'script_id' );
		foreach ( $int_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = absint( $data[ $field ] );
			}
		}

		// Boolean fields
		$bool_fields = array( 'will_volunteer', 'will_donate', 'will_vote', 'needs_ride', 'needs_absentee' );
		foreach ( $bool_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = (int) (bool) $data[ $field ];
			}
		}

		// Text fields
		$text_fields = array( 'interaction_type', 'outcome', 'result' );
		foreach ( $text_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $data[ $field ] );
			}
		}

		// Text area fields
		if ( isset( $data['notes'] ) ) {
			$sanitized['notes'] = sanitize_textarea_field( $data['notes'] );
		}
		if ( isset( $data['issue_priorities'] ) ) {
			$sanitized['issue_priorities'] = sanitize_textarea_field( $data['issue_priorities'] );
		}

		// Date field
		if ( isset( $data['interaction_date'] ) ) {
			$sanitized['interaction_date'] = sanitize_text_field( $data['interaction_date'] );
		}

		// Decimal fields (GPS coordinates)
		if ( isset( $data['location_latitude'] ) ) {
			$sanitized['location_latitude'] = floatval( $data['location_latitude'] );
		}
		if ( isset( $data['location_longitude'] ) ) {
			$sanitized['location_longitude'] = floatval( $data['location_longitude'] );
		}

		// JSON metadata
		if ( isset( $data['metadata'] ) ) {
			if ( is_array( $data['metadata'] ) ) {
				$sanitized['metadata'] = wp_json_encode( $data['metadata'] );
			} else {
				$sanitized['metadata'] = sanitize_textarea_field( $data['metadata'] );
			}
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
			'contact_id'         => '%d',
			'interaction_type'   => '%s',
			'interaction_date'   => '%s',
			'duration'           => '%d',
			'outcome'            => '%s',
			'result'             => '%s',
			'support_level'      => '%d',
			'will_volunteer'     => '%d',
			'will_donate'        => '%d',
			'will_vote'          => '%d',
			'needs_ride'         => '%d',
			'needs_absentee'     => '%d',
			'issue_priorities'   => '%s',
			'notes'              => '%s',
			'campaign_id'        => '%d',
			'user_id'            => '%d',
			'script_id'          => '%d',
			'location_latitude'  => '%f',
			'location_longitude' => '%f',
			'metadata'           => '%s',
		);
	}

	/**
	 * Get interaction types
	 *
	 * @since 1.0.0
	 * @return array Interaction types
	 */
	public function get_interaction_types() {
		return apply_filters( 'cp_crm_interaction_types', $this->interaction_types );
	}

	/**
	 * Get interaction results
	 *
	 * @since 1.0.0
	 * @return array Interaction results
	 */
	public function get_interaction_results() {
		return apply_filters( 'cp_crm_interaction_results', $this->interaction_results );
	}

	/**
	 * Bulk log interactions
	 *
	 * @since 1.0.0
	 * @param array $interactions Array of interaction data arrays
	 * @return int|WP_Error Number of interactions logged or WP_Error on failure
	 */
	public function bulk_log_interactions( $interactions ) {
		if ( empty( $interactions ) || ! is_array( $interactions ) ) {
			return new WP_Error( 'invalid_data', __( 'Invalid interactions data.', 'campaignpress' ) );
		}

		$logged = 0;
		$errors = array();

		foreach ( $interactions as $interaction_data ) {
			$result = $this->log_interaction( $interaction_data );
			if ( is_wp_error( $result ) ) {
				$errors[] = $result->get_error_message();
			} else {
				$logged++;
			}
		}

		if ( $logged === 0 ) {
			return new WP_Error( 'bulk_log_failed', __( 'Failed to log any interactions.', 'campaignpress' ), $errors );
		}

		// Log bulk action
		do_action( 'cp_crm_interactions_bulk_logged', $logged, count( $interactions ) );

		return $logged;
	}

	/**
	 * Get recent interactions across all contacts
	 *
	 * @since 1.0.0
	 * @param int $limit Number of interactions to retrieve
	 * @return array Recent interactions
	 */
	public function get_recent_interactions( $limit = 20 ) {
		$contacts_table = $this->db->get_table_name( 'contacts' );

		$interactions = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT i.*, c.first_name, c.last_name, c.email
				FROM {$this->table_name} i
				LEFT JOIN {$contacts_table} c ON i.contact_id = c.id
				ORDER BY i.interaction_date DESC
				LIMIT %d",
				$limit
			)
		);

		return $interactions;
	}
}
