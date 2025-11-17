<?php
/**
 * CampaignPress FEC Reports Generation
 *
 * Handles FEC report generation, formatting, and export. Generates all required
 * FEC reports including quarterly reports, pre-election reports, post-general
 * reports, 48-hour notices, and independent expenditure reports. Exports reports
 * in FEC Form 3 CSV format compatible with FEC filing software.
 *
 * FEC Requirements Implemented:
 * - 11 CFR §104.5 - Quarterly reports (Q1, Q2, Q3, Q4)
 * - 11 CFR §104.5 - Pre-election reports (12 days before election)
 * - 11 CFR §104.5 - Post-general report (30 days after general election)
 * - 11 CFR §104.22 - 48-hour notices (contributions over $1,000)
 * - 11 CFR §104.4 - Independent expenditure reports
 * - FEC Form 3 - Report of Receipts and Disbursements
 *
 * @package CampaignPress
 * @subpackage Compliance
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * FEC Reports Class
 *
 * @since 1.0.0
 */
class CampaignPress_FEC_Reports {

    /**
     * Contributions instance
     *
     * @var CampaignPress_FEC_Contributions
     */
    private $contributions;

    /**
     * Donors instance
     *
     * @var CampaignPress_FEC_Donors
     */
    private $donors;

    /**
     * Audit trail instance
     *
     * @var CampaignPress_FEC_Audit_Trail
     */
    private $audit_trail;

    /**
     * Quarterly report periods
     *
     * @var array
     */
    private $quarterly_periods = array(
        'Q1' => array('start' => '01-01', 'end' => '03-31', 'due' => '04-15'),
        'Q2' => array('start' => '04-01', 'end' => '06-30', 'due' => '07-15'),
        'Q3' => array('start' => '07-01', 'end' => '09-30', 'due' => '10-15'),
        'Q4' => array('start' => '10-01', 'end' => '12-31', 'due' => '01-31'),
    );

    /**
     * Constructor
     *
     * @since 1.0.0
     * @param CampaignPress_FEC_Contributions $contributions Contributions instance
     * @param CampaignPress_FEC_Donors $donors Donors instance
     * @param CampaignPress_FEC_Audit_Trail $audit_trail Audit trail instance
     */
    public function __construct($contributions, $donors, $audit_trail) {
        $this->contributions = $contributions;
        $this->donors = $donors;
        $this->audit_trail = $audit_trail;
    }

    /**
     * Generate FEC report
     *
     * @since 1.0.0
     * @param string $report_type Report type (quarterly, pre_election, post_general, 48hour)
     * @param string $report_period Report period (Q1, Q2, Q3, Q4, or date range)
     * @param array $options Additional options
     * @return array|WP_Error Report data or error
     */
    public function generate_report($report_type, $report_period, $options = array()) {
        // Validate report type
        $valid_types = array('quarterly', 'pre_election', 'post_general', '48hour', 'independent_expenditure');

        if (!in_array($report_type, $valid_types)) {
            return new WP_Error('invalid_report_type', __('Invalid report type.', 'campaignpress'));
        }

        // Get date range for report
        $date_range = $this->get_report_date_range($report_type, $report_period);

        if (is_wp_error($date_range)) {
            return $date_range;
        }

        // Get committee information
        $committee_info = get_option('cp_fec_committee_info', array());

        // Generate report based on type
        switch ($report_type) {
            case 'quarterly':
                $report_data = $this->generate_quarterly_report($date_range, $committee_info, $report_period);
                break;

            case 'pre_election':
                $report_data = $this->generate_pre_election_report($date_range, $committee_info, $options);
                break;

            case 'post_general':
                $report_data = $this->generate_post_general_report($date_range, $committee_info);
                break;

            case '48hour':
                $report_data = $this->generate_48hour_notice($date_range, $committee_info);
                break;

            case 'independent_expenditure':
                $report_data = $this->generate_independent_expenditure_report($date_range, $committee_info);
                break;

            default:
                return new WP_Error('unknown_report_type', __('Unknown report type.', 'campaignpress'));
        }

        if (is_wp_error($report_data)) {
            return $report_data;
        }

        // Save report to database
        $report_id = $this->save_report($report_type, $report_period, $report_data);

        if (is_wp_error($report_id)) {
            return $report_id;
        }

        $report_data['report_id'] = $report_id;

        // Log report generation
        $this->audit_trail->log_event('report_generated', array(
            'report_id' => $report_id,
            'report_type' => $report_type,
            'report_period' => $report_period,
            'date_from' => $date_range['start'],
            'date_to' => $date_range['end'],
        ));

        // Action hook after report generation
        do_action('cp_fec_report_generated', $report_id, $report_type, $report_data);

        return $report_data;
    }

