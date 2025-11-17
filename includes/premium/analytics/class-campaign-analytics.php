<?php
/**
 * Campaign Analytics Class
 *
 * Provides comprehensive analytics for campaign operations including
 * fundraising, volunteer management, events, contact engagement,
 * and geographic distribution.
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
 * Campaign Analytics Class
 *
 * @since 1.0.0
 */
class CampaignPress_Campaign_Analytics {

	/**
	 * Database object
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
		$this->wpdb = $wpdb;
	}

	/**
	 * Get Dashboard Data
	 *
	 * Returns key metrics for the analytics dashboard including
	 * funds raised, volunteer count, events, and contacts.
	 *
	 * @since 1.0.0
	 * @param int $days Number of days to include in analysis.
	 * @return array Dashboard metrics data.
	 */
	public function get_dashboard_data( $days = 30 ) {
		$start_date = date( 'Y-m-d', strtotime( "-{$days} days" ) );
		$end_date = date( 'Y-m-d' );

		return array(
			'key_metrics' => array(
				'total_raised'      => $this->get_total_raised( $start_date, $end_date ),
				'total_volunteers'  => $this->get_total_volunteers( $start_date, $end_date ),
				'total_events'      => $this->get_total_events( $start_date, $end_date ),
				'total_contacts'    => $this->get_total_contacts( $start_date, $end_date ),
				'avg_donation'      => $this->get_average_donation( $start_date, $end_date ),
				'volunteer_hours'   => $this->get_total_volunteer_hours( $start_date, $end_date ),
				'event_attendance'  => $this->get_total_event_attendance( $start_date, $end_date ),
				'engagement_score'  => $this->get_avg_engagement_score( $start_date, $end_date ),
			),
			'trends' => array(
				'fundraising'  => $this->get_fundraising_trend( $start_date, $end_date ),
				'volunteers'   => $this->get_volunteer_trend( $start_date, $end_date ),
				'events'       => $this->get_event_trend( $start_date, $end_date ),
				'engagement'   => $this->get_engagement_trend( $start_date, $end_date ),
			),
			'recent_activity' => array(
				'donations'   => $this->get_recent_donations( 10 ),
				'volunteers'  => $this->get_recent_volunteers( 10 ),
				'events'      => $this->get_upcoming_events( 5 ),
			),
			'date_range' => array(
				'start' => $start_date,
				'end'   => $end_date,
				'days'  => $days,
			),
		);
	}

	/**
	 * Get Fundraising Analytics
	 *
	 * Detailed fundraising analytics including sources, date ranges,
	 * average donations, and donor retention.
	 *
	 * @since 1.0.0
	 * @param int $days Number of days to analyze.
	 * @return array Fundraising analytics data.
	 */
	public function get_fundraising_analytics( $days = 30 ) {
		$start_date = date( 'Y-m-d', strtotime( "-{$days} days" ) );
		$end_date = date( 'Y-m-d' );

		return array(
			'summary' => array(
				'total_raised'        => $this->get_total_raised( $start_date, $end_date ),
				'total_donations'     => $this->get_donation_count( $start_date, $end_date ),
				'average_donation'    => $this->get_average_donation( $start_date, $end_date ),
				'median_donation'     => $this->get_median_donation( $start_date, $end_date ),
				'unique_donors'       => $this->get_unique_donor_count( $start_date, $end_date ),
				'recurring_donors'    => $this->get_recurring_donor_count( $start_date, $end_date ),
			),
			'by_source' => $this->get_donations_by_source( $start_date, $end_date ),
			'by_amount_range' => $this->get_donations_by_amount_range( $start_date, $end_date ),
			'timeline' => $this->get_fundraising_timeline( $start_date, $end_date ),
			'donor_retention' => array(
				'new_donors'       => $this->get_new_donor_count( $start_date, $end_date ),
				'returning_donors' => $this->get_returning_donor_count( $start_date, $end_date ),
				'retention_rate'   => $this->calculate_donor_retention_rate( $start_date, $end_date ),
				'lapsed_donors'    => $this->get_lapsed_donor_count(),
			),
			'top_donors' => $this->get_top_donors( $start_date, $end_date, 10 ),
			'goal_progress' => $this->get_fundraising_goal_progress(),
		);
	}

	/**
	 * Get Volunteer Analytics
	 *
	 * Comprehensive volunteer analytics including recruitment,
	 * retention, hours, and activity patterns.
	 *
	 * @since 1.0.0
	 * @param int $days Number of days to analyze.
	 * @return array Volunteer analytics data.
	 */
	public function get_volunteer_analytics( $days = 30 ) {
		$start_date = date( 'Y-m-d', strtotime( "-{$days} days" ) );
		$end_date = date( 'Y-m-d' );

		return array(
			'summary' => array(
				'total_volunteers'    => $this->get_total_volunteers( $start_date, $end_date ),
				'active_volunteers'   => $this->get_active_volunteers( $start_date, $end_date ),
				'new_volunteers'      => $this->get_new_volunteers( $start_date, $end_date ),
				'total_hours'         => $this->get_total_volunteer_hours( $start_date, $end_date ),
				'avg_hours_per_volunteer' => $this->get_avg_hours_per_volunteer( $start_date, $end_date ),
			),
			'recruitment' => array(
				'by_source'        => $this->get_volunteers_by_source( $start_date, $end_date ),
				'conversion_rate'  => $this->get_volunteer_conversion_rate( $start_date, $end_date ),
				'timeline'         => $this->get_volunteer_recruitment_timeline( $start_date, $end_date ),
			),
			'retention' => array(
				'retention_rate'   => $this->calculate_volunteer_retention_rate( $start_date, $end_date ),
				'churn_rate'       => $this->calculate_volunteer_churn_rate( $start_date, $end_date ),
				'inactive_count'   => $this->get_inactive_volunteer_count(),
			),
			'activity' => array(
				'by_type'          => $this->get_volunteer_activity_by_type( $start_date, $end_date ),
				'by_day_of_week'   => $this->get_volunteer_activity_by_day( $start_date, $end_date ),
				'by_time_of_day'   => $this->get_volunteer_activity_by_time( $start_date, $end_date ),
			),
			'leaderboard' => $this->get_volunteer_leaderboard( $start_date, $end_date, 20 ),
			'top_canvassers' => $this->get_top_canvassers( $start_date, $end_date, 10 ),
		);
	}

