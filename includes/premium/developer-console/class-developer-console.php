<?php
/**
 * Developer Console Core Class
 *
 * Main class for the CampaignPress Developer Console
 * Provides secure access to system management and debugging tools
 *
 * @package CampaignPress
 * @subpackage DeveloperConsole
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CampaignPress_Developer_Console {

    /**
     * Version
     */
    const VERSION = '1.0.0';

    /**
     * Maximum failed login attempts before lockout
     */
    const MAX_FAILED_ATTEMPTS = 5;

    /**
     * Lockout duration in seconds (30 minutes)
     */
    const LOCKOUT_DURATION = 1800;

    /**
     * Database instance
     *
     * @var CampaignPress_Developer_Console_Database
     */
    private $database;

    /**
     * Current developer settings
     *
     * @var object
     */
    private $settings;

    /**
     * Singleton instance
     *
     * @var CampaignPress_Developer_Console
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return CampaignPress_Developer_Console
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize the developer console
     */
    private function init() {
        // Load database class (only if not already loaded)
        if (!class_exists('CampaignPress_Developer_Console_Database')) {
            require_once __DIR__ . '/class-developer-console-database.php';
        }
        $this->database = new CampaignPress_Developer_Console_Database();

        // Check if tables exist, create if not
        if (!$this->database->tables_exist()) {
            $this->database->create_tables();
            $this->initialize_creator_settings();
        }

        // Load settings
        $this->load_settings();

        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'), 999);

        // Register AJAX handlers
        $this->register_ajax_handlers();

        // Add security headers
        add_action('admin_head', array($this, 'add_security_headers'));
    }

    /**
     * Initialize creator settings on first run
     */
    private function initialize_creator_settings() {
        global $wpdb;

        // Get license email as creator
        $creator_email = get_option('campaignpress_license_email', '');

        // Get current user as creator
        $creator_user_id = get_current_user_id();

        if (empty($creator_email)) {
            // Fallback to current admin user email
            $user = wp_get_current_user();
            if ($user && in_array('administrator', $user->roles)) {
                $creator_email = $user->user_email;
            }
        }

        // Insert initial settings
        $table_name = $wpdb->prefix . 'cp_dev_console_settings';

        $wpdb->insert(
            $table_name,
            array(
                'creator_user_id' => $creator_user_id,
                'creator_email' => $creator_email,
                'enabled' => 1,
                'security_level' => 'high',
                'session_timeout' => 3600,
                'allowed_actions' => json_encode(array('all')),
            ),
            array('%d', '%s', '%d', '%s', '%d', '%s')
        );

        // Store creator user ID in options for quick access
        update_option('campaignpress_dev_console_creator_id', $creator_user_id);
        update_option('campaignpress_dev_console_creator_email', $creator_email);
    }

    /**
     * Load developer console settings
     */
    private function load_settings() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_dev_console_settings';
        $this->settings = $wpdb->get_row("SELECT * FROM $table_name ORDER BY id ASC LIMIT 1");
    }

    /**
     * Check if current user is the creator
     *
     * @return bool
     */
    public function is_creator() {
        if (!is_user_logged_in()) {
            return false;
        }

        $current_user = wp_get_current_user();
        $current_user_id = $current_user->ID;
        $current_email = $current_user->user_email;

        // Must be an administrator
        if (!current_user_can('manage_options')) {
            return false;
        }

        // Check if settings exist
        if (!$this->settings) {
            return false;
        }

        // Check user ID match
        if ($this->settings->creator_user_id > 0 && $current_user_id === (int)$this->settings->creator_user_id) {
            return true;
        }

        // Check email match
        if (!empty($this->settings->creator_email) && $current_email === $this->settings->creator_email) {
            return true;
        }

        return false;
    }

    /**
     * Check if developer console is enabled
     *
     * @return bool
     */
    public function is_enabled() {
        if (!$this->settings) {
            return false;
        }

        return (bool)$this->settings->enabled;
    }

    /**
     * Check if creator account is locked
     *
     * @return bool
     */
    public function is_locked() {
        if (!$this->settings || !$this->settings->locked_until) {
            return false;
        }

        $locked_until = strtotime($this->settings->locked_until);
        $now = current_time('timestamp');

        if ($now < $locked_until) {
            return true;
        }

        // Unlock if lockout period has passed
        if ($now >= $locked_until) {
            $this->unlock_account();
            return false;
        }

        return false;
    }

    /**
     * Record failed login attempt
     */
    private function record_failed_attempt() {
        if (!$this->settings) {
            return;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_dev_console_settings';

        $attempts = (int)$this->settings->failed_login_attempts + 1;

        // Lock account if max attempts reached
        if ($attempts >= self::MAX_FAILED_ATTEMPTS) {
            $locked_until = date('Y-m-d H:i:s', current_time('timestamp') + self::LOCKOUT_DURATION);

            $wpdb->update(
                $table_name,
                array(
                    'failed_login_attempts' => $attempts,
                    'locked_until' => $locked_until
                ),
                array('id' => $this->settings->id),
                array('%d', '%s'),
                array('%d')
            );

            $this->log_activity(
                'auth',
                'account_locked',
                'Account locked due to too many failed login attempts',
                array('attempts' => $attempts, 'locked_until' => $locked_until),
                'warning'
            );
        } else {
            $wpdb->update(
                $table_name,
                array('failed_login_attempts' => $attempts),
                array('id' => $this->settings->id),
                array('%d'),
                array('%d')
            );
        }

        $this->load_settings();
    }

    /**
     * Unlock account
     */
    private function unlock_account() {
        if (!$this->settings) {
            return;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_dev_console_settings';

        $wpdb->update(
            $table_name,
            array(
                'failed_login_attempts' => 0,
                'locked_until' => null
            ),
            array('id' => $this->settings->id),
            array('%d', '%s'),
            array('%d')
        );

        $this->load_settings();
    }

    /**
     * Reset failed login attempts
     */
    private function reset_failed_attempts() {
        if (!$this->settings) {
            return;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_dev_console_settings';

        $wpdb->update(
            $table_name,
            array('failed_login_attempts' => 0),
            array('id' => $this->settings->id),
            array('%d'),
            array('%d')
        );
    }

    /**
     * Check IP whitelist
     *
     * @return bool
     */
    private function check_ip_whitelist() {
        if (!$this->settings || empty($this->settings->ip_whitelist)) {
            return true; // No whitelist = allow all
        }

        $whitelist = json_decode($this->settings->ip_whitelist, true);
        if (!is_array($whitelist) || empty($whitelist)) {
            return true;
        }

        $client_ip = $this->get_client_ip();

        foreach ($whitelist as $allowed_ip) {
            // Support CIDR notation or exact match
            if ($this->ip_in_range($client_ip, $allowed_ip)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    private function get_client_ip() {
        $ip = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return sanitize_text_field($ip);
    }

    /**
     * Check if IP is in range (supports CIDR)
     *
     * @param string $ip IP to check
     * @param string $range IP or CIDR range
     * @return bool
     */
    private function ip_in_range($ip, $range) {
        if ($ip === $range) {
            return true;
        }

        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        list($subnet, $bits) = explode('/', $range);

        $ip_long = ip2long($ip);
        $subnet_long = ip2long($subnet);
        $mask = -1 << (32 - (int)$bits);

        return ($ip_long & $mask) === ($subnet_long & $mask);
    }

    /**
     * Verify access to developer console
     *
     * @return array Array with 'allowed' (bool) and 'message' (string)
     */
    public function verify_access() {
        // Check if enabled
        if (!$this->is_enabled()) {
            return array(
                'allowed' => false,
                'message' => 'Developer console is currently disabled.'
            );
        }

        // Check if creator
        if (!$this->is_creator()) {
            $this->log_activity(
                'auth',
                'unauthorized_access_attempt',
                'Unauthorized user attempted to access developer console',
                array('user_id' => get_current_user_id()),
                'warning'
            );

            return array(
                'allowed' => false,
                'message' => 'Access denied. Only the creator can access the developer console.'
            );
        }

        // Check if account is locked
        if ($this->is_locked()) {
            $locked_until = strtotime($this->settings->locked_until);
            $remaining = $locked_until - current_time('timestamp');
            $minutes = ceil($remaining / 60);

            return array(
                'allowed' => false,
                'message' => "Account is locked due to too many failed attempts. Please try again in {$minutes} minutes."
            );
        }

        // Check IP whitelist
        if (!$this->check_ip_whitelist()) {
            $client_ip = $this->get_client_ip();

            $this->log_activity(
                'security',
                'ip_blocked',
                'Access denied due to IP not in whitelist',
                array('ip_address' => $client_ip),
                'warning'
            );

            return array(
                'allowed' => false,
                'message' => 'Access denied. Your IP address is not whitelisted.'
            );
        }

        // All checks passed - reset failed attempts and update last access
        $this->reset_failed_attempts();
        $this->update_last_access();

        return array(
            'allowed' => true,
            'message' => 'Access granted.'
        );
    }

    /**
     * Update last access time and IP
     */
    private function update_last_access() {
        if (!$this->settings) {
            return;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_dev_console_settings';

        $wpdb->update(
            $table_name,
            array(
                'last_access_at' => current_time('mysql'),
                'last_access_ip' => $this->get_client_ip()
            ),
            array('id' => $this->settings->id),
            array('%s', '%s'),
            array('%d')
        );
    }

    /**
     * Log developer activity
     *
     * @param string $category Action category
     * @param string $action_type Action type
     * @param string $description Action description
     * @param array $details Additional details
     * @param string $status Result status
     * @param string $error_message Error message if any
     */
    public function log_activity($category, $action_type, $description, $details = array(), $status = 'info', $error_message = null) {
        global $wpdb;

        $current_user = wp_get_current_user();
        $table_name = $wpdb->prefix . 'cp_dev_console_logs';

        $log_data = array(
            'developer_user_id' => $current_user->ID,
            'developer_email' => $current_user->user_email,
            'action_type' => $action_type,
            'action_category' => $category,
            'action_description' => $description,
            'action_details' => !empty($details) ? json_encode($details) : null,
            'result_status' => $status,
            'error_message' => $error_message,
            'ip_address' => $this->get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : null,
            'request_method' => isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field($_SERVER['REQUEST_METHOD']) : null,
            'request_uri' => isset($_SERVER['REQUEST_URI']) ? sanitize_text_field($_SERVER['REQUEST_URI']) : null,
            'memory_usage' => memory_get_usage(true)
        );

        $wpdb->insert($table_name, $log_data);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Only show menu to creator
        if (!$this->is_creator()) {
            return;
        }

        add_menu_page(
            'Developer Console',
            'Dev Console',
            'manage_options',
            'campaignpress-developer-console',
            array($this, 'render_console_page'),
            'dashicons-code-standards',
            2 // Position at top
        );
    }

    /**
     * Render developer console page
     */
    public function render_console_page() {
        // Verify access
        $access = $this->verify_access();

        if (!$access['allowed']) {
            echo '<div class="wrap">';
            echo '<h1>Developer Console - Access Denied</h1>';
            echo '<div class="notice notice-error"><p>' . esc_html($access['message']) . '</p></div>';
            echo '</div>';
            return;
        }

        // Log successful access
        $this->log_activity('auth', 'console_accessed', 'Developer console accessed successfully', array(), 'success');

        // Include the console page
        require_once __DIR__ . '/admin-page.php';
    }

    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers() {
        // System health
        add_action('wp_ajax_cp_dev_system_health', array($this, 'ajax_system_health'));

        // Database query
        add_action('wp_ajax_cp_dev_execute_query', array($this, 'ajax_execute_query'));

        // Activity logs
        add_action('wp_ajax_cp_dev_get_logs', array($this, 'ajax_get_logs'));

        // API test
        add_action('wp_ajax_cp_dev_test_api', array($this, 'ajax_test_api'));

        // Settings update
        add_action('wp_ajax_cp_dev_update_settings', array($this, 'ajax_update_settings'));

        // Export data
        add_action('wp_ajax_cp_dev_export_data', array($this, 'ajax_export_data'));

        // User management
        add_action('wp_ajax_cp_dev_manage_users', array($this, 'ajax_manage_users'));
    }

    /**
     * Add security headers
     */
    public function add_security_headers() {
        // Only on developer console page
        if (!isset($_GET['page']) || $_GET['page'] !== 'campaignpress-developer-console') {
            return;
        }

        // Prevent caching
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    /**
     * AJAX: Get system health
     */
    public function ajax_system_health() {
        check_ajax_referer('cp_dev_console_nonce', 'nonce');

        $access = $this->verify_access();
        if (!$access['allowed']) {
            wp_send_json_error(array('message' => $access['message']));
        }

        if (!class_exists('CampaignPress_Developer_System_Health')) {
            require_once __DIR__ . '/class-system-health.php';
        }
        $health = new CampaignPress_Developer_System_Health();

        $data = $health->get_all_health_data();

        $this->log_activity('system', 'health_check', 'System health check performed', array(), 'info');

        wp_send_json_success($data);
    }

    /**
     * AJAX: Execute database query
     */
    public function ajax_execute_query() {
        check_ajax_referer('cp_dev_console_nonce', 'nonce');

        $access = $this->verify_access();
        if (!$access['allowed']) {
            wp_send_json_error(array('message' => $access['message']));
        }

        if (!class_exists('CampaignPress_Developer_Database_Manager')) {
            require_once __DIR__ . '/class-database-manager.php';
        }
        $db_manager = new CampaignPress_Developer_Database_Manager();

        $result = $db_manager->execute_query($_POST);

        wp_send_json($result);
    }

    /**
     * AJAX: Get activity logs
     */
    public function ajax_get_logs() {
        check_ajax_referer('cp_dev_console_nonce', 'nonce');

        $access = $this->verify_access();
        if (!$access['allowed']) {
            wp_send_json_error(array('message' => $access['message']));
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_dev_console_logs';

        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 100;
        $offset = isset($_POST['offset']) ? absint($_POST['offset']) : 0;
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

        $where = '';
        if (!empty($category) && $category !== 'all') {
            $where = $wpdb->prepare(' WHERE action_category = %s', $category);
        }

        $logs = $wpdb->get_results(
            "SELECT * FROM $table_name $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset"
        );

        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where");

        wp_send_json_success(array(
            'logs' => $logs,
            'total' => $total
        ));
    }

    /**
     * AJAX: Test API endpoint
     */
    public function ajax_test_api() {
        check_ajax_referer('cp_dev_console_nonce', 'nonce');

        $access = $this->verify_access();
        if (!$access['allowed']) {
            wp_send_json_error(array('message' => $access['message']));
        }

        if (!class_exists('CampaignPress_Developer_API_Tester')) {
            require_once __DIR__ . '/class-api-tester.php';
        }
        $api_tester = new CampaignPress_Developer_API_Tester();

        $result = $api_tester->test_endpoint($_POST);

        wp_send_json($result);
    }

    /**
     * AJAX: Update settings
     */
    public function ajax_update_settings() {
        check_ajax_referer('cp_dev_console_nonce', 'nonce');

        $access = $this->verify_access();
        if (!$access['allowed']) {
            wp_send_json_error(array('message' => $access['message']));
        }

        if (!$this->settings) {
            wp_send_json_error(array('message' => 'Settings not initialized'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'cp_dev_console_settings';

        $setting_key = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '';
        $setting_value = isset($_POST['value']) ? $_POST['value'] : '';

        $allowed_settings = array('ip_whitelist', 'security_level', 'session_timeout', 'enabled');

        if (!in_array($setting_key, $allowed_settings)) {
            wp_send_json_error(array('message' => 'Invalid setting key'));
        }

        // Sanitize based on key
        switch ($setting_key) {
            case 'ip_whitelist':
                if (is_array($setting_value)) {
                    $setting_value = json_encode(array_map('sanitize_text_field', $setting_value));
                }
                break;
            case 'security_level':
                $setting_value = in_array($setting_value, array('standard', 'high', 'maximum')) ? $setting_value : 'high';
                break;
            case 'session_timeout':
                $setting_value = absint($setting_value);
                break;
            case 'enabled':
                $setting_value = (int)(bool)$setting_value;
                break;
        }

        $wpdb->update(
            $table_name,
            array($setting_key => $setting_value),
            array('id' => $this->settings->id)
        );

        $this->log_activity('settings', 'settings_updated', "Setting updated: {$setting_key}", array(
            'key' => $setting_key,
            'value' => $setting_value
        ), 'success');

        $this->load_settings();

        wp_send_json_success(array('message' => 'Setting updated successfully'));
    }

    /**
     * AJAX: Export data
     */
    public function ajax_export_data() {
        check_ajax_referer('cp_dev_console_nonce', 'nonce');

        $access = $this->verify_access();
        if (!$access['allowed']) {
            wp_send_json_error(array('message' => $access['message']));
        }

        if (!class_exists('CampaignPress_Developer_Data_Exporter')) {
            require_once __DIR__ . '/class-data-exporter.php';
        }
        $exporter = new CampaignPress_Developer_Data_Exporter();

        $result = $exporter->export($_POST);

        wp_send_json($result);
    }

    /**
     * AJAX: Manage users
     */
    public function ajax_manage_users() {
        check_ajax_referer('cp_dev_console_nonce', 'nonce');

        $access = $this->verify_access();
        if (!$access['allowed']) {
            wp_send_json_error(array('message' => $access['message']));
        }

        $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';

        switch ($action) {
            case 'list':
                $users = get_users(array('number' => 100));
                $user_data = array();

                foreach ($users as $user) {
                    $user_data[] = array(
                        'ID' => $user->ID,
                        'login' => $user->user_login,
                        'email' => $user->user_email,
                        'roles' => $user->roles,
                        'registered' => $user->user_registered
                    );
                }

                wp_send_json_success(array('users' => $user_data));
                break;

            default:
                wp_send_json_error(array('message' => 'Invalid action'));
        }
    }
}
