<?php
/**
 * CampaignPress FEC Contributions Management
 *
 * Handles contribution recording, tracking, and limit enforcement for FEC compliance.
 * Implements automatic validation against FEC contribution limits, prohibited source
 * detection, aggregation by donor, and special reporting requirements.
 *
 * FEC Requirements Implemented:
 * - 52 U.S.C. §30116 - Contribution limits enforcement
 * - 11 CFR §104.3 - Contribution itemization and reporting
 * - 11 CFR §104.22 - 48-hour notice requirements for contributions over $1,000
 * - 11 CFR §110 - Contribution limitations and prohibitions
 * - 11 CFR §111 - In-kind contribution valuation
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
 * FEC Contributions Class
 *
 * @since 1.0.0
 */
class CampaignPress_FEC_Contributions {

    /**
     * Database table name
     *
     * @var string
     */
    private $table_name;

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
     * Constructor
     *
     * @since 1.0.0
     * @param CampaignPress_FEC_Donors $donors Donors instance
     * @param CampaignPress_FEC_Audit_Trail $audit_trail Audit trail instance
     */
    public function __construct($donors, $audit_trail) {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'cp_fec_contributions';
        $this->donors = $donors;
        $this->audit_trail = $audit_trail;
    }

    /**
     * Create contributions database table
     *
     * @since 1.0.0
     */
    public function create_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            donor_id bigint(20) UNSIGNED NOT NULL,
            contribution_type varchar(50) DEFAULT 'monetary',
            amount decimal(10,2) NOT NULL DEFAULT 0.00,
            contribution_date datetime NOT NULL,
            received_date datetime NOT NULL,
            election_type varchar(50) DEFAULT 'primary',
            election_year int(4) NOT NULL,
            payment_method varchar(50) DEFAULT 'credit_card',
            check_number varchar(100) DEFAULT '',
            transaction_id varchar(255) DEFAULT '',
            memo_text text DEFAULT '',
            is_inkind tinyint(1) DEFAULT 0,
            inkind_description text DEFAULT '',
            inkind_fair_market_value decimal(10,2) DEFAULT 0.00,
            contribution_status varchar(50) DEFAULT 'completed',
            refund_id bigint(20) UNSIGNED DEFAULT NULL,
            refund_date datetime DEFAULT NULL,
            refund_amount decimal(10,2) DEFAULT 0.00,
            refund_reason text DEFAULT '',
            is_itemized tinyint(1) DEFAULT 0,
            requires_48hour_notice tinyint(1) DEFAULT 0,
            notice_48hour_filed tinyint(1) DEFAULT 0,
            notice_48hour_filed_date datetime DEFAULT NULL,
            conduit_name varchar(255) DEFAULT '',
            conduit_street1 varchar(255) DEFAULT '',
            conduit_street2 varchar(255) DEFAULT '',
            conduit_city varchar(100) DEFAULT '',
            conduit_state varchar(2) DEFAULT '',
            conduit_zip varchar(10) DEFAULT '',
            aggregate_primary_ytd decimal(10,2) DEFAULT 0.00,
            aggregate_general_ytd decimal(10,2) DEFAULT 0.00,
            aggregate_cycle_ytd decimal(10,2) DEFAULT 0.00,
            reported_on_form varchar(100) DEFAULT '',
            reported_date datetime DEFAULT NULL,
            receipt_generated tinyint(1) DEFAULT 0,
            receipt_number varchar(100) DEFAULT '',
            receipt_sent_date datetime DEFAULT NULL,
            created_by bigint(20) UNSIGNED DEFAULT 0,
            created_date datetime NOT NULL,
            modified_date datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY donor_id_idx (donor_id),
            KEY contribution_date_idx (contribution_date),
            KEY election_type_idx (election_type),
            KEY contribution_status_idx (contribution_status),
            KEY requires_48hour_notice_idx (requires_48hour_notice),
            KEY reported_on_form_idx (reported_on_form),
            KEY amount_idx (amount)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Record a contribution
     *
     * Performs all FEC compliance checks before recording:
     * - Validates donor exists and is not prohibited source
     * - Checks contribution limits
     * - Calculates aggregates
     * - Determines itemization requirement
     * - Checks 48-hour notice requirement
     * - Generates receipt
     *
     * @since 1.0.0
     * @param array $data Contribution data
     * @return int|WP_Error Contribution ID or error
     */
    public function record_contribution($data) {
        global $wpdb;

        // Validate required fields
        $validation = $this->validate_contribution_data($data);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $donor_id = absint($data['donor_id']);
        $amount = floatval($data['amount']);
        $election_type = isset($data['election_type']) ? sanitize_text_field($data['election_type']) : 'primary';

        // Verify donor exists
        $donor = $this->donors->get_donor($donor_id);
        if (!$donor) {
            return new WP_Error('donor_not_found', __('Donor not found.', 'campaignpress'));
        }

        // Check if donor is prohibited source
        if ($donor->is_prohibited_source) {
            $this->audit_trail->log_event('prohibited_contribution_blocked', array(
                'donor_id' => $donor_id,
                'amount' => $amount,
                'reason' => $donor->prohibited_source_reason,
            ));

            return new WP_Error('prohibited_source', $donor->prohibited_source_reason);
        }

        // Validate contribution against limits
        $limit_check = $this->validate_contribution($donor_id, $amount, $election_type);
        if (is_wp_error($limit_check)) {
            $this->audit_trail->log_event('contribution_limit_exceeded', array(
                'donor_id' => $donor_id,
                'amount' => $amount,
                'election_type' => $election_type,
                'error' => $limit_check->get_error_message(),
            ));

            return $limit_check;
        }

        // Prepare contribution data
        $contribution_date = isset($data['contribution_date']) ? $data['contribution_date'] : current_time('mysql');
        $received_date = isset($data['received_date']) ? $data['received_date'] : current_time('mysql');
        $election_year = isset($data['election_year']) ? absint($data['election_year']) : date('Y');

        // Calculate if contribution requires itemization (over $200 aggregate)
        $current_aggregate = $this->get_donor_aggregate($donor_id, $election_type, $election_year);
        $new_aggregate = $current_aggregate + $amount;
        $is_itemized = $new_aggregate > CP_FEC_ITEMIZATION_THRESHOLD;

        // Check if requires 48-hour notice (over $1,000 within 20 days of election)
        $requires_48hour = $this->check_48hour_requirement($amount, $contribution_date);

        // Prepare insert data
        $insert_data = array(
            'donor_id' => $donor_id,
            'contribution_type' => isset($data['contribution_type']) ? sanitize_text_field($data['contribution_type']) : 'monetary',
            'amount' => $amount,
            'contribution_date' => $contribution_date,
            'received_date' => $received_date,
            'election_type' => $election_type,
            'election_year' => $election_year,
            'payment_method' => isset($data['payment_method']) ? sanitize_text_field($data['payment_method']) : 'credit_card',
            'check_number' => isset($data['check_number']) ? sanitize_text_field($data['check_number']) : '',
            'transaction_id' => isset($data['transaction_id']) ? sanitize_text_field($data['transaction_id']) : '',
            'memo_text' => isset($data['memo_text']) ? sanitize_textarea_field($data['memo_text']) : '',
            'is_inkind' => isset($data['is_inkind']) ? (int)$data['is_inkind'] : 0,
            'inkind_description' => isset($data['inkind_description']) ? sanitize_textarea_field($data['inkind_description']) : '',
            'inkind_fair_market_value' => isset($data['inkind_fair_market_value']) ? floatval($data['inkind_fair_market_value']) : 0.00,
            'contribution_status' => isset($data['contribution_status']) ? sanitize_text_field($data['contribution_status']) : 'completed',
            'is_itemized' => $is_itemized ? 1 : 0,
            'requires_48hour_notice' => $requires_48hour ? 1 : 0,
            'aggregate_primary_ytd' => $election_type === 'primary' ? $new_aggregate : $current_aggregate,
            'aggregate_general_ytd' => $election_type === 'general' ? $new_aggregate : $current_aggregate,
            'aggregate_cycle_ytd' => $new_aggregate,
            'created_by' => get_current_user_id(),
            'created_date' => current_time('mysql'),
        );

        // Handle conduit information (earmarked contributions)
        if (isset($data['conduit_name'])) {
            $insert_data['conduit_name'] = sanitize_text_field($data['conduit_name']);
            $insert_data['conduit_street1'] = isset($data['conduit_street1']) ? sanitize_text_field($data['conduit_street1']) : '';
            $insert_data['conduit_city'] = isset($data['conduit_city']) ? sanitize_text_field($data['conduit_city']) : '';
            $insert_data['conduit_state'] = isset($data['conduit_state']) ? sanitize_text_field($data['conduit_state']) : '';
            $insert_data['conduit_zip'] = isset($data['conduit_zip']) ? sanitize_text_field($data['conduit_zip']) : '';
        }

        // Insert contribution
        $result = $wpdb->insert($this->table_name, $insert_data);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to record contribution.', 'campaignpress'));
        }