	/**
	 * Get Event Analytics
	 *
	 * Event analytics including attendance, RSVP conversion,
	 * and popular event types.
	 *
	 * @since 1.0.0
	 * @param int $days Number of days to analyze.
	 * @return array Event analytics data.
	 */
	public function get_event_analytics( $days = 30 ) {
		$start_date = date( 'Y-m-d', strtotime( "-{$days} days" ) );
		$end_date = date( 'Y-m-d' );

		return array(
			'summary' => array(
				'total_events'       => $this->get_total_events( $start_date, $end_date ),
				'total_attendees'    => $this->get_total_event_attendance( $start_date, $end_date ),
				'avg_attendance'     => $this->get_avg_event_attendance( $start_date, $end_date ),
				'total_rsvps'        => $this->get_total_rsvps( $start_date, $end_date ),
			),
			'by_type' => $this->get_events_by_type( $start_date, $end_date ),
			'by_location' => $this->get_events_by_location( $start_date, $end_date ),
			'rsvp_conversion' => array(
				'rsvp_count'         => $this->get_total_rsvps( $start_date, $end_date ),
				'attendance_count'   => $this->get_total_event_attendance( $start_date, $end_date ),
				'conversion_rate'    => $this->calculate_rsvp_conversion_rate( $start_date, $end_date ),
				'no_show_rate'       => $this->calculate_no_show_rate( $start_date, $end_date ),
			),
			'timeline' => $this->get_event_timeline( $start_date, $end_date ),
			'popular_events' => $this->get_popular_events( $start_date, $end_date, 10 ),
			'upcoming_events' => $this->get_upcoming_events( 10 ),
		);
	}

	/**
	 * Get Engagement Analytics
	 *
	 * Contact engagement scoring and trends analysis.
	 *
	 * @since 1.0.0
	 * @param int $days Number of days to analyze.
	 * @return array Engagement analytics data.
	 */
	public function get_engagement_analytics( $days = 30 ) {
		$start_date = date( 'Y-m-d', strtotime( "-{$days} days" ) );
		$end_date = date( 'Y-m-d' );

		return array(
			'summary' => array(
				'total_contacts'     => $this->get_total_contacts( $start_date, $end_date ),
				'avg_engagement'     => $this->get_avg_engagement_score( $start_date, $end_date ),
				'highly_engaged'     => $this->get_highly_engaged_count( $start_date, $end_date ),
				'at_risk'            => $this->get_at_risk_contacts_count(),
			),
			'engagement_distribution' => $this->get_engagement_score_distribution(),
			'engagement_trend' => $this->get_engagement_trend( $start_date, $end_date ),
			'by_activity_type' => $this->get_engagement_by_activity_type( $start_date, $end_date ),
			'top_engaged_contacts' => $this->get_top_engaged_contacts( 20 ),
			'engagement_factors' => array(
				'email_opens'        => $this->get_email_engagement_stats( $start_date, $end_date ),
				'event_attendance'   => $this->get_event_engagement_stats( $start_date, $end_date ),
				'volunteer_activity' => $this->get_volunteer_engagement_stats( $start_date, $end_date ),
				'donations'          => $this->get_donation_engagement_stats( $start_date, $end_date ),
			),
		);
	}

	/**
	 * Get Geographic Data
	 *
	 * Geographic distribution data for mapping visualization.
	 * Returns data only - rendering handled by JavaScript/Leaflet.
	 *
	 * @since 1.0.0
	 * @param int $days Number of days to analyze.
	 * @return array Geographic distribution data.
	 */
	public function get_geographic_data( $days = 30 ) {
		$start_date = date( 'Y-m-d', strtotime( "-{$days} days" ) );
		$end_date = date( 'Y-m-d' );

		return array(
			'contacts_by_location' => $this->get_contacts_by_location(),
			'donations_by_location' => $this->get_donations_by_location( $start_date, $end_date ),
			'volunteers_by_location' => $this->get_volunteers_by_location( $start_date, $end_date ),
			'events_by_location' => $this->get_events_by_location( $start_date, $end_date ),
			'heat_map_data' => $this->get_activity_heatmap_data( $start_date, $end_date ),
		);
	}

	/**
	 * Generate Leaderboards
	 *
	 * Creates various leaderboards for top performers.
	 *
	 * @since 1.0.0
	 * @param string $type Leaderboard type (volunteers, donors, canvassers).
	 * @param int    $days Number of days to analyze.
	 * @param int    $limit Number of results to return.
	 * @return array Leaderboard data.
	 */
	public function get_leaderboard( $type = 'volunteers', $days = 30, $limit = 20 ) {
		$start_date = date( 'Y-m-d', strtotime( "-{$days} days" ) );
		$end_date = date( 'Y-m-d' );

		switch ( $type ) {
			case 'volunteers':
				return $this->get_volunteer_leaderboard( $start_date, $end_date, $limit );
			case 'donors':
				return $this->get_top_donors( $start_date, $end_date, $limit );
			case 'canvassers':
				return $this->get_top_canvassers( $start_date, $end_date, $limit );
			default:
				return array();
		}
	}

