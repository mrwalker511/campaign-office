<?php
/**
 * Analytics Module Initialization
 *
 * Initializes the CampaignPress analytics system including campaign metrics,
 * performance tracking, and data visualization capabilities.
 *
 * @package CampaignPress
 * @subpackage Analytics
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize Analytics Module
 *
 * @since 1.0.0
 */
function campaignpress_analytics_init() {
	// Require analytics classes
	require_once dirname( __FILE__ ) . '/class-campaign-analytics.php';
	require_once dirname( __FILE__ ) . '/class-performance-metrics.php';
	require_once dirname( __FILE__ ) . '/analytics-dummy-data.php';

	// Initialize campaign analytics
	if ( class_exists( 'CampaignPress_Campaign_Analytics' ) ) {
		$GLOBALS['campaignpress_analytics'] = new CampaignPress_Campaign_Analytics();
	}

	// Initialize performance metrics
	if ( class_exists( 'CampaignPress_Performance_Metrics' ) ) {
		$GLOBALS['campaignpress_metrics'] = new CampaignPress_Performance_Metrics();
	}

	// Add admin menu items
	add_action( 'admin_menu', 'campaignpress_analytics_admin_menu', 15 );

	// Enqueue analytics scripts and styles
	add_action( 'admin_enqueue_scripts', 'campaignpress_analytics_enqueue_assets' );

	// Register AJAX handlers
	add_action( 'wp_ajax_campaignpress_get_analytics_data', 'campaignpress_ajax_get_analytics_data' );
	add_action( 'wp_ajax_campaignpress_export_analytics', 'campaignpress_ajax_export_analytics' );
	add_action( 'wp_ajax_campaignpress_update_metric', 'campaignpress_ajax_update_metric' );
	add_action( 'wp_ajax_campaignpress_add_metric', 'campaignpress_ajax_add_metric' );
	add_action( 'wp_ajax_campaignpress_get_goals', 'campaignpress_ajax_get_goals' );
	add_action( 'wp_ajax_campaignpress_save_goals', 'campaignpress_ajax_save_goals' );
}
add_action( 'init', 'campaignpress_analytics_init', 5 );

/**
 * Add Analytics Admin Menu
 *
 * @since 1.0.0
 */
function campaignpress_analytics_admin_menu() {
	// Main analytics dashboard
	add_menu_page(
		__( 'Analytics', 'campaignpress' ),
		__( 'Analytics', 'campaignpress' ),
		'manage_options',
		'campaignpress-analytics',
		'campaignpress_analytics_dashboard_page',
		'dashicons-chart-area',
		30
	);

	// Campaign Analytics submenu
	add_submenu_page(
		'campaignpress-analytics',
		__( 'Campaign Analytics', 'campaignpress' ),
		__( 'Campaign Analytics', 'campaignpress' ),
		'manage_options',
		'campaignpress-analytics',
		'campaignpress_analytics_dashboard_page'
	);

	// Performance Metrics submenu
	add_submenu_page(
		'campaignpress-analytics',
		__( 'Performance Metrics', 'campaignpress' ),
		__( 'Performance Metrics', 'campaignpress' ),
		'manage_options',
		'campaignpress-metrics',
		'campaignpress_metrics_dashboard_page'
	);

	// Reports submenu
	add_submenu_page(
		'campaignpress-analytics',
		__( 'Reports', 'campaignpress' ),
		__( 'Reports', 'campaignpress' ),
		'manage_options',
		'campaignpress-reports',
		'campaignpress_reports_page'
	);
}

/**
 * Enqueue Analytics Assets
 *
 * @since 1.0.0
 * @param string $hook Current admin page hook.
 */
