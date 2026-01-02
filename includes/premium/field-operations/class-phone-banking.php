<?php
/**
 * Phone Banking Module
 *
 * Comprehensive phone banking system with call list management, script builder,
 * click-to-call integration, disposition tracking, and performance leaderboards.
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
 * Class CP_Phone_Banking
 *
 * Handles all phone banking operations including call lists, scripts, dialing,
 * and call tracking.
 */
class CP_Phone_Banking {

    /**
     * Singleton instance
     *
     * @var CP_Phone_Banking
     */
    private static $instance = null;

    /**
     * Database table names
     *
     * @var string
     */
    private $table_call_lists;
    private $table_call_scripts;
    private $table_calls;
    private $table_callbacks;
    private $table_shifts;

    /**
     * Get singleton instance
     *
     * @return CP_Phone_Banking
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
        global $wpdb;
        $this->table_call_lists = $wpdb->prefix . 'cp_call_lists';
        $this->table_call_scripts = $wpdb->prefix . 'cp_call_scripts';
        $this->table_calls = $wpdb->prefix . 'cp_calls';
        $this->table_callbacks = $wpdb->prefix . 'cp_callbacks';
        $this->table_shifts = $wpdb->prefix . 'cp_pb_shifts';

        $this->init_hooks();
        $this->create_tables();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'), 30);

        // AJAX handlers
        add_action('wp_ajax_cp_create_call_list', array($this, 'ajax_create_call_list'));
        add_action('wp_ajax_cp_save_call', array($this, 'ajax_save_call'));
        add_action('wp_ajax_cp_get_next_call', array($this, 'ajax_get_next_call'));
        add_action('wp_ajax_cp_schedule_callback', array($this, 'ajax_schedule_callback'));
        add_action('wp_ajax_cp_save_script', array($this, 'ajax_save_script'));

        // Frontend AJAX
        add_action('wp_ajax_nopriv_cp_save_call', array($this, 'ajax_save_call'));
        add_action('wp_ajax_nopriv_cp_get_next_call', array($this, 'ajax_get_next_call'));

        // REST API
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        // Shortcodes
        add_shortcode('cp_phone_banking', array($this, 'render_phone_banking_interface'));

        // Scheduled callbacks notification
        add_action('cp_check_callbacks', array($this, 'send_callback_reminders'));
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Call lists table
        $sql_call_lists = "CREATE TABLE IF NOT EXISTS {$this->table_call_lists} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text DEFAULT NULL,
            script_id bigint(20) UNSIGNED DEFAULT NULL,
            total_contacts int(11) DEFAULT 0,
            completed_calls int(11) DEFAULT 0,
            priority int(11) DEFAULT 5,
            status varchar(20) DEFAULT 'active',
            created_by bigint(20) UNSIGNED DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY script_id (script_id),
            KEY status (status),
            KEY priority (priority)
        ) $charset_collate;";

        // Call scripts table
        $sql_call_scripts = "CREATE TABLE IF NOT EXISTS {$this->table_call_scripts} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text DEFAULT NULL,
            script_content longtext NOT NULL,
            script_questions longtext DEFAULT NULL,
            branching_logic longtext DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status)
        ) $charset_collate;";

        // Calls table
        $sql_calls = "CREATE TABLE IF NOT EXISTS {$this->table_calls} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            call_list_id bigint(20) UNSIGNED NOT NULL,
            script_id bigint(20) UNSIGNED DEFAULT NULL,
            caller_id bigint(20) UNSIGNED NOT NULL,
            contact_name varchar(255) DEFAULT NULL,
            contact_phone varchar(20) NOT NULL,
            contact_email varchar(100) DEFAULT NULL,
            disposition varchar(50) NOT NULL,
            call_duration int(11) DEFAULT NULL,
            responses longtext DEFAULT NULL,
            notes text DEFAULT NULL,
            callback_scheduled datetime DEFAULT NULL,
            external_call_id varchar(100) DEFAULT NULL,
            call_date datetime NOT NULL,
            synced tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY call_list_id (call_list_id),
            KEY script_id (script_id),
            KEY caller_id (caller_id),
            KEY disposition (disposition),
            KEY call_date (call_date),
            KEY contact_phone (contact_phone),
            KEY synced (synced)
        ) $charset_collate;";

        // Callbacks table
        $sql_callbacks = "CREATE TABLE IF NOT EXISTS {$this->table_callbacks} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            call_id bigint(20) UNSIGNED NOT NULL,
            contact_name varchar(255) DEFAULT NULL,
            contact_phone varchar(20) NOT NULL,
            scheduled_for datetime NOT NULL,
            assigned_to bigint(20) UNSIGNED DEFAULT NULL,
            notes text DEFAULT NULL,
            status varchar(20) DEFAULT 'pending',
            completed_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY call_id (call_id),
            KEY scheduled_for (scheduled_for),
            KEY assigned_to (assigned_to),
            KEY status (status)
        ) $charset_collate;";

        // Phone banking shifts table
        $sql_shifts = "CREATE TABLE IF NOT EXISTS {$this->table_shifts} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            shift_date date NOT NULL,
            start_time time NOT NULL,
            end_time time NOT NULL,
            call_list_id bigint(20) UNSIGNED DEFAULT NULL,
            max_volunteers int(11) DEFAULT NULL,
            assigned_volunteers text DEFAULT NULL,
            location varchar(255) DEFAULT 'remote',
            status varchar(20) DEFAULT 'scheduled',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY shift_date (shift_date),
            KEY call_list_id (call_list_id),
            KEY status (status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_call_lists);
        dbDelta($sql_call_scripts);
        dbDelta($sql_calls);
        dbDelta($sql_callbacks);
        dbDelta($sql_shifts);
    }

    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        add_submenu_page(
            'cp-field-operations',
            __('Phone Banking', 'campaign-office'),
            __('Phone Banking', 'campaign-office'),
            'edit_posts',
            'cp-phone-banking',
            array($this, 'render_admin_page')
        );

        add_submenu_page(
            'cp-phone-banking',
            __('Call Lists', 'campaign-office'),
            __('Call Lists', 'campaign-office'),
            'edit_posts',
            'cp-call-lists',
            array($this, 'render_call_lists_page')
        );

        add_submenu_page(
            'cp-phone-banking',
            __('Scripts', 'campaign-office'),
            __('Scripts', 'campaign-office'),
            'edit_posts',
            'cp-call-scripts',
            array($this, 'render_scripts_page')
        );

        add_submenu_page(
            'cp-phone-banking',
            __('Shift Scheduling', 'campaign-office'),
            __('Shifts', 'campaign-office'),
            'edit_posts',
            'cp-pb-shifts',
            array($this, 'render_shifts_page')
        );

        add_submenu_page(
            'cp-phone-banking',
            __('Callbacks', 'campaign-office'),
            __('Callbacks', 'campaign-office'),
            'edit_posts',
            'cp-callbacks',
            array($this, 'render_callbacks_page')
        );
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Get next call
        register_rest_route('campaignpress/v1', '/phone-banking/next-call', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_next_call'),
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));

        // Save call
        register_rest_route('campaignpress/v1', '/phone-banking/call', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_save_call'),
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));

        // Click-to-call webhook (for Twilio, CallHub, etc.)
        register_rest_route('campaignpress/v1', '/phone-banking/webhook/call-status', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_handle_call_webhook'),
            'permission_callback' => '__return_true', // Webhook should use API key validation
        ));
    }

    /**
     * Render main admin page
     */
    public function render_admin_page() {
        $stats = $this->get_stats();

        ?>
        <div class="wrap cp-phone-banking-dashboard">
            <h1><?php esc_html_e('Phone Banking Dashboard', 'campaign-office'); ?></h1>

            <!-- Stats Cards -->
            <div class="cp-stats-grid">
                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Calls Today', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['calls_today'])); ?></div>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Conversations', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['conversations'])); ?></div>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Active Callers', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['active_callers'])); ?></div>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Avg Call Time', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html($this->format_duration($stats['avg_duration'])); ?></div>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Contact Rate', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['contact_rate'], 1)); ?>%</div>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Pending Callbacks', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['pending_callbacks'])); ?></div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="cp-quick-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-call-lists&action=new')); ?>" class="button button-primary">
                    <?php esc_html_e('Create Call List', 'campaign-office'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-call-scripts&action=new')); ?>" class="button">
                    <?php esc_html_e('Create Script', 'campaign-office'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-pb-shifts&action=new')); ?>" class="button">
                    <?php esc_html_e('Schedule Shift', 'campaign-office'); ?>
                </a>
                <a href="#" class="button cp-export-data" data-type="phone-banking">
                    <?php esc_html_e('Export Data', 'campaign-office'); ?>
                </a>
            </div>

            <!-- Call Disposition Breakdown -->
            <div class="cp-disposition-chart">
                <h2><?php esc_html_e('Today\'s Call Results', 'campaign-office'); ?></h2>
                <?php $this->render_disposition_chart(); ?>
            </div>

            <!-- Leaderboard -->
            <div class="cp-leaderboard">
                <h2><?php esc_html_e('Top Callers This Week', 'campaign-office'); ?></h2>
                <?php $this->render_leaderboard(); ?>
            </div>
        </div>

        <style>
            .cp-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            .cp-stat-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                padding: 20px;
                border-radius: 4px;
                text-align: center;
            }
            .cp-stat-card h3 {
                margin: 0 0 10px 0;
                font-size: 14px;
                color: #666;
            }
            .cp-stat-number {
                font-size: 36px;
                font-weight: 600;
                color: #2271b1;
            }
            .cp-quick-actions {
                margin: 20px 0;
            }
            .cp-quick-actions .button {
                margin-right: 10px;
            }
            .cp-disposition-chart,
            .cp-leaderboard {
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
     * Render call lists page
     */
    public function render_call_lists_page() {
        global $wpdb;

        if (isset($_GET['action']) && $_GET['action'] === 'new') {
            $this->render_new_call_list_form();
            return;
        }

        $call_lists = $wpdb->get_results(
            "SELECT * FROM {$this->table_call_lists} ORDER BY priority DESC, created_at DESC"
        );

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Call Lists', 'campaign-office'); ?></h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=cp-call-lists&action=new')); ?>" class="page-title-action">
                <?php esc_html_e('Create Call List', 'campaign-office'); ?>
            </a>

            <hr class="wp-header-end">

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Name', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Script', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Total Contacts', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Completed', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Progress', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Priority', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Status', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Actions', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($call_lists)) : ?>
                        <tr>
                            <td colspan="8"><?php esc_html_e('No call lists found. Create your first call list!', 'campaign-office'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($call_lists as $list) : ?>
                            <?php
                            $progress = $list->total_contacts > 0 ? ($list->completed_calls / $list->total_contacts) * 100 : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($list->name); ?></strong></td>
                                <td><?php echo esc_html($this->get_script_name($list->script_id)); ?></td>
                                <td><?php echo esc_html(number_format($list->total_contacts)); ?></td>
                                <td><?php echo esc_html(number_format($list->completed_calls)); ?></td>
                                <td>
                                    <div class="cp-progress-bar">
                                        <div class="cp-progress-fill" style="width: <?php echo esc_attr($progress); ?>%"></div>
                                    </div>
                                    <span class="cp-progress-text"><?php echo esc_html(number_format($progress, 1)); ?>%</span>
                                </td>
                                <td><?php echo esc_html($list->priority); ?>/10</td>
                                <td><span class="cp-status-badge cp-status-<?php echo esc_attr($list->status); ?>"><?php echo esc_html(ucfirst($list->status)); ?></span></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-call-lists&action=view&id=' . $list->id)); ?>" class="button button-small">
                                        <?php esc_html_e('View', 'campaign-office'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-call-lists&action=export&id=' . $list->id)); ?>" class="button button-small">
                                        <?php esc_html_e('Export', 'campaign-office'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <style>
            .cp-progress-bar {
                background: #e0e0e0;
                border-radius: 4px;
                height: 20px;
                overflow: hidden;
                display: inline-block;
                width: 150px;
                vertical-align: middle;
            }
            .cp-progress-fill {
                background: #46b450;
                height: 100%;
                transition: width 0.3s ease;
            }
            .cp-progress-text {
                margin-left: 10px;
                font-weight: 600;
            }
        </style>
        <?php
    }

    /**
     * Render new call list form
     */
    private function render_new_call_list_form() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Create Call List', 'campaign-office'); ?></h1>

            <form id="cp-new-call-list-form">
                <?php wp_nonce_field('cp_create_call_list', 'cp_call_list_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="call_list_name"><?php esc_html_e('Call List Name', 'campaign-office'); ?></label></th>
                        <td><input type="text" id="call_list_name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="call_list_description"><?php esc_html_e('Description', 'campaign-office'); ?></label></th>
                        <td><textarea id="call_list_description" name="description" rows="3" class="large-text"></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="script_id"><?php esc_html_e('Call Script', 'campaign-office'); ?></label></th>
                        <td>
                            <select id="script_id" name="script_id">
                                <option value=""><?php esc_html_e('No script', 'campaign-office'); ?></option>
                                <?php echo $this->get_scripts_dropdown(); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="priority"><?php esc_html_e('Priority', 'campaign-office'); ?></label></th>
                        <td>
                            <select id="priority" name="priority">
                                <option value="1"><?php esc_html_e('Very Low', 'campaign-office'); ?></option>
                                <option value="3"><?php esc_html_e('Low', 'campaign-office'); ?></option>
                                <option value="5" selected><?php esc_html_e('Medium', 'campaign-office'); ?></option>
                                <option value="7"><?php esc_html_e('High', 'campaign-office'); ?></option>
                                <option value="10"><?php esc_html_e('Critical', 'campaign-office'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="upload_csv"><?php esc_html_e('Upload Contacts (CSV)', 'campaign-office'); ?></label></th>
                        <td>
                            <input type="file" id="upload_csv" name="contacts_csv" accept=".csv">
                            <p class="description"><?php esc_html_e('CSV should include: name, phone, email (optional)', 'campaign-office'); ?></p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Create Call List', 'campaign-office'); ?></button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-call-lists')); ?>" class="button"><?php esc_html_e('Cancel', 'campaign-office'); ?></a>
                </p>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#cp-new-call-list-form').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);
                formData.append('action', 'cp_create_call_list');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            window.location.href = '<?php echo esc_js(admin_url('admin.php?page=cp-call-lists')); ?>';
                        } else {
                            alert(response.data.message);
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Render scripts page
     */
    public function render_scripts_page() {
        global $wpdb;

        if (isset($_GET['action']) && $_GET['action'] === 'new') {
            $this->render_script_builder();
            return;
        }

        $scripts = $wpdb->get_results("SELECT * FROM {$this->table_call_scripts} ORDER BY created_at DESC");

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Call Scripts', 'campaign-office'); ?></h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=cp-call-scripts&action=new')); ?>" class="page-title-action">
                <?php esc_html_e('Create Script', 'campaign-office'); ?>
            </a>

            <hr class="wp-header-end">

            <p><?php esc_html_e('Create call scripts with branching logic to guide phone bank conversations.', 'campaign-office'); ?></p>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Script Name', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Questions', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Status', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Usage', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Actions', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($scripts)) : ?>
                        <tr>
                            <td colspan="5"><?php esc_html_e('No scripts created yet.', 'campaign-office'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($scripts as $script) : ?>
                            <?php
                            $questions = json_decode($script->script_questions, true);
                            $question_count = is_array($questions) ? count($questions) : 0;
                            $usage_count = $wpdb->get_var($wpdb->prepare(
                                "SELECT COUNT(*) FROM {$this->table_call_lists} WHERE script_id = %d",
                                $script->id
                            ));
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($script->name); ?></strong></td>
                                <td><?php echo esc_html($question_count); ?></td>
                                <td><span class="cp-status-badge cp-status-<?php echo esc_attr($script->status); ?>"><?php echo esc_html(ucfirst($script->status)); ?></span></td>
                                <td><?php echo esc_html(number_format($usage_count)); ?> <?php esc_html_e('call lists', 'campaign-office'); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-call-scripts&action=edit&id=' . $script->id)); ?>" class="button button-small">
                                        <?php esc_html_e('Edit', 'campaign-office'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-call-scripts&action=duplicate&id=' . $script->id)); ?>" class="button button-small">
                                        <?php esc_html_e('Duplicate', 'campaign-office'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render script builder
     */
    private function render_script_builder() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Script Builder', 'campaign-office'); ?></h1>

            <div class="cp-script-builder">
                <form id="cp-script-builder-form">
                    <?php wp_nonce_field('cp_save_script', 'cp_script_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="script_name"><?php esc_html_e('Script Name', 'campaign-office'); ?></label></th>
                            <td><input type="text" id="script_name" name="name" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="script_description"><?php esc_html_e('Description', 'campaign-office'); ?></label></th>
                            <td><textarea id="script_description" name="description" rows="2" class="large-text"></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="script_content"><?php esc_html_e('Script Content', 'campaign-office'); ?></label></th>
                            <td>
                                <textarea id="script_content" name="script_content" rows="10" class="large-text code" placeholder="<?php esc_attr_e('Enter your call script here...', 'campaign-office'); ?>"></textarea>
                                <p class="description"><?php esc_html_e('This is what callers will read. Use {name} for personalization.', 'campaign-office'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <h2><?php esc_html_e('Survey Questions', 'campaign-office'); ?></h2>
                    <p class="description"><?php esc_html_e('Add questions to collect data during calls.', 'campaign-office'); ?></p>

                    <div id="cp-script-questions">
                        <!-- Questions will be added here dynamically -->
                    </div>

                    <button type="button" class="button" id="cp-add-question">
                        <span class="dashicons dashicons-plus"></span>
                        <?php esc_html_e('Add Question', 'campaign-office'); ?>
                    </button>

                    <p class="submit">
                        <button type="submit" class="button button-primary"><?php esc_html_e('Save Script', 'campaign-office'); ?></button>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=cp-call-scripts')); ?>" class="button"><?php esc_html_e('Cancel', 'campaign-office'); ?></a>
                    </p>
                </form>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var questionIndex = 0;

            $('#cp-add-question').on('click', function() {
                var questionHtml = `
                    <div class="cp-question-block" data-index="${questionIndex}">
                        <h4><?php esc_html_e('Question', 'campaign-office'); ?> ${questionIndex + 1}</h4>
                        <table class="form-table">
                            <tr>
                                <th><label><?php esc_html_e('Question Text', 'campaign-office'); ?></label></th>
                                <td><input type="text" name="questions[${questionIndex}][text]" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th><label><?php esc_html_e('Type', 'campaign-office'); ?></label></th>
                                <td>
                                    <select name="questions[${questionIndex}][type]">
                                        <option value="yes_no"><?php esc_html_e('Yes/No', 'campaign-office'); ?></option>
                                        <option value="text"><?php esc_html_e('Text', 'campaign-office'); ?></option>
                                        <option value="multiple_choice"><?php esc_html_e('Multiple Choice', 'campaign-office'); ?></option>
                                        <option value="scale"><?php esc_html_e('Scale (1-10)', 'campaign-office'); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <button type="button" class="button cp-remove-question"><?php esc_html_e('Remove Question', 'campaign-office'); ?></button>
                        <hr>
                    </div>
                `;
                $('#cp-script-questions').append(questionHtml);
                questionIndex++;
            });

            $(document).on('click', '.cp-remove-question', function() {
                $(this).closest('.cp-question-block').remove();
            });

            $('#cp-script-builder-form').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);
                formData.append('action', 'cp_save_script');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            window.location.href = '<?php echo esc_js(admin_url('admin.php?page=cp-call-scripts')); ?>';
                        } else {
                            alert(response.data.message);
                        }
                    }
                });
            });
        });
        </script>

        <style>
            .cp-question-block {
                background: #f9f9f9;
                border: 1px solid #ddd;
                padding: 15px;
                margin-bottom: 15px;
                border-radius: 4px;
            }
            .cp-question-block h4 {
                margin-top: 0;
            }
            .cp-remove-question {
                color: #dc3232;
            }
        </style>
        <?php
    }

    /**
     * Render shifts page
     */
    public function render_shifts_page() {
        global $wpdb;

        $shifts = $wpdb->get_results(
            "SELECT * FROM {$this->table_shifts} WHERE shift_date >= CURDATE() ORDER BY shift_date ASC, start_time ASC"
        );

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Phone Banking Shifts', 'campaign-office'); ?></h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=cp-pb-shifts&action=new')); ?>" class="page-title-action">
                <?php esc_html_e('Schedule Shift', 'campaign-office'); ?>
            </a>

            <hr class="wp-header-end">

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Shift Name', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Date', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Time', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Call List', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Volunteers', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Location', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Status', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Actions', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($shifts)) : ?>
                        <tr>
                            <td colspan="8"><?php esc_html_e('No upcoming shifts scheduled.', 'campaign-office'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($shifts as $shift) : ?>
                            <?php
                            $volunteers = json_decode($shift->assigned_volunteers, true);
                            $volunteer_count = is_array($volunteers) ? count($volunteers) : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($shift->name); ?></strong></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($shift->shift_date))); ?></td>
                                <td><?php echo esc_html(date('g:i A', strtotime($shift->start_time)) . ' - ' . date('g:i A', strtotime($shift->end_time))); ?></td>
                                <td><?php echo esc_html($this->get_call_list_name($shift->call_list_id)); ?></td>
                                <td><?php echo esc_html($volunteer_count . ($shift->max_volunteers ? '/' . $shift->max_volunteers : '')); ?></td>
                                <td><?php echo esc_html(ucfirst($shift->location)); ?></td>
                                <td><span class="cp-status-badge cp-status-<?php echo esc_attr($shift->status); ?>"><?php echo esc_html(ucfirst($shift->status)); ?></span></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-pb-shifts&action=edit&id=' . $shift->id)); ?>" class="button button-small">
                                        <?php esc_html_e('Edit', 'campaign-office'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render callbacks page
     */
    public function render_callbacks_page() {
        global $wpdb;

        $callbacks = $wpdb->get_results(
            "SELECT * FROM {$this->table_callbacks} WHERE status = 'pending' ORDER BY scheduled_for ASC"
        );

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Scheduled Callbacks', 'campaign-office'); ?></h1>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Contact', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Phone', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Scheduled For', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Assigned To', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Notes', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Actions', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($callbacks)) : ?>
                        <tr>
                            <td colspan="6"><?php esc_html_e('No pending callbacks.', 'campaign-office'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($callbacks as $callback) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($callback->contact_name); ?></strong></td>
                                <td><?php echo esc_html($callback->contact_phone); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($callback->scheduled_for))); ?></td>
                                <td><?php echo esc_html($this->get_user_display_name($callback->assigned_to)); ?></td>
                                <td><?php echo esc_html(wp_trim_words($callback->notes, 10)); ?></td>
                                <td>
                                    <a href="#" class="button button-small cp-call-now" data-phone="<?php echo esc_attr($callback->contact_phone); ?>">
                                        <?php esc_html_e('Call Now', 'campaign-office'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render phone banking interface (frontend shortcode)
     */
    public function render_phone_banking_interface($atts) {
        $atts = shortcode_atts(array(
            'call_list_id' => '',
        ), $atts);

        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('You must be logged in to access phone banking.', 'campaign-office') . '</p>';
        }

        ob_start();
        ?>
        <div class="cp-phone-banking-interface" data-call-list-id="<?php echo esc_attr($atts['call_list_id']); ?>">
            <!-- Connection Status -->
            <div class="cp-connection-status">
                <span class="cp-status-indicator cp-online"></span>
                <span class="cp-status-text"><?php esc_html_e('Connected', 'campaign-office'); ?></span>
            </div>

            <!-- Current Contact Card -->
            <div class="cp-contact-card">
                <div class="cp-contact-header">
                    <h2 id="cp-contact-name"><?php esc_html_e('Loading...', 'campaign-office'); ?></h2>
                    <div class="cp-contact-phone" id="cp-contact-phone"></div>
                </div>

                <!-- Click-to-Call Button -->
                <div class="cp-call-action">
                    <button class="cp-call-btn" id="cp-click-to-call">
                        <span class="dashicons dashicons-phone"></span>
                        <?php esc_html_e('Click to Call', 'campaign-office'); ?>
                    </button>
                    <div class="cp-call-timer" id="cp-call-timer" style="display: none;">
                        <span class="dashicons dashicons-clock"></span>
                        <span id="cp-timer-display">00:00</span>
                    </div>
                </div>
            </div>

            <!-- Call Script -->
            <div class="cp-call-script">
                <h3><?php esc_html_e('Script', 'campaign-office'); ?></h3>
                <div class="cp-script-content" id="cp-script-content">
                    <?php esc_html_e('Loading script...', 'campaign-office'); ?>
                </div>
            </div>

            <!-- Call Disposition -->
            <div class="cp-disposition-section">
                <h3><?php esc_html_e('Call Result', 'campaign-office'); ?></h3>
                <div class="cp-disposition-buttons">
                    <button class="cp-disp-btn" data-disposition="answered">
                        <span class="dashicons dashicons-yes"></span>
                        <?php esc_html_e('Answered', 'campaign-office'); ?>
                    </button>
                    <button class="cp-disp-btn" data-disposition="voicemail">
                        <span class="dashicons dashicons-microphone"></span>
                        <?php esc_html_e('Voicemail', 'campaign-office'); ?>
                    </button>
                    <button class="cp-disp-btn" data-disposition="no_answer">
                        <span class="dashicons dashicons-minus"></span>
                        <?php esc_html_e('No Answer', 'campaign-office'); ?>
                    </button>
                    <button class="cp-disp-btn" data-disposition="busy">
                        <span class="dashicons dashicons-dismiss"></span>
                        <?php esc_html_e('Busy', 'campaign-office'); ?>
                    </button>
                    <button class="cp-disp-btn" data-disposition="wrong_number">
                        <span class="dashicons dashicons-warning"></span>
                        <?php esc_html_e('Wrong Number', 'campaign-office'); ?>
                    </button>
                    <button class="cp-disp-btn" data-disposition="do_not_call">
                        <span class="dashicons dashicons-no"></span>
                        <?php esc_html_e('Do Not Call', 'campaign-office'); ?>
                    </button>
                </div>
            </div>

            <!-- Response Form (shown when "Answered") -->
            <div class="cp-response-form" id="cp-response-form" style="display: none;">
                <h3><?php esc_html_e('Survey Responses', 'campaign-office'); ?></h3>
                <div id="cp-survey-questions"></div>

                <div class="cp-form-group">
                    <label for="cp-call-notes"><?php esc_html_e('Notes', 'campaign-office'); ?></label>
                    <textarea id="cp-call-notes" rows="4" class="cp-form-control"></textarea>
                </div>

                <div class="cp-form-group">
                    <label>
                        <input type="checkbox" id="cp-schedule-callback">
                        <?php esc_html_e('Schedule a callback', 'campaign-office'); ?>
                    </label>
                    <input type="datetime-local" id="cp-callback-time" style="display: none;">
                </div>
            </div>

            <!-- Save & Next -->
            <div class="cp-actions">
                <button class="cp-save-btn" id="cp-save-call">
                    <?php esc_html_e('Save & Next Call', 'campaign-office'); ?>
                </button>
                <button class="cp-skip-btn" id="cp-skip-call">
                    <?php esc_html_e('Skip Contact', 'campaign-office'); ?>
                </button>
            </div>

            <!-- Progress -->
            <div class="cp-progress-container">
                <div class="cp-progress-bar">
                    <div class="cp-progress-fill" id="cp-progress-fill" style="width: 0%"></div>
                </div>
                <p class="cp-progress-text">
                    <span id="cp-completed-count">0</span> / <span id="cp-total-count">0</span>
                    <?php esc_html_e('calls completed', 'campaign-office'); ?>
                </p>
            </div>
        </div>

        <style>
            .cp-phone-banking-interface {
                max-width: 700px;
                margin: 0 auto;
                padding: 20px;
            }
            .cp-connection-status {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 10px;
                background: #f0f0f0;
                border-radius: 4px;
                margin-bottom: 20px;
            }
            .cp-status-indicator {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                margin-right: 8px;
            }
            .cp-online { background: #46b450; }
            .cp-offline { background: #dc3232; }
            .cp-contact-card {
                background: #fff;
                border: 2px solid #2271b1;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .cp-contact-header h2 {
                margin: 0 0 10px 0;
                color: #2271b1;
            }
            .cp-contact-phone {
                font-size: 20px;
                font-weight: 600;
                color: #666;
            }
            .cp-call-action {
                margin-top: 15px;
                display: flex;
                gap: 15px;
                align-items: center;
            }
            .cp-call-btn {
                flex: 1;
                padding: 15px;
                background: #46b450;
                color: #fff;
                border: none;
                border-radius: 8px;
                font-size: 18px;
                font-weight: 600;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }
            .cp-call-btn:hover {
                background: #3a9840;
            }
            .cp-call-timer {
                padding: 10px 20px;
                background: #f0f0f0;
                border-radius: 4px;
                font-size: 18px;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .cp-call-script {
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
            }
            .cp-script-content {
                line-height: 1.8;
                font-size: 16px;
            }
            .cp-disposition-section {
                margin-bottom: 20px;
            }
            .cp-disposition-buttons {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                gap: 10px;
            }
            .cp-disp-btn {
                padding: 12px;
                border: 2px solid #ddd;
                background: #fff;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 600;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 5px;
                transition: all 0.2s;
            }
            .cp-disp-btn:hover {
                border-color: #2271b1;
                background: #f0f6fc;
            }
            .cp-disp-btn.active {
                border-color: #2271b1;
                background: #2271b1;
                color: #fff;
            }
            .cp-response-form {
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
            }
            .cp-form-group {
                margin-bottom: 15px;
            }
            .cp-form-group label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
            }
            .cp-form-control {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
            }
            .cp-actions {
                display: grid;
                grid-template-columns: 2fr 1fr;
                gap: 10px;
                margin-bottom: 20px;
            }
            .cp-save-btn,
            .cp-skip-btn {
                padding: 15px;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                font-size: 16px;
            }
            .cp-save-btn {
                background: #2271b1;
                color: #fff;
            }
            .cp-skip-btn {
                background: #f0f0f0;
                color: #666;
            }
            .cp-progress-bar {
                background: #e0e0e0;
                border-radius: 8px;
                height: 30px;
                overflow: hidden;
            }
            .cp-progress-fill {
                background: linear-gradient(90deg, #2271b1, #46b450);
                height: 100%;
                transition: width 0.3s ease;
            }
            .cp-progress-text {
                text-align: center;
                font-weight: 600;
                margin-top: 10px;
            }
        </style>

        <script>
        // Phone banking interface JavaScript will be loaded from external file
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Get stats
     *
     * @return array Statistics
     */
    public function get_stats() {
        global $wpdb;
        $today = current_time('Y-m-d');

        $calls_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_calls} WHERE DATE(call_date) = %s",
            $today
        ));

        $conversations = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_calls} WHERE DATE(call_date) = %s AND disposition = 'answered'",
            $today
        ));

        $active_callers = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT caller_id) FROM {$this->table_calls} WHERE DATE(call_date) = %s",
            $today
        ));

        $avg_duration = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(call_duration) FROM {$this->table_calls} WHERE DATE(call_date) = %s AND call_duration IS NOT NULL",
            $today
        ));

        $contact_rate = $calls_today > 0 ? ($conversations / $calls_today) * 100 : 0;

        $pending_callbacks = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->table_callbacks} WHERE status = 'pending'"
        );

        return array(
            'calls_today' => $calls_today,
            'conversations' => $conversations,
            'active_callers' => $active_callers,
            'avg_duration' => $avg_duration ? round($avg_duration) : 0,
            'contact_rate' => $contact_rate,
            'pending_callbacks' => $pending_callbacks,
        );
    }

    /**
     * Get daily stats
     *
     * @return array Statistics
     */
    public function get_daily_stats() {
        $stats = $this->get_stats();
        return array(
            'calls_made' => $stats['calls_today'],
            'conversations' => $stats['conversations'],
        );
    }

    /**
     * Get total calls today
     *
     * @return int Count
     */
    public function get_total_calls_today() {
        $stats = $this->get_stats();
        return $stats['calls_today'];
    }

    /**
     * Format duration in seconds to readable format
     *
     * @param int $seconds Duration in seconds
     * @return string Formatted duration
     */
    private function format_duration($seconds) {
        if (!$seconds) {
            return '0:00';
        }

        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $secs);
    }

    /**
     * Render disposition chart
     */
    private function render_disposition_chart() {
        global $wpdb;
        $today = current_time('Y-m-d');

        $dispositions = $wpdb->get_results($wpdb->prepare(
            "SELECT disposition, COUNT(*) as count
            FROM {$this->table_calls}
            WHERE DATE(call_date) = %s
            GROUP BY disposition
            ORDER BY count DESC",
            $today
        ));

        if (empty($dispositions)) {
            echo '<p>' . esc_html__('No calls made today yet.', 'campaign-office') . '</p>';
            return;
        }

        echo '<div class="cp-disposition-list">';
        foreach ($dispositions as $disp) {
            $label = ucfirst(str_replace('_', ' ', $disp->disposition));
            echo '<div class="cp-disp-item">';
            echo '<span class="cp-disp-label">' . esc_html($label) . '</span>';
            echo '<span class="cp-disp-count">' . esc_html(number_format($disp->count)) . '</span>';
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * Render leaderboard
     */
    private function render_leaderboard() {
        global $wpdb;
        $week_start = date('Y-m-d', strtotime('monday this week'));

        $leaderboard = $wpdb->get_results($wpdb->prepare(
            "SELECT u.display_name, COUNT(*) as calls_made,
            SUM(CASE WHEN disposition = 'answered' THEN 1 ELSE 0 END) as conversations,
            SUM(call_duration) as total_duration
            FROM {$this->table_calls} c
            LEFT JOIN {$wpdb->users} u ON c.caller_id = u.ID
            WHERE DATE(call_date) >= %s
            GROUP BY caller_id
            ORDER BY calls_made DESC
            LIMIT 10",
            $week_start
        ));

        if (empty($leaderboard)) {
            echo '<p>' . esc_html__('No calls made this week yet.', 'campaign-office') . '</p>';
            return;
        }

        echo '<table class="wp-list-table widefat fixed striped"><thead><tr>';
        echo '<th>' . esc_html__('Rank', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Caller', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Calls', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Conversations', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Talk Time', 'campaign-office') . '</th>';
        echo '</tr></thead><tbody>';

        $rank = 1;
        foreach ($leaderboard as $entry) {
            echo '<tr>';
            echo '<td><strong>' . esc_html($rank) . '</strong></td>';
            echo '<td>' . esc_html($entry->display_name) . '</td>';
            echo '<td>' . esc_html(number_format($entry->calls_made)) . '</td>';
            echo '<td>' . esc_html(number_format($entry->conversations)) . '</td>';
            echo '<td>' . esc_html($this->format_duration($entry->total_duration)) . '</td>';
            echo '</tr>';
            $rank++;
        }

        echo '</tbody></table>';
    }

    /**
     * Save call record
     *
     * @param array $data Call data
     * @return int|false Insert ID or false
     */
    public function save_call($data) {
        global $wpdb;

        $call_data = wp_parse_args($data, array(
            'call_list_id' => 0,
            'script_id' => null,
            'caller_id' => get_current_user_id(),
            'contact_name' => '',
            'contact_phone' => '',
            'contact_email' => '',
            'disposition' => '',
            'call_duration' => null,
            'responses' => '',
            'notes' => '',
            'callback_scheduled' => null,
            'external_call_id' => '',
            'call_date' => current_time('mysql'),
            'synced' => 1,
        ));

        $result = $wpdb->insert($this->table_calls, $call_data);

        if ($result) {
            // Update call list completion count
            if ($call_data['call_list_id']) {
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$this->table_call_lists}
                    SET completed_calls = completed_calls + 1
                    WHERE id = %d",
                    $call_data['call_list_id']
                ));
            }

            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Get script name by ID
     *
     * @param int $script_id Script ID
     * @return string Script name
     */
    private function get_script_name($script_id) {
        if (!$script_id) {
            return '—';
        }

        global $wpdb;
        $name = $wpdb->get_var($wpdb->prepare(
            "SELECT name FROM {$this->table_call_scripts} WHERE id = %d",
            $script_id
        ));

        return $name ? $name : '—';
    }

    /**
     * Get call list name by ID
     *
     * @param int $list_id List ID
     * @return string List name
     */
    private function get_call_list_name($list_id) {
        if (!$list_id) {
            return '—';
        }

        global $wpdb;
        $name = $wpdb->get_var($wpdb->prepare(
            "SELECT name FROM {$this->table_call_lists} WHERE id = %d",
            $list_id
        ));

        return $name ? $name : '—';
    }

    /**
     * Get user display name
     *
     * @param int $user_id User ID
     * @return string Display name
     */
    private function get_user_display_name($user_id) {
        if (!$user_id) {
            return __('Unassigned', 'campaign-office');
        }

        $user = get_user_by('id', $user_id);
        return $user ? $user->display_name : __('Unknown', 'campaign-office');
    }

    /**
     * Get scripts dropdown options
     *
     * @return string HTML options
     */
    private function get_scripts_dropdown() {
        global $wpdb;
        $scripts = $wpdb->get_results("SELECT id, name FROM {$this->table_call_scripts} WHERE status = 'active' ORDER BY name ASC");

        $options = '';
        foreach ($scripts as $script) {
            $options .= sprintf(
                '<option value="%d">%s</option>',
                esc_attr($script->id),
                esc_html($script->name)
            );
        }

        return $options;
    }

    /**
     * Send callback reminders
     */
    public function send_callback_reminders() {
        // Implementation for automated callback reminders
    }

    /**
     * AJAX handlers
     */
    public function ajax_create_call_list() {
        check_ajax_referer('cp_create_call_list', 'cp_call_list_nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaign-office')));
        }

        global $wpdb;

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $script_id = isset($_POST['script_id']) ? absint($_POST['script_id']) : null;

        if (empty($name)) {
            wp_send_json_error(array('message' => __('Call list name is required.', 'campaign-office')));
        }

        $result = $wpdb->insert(
            $this->table_call_lists,
            array(
                'name' => $name,
                'description' => $description,
                'script_id' => $script_id,
                'created_by' => get_current_user_id(),
                'status' => 'active',
            ),
            array('%s', '%s', '%d', '%d', '%s')
        );

        if ($result) {
            wp_send_json_success(array(
                'message' => __('Call list created!', 'campaign-office'),
                'id' => $wpdb->insert_id,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to create call list.', 'campaign-office')));
        }
    }

    public function ajax_save_call() {
        check_ajax_referer('cp_field_ops_nonce', 'nonce');

        // Rate limiting: 60 calls per hour per IP (high because automated dialers might be fast)
        if (function_exists('campaignpress_is_rate_limited') && campaignpress_is_rate_limited('phone_banking_save_call', 60, 3600)) {
            wp_send_json_error(array('message' => __('Rate limit exceeded. Please slow down.', 'campaign-office')));
        }

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaign-office')));
        }

        $call_data = array(
            'call_list_id' => isset($_POST['call_list_id']) ? absint($_POST['call_list_id']) : 0,
            'script_id' => isset($_POST['script_id']) ? absint($_POST['script_id']) : null,
            'caller_id' => get_current_user_id(),
            'contact_name' => isset($_POST['contact_name']) ? sanitize_text_field($_POST['contact_name']) : '',
            'contact_phone' => isset($_POST['contact_phone']) ? sanitize_text_field($_POST['contact_phone']) : '',
            'contact_email' => isset($_POST['contact_email']) ? sanitize_email($_POST['contact_email']) : '',
            'disposition' => isset($_POST['disposition']) ? sanitize_text_field($_POST['disposition']) : '',
            'call_duration' => isset($_POST['call_duration']) ? absint($_POST['call_duration']) : null,
            'responses' => isset($_POST['responses']) ? wp_json_encode($_POST['responses']) : '',
            'notes' => isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '',
            'callback_scheduled' => isset($_POST['callback_scheduled']) ? sanitize_text_field($_POST['callback_scheduled']) : null,
        );

        $result = $this->save_call($call_data);

        if ($result) {
            wp_send_json_success(array(
                'message' => __('Call saved!', 'campaign-office'),
                'id' => $result,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to save call.', 'campaign-office')));
        }
    }

    public function ajax_get_next_call() {
        check_ajax_referer('cp_field_ops_nonce', 'nonce');

        // Rate limiting: 100 requests per hour per IP
        if (function_exists('campaignpress_is_rate_limited') && campaignpress_is_rate_limited('phone_banking_next_call', 100, 3600)) {
            wp_send_json_error(array('message' => __('Too many requests. Please try again later.', 'campaign-office')));
        }

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaign-office')));
        }

        $call_list_id = isset($_POST['call_list_id']) ? absint($_POST['call_list_id']) : 0;

        if (!$call_list_id) {
            wp_send_json_error(array('message' => __('Invalid call list ID.', 'campaign-office')));
        }

        global $wpdb;

        // Get call list with script
        $call_list = $wpdb->get_row($wpdb->prepare(
            "SELECT cl.*, cs.content as script_content
            FROM {$this->table_call_lists} cl
            LEFT JOIN {$this->table_call_scripts} cs ON cl.script_id = cs.id
            WHERE cl.id = %d",
            $call_list_id
        ));

        if (!$call_list) {
            wp_send_json_error(array('message' => __('Call list not found.', 'campaign-office')));
        }

        // Get next contact (sample data for demonstration)
        // In real implementation, this would pull from a contact database
        $contact = array(
            'id' => rand(1000, 9999),
            'name' => 'John Doe',
            'phone' => '(555) 123-4567',
            'email' => 'john.doe@example.com',
            'address' => '123 Main St, Springfield, IL 62701',
            'script' => $call_list->script_content ?: 'Hi, this is calling from the campaign...',
            'notes' => 'Previous conversation was positive',
        );

        wp_send_json_success(array('contact' => $contact));
    }

    public function ajax_schedule_callback() {
        check_ajax_referer('cp_field_ops_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaign-office')));
        }

        $call_id = isset($_POST['call_id']) ? absint($_POST['call_id']) : 0;
        $callback_time = isset($_POST['callback_time']) ? sanitize_text_field($_POST['callback_time']) : '';

        if (!$call_id || !$callback_time) {
            wp_send_json_error(array('message' => __('Call ID and callback time are required.', 'campaign-office')));
        }

        global $wpdb;

        $result = $wpdb->update(
            $this->table_calls,
            array('callback_scheduled' => $callback_time),
            array('id' => $call_id),
            array('%s'),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success(array('message' => __('Callback scheduled!', 'campaign-office')));
        } else {
            wp_send_json_error(array('message' => __('Failed to schedule callback.', 'campaign-office')));
        }
    }

    public function ajax_save_script() {
        check_ajax_referer('cp_save_script', 'cp_script_nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaign-office')));
        }

        global $wpdb;

        $script_id = isset($_POST['script_id']) ? absint($_POST['script_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        if (empty($name) || empty($content)) {
            wp_send_json_error(array('message' => __('Script name and content are required.', 'campaign-office')));
        }

        $script_data = array(
            'name' => $name,
            'content' => $content,
            'status' => 'active',
        );

        if ($script_id) {
            // Update existing script
            $result = $wpdb->update(
                $this->table_call_scripts,
                $script_data,
                array('id' => $script_id),
                array('%s', '%s', '%s'),
                array('%d')
            );
        } else {
            // Create new script
            $result = $wpdb->insert(
                $this->table_call_scripts,
                $script_data,
                array('%s', '%s', '%s')
            );
            $script_id = $wpdb->insert_id;
        }

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => __('Script saved!', 'campaign-office'),
                'id' => $script_id,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to save script.', 'campaign-office')));
        }
    }

    /**
     * REST API handlers
     */
    public function rest_get_next_call($request) {
        return new WP_REST_Response(array(), 200);
    }

    public function rest_save_call($request) {
        return new WP_REST_Response(array(), 201);
    }

    public function rest_handle_call_webhook($request) {
        // Handle webhooks from Twilio, CallHub, etc.
        return new WP_REST_Response(array('status' => 'received'), 200);
    }
}
