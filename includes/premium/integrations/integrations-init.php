<?php
/**
 * CampaignPress Email & SMS Integrations Initialization
 *
 * Comprehensive initialization for email and SMS campaign automation integrations.
 * Manages platform connections, webhook receivers, and workflow automation.
 *
 * @package CampaignPress
 * @subpackage Premium/Integrations
 * @since 2.0.0
 * @version 2.0.0
 *
 * Supported Platforms:
 * - Email: Mailchimp, Action Network, Constant Contact, SendGrid, MailerLite, SMTP
 * - SMS: Twilio, Hustle, CallHub, RumbleUp
 *
 * Security Features:
 * - Encrypted API key storage using WordPress salts
 * - Webhook signature verification
 * - Rate limiting per platform
 * - CSRF protection via WordPress nonces
 * - Capability checks for all admin functions
 *
 * Compliance:
 * - TCPA compliance for SMS
 * - CAN-SPAM compliance for email
 * - Opt-in/opt-out management
 * - Consent tracking
 *
 * @author CampaignPress Team
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Integrations Manager Class
 *
 * Coordinates email, SMS, and automation workflow integrations.
 *
 * @since 2.0.0
 */
class CampaignPress_Integrations {

    /**
     * Version number
     *
     * @var string
     */
    const VERSION = '2.0.0';

    /**
     * Encryption method for API keys
     *
     * @var string
     */
    const ENCRYPTION_METHOD = 'AES-256-CBC';

    /**
     * Singleton instance
     *
     * @var CampaignPress_Integrations
     */
    private static $instance = null;

    /**
     * Email integrations handler
     *
     * @var CampaignPress_Email_Integrations
     */
    private $email_integrations;

    /**
     * SMS integrations handler
     *
     * @var CampaignPress_SMS_Integrations
     */
    private $sms_integrations;

    /**
     * Automation workflows handler
     *
     * @var CampaignPress_Automation_Workflows
     */
    private $automation_workflows;

    /**
     * Testing mode flag
     *
     * @var bool
     */
    private $testing_mode = false;

    /**
     * Rate limiting cache
     *
     * @var array
     */
    private $rate_limits = array();

    /**
     * Get singleton instance
     *
     * @return CampaignPress_Integrations
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - Initialize integrations system
     *
     * @since 2.0.0
     */
    private function __construct() {
        // Detect environment
        $is_production = $this->is_production_environment();

        // Set testing mode with safety checks
        $testing_mode = get_option('campaignpress_integrations_testing_mode', false);

        if ($is_production && $testing_mode) {
            // Disable testing mode in production
            $testing_mode = false;
            update_option('campaignpress_integrations_testing_mode', false);

            // Log security violation
            error_log('CampaignPress Security: Testing mode automatically disabled in production environment');

            // Notify administrators
            $this->notify_admin_of_testing_mode_violation();
        }

        $this->testing_mode = $testing_mode;

        // Load dependencies
        $this->load_dependencies();

        // Initialize integration handlers
        $this->init_handlers();

        // Register WordPress hooks
        $this->init_hooks();

        // Schedule cleanup tasks
        $this->schedule_cleanup_tasks();

        // Log initialization
        $this->log_event('integrations_initialized', array(
            'version' => self::VERSION,
            'testing_mode' => $this->testing_mode,
            'environment' => $is_production ? 'production' : 'development'
        ));
    }

    /**
     * Load required files
     *
     * @since 2.0.0
     */
    private function load_dependencies() {
        $integrations_dir = dirname(__FILE__);

        // Load email integrations
        require_once $integrations_dir . '/class-email-integrations.php';

        // Load SMS integrations
        require_once $integrations_dir . '/class-sms-integrations.php';

        // Load automation workflows
        require_once $integrations_dir . '/class-automation-workflows.php';
    }