function campaignpress_analytics_enqueue_assets( $hook ) {
	// Only load on analytics pages
	if ( strpos( $hook, 'campaignpress-analytics' ) === false &&
	     strpos( $hook, 'campaignpress-metrics' ) === false &&
	     strpos( $hook, 'campaignpress-reports' ) === false ) {
		return;
	}

	// Chart.js for data visualization (bundled locally for WordPress.org compliance)
	$chartjs_url = apply_filters(
		'campaignpress_chartjs_url',
		get_template_directory_uri() . '/assets/vendor/chartjs/chart.umd.min.js'
	);
	wp_enqueue_script(
		'chartjs',
		$chartjs_url,
		array(),
		'4.4.0',
		true
	);

	// Leaflet for geographic maps (bundled locally for WordPress.org compliance)
	$leaflet_js_url = apply_filters(
		'campaignpress_leaflet_js_url',
		get_template_directory_uri() . '/assets/vendor/leaflet/leaflet.js'
	);
	wp_enqueue_script(
		'leaflet',
		$leaflet_js_url,
		array(),
		'1.9.4',
		true
	);

	$leaflet_css_url = apply_filters(
		'campaignpress_leaflet_css_url',
		get_template_directory_uri() . '/assets/vendor/leaflet/leaflet.css'
	);
	wp_enqueue_style(
		'leaflet',
		$leaflet_css_url,
		array(),
		'1.9.4'
	);

	// Analytics custom scripts
	wp_enqueue_script(
		'campaignpress-analytics',
		get_template_directory_uri() . '/assets/js/analytics.js',
		array( 'jquery', 'chartjs' ),
		CAMPAIGNPRESS_VERSION,
		true
	);

	// Analytics styles
	wp_enqueue_style(
		'campaignpress-analytics',
		get_template_directory_uri() . '/assets/css/analytics.css',
		array(),
		CAMPAIGNPRESS_VERSION
	);

	// Localize script with data
	wp_localize_script(
		'campaignpress-analytics',
		'campaignpressAnalytics',
		array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'campaignpress_analytics_nonce' ),
			'i18n'       => array(
				'loading'       => __( 'Loading analytics...', 'campaignpress' ),
				'error'         => __( 'Error loading data', 'campaignpress' ),
				'exportSuccess' => __( 'Export completed successfully', 'campaignpress' ),
				'exportError'   => __( 'Export failed', 'campaignpress' ),
			),
		)
	);
}

/**
 * Analytics Dashboard Page
 *
 * @since 1.0.0
 */