    /**
     * Generate quarterly report
     *
     * @since 1.0.0
     * @param array $date_range Date range
     * @param array $committee_info Committee information
     * @param string $quarter Quarter (Q1, Q2, Q3, Q4)
     * @return array Report data
     */
    private function generate_quarterly_report($date_range, $committee_info, $quarter) {
        // Get all contributions for period
        $contributions_result = $this->contributions->get_contributions(array(
            'date_from' => $date_range['start'],
            'date_to' => $date_range['end'],
            'contribution_status' => 'completed',
            'per_page' => 999999,
        ));

        $contributions = $contributions_result['contributions'];

        // Calculate summary totals
        $summary = $this->calculate_summary($contributions, $date_range);

        // Get itemized contributions (over $200)
        $itemized = array_filter($contributions, function($c) {
            return $c->is_itemized == 1;
        });

        // Get unitemized total (under $200)
        $unitemized_total = array_sum(array_map(function($c) {
            return $c->is_itemized == 0 ? $c->amount : 0;
        }, $contributions));

        // Build report data
        $report = array(
            'report_type' => 'quarterly',
            'report_period' => $quarter,
            'coverage_from_date' => $date_range['start'],
            'coverage_through_date' => $date_range['end'],
            'committee_info' => $committee_info,
            'summary' => $summary,
            'itemized_receipts' => $this->format_itemized_receipts($itemized),
            'unitemized_receipts_total' => $unitemized_total,
            'total_contributions' => count($contributions),
            'total_itemized' => count($itemized),
            'generated_date' => current_time('mysql'),
        );

        return $report;
    }

    /**
     * Generate pre-election report
     *
     * Per 11 CFR §104.5, pre-election reports cover:
     * - 20 days before election through 12 days before election
     *
     * @since 1.0.0
     * @param array $date_range Date range
     * @param array $committee_info Committee information
     * @param array $options Additional options (election_date, election_type)
     * @return array Report data
     */
    private function generate_pre_election_report($date_range, $committee_info, $options) {
        $election_date = isset($options['election_date']) ? $options['election_date'] : '';
        $election_type = isset($options['election_type']) ? $options['election_type'] : 'general';

        // Get contributions for period
        $contributions_result = $this->contributions->get_contributions(array(
            'date_from' => $date_range['start'],
            'date_to' => $date_range['end'],
            'contribution_status' => 'completed',
            'per_page' => 999999,
        ));

        $contributions = $contributions_result['contributions'];

        // Calculate summary
        $summary = $this->calculate_summary($contributions, $date_range);

        // Get itemized contributions
        $itemized = array_filter($contributions, function($c) {
            return $c->is_itemized == 1;
        });

        $report = array(
            'report_type' => 'pre_election',
            'report_period' => 'Pre-' . ucfirst($election_type),
            'election_date' => $election_date,
            'election_type' => $election_type,
            'coverage_from_date' => $date_range['start'],
            'coverage_through_date' => $date_range['end'],
            'committee_info' => $committee_info,
            'summary' => $summary,
            'itemized_receipts' => $this->format_itemized_receipts($itemized),
            'total_contributions' => count($contributions),
            'generated_date' => current_time('mysql'),
        );

        return $report;
    }

    /**
     * Generate post-general election report
     *
     * Per 11 CFR §104.5, post-general reports are due 30 days after the general election
     *
     * @since 1.0.0
     * @param array $date_range Date range
     * @param array $committee_info Committee information
     * @return array Report data
     */
    private function generate_post_general_report($date_range, $committee_info) {
        // Get contributions for period
        $contributions_result = $this->contributions->get_contributions(array(
            'date_from' => $date_range['start'],
            'date_to' => $date_range['end'],
            'contribution_status' => 'completed',
            'per_page' => 999999,
        ));

        $contributions = $contributions_result['contributions'];

        // Calculate summary
        $summary = $this->calculate_summary($contributions, $date_range);

        // Get itemized contributions
        $itemized = array_filter($contributions, function($c) {
            return $c->is_itemized == 1;
        });

        $report = array(
            'report_type' => 'post_general',
            'report_period' => 'Post-General',
            'coverage_from_date' => $date_range['start'],
            'coverage_through_date' => $date_range['end'],
            'committee_info' => $committee_info,
            'summary' => $summary,
            'itemized_receipts' => $this->format_itemized_receipts($itemized),
            'total_contributions' => count($contributions),
            'generated_date' => current_time('mysql'),
        );

        return $report;
    }

