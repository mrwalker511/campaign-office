<?php
/**
 * CampaignPress Premium Activation System
 *
 * Comprehensive premium activation and management system for CampaignPress.
 * Handles license validation, feature toggles, auto-updates, and premium access control.
 *
 * @package CampaignPress
 * @subpackage Premium
 * @since 2.0.0
 * @version 2.0.0
 *
 * Security Features:
 * - Nonce verification for all admin actions
 * - Capability checks for administrative functions
 * - Input sanitization and validation
 * - SQL injection prevention via prepared statements
 * - XSS protection via output escaping
 * - CSRF protection via WordPress nonces
 *
 * Developer Mode:
 * Enable developer mode to bypass license checks and test all premium features.
 * Add to wp-config.php:
 *   define('CAMPAIGNPRESS_DEV_MODE', true);
 *
 * This will:
 * - Unlock all premium features (Professional tier)
 * - Bypass license validation
 * - Enable all modules with init files
 * - Show fake professional license in admin (expires in 10 years)
 *
 * @author CampaignPress Team
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main CampaignPress Premium Class
 *
 * Handles all premium functionality including license validation,
 * feature management, and premium access control.
 *
 * @since 2.0.0
 */
class CampaignPress_Premium {

    /**
     * Plugin version
     *
     * @var string
     */
    const VERSION = '2.0.0';

    /**
     * License server URL
     *
     * Note: This URL must be configured to point to your license server.
     * Use the 'campaignpress_license_server_url' filter to override this value.
     *
     * @var string
     */
    const LICENSE_SERVER = 'https://api.campaignpress.com/v1/';

    /**
     * Grace period in days for expired licenses
     *
     * @var int
     */
    const GRACE_PERIOD_DAYS = 7;

    /**
     * Singleton instance
     *
     * @var CampaignPress_Premium
     */
    private static $instance = null;

    /**
     * Premium features configuration
     *
     * @var array
     */
    private $premium_features = array();

    /**
     * Development mode flag
     *
     * @var bool
     */
    private $dev_mode = false;

    /**
     * Get singleton instance
     *
     * @return CampaignPress_Premium
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - Initialize premium system
     *
     * @since 2.0.0
     */
    private function __construct() {
        // Set development mode - must explicitly define CAMPAIGNPRESS_DEV_MODE
        // Note: WP_DEBUG is NOT used as a fallback to prevent accidental premium bypass
        $this->dev_mode = defined('CAMPAIGNPRESS_DEV_MODE') && CAMPAIGNPRESS_DEV_MODE;

        // Define premium features
        $this->define_premium_features();

        // Initialize hooks
        $this->init_hooks();

        // WARNING: CSP header relaxation for local development only
        // This provides a permissive CSP header to facilitate local development and testing.
        // It ONLY applies when:
        // 1. CAMPAIGNPRESS_DEV_MODE is explicitly set to true
        // 2. The request is in the admin area
        // 3. The hostname is exactly 'localhost' or '127.0.0.1'
        // NEVER enable CAMPAIGNPRESS_DEV_MODE on production sites.
        if ($this->dev_mode && is_admin() && in_array($_SERVER['HTTP_HOST'] ?? '', array('localhost', '127.0.0.1'), true) ) {
            // Only add CSP header if not already set
            if (!headers_sent() && !isset($GLOBALS['cp_csp_header_set'])) {
                add_action('send_headers', function() {
                    // Mark that we've set the CSP header
                    $GLOBALS['cp_csp_header_set'] = true;
                    // Permissive CSP for local development only
                    header("Content-Security-Policy: default-src 'self' data: blob: http://localhost https://localhost http://127.0.0.1 https://127.0.0.1 'unsafe-inline' 'unsafe-eval'; script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost https://localhost http://127.0.0.1 https://127.0.0.1; style-src 'self' 'unsafe-inline' http://localhost https://localhost; img-src 'self' data: http://localhost https://localhost; connect-src 'self' http://localhost https://localhost;");
                });
            }
        }

        // Log initialization
        $this->log_event('premium_system_initialized', array(
            'version' => self::VERSION,
            'dev_mode' => $this->dev_mode
        ));
    }

