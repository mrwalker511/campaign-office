<?php
/**
 * Field Operations Module Initialization
 *
 * Coordinates all field operations modules including canvassing, phone banking,
 * GOTV operations, and volunteer scheduling for premium CampaignPress installations.
 *
 * @package CampaignPress
 * @subpackage Premium/FieldOperations
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Field_Operations_Init
 *
 * Main initialization class for field operations modules
 */
class CP_Field_Operations_Init {

    /**
     * Version number for field operations modules
     *
     * @var string
     */
    const VERSION = '2.0.0';

    /**
     * Singleton instance
     *
     * @var CP_Field_Operations_Init
     */
    private static $instance = null;

    /**
     * Canvassing module instance
     *
     * @var CP_Canvassing
     */
    public $canvassing;

    /**
     * Phone banking module instance
     *
     * @var CP_Phone_Banking
     */
    public $phone_banking;

    /**
     * GOTV module instance
     *
     * @var CP_GOTV
     */
    public $gotv;

    /**
     * Volunteer scheduling module instance
     *
     * @var CP_Volunteer_Scheduling
     */
    public $volunteer_scheduling;

    /**
     * Get singleton instance
     *
     * @return CP_Field_Operations_Init
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
        $this->load_dependencies();
        $this->init_hooks();
        $this->init_modules();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        $field_ops_path = get_template_directory() . '/includes/premium/field-operations/';

        // Load module classes
        require_once $field_ops_path . 'class-canvassing.php';
        require_once $field_ops_path . 'class-phone-banking.php';
        require_once $field_ops_path . 'class-gotv.php';
        require_once $field_ops_path . 'class-volunteer-scheduling.php';
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'), 20);

        // Enqueue assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        // REST API endpoints
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        // Service worker for offline functionality
        add_action('wp_head', array($this, 'register_service_worker'), 1);

        // AJAX handlers
        add_action('wp_ajax_cp_field_ops_sync', array($this, 'handle_offline_sync'));
        add_action('wp_ajax_nopriv_cp_field_ops_sync', array($this, 'handle_offline_sync'));

        // Cron jobs for automated tasks
        add_action('cp_field_ops_daily_digest', array($this, 'send_daily_digest'));
    }

    /**
     * Initialize all field operations modules
     */
    private function init_modules() {
        $this->canvassing = CP_Canvassing::get_instance();
        $this->phone_banking = CP_Phone_Banking::get_instance();
        $this->gotv = CP_GOTV::get_instance();
        $this->volunteer_scheduling = CP_Volunteer_Scheduling::get_instance();
    }

    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        // Main field operations menu
        add_menu_page(
            __('Field Operations', 'campaign-office'),
            __('Field Ops', 'campaign-office'),
            'edit_posts',
            'cp-field-operations',
            array($this, 'render_dashboard'),
            'dashicons-location',
            25
        );

        // Dashboard submenu
        add_submenu_page(
            'cp-field-operations',
            __('Field Ops Dashboard', 'campaign-office'),
            __('Dashboard', 'campaign-office'),
            'edit_posts',
            'cp-field-operations',
            array($this, 'render_dashboard')
        );

        // Reports submenu
        add_submenu_page(
            'cp-field-operations',
            __('Field Ops Reports', 'campaign-office'),
            __('Reports', 'campaign-office'),
            'edit_posts',
            'cp-field-ops-reports',
            array($this, 'render_reports')
        );

