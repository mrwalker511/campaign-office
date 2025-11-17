<?php
/**
 * CampaignPress FEC Compliance System Initialization
 *
 * Main initialization file for the FEC (Federal Election Commission) compliance module.
 * Handles class loading, database initialization, hooks, and integration with WordPress
 * and the main CampaignPress plugin.
 *
 * This compliance system provides:
 * - FEC contribution limit tracking and enforcement
 * - Prohibited source detection (foreign nationals, federal contractors, etc.)
 * - Automatic contribution aggregation by donor
 * - Quarterly and special FEC report generation
 * - 48-hour notice reports for large contributions
 * - FEC Form 3 export (CSV compatible with FEC filing software)
 * - Complete audit trail for all financial transactions
 * - Donor profile management with occupation/employer tracking
 * - In-kind contribution tracking
 * - Refund and adjustment processing
 * - Multi-committee type support (candidate, PAC, party)
 *
 * FEC Compliance Standards:
 * - 52 U.S.C. §30116 - Contribution limits
 * - 52 U.S.C. §30121 - Prohibition on foreign nationals
 * - 52 U.S.C. §30118 - Prohibition on corporate/union contributions
 * - 11 CFR §104 - Reports by political committees
 * - 11 CFR §110 - Contribution and expenditure limitations
 *
 * @package CampaignPress
 * @subpackage Compliance
 * @since 1.0.0
 * @version 1.0.0
 *
 * Security Features:
 * - Nonce verification for all admin actions
 * - Capability checks for financial operations
 * - Input sanitization and validation
 * - SQL injection prevention via prepared statements
 * - XSS protection via output escaping
 * - CSRF protection via WordPress nonces
 * - Encryption for sensitive financial data
 *
 * @author CampaignPress Team
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main CampaignPress FEC Compliance Class
 *
 * Handles all FEC compliance functionality including contribution tracking,
 * limit enforcement, reporting, and audit trails.
 *
 * @since 1.0.0
 */
class CampaignPress_FEC_Compliance {

    /**
     * Compliance module version
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * FEC database schema version
     *
     * @var string
     */
    const DB_VERSION = '1.0.0';

    /**
     * Singleton instance
     *
     * @var CampaignPress_FEC_Compliance
     */
    private static $instance = null;

    /**
     * FEC Contributions instance
     *
     * @var CampaignPress_FEC_Contributions
     */
    public $contributions;

    /**
     * FEC Reports instance
     *
     * @var CampaignPress_FEC_Reports
     */
    public $reports;

    /**
     * FEC Audit Trail instance
     *
     * @var CampaignPress_FEC_Audit_Trail
     */
    public $audit_trail;

    /**
     * FEC Donors instance
     *
     * @var CampaignPress_FEC_Donors
     */
    public $donors;

    /**
     * Committee information
     *
     * @var array
     */
    private $committee_info = array();

    /**
     * Get singleton instance
     *
     * @since 1.0.0
     * @return CampaignPress_FEC_Compliance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - Initialize compliance system
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->define_constants();
        $this->load_dependencies();
        $this->init_hooks();
        $this->load_committee_info();
    }

    /**
     * Define compliance constants
     *
     * @since 1.0.0
     */
    private function define_constants() {
        if (!defined('CP_FEC_VERSION')) {
            define('CP_FEC_VERSION', self::VERSION);
        }

        if (!defined('CP_FEC_DB_VERSION')) {
            define('CP_FEC_DB_VERSION', self::DB_VERSION);
        }

        if (!defined('CP_FEC_PATH')) {
            define('CP_FEC_PATH', dirname(__FILE__) . '/');
        }

        // FEC contribution limits (2024 cycle - updated biennially)
        if (!defined('CP_FEC_INDIVIDUAL_LIMIT_CANDIDATE')) {
            define('CP_FEC_INDIVIDUAL_LIMIT_CANDIDATE', 3300); // Per election
        }

        if (!defined('CP_FEC_INDIVIDUAL_LIMIT_PAC')) {
            define('CP_FEC_INDIVIDUAL_LIMIT_PAC', 5000); // Per year
        }

        if (!defined('CP_FEC_INDIVIDUAL_LIMIT_PARTY')) {
            define('CP_FEC_INDIVIDUAL_LIMIT_PARTY', 10000); // Per year
        }

        if (!defined('CP_FEC_PAC_LIMIT_CANDIDATE')) {
            define('CP_FEC_PAC_LIMIT_CANDIDATE', 5000); // Per election
        }

        if (!defined('CP_FEC_ITEMIZATION_THRESHOLD')) {
            define('CP_FEC_ITEMIZATION_THRESHOLD', 200); // Contributions over $200 must be itemized
        }

        if (!defined('CP_FEC_48HOUR_THRESHOLD')) {
            define('CP_FEC_48HOUR_THRESHOLD', 1000); // Contributions over $1,000 require 48-hour notice
        }
    }