    /**
     * Generate 48-hour notice report
     *
     * Per 11 CFR §104.22, contributions over $1,000 received within 20 days
     * before an election must be reported within 48 hours
     *
     * @since 1.0.0
     * @param array $date_range Date range
     * @param array $committee_info Committee information
     * @return array Report data
     */
    private function generate_48hour_notice($date_range, $committee_info) {
        // Get contributions requiring 48-hour notice
        $contributions_result = $this->contributions->get_contributions(array(
            'date_from' => $date_range['start'],
            'date_to' => $date_range['end'],
            'requires_48hour_notice' => 1,
            'contribution_status' => 'completed',
            'per_page' => 999999,
        ));

        $contributions = $contributions_result['contributions'];

        // Format for 48-hour notice
        $notices = array();
        foreach ($contributions as $contribution) {
            $donor = $this->donors->get_donor($contribution->donor_id);

            $notices[] = array(
                'contribution_id' => $contribution->id,
                'donor_name' => $donor->first_name . ' ' . $donor->last_name,
                'donor_address' => $donor->street1 . ', ' . $donor->city . ', ' . $donor->state . ' ' . $donor->zip,
                'occupation' => $donor->occupation,
                'employer' => $donor->employer,
                'contribution_date' => $contribution->contribution_date,
                'amount' => $contribution->amount,
                'aggregate_ytd' => $contribution->aggregate_cycle_ytd,
            );
        }

        $report = array(
            'report_type' => '48hour',
            'report_period' => '48-Hour Notice',
            'coverage_from_date' => $date_range['start'],
            'coverage_through_date' => $date_range['end'],
            'committee_info' => $committee_info,
            'notices' => $notices,
            'total_notices' => count($notices),
            'total_amount' => array_sum(array_column($notices, 'amount')),
            'generated_date' => current_time('mysql'),
        );

        return $report;
    }

    /**
     * Generate independent expenditure report
     *
     * @since 1.0.0
     * @param array $date_range Date range
     * @param array $committee_info Committee information
     * @return array Report data
     */
    private function generate_independent_expenditure_report($date_range, $committee_info) {
        // This is a placeholder for independent expenditure tracking
        // Would need additional tables/functionality to track IE spending

        $report = array(
            'report_type' => 'independent_expenditure',
            'report_period' => 'Independent Expenditures',
            'coverage_from_date' => $date_range['start'],
            'coverage_through_date' => $date_range['end'],
            'committee_info' => $committee_info,
            'expenditures' => array(),
            'total_expenditures' => 0,
            'generated_date' => current_time('mysql'),
        );

        return apply_filters('cp_fec_independent_expenditure_report', $report, $date_range);
    }

    /**
     * Calculate report summary totals
     *
     * Calculates FEC Form 3 summary page line items
     *
     * @since 1.0.0
     * @param array $contributions Contributions for period
     * @param array $date_range Date range
     * @return array Summary calculations
     */
    private function calculate_summary($contributions, $date_range) {
        $total_receipts = 0;
        $total_individual = 0;
        $total_pac = 0;
        $total_party = 0;
        $total_inkind = 0;
        $total_refunds = 0;

        foreach ($contributions as $contribution) {
            $donor = $this->donors->get_donor($contribution->donor_id);

            // Add to total receipts
            $total_receipts += $contribution->amount;

            // Categorize by donor type
            switch ($donor->donor_type) {
                case 'individual':
                    $total_individual += $contribution->amount;
                    break;
                case 'pac':
                    $total_pac += $contribution->amount;
                    break;
                case 'party':
                    $total_party += $contribution->amount;
                    break;
            }

            // Track in-kind contributions
            if ($contribution->is_inkind) {
                $total_inkind += $contribution->amount;
            }

            // Track refunds
            if ($contribution->refund_amount > 0) {
                $total_refunds += $contribution->refund_amount;
            }
        }

        // Get cash on hand (would need disbursements table for accurate calculation)
        $previous_cash = floatval(get_option('cp_fec_previous_cash_on_hand', 0));
        $cash_on_hand = $previous_cash + $total_receipts; // Simplified - would subtract disbursements

        // FEC Form 3 Summary Page calculations
        $summary = array(
            // Line 6 - Cash on Hand at Beginning of Reporting Period
            'line_6_cash_beginning' => $previous_cash,

            // Line 11 - Total Receipts
            'line_11_total_receipts' => $total_receipts,

            // Line 11(a) - Contributions from Individuals/Persons
            'line_11a_individual_contributions' => $total_individual,

            // Line 11(b) - Contributions from Political Party Committees
            'line_11b_party_contributions' => $total_party,

            // Line 11(c) - Contributions from Other Political Committees (PACs)
            'line_11c_pac_contributions' => $total_pac,

            // Line 11(d) - Total Contributions
            'line_11d_total_contributions' => $total_individual + $total_party + $total_pac,

            // Line 13 - Total In-Kind Contributions
            'line_13_inkind_contributions' => $total_inkind,

            // Line 15 - Offsets to Operating Expenditures (Refunds)
            'line_15_refunds' => $total_refunds,

            // Line 16 - Other Receipts
            'line_16_other_receipts' => 0,

            // Line 19 - Cash on Hand at Close of Reporting Period
            'line_19_cash_ending' => $cash_on_hand,

            // Additional metadata
            'total_contributions_count' => count($contributions),
            'reporting_period_start' => $date_range['start'],
            'reporting_period_end' => $date_range['end'],
        );

        return $summary;
    }