    /**
     * Initialize integration handlers
     *
     * @since 2.0.0
     */
    private function init_handlers() {
        // Initialize email integrations
        $this->email_integrations = CampaignPress_Email_Integrations::get_instance();

        // Initialize SMS integrations
        $this->sms_integrations = CampaignPress_SMS_Integrations::get_instance();

        // Initialize automation workflows
        $this->automation_workflows = CampaignPress_Automation_Workflows::get_instance();
    }

    /**
     * Initialize WordPress hooks
     *
     * @since 2.0.0
     */
    private function init_hooks() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'), 30);

        // Admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // AJAX handlers
        add_action('wp_ajax_campaignpress_test_integration', array($this, 'ajax_test_integration'));
        add_action('wp_ajax_campaignpress_save_integration', array($this, 'ajax_save_integration'));
        add_action('wp_ajax_campaignpress_delete_integration', array($this, 'ajax_delete_integration'));
        add_action('wp_ajax_campaignpress_sync_integration', array($this, 'ajax_sync_integration'));

        // Webhook receivers (no authentication required)
        add_action('wp_ajax_nopriv_campaignpress_email_webhook', array($this, 'handle_email_webhook'));
        add_action('wp_ajax_campaignpress_email_webhook', array($this, 'handle_email_webhook'));
        add_action('wp_ajax_nopriv_campaignpress_sms_webhook', array($this, 'handle_sms_webhook'));
        add_action('wp_ajax_campaignpress_sms_webhook', array($this, 'handle_sms_webhook'));