    /**
     * Load required dependencies
     *
     * @since 1.0.0
     */
    private function load_dependencies() {
        // Load class files
        require_once CP_FEC_PATH . 'class-fec-donors.php';
        require_once CP_FEC_PATH . 'class-fec-contributions.php';
        require_once CP_FEC_PATH . 'class-fec-reports.php';
        require_once CP_FEC_PATH . 'class-fec-audit-trail.php';

        // Initialize core classes (order matters - donors first)
        $this->donors = new CampaignPress_FEC_Donors();
        $this->audit_trail = new CampaignPress_FEC_Audit_Trail();
        $this->contributions = new CampaignPress_FEC_Contributions($this->donors, $this->audit_trail);
        $this->reports = new CampaignPress_FEC_Reports($this->contributions, $this->donors, $this->audit_trail);
    }

    /**
     * Initialize WordPress hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Activation/Installation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Initialize on plugins loaded
        add_action('plugins_loaded', array($this, 'init'));

        // Admin initialization
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'add_admin_menu'), 20);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // AJAX handlers for compliance operations
        add_action('wp_ajax_cp_fec_validate_contribution', array($this, 'ajax_validate_contribution'));
        add_action('wp_ajax_cp_fec_record_contribution', array($this, 'ajax_record_contribution'));
        add_action('wp_ajax_cp_fec_generate_report', array($this, 'ajax_generate_report'));
        add_action('wp_ajax_cp_fec_export_fec_form', array($this, 'ajax_export_fec_form'));
        add_action('wp_ajax_cp_fec_check_donor_limits', array($this, 'ajax_check_donor_limits'));

        // Scheduled tasks
        add_action('cp_fec_daily_compliance_check', array($this, 'daily_compliance_check'));
        add_action('cp_fec_quarterly_report_reminder', array($this, 'quarterly_report_reminder'));
        add_action('cp_fec_audit_cleanup', array($this, 'audit_cleanup'));

        // Schedule cron jobs
        if (!wp_next_scheduled('cp_fec_daily_compliance_check')) {
            wp_schedule_event(time(), 'daily', 'cp_fec_daily_compliance_check');
        }

        if (!wp_next_scheduled('cp_fec_audit_cleanup')) {
            wp_schedule_event(time(), 'weekly', 'cp_fec_audit_cleanup');
        }

        // Integration hooks
        do_action('cp_fec_loaded', $this);
    }

    /**
     * Load committee information
     *
     * @since 1.0.0
     */
    private function load_committee_info() {
        $this->committee_info = get_option('cp_fec_committee_info', array(
            'committee_id' => '',
            'committee_name' => '',
            'committee_type' => 'candidate', // candidate, pac, party
            'treasurer_name' => '',
            'street1' => '',
            'street2' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'email' => '',
            'phone' => '',
            'candidate_name' => '',
            'candidate_office' => '', // house, senate, president
            'candidate_state' => '',
            'candidate_district' => '',
            'election_cycle' => date('Y'),
        ));
    }