        $contribution_id = $wpdb->insert_id;

        // Generate receipt number
        $receipt_number = $this->generate_receipt_number($contribution_id);
        $wpdb->update(
            $this->table_name,
            array('receipt_number' => $receipt_number),
            array('id' => $contribution_id),
            array('%s'),
            array('%d')
        );

        // Update donor aggregate totals
        $this->donors->update_aggregate_totals($donor_id);

        // Generate and send receipt if enabled
        if (get_option('cp_fec_auto_send_receipts', true)) {
            $this->generate_receipt($contribution_id);
        }

        // Send 48-hour notice alert if required
        if ($requires_48hour) {
            $this->send_48hour_alert($contribution_id);
        }

        // Log contribution in audit trail
        $this->audit_trail->log_event('contribution_recorded', array(
            'contribution_id' => $contribution_id,
            'donor_id' => $donor_id,
            'amount' => $amount,
            'election_type' => $election_type,
            'is_itemized' => $is_itemized,
            'requires_48hour_notice' => $requires_48hour,
        ));

        // Action hook after contribution recorded
        do_action('cp_fec_contribution_recorded', $contribution_id, $insert_data);

        return $contribution_id;
    }

    /**
     * Validate contribution against FEC limits
     *
     * Checks contribution amount against applicable limits based on:
     * - Committee type (candidate, PAC, party)
     * - Donor type (individual, PAC, party)
     * - Election type (primary, general)
     * - Current aggregate for donor
     *
     * @since 1.0.0
     * @param int $donor_id Donor ID
     * @param float $amount Contribution amount
     * @param string $election_type Election type (primary, general, special)
     * @return bool|WP_Error True if valid, error if exceeds limits
     */
    public function validate_contribution($donor_id, $amount, $election_type = 'primary') {
        // Get donor information
        $donor = $this->donors->get_donor($donor_id);
        if (!$donor) {
            return new WP_Error('donor_not_found', __('Donor not found.', 'campaignpress'));
        }

        // Get committee type
        $committee_info = get_option('cp_fec_committee_info', array());
        $committee_type = isset($committee_info['committee_type']) ? $committee_info['committee_type'] : 'candidate';

        // Determine applicable limit
        $limit = $this->get_contribution_limit($donor->donor_type, $committee_type);

        // Get current aggregate for this election
        $election_year = date('Y');
        $current_aggregate = $this->get_donor_aggregate($donor_id, $election_type, $election_year);

        // Calculate new aggregate
        $new_aggregate = $current_aggregate + $amount;

        // Check if exceeds limit
        if ($new_aggregate > $limit) {
            $remaining = max(0, $limit - $current_aggregate);

            return new WP_Error(
                'contribution_limit_exceeded',
                sprintf(
                    __('Contribution exceeds FEC limits. Limit: %s, Current aggregate: %s, Remaining: %s, Attempted: %s', 'campaignpress'),
                    cp_fec_format_amount($limit),
                    cp_fec_format_amount($current_aggregate),
                    cp_fec_format_amount($remaining),
                    cp_fec_format_amount($amount)
                ),
                array(
                    'limit' => $limit,
                    'current_aggregate' => $current_aggregate,
                    'remaining' => $remaining,
                    'attempted' => $amount,
                )
            );
        }

        // Return validation success with limit information
        return array(
            'valid' => true,
            'limit' => $limit,
            'current_aggregate' => $current_aggregate,
            'new_aggregate' => $new_aggregate,
            'remaining' => $limit - $new_aggregate,
        );
    }

    /**
     * Get contribution limit based on donor and committee types
     *
     * @since 1.0.0
     * @param string $donor_type Donor type (individual, pac, party, candidate)
     * @param string $committee_type Committee type (candidate, pac, party)
     * @return float Contribution limit
     */
    private function get_contribution_limit($donor_type, $committee_type) {
        // Default limits for candidate committees
        $limits = array(
            'individual' => array(
                'candidate' => CP_FEC_INDIVIDUAL_LIMIT_CANDIDATE,
                'pac' => CP_FEC_INDIVIDUAL_LIMIT_PAC,
                'party' => CP_FEC_INDIVIDUAL_LIMIT_PARTY,
            ),
            'pac' => array(
                'candidate' => CP_FEC_PAC_LIMIT_CANDIDATE,
                'pac' => CP_FEC_INDIVIDUAL_LIMIT_PAC,
                'party' => 15000, // PAC to party committee
            ),
            'party' => array(
                'candidate' => 5000, // Party committee to candidate (coordinated)
                'pac' => CP_FEC_INDIVIDUAL_LIMIT_PAC,
                'party' => 999999, // Unlimited party-to-party transfers
            ),
        );

        // Get applicable limit
        if (isset($limits[$donor_type][$committee_type])) {
            return $limits[$donor_type][$committee_type];
        }

        // Default to individual limit for candidate
        return CP_FEC_INDIVIDUAL_LIMIT_CANDIDATE;
    }

    /**
     * Get donor aggregate contributions for election
     *
     * @since 1.0.0
     * @param int $donor_id Donor ID
     * @param string $election_type Election type
     * @param int $election_year Election year
     * @return float Aggregate amount
     */
    public function get_donor_aggregate($donor_id, $election_type, $election_year) {
        global $wpdb;

        $aggregate = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(amount), 0)
             FROM {$this->table_name}
             WHERE donor_id = %d
             AND election_type = %s
             AND election_year = %d
             AND contribution_status = 'completed'",
            $donor_id,
            $election_type,
            $election_year
        ));

        return floatval($aggregate);
    }

    /**
     * Get donor contribution totals across all elections
     *
     * @since 1.0.0
     * @param int $donor_id Donor ID
     * @return array Contribution totals by election type
     */
    public function get_donor_contribution_totals($donor_id) {
        $election_year = date('Y');

        return array(
            'primary' => $this->get_donor_aggregate($donor_id, 'primary', $election_year),
            'general' => $this->get_donor_aggregate($donor_id, 'general', $election_year),
            'special' => $this->get_donor_aggregate($donor_id, 'special', $election_year),
            'total' => $this->get_donor_aggregate_all($donor_id, $election_year),
        );
    }

    /**
     * Get donor aggregate across all elections for year
     *
     * @since 1.0.0
     * @param int $donor_id Donor ID
     * @param int $election_year Election year
     * @return float Total aggregate
     */
    private function get_donor_aggregate_all($donor_id, $election_year) {
        global $wpdb;

        $aggregate = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(amount), 0)
             FROM {$this->table_name}
             WHERE donor_id = %d
             AND election_year = %d
             AND contribution_status = 'completed'",
            $donor_id,
            $election_year
        ));

        return floatval($aggregate);
    }

    /**
     * Check if contribution requires 48-hour notice
     *
     * Per 11 CFR §104.22, contributions over $1,000 received within 20 days
     * before an election require 48-hour notice to FEC
     *
     * @since 1.0.0
     * @param float $amount Contribution amount
     * @param string $contribution_date Contribution date
     * @return bool True if requires 48-hour notice
     */
    private function check_48hour_requirement($amount, $contribution_date) {
        // Must be over threshold
        if ($amount < CP_FEC_48HOUR_THRESHOLD) {
            return false;
        }

        // Check if within 20 days before election
        $election_date = get_option('cp_fec_next_election_date');
        if (!$election_date) {
            return false;
        }

        $days_before_election = (strtotime($election_date) - strtotime($contribution_date)) / DAY_IN_SECONDS;

        return $days_before_election >= 0 && $days_before_election <= 20;
    }

    /**
     * Check for contributions requiring 48-hour notice
     *
     * Runs daily to identify unfiled 48-hour notices
     *
     * @since 1.0.0
     */
    public function check_48hour_notices() {
        global $wpdb;

        $unfiled = $wpdb->get_results(
            "SELECT * FROM {$this->table_name}
             WHERE requires_48hour_notice = 1
             AND notice_48hour_filed = 0
             ORDER BY contribution_date DESC"
        );

        if (!empty($unfiled)) {
            // Send alert email
            $alert_email = get_option('cp_fec_alert_email', get_option('admin_email'));

            if ($alert_email) {
                $subject = sprintf(__('[FEC Alert] %d Unfiled 48-Hour Notices', 'campaignpress'), count($unfiled));
                $message = sprintf(__('You have %d contributions requiring 48-hour notice that have not been filed with the FEC.', 'campaignpress'), count($unfiled));
                $message .= "\n\n" . admin_url('admin.php?page=cp-fec-contributions&filter=48hour-unfiled');

                wp_mail($alert_email, $subject, $message);
            }

            // Log alert
            $this->audit_trail->log_event('48hour_notice_alert', array(
                'unfiled_count' => count($unfiled),
            ));
        }
    }

    /**
     * Send 48-hour notice alert
     *
     * @since 1.0.0
     * @param int $contribution_id Contribution ID
     */
    private function send_48hour_alert($contribution_id) {
        $contribution = $this->get_contribution($contribution_id);
        if (!$contribution) {
            return;
        }

        $donor = $this->donors->get_donor($contribution->donor_id);

        $alert_email = get_option('cp_fec_alert_email', get_option('admin_email'));

        if ($alert_email) {
            $subject = __('[FEC Alert] 48-Hour Notice Required', 'campaignpress');
            $message = sprintf(
                __('A contribution requiring 48-hour notice has been received:\n\nDonor: %s %s\nAmount: %s\nDate: %s\n\nFile 48-hour notice at: %s', 'campaignpress'),
                $donor->first_name,
                $donor->last_name,
                cp_fec_format_amount($contribution->amount),
                date_i18n(get_option('date_format'), strtotime($contribution->contribution_date)),
                admin_url('admin.php?page=cp-fec-contributions&contribution_id=' . $contribution_id)
            );

            wp_mail($alert_email, $subject, $message);
        }
    }

    /**
     * Check for donors approaching contribution limits
     *
     * @since 1.0.0
     */
    public function check_approaching_limits() {
        global $wpdb;

        // Get donors at 80% or more of limit
        $committee_info = get_option('cp_fec_committee_info', array());
        $committee_type = isset($committee_info['committee_type']) ? $committee_info['committee_type'] : 'candidate';
        $limit = CP_FEC_INDIVIDUAL_LIMIT_CANDIDATE; // Assuming individual donors to candidate

        $threshold = $limit * 0.8;

        $donors_table = $wpdb->prefix . 'cp_fec_donors';

        $approaching = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$donors_table}
             WHERE aggregate_cycle >= %f
             AND aggregate_cycle < %f
             ORDER BY aggregate_cycle DESC",
            $threshold,
            $limit
        ));

        if (!empty($approaching)) {
            $this->audit_trail->log_event('donors_approaching_limit', array(
                'count' => count($approaching),
                'threshold' => $threshold,
                'limit' => $limit,
            ));
        }
    }

    /**
     * Process contribution refund
     *
     * Per FEC regulations, refunds must be properly documented and reported
     *
     * @since 1.0.0
     * @param int $contribution_id Original contribution ID
     * @param float $refund_amount Amount to refund
     * @param string $refund_reason Reason for refund
     * @return bool|WP_Error True on success, error on failure
     */
    public function process_refund($contribution_id, $refund_amount, $refund_reason) {
        global $wpdb;

        // Get original contribution
        $contribution = $this->get_contribution($contribution_id);
        if (!$contribution) {
            return new WP_Error('contribution_not_found', __('Contribution not found.', 'campaignpress'));
        }

        // Validate refund amount
        if ($refund_amount > $contribution->amount) {
            return new WP_Error('invalid_refund', __('Refund amount cannot exceed contribution amount.', 'campaignpress'));
        }

        // Update contribution record with refund information
        $result = $wpdb->update(
            $this->table_name,
            array(
                'refund_date' => current_time('mysql'),
                'refund_amount' => $refund_amount,
                'refund_reason' => sanitize_textarea_field($refund_reason),
                'contribution_status' => $refund_amount == $contribution->amount ? 'refunded' : 'partially_refunded',
                'modified_date' => current_time('mysql'),
            ),
            array('id' => $contribution_id),
            array('%s', '%f', '%s', '%s', '%s'),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to process refund.', 'campaignpress'));
        }

        // Update donor aggregate totals
        $this->donors->update_aggregate_totals($contribution->donor_id);

        // Log refund in audit trail
        $this->audit_trail->log_event('contribution_refunded', array(
            'contribution_id' => $contribution_id,
            'donor_id' => $contribution->donor_id,
            'original_amount' => $contribution->amount,
            'refund_amount' => $refund_amount,
            'refund_reason' => $refund_reason,
        ));

        // Action hook after refund
        do_action('cp_fec_contribution_refunded', $contribution_id, $refund_amount, $refund_reason);

        return true;
    }

    /**
     * Generate receipt for contribution
     *
     * @since 1.0.0
     * @param int $contribution_id Contribution ID
     * @return bool True on success
     */
    public function generate_receipt($contribution_id) {
        global $wpdb;

        $contribution = $this->get_contribution($contribution_id);
        if (!$contribution) {
            return false;
        }

        $donor = $this->donors->get_donor($contribution->donor_id);
        if (!$donor) {
            return false;
        }

        // Generate receipt PDF/email (implementation depends on requirements)
        // This is a placeholder for receipt generation logic

        // Mark receipt as generated
        $wpdb->update(
            $this->table_name,
            array(
                'receipt_generated' => 1,
                'receipt_sent_date' => current_time('mysql'),
            ),
            array('id' => $contribution_id),
            array('%d', '%s'),
            array('%d')
        );

        // Allow custom receipt generation
        do_action('cp_fec_generate_receipt', $contribution_id, $contribution, $donor);

        return true;
    }

    /**
     * Generate receipt number
     *
     * @since 1.0.0
     * @param int $contribution_id Contribution ID
     * @return string Receipt number
     */
    private function generate_receipt_number($contribution_id) {
        $year = date('Y');
        $receipt_number = sprintf('RCP-%s-%06d', $year, $contribution_id);

        // Allow customization via filter
        return apply_filters('cp_fec_receipt_number', $receipt_number, $contribution_id);
    }

    /**
     * Get contribution by ID
     *
     * @since 1.0.0
     * @param int $contribution_id Contribution ID
     * @return object|null Contribution object or null
     */
    public function get_contribution($contribution_id) {
        global $wpdb;

        $contribution = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $contribution_id
        ));

        return $contribution;
    }

    /**
     * Get contributions
     *
     * @since 1.0.0
     * @param array $args Query arguments
     * @return array Contributions and pagination data
     */
    public function get_contributions($args = array()) {
        global $wpdb;

        $defaults = array(
            'donor_id' => 0,
            'election_type' => '',
            'election_year' => '',
            'date_from' => '',
            'date_to' => '',
            'min_amount' => 0,
            'max_amount' => 0,
            'contribution_status' => '',
            'requires_48hour_notice' => '',
            'is_itemized' => '',
            'page' => 1,
            'per_page' => 50,
            'orderby' => 'contribution_date',
            'order' => 'DESC',
        );

        $args = wp_parse_args($args, $defaults);

        $where = array('1=1');
        $where_values = array();

        // Filter by donor
        if (!empty($args['donor_id'])) {
            $where[] = "donor_id = %d";
            $where_values[] = $args['donor_id'];
        }

        // Filter by election type
        if (!empty($args['election_type'])) {
            $where[] = "election_type = %s";
            $where_values[] = $args['election_type'];
        }

        // Filter by election year
        if (!empty($args['election_year'])) {
            $where[] = "election_year = %d";
            $where_values[] = $args['election_year'];
        }

        // Filter by date range
        if (!empty($args['date_from'])) {
            $where[] = "contribution_date >= %s";
            $where_values[] = $args['date_from'];
        }

        if (!empty($args['date_to'])) {
            $where[] = "contribution_date <= %s";
            $where_values[] = $args['date_to'];
        }

        // Filter by amount range
        if (!empty($args['min_amount'])) {
            $where[] = "amount >= %f";
            $where_values[] = $args['min_amount'];
        }

        if (!empty($args['max_amount'])) {
            $where[] = "amount <= %f";
            $where_values[] = $args['max_amount'];
        }

        // Filter by status
        if (!empty($args['contribution_status'])) {
            $where[] = "contribution_status = %s";
            $where_values[] = $args['contribution_status'];
        }

        // Filter by 48-hour notice requirement
        if ($args['requires_48hour_notice'] !== '') {
            $where[] = "requires_48hour_notice = %d";
            $where_values[] = $args['requires_48hour_notice'] ? 1 : 0;
        }

        // Filter by itemization
        if ($args['is_itemized'] !== '') {
            $where[] = "is_itemized = %d";
            $where_values[] = $args['is_itemized'] ? 1 : 0;
        }

        $where_clause = implode(' AND ', $where);

        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}";
        if (!empty($where_values)) {
            $count_query = $wpdb->prepare($count_query, $where_values);
        }
        $total = $wpdb->get_var($count_query);

        // Calculate pagination
        $offset = ($args['page'] - 1) * $args['per_page'];

        // Get contributions
        $query = "SELECT * FROM {$this->table_name}
                  WHERE {$where_clause}
                  ORDER BY {$args['orderby']} {$args['order']}
                  LIMIT %d OFFSET %d";

        $where_values[] = $args['per_page'];
        $where_values[] = $offset;

        $contributions = $wpdb->get_results($wpdb->prepare($query, $where_values));

        return array(
            'contributions' => $contributions,
            'total' => $total,
            'page' => $args['page'],
            'per_page' => $args['per_page'],
            'pages' => ceil($total / $args['per_page']),
        );
    }

    /**
     * Validate contribution data
     *
     * @since 1.0.0
     * @param array $data Contribution data
     * @return bool|WP_Error True if valid, error otherwise
     */
    private function validate_contribution_data($data) {
        $errors = array();

        if (empty($data['donor_id'])) {
            $errors[] = __('Donor ID is required.', 'campaignpress');
        }

        if (empty($data['amount']) || floatval($data['amount']) <= 0) {
            $errors[] = __('Valid contribution amount is required.', 'campaignpress');
        }

        if (!empty($errors)) {
            return new WP_Error('validation_error', implode(' ', $errors));
        }

        return true;
    }

    /**
     * Get contribution statistics
     *
     * @since 1.0.0
     * @param array $args Filter arguments
     * @return array Statistics
     */
    public function get_statistics($args = array()) {
        global $wpdb;

        $defaults = array(
            'election_year' => date('Y'),
            'election_type' => '',
        );

        $args = wp_parse_args($args, $defaults);

        $where = array('contribution_status = "completed"');
        $where_values = array();

        if (!empty($args['election_year'])) {
            $where[] = "election_year = %d";
            $where_values[] = $args['election_year'];
        }

        if (!empty($args['election_type'])) {
            $where[] = "election_type = %s";
            $where_values[] = $args['election_type'];
        }

        $where_clause = implode(' AND ', $where);

        $query = "SELECT
                    COUNT(*) as total_contributions,
                    SUM(amount) as total_amount,
                    AVG(amount) as average_amount,
                    MIN(amount) as min_amount,
                    MAX(amount) as max_amount,
                    SUM(CASE WHEN is_itemized = 1 THEN 1 ELSE 0 END) as itemized_count,
                    SUM(CASE WHEN requires_48hour_notice = 1 THEN 1 ELSE 0 END) as notice_48hour_count
                  FROM {$this->table_name}
                  WHERE {$where_clause}";

        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }

        $stats = $wpdb->get_row($query, ARRAY_A);

        return $stats;
    }
}
