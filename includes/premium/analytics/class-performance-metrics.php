<?php
/**
 * Performance Metrics Class
 *
 * Handles KPI tracking, goal setting, progress monitoring, and metric
 * comparison for campaign performance analysis.
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
 * Performance Metrics Class
 *
 * @since 1.0.0
 */
class CampaignPress_Performance_Metrics {

	/**
	 * Database object
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Metrics table name
	 *
	 * @var string
	 */
	private $metrics_table;

	/**
	 * Metrics data table name
	 *
	 * @var string
	 */
	private $metrics_data_table;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->metrics_table = $wpdb->prefix . 'campaignpress_metrics';
		$this->metrics_data_table = $wpdb->prefix . 'campaignpress_metrics_data';

		// Create tables if they don't exist
		$this->maybe_create_tables();
	}

	/**
	 * Create Metrics Tables
	 *
	 * @since 1.0.0
	 */
	private function maybe_create_tables() {
		$charset_collate = $this->wpdb->get_charset_collate();

		// Metrics definition table
		$sql_metrics = "CREATE TABLE IF NOT EXISTS {$this->metrics_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			metric_key varchar(100) NOT NULL,
			metric_name varchar(255) NOT NULL,
			metric_type varchar(50) NOT NULL,
			description text,
			unit varchar(50),
			goal_value decimal(15,2),
			target_value decimal(15,2),
			alert_threshold decimal(15,2),
			comparison_period varchar(50) DEFAULT 'previous_period',
			is_active tinyint(1) DEFAULT 1,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY metric_key (metric_key),
			KEY is_active (is_active)
		) $charset_collate;";

		// Metrics data table
		$sql_metrics_data = "CREATE TABLE IF NOT EXISTS {$this->metrics_data_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			metric_id bigint(20) NOT NULL,
			metric_value decimal(15,2) NOT NULL,
			recorded_date date NOT NULL,
			notes text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY metric_id (metric_id),
			KEY recorded_date (recorded_date),
			KEY metric_date (metric_id, recorded_date)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_metrics );
		dbDelta( $sql_metrics_data );

		// Create default metrics if tables are newly created
		$this->maybe_create_default_metrics();
	}

	/**
	 * Create Default Metrics
	 *
	 * Creates default metrics if they don't exist. Each metric is checked
	 * individually to allow adding missing metrics even when some already exist.
	 *
	 * @since 1.0.0
	 */
	private function maybe_create_default_metrics() {
		// Note: We no longer return early if metrics exist because create_metric()
		// now checks for existing metrics by key before inserting. This allows
		// missing default metrics to be added even if some already exist.

		$default_metrics = array(
			array(
				'metric_key'        => 'total_fundraising',
				'metric_name'       => 'Total Fundraising',
				'metric_type'       => 'currency',
				'description'       => 'Total amount raised from all sources',
				'unit'              => 'USD',
				'goal_value'        => 100000,
				'target_value'      => 5000,
				'alert_threshold'   => 3000,
			),
			array(
				'metric_key'        => 'donor_count',
				'metric_name'       => 'Donor Count',
				'metric_type'       => 'count',
				'description'       => 'Number of unique donors',
				'unit'              => 'donors',
				'goal_value'        => 1000,
				'target_value'      => 50,
				'alert_threshold'   => 30,
			),
			array(
				'metric_key'        => 'volunteer_count',
				'metric_name'       => 'Volunteer Count',
				'metric_type'       => 'count',
				'description'       => 'Number of active volunteers',
				'unit'              => 'volunteers',
				'goal_value'        => 500,
				'target_value'      => 25,
				'alert_threshold'   => 15,
			),
			array(
				'metric_key'        => 'volunteer_hours',
				'metric_name'       => 'Volunteer Hours',
				'metric_type'       => 'hours',
				'description'       => 'Total volunteer hours logged',
				'unit'              => 'hours',
				'goal_value'        => 10000,
				'target_value'      => 500,
				'alert_threshold'   => 300,
			),
			array(
				'metric_key'        => 'event_attendance',
				'metric_name'       => 'Event Attendance',
				'metric_type'       => 'count',
				'description'       => 'Total event attendance',
				'unit'              => 'attendees',
				'goal_value'        => 5000,
				'target_value'      => 250,
				'alert_threshold'   => 150,
			),
			array(
				'metric_key'        => 'doors_knocked',
				'metric_name'       => 'Doors Knocked',
				'metric_type'       => 'count',
				'description'       => 'Number of doors knocked during canvassing',
				'unit'              => 'doors',
				'goal_value'        => 50000,
				'target_value'      => 2500,
				'alert_threshold'   => 1500,
			),
			array(
				'metric_key'        => 'phone_calls',
				'metric_name'       => 'Phone Calls',
				'metric_type'       => 'count',
				'description'       => 'Number of phone calls made',
				'unit'              => 'calls',
				'goal_value'        => 25000,
				'target_value'      => 1250,
				'alert_threshold'   => 750,
			),
			array(
				'metric_key'        => 'email_open_rate',
				'metric_name'       => 'Email Open Rate',
				'metric_type'       => 'percentage',
				'description'       => 'Email open rate percentage',
				'unit'              => '%',
				'goal_value'        => 30,
				'target_value'      => 25,
				'alert_threshold'   => 20,
			),
			array(
				'metric_key'        => 'conversion_rate',
				'metric_name'       => 'Conversion Rate',
				'metric_type'       => 'percentage',
				'description'       => 'Overall conversion rate',
				'unit'              => '%',
				'goal_value'        => 15,
				'target_value'      => 10,
				'alert_threshold'   => 7,
			),
			array(
				'metric_key'        => 'engagement_score',
				'metric_name'       => 'Average Engagement Score',
				'metric_type'       => 'score',
				'description'       => 'Average contact engagement score',
				'unit'              => 'points',
				'goal_value'        => 75,
				'target_value'      => 60,
				'alert_threshold'   => 50,
			),
		);

		foreach ( $default_metrics as $metric ) {
			$this->create_metric( $metric );
		}
	}

	/**
	 * Create Metric
	 *
	 * @since 1.0.0
	 * @param array $data Metric data.
	 * @return int|false Metric ID on success, false on failure.
	 */
	public function create_metric( $data ) {
		$defaults = array(
			'metric_key'        => '',
			'metric_name'       => '',
			'metric_type'       => 'count',
			'description'       => '',
			'unit'              => '',
			'goal_value'        => 0,
			'target_value'      => 0,
			'alert_threshold'   => 0,
			'comparison_period' => 'previous_period',
			'is_active'         => 1,
		);

		$data = wp_parse_args( $data, $defaults );

		// Validate required fields
		if ( empty( $data['metric_key'] ) || empty( $data['metric_name'] ) ) {
			return false;
		}

		// Check if metric already exists by key to avoid duplicate entry errors
		$existing = $this->get_metric_by_key( $data['metric_key'] );
		if ( $existing ) {
			return $existing->id;
		}

		$inserted = $this->wpdb->insert(
			$this->metrics_table,
			array(
				'metric_key'        => sanitize_key( $data['metric_key'] ),
				'metric_name'       => sanitize_text_field( $data['metric_name'] ),
				'metric_type'       => sanitize_text_field( $data['metric_type'] ),
				'description'       => sanitize_textarea_field( $data['description'] ),
				'unit'              => sanitize_text_field( $data['unit'] ),
				'goal_value'        => floatval( $data['goal_value'] ),
				'target_value'      => floatval( $data['target_value'] ),
				'alert_threshold'   => floatval( $data['alert_threshold'] ),
				'comparison_period' => sanitize_text_field( $data['comparison_period'] ),
				'is_active'         => intval( $data['is_active'] ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%s', '%d' )
		);

		if ( $inserted ) {
			return $this->wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Update Metric
	 *
	 * @since 1.0.0
	 * @param int   $metric_id Metric ID.
	 * @param array $data Updated metric data.
	 * @return bool True on success, false on failure.
	 */
	public function update_metric( $metric_id, $data ) {
		$allowed_fields = array(
			'metric_name',
			'metric_type',
			'description',
			'unit',
			'goal_value',
			'target_value',
			'alert_threshold',
			'comparison_period',
			'is_active',
		);

		$update_data = array();
		$format = array();

		foreach ( $data as $key => $value ) {
			if ( in_array( $key, $allowed_fields ) ) {
				if ( in_array( $key, array( 'goal_value', 'target_value', 'alert_threshold' ) ) ) {
					$update_data[ $key ] = floatval( $value );
					$format[] = '%f';
				} elseif ( $key === 'is_active' ) {
					$update_data[ $key ] = intval( $value );
					$format[] = '%d';
				} else {
					$update_data[ $key ] = sanitize_text_field( $value );
					$format[] = '%s';
				}
			}
		}

		if ( empty( $update_data ) ) {
			return false;
		}

		$updated = $this->wpdb->update(
			$this->metrics_table,
			$update_data,
			array( 'id' => $metric_id ),
			$format,
			array( '%d' )
		);

		return $updated !== false;
	}

	/**
	 * Get Metric
	 *
	 * @since 1.0.0
	 * @param int $metric_id Metric ID.
	 * @return object|null Metric object or null if not found.
	 */
	public function get_metric( $metric_id ) {
		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->metrics_table} WHERE id = %d",
				$metric_id
			)
		);
	}

	/**
	 * Get Metric by Key
	 *
	 * @since 1.0.0
	 * @param string $metric_key Metric key.
	 * @return object|null Metric object or null if not found.
	 */
	public function get_metric_by_key( $metric_key ) {
		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->metrics_table} WHERE metric_key = %s",
				$metric_key
			)
		);
	}

	/**
	 * Get All Metrics
	 *
	 * @since 1.0.0
	 * @param bool $active_only Whether to return only active metrics.
	 * @return array Array of metric objects.
	 */
	public function get_all_metrics( $active_only = true ) {
		$sql = "SELECT * FROM {$this->metrics_table}";

		if ( $active_only ) {
			$sql .= " WHERE is_active = 1";
		}

		$sql .= " ORDER BY metric_name ASC";

		return $this->wpdb->get_results( $sql );
	}

	/**
	 * Delete Metric
	 *
	 * @since 1.0.0
	 * @param int $metric_id Metric ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete_metric( $metric_id ) {
		// Delete metric data first
		$this->wpdb->delete(
			$this->metrics_data_table,
			array( 'metric_id' => $metric_id ),
			array( '%d' )
		);

		// Delete metric definition
		$deleted = $this->wpdb->delete(
			$this->metrics_table,
			array( 'id' => $metric_id ),
			array( '%d' )
		);

		return $deleted !== false;
	}

	/**
	 * Record Metric Value
	 *
	 * @since 1.0.0
	 * @param int    $metric_id Metric ID.
	 * @param float  $value Metric value.
	 * @param string $date Date (Y-m-d format).
	 * @param string $notes Optional notes.
	 * @return int|false Data ID on success, false on failure.
	 */
	public function record_metric_value( $metric_id, $value, $date = null, $notes = '' ) {
		if ( is_null( $date ) ) {
			$date = date( 'Y-m-d' );
		}

		$inserted = $this->wpdb->insert(
			$this->metrics_data_table,
			array(
				'metric_id'     => $metric_id,
				'metric_value'  => floatval( $value ),
				'recorded_date' => $date,
				'notes'         => sanitize_textarea_field( $notes ),
			),
			array( '%d', '%f', '%s', '%s' )
		);

		if ( $inserted ) {
			return $this->wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Get Metric Values
	 *
	 * @since 1.0.0
	 * @param int    $metric_id Metric ID.
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Array of metric values.
	 */
	public function get_metric_values( $metric_id, $start_date = null, $end_date = null ) {
		$sql = "SELECT * FROM {$this->metrics_data_table} WHERE metric_id = %d";
		$params = array( $metric_id );

		if ( ! is_null( $start_date ) ) {
			$sql .= " AND recorded_date >= %s";
			$params[] = $start_date;
		}

		if ( ! is_null( $end_date ) ) {
			$sql .= " AND recorded_date <= %s";
			$params[] = $end_date;
		}

		$sql .= " ORDER BY recorded_date ASC";

		return $this->wpdb->get_results(
			$this->wpdb->prepare( $sql, $params )
		);
	}

	/**
	 * Get Latest Metric Value
	 *
	 * @since 1.0.0
	 * @param int $metric_id Metric ID.
	 * @return object|null Latest metric value or null.
	 */
	public function get_latest_metric_value( $metric_id ) {
		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->metrics_data_table}
				WHERE metric_id = %d
				ORDER BY recorded_date DESC
				LIMIT 1",
				$metric_id
			)
		);
	}

	/**
	 * Get Metric Progress
	 *
	 * Calculates progress towards goal.
	 *
	 * @since 1.0.0
	 * @param int $metric_id Metric ID.
	 * @return array Progress data.
	 */
	public function get_metric_progress( $metric_id ) {
		$metric = $this->get_metric( $metric_id );
		if ( ! $metric ) {
			return array();
		}

		$latest = $this->get_latest_metric_value( $metric_id );
		$current_value = $latest ? floatval( $latest->metric_value ) : 0;

		$goal_value = floatval( $metric->goal_value );
		$target_value = floatval( $metric->target_value );

		$progress_percentage = $goal_value > 0 ? ( $current_value / $goal_value ) * 100 : 0;
		$target_met = $current_value >= $target_value;
		$goal_met = $current_value >= $goal_value;

		return array(
			'metric_id'           => $metric_id,
			'metric_name'         => $metric->metric_name,
			'current_value'       => $current_value,
			'goal_value'          => $goal_value,
			'target_value'        => $target_value,
			'progress_percentage' => $progress_percentage,
			'target_met'          => $target_met,
			'goal_met'            => $goal_met,
			'remaining'           => max( 0, $goal_value - $current_value ),
			'status'              => $this->get_metric_status( $current_value, $metric ),
		);
	}

	/**
	 * Get Metric Status
	 *
	 * @since 1.0.0
	 * @param float  $current_value Current value.
	 * @param object $metric Metric object.
	 * @return string Status (excellent, good, warning, critical).
	 */
	private function get_metric_status( $current_value, $metric ) {
		$goal_value = floatval( $metric->goal_value );
		$target_value = floatval( $metric->target_value );
		$alert_threshold = floatval( $metric->alert_threshold );

		if ( $current_value >= $goal_value ) {
			return 'excellent';
		} elseif ( $current_value >= $target_value ) {
			return 'good';
		} elseif ( $current_value >= $alert_threshold ) {
			return 'warning';
		} else {
			return 'critical';
		}
	}

	/**
	 * Compare Metric to Previous Period
	 *
	 * @since 1.0.0
	 * @param int    $metric_id Metric ID.
	 * @param string $period Period type (day, week, month, year).
	 * @return array Comparison data.
	 */
	public function compare_to_previous_period( $metric_id, $period = 'month' ) {
		$metric = $this->get_metric( $metric_id );
		if ( ! $metric ) {
			return array();
		}

		// Calculate date ranges
		switch ( $period ) {
			case 'day':
				$current_start = date( 'Y-m-d' );
				$current_end = date( 'Y-m-d' );
				$previous_start = date( 'Y-m-d', strtotime( '-1 day' ) );
				$previous_end = date( 'Y-m-d', strtotime( '-1 day' ) );
				break;

			case 'week':
				$current_start = date( 'Y-m-d', strtotime( 'monday this week' ) );
				$current_end = date( 'Y-m-d' );
				$previous_start = date( 'Y-m-d', strtotime( 'monday last week' ) );
				$previous_end = date( 'Y-m-d', strtotime( 'sunday last week' ) );
				break;

			case 'year':
				$current_start = date( 'Y-01-01' );
				$current_end = date( 'Y-m-d' );
				$previous_start = date( 'Y-01-01', strtotime( '-1 year' ) );
				$previous_end = date( 'Y-m-d', strtotime( '-1 year' ) );
				break;

			case 'month':
			default:
				$current_start = date( 'Y-m-01' );
				$current_end = date( 'Y-m-d' );
				$previous_start = date( 'Y-m-01', strtotime( 'first day of last month' ) );
				$previous_end = date( 'Y-m-t', strtotime( 'last day of last month' ) );
				break;
		}

		// Get values for both periods
		$current_values = $this->get_metric_values( $metric_id, $current_start, $current_end );
		$previous_values = $this->get_metric_values( $metric_id, $previous_start, $previous_end );

		// Calculate totals or averages based on metric type
		$current_total = $this->calculate_metric_aggregate( $current_values, $metric->metric_type );
		$previous_total = $this->calculate_metric_aggregate( $previous_values, $metric->metric_type );

		// Calculate change
		$change = $current_total - $previous_total;
		$percentage_change = $previous_total > 0 ? ( $change / $previous_total ) * 100 : 0;

		return array(
			'metric_id'         => $metric_id,
			'metric_name'       => $metric->metric_name,
			'period'            => $period,
			'current_period'    => array(
				'start' => $current_start,
				'end'   => $current_end,
				'value' => $current_total,
			),
			'previous_period'   => array(
				'start' => $previous_start,
				'end'   => $previous_end,
				'value' => $previous_total,
			),
			'change'            => $change,
			'percentage_change' => $percentage_change,
			'trend'             => $change > 0 ? 'up' : ( $change < 0 ? 'down' : 'stable' ),
		);
	}

	/**
	 * Calculate Metric Aggregate
	 *
	 * @since 1.0.0
	 * @param array  $values Array of metric value objects.
	 * @param string $metric_type Metric type.
	 * @return float Aggregated value.
	 */
	private function calculate_metric_aggregate( $values, $metric_type ) {
		if ( empty( $values ) ) {
			return 0;
		}

		$total = 0;
		$count = count( $values );

		foreach ( $values as $value ) {
			$total += floatval( $value->metric_value );
		}

		// For percentages and scores, use average
		if ( in_array( $metric_type, array( 'percentage', 'score' ) ) ) {
			return $count > 0 ? $total / $count : 0;
		}

		// For other types, use sum
		return $total;
	}

	/**
	 * Get Metrics Below Target
	 *
	 * Returns metrics that are below their alert threshold.
	 *
	 * @since 1.0.0
	 * @return array Array of metrics below target.
	 */
	public function get_metrics_below_target() {
		$metrics = $this->get_all_metrics( true );
		$below_target = array();

		foreach ( $metrics as $metric ) {
			$latest = $this->get_latest_metric_value( $metric->id );
			$current_value = $latest ? floatval( $latest->metric_value ) : 0;

			if ( $current_value < floatval( $metric->alert_threshold ) ) {
				$below_target[] = array(
					'metric_id'       => $metric->id,
					'metric_name'     => $metric->metric_name,
					'current_value'   => $current_value,
					'alert_threshold' => floatval( $metric->alert_threshold ),
					'target_value'    => floatval( $metric->target_value ),
					'deficit'         => floatval( $metric->alert_threshold ) - $current_value,
				);
			}
		}

		return $below_target;
	}

	/**
	 * Get Metrics Dashboard Data
	 *
	 * Returns comprehensive data for the metrics dashboard.
	 *
	 * @since 1.0.0
	 * @return array Dashboard data.
	 */
	public function get_dashboard_data() {
		$metrics = $this->get_all_metrics( true );
		$dashboard_data = array(
			'metrics'        => array(),
			'summary'        => array(
				'total_metrics'   => count( $metrics ),
				'goals_met'       => 0,
				'targets_met'     => 0,
				'needs_attention' => 0,
			),
			'alerts'         => array(),
		);

		foreach ( $metrics as $metric ) {
			$progress = $this->get_metric_progress( $metric->id );
			$comparison = $this->compare_to_previous_period( $metric->id, 'month' );

			$dashboard_data['metrics'][] = array(
				'metric'     => $metric,
				'progress'   => $progress,
				'comparison' => $comparison,
			);

			// Update summary
			if ( $progress['goal_met'] ) {
				$dashboard_data['summary']['goals_met']++;
			}
			if ( $progress['target_met'] ) {
				$dashboard_data['summary']['targets_met']++;
			}
			if ( $progress['status'] === 'critical' || $progress['status'] === 'warning' ) {
				$dashboard_data['summary']['needs_attention']++;

				// Add to alerts
				$dashboard_data['alerts'][] = array(
					'metric_id'   => $metric->id,
					'metric_name' => $metric->metric_name,
					'status'      => $progress['status'],
					'message'     => sprintf(
						'%s is at %s (threshold: %s)',
						$metric->metric_name,
						$progress['current_value'] . ' ' . $metric->unit,
						$metric->alert_threshold . ' ' . $metric->unit
					),
				);
			}
		}

		return $dashboard_data;
	}

	/**
	 * Get Metric Chart Data
	 *
	 * Returns formatted data for Chart.js visualization.
	 *
	 * @since 1.0.0
	 * @param int    $metric_id Metric ID.
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Chart data.
	 */
	public function get_metric_chart_data( $metric_id, $start_date = null, $end_date = null ) {
		$metric = $this->get_metric( $metric_id );
		if ( ! $metric ) {
			return array();
		}

		if ( is_null( $start_date ) ) {
			$start_date = date( 'Y-m-d', strtotime( '-30 days' ) );
		}
		if ( is_null( $end_date ) ) {
			$end_date = date( 'Y-m-d' );
		}

		$values = $this->get_metric_values( $metric_id, $start_date, $end_date );

		$labels = array();
		$data = array();

		foreach ( $values as $value ) {
			$labels[] = date( 'M d', strtotime( $value->recorded_date ) );
			$data[] = floatval( $value->metric_value );
		}

		return array(
			'labels' => $labels,
			'datasets' => array(
				array(
					'label' => $metric->metric_name,
					'data'  => $data,
					'borderColor' => '#3b82f6',
					'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
					'tension' => 0.4,
				),
			),
			'goal_line' => floatval( $metric->goal_value ),
			'target_line' => floatval( $metric->target_value ),
		);
	}

	/**
	 * Auto-Record Metrics
	 *
	 * Automatically records current values for all active metrics.
	 * Should be called via cron job.
	 *
	 * @since 1.0.0
	 */
	public function auto_record_metrics() {
		$metrics = $this->get_all_metrics( true );

		foreach ( $metrics as $metric ) {
			$value = $this->calculate_current_metric_value( $metric );
			if ( $value !== false ) {
				$this->record_metric_value(
					$metric->id,
					$value,
					date( 'Y-m-d' ),
					'Auto-recorded'
				);
			}
		}
	}

	/**
	 * Calculate Current Metric Value
	 *
	 * Calculates the current value for a metric based on its key.
	 *
	 * @since 1.0.0
	 * @param object $metric Metric object.
	 * @return float|false Current value or false if cannot calculate.
	 */
	private function calculate_current_metric_value( $metric ) {
		// This would integrate with the Campaign Analytics class
		// to fetch real-time data for each metric type
		$analytics = isset( $GLOBALS['campaignpress_analytics'] ) ? $GLOBALS['campaignpress_analytics'] : null;

		if ( ! $analytics ) {
			return false;
		}

		$today = date( 'Y-m-d' );

		switch ( $metric->metric_key ) {
			case 'total_fundraising':
				$data = $analytics->get_fundraising_analytics( 1 );
				return isset( $data['summary']['total_raised'] ) ? $data['summary']['total_raised'] : 0;

			case 'donor_count':
				$data = $analytics->get_fundraising_analytics( 1 );
				return isset( $data['summary']['unique_donors'] ) ? $data['summary']['unique_donors'] : 0;

			case 'volunteer_count':
				$data = $analytics->get_volunteer_analytics( 1 );
				return isset( $data['summary']['active_volunteers'] ) ? $data['summary']['active_volunteers'] : 0;

			case 'volunteer_hours':
				$data = $analytics->get_volunteer_analytics( 1 );
				return isset( $data['summary']['total_hours'] ) ? $data['summary']['total_hours'] : 0;

			case 'event_attendance':
				$data = $analytics->get_event_analytics( 1 );
				return isset( $data['summary']['total_attendees'] ) ? $data['summary']['total_attendees'] : 0;

			case 'engagement_score':
				$data = $analytics->get_engagement_analytics( 1 );
				return isset( $data['summary']['avg_engagement'] ) ? $data['summary']['avg_engagement'] : 0;

			default:
				return false;
		}
	}

	/**
	 * Export Metrics to CSV
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return string|false File URL on success, false on failure.
	 */
	public function export_metrics_to_csv( $start_date = null, $end_date = null ) {
		if ( is_null( $start_date ) ) {
			$start_date = date( 'Y-m-d', strtotime( '-30 days' ) );
		}
		if ( is_null( $end_date ) ) {
			$end_date = date( 'Y-m-d' );
		}

		$metrics = $this->get_all_metrics( true );

		$upload_dir = wp_upload_dir();
		$filename = 'campaignpress-metrics-' . date( 'Y-m-d-His' ) . '.csv';
		$filepath = $upload_dir['path'] . '/' . $filename;

		$fp = fopen( $filepath, 'w' );
		if ( ! $fp ) {
			return false;
		}

		// Write headers
		fputcsv( $fp, array( 'Metric', 'Current Value', 'Goal', 'Target', 'Progress %', 'Status' ) );

		// Write metric data
		foreach ( $metrics as $metric ) {
			$progress = $this->get_metric_progress( $metric->id );

			fputcsv( $fp, array(
				$metric->metric_name,
				$progress['current_value'] . ' ' . $metric->unit,
				$progress['goal_value'] . ' ' . $metric->unit,
				$progress['target_value'] . ' ' . $metric->unit,
				round( $progress['progress_percentage'], 2 ) . '%',
				ucfirst( $progress['status'] ),
			) );
		}

		fclose( $fp );

		return $upload_dir['url'] . '/' . $filename;
	}
}
