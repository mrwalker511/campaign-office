<?php
/**
 * API Webhooks Class
 *
 * Manages webhook subscriptions, event triggers, payload formatting,
 * retry logic, and webhook security for CampaignPress.
 *
 * @package CampaignPress
 * @subpackage API
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API Webhooks Class
 *
 * @since 1.0.0
 */
class CampaignPress_API_Webhooks {

	/**
	 * Database object
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Webhooks table name
	 *
	 * @var string
	 */
	private $webhooks_table;

	/**
	 * Webhook logs table name
	 *
	 * @var string
	 */
	private $webhook_logs_table;

	/**
	 * API namespace
	 *
	 * @var string
	 */
	private $namespace = 'campaignpress/v1';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->webhooks_table = $wpdb->prefix . 'campaignpress_webhooks';
		$this->webhook_logs_table = $wpdb->prefix . 'campaignpress_webhook_logs';

		// Create tables if they don't exist
		$this->maybe_create_tables();

		// Register webhook event hooks
		$this->register_webhook_hooks();
	}

	/**
	 * Create Webhook Tables
	 *
	 * @since 1.0.0
	 */
	private function maybe_create_tables() {
		$charset_collate = $this->wpdb->get_charset_collate();

		// Webhooks table
		$sql_webhooks = "CREATE TABLE IF NOT EXISTS {$this->webhooks_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			url varchar(500) NOT NULL,
			events text NOT NULL,
			secret varchar(255),
			is_active tinyint(1) DEFAULT 1,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY is_active (is_active)
		) $charset_collate;";

		// Webhook logs table
		$sql_webhook_logs = "CREATE TABLE IF NOT EXISTS {$this->webhook_logs_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			webhook_id bigint(20) NOT NULL,
			event varchar(100) NOT NULL,
			payload longtext,
			response_code int(3),
			response_body text,
			attempt int(2) DEFAULT 1,
			status varchar(50) DEFAULT 'pending',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY webhook_id (webhook_id),
			KEY event (event),
			KEY status (status),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_webhooks );
		dbDelta( $sql_webhook_logs );
	}

	/**
	 * Register Webhook Hooks
	 *
	 * Hooks into CampaignPress events to trigger webhooks.
	 *
	 * @since 1.0.0
	 */
	private function register_webhook_hooks() {
		// Contact events
		add_action( 'campaignpress_contact_created', array( $this, 'trigger_contact_created' ) );
		add_action( 'campaignpress_contact_updated', array( $this, 'trigger_contact_updated' ) );
		add_action( 'campaignpress_contact_deleted', array( $this, 'trigger_contact_deleted' ) );

		// Event events
		add_action( 'campaignpress_event_created', array( $this, 'trigger_event_created' ) );
		add_action( 'campaignpress_event_updated', array( $this, 'trigger_event_updated' ) );
		add_action( 'campaignpress_event_deleted', array( $this, 'trigger_event_deleted' ) );

		// Volunteer events
		add_action( 'campaignpress_volunteer_created', array( $this, 'trigger_volunteer_created' ) );
		add_action( 'campaignpress_volunteer_updated', array( $this, 'trigger_volunteer_updated' ) );

		// Donation events
		add_action( 'campaignpress_donation_received', array( $this, 'trigger_donation_received' ) );
	}

	/**
	 * Register Routes
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {
		// List webhooks
		register_rest_route( $this->namespace, '/webhooks', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_webhooks' ),
				'permission_callback' => array( $this, 'check_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_webhook' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => $this->get_webhook_schema(),
			),
		) );

		// Individual webhook
		register_rest_route( $this->namespace, '/webhooks/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_webhook' ),
				'permission_callback' => array( $this, 'check_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_webhook' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => $this->get_webhook_schema(),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_webhook' ),
				'permission_callback' => array( $this, 'check_permission' ),
			),
		) );

		// Webhook logs
		register_rest_route( $this->namespace, '/webhooks/(?P<id>[\d]+)/logs', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_webhook_logs' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		// Test webhook
		register_rest_route( $this->namespace, '/webhooks/(?P<id>[\d]+)/test', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'test_webhook' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );
	}

	/**
	 * Check Permission
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return bool True if user has permission.
	 */
	public function check_permission( $request ) {
		return current_user_can( 'manage_options' );
	}

	// ========================================================================
	// WEBHOOK CRUD OPERATIONS
	// ========================================================================

	/**
	 * Get Webhooks
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_webhooks( $request ) {
		$webhooks = $this->wpdb->get_results(
			"SELECT * FROM {$this->webhooks_table} ORDER BY created_at DESC"
		);

		foreach ( $webhooks as &$webhook ) {
			$webhook->events = maybe_unserialize( $webhook->events );
		}

		return rest_ensure_response( $webhooks );
	}

	/**
	 * Get Webhook
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function get_webhook( $request ) {
		$id = $request->get_param( 'id' );

		$webhook = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->webhooks_table} WHERE id = %d",
				$id
			)
		);

		if ( ! $webhook ) {
			return new WP_Error(
				'webhook_not_found',
				__( 'Webhook not found.', 'campaign-office' ),
				array( 'status' => 404 )
			);
		}

		$webhook->events = maybe_unserialize( $webhook->events );

		return rest_ensure_response( $webhook );
	}

	/**
	 * Create Webhook
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function create_webhook( $request ) {
		$name = sanitize_text_field( $request->get_param( 'name' ) );
		$url = esc_url_raw( $request->get_param( 'url' ) );
		$events = $request->get_param( 'events' );

		// Validate URL
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return new WP_Error(
				'invalid_url',
				__( 'Invalid webhook URL.', 'campaign-office' ),
				array( 'status' => 400 )
			);
		}

		// Generate secret
		$secret = bin2hex( random_bytes( 32 ) );

		$inserted = $this->wpdb->insert(
			$this->webhooks_table,
			array(
				'name'   => $name,
				'url'    => $url,
				'events' => maybe_serialize( $events ),
				'secret' => $secret,
			),
			array( '%s', '%s', '%s', '%s' )
		);

		if ( ! $inserted ) {
			return new WP_Error(
				'webhook_creation_failed',
				__( 'Failed to create webhook.', 'campaign-office' ),
				array( 'status' => 500 )
			);
		}

		$webhook_id = $this->wpdb->insert_id;
		$webhook = $this->wpdb->get_row(
			$this->wpdb->prepare( "SELECT * FROM {$this->webhooks_table} WHERE id = %d", $webhook_id )
		);

		$webhook->events = maybe_unserialize( $webhook->events );

		return rest_ensure_response( $webhook );
	}

	/**
	 * Update Webhook
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function update_webhook( $request ) {
		$id = $request->get_param( 'id' );

		$webhook = $this->wpdb->get_row(
			$this->wpdb->prepare( "SELECT * FROM {$this->webhooks_table} WHERE id = %d", $id )
		);

		if ( ! $webhook ) {
			return new WP_Error(
				'webhook_not_found',
				__( 'Webhook not found.', 'campaign-office' ),
				array( 'status' => 404 )
			);
		}

		$data = array();

		if ( $request->has_param( 'name' ) ) {
			$data['name'] = sanitize_text_field( $request->get_param( 'name' ) );
		}

		if ( $request->has_param( 'url' ) ) {
			$url = esc_url_raw( $request->get_param( 'url' ) );
			if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
				return new WP_Error(
					'invalid_url',
					__( 'Invalid webhook URL.', 'campaign-office' ),
					array( 'status' => 400 )
				);
			}
			$data['url'] = $url;
		}

		if ( $request->has_param( 'events' ) ) {
			$data['events'] = maybe_serialize( $request->get_param( 'events' ) );
		}

		if ( $request->has_param( 'is_active' ) ) {
			$data['is_active'] = intval( $request->get_param( 'is_active' ) );
		}

		if ( empty( $data ) ) {
			return new WP_Error(
				'no_data',
				__( 'No data to update.', 'campaign-office' ),
				array( 'status' => 400 )
			);
		}

		$updated = $this->wpdb->update(
			$this->webhooks_table,
			$data,
			array( 'id' => $id )
		);

		if ( $updated === false ) {
			return new WP_Error(
				'webhook_update_failed',
				__( 'Failed to update webhook.', 'campaign-office' ),
				array( 'status' => 500 )
			);
		}

		$webhook = $this->wpdb->get_row(
			$this->wpdb->prepare( "SELECT * FROM {$this->webhooks_table} WHERE id = %d", $id )
		);

		$webhook->events = maybe_unserialize( $webhook->events );

		return rest_ensure_response( $webhook );
	}

	/**
	 * Delete Webhook
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function delete_webhook( $request ) {
		$id = $request->get_param( 'id' );

		$webhook = $this->wpdb->get_row(
			$this->wpdb->prepare( "SELECT * FROM {$this->webhooks_table} WHERE id = %d", $id )
		);

		if ( ! $webhook ) {
			return new WP_Error(
				'webhook_not_found',
				__( 'Webhook not found.', 'campaign-office' ),
				array( 'status' => 404 )
			);
		}

		// Delete webhook logs first
		$this->wpdb->delete(
			$this->webhook_logs_table,
			array( 'webhook_id' => $id ),
			array( '%d' )
		);

		// Delete webhook
		$deleted = $this->wpdb->delete(
			$this->webhooks_table,
			array( 'id' => $id ),
			array( '%d' )
		);

		if ( ! $deleted ) {
			return new WP_Error(
				'webhook_deletion_failed',
				__( 'Failed to delete webhook.', 'campaign-office' ),
				array( 'status' => 500 )
			);
		}

		return rest_ensure_response( array(
			'deleted' => true,
			'id'      => $id,
		) );
	}

	/**
	 * Get Webhook Logs
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function get_webhook_logs( $request ) {
		$id = $request->get_param( 'id' );

		$logs = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->webhook_logs_table}
				WHERE webhook_id = %d
				ORDER BY created_at DESC
				LIMIT 100",
				$id
			)
		);

		return rest_ensure_response( $logs );
	}

	/**
	 * Test Webhook
	 *
	 * Sends a test payload to the webhook URL.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function test_webhook( $request ) {
		$id = $request->get_param( 'id' );

		$webhook = $this->wpdb->get_row(
			$this->wpdb->prepare( "SELECT * FROM {$this->webhooks_table} WHERE id = %d", $id )
		);

		if ( ! $webhook ) {
			return new WP_Error(
				'webhook_not_found',
				__( 'Webhook not found.', 'campaign-office' ),
				array( 'status' => 404 )
			);
		}

		$payload = array(
			'event' => 'webhook.test',
			'data'  => array(
				'message' => 'This is a test webhook from CampaignPress',
				'timestamp' => current_time( 'mysql' ),
			),
		);

		$result = $this->send_webhook( $webhook, 'webhook.test', $payload );

		return rest_ensure_response( $result );
	}

	// ========================================================================
	// EVENT TRIGGER METHODS
	// ========================================================================

	/**
	 * Trigger Contact Created Webhook
	 *
	 * @since 1.0.0
	 * @param object $contact Contact data.
	 */
	public function trigger_contact_created( $contact ) {
		$this->trigger_event( 'contact.created', $contact );
	}

	/**
	 * Trigger Contact Updated Webhook
	 *
	 * @since 1.0.0
	 * @param object $contact Contact data.
	 */
	public function trigger_contact_updated( $contact ) {
		$this->trigger_event( 'contact.updated', $contact );
	}

	/**
	 * Trigger Contact Deleted Webhook
	 *
	 * @since 1.0.0
	 * @param object $contact Contact data.
	 */
	public function trigger_contact_deleted( $contact ) {
		$this->trigger_event( 'contact.deleted', $contact );
	}

	/**
	 * Trigger Event Created Webhook
	 *
	 * @since 1.0.0
	 * @param array $event Event data.
	 */
	public function trigger_event_created( $event ) {
		$this->trigger_event( 'event.created', $event );
	}

	/**
	 * Trigger Event Updated Webhook
	 *
	 * @since 1.0.0
	 * @param array $event Event data.
	 */
	public function trigger_event_updated( $event ) {
		$this->trigger_event( 'event.updated', $event );
	}

	/**
	 * Trigger Event Deleted Webhook
	 *
	 * @since 1.0.0
	 * @param array $event Event data.
	 */
	public function trigger_event_deleted( $event ) {
		$this->trigger_event( 'event.deleted', $event );
	}

	/**
	 * Trigger Volunteer Created Webhook
	 *
	 * @since 1.0.0
	 * @param object $volunteer Volunteer data.
	 */
	public function trigger_volunteer_created( $volunteer ) {
		$this->trigger_event( 'volunteer.created', $volunteer );
	}

	/**
	 * Trigger Volunteer Updated Webhook
	 *
	 * @since 1.0.0
	 * @param object $volunteer Volunteer data.
	 */
	public function trigger_volunteer_updated( $volunteer ) {
		$this->trigger_event( 'volunteer.updated', $volunteer );
	}

	/**
	 * Trigger Donation Received Webhook
	 *
	 * @since 1.0.0
	 * @param object $donation Donation data.
	 */
	public function trigger_donation_received( $donation ) {
		$this->trigger_event( 'donation.received', $donation );
	}

	/**
	 * Trigger Event
	 *
	 * Main method to trigger webhooks for an event.
	 *
	 * @since 1.0.0
	 * @param string $event_name Event name.
	 * @param mixed  $data Event data.
	 */
	private function trigger_event( $event_name, $data ) {
		// Get all active webhooks
		$webhooks = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->webhooks_table} WHERE is_active = %d",
				1
			)
		);

		foreach ( $webhooks as $webhook ) {
			$events = maybe_unserialize( $webhook->events );

			// Check if webhook is subscribed to this event
			if ( ! in_array( $event_name, $events ) && ! in_array( '*', $events ) ) {
				continue;
			}

			// Format payload
			$payload = $this->format_payload( $event_name, $data );

			// Send webhook (async using wp_schedule_single_event for better performance)
			wp_schedule_single_event(
				time(),
				'campaignpress_send_webhook',
				array( $webhook->id, $event_name, $payload )
			);
		}
	}

	/**
	 * Format Payload
	 *
	 * @since 1.0.0
	 * @param string $event_name Event name.
	 * @param mixed  $data Event data.
	 * @return array Formatted payload.
	 */
	private function format_payload( $event_name, $data ) {
		return array(
			'event'     => $event_name,
			'timestamp' => current_time( 'mysql' ),
			'data'      => $data,
		);
	}

	/**
	 * Send Webhook
	 *
	 * Sends the webhook HTTP request with retry logic.
	 *
	 * @since 1.0.0
	 * @param object $webhook Webhook object.
	 * @param string $event_name Event name.
	 * @param array  $payload Payload data.
	 * @param int    $attempt Attempt number (for retry).
	 * @return array Result array.
	 */
	public function send_webhook( $webhook, $event_name, $payload, $attempt = 1 ) {
		// Create log entry
		$log_id = $this->wpdb->insert(
			$this->webhook_logs_table,
			array(
				'webhook_id' => $webhook->id,
				'event'      => $event_name,
				'payload'    => wp_json_encode( $payload ),
				'attempt'    => $attempt,
				'status'     => 'sending',
			),
			array( '%d', '%s', '%s', '%d', '%s' )
		);

		if ( ! $log_id ) {
			return array(
				'success' => false,
				'error'   => 'Failed to create log entry',
			);
		}

		$log_id = $this->wpdb->insert_id;

		// Generate signature
		$signature = $this->generate_signature( $payload, $webhook->secret );

		// Send HTTP request
		$response = wp_remote_post( $webhook->url, array(
			'timeout' => 15,
			'headers' => array(
				'Content-Type'         => 'application/json',
				'X-Webhook-Signature'  => $signature,
				'X-Webhook-Event'      => $event_name,
				'User-Agent'           => 'CampaignPress-Webhooks/1.0',
			),
			'body'    => wp_json_encode( $payload ),
		) );

		// Process response
		if ( is_wp_error( $response ) ) {
			$this->wpdb->update(
				$this->webhook_logs_table,
				array(
					'status'        => 'failed',
					'response_body' => $response->get_error_message(),
				),
				array( 'id' => $log_id ),
				array( '%s', '%s' ),
				array( '%d' )
			);

			// Retry logic (max 3 attempts)
			if ( $attempt < 3 ) {
				$retry_delay = pow( 2, $attempt ) * 60; // Exponential backoff: 2, 4, 8 minutes
				wp_schedule_single_event(
					time() + $retry_delay,
					'campaignpress_retry_webhook',
					array( $webhook->id, $event_name, $payload, $attempt + 1 )
				);
			}

			return array(
				'success' => false,
				'error'   => $response->get_error_message(),
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		// Update log entry
		$status = ( $response_code >= 200 && $response_code < 300 ) ? 'success' : 'failed';

		$this->wpdb->update(
			$this->webhook_logs_table,
			array(
				'status'        => $status,
				'response_code' => $response_code,
				'response_body' => $response_body,
			),
			array( 'id' => $log_id ),
			array( '%s', '%d', '%s' ),
			array( '%d' )
		);

		// Retry if failed and not max attempts
		if ( $status === 'failed' && $attempt < 3 ) {
			$retry_delay = pow( 2, $attempt ) * 60;
			wp_schedule_single_event(
				time() + $retry_delay,
				'campaignpress_retry_webhook',
				array( $webhook->id, $event_name, $payload, $attempt + 1 )
			);
		}

		return array(
			'success'       => $status === 'success',
			'response_code' => $response_code,
			'response_body' => $response_body,
		);
	}

	/**
	 * Generate Signature
	 *
	 * Generates HMAC signature for webhook security.
	 *
	 * @since 1.0.0
	 * @param array  $payload Payload data.
	 * @param string $secret Webhook secret.
	 * @return string Signature.
	 */
	private function generate_signature( $payload, $secret ) {
		$payload_json = wp_json_encode( $payload );
		return hash_hmac( 'sha256', $payload_json, $secret );
	}

	/**
	 * Verify Signature
	 *
	 * Verifies webhook signature for incoming webhooks.
	 *
	 * @since 1.0.0
	 * @param string $signature Signature from header.
	 * @param string $payload Request payload.
	 * @param string $secret Webhook secret.
	 * @return bool True if signature is valid.
	 */
	public function verify_signature( $signature, $payload, $secret ) {
		$expected_signature = hash_hmac( 'sha256', $payload, $secret );
		return hash_equals( $expected_signature, $signature );
	}

	/**
	 * Get Webhook Schema
	 *
	 * @since 1.0.0
	 * @return array Webhook schema.
	 */
	private function get_webhook_schema() {
		return array(
			'name' => array(
				'required' => true,
				'type'     => 'string',
			),
			'url' => array(
				'required' => true,
				'type'     => 'string',
				'format'   => 'uri',
			),
			'events' => array(
				'required' => true,
				'type'     => 'array',
				'items'    => array(
					'type' => 'string',
					'enum' => array(
						'*',
						'contact.created',
						'contact.updated',
						'contact.deleted',
						'event.created',
						'event.updated',
						'event.deleted',
						'volunteer.created',
						'volunteer.updated',
						'donation.received',
					),
				),
			),
			'is_active' => array(
				'required' => false,
				'type'     => 'boolean',
				'default'  => true,
			),
		);
	}

	/**
	 * Clean Old Webhook Logs
	 *
	 * Removes webhook logs older than 30 days.
	 * Should be called via cron job.
	 *
	 * @since 1.0.0
	 */
	public function clean_old_logs() {
		$this->wpdb->query(
			"DELETE FROM {$this->webhook_logs_table}
			WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);
	}

	/**
	 * Get Webhook Statistics
	 *
	 * @since 1.0.0
	 * @param int $webhook_id Webhook ID.
	 * @return array Statistics.
	 */
	public function get_webhook_statistics( $webhook_id ) {
		$stats = array(
			'total_deliveries' => 0,
			'successful'       => 0,
			'failed'           => 0,
			'success_rate'     => 0,
			'last_delivery'    => null,
		);

		$total = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->webhook_logs_table} WHERE webhook_id = %d",
				$webhook_id
			)
		);

		$successful = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->webhook_logs_table}
				WHERE webhook_id = %d AND status = 'success'",
				$webhook_id
			)
		);

		$failed = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->webhook_logs_table}
				WHERE webhook_id = %d AND status = 'failed'",
				$webhook_id
			)
		);

		$last_delivery = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT created_at FROM {$this->webhook_logs_table}
				WHERE webhook_id = %d
				ORDER BY created_at DESC
				LIMIT 1",
				$webhook_id
			)
		);

		$stats['total_deliveries'] = intval( $total );
		$stats['successful'] = intval( $successful );
		$stats['failed'] = intval( $failed );
		$stats['success_rate'] = $total > 0 ? ( $successful / $total ) * 100 : 0;
		$stats['last_delivery'] = $last_delivery;

		return $stats;
	}
}

