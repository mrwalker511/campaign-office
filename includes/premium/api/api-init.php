<?php
/**
 * REST API Module Initialization
 *
 * Initializes the CampaignPress REST API including custom endpoints,
 * webhooks, authentication, and rate limiting.
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
 * Initialize REST API Module
 *
 * @since 1.0.0
 */
function campaignpress_api_init() {
	// Require API classes
	require_once dirname( __FILE__ ) . '/class-api-endpoints.php';
	require_once dirname( __FILE__ ) . '/class-api-webhooks.php';

	// Initialize API endpoints
	if ( class_exists( 'CampaignPress_API_Endpoints' ) ) {
		$GLOBALS['campaignpress_api'] = new CampaignPress_API_Endpoints();
	}

	// Initialize webhooks
	if ( class_exists( 'CampaignPress_API_Webhooks' ) ) {
		$GLOBALS['campaignpress_webhooks'] = new CampaignPress_API_Webhooks();
	}

	// Register API routes
	add_action( 'rest_api_init', 'campaignpress_register_api_routes' );

	// Add API admin menu
	add_action( 'admin_menu', 'campaignpress_api_admin_menu', 20 );

	// API key authentication
	add_filter( 'determine_current_user', 'campaignpress_api_key_authentication', 20 );

	// Rate limiting
	add_action( 'rest_api_init', 'campaignpress_api_rate_limiting', 1 );

	// Request logging
	add_action( 'rest_pre_dispatch', 'campaignpress_log_api_request', 10, 3 );
	add_action( 'rest_post_dispatch', 'campaignpress_log_api_response', 10, 3 );
}
add_action( 'init', 'campaignpress_api_init', 5 );

/**
 * Register API Routes
 *
 * @since 1.0.0
 */
function campaignpress_register_api_routes() {
	$api = $GLOBALS['campaignpress_api'];
	if ( $api ) {
		$api->register_routes();
	}

	$webhooks = $GLOBALS['campaignpress_webhooks'];
	if ( $webhooks ) {
		$webhooks->register_routes();
	}
}

/**
 * Add API Admin Menu
 *
 * @since 1.0.0
 */
function campaignpress_api_admin_menu() {
	// Main API page
	add_menu_page(
		__( 'API Settings', 'campaign-office' ),
		__( 'API', 'campaign-office' ),
		'manage_options',
		'campaignpress-api',
		'campaignpress_api_settings_page',
		'dashicons-rest-api',
		35
	);

	// API Keys submenu
	add_submenu_page(
		'campaignpress-api',
		__( 'API Keys', 'campaign-office' ),
		__( 'API Keys', 'campaign-office' ),
		'manage_options',
		'campaignpress-api-keys',
		'campaignpress_api_keys_page'
	);

	// Webhooks submenu
	add_submenu_page(
		'campaignpress-api',
		__( 'Webhooks', 'campaign-office' ),
		__( 'Webhooks', 'campaign-office' ),
		'manage_options',
		'campaignpress-webhooks',
		'campaignpress_webhooks_page'
	);

	// API Logs submenu
	add_submenu_page(
		'campaignpress-api',
		__( 'API Logs', 'campaign-office' ),
		__( 'API Logs', 'campaign-office' ),
		'manage_options',
		'campaignpress-api-logs',
		'campaignpress_api_logs_page'
	);

	// API Documentation submenu
	add_submenu_page(
		'campaignpress-api',
		__( 'API Documentation', 'campaign-office' ),
		__( 'Documentation', 'campaign-office' ),
		'manage_options',
		'campaignpress-api-docs',
		'campaignpress_api_docs_page'
	);
}

/**
 * API Settings Page
 *
 * @since 1.0.0
 */