    /**
     * Define available premium features
     *
     * @since 2.0.0
     */
    private function define_premium_features() {
        /**
         * Filter premium features configuration
         *
         * @param array $features Premium features array
         */
        $this->premium_features = apply_filters('campaignpress_premium_features', array(
            'crm' => array(
                'name' => __('Advanced CRM System', 'campaign-office'),
                'description' => __('Complete donor and volunteer relationship management', 'campaign-office'),
                'enabled' => true,
                'init_file' => CAMPAIGNPRESS_INCLUDES_DIR . '/premium/crm/crm-init.php',
                'required_license' => 'professional',
                'icon' => 'dashicons-groups',
            ),
            'field_operations' => array(
                'name' => __('Field Operations', 'campaign-office'),
                'description' => __('Canvassing, phone banking, and field team management', 'campaign-office'),
                'enabled' => true,
                'init_file' => CAMPAIGNPRESS_INCLUDES_DIR . '/premium/field-operations/field-ops-init.php',
                'required_license' => 'professional',
                'icon' => 'dashicons-location',
            ),
            'compliance' => array(
                'name' => __('FEC Compliance', 'campaign-office'),
                'description' => __('Federal and state campaign finance compliance tools', 'campaign-office'),
                'enabled' => true,
                'init_file' => CAMPAIGNPRESS_INCLUDES_DIR . '/premium/compliance/compliance-init.php',
                'required_license' => 'professional',
                'icon' => 'dashicons-shield-alt',
            ),
            'analytics' => array(
                'name' => __('Advanced Analytics', 'campaign-office'),
                'description' => __('Deep insights into campaign performance and metrics', 'campaign-office'),
                'enabled' => true,
                'init_file' => CAMPAIGNPRESS_INCLUDES_DIR . '/premium/analytics/analytics-init.php',
                'required_license' => 'professional',
                'icon' => 'dashicons-chart-line',
            ),
            'api' => array(
                'name' => __('REST API Access', 'campaign-office'),
                'description' => __('Connect external tools via REST API', 'campaign-office'),
                'enabled' => true,
                'init_file' => CAMPAIGNPRESS_INCLUDES_DIR . '/premium/api/api-init.php',
                'required_license' => 'professional',
                'icon' => 'dashicons-rest-api',
            ),
            'white_label' => array(
                'name' => __('White Label', 'campaign-office'),
                'description' => __('Remove CampaignPress branding', 'campaign-office'),
                'enabled' => true,
                'init_file' => null,
                'required_license' => 'professional',
                'icon' => 'dashicons-admin-appearance',
            ),
            'priority_support' => array(
                'name' => __('Priority Support', 'campaign-office'),
                'description' => __('24/7 priority support access', 'campaign-office'),
                'enabled' => true,
                'init_file' => null,
                'required_license' => 'professional',
                'icon' => 'dashicons-sos',
            ),
            'auto_updates' => array(
                'name' => __('Automatic Updates', 'campaign-office'),
                'description' => __('Automatic premium theme updates', 'campaign-office'),
                'enabled' => true,
                'init_file' => null,
                'required_license' => 'professional',
                'icon' => 'dashicons-update',
            ),
            'developer_console' => array(
                'name' => __('Developer Console', 'campaign-office'),
                'description' => __('Advanced developer tools and system management console', 'campaign-office'),
                'enabled' => true,
                'init_file' => CAMPAIGNPRESS_INCLUDES_DIR . '/premium/developer-console/developer-console-init.php',
                'required_license' => 'professional',
                'icon' => 'dashicons-code-standards',
            ),
            'design_studio_templates' => array(
                'name' => __('Premium Design Templates', 'campaign-office'),
                'description' => __('50+ professionally designed campaign page templates', 'campaign-office'),
                'enabled' => true,
                'init_file' => CAMPAIGNPRESS_INCLUDES_DIR . '/premium/design-studio/premium-templates-init.php',
                'required_license' => 'professional',
                'icon' => 'dashicons-layout',
            ),
        ));
    }