/**
 * Scheduled Action: Send Webhook
 *
 * @since 1.0.0
 * @param int    $webhook_id Webhook ID.
 * @param string $event_name Event name.
 * @param array  $payload Payload data.
 */
function campaignpress_scheduled_send_webhook( $webhook_id, $event_name, $payload ) {
	global $wpdb;
	$webhooks_table = $wpdb->prefix . 'campaignpress_webhooks';

	$webhook = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$webhooks_table} WHERE id = %d", $webhook_id )
	);

	if ( $webhook ) {
		$webhooks_class = $GLOBALS['campaignpress_webhooks'];
		if ( $webhooks_class ) {
			$webhooks_class->send_webhook( $webhook, $event_name, $payload );
		}
	}
}
add_action( 'campaignpress_send_webhook', 'campaignpress_scheduled_send_webhook', 10, 3 );

/**
 * Scheduled Action: Retry Webhook
 *
 * @since 1.0.0
 * @param int    $webhook_id Webhook ID.
 * @param string $event_name Event name.
 * @param array  $payload Payload data.
 * @param int    $attempt Attempt number.
 */
function campaignpress_scheduled_retry_webhook( $webhook_id, $event_name, $payload, $attempt ) {
	global $wpdb;
	$webhooks_table = $wpdb->prefix . 'campaignpress_webhooks';

	$webhook = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$webhooks_table} WHERE id = %d", $webhook_id )
	);

	if ( $webhook ) {
		$webhooks_class = $GLOBALS['campaignpress_webhooks'];
		if ( $webhooks_class ) {
			$webhooks_class->send_webhook( $webhook, $event_name, $payload, $attempt );
		}
	}
}
add_action( 'campaignpress_retry_webhook', 'campaignpress_scheduled_retry_webhook', 10, 4 );

/**
 * Scheduled Action: Clean Webhook Logs
 *
 * @since 1.0.0
 */
function campaignpress_scheduled_clean_webhook_logs() {
	$webhooks_class = $GLOBALS['campaignpress_webhooks'];
	if ( $webhooks_class ) {
		$webhooks_class->clean_old_logs();
	}
}
add_action( 'campaignpress_clean_webhook_logs', 'campaignpress_scheduled_clean_webhook_logs' );

// Schedule daily cleanup if not already scheduled
if ( ! wp_next_scheduled( 'campaignpress_clean_webhook_logs' ) ) {
	wp_schedule_event( time(), 'daily', 'campaignpress_clean_webhook_logs' );
}
