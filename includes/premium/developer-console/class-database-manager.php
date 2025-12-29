<?php
/**
 * Developer Console Database Manager Class
 *
 * Handles database queries and management operations
 *
 * @package CampaignPress
 * @subpackage DeveloperConsole
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CampaignPress_Developer_Database_Manager {

    /**
     * Contact Migration instance
     */
    private $migration;

    /**
     * Constructor
     */
    public function __construct() {
        require_once plugin_dir_path(__FILE__) . '../../core/class-contact-migration.php';
        $this->migration = new CampaignPress_Contact_Migration();
    }

    /**
     * Dangerous query patterns
     */
    private $dangerous_patterns = array(
        'DROP\s+TABLE',
        'DROP\s+DATABASE',
        'TRUNCATE',
        'ALTER\s+TABLE.*DROP',
        'DELETE\s+FROM.*WHERE\s+1\s*=\s*1',
        'UPDATE.*WHERE\s+1\s*=\s*1',
        'GRANT',
        'REVOKE',
        'CREATE\s+USER',
        'DROP\s+USER'
    );

    /**
     * Execute a database query
     *
     * @param array $request Request data
     * @return array Result
     */
    public function execute_query($request) {
        global $wpdb;

        $start_time = microtime(true);

        // Get query
        $query = isset($request['query']) ? trim($request['query']) : '';
        $save_query = isset($request['save_query']) ? (bool)$request['save_query'] : false;
        $query_name = isset($request['query_name']) ? sanitize_text_field($request['query_name']) : '';

        if (empty($query)) {
            return array(
                'success' => false,
                'message' => 'Query is empty'
            );
        }

        // Check for dangerous patterns
        $is_dangerous = $this->is_dangerous_query($query);

        if ($is_dangerous) {
            // Require confirmation for dangerous queries
            $confirmed = isset($request['confirmed']) ? (bool)$request['confirmed'] : false;

            if (!$confirmed) {
                return array(
                    'success' => false,
                    'requires_confirmation' => true,
                    'message' => 'This query is potentially dangerous and requires confirmation.',
                    'warning' => 'This query could modify or delete data. Please review carefully before proceeding.'
                );
            }
        }

        // Detect query type
        $query_type = $this->detect_query_type($query);

        // Execute query
        try {
            // Suppress errors temporarily to handle them
            $wpdb->suppress_errors();

            if ($query_type === 'SELECT' || $query_type === 'SHOW' || $query_type === 'DESCRIBE') {
                $results = $wpdb->get_results($query, ARRAY_A);
                $affected_rows = $wpdb->num_rows;
            } else {
                $results = $wpdb->query($query);
                $affected_rows = $wpdb->rows_affected;
            }

            $execution_time = microtime(true) - $start_time;

            // Check for errors
            if ($wpdb->last_error) {
                $this->log_query($query, $query_type, 'failure', $wpdb->last_error, $execution_time);

                return array(
                    'success' => false,
                    'message' => 'Query failed: ' . $wpdb->last_error,
                    'query' => $query,
                    'execution_time' => round($execution_time, 4)
                );
            }

            // Save query if requested
            if ($save_query && !empty($query_name)) {
                $this->save_query($query_name, $query, $query_type, $is_dangerous);
            }

            // Log successful query
            $this->log_query($query, $query_type, 'success', null, $execution_time, $affected_rows);

            $response = array(
                'success' => true,
                'message' => 'Query executed successfully',
                'query_type' => $query_type,
                'execution_time' => round($execution_time, 4),
                'affected_rows' => $affected_rows
            );

            if ($query_type === 'SELECT' || $query_type === 'SHOW' || $query_type === 'DESCRIBE') {
                $response['results'] = $results;
                $response['row_count'] = count($results);
                $response['columns'] = !empty($results) ? array_keys($results[0]) : array();
            }

            return $response;

        } catch (Exception $e) {
            $execution_time = microtime(true) - $start_time;
            $this->log_query($query, $query_type, 'failure', $e->getMessage(), $execution_time);

            return array(
                'success' => false,
                'message' => 'Query failed: ' . $e->getMessage(),
                'query' => $query,
                'execution_time' => round($execution_time, 4)
            );
        }
    }

    /**
     * Check if query is dangerous
     *
     * @param string $query SQL query
     * @return bool
     */
    private function is_dangerous_query($query) {
        $query_upper = strtoupper($query);

        foreach ($this->dangerous_patterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $query_upper)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect query type
     *
     * @param string $query SQL query
     * @return string Query type
     */
    private function detect_query_type($query) {
        $query_upper = strtoupper(trim($query));

        if (strpos($query_upper, 'SELECT') === 0) {
            return 'SELECT';
        } elseif (strpos($query_upper, 'INSERT') === 0) {
            return 'INSERT';
        } elseif (strpos($query_upper, 'UPDATE') === 0) {
            return 'UPDATE';
        } elseif (strpos($query_upper, 'DELETE') === 0) {
            return 'DELETE';
        } elseif (strpos($query_upper, 'SHOW') === 0) {
            return 'SHOW';
        } elseif (strpos($query_upper, 'DESCRIBE') === 0 || strpos($query_upper, 'DESC') === 0) {
            return 'DESCRIBE';
        } else {
            return 'CUSTOM';
        }
    }

    /**
     * Save query
     *
     * @param string $name Query name
     * @param string $query SQL query
     * @param string $type Query type
     * @param bool $is_dangerous Is dangerous flag
     */
    private function save_query($name, $query, $type, $is_dangerous) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_dev_console_queries';

        $wpdb->insert(
            $table_name,
            array(
                'developer_user_id' => get_current_user_id(),
                'query_name' => $name,
                'sql_query' => $query,
                'query_type' => $type,
                'is_dangerous' => $is_dangerous ? 1 : 0
            ),
            array('%d', '%s', '%s', '%s', '%d')
        );
    }

    /**
     * Log query execution
     *
     * @param string $query SQL query
     * @param string $type Query type
     * @param string $status Status
     * @param string $error Error message
     * @param float $execution_time Execution time
     * @param int $affected_rows Affected rows
     */
    private function log_query($query, $type, $status, $error = null, $execution_time = 0, $affected_rows = 0) {
        $console = CampaignPress_Developer_Console::get_instance();

        $console->log_activity(
            'database',
            'query_executed',
            "Database query executed: {$type}",
            array(
                'query' => $query,
                'query_type' => $type,
                'affected_rows' => $affected_rows,
                'execution_time' => $execution_time
            ),
            $status,
            $error
        );
    }

    /**
     * Get saved queries
     *
     * @return array
     */
    public function get_saved_queries() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_dev_console_queries';
        $user_id = get_current_user_id();

        $queries = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE developer_user_id = %d ORDER BY is_favorite DESC, last_executed_at DESC",
            $user_id
        ));

        return $queries;
    }

    /**
     * Get all database tables
     *
     * @return array
     */
    public function get_all_tables() {
        global $wpdb;

        $tables = $wpdb->get_results(
            "SELECT
                TABLE_NAME as table_name,
                TABLE_ROWS as table_rows,
                ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS size_mb,
                ENGINE as engine
             FROM information_schema.TABLES
             WHERE table_schema = (SELECT DATABASE())
             ORDER BY table_name"
        );

        return $tables;
    }

    /**
     * Get table structure
     *
     * @param string $table_name Table name
     * @return array
     */
    public function get_table_structure($table_name) {
        global $wpdb;

        // Sanitize table name
        $table_name = sanitize_text_field($table_name);

        // Verify table exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.TABLES
             WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            $table_name
        ));

        if (!$exists) {
            return array(
                'success' => false,
                'message' => 'Table does not exist'
            );
        }

        // Get columns
        $columns = $wpdb->get_results("DESCRIBE `{$table_name}`");

        // Get indexes
        $indexes = $wpdb->get_results("SHOW INDEX FROM `{$table_name}`");

        // Get create statement
        $create_table = $wpdb->get_row("SHOW CREATE TABLE `{$table_name}`", ARRAY_N);

        return array(
            'success' => true,
            'table_name' => $table_name,
            'columns' => $columns,
            'indexes' => $indexes,
            'create_statement' => isset($create_table[1]) ? $create_table[1] : '',
            'query_type' => 'DESCRIBE',
            'execution_time' => 0.001, // Placeholder
            'row_count' => count($columns)
        );
    }

    /**
     * Get table preview
     *
     * @param string $table_name Table name
     * @param int $limit Number of rows
     * @return array
     */
    public function get_table_preview($table_name, $limit = 50) {
        global $wpdb;

        // Sanitize table name
        $table_name = sanitize_text_field($table_name);

        // Verify table exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.TABLES
             WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            $table_name
        ));

        if (!$exists) {
            return array(
                'success' => false,
                'message' => 'Table does not exist'
            );
        }

        $limit = min(absint($limit), 100); // Max 100 rows

        $results = $wpdb->get_results("SELECT * FROM `{$table_name}` LIMIT {$limit}", ARRAY_A);
        $total_rows = $wpdb->get_var("SELECT COUNT(*) FROM `{$table_name}`");

        return array(
            'success' => true,
            'table_name' => $table_name,
            'results' => $results,
            'row_count' => count($results),
            'total_rows' => $total_rows,
            'columns' => !empty($results) ? array_keys($results[0]) : array(),
            'query_type' => 'SELECT',
            'execution_time' => 0.001 // Placeholder
        );
    }

    /**
     * Optimize table
     *
     * @param string $table_name Table name
     * @return array
     */
    public function optimize_table($table_name) {
        global $wpdb;

        // Sanitize table name
        $table_name = sanitize_text_field($table_name);

        $result = $wpdb->query("OPTIMIZE TABLE `{$table_name}`");

        if ($result !== false) {
            $this->log_query("OPTIMIZE TABLE `{$table_name}`", 'CUSTOM', 'success');

            return array(
                'success' => true,
                'message' => "Table {$table_name} optimized successfully"
            );
        } else {
            $this->log_query("OPTIMIZE TABLE `{$table_name}`", 'CUSTOM', 'failure', $wpdb->last_error);

            return array(
                'success' => false,
                'message' => 'Optimization failed: ' . $wpdb->last_error
            );
        }
    }

    /**
     * Get CampaignPress specific statistics
     *
     * @return array
     */
    public function get_campaignpress_stats() {
        global $wpdb;

        $stats = array();

        // CRM stats
        $crm_contacts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cp_crm_contacts");
        $crm_interactions = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cp_crm_interactions");

        $stats['crm'] = array(
            'contacts' => $crm_contacts,
            'interactions' => $crm_interactions
        );

        // Field operations stats
        $walks = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cp_canvassing_walks");
        $phone_calls = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cp_phone_calls");

        $stats['field_ops'] = array(
            'walks' => $walks,
            'phone_calls' => $phone_calls
        );

        // FEC compliance stats
        $donors = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cp_fec_donors");
        $contributions = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cp_fec_contributions");
        $total_contributions = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}cp_fec_contributions");

        $stats['fec'] = array(
            'donors' => $donors,
            'contributions' => $contributions,
            'total_amount' => $total_contributions
        );

        // Custom post types
        $issues = wp_count_posts('cp_issue');
        $events = wp_count_posts('cp_event');
        $endorsements = wp_count_posts('cp_endorsement');
        $team = wp_count_posts('cp_team');
        $volunteers = wp_count_posts('cp_volunteer');

        $stats['content'] = array(
            'issues' => $issues->publish,
            'events' => $events->publish,
            'endorsements' => $endorsements->publish,
            'team_members' => $team->publish,
            'volunteer_opportunities' => $volunteers->publish
        );

        return $stats;
    }

    /**
     * Run the contact consolidation migration
     *
     * @return array Results
     */
    public function run_contact_migration() {
        if (!current_user_can('manage_options')) {
            return array('success' => false, 'message' => 'Unauthorized');
        }

        $results = $this->migration->run_migration();

        return array(
            'success' => true,
            'results' => $results
        );
    }
}