    /**
     * Initialize compliance system
     *
     * @since 1.0.0
     */
    public function init() {
        // Check if database tables exist
        if (!$this->tables_exist()) {
            $this->create_tables();
        }

        // Load text domain for translations
        load_plugin_textdomain('campaignpress', false, dirname(plugin_basename(__FILE__)) . '/languages');

        // Allow customization after initialization
        do_action('cp_fec_init', $this);
    }

    /**
     * Admin initialization
     *
     * @since 1.0.0
     */
    public function admin_init() {
        // Check for database updates
        $db_version = get_option('cp_fec_db_version', '0.0.0');
        if (version_compare($db_version, self::DB_VERSION, '<')) {
            $this->create_tables();
        }

        // Register settings
        $this->register_settings();
    }

    /**
     * Plugin activation
     *
     * @since 1.0.0
     */
    public function activate() {
        // Create database tables
        $this->create_tables();

        // Set activation flag
        update_option('cp_fec_activated', time());
        update_option('cp_fec_db_version', self::DB_VERSION);

        // Log activation
        if (function_exists('error_log')) {
            error_log('CampaignPress FEC Compliance activated at ' . date('Y-m-d H:i:s'));
        }
    }

    /**
     * Plugin deactivation
     *
     * @since 1.0.0
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('cp_fec_daily_compliance_check');
        wp_clear_scheduled_hook('cp_fec_quarterly_report_reminder');
        wp_clear_scheduled_hook('cp_fec_audit_cleanup');

        // Log deactivation
        if (function_exists('error_log')) {
            error_log('CampaignPress FEC Compliance deactivated at ' . date('Y-m-d H:i:s'));
        }
    }

    /**
     * Create database tables for FEC compliance
     *
     * @since 1.0.0
     */
    private function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Create donors table (handled by CampaignPress_FEC_Donors class)
        $this->donors->create_table();

        // Create contributions table (handled by CampaignPress_FEC_Contributions class)
        $this->contributions->create_table();

        // Create audit trail table (handled by CampaignPress_FEC_Audit_Trail class)
        $this->audit_trail->create_table();