function campaignpress_api_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'campaign-office' ) );
	}

	// Handle form submission
	if ( isset( $_POST['campaignpress_api_settings_nonce'] ) &&
	     wp_verify_nonce( $_POST['campaignpress_api_settings_nonce'], 'campaignpress_api_settings' ) ) {

		update_option( 'campaignpress_api_enabled', isset( $_POST['api_enabled'] ) ? 1 : 0 );
		update_option( 'campaignpress_api_require_authentication', isset( $_POST['require_auth'] ) ? 1 : 0 );
		update_option( 'campaignpress_api_rate_limit', intval( $_POST['rate_limit'] ) );
		update_option( 'campaignpress_api_log_requests', isset( $_POST['log_requests'] ) ? 1 : 0 );

		echo '<div class="notice notice-success"><p>' . __( 'Settings saved successfully.', 'campaign-office' ) . '</p></div>';
	}

	$api_enabled = get_option( 'campaignpress_api_enabled', 1 );
	$require_auth = get_option( 'campaignpress_api_require_authentication', 1 );
	$rate_limit = get_option( 'campaignpress_api_rate_limit', 100 );
	$log_requests = get_option( 'campaignpress_api_log_requests', 1 );
	?>
	<div class="wrap campaignpress-api-settings">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<form method="post" action="">
			<?php wp_nonce_field( 'campaignpress_api_settings', 'campaignpress_api_settings_nonce' ); ?>

			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="api_enabled"><?php esc_html_e( 'Enable API', 'campaign-office' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="api_enabled" name="api_enabled" value="1" <?php checked( $api_enabled, 1 ); ?>>
						<p class="description"><?php esc_html_e( 'Enable or disable the REST API endpoints.', 'campaign-office' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="require_auth"><?php esc_html_e( 'Require Authentication', 'campaign-office' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="require_auth" name="require_auth" value="1" <?php checked( $require_auth, 1 ); ?>>
						<p class="description"><?php esc_html_e( 'Require API key authentication for all requests.', 'campaign-office' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="rate_limit"><?php esc_html_e( 'Rate Limit', 'campaign-office' ); ?></label>
					</th>
					<td>
						<input type="number" id="rate_limit" name="rate_limit" value="<?php echo esc_attr( $rate_limit ); ?>" class="small-text">
						<span><?php esc_html_e( 'requests per hour', 'campaign-office' ); ?></span>
						<p class="description"><?php esc_html_e( 'Maximum number of API requests allowed per hour per API key.', 'campaign-office' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="log_requests"><?php esc_html_e( 'Log API Requests', 'campaign-office' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="log_requests" name="log_requests" value="1" <?php checked( $log_requests, 1 ); ?>>
						<p class="description"><?php esc_html_e( 'Log all API requests for debugging and monitoring.', 'campaign-office' ); ?></p>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'API Endpoints', 'campaign-office' ); ?></h2>
			<p><?php esc_html_e( 'Base URL:', 'campaign-office' ); ?> <code><?php echo esc_url( rest_url( 'campaignpress/v1' ) ); ?></code></p>

			<div class="api-endpoints-list">
				<h3><?php esc_html_e( 'Available Endpoints', 'campaign-office' ); ?></h3>
				<ul>
					<li><code>GET /contacts</code> - <?php esc_html_e( 'List all contacts', 'campaign-office' ); ?></li>
					<li><code>GET /contacts/{id}</code> - <?php esc_html_e( 'Get a specific contact', 'campaign-office' ); ?></li>
					<li><code>POST /contacts</code> - <?php esc_html_e( 'Create a new contact', 'campaign-office' ); ?></li>
					<li><code>PUT /contacts/{id}</code> - <?php esc_html_e( 'Update a contact', 'campaign-office' ); ?></li>
					<li><code>DELETE /contacts/{id}</code> - <?php esc_html_e( 'Delete a contact', 'campaign-office' ); ?></li>
					<li><code>GET /events</code> - <?php esc_html_e( 'List all events', 'campaign-office' ); ?></li>
					<li><code>GET /events/{id}</code> - <?php esc_html_e( 'Get a specific event', 'campaign-office' ); ?></li>
					<li><code>POST /events</code> - <?php esc_html_e( 'Create a new event', 'campaign-office' ); ?></li>
					<li><code>PUT /events/{id}</code> - <?php esc_html_e( 'Update an event', 'campaign-office' ); ?></li>
					<li><code>DELETE /events/{id}</code> - <?php esc_html_e( 'Delete an event', 'campaign-office' ); ?></li>
					<li><code>GET /volunteers</code> - <?php esc_html_e( 'List all volunteers', 'campaign-office' ); ?></li>
					<li><code>POST /volunteers</code> - <?php esc_html_e( 'Create a new volunteer', 'campaign-office' ); ?></li>
					<li><code>POST /donations</code> - <?php esc_html_e( 'Record a donation (webhook)', 'campaign-office' ); ?></li>
					<li><code>GET /analytics</code> - <?php esc_html_e( 'Get analytics data', 'campaign-office' ); ?></li>
					<li><code>POST /field-ops/canvassing</code> - <?php esc_html_e( 'Submit canvassing results', 'campaign-office' ); ?></li>
					<li><code>POST /field-ops/phone-banking</code> - <?php esc_html_e( 'Submit phone banking results', 'campaign-office' ); ?></li>
				</ul>
			</div>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * API Keys Page
 *
 * @since 1.0.0
 */
function campaignpress_api_keys_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'campaign-office' ) );
	}

	// Handle key generation
	if ( isset( $_POST['generate_api_key'] ) &&
	     wp_verify_nonce( $_POST['api_key_nonce'], 'generate_api_key' ) ) {
		$key_name = sanitize_text_field( $_POST['key_name'] );
		$api_key = campaignpress_generate_api_key( $key_name );

		echo '<div class="notice notice-success"><p>' .
		     sprintf( __( 'API Key generated: <strong>%s</strong> (Save this key - it will not be shown again)', 'campaign-office' ), esc_html( $api_key ) ) .
		     '</p></div>';
	}

	// Handle key deletion
	if ( isset( $_POST['delete_api_key'] ) &&
	     wp_verify_nonce( $_POST['delete_key_nonce'], 'delete_api_key' ) ) {
		$key_id = intval( $_POST['key_id'] );
		campaignpress_delete_api_key( $key_id );

		echo '<div class="notice notice-success"><p>' . __( 'API Key deleted successfully.', 'campaign-office' ) . '</p></div>';
	}

	$api_keys = campaignpress_get_api_keys();
	?>
	<div class="wrap campaignpress-api-keys">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<h2><?php esc_html_e( 'Generate New API Key', 'campaign-office' ); ?></h2>
		<form method="post" action="">
			<?php wp_nonce_field( 'generate_api_key', 'api_key_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="key_name"><?php esc_html_e( 'Key Name', 'campaign-office' ); ?></label>
					</th>
					<td>
						<input type="text" id="key_name" name="key_name" class="regular-text" required>
						<p class="description"><?php esc_html_e( 'A descriptive name for this API key.', 'campaign-office' ); ?></p>
					</td>
				</tr>
			</table>
			<button type="submit" name="generate_api_key" class="button button-primary"><?php esc_html_e( 'Generate API Key', 'campaign-office' ); ?></button>
		</form>

		<h2><?php esc_html_e( 'Existing API Keys', 'campaign-office' ); ?></h2>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'campaign-office' ); ?></th>
					<th><?php esc_html_e( 'Key (truncated)', 'campaign-office' ); ?></th>
					<th><?php esc_html_e( 'Created', 'campaign-office' ); ?></th>
					<th><?php esc_html_e( 'Last Used', 'campaign-office' ); ?></th>
					<th><?php esc_html_e( 'Requests', 'campaign-office' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'campaign-office' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $api_keys ) ) : ?>
					<tr>
						<td colspan="6"><?php esc_html_e( 'No API keys found.', 'campaign-office' ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $api_keys as $key ) : ?>
						<tr>
							<td><?php echo esc_html( $key->name ); ?></td>
							<td><code><?php echo esc_html( substr( $key->api_key, 0, 12 ) . '...' ); ?></code></td>
							<td><?php echo esc_html( date( 'Y-m-d H:i', strtotime( $key->created_at ) ) ); ?></td>
							<td><?php echo esc_html( $key->last_used ? date( 'Y-m-d H:i', strtotime( $key->last_used ) ) : 'Never' ); ?></td>
							<td><?php echo esc_html( $key->request_count ); ?></td>
							<td>
								<form method="post" action="" style="display:inline;">
									<?php wp_nonce_field( 'delete_api_key', 'delete_key_nonce' ); ?>
									<input type="hidden" name="key_id" value="<?php echo esc_attr( $key->id ); ?>">
									<button type="submit" name="delete_api_key" class="button button-small button-link-delete"
									        onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this API key?', 'campaign-office' ); ?>')">
										<?php esc_html_e( 'Delete', 'campaign-office' ); ?>
									</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Webhooks Page
 *
 * @since 1.0.0
 */