	/**
	 * Export Analytics to CSV
	 *
	 * Generates a CSV export of analytics data.
	 *
	 * @since 1.0.0
	 * @param int $days Number of days to include.
	 * @return string|false File URL on success, false on failure.
	 */
	public function export_to_csv( $days = 30 ) {
		$data = $this->get_dashboard_data( $days );

		$upload_dir = wp_upload_dir();
		$filename = 'campaignpress-analytics-' . date( 'Y-m-d-His' ) . '.csv';
		$filepath = $upload_dir['path'] . '/' . $filename;

		$fp = fopen( $filepath, 'w' );
		if ( ! $fp ) {
			return false;
		}

		// Write headers
		fputcsv( $fp, array( 'Metric', 'Value' ) );

		// Write key metrics
		foreach ( $data['key_metrics'] as $metric => $value ) {
			fputcsv( $fp, array( ucwords( str_replace( '_', ' ', $metric ) ), $value ) );
		}

		fclose( $fp );

		return $upload_dir['url'] . '/' . $filename;
	}

	/**
	 * Export Analytics to PDF
	 *
	 * Generates a PDF export of analytics data.
	 * Placeholder for PDF generation functionality.
	 *
	 * @since 1.0.0
	 * @param int $days Number of days to include.
	 * @return string|false File URL on success, false on failure.
	 */
	public function export_to_pdf( $days = 30 ) {
		// Placeholder for PDF generation
		// Would integrate with a library like TCPDF or FPDF
		return false;
	}

	// ========================================================================
	// PRIVATE HELPER METHODS - Fundraising
	// ========================================================================

	/**
	 * Get Total Amount Raised
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return float Total amount raised.
	 */
	private function get_total_raised( $start_date, $end_date ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_donations';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COALESCE(SUM(amount), 0)
				FROM {$table_name}
				WHERE status = 'completed'
				AND donation_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return floatval( $result );
	}

	/**
	 * Get Donation Count
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return int Number of donations.
	 */
	private function get_donation_count( $start_date, $end_date ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_donations';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$table_name}
				WHERE status = 'completed'
				AND donation_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return intval( $result );
	}

	/**
	 * Get Average Donation Amount
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return float Average donation amount.
	 */
	private function get_average_donation( $start_date, $end_date ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_donations';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COALESCE(AVG(amount), 0)
				FROM {$table_name}
				WHERE status = 'completed'
				AND donation_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return floatval( $result );
	}

	/**
	 * Get Median Donation Amount
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return float Median donation amount.
	 */
	private function get_median_donation( $start_date, $end_date ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_donations';

		$amounts = $this->wpdb->get_col(
			$this->wpdb->prepare(
				"SELECT amount
				FROM {$table_name}
				WHERE status = 'completed'
				AND donation_date BETWEEN %s AND %s
				ORDER BY amount",
				$start_date,
				$end_date
			)
		);

		if ( empty( $amounts ) ) {
			return 0;
		}

		$count = count( $amounts );
		$middle = floor( $count / 2 );

		if ( $count % 2 == 0 ) {
			return ( $amounts[ $middle - 1 ] + $amounts[ $middle ] ) / 2;
		} else {
			return $amounts[ $middle ];
		}
	}

	/**
	 * Get Unique Donor Count
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return int Number of unique donors.
	 */
	private function get_unique_donor_count( $start_date, $end_date ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_donations';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(DISTINCT contact_id)
				FROM {$table_name}
				WHERE status = 'completed'
				AND donation_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return intval( $result );
	}