        // Settings submenu
        add_submenu_page(
            'cp-field-operations',
            __('Field Ops Settings', 'campaign-office'),
            __('Settings', 'campaign-office'),
            'manage_options',
            'cp-field-ops-settings',
            array($this, 'render_settings')
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        // Only load on field ops pages
        if (strpos($hook, 'cp-field-operations') === false &&
            strpos($hook, 'cp-canvassing') === false &&
            strpos($hook, 'cp-phone-banking') === false &&
            strpos($hook, 'cp-gotv') === false &&
            strpos($hook, 'cp-volunteer-scheduling') === false) {
            return;
        }

        // Styles
        wp_enqueue_style(
            'cp-field-ops-admin',
            get_template_directory_uri() . '/assets/css/field-ops-admin.css',
            array(),
            self::VERSION
        );

        // Scripts
        wp_enqueue_script(
            'cp-field-ops-admin',
            get_template_directory_uri() . '/assets/js/field-ops-admin.js',
            array('jquery', 'jquery-ui-sortable', 'wp-api'),
            self::VERSION,
            true
        );

        // Localize script
        wp_localize_script('cp-field-ops-admin', 'cpFieldOps', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('campaignpress/v1/'),
            'nonce' => wp_create_nonce('cp_field_ops_nonce'),
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this item?', 'campaign-office'),
                'savingChanges' => __('Saving changes...', 'campaign-office'),
                'changesSaved' => __('Changes saved successfully!', 'campaign-office'),
                'errorOccurred' => __('An error occurred. Please try again.', 'campaign-office'),
            ),
        ));
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only load on field ops pages/interfaces
        if (!is_page_template('template-canvassing.php') &&
            !is_page_template('template-phone-banking.php') &&
            !is_page_template('template-gotv.php')) {
            return;
        }

        // Styles
        wp_enqueue_style(
            'cp-field-ops',
            get_template_directory_uri() . '/assets/css/field-ops.css',
            array(),
            self::VERSION
        );

        // Scripts
        wp_enqueue_script(
            'cp-field-ops',
            get_template_directory_uri() . '/assets/js/field-ops.js',
            array('jquery'),
            self::VERSION,
            true
        );

        // Offline sync library
        wp_enqueue_script(
            'cp-field-ops-offline',
            get_template_directory_uri() . '/assets/js/field-ops-offline.js',
            array('cp-field-ops'),
            self::VERSION,
            true
        );

        // Localize script
        wp_localize_script('cp-field-ops', 'cpFieldOps', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('campaignpress/v1/'),
            'nonce' => wp_create_nonce('cp_field_ops_nonce'),
            'offlineMode' => true,
            'syncInterval' => 30000, // 30 seconds
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
            'strings' => array(
                'offline' => __('You are currently offline. Data will sync when connection is restored.', 'campaign-office'),
                'online' => __('Connection restored. Syncing data...', 'campaign-office'),
                'syncComplete' => __('Data synced successfully!', 'campaign-office'),
                'syncError' => __('Sync failed. Will retry automatically.', 'campaign-office'),
            ),
        ));
    }

    /**
     * Register service worker for offline functionality
     */
    public function register_service_worker() {
        if (!is_page_template('template-canvassing.php') &&
            !is_page_template('template-phone-banking.php') &&
            !is_page_template('template-gotv.php')) {
            return;
        }

        ?>
        <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo esc_url(get_template_directory_uri() . '/assets/js/service-worker.js'); ?>')
                    .then(function(registration) {
                        if (typeof cpFieldOps !== 'undefined' && cpFieldOps.debug) {
                            console.log('Service Worker registered:', registration);
                        }
                    })
                    .catch(function(error) {
                        if (typeof cpFieldOps !== 'undefined' && cpFieldOps.debug) {
                            console.log('Service Worker registration failed:', error);
                        }
                    });
            });
        }
        </script>
        <?php
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Sync endpoint for offline data
        register_rest_route('campaignpress/v1', '/field-ops/sync', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_sync_offline_data'),
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));

        // Dashboard stats endpoint
        register_rest_route('campaignpress/v1', '/field-ops/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_stats'),
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));
    }

    /**
     * Handle offline data sync
     */
    public function handle_offline_sync() {
        check_ajax_referer('cp_field_ops_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaign-office')));
        }

        $sync_data = isset($_POST['sync_data']) ? json_decode(wp_unslash($_POST['sync_data']), true) : array();

        if (empty($sync_data)) {
            wp_send_json_error(array('message' => __('No data to sync.', 'campaign-office')));
        }

        $results = array(
            'canvassing' => 0,
            'phone_banking' => 0,
            'gotv' => 0,
            'errors' => array(),
        );

        // Process canvassing records
        if (isset($sync_data['canvassing']) && is_array($sync_data['canvassing'])) {
            foreach ($sync_data['canvassing'] as $record) {
                $result = $this->canvassing->save_interaction($record);
                if ($result) {
                    $results['canvassing']++;
                } else {
                    $results['errors'][] = 'Canvassing record failed to save';
                }
            }
        }

        // Process phone banking records
        if (isset($sync_data['phone_banking']) && is_array($sync_data['phone_banking'])) {
            foreach ($sync_data['phone_banking'] as $record) {
                $result = $this->phone_banking->save_call($record);
                if ($result) {
                    $results['phone_banking']++;
                } else {
                    $results['errors'][] = 'Phone banking record failed to save';
                }
            }
        }

        // Process GOTV records
        if (isset($sync_data['gotv']) && is_array($sync_data['gotv'])) {
            foreach ($sync_data['gotv'] as $record) {
                $result = $this->gotv->save_voter_contact($record);
                if ($result) {
                    $results['gotv']++;
                } else {
                    $results['errors'][] = 'GOTV record failed to save';
                }
            }
        }

        wp_send_json_success(array(
            'message' => __('Data synced successfully!', 'campaign-office'),
            'results' => $results,
        ));
    }

    /**
     * REST API callback for syncing offline data
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function rest_sync_offline_data($request) {
        $sync_data = $request->get_json_params();

        if (empty($sync_data)) {
            return new WP_Error('no_data', __('No data to sync.', 'campaign-office'), array('status' => 400));
        }

        $results = array(
            'canvassing' => 0,
            'phone_banking' => 0,
            'gotv' => 0,
            'errors' => array(),
        );

        // Process canvassing records
        if (isset($sync_data['canvassing']) && is_array($sync_data['canvassing'])) {
            foreach ($sync_data['canvassing'] as $record) {
                $result = $this->canvassing->save_interaction($record);
                if ($result) {
                    $results['canvassing']++;
                } else {
                    $results['errors'][] = 'Canvassing record failed to save';
                }
            }
        }

        // Process phone banking records
        if (isset($sync_data['phone_banking']) && is_array($sync_data['phone_banking'])) {
            foreach ($sync_data['phone_banking'] as $record) {
                $result = $this->phone_banking->save_call($record);
                if ($result) {
                    $results['phone_banking']++;
                } else {
                    $results['errors'][] = 'Phone banking record failed to save';
                }
            }
        }

        // Process GOTV records
        if (isset($sync_data['gotv']) && is_array($sync_data['gotv'])) {
            foreach ($sync_data['gotv'] as $record) {
                $result = $this->gotv->save_voter_contact($record);
                if ($result) {
                    $results['gotv']++;
                } else {
                    $results['errors'][] = 'GOTV record failed to save';
                }
            }
        }

        return new WP_REST_Response(array(
            'message' => __('Data synced successfully!', 'campaign-office'),
            'results' => $results,
        ), 200);
    }

    /**
     * REST API callback for getting dashboard stats
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function rest_get_stats($request) {
        $stats = array(
            'canvassing' => $this->canvassing->get_stats(),
            'phone_banking' => $this->phone_banking->get_stats(),
            'gotv' => $this->gotv->get_stats(),
            'volunteers' => $this->volunteer_scheduling->get_stats(),
        );

        return new WP_REST_Response($stats, 200);
    }

    /**
     * Render field operations dashboard
     */
    public function render_dashboard() {
        ?>
        <div class="wrap cp-field-ops-dashboard">
            <h1><?php esc_html_e('Field Operations Dashboard', 'campaign-office'); ?></h1>

            <div class="cp-dashboard-grid">
                <!-- Canvassing Stats -->
                <div class="cp-dashboard-card">
                    <h2><?php esc_html_e('Canvassing', 'campaign-office'); ?></h2>
                    <div class="cp-stat-large">
                        <?php echo esc_html(number_format($this->canvassing->get_total_doors_knocked())); ?>
                    </div>
                    <p class="cp-stat-label"><?php esc_html_e('Doors Knocked Today', 'campaign-office'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-canvassing')); ?>" class="button">
                        <?php esc_html_e('Manage Canvassing', 'campaign-office'); ?>
                    </a>
                </div>

                <!-- Phone Banking Stats -->
                <div class="cp-dashboard-card">
                    <h2><?php esc_html_e('Phone Banking', 'campaign-office'); ?></h2>
                    <div class="cp-stat-large">
                        <?php echo esc_html(number_format($this->phone_banking->get_total_calls_today())); ?>
                    </div>
                    <p class="cp-stat-label"><?php esc_html_e('Calls Made Today', 'campaign-office'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-phone-banking')); ?>" class="button">
                        <?php esc_html_e('Manage Phone Banking', 'campaign-office'); ?>
                    </a>
                </div>

                <!-- GOTV Stats -->
                <div class="cp-dashboard-card">
                    <h2><?php esc_html_e('Get Out The Vote', 'campaign-office'); ?></h2>
                    <div class="cp-stat-large">
                        <?php echo esc_html(number_format($this->gotv->get_turnout_percentage(), 1)); ?>%
                    </div>
                    <p class="cp-stat-label"><?php esc_html_e('Voter Turnout', 'campaign-office'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-gotv')); ?>" class="button">
                        <?php esc_html_e('Manage GOTV', 'campaign-office'); ?>
                    </a>
                </div>

                <!-- Volunteer Stats -->
                <div class="cp-dashboard-card">
                    <h2><?php esc_html_e('Volunteers', 'campaign-office'); ?></h2>
                    <div class="cp-stat-large">
                        <?php echo esc_html(number_format($this->volunteer_scheduling->get_active_volunteers_today())); ?>
                    </div>
                    <p class="cp-stat-label"><?php esc_html_e('Active Volunteers Today', 'campaign-office'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-volunteer-scheduling')); ?>" class="button">
                        <?php esc_html_e('Manage Scheduling', 'campaign-office'); ?>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="cp-recent-activity">
                <h2><?php esc_html_e('Recent Activity', 'campaign-office'); ?></h2>
                <div id="cp-activity-feed"></div>
            </div>
        </div>

        <style>
            .cp-dashboard-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            .cp-dashboard-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                padding: 20px;
                border-radius: 4px;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .cp-dashboard-card h2 {
                margin-top: 0;
                font-size: 16px;
                color: #555;
            }
            .cp-stat-large {
                font-size: 48px;
                font-weight: 600;
                color: #2271b1;
                line-height: 1;
                margin: 15px 0;
            }
            .cp-stat-label {
                color: #666;
                margin-bottom: 15px;
            }
            .cp-recent-activity {
                margin-top: 30px;
                background: #fff;
                border: 1px solid #ccd0d4;
                padding: 20px;
                border-radius: 4px;
            }
        </style>
        <?php
    }

    /**
     * Render reports page
     */
    public function render_reports() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Field Operations Reports', 'campaign-office'); ?></h1>
            <p><?php esc_html_e('Comprehensive reporting across all field operations activities.', 'campaign-office'); ?></p>

            <!-- Report filters and export options will be added here -->
        </div>
        <?php
    }

    /**
     * Render settings page
     */
    public function render_settings() {
        if (isset($_POST['cp_field_ops_settings']) && check_admin_referer('cp_field_ops_settings_nonce')) {
            $this->save_settings();
            echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved successfully!', 'campaign-office') . '</p></div>';
        }

        $settings = $this->get_settings();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Field Operations Settings', 'campaign-office'); ?></h1>

            <form method="post" action="">
                <?php wp_nonce_field('cp_field_ops_settings_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Offline Mode', 'campaign-office'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="offline_mode" value="1" <?php checked($settings['offline_mode'], 1); ?>>
                                <?php esc_html_e('Allow canvassers and phone bankers to work offline', 'campaign-office'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('GPS Tracking', 'campaign-office'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="gps_tracking" value="1" <?php checked($settings['gps_tracking'], 1); ?>>
                                <?php esc_html_e('Enable GPS tracking for canvassers', 'campaign-office'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Auto-sync Interval', 'campaign-office'); ?></th>
                        <td>
                            <select name="sync_interval">
                                <option value="15000" <?php selected($settings['sync_interval'], 15000); ?>>15 <?php esc_html_e('seconds', 'campaign-office'); ?></option>
                                <option value="30000" <?php selected($settings['sync_interval'], 30000); ?>>30 <?php esc_html_e('seconds', 'campaign-office'); ?></option>
                                <option value="60000" <?php selected($settings['sync_interval'], 60000); ?>>1 <?php esc_html_e('minute', 'campaign-office'); ?></option>
                                <option value="300000" <?php selected($settings['sync_interval'], 300000); ?>>5 <?php esc_html_e('minutes', 'campaign-office'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="cp_field_ops_settings" class="button-primary" value="<?php esc_attr_e('Save Settings', 'campaign-office'); ?>">
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Get field operations settings
     *
     * @return array Settings
     */
    private function get_settings() {
        return wp_parse_args(get_option('cp_field_ops_settings', array()), array(
            'offline_mode' => 1,
            'gps_tracking' => 1,
            'sync_interval' => 30000,
        ));
    }

    /**
     * Save field operations settings
     */
    private function save_settings() {
        $settings = array(
            'offline_mode' => isset($_POST['offline_mode']) ? 1 : 0,
            'gps_tracking' => isset($_POST['gps_tracking']) ? 1 : 0,
            'sync_interval' => isset($_POST['sync_interval']) ? absint($_POST['sync_interval']) : 30000,
        );

        update_option('cp_field_ops_settings', $settings);
    }

    /**
     * Send daily digest email
     */
    public function send_daily_digest() {
        // Get admin email
        $admin_email = get_option('admin_email');

        // Compile stats
        $stats = array(
            'canvassing' => $this->canvassing->get_daily_stats(),
            'phone_banking' => $this->phone_banking->get_daily_stats(),
            'gotv' => $this->gotv->get_daily_stats(),
        );

        // Send email (simplified version)
        // In production, use a proper email template
        $subject = sprintf(__('[%s] Daily Field Operations Report', 'campaign-office'), get_bloginfo('name'));
        $message = $this->format_daily_digest($stats);

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Format daily digest email
     *
     * @param array $stats Statistics array
     * @return string Formatted email content
     */
    private function format_daily_digest($stats) {
        // Format email content
        // This is a simplified version
        ob_start();
        ?>
        Field Operations Daily Report
        =============================

        Canvassing: <?php echo esc_html($stats['canvassing']['doors_knocked']); ?> doors knocked
        Phone Banking: <?php echo esc_html($stats['phone_banking']['calls_made']); ?> calls made
        GOTV: <?php echo esc_html($stats['gotv']['contacts']); ?> voter contacts

        View full report: <?php echo esc_url(admin_url('admin.php?page=cp-field-ops-reports')); ?>
        <?php
        return ob_get_clean();
    }
}

/**
 * Initialize field operations on theme setup
 */
function cp_init_field_operations() {
    // Check if this is premium version
    if (!defined('CAMPAIGNPRESS_PREMIUM') || !CAMPAIGNPRESS_PREMIUM) {
        return;
    }

    CP_Field_Operations_Init::get_instance();
}
add_action('after_setup_theme', 'cp_init_field_operations');

/**
 * Get field operations instance
 *
 * @return CP_Field_Operations_Init
 */
function cp_field_ops() {
    return CP_Field_Operations_Init::get_instance();
}