function campaignpress_webhooks_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'campaign-office' ) );
	}

	$webhooks = $GLOBALS['campaignpress_webhooks'];
	?>
	<div class="wrap campaignpress-webhooks">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<p><?php esc_html_e( 'Manage webhook subscriptions for external service integrations.', 'campaign-office' ); ?></p>

		<div id="webhooks-content">
			<!-- Content loaded via JavaScript -->
			<div class="loading-spinner">
				<span class="spinner is-active"></span>
				<p><?php esc_html_e( 'Loading webhooks...', 'campaign-office' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

/**
 * API Logs Page
 *
 * @since 1.0.0
 */
function campaignpress_api_logs_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'campaign-office' ) );
	}

	global $wpdb;
	$logs_table = $wpdb->prefix . 'campaignpress_api_logs';

	$logs = $wpdb->get_results(
		"SELECT * FROM {$logs_table} ORDER BY created_at DESC LIMIT 100"
	);
	?>
	<div class="wrap campaignpress-api-logs">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Timestamp', 'campaign-office' ); ?></th>
					<th><?php esc_html_e( 'Method', 'campaign-office' ); ?></th>
					<th><?php esc_html_e( 'Endpoint', 'campaign-office' ); ?></th>
					<th><?php esc_html_e( 'Status', 'campaign-office' ); ?></th>
					<th><?php esc_html_e( 'IP Address', 'campaign-office' ); ?></th>
					<th><?php esc_html_e( 'Response Time', 'campaign-office' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $logs ) ) : ?>
					<tr>
						<td colspan="6"><?php esc_html_e( 'No API logs found.', 'campaign-office' ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $logs as $log ) : ?>
						<tr>
							<td><?php echo esc_html( $log->created_at ); ?></td>
							<td><code><?php echo esc_html( $log->method ); ?></code></td>
							<td><?php echo esc_html( $log->endpoint ); ?></td>
							<td><?php echo esc_html( $log->status_code ); ?></td>
							<td><?php echo esc_html( $log->ip_address ); ?></td>
							<td><?php echo esc_html( $log->response_time . ' ms' ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * API Documentation Page
 *
 * @since 1.0.0
 */
function campaignpress_api_docs_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'campaign-office' ) );
	}
	?>
	<div class="wrap campaignpress-api-docs">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div class="api-documentation">
			<h2><?php esc_html_e( 'Authentication', 'campaign-office' ); ?></h2>
			<p><?php esc_html_e( 'Include your API key in the request header:', 'campaign-office' ); ?></p>
			<pre><code>X-CampaignPress-API-Key: your-api-key-here</code></pre>

			<h2><?php esc_html_e( 'Endpoints', 'campaign-office' ); ?></h2>

			<h3><?php esc_html_e( 'Contacts', 'campaign-office' ); ?></h3>
			<p><code>GET /campaignpress/v1/contacts</code> - <?php esc_html_e( 'List contacts with pagination and filtering', 'campaign-office' ); ?></p>
			<p><code>POST /campaignpress/v1/contacts</code> - <?php esc_html_e( 'Create a new contact', 'campaign-office' ); ?></p>

			<h3><?php esc_html_e( 'Events', 'campaign-office' ); ?></h3>
			<p><code>GET /campaignpress/v1/events</code> - <?php esc_html_e( 'List events with pagination', 'campaign-office' ); ?></p>
			<p><code>POST /campaignpress/v1/events</code> - <?php esc_html_e( 'Create a new event', 'campaign-office' ); ?></p>

			<h3><?php esc_html_e( 'Rate Limits', 'campaign-office' ); ?></h3>
			<p><?php echo sprintf( __( 'Current rate limit: %d requests per hour', 'campaign-office' ), get_option( 'campaignpress_api_rate_limit', 100 ) ); ?></p>

			<h3><?php esc_html_e( 'Third-Party Integrations', 'campaign-office' ); ?></h3>
			<p><?php esc_html_e( 'CampaignPress API supports integration with:', 'campaign-office' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'NationBuilder - Sync contacts and events', 'campaign-office' ); ?></li>
				<li><?php esc_html_e( 'NGP VAN - Import voter data and volunteer information', 'campaign-office' ); ?></li>
				<li><?php esc_html_e( 'Action Network - Share petitions and fundraising campaigns', 'campaign-office' ); ?></li>
			</ul>
		</div>
	</div>
	<?php
}