    /**
     * Initialize WordPress hooks
     *
     * @since 2.0.0
     */
    private function init_hooks() {
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'), 5);
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_notices', array($this, 'display_admin_notices'));

        // Dashboard widget
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));

        // AJAX handlers
        add_action('wp_ajax_cp_validate_license', array($this, 'ajax_validate_license'));
        add_action('wp_ajax_cp_deactivate_license', array($this, 'ajax_deactivate_license'));
        add_action('wp_ajax_cp_toggle_feature', array($this, 'ajax_toggle_feature'));
        add_action('wp_ajax_cp_check_updates', array($this, 'ajax_check_updates'));

        // Auto-update system
        add_filter('pre_set_site_transient_update_themes', array($this, 'check_for_theme_update'));
        add_filter('themes_api', array($this, 'theme_api_call'), 10, 3);

        // Activation/Deactivation hooks (run on theme switch)
        add_action('after_switch_theme', array($this, 'activation_hook'));
        add_action('switch_theme', array($this, 'deactivation_hook'));

        // Load premium features if active
        add_action('init', array($this, 'load_premium_features'), 1);

        // Cron hooks for license checking
        add_action('campaignpress_daily_license_check', array($this, 'daily_license_check'));
        if (!wp_next_scheduled('campaignpress_daily_license_check')) {
            wp_schedule_event(time(), 'daily', 'campaignpress_daily_license_check');
        }
    }

    /**
     * Add premium admin menu
     *
     * @since 2.0.0
     */
    public function add_admin_menu() {
        // Main premium menu item
        add_menu_page(
            __('CampaignPress Premium', 'campaign-office'),
            __('CampaignPress Pro', 'campaign-office'),
            'manage_options',
            'campaignpress-premium',
            array($this, 'render_license_page'),
            'dashicons-star-filled',
            3
        );

        // License management submenu
        add_submenu_page(
            'campaignpress-premium',
            __('License Management', 'campaign-office'),
            __('License', 'campaign-office'),
            'manage_options',
            'campaignpress-premium',
            array($this, 'render_license_page')
        );

        // Feature toggles submenu (only if premium is active)
        if ($this->is_premium_active()) {
            add_submenu_page(
                'campaignpress-premium',
                __('Feature Management', 'campaign-office'),
                __('Features', 'campaign-office'),
                'manage_options',
                'campaignpress-features',
                array($this, 'render_features_page')
            );

            // System status submenu
            add_submenu_page(
                'campaignpress-premium',
                __('System Status', 'campaign-office'),
                __('System Status', 'campaign-office'),
                'manage_options',
                'campaignpress-system-status',
                array($this, 'render_system_status_page')
            );
        }

        // Upgrade page (only if not premium or expired)
        if (!$this->is_premium_active() || $this->is_license_expired()) {
            add_submenu_page(
                'campaignpress-premium',
                __('Upgrade to Premium', 'campaign-office'),
                __('Upgrade', 'campaign-office'),
                'manage_options',
                'campaignpress-upgrade',
                array($this, 'render_upgrade_page')
            );
        }
    }

    /**
     * Register plugin settings
     *
     * @since 2.0.0
     */
    public function register_settings() {
        // License settings
        register_setting('campaignpress_premium_license', 'campaignpress_license_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));

        register_setting('campaignpress_premium_license', 'campaignpress_license_email', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default' => '',
        ));

        // Feature toggles
        register_setting('campaignpress_premium_features', 'campaignpress_enabled_features', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_enabled_features'),
            'default' => array(),
        ));
    }

    /**
     * Sanitize enabled features array
     *
     * @param array $input Raw input
     * @return array Sanitized array
     */
    public function sanitize_enabled_features($input) {
        if (!is_array($input)) {
            return array();
        }

        $sanitized = array();
        $valid_features = array_keys($this->premium_features);

        foreach ($input as $feature => $enabled) {
            if (in_array($feature, $valid_features, true)) {
                $sanitized[$feature] = (bool) $enabled;
            }
        }

        return $sanitized;
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our admin pages (campaignpress, cp-*, campaign-data, etc.)
        if (strpos($hook, 'campaign') === false && strpos($hook, 'cp-') === false && strpos($hook, 'cp_') === false) {
            return;
        }

        // Admin styles - check if file exists
        $admin_css_path = CAMPAIGNPRESS_THEME_DIR . '/assets/css/premium-admin.css';
        if (file_exists($admin_css_path)) {
            wp_enqueue_style(
                'campaignpress-premium-admin',
                CAMPAIGNPRESS_THEME_URI . '/assets/css/premium-admin.css',
                array(),
                self::VERSION
            );
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('CampaignPress Premium: Admin CSS file not found: ' . $admin_css_path);
        }

        // Admin scripts - check if file exists
        $admin_js_path = CAMPAIGNPRESS_THEME_DIR . '/assets/js/premium-admin.js';
        if (file_exists($admin_js_path)) {
            // Load in header (not footer) so localized `cpPremium` is available
            // for inline scripts output on the admin page.
            wp_enqueue_script(
                'campaignpress-premium-admin',
                CAMPAIGNPRESS_THEME_URI . '/assets/js/premium-admin.js',
                array('jquery'),
                self::VERSION,
                false
            );

            // Localize script with AJAX data
            wp_localize_script('campaignpress-premium-admin', 'cpPremium', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cp_premium_nonce'),
                'strings' => array(
                    'validating' => __('Validating license...', 'campaign-office'),
                    'deactivating' => __('Deactivating license...', 'campaign-office'),
                    'success' => __('Success!', 'campaign-office'),
                    'error' => __('An error occurred.', 'campaign-office'),
                    'confirm_deactivate' => __('Are you sure you want to deactivate your license?', 'campaign-office'),
                ),
            ));
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('CampaignPress Premium: Admin JS file not found: ' . $admin_js_path);
        }
    }

    /**
     * Display admin notices
     *
     * @since 2.0.0
     */
    public function display_admin_notices() {
        // Check if we're on a CampaignPress admin page
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'campaign-office') === false) {
            return;
        }

        // License expired notice (with grace period)
        if ($this->is_license_expired()) {
            $expiry_date = get_option('campaignpress_license_expiry');
            $grace_end = strtotime($expiry_date . ' +' . self::GRACE_PERIOD_DAYS . ' days');
            $days_remaining = max(0, ceil(($grace_end - time()) / DAY_IN_SECONDS));

            if ($days_remaining > 0) {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p>
                        <strong><?php _e('CampaignPress Premium License Expired', 'campaign-office'); ?></strong><br>
                        <?php
                        printf(
                            __('Your license expired on %s. You have %d days remaining in your grace period. Please renew to continue receiving updates and support.', 'campaign-office'),
                            date_i18n(get_option('date_format'), strtotime($expiry_date)),
                            $days_remaining
                        );
                        ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=campaignpress-premium')); ?>" class="button button-primary" style="margin-left: 10px;">
                            <?php _e('Renew License', 'campaign-office'); ?>
                        </a>
                    </p>
                </div>
                <?php
            } else {
                ?>
                <div class="notice notice-error">
                    <p>
                        <strong><?php _e('CampaignPress Premium Grace Period Ended', 'campaign-office'); ?></strong><br>
                        <?php _e('Your grace period has ended. Premium features have been deactivated. Please renew your license.', 'campaign-office'); ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=campaignpress-premium')); ?>" class="button button-primary" style="margin-left: 10px;">
                            <?php _e('Renew License', 'campaign-office'); ?>
                        </a>
                    </p>
                </div>
                <?php
            }
        }

        // No license notice
        if (!get_option('campaignpress_license_key')) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p>
                    <strong><?php _e('Activate CampaignPress Premium', 'campaign-office'); ?></strong><br>
                    <?php _e('Enter your license key to unlock premium features.', 'campaign-office'); ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=campaignpress-premium')); ?>" class="button button-primary" style="margin-left: 10px;">
                        <?php _e('Activate License', 'campaign-office'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Add premium status dashboard widget
     *
     * @since 2.0.0
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'campaignpress_premium_status',
            __('CampaignPress Premium Status', 'campaign-office'),
            array($this, 'render_dashboard_widget')
        );
    }

    /**
     * Render dashboard widget content
     *
     * @since 2.0.0
     */
    public function render_dashboard_widget() {
        $is_active = $this->is_premium_active();
        $license_data = $this->get_license_data();

        ?>
        <div class="campaignpress-dashboard-widget">
            <?php if ($is_active): ?>
                <div class="cp-status-active">
                    <span class="dashicons dashicons-yes-alt" style="color: #46b450; font-size: 24px;"></span>
                    <h3 style="margin: 10px 0;"><?php _e('Premium Active', 'campaign-office'); ?></h3>

                    <?php if ($license_data): ?>
                        <p><strong><?php _e('License Type:', 'campaign-office'); ?></strong> <?php echo esc_html(ucfirst($license_data['license_type'])); ?></p>
                        <p><strong><?php _e('Expires:', 'campaign-office'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($license_data['expiry_date']))); ?></p>
                        <p><strong><?php _e('Site:', 'campaign-office'); ?></strong> <?php echo esc_html(get_bloginfo('name')); ?></p>
                    <?php endif; ?>

                    <h4><?php _e('Active Features:', 'campaign-office'); ?></h4>
                    <ul style="margin-left: 20px;">
                        <?php
                        $enabled_features = $this->get_enabled_features();
                        $count = 0;
                        foreach ($this->premium_features as $key => $feature) {
                            if ($this->is_feature_enabled($key)) {
                                echo '<li>' . esc_html($feature['name']) . '</li>';
                                $count++;
                            }
                        }
                        if ($count === 0) {
                            echo '<li><em>' . __('No features enabled', 'campaign-office') . '</em></li>';
                        }
                        ?>
                    </ul>

                    <p style="margin-top: 15px;">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=campaignpress-features')); ?>" class="button button-secondary">
                            <?php _e('Manage Features', 'campaign-office'); ?>
                        </a>
                    </p>
                </div>
            <?php else: ?>
                <div class="cp-status-inactive">
                    <span class="dashicons dashicons-lock" style="color: #dc3232; font-size: 24px;"></span>
                    <h3 style="margin: 10px 0;"><?php _e('Premium Inactive', 'campaign-office'); ?></h3>
                    <p><?php _e('Unlock powerful campaign management features with CampaignPress Premium.', 'campaign-office'); ?></p>

                    <ul style="margin-left: 20px;">
                        <li><?php _e('Advanced CRM System', 'campaign-office'); ?></li>
                        <li><?php _e('Field Operations Management', 'campaign-office'); ?></li>
                        <li><?php _e('FEC Compliance Tools', 'campaign-office'); ?></li>
                        <li><?php _e('Advanced Analytics', 'campaign-office'); ?></li>
                        <li><?php _e('Priority Support', 'campaign-office'); ?></li>
                    </ul>

                    <p style="margin-top: 15px;">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=campaignpress-upgrade')); ?>" class="button button-primary">
                            <?php _e('View Upgrade Options', 'campaign-office'); ?>
                        </a>
                        <?php if (get_option('campaignpress_license_key')): ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=campaignpress-premium')); ?>" class="button button-secondary">
                                <?php _e('Check License', 'campaign-office'); ?>
                            </a>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * AJAX: Validate license key
     *
     * @since 2.0.0
     */
    public function ajax_validate_license() {
        // Log that the AJAX endpoint was called (do not log sensitive license values)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('cp_validate_license called; user=' . get_current_user_id());
        }

        // Security checks - use non-die check to log/debug failures gracefully in dev
        $nonce_ok = check_ajax_referer('cp_premium_nonce', 'nonce', false);
        if (!$nonce_ok) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('cp_validate_license: nonce verification failed; user=' . get_current_user_id());
            }
            wp_send_json_error(array('message' => __('Security check failed (invalid nonce).', 'campaign-office')));
        }

        if (!current_user_can('manage_options')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('cp_validate_license: insufficient permissions; user=' . get_current_user_id());
            }
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'campaign-office')));
        }

        $license_key = isset($_POST['license_key']) ? sanitize_text_field($_POST['license_key']) : '';
        $license_email = isset($_POST['license_email']) ? sanitize_email($_POST['license_email']) : '';

        if (empty($license_key)) {
            wp_send_json_error(array('message' => __('License key is required.', 'campaign-office')));
        }

        // Validate license with server
        $result = $this->validate_license_key($license_key, $license_email);

        if ($result['success']) {
            // Save license data
            update_option('campaignpress_license_key', $license_key);
            update_option('campaignpress_license_email', $license_email);
            update_option('campaignpress_license_status', 'active');
            update_option('campaignpress_license_type', $result['data']['license_type']);
            update_option('campaignpress_license_expiry', $result['data']['expiry_date']);
            update_option('campaignpress_premium_active', true);
            update_option('campaignpress_license_activated_date', current_time('mysql'));

            // Log activation
            $this->log_event('license_activated', array(
                'license_type' => $result['data']['license_type'],
                'expiry_date' => $result['data']['expiry_date'],
            ));

            wp_send_json_success(array(
                'message' => __('License activated successfully!', 'campaign-office'),
                'data' => $result['data'],
            ));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }

    /**
     * AJAX: Deactivate license key
     *
     * @since 2.0.0
     */
    public function ajax_deactivate_license() {
        // Security checks
        check_ajax_referer('cp_premium_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'campaign-office')));
        }

        $license_key = get_option('campaignpress_license_key');

        if (!$license_key) {
            wp_send_json_error(array('message' => __('No license key found.', 'campaign-office')));
        }

        // Deactivate license on server
        $result = $this->deactivate_license_key($license_key);

        // Always deactivate locally regardless of server response
        delete_option('campaignpress_license_key');
        delete_option('campaignpress_license_email');
        delete_option('campaignpress_license_status');
        delete_option('campaignpress_license_type');
        delete_option('campaignpress_license_expiry');
        update_option('campaignpress_premium_active', false);

        // Log deactivation
        $this->log_event('license_deactivated', array(
            'server_response' => $result['success'],
        ));

        wp_send_json_success(array('message' => __('License deactivated successfully.', 'campaign-office')));
    }

    /**
     * AJAX: Toggle feature on/off
     *
     * @since 2.0.0
     */
    public function ajax_toggle_feature() {
        // Security checks
        check_ajax_referer('cp_premium_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'campaign-office')));
        }

        if (!$this->is_premium_active()) {
            wp_send_json_error(array('message' => __('Premium is not active.', 'campaign-office')));
        }

        $feature = isset($_POST['feature']) ? sanitize_text_field($_POST['feature']) : '';
        $enabled = isset($_POST['enabled']) ? (bool) $_POST['enabled'] : false;

        if (!isset($this->premium_features[$feature])) {
            wp_send_json_error(array('message' => __('Invalid feature.', 'campaign-office')));
        }

        // Get current enabled features
        $enabled_features = get_option('campaignpress_enabled_features', array());
        $enabled_features[$feature] = $enabled;
        update_option('campaignpress_enabled_features', $enabled_features);

        // Log feature toggle
        $this->log_event('feature_toggled', array(
            'feature' => $feature,
            'enabled' => $enabled,
        ));

        wp_send_json_success(array(
            'message' => $enabled ? __('Feature enabled.', 'campaign-office') : __('Feature disabled.', 'campaign-office'),
        ));
    }

    /**
     * AJAX: Check for updates
     *
     * @since 2.0.0
     */
    public function ajax_check_updates() {
        // Security checks
        check_ajax_referer('cp_premium_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'campaign-office')));
        }

        // Force update check
        delete_site_transient('update_themes');
        wp_update_themes();

        $update_data = $this->get_update_info();

        if ($update_data) {
            wp_send_json_success(array(
                'message' => __('Update available!', 'campaign-office'),
                'data' => $update_data,
            ));
        } else {
            wp_send_json_success(array(
                'message' => __('Your theme is up to date.', 'campaign-office'),
                'data' => null,
            ));
        }
    }

    /**
     * Validate license key with server
     *
     * @param string $license_key License key
     * @param string $license_email License email
     * @return array Result array with success status and data
     */
    private function validate_license_key($license_key, $license_email) {
        // Development mode bypass
        if ($this->dev_mode && defined('CAMPAIGNPRESS_DEV_LICENSE_BYPASS') && CAMPAIGNPRESS_DEV_LICENSE_BYPASS) {
            return array(
                'success' => true,
                'message' => __('Development mode - license validation bypassed', 'campaign-office'),
                'data' => array(
                    'license_type' => 'professional',
                    'expiry_date' => date('Y-m-d', strtotime('+1 year')),
                    'site_limit' => 5,
                ),
            );
        }

        // Allow customization of license server URL via filter
        $license_server_url = apply_filters('campaignpress_license_server_url', self::LICENSE_SERVER);

        // Check if license server is configured
        if ($license_server_url === self::LICENSE_SERVER && self::LICENSE_SERVER === 'https://api.campaignpress.com/v1/') {
            return array(
                'success' => false,
                'message' => __('License server not configured. Please set up your license server or use the campaignpress_license_server_url filter.', 'campaign-office'),
            );
        }

        // Make API request to license server
        $response = wp_remote_post($license_server_url . 'validate', array(
            'timeout' => 15,
            'body' => array(
                'license_key' => $license_key,
                'email' => $license_email,
                'domain' => home_url(),
                'product' => 'campaign-office',
                'version' => self::VERSION,
            ),
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => sprintf(__('Connection error: %s', 'campaign-office'), $response->get_error_message()),
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($body['success']) || !$body['success']) {
            return array(
                'success' => false,
                'message' => isset($body['message']) ? $body['message'] : __('License validation failed.', 'campaign-office'),
            );
        }

        return array(
            'success' => true,
            'message' => __('License validated successfully.', 'campaign-office'),
            'data' => $body['data'],
        );
    }

    /**
     * Deactivate license key on server
     *
     * @param string $license_key License key
     * @return array Result array with success status
     */
    private function deactivate_license_key($license_key) {
        // Make API request to license server
        $response = wp_remote_post(self::LICENSE_SERVER . 'deactivate', array(
            'timeout' => 15,
            'body' => array(
                'license_key' => $license_key,
                'domain' => home_url(),
            ),
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        return array(
            'success' => isset($body['success']) ? $body['success'] : false,
            'message' => isset($body['message']) ? $body['message'] : '',
        );
    }

    /**
     * Check for theme updates
     *
     * @param object $transient Update transient
     * @return object Modified transient
     */
    public function check_for_theme_update($transient) {
        if (!$this->is_premium_active() || !$this->is_feature_enabled('auto_updates')) {
            return $transient;
        }

        if (empty($transient->checked)) {
            return $transient;
        }

        $theme_slug = get_option('template');
        $update_data = $this->get_update_info();

        if ($update_data && version_compare($update_data['version'], CAMPAIGNPRESS_VERSION, '>')) {
            $transient->response[$theme_slug] = array(
                'theme' => $theme_slug,
                'new_version' => $update_data['version'],
                'url' => $update_data['url'],
                'package' => $update_data['package'],
            );
        }

        return $transient;
    }

    /**
     * Get theme update information from server
     *
     * @return array|false Update data or false if no update
     */
    private function get_update_info() {
        $license_key = get_option('campaignpress_license_key');
        if (!$license_key) {
            return false;
        }

        // Check cache first
        $cache_key = 'campaignpress_update_info';
        $cached_data = get_transient($cache_key);
        if ($cached_data !== false) {
            return $cached_data;
        }

        // Make API request
        $response = wp_remote_get(self::LICENSE_SERVER . 'update-check', array(
            'timeout' => 15,
            'body' => array(
                'license_key' => $license_key,
                'version' => CAMPAIGNPRESS_VERSION,
                'domain' => home_url(),
            ),
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($body['success']) || !$body['success']) {
            return false;
        }

        $update_data = isset($body['data']) ? $body['data'] : false;

        // Cache for 12 hours
        set_transient($cache_key, $update_data, 12 * HOUR_IN_SECONDS);

        return $update_data;
    }

    /**
     * Handle theme API calls for update information
     *
     * @param false|object|array $result The result object or array
     * @param string $action The type of information being requested
     * @param object $args Plugin/Theme API arguments
     * @return false|object|array Modified result
     */
    public function theme_api_call($result, $action, $args) {
        if ($action !== 'theme_information') {
            return $result;
        }

        $theme_slug = get_option('template');
        if ($args->slug !== $theme_slug) {
            return $result;
        }

        $update_data = $this->get_update_info();
        if (!$update_data) {
            return $result;
        }

        return (object) $update_data;
    }

    /**
     * Daily license check via cron
     *
     * @since 2.0.0
     */
    public function daily_license_check() {
        $license_key = get_option('campaignpress_license_key');
        $license_email = get_option('campaignpress_license_email');

        if (!$license_key) {
            return;
        }

        // Validate license
        $result = $this->validate_license_key($license_key, $license_email);

        if ($result['success']) {
            update_option('campaignpress_license_status', 'active');
            update_option('campaignpress_license_type', $result['data']['license_type']);
            update_option('campaignpress_license_expiry', $result['data']['expiry_date']);
            update_option('campaignpress_premium_active', true);
        } else {
            // License invalid - check if in grace period
            $expiry_date = get_option('campaignpress_license_expiry');
            if ($expiry_date) {
                $grace_end = strtotime($expiry_date . ' +' . self::GRACE_PERIOD_DAYS . ' days');
                if (time() > $grace_end) {
                    // Grace period ended
                    update_option('campaignpress_license_status', 'expired');
                    update_option('campaignpress_premium_active', false);
                }
            }
        }
    }

    /**
     * Load premium features based on toggles
     *
     * @since 2.0.0
     */
    public function load_premium_features() {
        if (!$this->is_premium_active()) {
            return;
        }

        foreach ($this->premium_features as $feature_key => $feature_data) {
            if (!$this->is_feature_enabled($feature_key)) {
                continue;
            }

            if (!isset($feature_data['init_file']) || !$feature_data['init_file']) {
                continue;
            }

            if (!file_exists($feature_data['init_file'])) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("CampaignPress Premium: Feature '{$feature_key}' init file not found: {$feature_data['init_file']}");
                }
                continue;
            }

            // Check license type requirement
            if (isset($feature_data['required_license'])) {
                $current_license = get_option('campaignpress_license_type', 'free');
                if (!$this->license_meets_requirement($current_license, $feature_data['required_license'])) {
                    continue;
                }
            }

            // Check for required database tables (if specified)
            if (isset($feature_data['required_tables']) && is_array($feature_data['required_tables'])) {
                $missing_tables = $this->check_required_tables($feature_data['required_tables']);
                if (!empty($missing_tables)) {
                    // Store missing dependencies for admin notice
                    $missing_deps = get_option('campaignpress_missing_dependencies', array());
                    $missing_deps[$feature_key] = $missing_tables;
                    update_option('campaignpress_missing_dependencies', $missing_deps);

                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("CampaignPress Premium: Feature '{$feature_key}' missing required tables: " . implode(', ', $missing_tables));
                    }
                    // Continue loading anyway - feature may handle missing tables gracefully
                }
            }

            require_once $feature_data['init_file'];
        }
    }

    /**
     * Check if required database tables exist
     *
     * @param array $tables Array of table names (without prefix)
     * @return array Array of missing table names
     */
    private function check_required_tables($tables) {
        global $wpdb;
        $missing = array();

        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
                $missing[] = $table;
            }
        }

        return $missing;
    }

    /**
     * Check if current license meets feature requirement
     *
     * @param string $current_license Current license type
     * @param string $required_license Required license type
     * @return bool True if license meets requirement
     */
    private function license_meets_requirement($current_license, $required_license) {
        $hierarchy = array('free' => 0, 'professional' => 1);

        $current_level = isset($hierarchy[$current_license]) ? $hierarchy[$current_license] : 0;
        $required_level = isset($hierarchy[$required_license]) ? $hierarchy[$required_license] : 0;

        return $current_level >= $required_level;
    }

    /**
     * Theme activation hook
     *
     * @since 2.0.0
     */
    public function activation_hook() {
        // Log activation
        $this->log_event('theme_activated', array(
            'version' => self::VERSION,
            'premium_active' => $this->is_premium_active(),
        ));

        // Check license status if key exists
        $license_key = get_option('campaignpress_license_key');
        if ($license_key) {
            $license_email = get_option('campaignpress_license_email');
            $result = $this->validate_license_key($license_key, $license_email);

            if ($result['success']) {
                update_option('campaignpress_premium_active', true);
            }
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Theme deactivation hook
     *
     * @since 2.0.0
     */
    public function deactivation_hook() {
        // Log deactivation
        $this->log_event('theme_deactivated', array(
            'version' => self::VERSION,
        ));

        // Clear scheduled events
        wp_clear_scheduled_hook('campaignpress_daily_license_check');

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Log premium system events
     *
     * @param string $event Event name
     * @param array $data Event data
     */
    private function log_event($event, $data = array()) {
        // Only log in dev mode or if logging is enabled
        if (!$this->dev_mode && !get_option('campaignpress_enable_logging', false)) {
            return;
        }

        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'event' => $event,
            'data' => $data,
            'user_id' => get_current_user_id(),
            'ip' => $this->get_client_ip(),
        );

        // Get existing logs
        $logs = get_option('campaignpress_premium_logs', array());

        // Add new log entry
        array_unshift($logs, $log_entry);

        // Keep only last 100 entries
        $logs = array_slice($logs, 0, 100);

        // Save logs
        update_option('campaignpress_premium_logs', $logs);
    }

    /**
     * Get client IP address
     *
     * @return string IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return 'Unknown';
    }

    /**
     * Check if premium is active
     *
     * @return bool True if premium is active
     */
    public function is_premium_active() {
        // Developer mode bypass - always active
        if ($this->dev_mode) {
            return true;
        }

        $is_active = get_option('campaignpress_premium_active', false);

        // Check grace period if expired
        if (!$is_active && $this->is_in_grace_period()) {
            return true;
        }

        return $is_active;
    }

    /**
     * Check if license is expired
     *
     * @return bool True if expired
     */
    public function is_license_expired() {
        // Developer mode bypass - never expired
        if ($this->dev_mode) {
            return false;
        }

        $expiry_date = get_option('campaignpress_license_expiry');
        if (!$expiry_date) {
            return false;
        }

        return time() > strtotime($expiry_date);
    }

    /**
     * Check if in grace period
     *
     * @return bool True if in grace period
     */
    private function is_in_grace_period() {
        $expiry_date = get_option('campaignpress_license_expiry');
        if (!$expiry_date) {
            return false;
        }

        $grace_end = strtotime($expiry_date . ' +' . self::GRACE_PERIOD_DAYS . ' days');
        return time() <= $grace_end;
    }

    /**
     * Check if a specific feature is enabled
     *
     * @param string $feature Feature key
     * @return bool True if enabled
     */
    public function is_feature_enabled($feature) {
        if (!isset($this->premium_features[$feature])) {
            return false;
        }

        // Developer mode bypass - enable all features with init files
        if ($this->dev_mode && isset($this->premium_features[$feature]['init_file']) && $this->premium_features[$feature]['init_file']) {
            return true;
        }

        $enabled_features = get_option('campaignpress_enabled_features', array());

        // If not explicitly set, use default from feature config
        if (!isset($enabled_features[$feature])) {
            return isset($this->premium_features[$feature]['enabled']) ? $this->premium_features[$feature]['enabled'] : false;
        }

        return (bool) $enabled_features[$feature];
    }

    /**
     * Get license data
     *
     * @return array|false License data or false
     */
    public function get_license_data() {
        // Developer mode bypass - return fake professional license
        if ($this->dev_mode) {
            return array(
                'license_key' => 'DEV-MODE-' . wp_generate_password(12, false),
                'license_email' => get_option('admin_email'),
                'license_status' => 'active',
                'license_type' => 'professional',
                'expiry_date' => date('Y-m-d', strtotime('+10 years')),
                'activated_date' => date('Y-m-d'),
            );
        }

        $license_key = get_option('campaignpress_license_key');
        if (!$license_key) {
            return false;
        }

        return array(
            'license_key' => $license_key,
            'license_email' => get_option('campaignpress_license_email'),
            'license_status' => get_option('campaignpress_license_status'),
            'license_type' => get_option('campaignpress_license_type'),
            'expiry_date' => get_option('campaignpress_license_expiry'),
            'activated_date' => get_option('campaignpress_license_activated_date'),
        );
    }

    /**
     * Get enabled features
     *
     * @return array Array of enabled features
     */
    public function get_enabled_features() {
        return get_option('campaignpress_enabled_features', array());
    }

    /**
     * Get all premium features
     *
     * @return array Premium features array
     */
    public function get_premium_features() {
        return $this->premium_features;
    }

    /**
     * Render license management page
     *
     * @since 2.0.0
     */
    public function render_license_page() {
        require_once dirname(__FILE__) . '/admin-pages/license-page.php';
    }

    /**
     * Render features management page
     *
     * @since 2.0.0
     */
    public function render_features_page() {
        require_once dirname(__FILE__) . '/admin-pages/features-page.php';
    }

    /**
     * Render system status page
     *
     * @since 2.0.0
     */
    public function render_system_status_page() {
        require_once dirname(__FILE__) . '/admin-pages/system-status-page.php';
    }

    /**
     * Render upgrade page
     *
     * @since 2.0.0
     */
    public function render_upgrade_page() {
        require_once dirname(__FILE__) . '/admin-pages/upgrade-page.php';
    }
}

// Initialize the premium system
CampaignPress_Premium::get_instance();

// Load premium demo content generator
require_once dirname(__FILE__) . '/premium-demo-content.php';

/**
 * Helper Functions
 */

/**
 * Check if premium is active
 *
 * @return bool True if premium is active
 */
function cp_is_premium_active() {
    return CampaignPress_Premium::get_instance()->is_premium_active();
}

/**
 * Check if a premium feature is available and enabled
 *
 * @param string $feature Feature key (e.g., 'crm', 'field_operations')
 * @return bool True if feature is available
 */
function cp_has_premium_feature($feature) {
    if (!cp_is_premium_active()) {
        return false;
    }

    return CampaignPress_Premium::get_instance()->is_feature_enabled($feature);
}

/**
 * Get license data
 *
 * @return array|false License data or false
 */
function cp_get_license_data() {
    return CampaignPress_Premium::get_instance()->get_license_data();
}

/**
 * Get license type
 *
 * @return string|false License type or false
 */
function cp_get_license_type() {
    $data = cp_get_license_data();
    return $data ? $data['license_type'] : false;
}

/**
 * Check if license meets minimum requirement
 *
 * @param string $required_license Required license type (basic, professional, enterprise)
 * @return bool True if current license meets requirement
 */
function cp_license_meets_requirement($required_license) {
    if (!cp_is_premium_active()) {
        return false;
    }

    $current = cp_get_license_type();
    if (!$current) {
        return false;
    }

    $hierarchy = array('free' => 0, 'professional' => 1);
    $current_level = isset($hierarchy[$current]) ? $hierarchy[$current] : 0;
    $required_level = isset($hierarchy[$required_license]) ? $hierarchy[$required_license] : 0;

    return $current_level >= $required_level;
}

/**
 * Display premium-only notice
 *
 * @param string $feature Feature name (optional)
 */
function cp_premium_only_notice($feature = '') {
    $message = $feature
        ? sprintf(__('%s is a premium-only feature.', 'campaign-office'), $feature)
        : __('This is a premium-only feature.', 'campaign-office');

    printf(
        '<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
        esc_html($message),
        esc_url(admin_url('admin.php?page=campaignpress-upgrade')),
        esc_html__('Upgrade to Premium', 'campaign-office')
    );
}

/**
 * Get premium features list
 *
 * @return array Premium features
 */
function cp_get_premium_features() {
    return CampaignPress_Premium::get_instance()->get_premium_features();
}

/**
 * Render feature check icon/text
 *
 * @param bool|string $value Feature value
 * @return string HTML output
 */
function cp_render_feature_check($value) {
    if ($value === true) {
        return '<span class="cp-check-yes"><span class="dashicons dashicons-yes-alt"></span></span>';
    } elseif ($value === false) {
        return '<span class="cp-check-no"><span class="dashicons dashicons-minus"></span></span>';
    } else {
        return '<span class="cp-check-text">' . esc_html($value) . '</span>';
    }
}

/**
 * Initialize CampaignPress Premium
 *
 * @return CampaignPress_Premium
 */
function campaignpress_premium() {
    return CampaignPress_Premium::get_instance();
}

// Start the premium system
campaignpress_premium();
