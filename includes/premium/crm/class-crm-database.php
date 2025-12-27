<?php
/**
 * CRM Database Schema and Management Class
 *
 * Handles all database table creation, updates, and schema management
 * for the CampaignPress CRM system. Designed to handle 50K+ contacts
 * with optimized indexing and performance considerations.
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
 * CRM Database Management Class
 *
 * @since 1.0.0
 */
class CampaignPress_CRM_Database {

	/**
	 * Database version for schema updates
	 *
	 * @var string
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Table prefix for CRM tables
	 *
	 * @var string
	 */
	private $prefix;

	/**
	 * WordPress database object
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb   = $wpdb;
		$this->prefix = $wpdb->prefix . 'cp_crm_';
	}

	/**
	 * Initialize database tables
	 *
	 * Creates all required tables with proper indexing and charset
	 *
	 * @since 1.0.0
	 * @return bool True on success, false on failure
	 */
	public function create_tables() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $this->wpdb->get_charset_collate();
		$created         = true;

		// Track any errors
		$errors = array();

		try {
			// Create contacts table
			$created = $this->create_contacts_table( $charset_collate ) && $created;

			// Create interactions table
			$created = $this->create_interactions_table( $charset_collate ) && $created;

			// Create tags table
			$created = $this->create_tags_table( $charset_collate ) && $created;

			// Create contact tags relationship table
			$created = $this->create_contact_tags_table( $charset_collate ) && $created;

			// Create custom fields table
			$created = $this->create_custom_fields_table( $charset_collate ) && $created;

			// Create custom field values table
			$created = $this->create_custom_field_values_table( $charset_collate ) && $created;

			// Create households table
			$created = $this->create_households_table( $charset_collate ) && $created;

			// Create duplicate groups table
			$created = $this->create_duplicate_groups_table( $charset_collate ) && $created;

			// Create engagement scores table
			$created = $this->create_engagement_scores_table( $charset_collate ) && $created;

			// Create segments table
			$created = $this->create_segments_table( $charset_collate ) && $created;

			// Create segment contacts relationship table
			$created = $this->create_segment_contacts_table( $charset_collate ) && $created;

			// Update database version
			update_option( 'cp_crm_db_version', self::DB_VERSION );

			return $created;

		} catch ( Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'CampaignPress CRM Database Error: ' . $e->getMessage() );
		}
		return false;
		}
	}

	/**
	 * Create contacts table
	 *
	 * Primary table for storing contact/voter information
	 * Optimized for 50K+ records with proper indexing
	 *
	 * @since 1.0.0
	 * @param string $charset_collate Database charset collation
	 * @return bool True on success
	 */
	private function create_contacts_table( $charset_collate ) {
		$table_name = $this->prefix . 'contacts';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			external_id varchar(100) DEFAULT NULL,
			first_name varchar(100) DEFAULT NULL,
			last_name varchar(100) DEFAULT NULL,
			email varchar(100) DEFAULT NULL,
			phone varchar(20) DEFAULT NULL,
			mobile_phone varchar(20) DEFAULT NULL,
			address_line1 varchar(255) DEFAULT NULL,
			address_line2 varchar(255) DEFAULT NULL,
			city varchar(100) DEFAULT NULL,
			state varchar(50) DEFAULT NULL,
			zip_code varchar(20) DEFAULT NULL,
			county varchar(100) DEFAULT NULL,
			congressional_district varchar(10) DEFAULT NULL,
			state_house_district varchar(10) DEFAULT NULL,
			state_senate_district varchar(10) DEFAULT NULL,
			precinct varchar(50) DEFAULT NULL,
			date_of_birth date DEFAULT NULL,
			age int(3) DEFAULT NULL,
			gender varchar(20) DEFAULT NULL,
			party_affiliation varchar(50) DEFAULT NULL,
			voter_registration_status varchar(50) DEFAULT NULL,
			voter_id varchar(100) DEFAULT NULL,
			household_id bigint(20) UNSIGNED DEFAULT NULL,
			latitude decimal(10, 8) DEFAULT NULL,
			longitude decimal(11, 8) DEFAULT NULL,
			engagement_score int(5) DEFAULT 0,
			last_contact_date datetime DEFAULT NULL,
			contact_count int(10) DEFAULT 0,
			email_status varchar(20) DEFAULT 'unknown',
			phone_status varchar(20) DEFAULT 'unknown',
			do_not_contact tinyint(1) DEFAULT 0,
			is_volunteer tinyint(1) DEFAULT 0,
			is_donor tinyint(1) DEFAULT 0,
			is_likely_supporter tinyint(1) DEFAULT 0,
			support_level int(2) DEFAULT NULL,
			turnout_score int(3) DEFAULT NULL,
			partisan_score int(3) DEFAULT NULL,
			notes text DEFAULT NULL,
			source varchar(100) DEFAULT NULL,
			duplicate_group_id bigint(20) UNSIGNED DEFAULT NULL,
			is_primary_in_duplicate_group tinyint(1) DEFAULT 1,
			created_by bigint(20) UNSIGNED DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY email (email),
			KEY external_id (external_id),
			KEY last_name (last_name),
			KEY zip_code (zip_code),
			KEY city (city),
			KEY state (state),
			KEY congressional_district (congressional_district),
			KEY state_house_district (state_house_district),
			KEY state_senate_district (state_senate_district),
			KEY party_affiliation (party_affiliation),
			KEY household_id (household_id),
			KEY engagement_score (engagement_score),
			KEY last_contact_date (last_contact_date),
			KEY duplicate_group_id (duplicate_group_id),
			KEY voter_id (voter_id),
			KEY created_at (created_at),
			KEY name_lookup (last_name, first_name),
			KEY location_lookup (state, city, zip_code),
			KEY supporter_lookup (is_likely_supporter, support_level),
			KEY geo_lookup (latitude, longitude)
		) $charset_collate;";

		dbDelta( $sql );
		return true;
	}

	/**
	 * Create interactions table
	 *
	 * Tracks all contact interactions (calls, texts, emails, door knocks)
	 *
	 * @since 1.0.0
	 * @param string $charset_collate Database charset collation
	 * @return bool True on success
	 */
	private function create_interactions_table( $charset_collate ) {
		$table_name = $this->prefix . 'interactions';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			contact_id bigint(20) UNSIGNED NOT NULL,
			interaction_type varchar(50) NOT NULL,
			interaction_date datetime NOT NULL,
			duration int(10) DEFAULT NULL,
			outcome varchar(100) DEFAULT NULL,
			result varchar(50) DEFAULT NULL,
			support_level int(2) DEFAULT NULL,
			will_volunteer tinyint(1) DEFAULT NULL,
			will_donate tinyint(1) DEFAULT NULL,
			will_vote tinyint(1) DEFAULT NULL,
			needs_ride tinyint(1) DEFAULT NULL,
			needs_absentee tinyint(1) DEFAULT NULL,
			issue_priorities text DEFAULT NULL,
			notes text DEFAULT NULL,
			campaign_id bigint(20) UNSIGNED DEFAULT NULL,
			user_id bigint(20) UNSIGNED DEFAULT NULL,
			script_id bigint(20) UNSIGNED DEFAULT NULL,
			location_latitude decimal(10, 8) DEFAULT NULL,
			location_longitude decimal(11, 8) DEFAULT NULL,
			metadata longtext DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY contact_id (contact_id),
			KEY interaction_type (interaction_type),
			KEY interaction_date (interaction_date),
			KEY user_id (user_id),
			KEY campaign_id (campaign_id),
			KEY result (result),
			KEY created_at (created_at),
			KEY contact_type_date (contact_id, interaction_type, interaction_date)
		) $charset_collate;";

		dbDelta( $sql );
		return true;
	}

	/**
	 * Create tags table
	 *
	 * Stores tag definitions for contact categorization
	 *
	 * @since 1.0.0
	 * @param string $charset_collate Database charset collation
	 * @return bool True on success
	 */
	private function create_tags_table( $charset_collate ) {
		$table_name = $this->prefix . 'tags';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(100) NOT NULL,
			slug varchar(100) NOT NULL,
			description text DEFAULT NULL,
			color varchar(7) DEFAULT '#3498db',
			icon varchar(50) DEFAULT NULL,
			tag_type varchar(50) DEFAULT 'general',
			is_system tinyint(1) DEFAULT 0,
			display_order int(10) DEFAULT 0,
			created_by bigint(20) UNSIGNED DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug),
			KEY name (name),
			KEY tag_type (tag_type)
		) $charset_collate;";

		dbDelta( $sql );
		return true;
	}

	/**
	 * Create contact tags relationship table
	 *
	 * Many-to-many relationship between contacts and tags
	 *
	 * @since 1.0.0
	 * @param string $charset_collate Database charset collation
	 * @return bool True on success
	 */
	private function create_contact_tags_table( $charset_collate ) {
		$table_name = $this->prefix . 'contact_tags';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			contact_id bigint(20) UNSIGNED NOT NULL,
			tag_id bigint(20) UNSIGNED NOT NULL,
			added_by bigint(20) UNSIGNED DEFAULT NULL,
			added_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY contact_tag (contact_id, tag_id),
			KEY contact_id (contact_id),
			KEY tag_id (tag_id),
			KEY added_at (added_at)
		) $charset_collate;";

		dbDelta( $sql );
		return true;
	}

	/**
	 * Create custom fields table
	 *
	 * Defines custom fields for flexible voter data storage
	 *
	 * @since 1.0.0
	 * @param string $charset_collate Database charset collation
	 * @return bool True on success
	 */
	private function create_custom_fields_table( $charset_collate ) {
		$table_name = $this->prefix . 'custom_fields';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			field_name varchar(100) NOT NULL,
			field_slug varchar(100) NOT NULL,
			field_type varchar(50) NOT NULL,
			field_options longtext DEFAULT NULL,
			default_value text DEFAULT NULL,
			is_required tinyint(1) DEFAULT 0,
			is_searchable tinyint(1) DEFAULT 1,
			is_system tinyint(1) DEFAULT 0,
			display_order int(10) DEFAULT 0,
			help_text text DEFAULT NULL,
			validation_rules text DEFAULT NULL,
			created_by bigint(20) UNSIGNED DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY field_slug (field_slug),
			KEY field_type (field_type),
			KEY is_searchable (is_searchable)
		) $charset_collate;";

		dbDelta( $sql );
		return true;
	}

	/**
	 * Create custom field values table
	 *
	 * Stores custom field values for contacts
	 *
	 * @since 1.0.0
	 * @param string $charset_collate Database charset collation
	 * @return bool True on success
	 */
	private function create_custom_field_values_table( $charset_collate ) {
		$table_name = $this->prefix . 'custom_field_values';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			contact_id bigint(20) UNSIGNED NOT NULL,
			field_id bigint(20) UNSIGNED NOT NULL,
			field_value longtext DEFAULT NULL,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY contact_field (contact_id, field_id),
			KEY contact_id (contact_id),
			KEY field_id (field_id)
		) $charset_collate;";

		dbDelta( $sql );
		return true;
	}

	/**
	 * Create households table
	 *
	 * Groups contacts by household/address
	 *
	 * @since 1.0.0
	 * @param string $charset_collate Database charset collation
	 * @return bool True on success
	 */
	private function create_households_table( $charset_collate ) {
		$table_name = $this->prefix . 'households';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			household_name varchar(255) DEFAULT NULL,
			address_line1 varchar(255) NOT NULL,
			address_line2 varchar(255) DEFAULT NULL,
			city varchar(100) NOT NULL,
			state varchar(50) NOT NULL,
			zip_code varchar(20) NOT NULL,
			county varchar(100) DEFAULT NULL,
			household_size int(3) DEFAULT 1,
			registered_voters int(3) DEFAULT 0,
			likely_voters int(3) DEFAULT 0,
			household_income_estimate varchar(50) DEFAULT NULL,
			home_ownership varchar(20) DEFAULT NULL,
			household_type varchar(50) DEFAULT NULL,
			notes text DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY zip_code (zip_code),
			KEY city (city),
			KEY state (state),
			KEY address_lookup (address_line1, city, state, zip_code)
		) $charset_collate;";

		dbDelta( $sql );
		return true;
	}

	/**
	 * Create duplicate groups table
	 *
	 * Tracks potential duplicate contacts for merging
	 *
	 * @since 1.0.0
	 * @param string $charset_collate Database charset collation
	 * @return bool True on success
	 */
	private function create_duplicate_groups_table( $charset_collate ) {
		$table_name = $this->prefix . 'duplicate_groups';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			match_type varchar(50) NOT NULL,
			confidence_score int(3) DEFAULT 0,
			is_resolved tinyint(1) DEFAULT 0,
			resolved_by bigint(20) UNSIGNED DEFAULT NULL,
			resolved_at datetime DEFAULT NULL,
			notes text DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY match_type (match_type),
			KEY is_resolved (is_resolved),
			KEY confidence_score (confidence_score)
		) $charset_collate;";

		dbDelta( $sql );
		return true;
	}

	/**
	 * Create engagement scores table
	 *
	 * Stores historical engagement scores and algorithm results
	 *
	 * @since 1.0.0
	 * @param string $charset_collate Database charset collation
	 * @return bool True on success
	 */
	private function create_engagement_scores_table( $charset_collate ) {
		$table_name = $this->prefix . 'engagement_scores';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			contact_id bigint(20) UNSIGNED NOT NULL,
			score int(5) NOT NULL DEFAULT 0,
			score_breakdown longtext DEFAULT NULL,
			recency_score int(5) DEFAULT 0,
			frequency_score int(5) DEFAULT 0,
			interaction_quality_score int(5) DEFAULT 0,
			response_rate decimal(5,2) DEFAULT 0.00,
			calculation_date datetime NOT NULL,
			algorithm_version varchar(20) DEFAULT '1.0',
			PRIMARY KEY  (id),
			KEY contact_id (contact_id),
			KEY score (score),
			KEY calculation_date (calculation_date)
		) $charset_collate;";

		dbDelta( $sql );
		return true;
	}

	/**
	 * Create segments table
	 *
	 * Defines dynamic contact segments based on criteria
	 *
	 * @since 1.0.0
	 * @param string $charset_collate Database charset collation
	 * @return bool True on success
	 */
	private function create_segments_table( $charset_collate ) {
		$table_name = $this->prefix . 'segments';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			description text DEFAULT NULL,
			segment_type varchar(50) DEFAULT 'dynamic',
			criteria longtext DEFAULT NULL,
			contact_count int(10) DEFAULT 0,
			last_calculated datetime DEFAULT NULL,
			is_active tinyint(1) DEFAULT 1,
			created_by bigint(20) UNSIGNED DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY segment_type (segment_type),
			KEY is_active (is_active),
			KEY created_by (created_by)
		) $charset_collate;";

		dbDelta( $sql );
		return true;
	}

	/**
	 * Create segment contacts relationship table
	 *
	 * Many-to-many relationship between segments and contacts
	 *
	 * @since 1.0.0
	 * @param string $charset_collate Database charset collation
	 * @return bool True on success
	 */
	private function create_segment_contacts_table( $charset_collate ) {
		$table_name = $this->prefix . 'segment_contacts';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			segment_id bigint(20) UNSIGNED NOT NULL,
			contact_id bigint(20) UNSIGNED NOT NULL,
			added_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY segment_contact (segment_id, contact_id),
			KEY segment_id (segment_id),
			KEY contact_id (contact_id)
		) $charset_collate;";

		dbDelta( $sql );
		return true;
	}

	/**
	 * Drop all CRM tables
	 *
	 * Use with caution - permanently deletes all CRM data
	 *
	 * @since 1.0.0
	 * @return bool True on success
	 */
	public function drop_tables() {
		$tables = array(
			'segment_contacts',
			'segments',
			'engagement_scores',
			'duplicate_groups',
			'households',
			'custom_field_values',
			'custom_fields',
			'contact_tags',
			'tags',
			'interactions',
			'contacts',
		);

		foreach ( $tables as $table ) {
			$table_name = $this->prefix . $table;
			$this->wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
		}

		delete_option( 'cp_crm_db_version' );
		return true;
	}

	/**
	 * Get table name with prefix
	 *
	 * @since 1.0.0
	 * @param string $table Table name without prefix
	 * @return string Full table name with prefix
	 */
	public function get_table_name( $table ) {
		return $this->prefix . $table;
	}

	/**
	 * Check if tables exist
	 *
	 * @since 1.0.0
	 * @return bool True if all tables exist
	 */
	public function tables_exist() {
		$table_name = $this->prefix . 'contacts';
		$result     = $this->wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );
		return $result === $table_name;
	}

	/**
	 * Get database statistics
	 *
	 * @since 1.0.0
	 * @return array Database statistics
	 */
	public function get_statistics() {
		$stats = array();

		// Get contact count
		$contacts_table       = $this->prefix . 'contacts';
		$stats['contacts']    = (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM {$contacts_table}" );

		// Get interaction count
		$interactions_table       = $this->prefix . 'interactions';
		$stats['interactions']    = (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM {$interactions_table}" );

		// Get tag count
		$tags_table       = $this->prefix . 'tags';
		$stats['tags']    = (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM {$tags_table}" );

		// Get household count
		$households_table       = $this->prefix . 'households';
		$stats['households']    = (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM {$households_table}" );

		// Get segment count
		$segments_table       = $this->prefix . 'segments';
		$stats['segments']    = (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM {$segments_table}" );

		return $stats;
	}

	/**
	 * Optimize database tables
	 *
	 * Runs OPTIMIZE TABLE on all CRM tables for performance
	 *
	 * @since 1.0.0
	 * @return bool True on success
	 */
	public function optimize_tables() {
		$tables = array(
			'contacts',
			'interactions',
			'tags',
			'contact_tags',
			'custom_fields',
			'custom_field_values',
			'households',
			'duplicate_groups',
			'engagement_scores',
			'segments',
			'segment_contacts',
		);

		foreach ( $tables as $table ) {
			$table_name = $this->prefix . $table;
			$this->wpdb->query( "OPTIMIZE TABLE {$table_name}" );
		}

		return true;
	}
}