/**
 * API Key Authentication
 *
 * @since 1.0.0
 * @param int|false $user_id User ID if already authenticated.
 * @return int|false User ID on success, false on failure.
 */
function campaignpress_api_key_authentication( $user_id ) {
	// Only apply to REST API requests
	if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) {
		return $user_id;
	}

	// Skip if already authenticated
	if ( $user_id ) {
		return $user_id;
	}

	// Check if API is enabled
	if ( ! get_option( 'campaignpress_api_enabled', 1 ) ) {
		return $user_id;
	}

	// Get API key from header
	$api_key = isset( $_SERVER['HTTP_X_CAMPAIGNPRESS_API_KEY'] ) ?
	           sanitize_text_field( $_SERVER['HTTP_X_CAMPAIGNPRESS_API_KEY'] ) : '';

	if ( empty( $api_key ) ) {
		return $user_id;
	}

	// Verify API key
	$key_data = campaignpress_verify_api_key( $api_key );
	if ( $key_data ) {
		// Update last used timestamp
		campaignpress_update_api_key_usage( $key_data->id );

		// Return admin user ID for API requests
		return 1;
	}

	return $user_id;
}

/**
 * API Rate Limiting
 *
 * @since 1.0.0
 */
function campaignpress_api_rate_limiting() {
	// Get API key from header
	$api_key = isset( $_SERVER['HTTP_X_CAMPAIGNPRESS_API_KEY'] ) ?
	           sanitize_text_field( $_SERVER['HTTP_X_CAMPAIGNPRESS_API_KEY'] ) : '';

	if ( empty( $api_key ) ) {
		return;
	}

	$key_data = campaignpress_verify_api_key( $api_key );
	if ( ! $key_data ) {
		return;
	}

	$rate_limit = get_option( 'campaignpress_api_rate_limit', 100 );
	$current_count = get_transient( 'campaignpress_api_rate_' . $key_data->id );

	if ( $current_count === false ) {
		set_transient( 'campaignpress_api_rate_' . $key_data->id, 1, HOUR_IN_SECONDS );
	} elseif ( $current_count >= $rate_limit ) {
		wp_send_json_error( array(
			'code'    => 'rate_limit_exceeded',
			'message' => 'Rate limit exceeded. Please try again later.',
		), 429 );
		exit;
	} else {
		set_transient( 'campaignpress_api_rate_' . $key_data->id, $current_count + 1, HOUR_IN_SECONDS );
	}
}