	/**
	 * Get Recurring Donor Count
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return int Number of recurring donors.
	 */
	private function get_recurring_donor_count( $start_date, $end_date ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_donations';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(DISTINCT contact_id)
				FROM {$table_name}
				WHERE status = 'completed'
				AND donation_date BETWEEN %s AND %s
				AND contact_id IN (
					SELECT contact_id
					FROM {$table_name}
					WHERE status = 'completed'
					GROUP BY contact_id
					HAVING COUNT(*) > 1
				)",
				$start_date,
				$end_date
			)
		);

		return intval( $result );
	}

	/**
	 * Get New Donor Count
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return int Number of new donors.
	 */
	private function get_new_donor_count( $start_date, $end_date ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_donations';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(DISTINCT contact_id)
				FROM {$table_name}
				WHERE status = 'completed'
				AND donation_date BETWEEN %s AND %s
				AND contact_id NOT IN (
					SELECT DISTINCT contact_id
					FROM {$table_name}
					WHERE status = 'completed'
					AND donation_date < %s
				)",
				$start_date,
				$end_date,
				$start_date
			)
		);

		return intval( $result );
	}

	/**
	 * Get Returning Donor Count
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return int Number of returning donors.
	 */
	private function get_returning_donor_count( $start_date, $end_date ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_donations';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(DISTINCT contact_id)
				FROM {$table_name}
				WHERE status = 'completed'
				AND donation_date BETWEEN %s AND %s
				AND contact_id IN (
					SELECT DISTINCT contact_id
					FROM {$table_name}
					WHERE status = 'completed'
					AND donation_date < %s
				)",
				$start_date,
				$end_date,
				$start_date
			)
		);

		return intval( $result );
	}

	/**
	 * Get Lapsed Donor Count
	 *
	 * Donors who haven't donated in the last 90 days.
	 *
	 * @since 1.0.0
	 * @return int Number of lapsed donors.
	 */
	private function get_lapsed_donor_count() {
		$table_name = $this->wpdb->prefix . 'campaignpress_donations';
		$cutoff_date = date( 'Y-m-d', strtotime( '-90 days' ) );

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(DISTINCT contact_id)
				FROM {$table_name}
				WHERE status = 'completed'
				AND contact_id NOT IN (
					SELECT DISTINCT contact_id
					FROM {$table_name}
					WHERE status = 'completed'
					AND donation_date >= %s
				)",
				$cutoff_date
			)
		);

		return intval( $result );
	}

	/**
	 * Calculate Donor Retention Rate
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return float Retention rate as percentage.
	 */
	private function calculate_donor_retention_rate( $start_date, $end_date ) {
		$returning = $this->get_returning_donor_count( $start_date, $end_date );
		$total = $this->get_unique_donor_count( $start_date, $end_date );

		if ( $total == 0 ) {
			return 0;
		}

		return ( $returning / $total ) * 100;
	}

	/**
	 * Get Donations by Source
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Donations grouped by source.
	 */
	private function get_donations_by_source( $start_date, $end_date ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_donations';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT source, COUNT(*) as count, SUM(amount) as total
				FROM {$table_name}
				WHERE status = 'completed'
				AND donation_date BETWEEN %s AND %s
				GROUP BY source
				ORDER BY total DESC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Donations by Amount Range
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Donations grouped by amount ranges.
	 */
	private function get_donations_by_amount_range( $start_date, $end_date ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_donations';

		$ranges = array(
			'0-25'      => array( 0, 25 ),
			'26-50'     => array( 26, 50 ),
			'51-100'    => array( 51, 100 ),
			'101-250'   => array( 101, 250 ),
			'251-500'   => array( 251, 500 ),
			'501-1000'  => array( 501, 1000 ),
			'1001+'     => array( 1001, 999999999 ),
		);

		$results = array();
		foreach ( $ranges as $label => $range ) {
			$count = $this->wpdb->get_var(
				$this->wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$table_name}
					WHERE status = 'completed'
					AND donation_date BETWEEN %s AND %s
					AND amount >= %f AND amount <= %f",
					$start_date,
					$end_date,
					$range[0],
					$range[1]
				)
			);

			$results[] = array(
				'range' => $label,
				'count' => intval( $count ),
			);
		}

		return $results;
	}

	/**
	 * Get Fundraising Timeline
	 *
	 * Daily fundraising data for time-series charts.
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Daily fundraising data.
	 */
	private function get_fundraising_timeline( $start_date, $end_date ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_donations';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT DATE(donation_date) as date, COUNT(*) as count, SUM(amount) as total
				FROM {$table_name}
				WHERE status = 'completed'
				AND donation_date BETWEEN %s AND %s
				GROUP BY DATE(donation_date)
				ORDER BY date ASC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Top Donors
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param int    $limit Number of donors to return.
	 * @return array Top donors data.
	 */
	private function get_top_donors( $start_date, $end_date, $limit = 10 ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_donations';
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT c.first_name, c.last_name, c.email,
				COUNT(d.id) as donation_count, SUM(d.amount) as total_amount
				FROM {$table_name} d
				LEFT JOIN {$contacts_table} c ON d.contact_id = c.id
				WHERE d.status = 'completed'
				AND d.donation_date BETWEEN %s AND %s
				GROUP BY d.contact_id
				ORDER BY total_amount DESC
				LIMIT %d",
				$start_date,
				$end_date,
				$limit
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Recent Donations
	 *
	 * @since 1.0.0
	 * @param int $limit Number of donations to return.
	 * @return array Recent donations.
	 */
	private function get_recent_donations( $limit = 10 ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_donations';
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT d.*, c.first_name, c.last_name, c.email
				FROM {$table_name} d
				LEFT JOIN {$contacts_table} c ON d.contact_id = c.id
				WHERE d.status = 'completed'
				ORDER BY d.donation_date DESC
				LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Fundraising Goal Progress
	 *
	 * @since 1.0.0
	 * @return array Goal progress data.
	 */
	private function get_fundraising_goal_progress() {
		$goal = get_option( 'campaignpress_fundraising_goal', 100000 );
		$raised = $this->get_total_raised( '1970-01-01', date( 'Y-m-d' ) );

		return array(
			'goal'       => floatval( $goal ),
			'raised'     => $raised,
			'remaining'  => max( 0, $goal - $raised ),
			'percentage' => $goal > 0 ? ( $raised / $goal ) * 100 : 0,
		);
	}

	/**
	 * Get Fundraising Trend
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Trend data with percentage change.
	 */
	private function get_fundraising_trend( $start_date, $end_date ) {
		$current_total = $this->get_total_raised( $start_date, $end_date );

		$days_diff = ( strtotime( $end_date ) - strtotime( $start_date ) ) / 86400;
		$previous_start = date( 'Y-m-d', strtotime( $start_date ) - ( $days_diff * 86400 ) );
		$previous_end = date( 'Y-m-d', strtotime( $start_date ) - 86400 );

		$previous_total = $this->get_total_raised( $previous_start, $previous_end );

		$change = $current_total - $previous_total;
		$percentage_change = $previous_total > 0 ? ( $change / $previous_total ) * 100 : 0;

		return array(
			'current'           => $current_total,
			'previous'          => $previous_total,
			'change'            => $change,
			'percentage_change' => $percentage_change,
			'trend'             => $change >= 0 ? 'up' : 'down',
		);
	}

	// ========================================================================
	// PRIVATE HELPER METHODS - Volunteers
	// ========================================================================

	/**
	 * Get Total Volunteers
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return int Total number of volunteers.
	 */
	private function get_total_volunteers( $start_date, $end_date ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_volunteers';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$table_name}
				WHERE signup_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return intval( $result );
	}

	/**
	 * Get Active Volunteers
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return int Number of active volunteers.
	 */
	private function get_active_volunteers( $start_date, $end_date ) {
		$activity_table = $this->wpdb->prefix . 'campaignpress_volunteer_activity';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(DISTINCT volunteer_id)
				FROM {$activity_table}
				WHERE activity_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return intval( $result );
	}

	/**
	 * Get New Volunteers
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return int Number of new volunteers.
	 */
	private function get_new_volunteers( $start_date, $end_date ) {
		return $this->get_total_volunteers( $start_date, $end_date );
	}

	/**
	 * Get Total Volunteer Hours
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return float Total volunteer hours.
	 */
	private function get_total_volunteer_hours( $start_date, $end_date ) {
		$activity_table = $this->wpdb->prefix . 'campaignpress_volunteer_activity';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COALESCE(SUM(hours), 0)
				FROM {$activity_table}
				WHERE activity_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return floatval( $result );
	}

	/**
	 * Get Average Hours Per Volunteer
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return float Average hours per volunteer.
	 */
	private function get_avg_hours_per_volunteer( $start_date, $end_date ) {
		$total_hours = $this->get_total_volunteer_hours( $start_date, $end_date );
		$active_volunteers = $this->get_active_volunteers( $start_date, $end_date );

		if ( $active_volunteers == 0 ) {
			return 0;
		}

		return $total_hours / $active_volunteers;
	}

	/**
	 * Get Volunteers by Source
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Volunteers grouped by source.
	 */
	private function get_volunteers_by_source( $start_date, $end_date ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_volunteers';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT source, COUNT(*) as count
				FROM {$table_name}
				WHERE signup_date BETWEEN %s AND %s
				GROUP BY source
				ORDER BY count DESC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Volunteer Conversion Rate
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return float Conversion rate as percentage.
	 */
	private function get_volunteer_conversion_rate( $start_date, $end_date ) {
		$signups = $this->get_total_volunteers( $start_date, $end_date );
		$active = $this->get_active_volunteers( $start_date, $end_date );

		if ( $signups == 0 ) {
			return 0;
		}

		return ( $active / $signups ) * 100;
	}

	/**
	 * Get Volunteer Recruitment Timeline
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Daily volunteer recruitment data.
	 */
	private function get_volunteer_recruitment_timeline( $start_date, $end_date ) {
		$table_name = $this->wpdb->prefix . 'campaignpress_volunteers';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT DATE(signup_date) as date, COUNT(*) as count
				FROM {$table_name}
				WHERE signup_date BETWEEN %s AND %s
				GROUP BY DATE(signup_date)
				ORDER BY date ASC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Calculate Volunteer Retention Rate
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return float Retention rate as percentage.
	 */
	private function calculate_volunteer_retention_rate( $start_date, $end_date ) {
		$days_diff = ( strtotime( $end_date ) - strtotime( $start_date ) ) / 86400;
		$previous_start = date( 'Y-m-d', strtotime( $start_date ) - ( $days_diff * 86400 ) );
		$previous_end = date( 'Y-m-d', strtotime( $start_date ) - 86400 );

		$previous_active = $this->get_active_volunteers( $previous_start, $previous_end );
		$still_active = $this->get_volunteers_still_active( $previous_start, $previous_end, $start_date, $end_date );

		if ( $previous_active == 0 ) {
			return 0;
		}

		return ( $still_active / $previous_active ) * 100;
	}

	/**
	 * Get Volunteers Still Active
	 *
	 * @since 1.0.0
	 * @param string $previous_start Previous period start.
	 * @param string $previous_end Previous period end.
	 * @param string $current_start Current period start.
	 * @param string $current_end Current period end.
	 * @return int Number of volunteers still active.
	 */
	private function get_volunteers_still_active( $previous_start, $previous_end, $current_start, $current_end ) {
		$activity_table = $this->wpdb->prefix . 'campaignpress_volunteer_activity';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(DISTINCT volunteer_id)
				FROM {$activity_table}
				WHERE activity_date BETWEEN %s AND %s
				AND volunteer_id IN (
					SELECT DISTINCT volunteer_id
					FROM {$activity_table}
					WHERE activity_date BETWEEN %s AND %s
				)",
				$current_start,
				$current_end,
				$previous_start,
				$previous_end
			)
		);

		return intval( $result );
	}

	/**
	 * Calculate Volunteer Churn Rate
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return float Churn rate as percentage.
	 */
	private function calculate_volunteer_churn_rate( $start_date, $end_date ) {
		$retention_rate = $this->calculate_volunteer_retention_rate( $start_date, $end_date );
		return 100 - $retention_rate;
	}

	/**
	 * Get Inactive Volunteer Count
	 *
	 * @since 1.0.0
	 * @return int Number of inactive volunteers.
	 */
	private function get_inactive_volunteer_count() {
		$activity_table = $this->wpdb->prefix . 'campaignpress_volunteer_activity';
		$volunteers_table = $this->wpdb->prefix . 'campaignpress_volunteers';
		$cutoff_date = date( 'Y-m-d', strtotime( '-60 days' ) );

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$volunteers_table}
				WHERE id NOT IN (
					SELECT DISTINCT volunteer_id
					FROM {$activity_table}
					WHERE activity_date >= %s
				)",
				$cutoff_date
			)
		);

		return intval( $result );
	}

	/**
	 * Get Volunteer Activity by Type
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Activity grouped by type.
	 */
	private function get_volunteer_activity_by_type( $start_date, $end_date ) {
		$activity_table = $this->wpdb->prefix . 'campaignpress_volunteer_activity';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT activity_type, COUNT(*) as count, SUM(hours) as total_hours
				FROM {$activity_table}
				WHERE activity_date BETWEEN %s AND %s
				GROUP BY activity_type
				ORDER BY total_hours DESC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Volunteer Activity by Day of Week
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Activity grouped by day of week.
	 */
	private function get_volunteer_activity_by_day( $start_date, $end_date ) {
		$activity_table = $this->wpdb->prefix . 'campaignpress_volunteer_activity';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT DAYNAME(activity_date) as day, COUNT(*) as count, SUM(hours) as total_hours
				FROM {$activity_table}
				WHERE activity_date BETWEEN %s AND %s
				GROUP BY DAYNAME(activity_date), DAYOFWEEK(activity_date)
				ORDER BY DAYOFWEEK(activity_date)",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Volunteer Activity by Time of Day
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Activity grouped by time of day.
	 */
	private function get_volunteer_activity_by_time( $start_date, $end_date ) {
		$activity_table = $this->wpdb->prefix . 'campaignpress_volunteer_activity';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT
					CASE
						WHEN HOUR(activity_date) BETWEEN 6 AND 11 THEN 'Morning'
						WHEN HOUR(activity_date) BETWEEN 12 AND 17 THEN 'Afternoon'
						WHEN HOUR(activity_date) BETWEEN 18 AND 21 THEN 'Evening'
						ELSE 'Night'
					END as time_period,
					COUNT(*) as count,
					SUM(hours) as total_hours
				FROM {$activity_table}
				WHERE activity_date BETWEEN %s AND %s
				GROUP BY time_period
				ORDER BY FIELD(time_period, 'Morning', 'Afternoon', 'Evening', 'Night')",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Volunteer Leaderboard
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param int    $limit Number of volunteers to return.
	 * @return array Top volunteers by hours.
	 */
	private function get_volunteer_leaderboard( $start_date, $end_date, $limit = 20 ) {
		$activity_table = $this->wpdb->prefix . 'campaignpress_volunteer_activity';
		$volunteers_table = $this->wpdb->prefix . 'campaignpress_volunteers';
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT c.first_name, c.last_name, c.email,
				COUNT(a.id) as activity_count, SUM(a.hours) as total_hours
				FROM {$activity_table} a
				LEFT JOIN {$volunteers_table} v ON a.volunteer_id = v.id
				LEFT JOIN {$contacts_table} c ON v.contact_id = c.id
				WHERE a.activity_date BETWEEN %s AND %s
				GROUP BY a.volunteer_id
				ORDER BY total_hours DESC
				LIMIT %d",
				$start_date,
				$end_date,
				$limit
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Top Canvassers
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param int    $limit Number of canvassers to return.
	 * @return array Top canvassers.
	 */
	private function get_top_canvassers( $start_date, $end_date, $limit = 10 ) {
		$canvassing_table = $this->wpdb->prefix . 'campaignpress_canvassing_results';
		$volunteers_table = $this->wpdb->prefix . 'campaignpress_volunteers';
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT c.first_name, c.last_name, c.email,
				COUNT(cr.id) as doors_knocked,
				SUM(CASE WHEN cr.response = 'positive' THEN 1 ELSE 0 END) as positive_responses
				FROM {$canvassing_table} cr
				LEFT JOIN {$volunteers_table} v ON cr.volunteer_id = v.id
				LEFT JOIN {$contacts_table} c ON v.contact_id = c.id
				WHERE cr.canvass_date BETWEEN %s AND %s
				GROUP BY cr.volunteer_id
				ORDER BY doors_knocked DESC
				LIMIT %d",
				$start_date,
				$end_date,
				$limit
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Recent Volunteers
	 *
	 * @since 1.0.0
	 * @param int $limit Number of volunteers to return.
	 * @return array Recent volunteers.
	 */
	private function get_recent_volunteers( $limit = 10 ) {
		$volunteers_table = $this->wpdb->prefix . 'campaignpress_volunteers';
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT v.*, c.first_name, c.last_name, c.email
				FROM {$volunteers_table} v
				LEFT JOIN {$contacts_table} c ON v.contact_id = c.id
				ORDER BY v.signup_date DESC
				LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Volunteer Trend
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Trend data with percentage change.
	 */
	private function get_volunteer_trend( $start_date, $end_date ) {
		$current_total = $this->get_total_volunteers( $start_date, $end_date );

		$days_diff = ( strtotime( $end_date ) - strtotime( $start_date ) ) / 86400;
		$previous_start = date( 'Y-m-d', strtotime( $start_date ) - ( $days_diff * 86400 ) );
		$previous_end = date( 'Y-m-d', strtotime( $start_date ) - 86400 );

		$previous_total = $this->get_total_volunteers( $previous_start, $previous_end );

		$change = $current_total - $previous_total;
		$percentage_change = $previous_total > 0 ? ( $change / $previous_total ) * 100 : 0;

		return array(
			'current'           => $current_total,
			'previous'          => $previous_total,
			'change'            => $change,
			'percentage_change' => $percentage_change,
			'trend'             => $change >= 0 ? 'up' : 'down',
		);
	}

	// ========================================================================
	// PRIVATE HELPER METHODS - Events
	// ========================================================================

	/**
	 * Get Total Events
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return int Total number of events.
	 */
	private function get_total_events( $start_date, $end_date ) {
		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$this->wpdb->posts}
				WHERE post_type = 'campaign_event'
				AND post_status = 'publish'
				AND post_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return intval( $result );
	}

	/**
	 * Get Total Event Attendance
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return int Total event attendance.
	 */
	private function get_total_event_attendance( $start_date, $end_date ) {
		$attendance_table = $this->wpdb->prefix . 'campaignpress_event_attendance';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$attendance_table}
				WHERE status = 'attended'
				AND event_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return intval( $result );
	}

	/**
	 * Get Average Event Attendance
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return float Average attendance per event.
	 */
	private function get_avg_event_attendance( $start_date, $end_date ) {
		$total_attendance = $this->get_total_event_attendance( $start_date, $end_date );
		$total_events = $this->get_total_events( $start_date, $end_date );

		if ( $total_events == 0 ) {
			return 0;
		}

		return $total_attendance / $total_events;
	}

	/**
	 * Get Total RSVPs
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return int Total RSVPs.
	 */
	private function get_total_rsvps( $start_date, $end_date ) {
		$attendance_table = $this->wpdb->prefix . 'campaignpress_event_attendance';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$attendance_table}
				WHERE status IN ('rsvp', 'attended')
				AND rsvp_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return intval( $result );
	}

	/**
	 * Calculate RSVP Conversion Rate
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return float Conversion rate as percentage.
	 */
	private function calculate_rsvp_conversion_rate( $start_date, $end_date ) {
		$rsvps = $this->get_total_rsvps( $start_date, $end_date );
		$attended = $this->get_total_event_attendance( $start_date, $end_date );

		if ( $rsvps == 0 ) {
			return 0;
		}

		return ( $attended / $rsvps ) * 100;
	}

	/**
	 * Calculate No-Show Rate
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return float No-show rate as percentage.
	 */
	private function calculate_no_show_rate( $start_date, $end_date ) {
		return 100 - $this->calculate_rsvp_conversion_rate( $start_date, $end_date );
	}

	/**
	 * Get Events by Type
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Events grouped by type.
	 */
	private function get_events_by_type( $start_date, $end_date ) {
		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT t.name as event_type, COUNT(*) as count
				FROM {$this->wpdb->posts} p
				LEFT JOIN {$this->wpdb->term_relationships} tr ON p.ID = tr.object_id
				LEFT JOIN {$this->wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				LEFT JOIN {$this->wpdb->terms} t ON tt.term_id = t.term_id
				WHERE p.post_type = 'campaign_event'
				AND p.post_status = 'publish'
				AND p.post_date BETWEEN %s AND %s
				AND tt.taxonomy = 'event_type'
				GROUP BY t.name
				ORDER BY count DESC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Events by Location
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Events grouped by location.
	 */
	private function get_events_by_location( $start_date, $end_date ) {
		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT pm.meta_value as location, COUNT(*) as count
				FROM {$this->wpdb->posts} p
				LEFT JOIN {$this->wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = 'campaign_event'
				AND p.post_status = 'publish'
				AND p.post_date BETWEEN %s AND %s
				AND pm.meta_key = '_campaign_event_location'
				GROUP BY pm.meta_value
				ORDER BY count DESC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Event Timeline
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Daily event data.
	 */
	private function get_event_timeline( $start_date, $end_date ) {
		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT DATE(post_date) as date, COUNT(*) as count
				FROM {$this->wpdb->posts}
				WHERE post_type = 'campaign_event'
				AND post_status = 'publish'
				AND post_date BETWEEN %s AND %s
				GROUP BY DATE(post_date)
				ORDER BY date ASC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Popular Events
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param int    $limit Number of events to return.
	 * @return array Most attended events.
	 */
	private function get_popular_events( $start_date, $end_date, $limit = 10 ) {
		$attendance_table = $this->wpdb->prefix . 'campaignpress_event_attendance';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT p.ID, p.post_title, COUNT(a.id) as attendance_count
				FROM {$this->wpdb->posts} p
				LEFT JOIN {$attendance_table} a ON p.ID = a.event_id
				WHERE p.post_type = 'campaign_event'
				AND p.post_status = 'publish'
				AND p.post_date BETWEEN %s AND %s
				AND a.status = 'attended'
				GROUP BY p.ID
				ORDER BY attendance_count DESC
				LIMIT %d",
				$start_date,
				$end_date,
				$limit
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Upcoming Events
	 *
	 * @since 1.0.0
	 * @param int $limit Number of events to return.
	 * @return array Upcoming events.
	 */
	private function get_upcoming_events( $limit = 5 ) {
		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT p.ID, p.post_title, pm.meta_value as event_date
				FROM {$this->wpdb->posts} p
				LEFT JOIN {$this->wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = 'campaign_event'
				AND p.post_status = 'publish'
				AND pm.meta_key = '_campaign_event_date'
				AND pm.meta_value >= %s
				ORDER BY pm.meta_value ASC
				LIMIT %d",
				date( 'Y-m-d H:i:s' ),
				$limit
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Event Trend
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Trend data with percentage change.
	 */
	private function get_event_trend( $start_date, $end_date ) {
		$current_total = $this->get_total_events( $start_date, $end_date );

		$days_diff = ( strtotime( $end_date ) - strtotime( $start_date ) ) / 86400;
		$previous_start = date( 'Y-m-d', strtotime( $start_date ) - ( $days_diff * 86400 ) );
		$previous_end = date( 'Y-m-d', strtotime( $start_date ) - 86400 );

		$previous_total = $this->get_total_events( $previous_start, $previous_end );

		$change = $current_total - $previous_total;
		$percentage_change = $previous_total > 0 ? ( $change / $previous_total ) * 100 : 0;

		return array(
			'current'           => $current_total,
			'previous'          => $previous_total,
			'change'            => $change,
			'percentage_change' => $percentage_change,
			'trend'             => $change >= 0 ? 'up' : 'down',
		);
	}

	// ========================================================================
	// PRIVATE HELPER METHODS - Contacts & Engagement
	// ========================================================================

	/**
	 * Get Total Contacts
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return int Total number of contacts.
	 */
	private function get_total_contacts( $start_date, $end_date ) {
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$contacts_table}
				WHERE created_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return intval( $result );
	}

	/**
	 * Get Average Engagement Score
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return float Average engagement score.
	 */
	private function get_avg_engagement_score( $start_date, $end_date ) {
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COALESCE(AVG(engagement_score), 0)
				FROM {$contacts_table}
				WHERE created_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return floatval( $result );
	}

	/**
	 * Get Highly Engaged Contact Count
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return int Number of highly engaged contacts.
	 */
	private function get_highly_engaged_count( $start_date, $end_date ) {
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$contacts_table}
				WHERE created_at BETWEEN %s AND %s
				AND engagement_score >= 75",
				$start_date,
				$end_date
			)
		);

		return intval( $result );
	}

	/**
	 * Get At-Risk Contacts Count
	 *
	 * Contacts with declining engagement.
	 *
	 * @since 1.0.0
	 * @return int Number of at-risk contacts.
	 */
	private function get_at_risk_contacts_count() {
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';

		$result = $this->wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$contacts_table}
			WHERE engagement_score < 25
			AND last_interaction_date < DATE_SUB(NOW(), INTERVAL 60 DAY)"
		);

		return intval( $result );
	}

	/**
	 * Get Engagement Score Distribution
	 *
	 * @since 1.0.0
	 * @return array Engagement scores grouped by ranges.
	 */
	private function get_engagement_score_distribution() {
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';

		$ranges = array(
			'0-25'   => array( 0, 25 ),
			'26-50'  => array( 26, 50 ),
			'51-75'  => array( 51, 75 ),
			'76-100' => array( 76, 100 ),
		);

		$results = array();
		foreach ( $ranges as $label => $range ) {
			$count = $this->wpdb->get_var(
				$this->wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$contacts_table}
					WHERE engagement_score >= %d AND engagement_score <= %d",
					$range[0],
					$range[1]
				)
			);

			$results[] = array(
				'range' => $label,
				'count' => intval( $count ),
			);
		}

		return $results;
	}

	/**
	 * Get Engagement Trend
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Daily engagement data.
	 */
	private function get_engagement_trend( $start_date, $end_date ) {
		$interactions_table = $this->wpdb->prefix . 'campaignpress_interactions';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT DATE(interaction_date) as date, COUNT(*) as count
				FROM {$interactions_table}
				WHERE interaction_date BETWEEN %s AND %s
				GROUP BY DATE(interaction_date)
				ORDER BY date ASC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Engagement by Activity Type
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Engagement grouped by activity type.
	 */
	private function get_engagement_by_activity_type( $start_date, $end_date ) {
		$interactions_table = $this->wpdb->prefix . 'campaignpress_interactions';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT interaction_type, COUNT(*) as count
				FROM {$interactions_table}
				WHERE interaction_date BETWEEN %s AND %s
				GROUP BY interaction_type
				ORDER BY count DESC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Top Engaged Contacts
	 *
	 * @since 1.0.0
	 * @param int $limit Number of contacts to return.
	 * @return array Most engaged contacts.
	 */
	private function get_top_engaged_contacts( $limit = 20 ) {
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT first_name, last_name, email, engagement_score
				FROM {$contacts_table}
				ORDER BY engagement_score DESC
				LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Email Engagement Stats
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Email engagement metrics.
	 */
	private function get_email_engagement_stats( $start_date, $end_date ) {
		$interactions_table = $this->wpdb->prefix . 'campaignpress_interactions';

		$opens = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$interactions_table}
				WHERE interaction_type = 'email_open'
				AND interaction_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		$clicks = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$interactions_table}
				WHERE interaction_type = 'email_click'
				AND interaction_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return array(
			'opens'      => intval( $opens ),
			'clicks'     => intval( $clicks ),
			'click_rate' => $opens > 0 ? ( $clicks / $opens ) * 100 : 0,
		);
	}

	/**
	 * Get Event Engagement Stats
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Event engagement metrics.
	 */
	private function get_event_engagement_stats( $start_date, $end_date ) {
		$attendance_table = $this->wpdb->prefix . 'campaignpress_event_attendance';

		$attendance = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$attendance_table}
				WHERE status = 'attended'
				AND event_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return array(
			'events_attended' => intval( $attendance ),
		);
	}

	/**
	 * Get Volunteer Engagement Stats
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Volunteer engagement metrics.
	 */
	private function get_volunteer_engagement_stats( $start_date, $end_date ) {
		$activity_table = $this->wpdb->prefix . 'campaignpress_volunteer_activity';

		$hours = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COALESCE(SUM(hours), 0)
				FROM {$activity_table}
				WHERE activity_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return array(
			'volunteer_hours' => floatval( $hours ),
		);
	}

	/**
	 * Get Donation Engagement Stats
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Donation engagement metrics.
	 */
	private function get_donation_engagement_stats( $start_date, $end_date ) {
		$donations_table = $this->wpdb->prefix . 'campaignpress_donations';

		$total = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COALESCE(SUM(amount), 0)
				FROM {$donations_table}
				WHERE status = 'completed'
				AND donation_date BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return array(
			'total_donated' => floatval( $total ),
		);
	}

	// ========================================================================
	// PRIVATE HELPER METHODS - Geographic
	// ========================================================================

	/**
	 * Get Contacts by Location
	 *
	 * @since 1.0.0
	 * @return array Contacts grouped by location.
	 */
	private function get_contacts_by_location() {
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';

		$results = $this->wpdb->get_results(
			"SELECT city, state, COUNT(*) as count,
			AVG(latitude) as lat, AVG(longitude) as lng
			FROM {$contacts_table}
			WHERE city IS NOT NULL AND state IS NOT NULL
			GROUP BY city, state
			ORDER BY count DESC",
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Donations by Location
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Donations grouped by location.
	 */
	private function get_donations_by_location( $start_date, $end_date ) {
		$donations_table = $this->wpdb->prefix . 'campaignpress_donations';
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT c.city, c.state, COUNT(d.id) as count, SUM(d.amount) as total,
				AVG(c.latitude) as lat, AVG(c.longitude) as lng
				FROM {$donations_table} d
				LEFT JOIN {$contacts_table} c ON d.contact_id = c.id
				WHERE d.status = 'completed'
				AND d.donation_date BETWEEN %s AND %s
				AND c.city IS NOT NULL AND c.state IS NOT NULL
				GROUP BY c.city, c.state
				ORDER BY total DESC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Volunteers by Location
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Volunteers grouped by location.
	 */
	private function get_volunteers_by_location( $start_date, $end_date ) {
		$volunteers_table = $this->wpdb->prefix . 'campaignpress_volunteers';
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT c.city, c.state, COUNT(v.id) as count,
				AVG(c.latitude) as lat, AVG(c.longitude) as lng
				FROM {$volunteers_table} v
				LEFT JOIN {$contacts_table} c ON v.contact_id = c.id
				WHERE v.signup_date BETWEEN %s AND %s
				AND c.city IS NOT NULL AND c.state IS NOT NULL
				GROUP BY c.city, c.state
				ORDER BY count DESC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get Activity Heatmap Data
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array Heatmap coordinates with intensity values.
	 */
	private function get_activity_heatmap_data( $start_date, $end_date ) {
		$contacts_table = $this->wpdb->prefix . 'campaignpress_contacts';
		$interactions_table = $this->wpdb->prefix . 'campaignpress_interactions';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT c.latitude as lat, c.longitude as lng, COUNT(i.id) as intensity
				FROM {$interactions_table} i
				LEFT JOIN {$contacts_table} c ON i.contact_id = c.id
				WHERE i.interaction_date BETWEEN %s AND %s
				AND c.latitude IS NOT NULL AND c.longitude IS NOT NULL
				GROUP BY c.latitude, c.longitude",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return $results;
	}
}