        // Update database version
        update_option('cp_fec_db_version', self::DB_VERSION);
    }

    /**
     * Check if all required tables exist
     *
     * @since 1.0.0
     * @return bool True if all tables exist
     */
    private function tables_exist() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'cp_fec_donors',
            $wpdb->prefix . 'cp_fec_contributions',
            $wpdb->prefix . 'cp_fec_audit_log',
        );

        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
                return false;
            }
        }

        return true;
    }

    /**
     * Register plugin settings
     *
     * @since 1.0.0
     */
    private function register_settings() {
        // Committee information
        register_setting('cp_fec_settings', 'cp_fec_committee_info', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_committee_info'),
        ));

        // Compliance settings
        register_setting('cp_fec_settings', 'cp_fec_enable_auto_limits', array(
            'type' => 'boolean',
            'default' => true,
        ));

        register_setting('cp_fec_settings', 'cp_fec_enable_prohibited_source_check', array(
            'type' => 'boolean',
            'default' => true,
        ));

        register_setting('cp_fec_settings', 'cp_fec_enable_48hour_alerts', array(
            'type' => 'boolean',
            'default' => true,
        ));

        register_setting('cp_fec_settings', 'cp_fec_alert_email', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email',
        ));

        register_setting('cp_fec_settings', 'cp_fec_audit_retention_years', array(
            'type' => 'integer',
            'default' => 3,
        ));
    }

    /**
     * Sanitize committee information
     *
     * @since 1.0.0
     * @param array $input Raw input
     * @return array Sanitized array
     */
    public function sanitize_committee_info($input) {
        $sanitized = array();

        $sanitized['committee_id'] = isset($input['committee_id']) ? sanitize_text_field($input['committee_id']) : '';
        $sanitized['committee_name'] = isset($input['committee_name']) ? sanitize_text_field($input['committee_name']) : '';
        $sanitized['committee_type'] = isset($input['committee_type']) ? sanitize_text_field($input['committee_type']) : 'candidate';
        $sanitized['treasurer_name'] = isset($input['treasurer_name']) ? sanitize_text_field($input['treasurer_name']) : '';
        $sanitized['street1'] = isset($input['street1']) ? sanitize_text_field($input['street1']) : '';
        $sanitized['street2'] = isset($input['street2']) ? sanitize_text_field($input['street2']) : '';
        $sanitized['city'] = isset($input['city']) ? sanitize_text_field($input['city']) : '';
        $sanitized['state'] = isset($input['state']) ? sanitize_text_field($input['state']) : '';
        $sanitized['zip'] = isset($input['zip']) ? sanitize_text_field($input['zip']) : '';
        $sanitized['email'] = isset($input['email']) ? sanitize_email($input['email']) : '';
        $sanitized['phone'] = isset($input['phone']) ? sanitize_text_field($input['phone']) : '';
        $sanitized['candidate_name'] = isset($input['candidate_name']) ? sanitize_text_field($input['candidate_name']) : '';
        $sanitized['candidate_office'] = isset($input['candidate_office']) ? sanitize_text_field($input['candidate_office']) : '';
        $sanitized['candidate_state'] = isset($input['candidate_state']) ? sanitize_text_field($input['candidate_state']) : '';
        $sanitized['candidate_district'] = isset($input['candidate_district']) ? sanitize_text_field($input['candidate_district']) : '';
        $sanitized['election_cycle'] = isset($input['election_cycle']) ? absint($input['election_cycle']) : date('Y');

        return $sanitized;
    }

    /**
     * Add compliance admin menu
     *
     * @since 1.0.0
     */
    public function add_admin_menu() {
        // Main FEC Compliance menu
        add_menu_page(
            __('FEC Compliance', 'campaignpress'),
            __('FEC Compliance', 'campaignpress'),
            'manage_options',
            'cp-fec-compliance',
            array($this, 'render_dashboard_page'),
            'dashicons-shield-alt',
            25
        );

        // Dashboard submenu
        add_submenu_page(
            'cp-fec-compliance',
            __('Compliance Dashboard', 'campaignpress'),
            __('Dashboard', 'campaignpress'),
            'manage_options',
            'cp-fec-compliance',
            array($this, 'render_dashboard_page')
        );

        // Contributions submenu
        add_submenu_page(
            'cp-fec-compliance',
            __('Contributions', 'campaignpress'),
            __('Contributions', 'campaignpress'),
            'manage_options',
            'cp-fec-contributions',
            array($this, 'render_contributions_page')
        );

        // Donors submenu
        add_submenu_page(
            'cp-fec-compliance',
            __('Donors', 'campaignpress'),
            __('Donors', 'campaignpress'),
            'manage_options',
            'cp-fec-donors',
            array($this, 'render_donors_page')
        );

        // Reports submenu
        add_submenu_page(
            'cp-fec-compliance',
            __('FEC Reports', 'campaignpress'),
            __('Reports', 'campaignpress'),
            'manage_options',
            'cp-fec-reports',
            array($this, 'render_reports_page')
        );

        // Audit Trail submenu
        add_submenu_page(
            'cp-fec-compliance',
            __('Audit Trail', 'campaignpress'),
            __('Audit Trail', 'campaignpress'),
            'manage_options',
            'cp-fec-audit',
            array($this, 'render_audit_page')
        );

        // Settings submenu
        add_submenu_page(
            'cp-fec-compliance',
            __('Compliance Settings', 'campaignpress'),
            __('Settings', 'campaignpress'),
            'manage_options',
            'cp-fec-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Enqueue admin assets
     *
     * @since 1.0.0
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        // Only load on FEC compliance pages
        if (strpos($hook, 'cp-fec') === false) {
            return;
        }

        // Admin styles
        wp_enqueue_style('cp-fec-admin', get_template_directory_uri() . '/assets/css/fec-admin.css', array(), self::VERSION);

        // Admin scripts
        wp_enqueue_script('cp-fec-admin', get_template_directory_uri() . '/assets/js/fec-admin.js', array('jquery'), self::VERSION, true);

        // Localize script
        wp_localize_script('cp-fec-admin', 'cpFEC', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cp_fec_nonce'),
            'limits' => array(
                'individual_candidate' => CP_FEC_INDIVIDUAL_LIMIT_CANDIDATE,
                'individual_pac' => CP_FEC_INDIVIDUAL_LIMIT_PAC,
                'individual_party' => CP_FEC_INDIVIDUAL_LIMIT_PARTY,
                'pac_candidate' => CP_FEC_PAC_LIMIT_CANDIDATE,
                'itemization_threshold' => CP_FEC_ITEMIZATION_THRESHOLD,
                '48hour_threshold' => CP_FEC_48HOUR_THRESHOLD,
            ),
            'strings' => array(
                'validating' => __('Validating contribution...', 'campaignpress'),
                'processing' => __('Processing...', 'campaignpress'),
                'success' => __('Success!', 'campaignpress'),
                'error' => __('An error occurred.', 'campaignpress'),
                'limit_exceeded' => __('Contribution limit exceeded!', 'campaignpress'),
                'prohibited_source' => __('Prohibited contribution source!', 'campaignpress'),
            ),
        ));
    }

    /**
     * Daily compliance check
     *
     * @since 1.0.0
     */
    public function daily_compliance_check() {
        // Check for contributions needing 48-hour notice
        $this->contributions->check_48hour_notices();

        // Check for approaching limits
        $this->contributions->check_approaching_limits();

        // Log check completion
        $this->audit_trail->log_event('daily_compliance_check', array(
            'timestamp' => current_time('mysql'),
        ));
    }

    /**
     * Quarterly report reminder
     *
     * @since 1.0.0
     */
    public function quarterly_report_reminder() {
        // Send email reminder for quarterly reports
        $alert_email = get_option('cp_fec_alert_email', get_option('admin_email'));

        if ($alert_email) {
            wp_mail(
                $alert_email,
                __('Quarterly FEC Report Due Soon', 'campaignpress'),
                __('This is a reminder that your quarterly FEC report is due soon. Please log in to generate and file your report.', 'campaignpress')
            );
        }
    }

    /**
     * Audit cleanup - Archive old audit logs
     *
     * @since 1.0.0
     */
    public function audit_cleanup() {
        $retention_years = get_option('cp_fec_audit_retention_years', 3);
        $this->audit_trail->cleanup_old_logs($retention_years);
    }

    /**
     * AJAX: Validate contribution
     *
     * @since 1.0.0
     */
    public function ajax_validate_contribution() {
        check_ajax_referer('cp_fec_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaignpress')));
        }

        $donor_id = isset($_POST['donor_id']) ? absint($_POST['donor_id']) : 0;
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $election_type = isset($_POST['election_type']) ? sanitize_text_field($_POST['election_type']) : 'primary';

        $validation = $this->contributions->validate_contribution($donor_id, $amount, $election_type);

        if (is_wp_error($validation)) {
            wp_send_json_error(array('message' => $validation->get_error_message()));
        }

        wp_send_json_success($validation);
    }

    /**
     * AJAX: Record contribution
     *
     * @since 1.0.0
     */
    public function ajax_record_contribution() {
        check_ajax_referer('cp_fec_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaignpress')));
        }

        $contribution_data = isset($_POST['contribution']) ? $_POST['contribution'] : array();

        $result = $this->contributions->record_contribution($contribution_data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('contribution_id' => $result));
    }

    /**
     * AJAX: Generate report
     *
     * @since 1.0.0
     */
    public function ajax_generate_report() {
        check_ajax_referer('cp_fec_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaignpress')));
        }

        $report_type = isset($_POST['report_type']) ? sanitize_text_field($_POST['report_type']) : 'quarterly';
        $report_period = isset($_POST['report_period']) ? sanitize_text_field($_POST['report_period']) : '';

        $result = $this->reports->generate_report($report_type, $report_period);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success($result);
    }

    /**
     * AJAX: Export FEC form
     *
     * @since 1.0.0
     */
    public function ajax_export_fec_form() {
        check_ajax_referer('cp_fec_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaignpress')));
        }

        $report_id = isset($_POST['report_id']) ? absint($_POST['report_id']) : 0;

        $result = $this->reports->export_fec_form3($report_id);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('file_url' => $result));
    }

    /**
     * AJAX: Check donor limits
     *
     * @since 1.0.0
     */
    public function ajax_check_donor_limits() {
        check_ajax_referer('cp_fec_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaignpress')));
        }

        $donor_id = isset($_POST['donor_id']) ? absint($_POST['donor_id']) : 0;

        $limits = $this->contributions->get_donor_contribution_totals($donor_id);

        wp_send_json_success($limits);
    }

    /**
     * Render dashboard page
     *
     * @since 1.0.0
     */
    public function render_dashboard_page() {
        require_once CP_FEC_PATH . 'views/dashboard.php';
    }

    /**
     * Render contributions page
     *
     * @since 1.0.0
     */
    public function render_contributions_page() {
        require_once CP_FEC_PATH . 'views/contributions.php';
    }

    /**
     * Render donors page
     *
     * @since 1.0.0
     */
    public function render_donors_page() {
        require_once CP_FEC_PATH . 'views/donors.php';
    }

    /**
     * Render reports page
     *
     * @since 1.0.0
     */
    public function render_reports_page() {
        require_once CP_FEC_PATH . 'views/reports.php';
    }

    /**
     * Render audit trail page
     *
     * @since 1.0.0
     */
    public function render_audit_page() {
        require_once CP_FEC_PATH . 'views/audit.php';
    }

    /**
     * Render settings page
     *
     * @since 1.0.0
     */
    public function render_settings_page() {
        require_once CP_FEC_PATH . 'views/settings.php';
    }

    /**
     * Get committee information
     *
     * @since 1.0.0
     * @return array Committee information
     */
    public function get_committee_info() {
        return $this->committee_info;
    }

    /**
     * Get compliance version
     *
     * @since 1.0.0
     * @return string Version number
     */
    public function get_version() {
        return self::VERSION;
    }
}

