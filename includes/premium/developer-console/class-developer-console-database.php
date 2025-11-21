<?php
/**
 * Developer Console Database Class
 *
 * Handles database table creation and schema management for the developer console
 *
 * @package CampaignPress
 * @subpackage DeveloperConsole
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CampaignPress_Developer_Console_Database {

    /**
     * Database version for migrations
     */
    const DB_VERSION = '1.0.0';

    /**
     * Constructor
     */
    public function __construct() {
        // Hook into activation
        add_action('campaignpress_developer_console_activate', array($this, 'create_tables'));
    }

    /**
     * Create all developer console tables
     *
     * @return bool Success status
     */
    public function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Create developer console settings table
        $this->create_settings_table($charset_collate);

        // Create developer activity logs table
        $this->create_activity_logs_table($charset_collate);

        // Create developer queries table
        $this->create_queries_table($charset_collate);

        // Store DB version
        update_option('campaignpress_dev_console_db_version', self::DB_VERSION);

        return true;
    }

    /**
     * Create developer console settings table
     *
     * @param string $charset_collate Database charset
     */
    private function create_settings_table($charset_collate) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_dev_console_settings';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            creator_user_id bigint(20) UNSIGNED NOT NULL,
            creator_email varchar(100) NOT NULL,
            api_token varchar(255) DEFAULT NULL,
            api_token_hash varchar(255) DEFAULT NULL,
            ip_whitelist text DEFAULT NULL,
            enabled tinyint(1) DEFAULT 1,
            two_factor_enabled tinyint(1) DEFAULT 0,
            session_timeout int(11) DEFAULT 3600,
            allowed_actions text DEFAULT NULL,
            security_level enum('standard','high','maximum') DEFAULT 'high',
            last_access_at datetime DEFAULT NULL,
            last_access_ip varchar(45) DEFAULT NULL,
            failed_login_attempts int(11) DEFAULT 0,
            locked_until datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY creator_user_id (creator_user_id),
            KEY creator_email (creator_email),
            KEY enabled (enabled)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create developer activity logs table
     *
     * @param string $charset_collate Database charset
     */
    private function create_activity_logs_table($charset_collate) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_dev_console_logs';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            developer_user_id bigint(20) UNSIGNED NOT NULL,
            developer_email varchar(100) NOT NULL,
            action_type varchar(50) NOT NULL,
            action_category enum('auth','database','api','system','user','data','security','settings') DEFAULT 'system',
            action_description text NOT NULL,
            action_details longtext DEFAULT NULL,
            affected_table varchar(64) DEFAULT NULL,
            affected_record_id bigint(20) DEFAULT NULL,
            sql_query text DEFAULT NULL,
            result_status enum('success','failure','warning','info') DEFAULT 'info',
            error_message text DEFAULT NULL,
            ip_address varchar(45) NOT NULL,
            user_agent varchar(255) DEFAULT NULL,
            request_method varchar(10) DEFAULT NULL,
            request_uri varchar(500) DEFAULT NULL,
            execution_time float DEFAULT NULL,
            memory_usage bigint(20) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY developer_user_id (developer_user_id),
            KEY action_type (action_type),
            KEY action_category (action_category),
            KEY result_status (result_status),
            KEY created_at (created_at),
            KEY ip_address (ip_address)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create developer saved queries table
     *
     * @param string $charset_collate Database charset
     */
    private function create_queries_table($charset_collate) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_dev_console_queries';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            developer_user_id bigint(20) UNSIGNED NOT NULL,
            query_name varchar(100) NOT NULL,
            query_description text DEFAULT NULL,
            sql_query text NOT NULL,
            query_type enum('SELECT','INSERT','UPDATE','DELETE','SHOW','DESCRIBE','CUSTOM') DEFAULT 'SELECT',
            is_favorite tinyint(1) DEFAULT 0,
            is_dangerous tinyint(1) DEFAULT 0,
            execution_count int(11) DEFAULT 0,
            last_executed_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY developer_user_id (developer_user_id),
            KEY query_type (query_type),
            KEY is_favorite (is_favorite)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Drop all developer console tables (for uninstall)
     */
    public static function drop_tables() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'cp_dev_console_settings',
            $wpdb->prefix . 'cp_dev_console_logs',
            $wpdb->prefix . 'cp_dev_console_queries'
        );

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }

        delete_option('campaignpress_dev_console_db_version');
    }

    /**
     * Check if tables exist
     *
     * @return bool
     */
    public function tables_exist() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_dev_console_settings';
        $query = $wpdb->prepare('SHOW TABLES LIKE %s', $table_name);

        return $wpdb->get_var($query) === $table_name;
    }
}