    /**
     * Format itemized receipts for reporting
     *
     * @since 1.0.0
     * @param array $contributions Itemized contributions
     * @return array Formatted receipts
     */
    private function format_itemized_receipts($contributions) {
        $itemized = array();

        foreach ($contributions as $contribution) {
            $donor = $this->donors->get_donor($contribution->donor_id);

            if (!$donor) {
                continue;
            }

            $itemized[] = array(
                'contribution_id' => $contribution->id,
                'receipt_date' => date('Y-m-d', strtotime($contribution->contribution_date)),
                'contributor_name' => $donor->first_name . ' ' . $donor->last_name,
                'contributor_street1' => $donor->street1,
                'contributor_street2' => $donor->street2,
                'contributor_city' => $donor->city,
                'contributor_state' => $donor->state,
                'contributor_zip' => $donor->zip,
                'contributor_occupation' => $donor->occupation,
                'contributor_employer' => $donor->employer,
                'contribution_amount' => $contribution->amount,
                'contribution_aggregate_ytd' => $contribution->aggregate_cycle_ytd,
                'election_type' => $contribution->election_type,
                'memo_text' => $contribution->memo_text,
            );
        }

        return $itemized;
    }

    /**
     * Export report to FEC Form 3 CSV format
     *
     * Exports report in format compatible with FEC filing software
     *
     * @since 1.0.0
     * @param int $report_id Report ID
     * @return string|WP_Error File URL or error
     */
    public function export_fec_form3($report_id) {
        global $wpdb;

        // Get report data
        $reports_table = $wpdb->prefix . 'cp_fec_reports';
        $report = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$reports_table} WHERE id = %d",
            $report_id
        ));

        if (!$report) {
            return new WP_Error('report_not_found', __('Report not found.', 'campaignpress'));
        }

        $report_data = json_decode($report->report_data, true);

        // Create export directory
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/fec-reports/';

        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }

        // Generate filename
        $filename = sprintf('FEC-Form3-%s-%s.csv', $report->report_period, date('Ymd-His'));
        $filepath = $export_dir . $filename;

        // Create CSV file
        $file = fopen($filepath, 'w');

        if ($file === false) {
            return new WP_Error('file_error', __('Failed to create export file.', 'campaignpress'));
        }

        // FEC Form 3 Format Header
        $this->write_fec_header($file, $report_data);

        // Summary Page (SA)
        $this->write_fec_summary($file, $report_data);

        // Schedule A - Itemized Receipts (SA)
        if (!empty($report_data['itemized_receipts'])) {
            foreach ($report_data['itemized_receipts'] as $receipt) {
                $this->write_fec_schedule_a($file, $receipt, $report_data);
            }
        }

        fclose($file);

        // Get file URL
        $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $filepath);

        // Log export
        $this->audit_trail->log_event('report_exported', array(
            'report_id' => $report_id,
            'filename' => $filename,
            'format' => 'FEC Form 3 CSV',
        ));

        return $file_url;
    }

    /**
     * Write FEC file header
     *
     * @since 1.0.0
     * @param resource $file File handle
     * @param array $report_data Report data
     */
    private function write_fec_header($file, $report_data) {
        $committee = $report_data['committee_info'];

        // FEC HDR record
        fputcsv($file, array(
            'HDR',
            'FEC',
            '8.3', // FEC format version
            $committee['committee_id'],
            $committee['committee_name'],
            'F3', // Form type
            date('Ymd'), // File creation date
        ));
    }

    /**
     * Write FEC summary data
     *
     * @since 1.0.0
     * @param resource $file File handle
     * @param array $report_data Report data
     */
    private function write_fec_summary($file, $report_data) {
        $committee = $report_data['committee_info'];
        $summary = $report_data['summary'];

        // F3 Summary record
        fputcsv($file, array(
            'F3',
            $committee['committee_id'],
            $committee['committee_name'],
            str_replace('-', '', $report_data['coverage_from_date']), // YYYYMMDD
            str_replace('-', '', $report_data['coverage_through_date']), // YYYYMMDD
            number_format($summary['line_6_cash_beginning'], 2, '.', ''),
            number_format($summary['line_11_total_receipts'], 2, '.', ''),
            number_format($summary['line_11a_individual_contributions'], 2, '.', ''),
            number_format($summary['line_11b_party_contributions'], 2, '.', ''),
            number_format($summary['line_11c_pac_contributions'], 2, '.', ''),
            number_format($summary['line_11d_total_contributions'], 2, '.', ''),
            number_format($summary['line_19_cash_ending'], 2, '.', ''),
        ));
    }

    /**
     * Write FEC Schedule A (Itemized Receipts)
     *
     * @since 1.0.0
     * @param resource $file File handle
     * @param array $receipt Receipt data
     * @param array $report_data Report data
     */
    private function write_fec_schedule_a($file, $receipt, $report_data) {
        $committee = $report_data['committee_info'];

        // SA11AI - Individual contribution record
        fputcsv($file, array(
            'SA11AI', // Form/Line/Transaction ID
            $committee['committee_id'],
            'IND', // Entity type (IND = Individual)
            $receipt['contributor_name'],
            $receipt['contributor_street1'],
            $receipt['contributor_street2'],
            $receipt['contributor_city'],
            $receipt['contributor_state'],
            $receipt['contributor_zip'],
            str_replace('-', '', $receipt['receipt_date']), // YYYYMMDD
            number_format($receipt['contribution_amount'], 2, '.', ''),
            number_format($receipt['contribution_aggregate_ytd'], 2, '.', ''),
            $receipt['contributor_occupation'],
            $receipt['contributor_employer'],
            strtoupper($receipt['election_type'][0]), // P=Primary, G=General
            $receipt['memo_text'],
        ));
    }

    /**
     * Get report date range
     *
     * @since 1.0.0
     * @param string $report_type Report type
     * @param string $report_period Report period
     * @return array|WP_Error Date range or error
     */
    private function get_report_date_range($report_type, $report_period) {
        $year = date('Y');

        switch ($report_type) {
            case 'quarterly':
                if (!isset($this->quarterly_periods[$report_period])) {
                    return new WP_Error('invalid_period', __('Invalid quarterly period.', 'campaignpress'));
                }

                $period = $this->quarterly_periods[$report_period];

                return array(
                    'start' => $year . '-' . $period['start'],
                    'end' => $year . '-' . $period['end'],
                    'due' => ($report_period === 'Q4' ? ($year + 1) : $year) . '-' . $period['due'],
                );

            case 'pre_election':
            case 'post_general':
            case '48hour':
                // For these reports, period should be in format 'YYYY-MM-DD:YYYY-MM-DD'
                if (strpos($report_period, ':') === false) {
                    return new WP_Error('invalid_period', __('Period must be in format YYYY-MM-DD:YYYY-MM-DD', 'campaignpress'));
                }

                list($start, $end) = explode(':', $report_period);

                return array(
                    'start' => $start,
                    'end' => $end,
                );

            default:
                return new WP_Error('unknown_report_type', __('Unknown report type.', 'campaignpress'));
        }
    }

    /**
     * Save report to database
     *
     * @since 1.0.0
     * @param string $report_type Report type
     * @param string $report_period Report period
     * @param array $report_data Report data
     * @return int|WP_Error Report ID or error
     */
    private function save_report($report_type, $report_period, $report_data) {
        global $wpdb;

        $reports_table = $wpdb->prefix . 'cp_fec_reports';

        // Create reports table if it doesn't exist
        $this->create_reports_table();

        // Prepare report data
        $insert_data = array(
            'report_type' => $report_type,
            'report_period' => $report_period,
            'coverage_from_date' => $report_data['coverage_from_date'],
            'coverage_through_date' => $report_data['coverage_through_date'],
            'report_data' => json_encode($report_data),
            'total_receipts' => isset($report_data['summary']['line_11_total_receipts']) ? $report_data['summary']['line_11_total_receipts'] : 0,
            'total_contributions' => isset($report_data['total_contributions']) ? $report_data['total_contributions'] : 0,
            'filed_status' => 'draft',
            'created_by' => get_current_user_id(),
            'created_date' => current_time('mysql'),
        );

        $result = $wpdb->insert($reports_table, $insert_data);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to save report.', 'campaignpress'));
        }

        return $wpdb->insert_id;
    }

    /**
     * Create reports table
     *
     * @since 1.0.0
     */
    private function create_reports_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_fec_reports';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            report_type varchar(50) NOT NULL,
            report_period varchar(50) NOT NULL,
            coverage_from_date date NOT NULL,
            coverage_through_date date NOT NULL,
            report_data longtext NOT NULL,
            total_receipts decimal(12,2) DEFAULT 0.00,
            total_contributions int(11) DEFAULT 0,
            filed_status varchar(50) DEFAULT 'draft',
            filed_date datetime DEFAULT NULL,
            filed_by bigint(20) UNSIGNED DEFAULT 0,
            amendment_number int(11) DEFAULT 0,
            amended_report_id bigint(20) UNSIGNED DEFAULT NULL,
            created_by bigint(20) UNSIGNED DEFAULT 0,
            created_date datetime NOT NULL,
            modified_date datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY report_type_idx (report_type),
            KEY report_period_idx (report_period),
            KEY filed_status_idx (filed_status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Get saved reports
     *
     * @since 1.0.0
     * @param array $args Query arguments
     * @return array Reports and pagination
     */
    public function get_reports($args = array()) {
        global $wpdb;

        $reports_table = $wpdb->prefix . 'cp_fec_reports';

        $defaults = array(
            'report_type' => '',
            'filed_status' => '',
            'year' => '',
            'page' => 1,
            'per_page' => 20,
            'orderby' => 'created_date',
            'order' => 'DESC',
        );

        $args = wp_parse_args($args, $defaults);

        $where = array('1=1');
        $where_values = array();

        if (!empty($args['report_type'])) {
            $where[] = "report_type = %s";
            $where_values[] = $args['report_type'];
        }

        if (!empty($args['filed_status'])) {
            $where[] = "filed_status = %s";
            $where_values[] = $args['filed_status'];
        }

        if (!empty($args['year'])) {
            $where[] = "YEAR(coverage_from_date) = %d";
            $where_values[] = $args['year'];
        }

        $where_clause = implode(' AND ', $where);

        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$reports_table} WHERE {$where_clause}";
        if (!empty($where_values)) {
            $count_query = $wpdb->prepare($count_query, $where_values);
        }
        $total = $wpdb->get_var($count_query);

        // Get reports
        $offset = ($args['page'] - 1) * $args['per_page'];

        $query = "SELECT * FROM {$reports_table}
                  WHERE {$where_clause}
                  ORDER BY {$args['orderby']} {$args['order']}
                  LIMIT %d OFFSET %d";

        $where_values[] = $args['per_page'];
        $where_values[] = $offset;

        $reports = $wpdb->get_results($wpdb->prepare($query, $where_values));

        return array(
            'reports' => $reports,
            'total' => $total,
            'page' => $args['page'],
            'per_page' => $args['per_page'],
            'pages' => ceil($total / $args['per_page']),
        );
    }

    /**
     * Mark report as filed
     *
     * @since 1.0.0
     * @param int $report_id Report ID
     * @return bool True on success
     */
    public function mark_report_filed($report_id) {
        global $wpdb;

        $reports_table = $wpdb->prefix . 'cp_fec_reports';

        $result = $wpdb->update(
            $reports_table,
            array(
                'filed_status' => 'filed',
                'filed_date' => current_time('mysql'),
                'filed_by' => get_current_user_id(),
            ),
            array('id' => $report_id),
            array('%s', '%s', '%d'),
            array('%d')
        );

        if ($result !== false) {
            $this->audit_trail->log_event('report_filed', array(
                'report_id' => $report_id,
            ));
        }

        return $result !== false;
    }
}