/**
 * Initialize the FEC Compliance system
 *
 * @since 1.0.0
 * @return CampaignPress_FEC_Compliance
 */
function cp_fec() {
    return CampaignPress_FEC_Compliance::get_instance();
}

// Initialize FEC Compliance
cp_fec();

/**
 * Helper Functions
 */

/**
 * Check if FEC compliance is active
 *
 * @since 1.0.0
 * @return bool True if active
 */
function cp_fec_is_active() {
    return (bool) get_option('cp_fec_activated', false);
}

/**
 * Get current FEC contribution limits
 *
 * @since 1.0.0
 * @return array Contribution limits
 */
function cp_fec_get_limits() {
    return array(
        'individual_candidate' => CP_FEC_INDIVIDUAL_LIMIT_CANDIDATE,
        'individual_pac' => CP_FEC_INDIVIDUAL_LIMIT_PAC,
        'individual_party' => CP_FEC_INDIVIDUAL_LIMIT_PARTY,
        'pac_candidate' => CP_FEC_PAC_LIMIT_CANDIDATE,
        'itemization_threshold' => CP_FEC_ITEMIZATION_THRESHOLD,
        '48hour_threshold' => CP_FEC_48HOUR_THRESHOLD,
    );
}

/**
 * Format amount for FEC display
 *
 * @since 1.0.0
 * @param float $amount Amount to format
 * @return string Formatted amount
 */
function cp_fec_format_amount($amount) {
    return '$' . number_format($amount, 2);
}