function campaignpress_analytics_dashboard_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'campaignpress' ) );
	}

	$analytics = $GLOBALS['campaignpress_analytics'];
	?>
	<div class="wrap campaignpress-analytics-dashboard">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div class="campaignpress-analytics-header">
			<div class="date-range-selector">
				<label for="analytics-date-range"><?php esc_html_e( 'Date Range:', 'campaignpress' ); ?></label>
				<select id="analytics-date-range" name="date_range">
					<option value="7"><?php esc_html_e( 'Last 7 Days', 'campaignpress' ); ?></option>
					<option value="30" selected><?php esc_html_e( 'Last 30 Days', 'campaignpress' ); ?></option>
					<option value="90"><?php esc_html_e( 'Last 90 Days', 'campaignpress' ); ?></option>
					<option value="365"><?php esc_html_e( 'Last Year', 'campaignpress' ); ?></option>
					<option value="custom"><?php esc_html_e( 'Custom Range', 'campaignpress' ); ?></option>
				</select>
			</div>

			<div class="export-buttons">
				<button class="button button-secondary" id="export-csv">
					<?php esc_html_e( 'Export CSV', 'campaignpress' ); ?>
				</button>
				<button class="button button-secondary" id="export-pdf">
					<?php esc_html_e( 'Export PDF', 'campaignpress' ); ?>
				</button>
			</div>
		</div>

		<div id="analytics-content">
			<!-- Content loaded via JavaScript -->
			<div class="loading-spinner">
				<span class="spinner is-active"></span>
				<p><?php esc_html_e( 'Loading analytics...', 'campaignpress' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Performance Metrics Dashboard Page
 *
 * @since 1.0.0
 */
function campaignpress_metrics_dashboard_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'campaignpress' ) );
	}

	$metrics = $GLOBALS['campaignpress_metrics'];
	?>
	<div class="wrap campaignpress-metrics-dashboard">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div class="metrics-actions">
			<button class="button button-primary" id="add-new-metric">
				<?php esc_html_e( 'Add New Metric', 'campaignpress' ); ?>
			</button>
			<button class="button button-secondary" id="set-goals">
				<?php esc_html_e( 'Set Goals', 'campaignpress' ); ?>
			</button>
		</div>

		<div id="metrics-content">
			<!-- Content loaded via JavaScript -->
			<div class="loading-spinner">
				<span class="spinner is-active"></span>
				<p><?php esc_html_e( 'Loading metrics...', 'campaignpress' ); ?></p>
			</div>
		</div>
	</div>

	<!-- Add Metric Modal -->
	<div id="add-metric-modal" class="cp-modal" style="display:none;">
		<div class="cp-modal-overlay"></div>
		<div class="cp-modal-content">
			<div class="cp-modal-header">
				<h2><?php esc_html_e( 'Add New Metric', 'campaignpress' ); ?></h2>
				<button class="cp-modal-close" aria-label="<?php esc_attr_e( 'Close', 'campaignpress' ); ?>">&times;</button>
			</div>
			<div class="cp-modal-body">
				<form id="add-metric-form">
					<?php wp_nonce_field( 'campaignpress_add_metric', 'add_metric_nonce' ); ?>

					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="metric_key"><?php esc_html_e( 'Metric Key', 'campaignpress' ); ?> <span class="required">*</span></label>
							</th>
							<td>
								<input type="text" id="metric_key" name="metric_key" class="regular-text" required pattern="[a-z0-9_]+" title="<?php esc_attr_e( 'Only lowercase letters, numbers, and underscores', 'campaignpress' ); ?>">
								<p class="description"><?php esc_html_e( 'Unique identifier (e.g., social_media_followers). Only lowercase letters, numbers, and underscores.', 'campaignpress' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="metric_name"><?php esc_html_e( 'Metric Name', 'campaignpress' ); ?> <span class="required">*</span></label>
							</th>
							<td>
								<input type="text" id="metric_name" name="metric_name" class="regular-text" required>
								<p class="description"><?php esc_html_e( 'Display name (e.g., Social Media Followers)', 'campaignpress' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="metric_type"><?php esc_html_e( 'Metric Type', 'campaignpress' ); ?></label>
							</th>
							<td>
								<select id="metric_type" name="metric_type" class="regular-text">
									<option value="count"><?php esc_html_e( 'Count', 'campaignpress' ); ?></option>
									<option value="currency"><?php esc_html_e( 'Currency', 'campaignpress' ); ?></option>
									<option value="percentage"><?php esc_html_e( 'Percentage', 'campaignpress' ); ?></option>
									<option value="hours"><?php esc_html_e( 'Hours', 'campaignpress' ); ?></option>
									<option value="score"><?php esc_html_e( 'Score', 'campaignpress' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="metric_description"><?php esc_html_e( 'Description', 'campaignpress' ); ?></label>
							</th>
							<td>
								<textarea id="metric_description" name="description" class="large-text" rows="3"></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="metric_unit"><?php esc_html_e( 'Unit', 'campaignpress' ); ?></label>
							</th>
							<td>
								<input type="text" id="metric_unit" name="unit" class="regular-text" placeholder="<?php esc_attr_e( 'e.g., followers, USD, %', 'campaignpress' ); ?>">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="goal_value"><?php esc_html_e( 'Goal Value', 'campaignpress' ); ?></label>
							</th>
							<td>
								<input type="number" id="goal_value" name="goal_value" class="regular-text" step="0.01" min="0" value="0">
								<p class="description"><?php esc_html_e( 'The ultimate goal to achieve', 'campaignpress' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="target_value"><?php esc_html_e( 'Target Value', 'campaignpress' ); ?></label>
							</th>
							<td>
								<input type="number" id="target_value" name="target_value" class="regular-text" step="0.01" min="0" value="0">
								<p class="description"><?php esc_html_e( 'Periodic target (e.g., monthly target)', 'campaignpress' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="alert_threshold"><?php esc_html_e( 'Alert Threshold', 'campaignpress' ); ?></label>
							</th>
							<td>
								<input type="number" id="alert_threshold" name="alert_threshold" class="regular-text" step="0.01" min="0" value="0">
								<p class="description"><?php esc_html_e( 'Show warning if below this value', 'campaignpress' ); ?></p>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<div class="cp-modal-footer">
				<button type="button" class="button cp-modal-cancel"><?php esc_html_e( 'Cancel', 'campaignpress' ); ?></button>
				<button type="button" class="button button-primary" id="save-metric-btn"><?php esc_html_e( 'Add Metric', 'campaignpress' ); ?></button>
			</div>
		</div>
	</div>

	<!-- Goals Modal -->
	<div id="goals-modal" class="cp-modal" style="display:none;">
		<div class="cp-modal-overlay"></div>
		<div class="cp-modal-content">
			<div class="cp-modal-header">
				<h2><?php esc_html_e( 'Set Campaign Goals', 'campaignpress' ); ?></h2>
				<button class="cp-modal-close" aria-label="<?php esc_attr_e( 'Close', 'campaignpress' ); ?>">&times;</button>
			</div>
			<div class="cp-modal-body">
				<form id="goals-form">
					<?php wp_nonce_field( 'campaignpress_save_goals', 'save_goals_nonce' ); ?>
					<p class="description" style="margin-bottom: 20px;">
						<?php esc_html_e( 'Set your campaign goals to track progress on the dashboard. Leave blank to hide that goal.', 'campaignpress' ); ?>
					</p>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="goal_fundraising"><?php esc_html_e( 'Fundraising Goal', 'campaignpress' ); ?></label>
							</th>
							<td>
								<input type="number" id="goal_fundraising" name="fundraising" class="regular-text" step="0.01" min="0" value="0">
								<p class="description"><?php esc_html_e( 'Total fundraising target (e.g., 50000)', 'campaignpress' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="goal_volunteers"><?php esc_html_e( 'Volunteer Goal', 'campaignpress' ); ?></label>
							</th>
							<td>
								<input type="number" id="goal_volunteers" name="volunteers" class="regular-text" step="1" min="0" value="0">
								<p class="description"><?php esc_html_e( 'Total number of volunteers to recruit', 'campaignpress' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="goal_events"><?php esc_html_e( 'Events Goal', 'campaignpress' ); ?></label>
							</th>
							<td>
								<input type="number" id="goal_events" name="events" class="regular-text" step="1" min="0" value="0">
								<p class="description"><?php esc_html_e( 'Total number of events to host', 'campaignpress' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="goal_contacts"><?php esc_html_e( 'Contacts Goal', 'campaignpress' ); ?></label>
							</th>
							<td>
								<input type="number" id="goal_contacts" name="contacts" class="regular-text" step="1" min="0" value="0">
								<p class="description"><?php esc_html_e( 'Total number of contacts to add to CRM', 'campaignpress' ); ?></p>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<div class="cp-modal-footer">
				<button type="button" class="button cp-modal-cancel"><?php esc_html_e( 'Cancel', 'campaignpress' ); ?></button>
				<button type="button" class="button button-primary" id="save-goals-btn"><?php esc_html_e( 'Save Goals', 'campaignpress' ); ?></button>
			</div>
		</div>
	</div>

	<style>
		.cp-modal {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			z-index: 100000;
		}
		.cp-modal-overlay {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.6);
		}
		.cp-modal-content {
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			background: #fff;
			border-radius: 8px;
			width: 90%;
			max-width: 600px;
			max-height: 90vh;
			overflow: hidden;
			display: flex;
			flex-direction: column;
		}
		.cp-modal-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 15px 20px;
			border-bottom: 1px solid #ddd;
			background: #f6f7f7;
		}
		.cp-modal-header h2 {
			margin: 0;
			font-size: 18px;
		}
		.cp-modal-close {
			background: none;
			border: none;
			font-size: 24px;
			cursor: pointer;
			color: #666;
			padding: 0;
			line-height: 1;
		}
		.cp-modal-close:hover {
			color: #d63638;
		}
		.cp-modal-body {
			padding: 20px;
			overflow-y: auto;
			flex: 1;
		}
		.cp-modal-body .form-table th {
			padding: 10px 0;
			width: 150px;
		}
		.cp-modal-body .form-table td {
			padding: 10px 0;
		}
		.cp-modal-footer {
			display: flex;
			justify-content: flex-end;
			gap: 10px;
			padding: 15px 20px;
			border-top: 1px solid #ddd;
			background: #f6f7f7;
		}
		.required {
			color: #d63638;
		}
	</style>
	<?php
}

/**
 * Reports Page
 *
 * @since 1.0.0
 */
function campaignpress_reports_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'campaignpress' ) );
	}
	?>
	<div class="wrap campaignpress-reports">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div class="report-templates">
			<h2><?php esc_html_e( 'Available Reports', 'campaignpress' ); ?></h2>
			<ul class="report-list">
				<li><a href="#" class="report-link" data-report="fundraising"><?php esc_html_e( 'Fundraising Report', 'campaignpress' ); ?></a></li>
				<li><a href="#" class="report-link" data-report="volunteer"><?php esc_html_e( 'Volunteer Report', 'campaignpress' ); ?></a></li>
				<li><a href="#" class="report-link" data-report="event"><?php esc_html_e( 'Event Report', 'campaignpress' ); ?></a></li>
				<li><a href="#" class="report-link" data-report="engagement"><?php esc_html_e( 'Engagement Report', 'campaignpress' ); ?></a></li>
				<li><a href="#" class="report-link" data-report="geographic"><?php esc_html_e( 'Geographic Report', 'campaignpress' ); ?></a></li>
				<li><a href="#" class="report-link" data-report="comprehensive"><?php esc_html_e( 'Comprehensive Report', 'campaignpress' ); ?></a></li>
			</ul>
		</div>

		<div id="report-content">
			<!-- Report content loaded via JavaScript -->
		</div>
	</div>
	<?php
}

/**
 * AJAX Handler: Get Analytics Data
 *
 * @since 1.0.0
 */
function campaignpress_ajax_get_analytics_data() {
	check_ajax_referer( 'campaignpress_analytics_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'campaignpress' ) ) );
	}

	$analytics = $GLOBALS['campaignpress_analytics'];
	$date_range = isset( $_POST['date_range'] ) ? intval( $_POST['date_range'] ) : 30;
	$report_type = isset( $_POST['report_type'] ) ? sanitize_text_field( $_POST['report_type'] ) : 'dashboard';

	$data = array();

	switch ( $report_type ) {
		case 'dashboard':
			$data = $analytics->get_dashboard_data( $date_range );
			break;
		case 'fundraising':
			$data = $analytics->get_fundraising_analytics( $date_range );
			break;
		case 'volunteer':
			$data = $analytics->get_volunteer_analytics( $date_range );
			break;
		case 'event':
			$data = $analytics->get_event_analytics( $date_range );
			break;
		case 'engagement':
			$data = $analytics->get_engagement_analytics( $date_range );
			break;
		case 'geographic':
			$data = $analytics->get_geographic_data( $date_range );
			break;
	}

	wp_send_json_success( $data );
}

/**
 * AJAX Handler: Export Analytics
 *
 * @since 1.0.0
 */
function campaignpress_ajax_export_analytics() {
	check_ajax_referer( 'campaignpress_analytics_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'campaignpress' ) ) );
	}

	$analytics = $GLOBALS['campaignpress_analytics'];
	$format = isset( $_POST['format'] ) ? sanitize_text_field( $_POST['format'] ) : 'csv';
	$date_range = isset( $_POST['date_range'] ) ? intval( $_POST['date_range'] ) : 30;

	if ( $format === 'csv' ) {
		$file_url = $analytics->export_to_csv( $date_range );
	} elseif ( $format === 'pdf' ) {
		$file_url = $analytics->export_to_pdf( $date_range );
	}

	if ( ! empty( $file_url ) ) {
		wp_send_json_success( array( 'file_url' => $file_url ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Export failed', 'campaignpress' ) ) );
	}
}

/**
 * AJAX Handler: Update Metric
 *
 * @since 1.0.0
 */
function campaignpress_ajax_update_metric() {
	check_ajax_referer( 'campaignpress_analytics_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'campaignpress' ) ) );
	}

	$metrics = $GLOBALS['campaignpress_metrics'];
	$metric_id = isset( $_POST['metric_id'] ) ? intval( $_POST['metric_id'] ) : 0;
	$metric_data = isset( $_POST['metric_data'] ) ? $_POST['metric_data'] : array();

	$result = $metrics->update_metric( $metric_id, $metric_data );

	if ( $result ) {
		wp_send_json_success( array( 'message' => __( 'Metric updated successfully', 'campaignpress' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to update metric', 'campaignpress' ) ) );
	}
}

/**
 * AJAX Handler: Add New Metric
 *
 * @since 2.0.0
 */
function campaignpress_ajax_add_metric() {
	check_ajax_referer( 'campaignpress_analytics_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'campaignpress' ) ) );
	}

	// Validate required fields
	$metric_key = isset( $_POST['metric_key'] ) ? sanitize_key( $_POST['metric_key'] ) : '';
	$metric_name = isset( $_POST['metric_name'] ) ? sanitize_text_field( $_POST['metric_name'] ) : '';

	if ( empty( $metric_key ) || empty( $metric_name ) ) {
		wp_send_json_error( array( 'message' => __( 'Metric key and name are required', 'campaignpress' ) ) );
	}

	// Prepare metric data
	$metric_data = array(
		'metric_key'        => $metric_key,
		'metric_name'       => $metric_name,
		'metric_type'       => isset( $_POST['metric_type'] ) ? sanitize_text_field( $_POST['metric_type'] ) : 'count',
		'description'       => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '',
		'unit'              => isset( $_POST['unit'] ) ? sanitize_text_field( $_POST['unit'] ) : '',
		'goal_value'        => isset( $_POST['goal_value'] ) ? floatval( $_POST['goal_value'] ) : 0,
		'target_value'      => isset( $_POST['target_value'] ) ? floatval( $_POST['target_value'] ) : 0,
		'alert_threshold'   => isset( $_POST['alert_threshold'] ) ? floatval( $_POST['alert_threshold'] ) : 0,
		'comparison_period' => 'previous_period',
		'is_active'         => 1,
	);

	$metrics = $GLOBALS['campaignpress_metrics'];

	// Check if metric with this key already exists
	$existing = $metrics->get_metric_by_key( $metric_key );
	if ( $existing ) {
		wp_send_json_error( array( 'message' => __( 'A metric with this key already exists', 'campaignpress' ) ) );
	}

	// Create the metric
	$result = $metrics->create_metric( $metric_data );

	if ( $result ) {
		wp_send_json_success( array(
			'message'   => __( 'Metric added successfully', 'campaignpress' ),
			'metric_id' => $result,
		) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to add metric', 'campaignpress' ) ) );
	}
}

/**
 * AJAX handler to get campaign goals
 *
 * @since 2.0.0
 */
function campaignpress_ajax_get_goals() {
	check_ajax_referer( 'campaignpress_analytics_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'campaignpress' ) ) );
	}

	$goals = get_option( 'campaignpress_campaign_goals', array(
		'fundraising' => 0,
		'volunteers'  => 0,
		'events'      => 0,
		'contacts'    => 0,
	) );

	wp_send_json_success( array( 'goals' => $goals ) );
}

/**
 * AJAX handler to save campaign goals
 *
 * @since 2.0.0
 */
function campaignpress_ajax_save_goals() {
	check_ajax_referer( 'campaignpress_analytics_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'campaignpress' ) ) );
	}

	$goals = array(
		'fundraising' => isset( $_POST['fundraising'] ) ? floatval( $_POST['fundraising'] ) : 0,
		'volunteers'  => isset( $_POST['volunteers'] ) ? absint( $_POST['volunteers'] ) : 0,
		'events'      => isset( $_POST['events'] ) ? absint( $_POST['events'] ) : 0,
		'contacts'    => isset( $_POST['contacts'] ) ? absint( $_POST['contacts'] ) : 0,
	);

	$result = update_option( 'campaignpress_campaign_goals', $goals );

	if ( $result !== false ) {
		wp_send_json_success( array( 'message' => __( 'Goals saved successfully', 'campaignpress' ) ) );
	} else {
		// Check if the goals are unchanged (which also returns false)
		$current_goals = get_option( 'campaignpress_campaign_goals', array() );
		if ( $current_goals === $goals ) {
			wp_send_json_success( array( 'message' => __( 'Goals saved successfully', 'campaignpress' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to save goals', 'campaignpress' ) ) );
		}
	}
}