/**
 * Log API Request
 *
 * @since 1.0.0
 * @param mixed           $result Response data.
 * @param WP_REST_Server  $server Server instance.
 * @param WP_REST_Request $request Request object.
 */
function campaignpress_log_api_request( $result, $server, $request ) {
	if ( ! get_option( 'campaignpress_api_log_requests', 1 ) ) {
		return $result;
	}

	$GLOBALS['campaignpress_api_request_start'] = microtime( true );

	return $result;
}

/**
 * Log API Response
 *
 * @since 1.0.0
 * @param WP_HTTP_Response $result Result to send.
 * @param WP_REST_Server   $server Server instance.
 * @param WP_REST_Request  $request Request object.
 * @return WP_HTTP_Response
 */
function campaignpress_log_api_response( $result, $server, $request ) {
	if ( ! get_option( 'campaignpress_api_log_requests', 1 ) ) {
		return $result;
	}

	global $wpdb;
	$logs_table = $wpdb->prefix . 'campaignpress_api_logs';

	$start_time = isset( $GLOBALS['campaignpress_api_request_start'] ) ?
	              $GLOBALS['campaignpress_api_request_start'] : microtime( true );
	$response_time = ( microtime( true ) - $start_time ) * 1000; // Convert to milliseconds

	$wpdb->insert(
		$logs_table,
		array(
			'method'        => $request->get_method(),
			'endpoint'      => $request->get_route(),
			'status_code'   => $result->get_status(),
			'ip_address'    => $_SERVER['REMOTE_ADDR'],
			'response_time' => round( $response_time, 2 ),
		),
		array( '%s', '%s', '%d', '%s', '%f' )
	);

	return $result;
}

