<?php
/**
 * CampaignPress FEC Audit Trail
 *
 * Comprehensive audit logging system for FEC compliance. Maintains detailed
 * records of all financial transactions, user actions, compliance checks,
 * and system events for FEC audit and record-keeping requirements.
 *
 * FEC Requirements Implemented:
 * - 11 CFR §102.9 - Record retention (3+ years)
 * - 11 CFR §104.14 - Preservation of records
 * - 11 CFR §111 - Audit and investigation compliance
 * - Complete transaction history and modification tracking
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
 * FEC Audit Trail Class
 *
 * @since 1.0.0
 */
class CampaignPress_FEC_Audit_Trail {

    /**
     * Database table name
     *
     * @var string
     */
    private $table_name;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'cp_fec_audit_log';
    }

    /**
     * Create audit log database table
     *
     * @since 1.0.0
     */
    public function create_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_type varchar(100) NOT NULL,
            event_category varchar(50) DEFAULT 'general',
            event_description text DEFAULT '',
            user_id bigint(20) UNSIGNED DEFAULT 0,
            user_login varchar(100) DEFAULT '',
            user_role varchar(100) DEFAULT '',
            ip_address varchar(100) DEFAULT '',
            user_agent text DEFAULT '',
            related_object_type varchar(50) DEFAULT '',
            related_object_id bigint(20) UNSIGNED DEFAULT 0,
            donor_id bigint(20) UNSIGNED DEFAULT 0,
            contribution_id bigint(20) UNSIGNED DEFAULT 0,
            report_id bigint(20) UNSIGNED DEFAULT 0,
            amount decimal(10,2) DEFAULT 0.00,
            event_data longtext DEFAULT '',
            compliance_check_result varchar(50) DEFAULT '',
            compliance_check_details text DEFAULT '',
            severity varchar(20) DEFAULT 'info',
            is_error tinyint(1) DEFAULT 0,
            error_message text DEFAULT '',
            session_id varchar(100) DEFAULT '',
            request_uri text DEFAULT '',
            http_method varchar(10) DEFAULT '',
            event_timestamp datetime NOT NULL,
            created_date datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY event_type_idx (event_type),
            KEY event_category_idx (event_category),
            KEY user_id_idx (user_id),
            KEY donor_id_idx (donor_id),
            KEY contribution_id_idx (contribution_id),
            KEY event_timestamp_idx (event_timestamp),
            KEY severity_idx (severity),
            KEY is_error_idx (is_error)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Log audit event
     *
     * Records an event in the audit trail with comprehensive metadata
     *
     * @since 1.0.0
     * @param string $event_type Event type identifier
     * @param array $data Event data
     * @param string $severity Severity level (info, warning, error, critical)
     * @return int|false Log entry ID or false on failure
     */
    public function log_event($event_type, $data = array(), $severity = 'info') {
        global $wpdb;

        // Get current user information
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $user_login = $current_user->user_login;
        $user_roles = $current_user->roles;
        $user_role = !empty($user_roles) ? $user_roles[0] : '';

        // Prepare audit data
        $insert_data = array(
            'event_type' => sanitize_text_field($event_type),
            'event_category' => $this->get_event_category($event_type),
            'event_description' => $this->get_event_description($event_type, $data),
            'user_id' => $user_id,
            'user_login' => $user_login,
            'user_role' => $user_role,
            'ip_address' => $this->get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr(sanitize_text_field($_SERVER['HTTP_USER_AGENT']), 0, 255) : '',
            'session_id' => session_id(),
            'request_uri' => isset($_SERVER['REQUEST_URI']) ? sanitize_text_field($_SERVER['REQUEST_URI']) : '',
            'http_method' => isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field($_SERVER['REQUEST_METHOD']) : '',
            'severity' => sanitize_text_field($severity),
            'is_error' => in_array($severity, array('error', 'critical')) ? 1 : 0,
            'event_timestamp' => current_time('mysql'),
            'created_date' => current_time('mysql'),
        );

        // Extract specific IDs from data
        if (isset($data['donor_id'])) {
            $insert_data['donor_id'] = absint($data['donor_id']);
        }

        if (isset($data['contribution_id'])) {
            $insert_data['contribution_id'] = absint($data['contribution_id']);
        }

        if (isset($data['report_id'])) {
            $insert_data['report_id'] = absint($data['report_id']);
        }

        if (isset($data['amount'])) {
            $insert_data['amount'] = floatval($data['amount']);
        }

        if (isset($data['related_object_type'])) {
            $insert_data['related_object_type'] = sanitize_text_field($data['related_object_type']);
        }

        if (isset($data['related_object_id'])) {
            $insert_data['related_object_id'] = absint($data['related_object_id']);
        }

        // Store compliance check results
        if (isset($data['compliance_check_result'])) {
            $insert_data['compliance_check_result'] = sanitize_text_field($data['compliance_check_result']);
        }

        if (isset($data['compliance_check_details'])) {
            $insert_data['compliance_check_details'] = sanitize_textarea_field($data['compliance_check_details']);
        }

        if (isset($data['error_message'])) {
            $insert_data['error_message'] = sanitize_textarea_field($data['error_message']);
        }

        // Serialize and store full event data (JSON format)
        $insert_data['event_data'] = json_encode($data);

        // Insert audit log entry
        $result = $wpdb->insert($this->table_name, $insert_data);

        if ($result === false) {
            // If logging fails, try to log to PHP error log
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(sprintf(
                    'FEC Audit Log Insert Failed: Type=%s, User=%s, Data=%s',
                    $event_type,
                    $user_login,
                    json_encode($data)
                ));
            }
            return false;
        }

        $log_id = $wpdb->insert_id;

        // Fire action hook after logging
        do_action('cp_fec_audit_logged', $log_id, $event_type, $data);

        return $log_id;
    }

    /**
     * Get event category based on event type
     *
     * @since 1.0.0
     * @param string $event_type Event type
     * @return string Event category
     */
    private function get_event_category($event_type) {
        $categories = array(
            // Contribution events
            'contribution_recorded' => 'contribution',
            'contribution_updated' => 'contribution',
            'contribution_refunded' => 'contribution',
            'contribution_deleted' => 'contribution',
            'contribution_limit_exceeded' => 'compliance',
            'prohibited_contribution_blocked' => 'compliance',

            // Donor events
            'donor_created' => 'donor',
            'donor_updated' => 'donor',
            'donor_deleted' => 'donor',
            'donor_flagged_prohibited' => 'compliance',

            // Report events
            'report_generated' => 'report',
            'report_filed' => 'report',
            'report_amended' => 'report',
            'report_exported' => 'report',

            // Compliance events
            'daily_compliance_check' => 'compliance',
            '48hour_notice_alert' => 'compliance',
            'donors_approaching_limit' => 'compliance',

            // System events
            'settings_updated' => 'system',
            'database_updated' => 'system',
            'audit_export' => 'system',
        );

        return isset($categories[$event_type]) ? $categories[$event_type] : 'general';
    }

    /**
     * Get human-readable event description
     *
     * @since 1.0.0
     * @param string $event_type Event type
     * @param array $data Event data
     * @return string Event description
     */
    private function get_event_description($event_type, $data) {
        $descriptions = array(
            'contribution_recorded' => sprintf(
                __('Contribution of %s recorded from donor #%d', 'campaign-office'),
                isset($data['amount']) ? cp_fec_format_amount($data['amount']) : '$0.00',
                isset($data['donor_id']) ? $data['donor_id'] : 0
            ),
            'contribution_refunded' => sprintf(
                __('Contribution #%d refunded: %s', 'campaign-office'),
                isset($data['contribution_id']) ? $data['contribution_id'] : 0,
                isset($data['refund_amount']) ? cp_fec_format_amount($data['refund_amount']) : '$0.00'
            ),
            'contribution_limit_exceeded' => sprintf(
                __('Contribution limit exceeded for donor #%d', 'campaign-office'),
                isset($data['donor_id']) ? $data['donor_id'] : 0
            ),
            'prohibited_contribution_blocked' => sprintf(
                __('Prohibited contribution blocked from donor #%d: %s', 'campaign-office'),
                isset($data['donor_id']) ? $data['donor_id'] : 0,
                isset($data['reason']) ? $data['reason'] : 'Unknown reason'
            ),
            'donor_created' => __('New donor profile created', 'campaign-office'),
            'donor_updated' => sprintf(
                __('Donor #%d profile updated', 'campaign-office'),
                isset($data['donor_id']) ? $data['donor_id'] : 0
            ),
            'donor_flagged_prohibited' => sprintf(
                __('Donor #%d flagged as prohibited source', 'campaign-office'),
                isset($data['donor_id']) ? $data['donor_id'] : 0
            ),
            'report_generated' => sprintf(
                __('%s report generated for period %s', 'campaign-office'),
                isset($data['report_type']) ? $data['report_type'] : 'Unknown',
                isset($data['report_period']) ? $data['report_period'] : 'Unknown'
            ),
            'report_filed' => sprintf(
                __('Report #%d filed with FEC', 'campaign-office'),
                isset($data['report_id']) ? $data['report_id'] : 0
            ),
            '48hour_notice_alert' => __('48-hour notice requirement triggered', 'campaign-office'),
            'daily_compliance_check' => __('Daily compliance check completed', 'campaign-office'),
            'settings_updated' => __('Compliance settings updated', 'campaign-office'),
        );

        // Return custom description if available, otherwise use event type
        return isset($descriptions[$event_type]) ? $descriptions[$event_type] : ucfirst(str_replace('_', ' ', $event_type));
    }

    /**
     * Get client IP address
     *
     * @since 1.0.0
     * @return string IP address
     */
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return 'Unknown';
    }

    /**
     * Get audit log entries
     *
     * @since 1.0.0
     * @param array $args Query arguments
     * @return array Audit entries and pagination data
     */
    public function get_audit_logs($args = array()) {
        global $wpdb;

        $defaults = array(
            'event_type' => '',
            'event_category' => '',
            'user_id' => 0,
            'donor_id' => 0,
            'contribution_id' => 0,
            'report_id' => 0,
            'severity' => '',
            'is_error' => '',
            'date_from' => '',
            'date_to' => '',
            'search' => '',
            'page' => 1,
            'per_page' => 50,
            'orderby' => 'event_timestamp',
            'order' => 'DESC',
        );

        $args = wp_parse_args($args, $defaults);

        $where = array('1=1');
        $where_values = array();

        // Filter by event type
        if (!empty($args['event_type'])) {
            $where[] = "event_type = %s";
            $where_values[] = $args['event_type'];
        }

        // Filter by event category
        if (!empty($args['event_category'])) {
            $where[] = "event_category = %s";
            $where_values[] = $args['event_category'];
        }

        // Filter by user
        if (!empty($args['user_id'])) {
            $where[] = "user_id = %d";
            $where_values[] = $args['user_id'];
        }

        // Filter by donor
        if (!empty($args['donor_id'])) {
            $where[] = "donor_id = %d";
            $where_values[] = $args['donor_id'];
        }

        // Filter by contribution
        if (!empty($args['contribution_id'])) {
            $where[] = "contribution_id = %d";
            $where_values[] = $args['contribution_id'];
        }

        // Filter by report
        if (!empty($args['report_id'])) {
            $where[] = "report_id = %d";
            $where_values[] = $args['report_id'];
        }

        // Filter by severity
        if (!empty($args['severity'])) {
            $where[] = "severity = %s";
            $where_values[] = $args['severity'];
        }

        // Filter by error status
        if ($args['is_error'] !== '') {
            $where[] = "is_error = %d";
            $where_values[] = $args['is_error'] ? 1 : 0;
        }

        // Filter by date range
        if (!empty($args['date_from'])) {
            $where[] = "event_timestamp >= %s";
            $where_values[] = $args['date_from'];
        }

        if (!empty($args['date_to'])) {
            $where[] = "event_timestamp <= %s";
            $where_values[] = $args['date_to'];
        }

        // Search in description and event data
        if (!empty($args['search'])) {
            $where[] = "(event_description LIKE %s OR event_data LIKE %s OR user_login LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
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

        // Get audit logs
        $query = "SELECT * FROM {$this->table_name}
                  WHERE {$where_clause}
                  ORDER BY {$args['orderby']} {$args['order']}
                  LIMIT %d OFFSET %d";

        $where_values[] = $args['per_page'];
        $where_values[] = $offset;

        $logs = $wpdb->get_results($wpdb->prepare($query, $where_values));

        // Decode event_data JSON for each log
        foreach ($logs as $log) {
            $log->event_data_decoded = json_decode($log->event_data, true);
        }

        return array(
            'logs' => $logs,
            'total' => $total,
            'page' => $args['page'],
            'per_page' => $args['per_page'],
            'pages' => ceil($total / $args['per_page']),
        );
    }

    /**
     * Get audit log entry by ID
     *
     * @since 1.0.0
     * @param int $log_id Log ID
     * @return object|null Log entry or null
     */
    public function get_audit_log($log_id) {
        global $wpdb;

        $log = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $log_id
        ));

        if ($log) {
            $log->event_data_decoded = json_decode($log->event_data, true);
        }

        return $log;
    }

    /**
     * Get audit statistics
     *
     * @since 1.0.0
     * @param array $args Filter arguments
     * @return array Statistics
     */
    public function get_statistics($args = array()) {
        global $wpdb;

        $defaults = array(
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to' => date('Y-m-d'),
        );

        $args = wp_parse_args($args, $defaults);

        $where = array('1=1');
        $where_values = array();

        if (!empty($args['date_from'])) {
            $where[] = "event_timestamp >= %s";
            $where_values[] = $args['date_from'];
        }

        if (!empty($args['date_to'])) {
            $where[] = "event_timestamp <= %s";
            $where_values[] = $args['date_to'];
        }

        $where_clause = implode(' AND ', $where);

        // Get overall statistics
        $query = "SELECT
                    COUNT(*) as total_events,
                    SUM(CASE WHEN severity = 'error' OR severity = 'critical' THEN 1 ELSE 0 END) as error_count,
                    SUM(CASE WHEN event_category = 'contribution' THEN 1 ELSE 0 END) as contribution_events,
                    SUM(CASE WHEN event_category = 'compliance' THEN 1 ELSE 0 END) as compliance_events,
                    SUM(CASE WHEN event_category = 'report' THEN 1 ELSE 0 END) as report_events
                  FROM {$this->table_name}
                  WHERE {$where_clause}";

        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }

        $stats = $wpdb->get_row($query, ARRAY_A);

        // Get event counts by type
        $event_types_query = "SELECT event_type, COUNT(*) as count
                              FROM {$this->table_name}
                              WHERE {$where_clause}
                              GROUP BY event_type
                              ORDER BY count DESC
                              LIMIT 10";

        if (!empty($where_values)) {
            $event_types_query = $wpdb->prepare($event_types_query, $where_values);
        }

        $stats['top_event_types'] = $wpdb->get_results($event_types_query, ARRAY_A);

        return $stats;
    }

    /**
     * Export audit logs to CSV
     *
     * @since 1.0.0
     * @param array $args Query arguments
     * @return string|WP_Error File path or error
     */
    public function export_audit_logs($args = array()) {
        // Get audit logs (no pagination for export)
        $args['per_page'] = 999999;
        $args['page'] = 1;

        $result = $this->get_audit_logs($args);
        $logs = $result['logs'];

        if (empty($logs)) {
            return new WP_Error('no_logs', __('No audit logs found for export.', 'campaign-office'));
        }

        // Create export directory
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/fec-audit-exports/';

        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }

        // Generate filename
        $filename = 'fec-audit-log-' . date('Y-m-d-His') . '.csv';
        $filepath = $export_dir . $filename;

        // Open file for writing
        $file = fopen($filepath, 'w');

        if ($file === false) {
            return new WP_Error('file_error', __('Failed to create export file.', 'campaign-office'));
        }

        // Write CSV headers
        $headers = array(
            'ID',
            'Event Type',
            'Event Category',
            'Description',
            'User',
            'User Role',
            'Donor ID',
            'Contribution ID',
            'Report ID',
            'Amount',
            'Severity',
            'IP Address',
            'Compliance Result',
            'Error Message',
            'Timestamp',
        );

        fputcsv($file, $headers);

        // Write data rows
        foreach ($logs as $log) {
            $row = array(
                $log->id,
                $log->event_type,
                $log->event_category,
                $log->event_description,
                $log->user_login,
                $log->user_role,
                $log->donor_id,
                $log->contribution_id,
                $log->report_id,
                $log->amount,
                $log->severity,
                $log->ip_address,
                $log->compliance_check_result,
                $log->error_message,
                $log->event_timestamp,
            );

            fputcsv($file, $row);
        }

        fclose($file);

        // Log the export
        $this->log_event('audit_export', array(
            'filename' => $filename,
            'record_count' => count($logs),
        ));

        return $filepath;
    }

    /**
     * Cleanup old audit logs
     *
     * Per FEC regulations, records must be retained for 3+ years
     * This function archives logs older than retention period
     *
     * @since 1.0.0
     * @param int $retention_years Number of years to retain (minimum 3)
     * @return int Number of logs archived
     */
    public function cleanup_old_logs($retention_years = 3) {
        global $wpdb;

        // Ensure minimum 3 year retention
        $retention_years = max(3, $retention_years);

        // Calculate cutoff date
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_years} years"));

        // Before deletion, export old logs for archival
        $old_logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE event_timestamp < %s",
            $cutoff_date
        ));

        $archived_count = 0;

        if (!empty($old_logs)) {
            // Export to archive file
            $archive_file = $this->archive_old_logs($old_logs);

            if (!is_wp_error($archive_file)) {
                // Delete old logs from database after successful archive
                $deleted = $wpdb->query($wpdb->prepare(
                    "DELETE FROM {$this->table_name} WHERE event_timestamp < %s",
                    $cutoff_date
                ));

                $archived_count = $deleted;

                // Log the cleanup
                $this->log_event('audit_cleanup', array(
                    'archived_count' => $archived_count,
                    'archive_file' => $archive_file,
                    'cutoff_date' => $cutoff_date,
                ));
            }
        }

        return $archived_count;
    }

    /**
     * Archive old logs to file
     *
     * @since 1.0.0
     * @param array $logs Logs to archive
     * @return string|WP_Error Archive file path or error
     */
    private function archive_old_logs($logs) {
        // Create archive directory
        $upload_dir = wp_upload_dir();
        $archive_dir = $upload_dir['basedir'] . '/fec-audit-archives/';

        if (!file_exists($archive_dir)) {
            wp_mkdir_p($archive_dir);
        }

        // Generate archive filename
        $filename = 'fec-audit-archive-' . date('Y-m-d-His') . '.json';
        $filepath = $archive_dir . $filename;

        // Write logs as JSON
        $json = json_encode($logs, JSON_PRETTY_PRINT);

        $result = file_put_contents($filepath, $json);

        if ($result === false) {
            return new WP_Error('archive_error', __('Failed to create archive file.', 'campaign-office'));
        }

        return $filepath;
    }

    /**
     * Get recent activity for dashboard
     *
     * @since 1.0.0
     * @param int $limit Number of recent entries
     * @return array Recent audit entries
     */
    public function get_recent_activity($limit = 20) {
        global $wpdb;

        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name}
             ORDER BY event_timestamp DESC
             LIMIT %d",
            $limit
        ));

        foreach ($logs as $log) {
            $log->event_data_decoded = json_decode($log->event_data, true);
        }

        return $logs;
    }

    /**
     * Get compliance alerts (errors and warnings)
     *
     * @since 1.0.0
     * @param int $days Number of days to look back
     * @return array Compliance alerts
     */
    public function get_compliance_alerts($days = 7) {
        global $wpdb;

        $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $alerts = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name}
             WHERE event_category = 'compliance'
             AND severity IN ('warning', 'error', 'critical')
             AND event_timestamp >= %s
             ORDER BY event_timestamp DESC",
            $date_from
        ));

        foreach ($alerts as $alert) {
            $alert->event_data_decoded = json_decode($alert->event_data, true);
        }

        return $alerts;
    }
}