        // Cron tasks
        add_action('campaignpress_sync_integrations', array($this, 'sync_all_integrations'));
        add_action('campaignpress_cleanup_integration_logs', array($this, 'cleanup_old_logs'));
    }

    /**
     * Add admin menu pages
     *
     * @since 2.0.0
     */
    public function add_admin_menu() {
        add_submenu_page(
            'campaignpress',
            __('Integrations', 'campaignpress'),
            __('Integrations', 'campaignpress'),
            'manage_options',
            'campaignpress-integrations',
            array($this, 'render_integrations_page')
        );

        add_submenu_page(
            'campaignpress',
            __('Automation Workflows', 'campaignpress'),
            __('Automation', 'campaignpress'),
            'manage_options',
            'campaignpress-automation',
            array($this, 'render_automation_page')
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     * @since 2.0.0
     */
    public function enqueue_admin_assets($hook) {
        // Only load on integration pages
        if (!in_array($hook, array('campaignpress_page_campaignpress-integrations', 'campaignpress_page_campaignpress-automation'))) {
            return;
        }

        // Enqueue styles
        wp_enqueue_style(
            'campaignpress-integrations',
            get_template_directory_uri() . '/assets/css/admin-integrations.css',
            array(),
            self::VERSION
        );

        // Enqueue scripts
        wp_enqueue_script(
            'campaignpress-integrations',
            get_template_directory_uri() . '/assets/js/admin-integrations.js',
            array('jquery', 'jquery-ui-sortable'),
            self::VERSION,
            true
        );

        // Localize script
        wp_localize_script('campaignpress-integrations', 'campaignpressIntegrations', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('campaignpress_integrations'),
            'webhookUrl' => admin_url('admin-ajax.php'),
            'testingMode' => $this->testing_mode,
            'strings' => array(
                'testSuccess' => __('Connection successful!', 'campaignpress'),
                'testFailed' => __('Connection failed. Please check your credentials.', 'campaignpress'),
                'saveSuccess' => __('Integration saved successfully.', 'campaignpress'),
                'saveFailed' => __('Failed to save integration.', 'campaignpress'),
                'deleteConfirm' => __('Are you sure you want to delete this integration?', 'campaignpress'),
                'syncStarted' => __('Synchronization started...', 'campaignpress'),
                'syncComplete' => __('Synchronization complete!', 'campaignpress'),
            )
        ));
    }

    /**
     * Render integrations management page
     *
     * @since 2.0.0
     */
    public function render_integrations_page() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'campaignpress'));
        }

        // Get active integrations
        $email_integrations = $this->email_integrations->get_all_integrations();
        $sms_integrations = $this->sms_integrations->get_all_integrations();

        // Get available platforms
        $email_platforms = $this->email_integrations->get_supported_platforms();
        $sms_platforms = $this->sms_integrations->get_supported_platforms();

        include dirname(__FILE__) . '/views/admin-integrations-page.php';
    }

    /**
     * Render automation workflows page
     *
     * @since 2.0.0
     */
    public function render_automation_page() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'campaignpress'));
        }

        // Get workflows
        $workflows = $this->automation_workflows->get_all_workflows();

        include dirname(__FILE__) . '/views/admin-automation-page.php';
    }

    /**
     * AJAX: Test integration connection
     *
     * @since 2.0.0
     */
    public function ajax_test_integration() {
        // Verify nonce
        check_ajax_referer('campaignpress_integrations', 'nonce');

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'campaignpress')));
        }

        // Get parameters
        $type = sanitize_text_field($_POST['type'] ?? '');
        $platform = sanitize_text_field($_POST['platform'] ?? '');
        $credentials = $_POST['credentials'] ?? array();

        // Sanitize credentials
        $credentials = $this->sanitize_credentials($credentials);

        // Test connection
        $result = false;
        if ($type === 'email') {
            $result = $this->email_integrations->test_connection($platform, $credentials);
        } elseif ($type === 'sms') {
            $result = $this->sms_integrations->test_connection($platform, $credentials);
        }

        if ($result) {
            wp_send_json_success(array('message' => __('Connection successful!', 'campaignpress')));
        } else {
            wp_send_json_error(array('message' => __('Connection failed.', 'campaignpress')));
        }
    }

    /**
     * AJAX: Save integration
     *
     * @since 2.0.0
     */
    public function ajax_save_integration() {
        // Verify nonce
        check_ajax_referer('campaignpress_integrations', 'nonce');

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'campaignpress')));
        }

        // Get parameters
        $integration_id = sanitize_text_field($_POST['integration_id'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? '');
        $platform = sanitize_text_field($_POST['platform'] ?? '');
        $name = sanitize_text_field($_POST['name'] ?? '');
        $credentials = $_POST['credentials'] ?? array();
        $settings = $_POST['settings'] ?? array();

        // Sanitize data
        $credentials = $this->sanitize_credentials($credentials);
        $settings = $this->sanitize_settings($settings);

        // Save integration
        $result = false;
        if ($type === 'email') {
            $result = $this->email_integrations->save_integration($integration_id, $platform, $name, $credentials, $settings);
        } elseif ($type === 'sms') {
            $result = $this->sms_integrations->save_integration($integration_id, $platform, $name, $credentials, $settings);
        }

        if ($result) {
            wp_send_json_success(array('message' => __('Integration saved successfully.', 'campaignpress')));
        } else {
            wp_send_json_error(array('message' => __('Failed to save integration.', 'campaignpress')));
        }
    }

    /**
     * AJAX: Delete integration
     *
     * @since 2.0.0
     */
    public function ajax_delete_integration() {
        // Verify nonce
        check_ajax_referer('campaignpress_integrations', 'nonce');

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'campaignpress')));
        }

        // Get parameters
        $integration_id = sanitize_text_field($_POST['integration_id'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? '');

        // Delete integration
        $result = false;
        if ($type === 'email') {
            $result = $this->email_integrations->delete_integration($integration_id);
        } elseif ($type === 'sms') {
            $result = $this->sms_integrations->delete_integration($integration_id);
        }

        if ($result) {
            wp_send_json_success(array('message' => __('Integration deleted successfully.', 'campaignpress')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete integration.', 'campaignpress')));
        }
    }

    /**
     * AJAX: Sync integration
     *
     * @since 2.0.0
     */
    public function ajax_sync_integration() {
        // Verify nonce
        check_ajax_referer('campaignpress_integrations', 'nonce');

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'campaignpress')));
        }

        // Get parameters
        $integration_id = sanitize_text_field($_POST['integration_id'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? '');

        // Sync integration
        $result = false;
        if ($type === 'email') {
            $result = $this->email_integrations->sync_integration($integration_id);
        } elseif ($type === 'sms') {
            $result = $this->sms_integrations->sync_integration($integration_id);
        }

        if ($result) {
            wp_send_json_success(array('message' => __('Synchronization complete.', 'campaignpress')));
        } else {
            wp_send_json_error(array('message' => __('Synchronization failed.', 'campaignpress')));
        }
    }

    /**
     * Handle email webhook
     *
     * @since 2.0.0
     */
    public function handle_email_webhook() {
        // Check IP whitelist first
        if (!$this->is_webhook_ip_allowed()) {
            $this->log_event('webhook_ip_blocked', array(
                'ip' => $this->get_client_ip(),
                'type' => 'email'
            ));
            wp_send_json_error(array('message' => 'IP not allowed'), 403);
            return;
        }

        // Check rate limiting
        if (!$this->check_webhook_rate_limit('email')) {
            $this->log_event('webhook_rate_limited', array(
                'ip' => $this->get_client_ip(),
                'type' => 'email'
            ));
            wp_send_json_error(array('message' => 'Rate limit exceeded'), 429);
            return;
        }

        // Get platform from request
        $platform = sanitize_text_field($_GET['platform'] ?? '');

        if (empty($platform)) {
            wp_send_json_error(array('message' => 'Platform not specified'));
            return;
        }

        // Log webhook received
        $this->log_event('webhook_received', array(
            'type' => 'email',
            'platform' => $platform,
            'ip' => $this->get_client_ip()
        ));

        // Delegate to email integrations handler
        $this->email_integrations->handle_webhook($platform);
    }

    /**
     * Handle SMS webhook
     *
     * @since 2.0.0
     */
    public function handle_sms_webhook() {
        // Check IP whitelist first
        if (!$this->is_webhook_ip_allowed()) {
            $this->log_event('webhook_ip_blocked', array(
                'ip' => $this->get_client_ip(),
                'type' => 'sms'
            ));
            wp_send_json_error(array('message' => 'IP not allowed'), 403);
            return;
        }

        // Check rate limiting
        if (!$this->check_webhook_rate_limit('sms')) {
            $this->log_event('webhook_rate_limited', array(
                'ip' => $this->get_client_ip(),
                'type' => 'sms'
            ));
            wp_send_json_error(array('message' => 'Rate limit exceeded'), 429);
            return;
        }

        // Get platform from request
        $platform = sanitize_text_field($_GET['platform'] ?? '');

        if (empty($platform)) {
            wp_send_json_error(array('message' => 'Platform not specified'));
            return;
        }

        // Log webhook received
        $this->log_event('webhook_received', array(
            'type' => 'sms',
            'platform' => $platform,
            'ip' => $this->get_client_ip()
        ));

        // Delegate to SMS integrations handler
        $this->sms_integrations->handle_webhook($platform);
    }

    /**
     * Check if webhook request IP is allowed
     *
     * @return bool
     * @since 2.0.0
     */
    private function is_webhook_ip_allowed() {
        // In testing mode, allow localhost and private IPs
        if ($this->testing_mode && !$this->is_production_environment()) {
            $ip = $this->get_client_ip();
            $allowed_test_ips = array('127.0.0.1', '::1', 'localhost');
            if (in_array($ip, $allowed_test_ips, true) || $this->is_private_ip($ip)) {
                return true;
            }
        }

        // Get IP whitelist from options
        $allowed_ips = get_option('campaignpress_webhook_allowed_ips', array());

        // If no whitelist configured, deny all (secure by default)
        if (empty($allowed_ips) || !is_array($allowed_ips)) {
            return false;
        }

        $request_ip = $this->get_client_ip();
        return in_array($request_ip, $allowed_ips, true);
    }

    /**
     * Check webhook rate limit
     *
     * @param string $type Webhook type (email/sms)
     * @return bool
     * @since 2.0.0
     */
    private function check_webhook_rate_limit($type) {
        $ip = $this->get_client_ip();
        $key = 'webhook_' . md5($type . $ip);
        $count = get_transient($key);

        if ($count === false) {
            set_transient($key, 1, MINUTE_IN_SECONDS);
            return true;
        }

        // Max 10 webhooks per minute per IP
        if ($count >= 10) {
            return false;
        }

        set_transient($key, $count + 1, MINUTE_IN_SECONDS);
        return true;
    }

    /**
     * Get client IP address
     *
     * @return string
     * @since 2.0.0
     */
    private function get_client_ip() {
        $ip = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        return sanitize_text_field($ip);
    }

    /**
     * Check if IP is a private IP address
     *
     * @param string $ip IP address to check
     * @return bool
     * @since 2.0.0
     */
    private function is_private_ip($ip) {
        // Convert to long for comparison
        $long_ip = ip2long($ip);

        if ($long_ip === false) {
            return false;
        }

        // Check private IP ranges
        $private_ranges = array(
            array('10.0.0.0', '10.255.255.255'),       // 10.0.0.0/8
            array('172.16.0.0', '172.31.255.255'),     // 172.16.0.0/12
            array('192.168.0.0', '192.168.255.255'),   // 192.168.0.0/16
            array('127.0.0.0', '127.255.255.255'),     // 127.0.0.0/8
            array('169.254.0.0', '169.254.255.255'),   // 169.254.0.0/16
        );

        foreach ($private_ranges as $range) {
            $min = ip2long($range[0]);
            $max = ip2long($range[1]);
            if ($long_ip >= $min && $long_ip <= $max) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if running in production environment
     *
     * @return bool
     * @since 2.0.0
     */
    private function is_production_environment() {
        // Check for production environment constant
        if (defined('WP_ENV') && WP_ENV === 'production') {
            return true;
        }

        // Check for WP environment type
        if (function_exists('wp_get_environment_type') && wp_get_environment_type() === 'production') {
            return true;
        }

        // Check domain
        $domain = parse_url(home_url(), PHP_URL_HOST);
        $production_domains = apply_filters('campaignpress_production_domains', array());

        if (!empty($production_domains) && in_array($domain, $production_domains, true)) {
            return true;
        }

        // Check if localhost
        $localhost_patterns = array('localhost', '127.0.0.1', '.local', '.test', '.dev');
        foreach ($localhost_patterns as $pattern) {
            if (strpos($domain, $pattern) !== false) {
                return false;
            }
        }

        // Default to production if not explicitly development
        return true;
    }

    /**
     * Sync all integrations
     *
     * Scheduled task to sync all active integrations.
     *
     * @since 2.0.0
     */
    public function sync_all_integrations() {
        // Sync email integrations
        $this->email_integrations->sync_all();

        // Sync SMS integrations
        $this->sms_integrations->sync_all();

        // Log completion
        $this->log_event('sync_all_complete', array(
            'timestamp' => current_time('mysql')
        ));
    }

    /**
     * Cleanup old logs
     *
     * Removes logs older than 90 days.
     *
     * @since 2.0.0
     */
    public function cleanup_old_logs() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'campaignpress_integration_logs';
        $days_to_keep = apply_filters('campaignpress_integration_logs_retention', 90);
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days_to_keep} days"));

        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table_name} WHERE created_at < %s",
            $cutoff_date
        ));

        // Log cleanup
        $this->log_event('logs_cleanup', array(
            'deleted_count' => $deleted,
            'cutoff_date' => $cutoff_date
        ));
    }

    /**
     * Schedule cleanup tasks
     *
     * @since 2.0.0
     */
    private function schedule_cleanup_tasks() {
        // Schedule daily sync
        if (!wp_next_scheduled('campaignpress_sync_integrations')) {
            wp_schedule_event(time(), 'daily', 'campaignpress_sync_integrations');
        }

        // Schedule weekly log cleanup
        if (!wp_next_scheduled('campaignpress_cleanup_integration_logs')) {
            wp_schedule_event(time(), 'weekly', 'campaignpress_cleanup_integration_logs');
        }
    }

    /**
     * Encrypt sensitive data with HMAC integrity verification
     *
     * @param string $data Data to encrypt
     * @return string Encrypted data with HMAC
     * @since 2.0.0
     */
    public function encrypt($data) {
        if (empty($data)) {
            return '';
        }

        // Generate encryption key from WordPress salts
        $key = hash('sha256', AUTH_KEY . SECURE_AUTH_KEY, true);

        // Generate initialization vector
        $iv_length = openssl_cipher_iv_length(self::ENCRYPTION_METHOD);
        $iv = openssl_random_pseudo_bytes($iv_length);

        // Encrypt data
        $encrypted = openssl_encrypt($data, self::ENCRYPTION_METHOD, $key, OPENSSL_RAW_DATA, $iv);

        // Generate HMAC for integrity verification
        $hmac = hash_hmac('sha256', $iv . $encrypted, $key, true);

        // Combine IV, encrypted data, and HMAC
        return base64_encode($iv . $encrypted . $hmac);
    }

    /**
     * Decrypt sensitive data with HMAC integrity verification
     *
     * @param string $data Encrypted data with HMAC
     * @return string|false Decrypted data or false if tampered
     * @since 2.0.0
     */
    public function decrypt($data) {
        if (empty($data)) {
            return '';
        }

        // Generate decryption key from WordPress salts
        $key = hash('sha256', AUTH_KEY . SECURE_AUTH_KEY, true);

        // Decode data
        $decoded = base64_decode($data);
        if ($decoded === false) {
            return false;
        }

        // Extract IV, encrypted data, and HMAC
        $iv_length = openssl_cipher_iv_length(self::ENCRYPTION_METHOD);
        $hmac_length = 32; // SHA-256 HMAC is 32 bytes

        if (strlen($decoded) < ($iv_length + $hmac_length)) {
            return false; // Data is too short
        }

        $iv = substr($decoded, 0, $iv_length);
        $hmac_stored = substr($decoded, -$hmac_length);
        $encrypted = substr($decoded, $iv_length, -$hmac_length);

        // Verify HMAC first (prevents padding oracle attacks)
        $hmac_calculated = hash_hmac('sha256', $iv . $encrypted, $key, true);
        if (!hash_equals($hmac_stored, $hmac_calculated)) {
            // Data has been tampered with
            error_log('CampaignPress Security: Encrypted data integrity check failed - possible tampering detected');
            return false;
        }

        // Decrypt data
        $decrypted = openssl_decrypt($encrypted, self::ENCRYPTION_METHOD, $key, OPENSSL_RAW_DATA, $iv);

        return $decrypted;
    }

    /**
     * Check rate limit
     *
     * @param string $key Rate limit key
     * @param int $limit Maximum requests
     * @param int $period Time period in seconds
     * @return bool True if within limit
     * @since 2.0.0
     */
    public function check_rate_limit($key, $limit = 100, $period = 3600) {
        $current_time = time();
        $cache_key = 'rate_limit_' . md5($key);

        // Get cached rate limit data
        $rate_data = wp_cache_get($cache_key, 'campaignpress_integrations');

        if ($rate_data === false) {
            // Initialize rate limit
            $rate_data = array(
                'count' => 1,
                'reset_time' => $current_time + $period
            );
            wp_cache_set($cache_key, $rate_data, 'campaignpress_integrations', $period);
            return true;
        }

        // Check if period has expired
        if ($current_time > $rate_data['reset_time']) {
            // Reset counter
            $rate_data = array(
                'count' => 1,
                'reset_time' => $current_time + $period
            );
            wp_cache_set($cache_key, $rate_data, 'campaignpress_integrations', $period);
            return true;
        }

        // Check if limit exceeded
        if ($rate_data['count'] >= $limit) {
            return false;
        }

        // Increment counter
        $rate_data['count']++;
        wp_cache_set($cache_key, $rate_data, 'campaignpress_integrations', $period);

        return true;
    }

    /**
     * Log integration event
     *
     * @param string $event_type Event type
     * @param array $data Event data
     * @return bool Success status
     * @since 2.0.0
     */
    public function log_event($event_type, $data = array()) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'campaignpress_integration_logs';

        return $wpdb->insert(
            $table_name,
            array(
                'event_type' => $event_type,
                'event_data' => wp_json_encode($data),
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s')
        );
    }

    /**
     * Sanitize credentials array
     *
     * @param array $credentials Credentials to sanitize
     * @return array Sanitized credentials
     * @since 2.0.0
     */
    private function sanitize_credentials($credentials) {
        $sanitized = array();

        foreach ($credentials as $key => $value) {
            $sanitized[sanitize_key($key)] = sanitize_text_field($value);
        }

        return $sanitized;
    }

    /**
     * Sanitize settings array
     *
     * @param array $settings Settings to sanitize
     * @return array Sanitized settings
     * @since 2.0.0
     */
    private function sanitize_settings($settings) {
        $sanitized = array();

        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                $sanitized[sanitize_key($key)] = $this->sanitize_settings($value);
            } elseif (is_bool($value)) {
                $sanitized[sanitize_key($key)] = (bool) $value;
            } elseif (is_numeric($value)) {
                $sanitized[sanitize_key($key)] = is_float($value) ? floatval($value) : intval($value);
            } else {
                $sanitized[sanitize_key($key)] = sanitize_text_field($value);
            }
        }

        return $sanitized;
    }

    /**
     * Get email integrations handler
     *
     * @return CampaignPress_Email_Integrations
     * @since 2.0.0
     */
    public function get_email_integrations() {
        return $this->email_integrations;
    }

    /**
     * Get SMS integrations handler
     *
     * @return CampaignPress_SMS_Integrations
     * @since 2.0.0
     */
    public function get_sms_integrations() {
        return $this->sms_integrations;
    }

    /**
     * Get automation workflows handler
     *
     * @return CampaignPress_Automation_Workflows
     * @since 2.0.0
     */
    public function get_automation_workflows() {
        return $this->automation_workflows;
    }

    /**
     * Is testing mode enabled
     *
     * @return bool
     * @since 2.0.0
     */
    public function is_testing_mode() {
        return $this->testing_mode;
    }

    /**
     * Get webhook URL for platform
     *
     * @param string $type Integration type (email/sms)
     * @param string $platform Platform name
     * @return string Webhook URL
     * @since 2.0.0
     */
    public function get_webhook_url($type, $platform) {
        return add_query_arg(
            array(
                'action' => 'campaignpress_' . $type . '_webhook',
                'platform' => $platform
            ),
            admin_url('admin-ajax.php')
        );
    }

    /**
     * Notify administrators of testing mode security violation
     *
     * @since 2.0.0
     */
    private function notify_admin_of_testing_mode_violation() {
        $admin_email = get_option('admin_email');
        $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);

        $subject = sprintf(
            __('[Security Alert] Testing mode disabled on %s', 'campaignpress'),
            $site_name
        );

        $message = sprintf(
            __("CampaignPress detected that testing mode was enabled in a production environment and has automatically disabled it.\n\nSite: %s\nTime: %s\n\nTesting mode bypasses security controls and should NEVER be enabled in production.\n\nNo action is required - this is just a notification that the system automatically protected itself.\n\nIf you believe this is an error, please check your environment configuration.", 'campaignpress'),
            home_url(),
            current_time('mysql')
        );

        wp_mail($admin_email, $subject, $message, array('Content-Type: text/plain; charset=UTF-8'));
    }
}

/**
 * Initialize integrations system
 *
 * @return CampaignPress_Integrations
 */
function campaignpress_integrations() {
    return CampaignPress_Integrations::get_instance();
}

// Initialize
campaignpress_integrations();