// ========================================================================
// API KEY MANAGEMENT FUNCTIONS
// ========================================================================

/**
 * Generate API Key
 *
 * @since 1.0.0
 * @param string $name Key name.
 * @return string Generated API key.
 */
function campaignpress_generate_api_key( $name ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'campaignpress_api_keys';

	$api_key = 'cp_' . bin2hex( random_bytes( 32 ) );

	$wpdb->insert(
		$table_name,
		array(
			'name'    => sanitize_text_field( $name ),
			'api_key' => $api_key,
		),
		array( '%s', '%s' )
	);

	return $api_key;
}

/**
 * Verify API Key
 *
 * @since 1.0.0
 * @param string $api_key API key to verify.
 * @return object|false Key data on success, false on failure.
 */
function campaignpress_verify_api_key( $api_key ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'campaignpress_api_keys';

	return $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE api_key = %s AND is_active = 1",
			$api_key
		)
	);
}

/**
 * Update API Key Usage
 *
 * @since 1.0.0
 * @param int $key_id API key ID.
 */
function campaignpress_update_api_key_usage( $key_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'campaignpress_api_keys';

	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$table_name}
			SET request_count = request_count + 1, last_used = NOW()
			WHERE id = %d",
			$key_id
		)
	);
}

/**
 * Get API Keys
 *
 * @since 1.0.0
 * @return array Array of API key objects.
 */
function campaignpress_get_api_keys() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'campaignpress_api_keys';

	return $wpdb->get_results(
		"SELECT * FROM {$table_name} ORDER BY created_at DESC"
	);
}

/**
 * Delete API Key
 *
 * @since 1.0.0
 * @param int $key_id API key ID.
 * @return bool True on success, false on failure.
 */
function campaignpress_delete_api_key( $key_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'campaignpress_api_keys';

	$deleted = $wpdb->delete(
		$table_name,
		array( 'id' => $key_id ),
		array( '%d' )
	);

	return $deleted !== false;
}

/**
 * Create API Tables
 *
 * @since 1.0.0
 */
function campaignpress_create_api_tables() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	// API Keys table
	$sql_keys = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}campaignpress_api_keys (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		name varchar(255) NOT NULL,
		api_key varchar(255) NOT NULL,
		is_active tinyint(1) DEFAULT 1,
		request_count bigint(20) DEFAULT 0,
		last_used datetime,
		created_at datetime DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		UNIQUE KEY api_key (api_key)
	) $charset_collate;";

	// API Logs table
	$sql_logs = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}campaignpress_api_logs (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		method varchar(10) NOT NULL,
		endpoint varchar(255) NOT NULL,
		status_code int(3) NOT NULL,
		ip_address varchar(45),
		response_time decimal(10,2),
		created_at datetime DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY created_at (created_at)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql_keys );
	dbDelta( $sql_logs );
}
add_action( 'after_switch_theme', 'campaignpress_create_api_tables' );
