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
		__( 'Analytics', 'campaign-office' ),
		__( 'Analytics', 'campaign-office' ),
		'manage_options',
		'campaignpress-analytics',
		'campaignpress_analytics_dashboard_page',
		'dashicons-chart-area',
		30
	);

	// Campaign Analytics submenu
	add_submenu_page(
		'campaignpress-analytics',
		__( 'Campaign Analytics', 'campaign-office' ),
		__( 'Campaign Analytics', 'campaign-office' ),
		'manage_options',
		'campaignpress-analytics',
		'campaignpress_analytics_dashboard_page'
	);

	// Performance Metrics submenu
	add_submenu_page(
		'campaignpress-analytics',
		__( 'Performance Metrics', 'campaign-office' ),
		__( 'Performance Metrics', 'campaign-office' ),
		'manage_options',
		'campaignpress-metrics',
		'campaignpress_metrics_dashboard_page'
	);

	// Reports submenu
	add_submenu_page(
		'campaignpress-analytics',
		__( 'Reports', 'campaign-office' ),
		__( 'Reports', 'campaign-office' ),
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

	// Chart.js for data visualization
	wp_enqueue_script(
		'chartjs',
		'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
		array(),
		'4.4.0',
		true
	);

	// Leaflet for geographic maps
	wp_enqueue_script(
		'leaflet',
		'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
		array(),
		'1.9.4',
		true
	);

	wp_enqueue_style(
		'leaflet',
		'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
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
				'loading'       => __( 'Loading analytics...', 'campaign-office' ),
				'error'         => __( 'Error loading data', 'campaign-office' ),
				'exportSuccess' => __( 'Export completed successfully', 'campaign-office' ),
				'exportError'   => __( 'Export failed', 'campaign-office' ),
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
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'campaign-office' ) );
	}

	$analytics = $GLOBALS['campaignpress_analytics'];
	?>
	<div class="wrap campaignpress-analytics-dashboard">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div class="campaignpress-analytics-header">
			<div class="date-range-selector">
				<label for="analytics-date-range"><?php esc_html_e( 'Date Range:', 'campaign-office' ); ?></label>
				<select id="analytics-date-range" name="date_range">
					<option value="7"><?php esc_html_e( 'Last 7 Days', 'campaign-office' ); ?></option>
					<option value="30" selected><?php esc_html_e( 'Last 30 Days', 'campaign-office' ); ?></option>
					<option value="90"><?php esc_html_e( 'Last 90 Days', 'campaign-office' ); ?></option>
					<option value="365"><?php esc_html_e( 'Last Year', 'campaign-office' ); ?></option>
					<option value="custom"><?php esc_html_e( 'Custom Range', 'campaign-office' ); ?></option>
				</select>
			</div>

			<div class="export-buttons">
				<button class="button button-secondary" id="export-csv">
					<?php esc_html_e( 'Export CSV', 'campaign-office' ); ?>
				</button>
				<button class="button button-secondary" id="export-pdf">
					<?php esc_html_e( 'Export PDF', 'campaign-office' ); ?>
				</button>
			</div>
		</div>

		<div id="analytics-content">
			<!-- Content loaded via JavaScript -->
			<div class="loading-spinner">
				<span class="spinner is-active"></span>
				<p><?php esc_html_e( 'Loading analytics...', 'campaign-office' ); ?></p>
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
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'campaign-office' ) );
	}

	$metrics = $GLOBALS['campaignpress_metrics'];
	?>
	<div class="wrap campaignpress-metrics-dashboard">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div class="metrics-actions">
			<button class="button button-primary" id="add-new-metric">
				<?php esc_html_e( 'Add New Metric', 'campaign-office' ); ?>
			</button>
			<button class="button button-secondary" id="set-goals">
				<?php esc_html_e( 'Set Goals', 'campaign-office' ); ?>
			</button>
		</div>

		<div id="metrics-content">
			<!-- Content loaded via JavaScript -->
			<div class="loading-spinner">
				<span class="spinner is-active"></span>
				<p><?php esc_html_e( 'Loading metrics...', 'campaign-office' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Reports Page
 *
 * @since 1.0.0
 */
function campaignpress_reports_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'campaign-office' ) );
	}
	?>
	<div class="wrap campaignpress-reports">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div class="report-templates">
			<h2><?php esc_html_e( 'Available Reports', 'campaign-office' ); ?></h2>
			<ul class="report-list">
				<li><a href="#" class="report-link" data-report="fundraising"><?php esc_html_e( 'Fundraising Report', 'campaign-office' ); ?></a></li>
				<li><a href="#" class="report-link" data-report="volunteer"><?php esc_html_e( 'Volunteer Report', 'campaign-office' ); ?></a></li>
				<li><a href="#" class="report-link" data-report="event"><?php esc_html_e( 'Event Report', 'campaign-office' ); ?></a></li>
				<li><a href="#" class="report-link" data-report="engagement"><?php esc_html_e( 'Engagement Report', 'campaign-office' ); ?></a></li>
				<li><a href="#" class="report-link" data-report="geographic"><?php esc_html_e( 'Geographic Report', 'campaign-office' ); ?></a></li>
				<li><a href="#" class="report-link" data-report="comprehensive"><?php esc_html_e( 'Comprehensive Report', 'campaign-office' ); ?></a></li>
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
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'campaign-office' ) ) );
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
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'campaign-office' ) ) );
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
		wp_send_json_error( array( 'message' => __( 'Export failed', 'campaign-office' ) ) );
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
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'campaign-office' ) ) );
	}

	$metrics = $GLOBALS['campaignpress_metrics'];
	$metric_id = isset( $_POST['metric_id'] ) ? intval( $_POST['metric_id'] ) : 0;
	$metric_data = isset( $_POST['metric_data'] ) ? $_POST['metric_data'] : array();

	$result = $metrics->update_metric( $metric_id, $metric_data );

	if ( $result ) {
		wp_send_json_success( array( 'message' => __( 'Metric updated successfully', 'campaign-office' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to update metric', 'campaign-office' ) ) );
	}
}
