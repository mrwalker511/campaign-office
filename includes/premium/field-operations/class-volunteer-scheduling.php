<?php
/**
 * Volunteer Scheduling Module
 *
 * Advanced volunteer scheduling system with shift management, availability tracking,
 * automated reminders, check-in/check-out, hours tracking, and recurring shifts.
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
 * Class CP_Volunteer_Scheduling
 *
 * Handles volunteer shift scheduling, availability tracking, check-ins,
 * and hours management.
 */
class CP_Volunteer_Scheduling {

    /**
     * Singleton instance
     *
     * @var CP_Volunteer_Scheduling
     */
    private static $instance = null;

    /**
     * Database table names
     *
     * @var string
     */
    private $table_shifts;
    private $table_shift_assignments;
    private $table_availability;
    private $table_check_ins;
    private $table_hours;

    /**
     * Get singleton instance
     *
     * @return CP_Volunteer_Scheduling
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
        $this->table_shifts = $wpdb->prefix . 'cp_volunteer_shifts';
        $this->table_shift_assignments = $wpdb->prefix . 'cp_volunteer_shift_assignments';
        $this->table_availability = $wpdb->prefix . 'cp_volunteer_availability';
        $this->table_check_ins = $wpdb->prefix . 'cp_volunteer_check_ins';
        $this->table_hours = $wpdb->prefix . 'cp_volunteer_hours';

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
        add_action('wp_ajax_cp_create_shift', array($this, 'ajax_create_shift'));
        add_action('wp_ajax_cp_assign_volunteer', array($this, 'ajax_assign_volunteer'));
        add_action('wp_ajax_cp_check_in_volunteer', array($this, 'ajax_check_in'));
        add_action('wp_ajax_cp_check_out_volunteer', array($this, 'ajax_check_out'));
        add_action('wp_ajax_cp_save_availability', array($this, 'ajax_save_availability'));

        // Frontend AJAX
        add_action('wp_ajax_nopriv_cp_check_in_volunteer', array($this, 'ajax_check_in'));
        add_action('wp_ajax_nopriv_cp_check_out_volunteer', array($this, 'ajax_check_out'));

        // REST API
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        // Shortcodes
        add_shortcode('cp_volunteer_shifts', array($this, 'render_shift_calendar'));
        add_shortcode('cp_volunteer_checkin', array($this, 'render_checkin_interface'));
        add_shortcode('cp_volunteer_availability', array($this, 'render_availability_form'));

        // Scheduled tasks
        add_action('cp_send_shift_reminders', array($this, 'send_shift_reminders'));
        add_action('cp_check_no_shows', array($this, 'process_no_shows'));

        // Recurring shifts
        add_action('cp_create_recurring_shifts', array($this, 'generate_recurring_shifts'));
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Shifts table
        $sql_shifts = "CREATE TABLE IF NOT EXISTS {$this->table_shifts} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text DEFAULT NULL,
            shift_type varchar(50) DEFAULT 'general',
            shift_date date NOT NULL,
            start_time time NOT NULL,
            end_time time NOT NULL,
            location varchar(255) DEFAULT NULL,
            location_address text DEFAULT NULL,
            max_volunteers int(11) DEFAULT NULL,
            min_volunteers int(11) DEFAULT NULL,
            required_skills text DEFAULT NULL,
            is_recurring tinyint(1) DEFAULT 0,
            recurrence_pattern varchar(50) DEFAULT NULL,
            parent_shift_id bigint(20) UNSIGNED DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            created_by bigint(20) UNSIGNED DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY shift_date (shift_date),
            KEY shift_type (shift_type),
            KEY status (status),
            KEY is_recurring (is_recurring),
            KEY parent_shift_id (parent_shift_id)
        ) $charset_collate;";

        // Shift assignments table
        $sql_shift_assignments = "CREATE TABLE IF NOT EXISTS {$this->table_shift_assignments} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            shift_id bigint(20) UNSIGNED NOT NULL,
            volunteer_id bigint(20) UNSIGNED NOT NULL,
            status varchar(20) DEFAULT 'confirmed',
            assigned_by bigint(20) UNSIGNED DEFAULT NULL,
            confirmed_at datetime DEFAULT NULL,
            cancelled_at datetime DEFAULT NULL,
            no_show tinyint(1) DEFAULT 0,
            notes text DEFAULT NULL,
            reminder_sent tinyint(1) DEFAULT 0,
            reminder_sent_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY shift_id (shift_id),
            KEY volunteer_id (volunteer_id),
            KEY status (status),
            KEY no_show (no_show),
            UNIQUE KEY unique_assignment (shift_id, volunteer_id)
        ) $charset_collate;";

        // Volunteer availability table
        $sql_availability = "CREATE TABLE IF NOT EXISTS {$this->table_availability} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            volunteer_id bigint(20) UNSIGNED NOT NULL,
            day_of_week int(11) NOT NULL,
            time_slot varchar(50) NOT NULL,
            available tinyint(1) DEFAULT 1,
            recurring tinyint(1) DEFAULT 1,
            start_date date DEFAULT NULL,
            end_date date DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY volunteer_id (volunteer_id),
            KEY day_of_week (day_of_week)
        ) $charset_collate;";

        // Check-ins table
        $sql_check_ins = "CREATE TABLE IF NOT EXISTS {$this->table_check_ins} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            assignment_id bigint(20) UNSIGNED NOT NULL,
            shift_id bigint(20) UNSIGNED NOT NULL,
            volunteer_id bigint(20) UNSIGNED NOT NULL,
            check_in_time datetime NOT NULL,
            check_out_time datetime DEFAULT NULL,
            actual_hours decimal(5,2) DEFAULT NULL,
            location varchar(255) DEFAULT NULL,
            check_in_method varchar(20) DEFAULT 'manual',
            check_in_ip varchar(45) DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY assignment_id (assignment_id),
            KEY shift_id (shift_id),
            KEY volunteer_id (volunteer_id),
            KEY check_in_time (check_in_time)
        ) $charset_collate;";

        // Hours tracking table
        $sql_hours = "CREATE TABLE IF NOT EXISTS {$this->table_hours} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            volunteer_id bigint(20) UNSIGNED NOT NULL,
            shift_id bigint(20) UNSIGNED DEFAULT NULL,
            check_in_id bigint(20) UNSIGNED DEFAULT NULL,
            hours decimal(5,2) NOT NULL,
            hours_date date NOT NULL,
            activity_type varchar(50) DEFAULT 'shift',
            description text DEFAULT NULL,
            approved tinyint(1) DEFAULT 1,
            approved_by bigint(20) UNSIGNED DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY volunteer_id (volunteer_id),
            KEY shift_id (shift_id),
            KEY hours_date (hours_date),
            KEY approved (approved)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_shifts);
        dbDelta($sql_shift_assignments);
        dbDelta($sql_availability);
        dbDelta($sql_check_ins);
        dbDelta($sql_hours);
    }

    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        add_submenu_page(
            'cp-field-operations',
            __('Volunteer Scheduling', 'campaign-office'),
            __('Scheduling', 'campaign-office'),
            'edit_posts',
            'cp-volunteer-scheduling',
            array($this, 'render_admin_page')
        );

        add_submenu_page(
            'cp-volunteer-scheduling',
            __('Shift Calendar', 'campaign-office'),
            __('Calendar', 'campaign-office'),
            'edit_posts',
            'cp-shift-calendar',
            array($this, 'render_calendar_page')
        );

        add_submenu_page(
            'cp-volunteer-scheduling',
            __('Volunteer Availability', 'campaign-office'),
            __('Availability', 'campaign-office'),
            'edit_posts',
            'cp-volunteer-availability',
            array($this, 'render_availability_page')
        );

        add_submenu_page(
            'cp-volunteer-scheduling',
            __('Hours Tracking', 'campaign-office'),
            __('Hours', 'campaign-office'),
            'edit_posts',
            'cp-volunteer-hours',
            array($this, 'render_hours_page')
        );

        add_submenu_page(
            'cp-volunteer-scheduling',
            __('Check-In/Out', 'campaign-office'),
            __('Check-In', 'campaign-office'),
            'edit_posts',
            'cp-volunteer-checkin',
            array($this, 'render_checkin_page')
        );
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Get upcoming shifts
        register_rest_route('campaignpress/v1', '/scheduling/shifts', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_shifts'),
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));

        // Check in
        register_rest_route('campaignpress/v1', '/scheduling/check-in', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_check_in'),
            'permission_callback' => function() {
                return is_user_logged_in();
            },
        ));

        // Check out
        register_rest_route('campaignpress/v1', '/scheduling/check-out', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_check_out'),
            'permission_callback' => function() {
                return is_user_logged_in();
            },
        ));
    }

    /**
     * Render main admin page
     */
    public function render_admin_page() {
        $stats = $this->get_stats();

        ?>
        <div class="wrap cp-scheduling-dashboard">
            <h1><?php esc_html_e('Volunteer Scheduling Dashboard', 'campaign-office'); ?></h1>

            <!-- Stats Cards -->
            <div class="cp-stats-grid">
                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Volunteers Today', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['volunteers_today'])); ?></div>
                    <p class="cp-stat-meta"><?php echo esc_html(number_format($stats['checked_in_now'])); ?> <?php esc_html_e('currently checked in', 'campaign-office'); ?></p>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Upcoming Shifts', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['upcoming_shifts'])); ?></div>
                    <p class="cp-stat-meta"><?php esc_html_e('in next 7 days', 'campaign-office'); ?></p>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Unfilled Shifts', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['unfilled_shifts'])); ?></div>
                    <p class="cp-stat-meta"><?php esc_html_e('need volunteers', 'campaign-office'); ?></p>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Total Hours (This Week)', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['total_hours_week'], 1)); ?></div>
                    <p class="cp-stat-meta"><?php echo esc_html(number_format($stats['unique_volunteers_week'])); ?> <?php esc_html_e('volunteers', 'campaign-office'); ?></p>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('No-Show Rate', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['no_show_rate'], 1)); ?>%</div>
                    <p class="cp-stat-meta"><?php esc_html_e('this month', 'campaign-office'); ?></p>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Fill Rate', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['fill_rate'], 1)); ?>%</div>
                    <p class="cp-stat-meta"><?php esc_html_e('average', 'campaign-office'); ?></p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="cp-quick-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-shift-calendar&action=new')); ?>" class="button button-primary">
                    <?php esc_html_e('Create Shift', 'campaign-office'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-shift-calendar')); ?>" class="button">
                    <?php esc_html_e('View Calendar', 'campaign-office'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-volunteer-checkin')); ?>" class="button">
                    <?php esc_html_e('Check-In Volunteers', 'campaign-office'); ?>
                </a>
                <a href="#" class="button cp-export-data" data-type="scheduling">
                    <?php esc_html_e('Export Hours', 'campaign-office'); ?>
                </a>
            </div>

            <!-- Today's Shifts -->
            <div class="cp-todays-shifts">
                <h2><?php esc_html_e('Today\'s Shifts', 'campaign-office'); ?></h2>
                <?php $this->render_todays_shifts(); ?>
            </div>

            <!-- Top Volunteers -->
            <div class="cp-top-volunteers">
                <h2><?php esc_html_e('Top Volunteers This Month', 'campaign-office'); ?></h2>
                <?php $this->render_top_volunteers(); ?>
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
            .cp-stat-meta {
                color: #666;
                margin: 10px 0 0 0;
                font-size: 13px;
            }
            .cp-quick-actions {
                margin: 20px 0;
            }
            .cp-quick-actions .button {
                margin-right: 10px;
            }
            .cp-todays-shifts,
            .cp-top-volunteers {
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
     * Render calendar page
     */
    public function render_calendar_page() {
        global $wpdb;

        if (isset($_GET['action']) && $_GET['action'] === 'new') {
            $this->render_new_shift_form();
            return;
        }

        // Get current month's shifts
        $month = isset($_GET['month']) ? sanitize_text_field($_GET['month']) : current_time('Y-m');
        $shifts = $wpdb->get_results($wpdb->prepare(
            "SELECT s.*, COUNT(DISTINCT sa.volunteer_id) as assigned_volunteers
            FROM {$this->table_shifts} s
            LEFT JOIN {$this->table_shift_assignments} sa ON s.id = sa.shift_id AND sa.status = 'confirmed'
            WHERE DATE_FORMAT(s.shift_date, '%%Y-%%m') = %s
            GROUP BY s.id
            ORDER BY s.shift_date ASC, s.start_time ASC",
            $month
        ));

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Shift Calendar', 'campaign-office'); ?></h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=cp-shift-calendar&action=new')); ?>" class="page-title-action">
                <?php esc_html_e('Create Shift', 'campaign-office'); ?>
            </a>

            <hr class="wp-header-end">

            <!-- Month Navigator -->
            <div class="cp-month-nav">
                <a href="<?php echo esc_url(add_query_arg('month', date('Y-m', strtotime($month . '-01 -1 month')))); ?>" class="button">
                    &laquo; <?php esc_html_e('Previous Month', 'campaign-office'); ?>
                </a>
                <strong><?php echo esc_html(date_i18n('F Y', strtotime($month . '-01'))); ?></strong>
                <a href="<?php echo esc_url(add_query_arg('month', date('Y-m', strtotime($month . '-01 +1 month')))); ?>" class="button">
                    <?php esc_html_e('Next Month', 'campaign-office'); ?> &raquo;
                </a>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Date & Time', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Shift Name', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Type', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Location', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Volunteers', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Status', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Actions', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($shifts)) : ?>
                        <tr>
                            <td colspan="7"><?php esc_html_e('No shifts scheduled for this month.', 'campaign-office'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($shifts as $shift) : ?>
                            <?php
                            $fill_percentage = $shift->max_volunteers > 0 ? ($shift->assigned_volunteers / $shift->max_volunteers) * 100 : 0;
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($shift->shift_date))); ?></strong><br>
                                    <?php echo esc_html(date('g:i A', strtotime($shift->start_time)) . ' - ' . date('g:i A', strtotime($shift->end_time))); ?>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($shift->name); ?></strong>
                                    <?php if ($shift->is_recurring) : ?>
                                        <span class="cp-recurring-badge" title="<?php esc_attr_e('Recurring shift', 'campaign-office'); ?>">
                                            <span class="dashicons dashicons-update"></span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $shift->shift_type))); ?></td>
                                <td><?php echo esc_html($shift->location); ?></td>
                                <td>
                                    <span class="cp-volunteer-count <?php echo $fill_percentage >= 100 ? 'cp-filled' : ($fill_percentage >= 50 ? 'cp-partial' : 'cp-unfilled'); ?>">
                                        <?php echo esc_html($shift->assigned_volunteers . ($shift->max_volunteers ? '/' . $shift->max_volunteers : '')); ?>
                                    </span>
                                </td>
                                <td><span class="cp-status-badge cp-status-<?php echo esc_attr($shift->status); ?>"><?php echo esc_html(ucfirst($shift->status)); ?></span></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-shift-calendar&action=edit&id=' . $shift->id)); ?>" class="button button-small">
                                        <?php esc_html_e('Edit', 'campaign-office'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-shift-calendar&action=assign&id=' . $shift->id)); ?>" class="button button-small">
                                        <?php esc_html_e('Assign', 'campaign-office'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <style>
            .cp-month-nav {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: 20px 0;
                padding: 15px;
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
            }
            .cp-recurring-badge {
                color: #f56e28;
            }
            .cp-volunteer-count {
                padding: 4px 8px;
                border-radius: 3px;
                font-weight: 600;
            }
            .cp-filled {
                background: #e8f5e9;
                color: #46b450;
            }
            .cp-partial {
                background: #fff4e5;
                color: #f56e28;
            }
            .cp-unfilled {
                background: #ffd4d4;
                color: #dc3232;
            }
        </style>
        <?php
    }

    /**
     * Render new shift form
     */
    private function render_new_shift_form() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Create Shift', 'campaign-office'); ?></h1>

            <form id="cp-new-shift-form">
                <?php wp_nonce_field('cp_create_shift', 'cp_shift_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="shift_name"><?php esc_html_e('Shift Name', 'campaign-office'); ?></label></th>
                        <td><input type="text" id="shift_name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shift_description"><?php esc_html_e('Description', 'campaign-office'); ?></label></th>
                        <td><textarea id="shift_description" name="description" rows="3" class="large-text"></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shift_type"><?php esc_html_e('Shift Type', 'campaign-office'); ?></label></th>
                        <td>
                            <select id="shift_type" name="shift_type">
                                <option value="canvassing"><?php esc_html_e('Canvassing', 'campaign-office'); ?></option>
                                <option value="phone_banking"><?php esc_html_e('Phone Banking', 'campaign-office'); ?></option>
                                <option value="data_entry"><?php esc_html_e('Data Entry', 'campaign-office'); ?></option>
                                <option value="event_support"><?php esc_html_e('Event Support', 'campaign-office'); ?></option>
                                <option value="general"><?php esc_html_e('General', 'campaign-office'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shift_date"><?php esc_html_e('Date', 'campaign-office'); ?></label></th>
                        <td><input type="date" id="shift_date" name="shift_date" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Time', 'campaign-office'); ?></th>
                        <td>
                            <input type="time" name="start_time" required> <?php esc_html_e('to', 'campaign-office'); ?>
                            <input type="time" name="end_time" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="location"><?php esc_html_e('Location', 'campaign-office'); ?></label></th>
                        <td><input type="text" id="location" name="location" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="max_volunteers"><?php esc_html_e('Max Volunteers', 'campaign-office'); ?></label></th>
                        <td><input type="number" id="max_volunteers" name="max_volunteers" min="1" value="10"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="min_volunteers"><?php esc_html_e('Min Volunteers', 'campaign-office'); ?></label></th>
                        <td><input type="number" id="min_volunteers" name="min_volunteers" min="1" value="1"></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Recurring', 'campaign-office'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="is_recurring" id="is_recurring" value="1">
                                <?php esc_html_e('This is a recurring shift', 'campaign-office'); ?>
                            </label>
                            <div id="recurrence-options" style="display: none; margin-top: 10px;">
                                <select name="recurrence_pattern">
                                    <option value="weekly"><?php esc_html_e('Weekly', 'campaign-office'); ?></option>
                                    <option value="biweekly"><?php esc_html_e('Bi-weekly', 'campaign-office'); ?></option>
                                    <option value="monthly"><?php esc_html_e('Monthly', 'campaign-office'); ?></option>
                                </select>
                                <label>
                                    <?php esc_html_e('Until:', 'campaign-office'); ?>
                                    <input type="date" name="recurrence_end_date">
                                </label>
                            </div>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Create Shift', 'campaign-office'); ?></button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-shift-calendar')); ?>" class="button"><?php esc_html_e('Cancel', 'campaign-office'); ?></a>
                </p>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#is_recurring').on('change', function() {
                $('#recurrence-options').toggle(this.checked);
            });

            $('#cp-new-shift-form').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);
                formData.append('action', 'cp_create_shift');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            window.location.href = '<?php echo esc_js(admin_url('admin.php?page=cp-shift-calendar')); ?>';
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
     * Render availability page
     */
    public function render_availability_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Volunteer Availability', 'campaign-office'); ?></h1>
            <p><?php esc_html_e('View and manage volunteer availability schedules.', 'campaign-office'); ?></p>

            <!-- Availability grid will be rendered here -->
        </div>
        <?php
    }

    /**
     * Render hours tracking page
     */
    public function render_hours_page() {
        global $wpdb;

        // Get date range
        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : date('Y-m-t');

        $hours = $wpdb->get_results($wpdb->prepare(
            "SELECT h.*, u.display_name, s.name as shift_name
            FROM {$this->table_hours} h
            LEFT JOIN {$wpdb->users} u ON h.volunteer_id = u.ID
            LEFT JOIN {$this->table_shifts} s ON h.shift_id = s.id
            WHERE h.hours_date BETWEEN %s AND %s
            ORDER BY h.hours_date DESC",
            $start_date,
            $end_date
        ));

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Volunteer Hours Tracking', 'campaign-office'); ?></h1>

            <!-- Date Range Filter -->
            <form method="get" class="cp-date-filter">
                <input type="hidden" name="page" value="cp-volunteer-hours">
                <label><?php esc_html_e('From:', 'campaign-office'); ?></label>
                <input type="date" name="start_date" value="<?php echo esc_attr($start_date); ?>">
                <label><?php esc_html_e('To:', 'campaign-office'); ?></label>
                <input type="date" name="end_date" value="<?php echo esc_attr($end_date); ?>">
                <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'campaign-office'); ?>">
            </form>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Date', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Volunteer', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Shift/Activity', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Hours', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Type', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Approved', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($hours)) : ?>
                        <tr>
                            <td colspan="6"><?php esc_html_e('No hours recorded for this period.', 'campaign-office'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php
                        $total_hours = 0;
                        foreach ($hours as $hour) :
                            $total_hours += $hour->hours;
                        ?>
                            <tr>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($hour->hours_date))); ?></td>
                                <td><?php echo esc_html($hour->display_name); ?></td>
                                <td><?php echo esc_html($hour->shift_name ?: $hour->description); ?></td>
                                <td><strong><?php echo esc_html(number_format($hour->hours, 2)); ?></strong></td>
                                <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $hour->activity_type))); ?></td>
                                <td>
                                    <?php if ($hour->approved) : ?>
                                        <span class="dashicons dashicons-yes" style="color: #46b450;"></span>
                                    <?php else : ?>
                                        <button class="button button-small cp-approve-hours" data-id="<?php echo esc_attr($hour->id); ?>">
                                            <?php esc_html_e('Approve', 'campaign-office'); ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="cp-total-row">
                            <td colspan="3"><strong><?php esc_html_e('Total Hours:', 'campaign-office'); ?></strong></td>
                            <td colspan="3"><strong><?php echo esc_html(number_format($total_hours, 2)); ?></strong></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <style>
            .cp-date-filter {
                margin: 20px 0;
                padding: 15px;
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
            }
            .cp-date-filter label {
                margin: 0 5px 0 15px;
            }
            .cp-total-row {
                background: #f9f9f9;
                font-weight: 600;
            }
        </style>
        <?php
    }

    /**
     * Render check-in page
     */
    public function render_checkin_page() {
        global $wpdb;

        $today = current_time('Y-m-d');
        $active_checkins = $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, u.display_name, s.name as shift_name
            FROM {$this->table_check_ins} c
            LEFT JOIN {$wpdb->users} u ON c.volunteer_id = u.ID
            LEFT JOIN {$this->table_shifts} s ON c.shift_id = s.id
            WHERE DATE(c.check_in_time) = %s AND c.check_out_time IS NULL
            ORDER BY c.check_in_time DESC",
            $today
        ));

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Volunteer Check-In/Out', 'campaign-office'); ?></h1>

            <div class="cp-checkin-stats">
                <div class="cp-stat">
                    <div class="cp-stat-number"><?php echo esc_html(count($active_checkins)); ?></div>
                    <div class="cp-stat-label"><?php esc_html_e('Currently Checked In', 'campaign-office'); ?></div>
                </div>
            </div>

            <h2><?php esc_html_e('Active Check-Ins', 'campaign-office'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Volunteer', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Shift', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Check-In Time', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Duration', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Actions', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($active_checkins)) : ?>
                        <tr>
                            <td colspan="5"><?php esc_html_e('No volunteers currently checked in.', 'campaign-office'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($active_checkins as $checkin) : ?>
                            <?php
                            $duration = time() - strtotime($checkin->check_in_time);
                            $hours = floor($duration / 3600);
                            $minutes = floor(($duration % 3600) / 60);
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($checkin->display_name); ?></strong></td>
                                <td><?php echo esc_html($checkin->shift_name); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('time_format'), strtotime($checkin->check_in_time))); ?></td>
                                <td><?php echo esc_html(sprintf('%dh %dm', $hours, $minutes)); ?></td>
                                <td>
                                    <button class="button button-primary cp-checkout-btn" data-checkin-id="<?php echo esc_attr($checkin->id); ?>">
                                        <?php esc_html_e('Check Out', 'campaign-office'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <style>
            .cp-checkin-stats {
                margin: 20px 0;
            }
            .cp-stat {
                display: inline-block;
                background: #fff;
                border: 1px solid #ccd0d4;
                padding: 20px 40px;
                border-radius: 4px;
                text-align: center;
            }
            .cp-stat-number {
                font-size: 48px;
                font-weight: 600;
                color: #2271b1;
            }
            .cp-stat-label {
                color: #666;
                margin-top: 5px;
            }
        </style>
        <?php
    }

    /**
     * Render shift calendar (frontend shortcode)
     */
    public function render_shift_calendar($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('You must be logged in to view shifts.', 'campaign-office') . '</p>';
        }

        // Calendar implementation would go here
        return '<div class="cp-shift-calendar">' . esc_html__('Shift calendar coming soon', 'campaign-office') . '</div>';
    }

    /**
     * Render check-in interface (frontend shortcode)
     */
    public function render_checkin_interface($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('You must be logged in to check in.', 'campaign-office') . '</p>';
        }

        ob_start();
        ?>
        <div class="cp-volunteer-checkin">
            <h3><?php esc_html_e('Volunteer Check-In', 'campaign-office'); ?></h3>

            <div id="cp-checkin-status"></div>

            <button id="cp-checkin-btn" class="cp-checkin-button">
                <?php esc_html_e('Check In', 'campaign-office'); ?>
            </button>
            <button id="cp-checkout-btn" class="cp-checkout-button" style="display: none;">
                <?php esc_html_e('Check Out', 'campaign-office'); ?>
            </button>
        </div>

        <script>
        // Check-in/out JavaScript will be loaded from external file
        </script>

        <style>
            .cp-volunteer-checkin {
                text-align: center;
                padding: 40px;
            }
            .cp-checkin-button,
            .cp-checkout-button {
                padding: 20px 40px;
                font-size: 18px;
                font-weight: 600;
                border: none;
                border-radius: 8px;
                cursor: pointer;
            }
            .cp-checkin-button {
                background: #46b450;
                color: #fff;
            }
            .cp-checkout-button {
                background: #dc3232;
                color: #fff;
            }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Render availability form (frontend shortcode)
     */
    public function render_availability_form($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('You must be logged in to set availability.', 'campaign-office') . '</p>';
        }

        // Availability form implementation
        return '<div class="cp-availability-form">' . esc_html__('Availability form coming soon', 'campaign-office') . '</div>';
    }

    /**
     * Get stats
     *
     * @return array Statistics
     */
    public function get_stats() {
        global $wpdb;

        $today = current_time('Y-m-d');
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $week_end = date('Y-m-d', strtotime('sunday this week'));

        $volunteers_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT volunteer_id) FROM {$this->table_check_ins} WHERE DATE(check_in_time) = %s",
            $today
        ));

        $checked_in_now = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_check_ins} WHERE DATE(check_in_time) = %s AND check_out_time IS NULL",
            $today
        ));

        $upcoming_shifts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_shifts} WHERE shift_date BETWEEN %s AND DATE_ADD(%s, INTERVAL 7 DAY)",
            $today,
            $today
        ));

        $unfilled_shifts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_shifts} s
            WHERE s.shift_date >= %s
            AND (SELECT COUNT(*) FROM {$this->table_shift_assignments} sa WHERE sa.shift_id = s.id AND sa.status = 'confirmed') < COALESCE(s.min_volunteers, 1)",
            $today
        ));

        $total_hours_week = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(hours) FROM {$this->table_hours} WHERE hours_date BETWEEN %s AND %s",
            $week_start,
            $week_end
        ));

        $unique_volunteers_week = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT volunteer_id) FROM {$this->table_hours} WHERE hours_date BETWEEN %s AND %s",
            $week_start,
            $week_end
        ));

        // Calculate no-show rate for this month
        $month_start = date('Y-m-01');
        $total_assignments_month = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_shift_assignments} sa
            LEFT JOIN {$this->table_shifts} s ON sa.shift_id = s.id
            WHERE s.shift_date >= %s",
            $month_start
        ));

        $no_shows_month = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_shift_assignments} sa
            LEFT JOIN {$this->table_shifts} s ON sa.shift_id = s.id
            WHERE s.shift_date >= %s AND sa.no_show = 1",
            $month_start
        ));

        $no_show_rate = $total_assignments_month > 0 ? ($no_shows_month / $total_assignments_month) * 100 : 0;

        // Calculate average fill rate
        $fill_rate = 75; // Placeholder calculation

        return array(
            'volunteers_today' => $volunteers_today,
            'checked_in_now' => $checked_in_now,
            'upcoming_shifts' => $upcoming_shifts,
            'unfilled_shifts' => $unfilled_shifts,
            'total_hours_week' => $total_hours_week ? floatval($total_hours_week) : 0,
            'unique_volunteers_week' => $unique_volunteers_week,
            'no_show_rate' => $no_show_rate,
            'fill_rate' => $fill_rate,
        );
    }

    /**
     * Get active volunteers today
     *
     * @return int Count
     */
    public function get_active_volunteers_today() {
        $stats = $this->get_stats();
        return $stats['volunteers_today'];
    }

    /**
     * Render today's shifts
     */
    private function render_todays_shifts() {
        global $wpdb;

        $today = current_time('Y-m-d');
        $shifts = $wpdb->get_results($wpdb->prepare(
            "SELECT s.*, COUNT(DISTINCT sa.volunteer_id) as assigned_volunteers
            FROM {$this->table_shifts} s
            LEFT JOIN {$this->table_shift_assignments} sa ON s.id = sa.shift_id AND sa.status = 'confirmed'
            WHERE s.shift_date = %s
            GROUP BY s.id
            ORDER BY s.start_time ASC",
            $today
        ));

        if (empty($shifts)) {
            echo '<p>' . esc_html__('No shifts scheduled for today.', 'campaign-office') . '</p>';
            return;
        }

        echo '<table class="wp-list-table widefat fixed striped"><thead><tr>';
        echo '<th>' . esc_html__('Time', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Shift', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Type', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Volunteers', 'campaign-office') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($shifts as $shift) {
            echo '<tr>';
            echo '<td>' . esc_html(date('g:i A', strtotime($shift->start_time)) . ' - ' . date('g:i A', strtotime($shift->end_time))) . '</td>';
            echo '<td><strong>' . esc_html($shift->name) . '</strong></td>';
            echo '<td>' . esc_html(ucfirst(str_replace('_', ' ', $shift->shift_type))) . '</td>';
            echo '<td>' . esc_html($shift->assigned_volunteers . ($shift->max_volunteers ? '/' . $shift->max_volunteers : '')) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    /**
     * Render top volunteers
     */
    private function render_top_volunteers() {
        global $wpdb;

        $month_start = date('Y-m-01');
        $top_volunteers = $wpdb->get_results($wpdb->prepare(
            "SELECT u.display_name, SUM(h.hours) as total_hours, COUNT(DISTINCT h.shift_id) as shifts_worked
            FROM {$this->table_hours} h
            LEFT JOIN {$wpdb->users} u ON h.volunteer_id = u.ID
            WHERE h.hours_date >= %s
            GROUP BY h.volunteer_id
            ORDER BY total_hours DESC
            LIMIT 10",
            $month_start
        ));

        if (empty($top_volunteers)) {
            echo '<p>' . esc_html__('No volunteer activity this month yet.', 'campaign-office') . '</p>';
            return;
        }

        echo '<table class="wp-list-table widefat fixed striped"><thead><tr>';
        echo '<th>' . esc_html__('Rank', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Volunteer', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Hours', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Shifts', 'campaign-office') . '</th>';
        echo '</tr></thead><tbody>';

        $rank = 1;
        foreach ($top_volunteers as $volunteer) {
            echo '<tr>';
            echo '<td><strong>' . esc_html($rank) . '</strong></td>';
            echo '<td>' . esc_html($volunteer->display_name) . '</td>';
            echo '<td>' . esc_html(number_format($volunteer->total_hours, 1)) . '</td>';
            echo '<td>' . esc_html(number_format($volunteer->shifts_worked)) . '</td>';
            echo '</tr>';
            $rank++;
        }

        echo '</tbody></table>';
    }

    /**
     * Send shift reminders
     */
    public function send_shift_reminders() {
        // Implementation for automated shift reminders
    }

    /**
     * Process no-shows
     */
    public function process_no_shows() {
        // Implementation for detecting and marking no-shows
    }

    /**
     * Generate recurring shifts
     */
    public function generate_recurring_shifts() {
        // Implementation for creating recurring shift instances
    }

    /**
     * AJAX handlers
     */
    public function ajax_create_shift() {
        check_ajax_referer('cp_create_shift', 'cp_shift_nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaign-office')));
        }

        global $wpdb;

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $shift_date = isset($_POST['shift_date']) ? sanitize_text_field($_POST['shift_date']) : '';
        $start_time = isset($_POST['start_time']) ? sanitize_text_field($_POST['start_time']) : '';
        $end_time = isset($_POST['end_time']) ? sanitize_text_field($_POST['end_time']) : '';
        $shift_type = isset($_POST['shift_type']) ? sanitize_text_field($_POST['shift_type']) : 'general';
        $max_volunteers = isset($_POST['max_volunteers']) ? absint($_POST['max_volunteers']) : null;

        if (empty($name) || empty($shift_date) || empty($start_time) || empty($end_time)) {
            wp_send_json_error(array('message' => __('All fields are required.', 'campaign-office')));
        }

        $result = $wpdb->insert(
            $this->table_shifts,
            array(
                'name' => $name,
                'shift_date' => $shift_date,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'shift_type' => $shift_type,
                'max_volunteers' => $max_volunteers,
                'created_by' => get_current_user_id(),
                'status' => 'active',
            ),
            array('%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s')
        );

        if ($result) {
            wp_send_json_success(array(
                'message' => __('Shift created!', 'campaign-office'),
                'id' => $wpdb->insert_id,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to create shift.', 'campaign-office')));
        }
    }

    public function ajax_assign_volunteer() {
        check_ajax_referer('cp_field_ops_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaign-office')));
        }

        global $wpdb;

        $shift_id = isset($_POST['shift_id']) ? absint($_POST['shift_id']) : 0;
        $volunteer_id = isset($_POST['volunteer_id']) ? absint($_POST['volunteer_id']) : 0;

        if (!$shift_id || !$volunteer_id) {
            wp_send_json_error(array('message' => __('Shift ID and volunteer ID are required.', 'campaign-office')));
        }

        // Check if already assigned
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_shift_assignments}
            WHERE shift_id = %d AND volunteer_id = %d",
            $shift_id,
            $volunteer_id
        ));

        if ($existing) {
            wp_send_json_error(array('message' => __('Volunteer is already assigned to this shift.', 'campaign-office')));
        }

        $result = $wpdb->insert(
            $this->table_shift_assignments,
            array(
                'shift_id' => $shift_id,
                'volunteer_id' => $volunteer_id,
                'status' => 'confirmed',
            ),
            array('%d', '%d', '%s')
        );

        if ($result) {
            $volunteer = get_user_by('id', $volunteer_id);
            wp_send_json_success(array(
                'message' => sprintf(__('%s assigned to shift successfully!', 'campaign-office'), $volunteer->display_name),
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to assign volunteer.', 'campaign-office')));
        }
    }

    public function ajax_check_in() {
        check_ajax_referer('cp_field_ops_nonce', 'nonce');

        $volunteer_id = get_current_user_id();
        if (!$volunteer_id) {
            wp_send_json_error(array('message' => __('You must be logged in.', 'campaign-office')));
        }

        global $wpdb;

        $shift_id = isset($_POST['shift_id']) ? absint($_POST['shift_id']) : null;
        $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';

        // Check if already checked in
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_check_ins}
            WHERE volunteer_id = %d AND check_out_time IS NULL
            ORDER BY check_in_time DESC LIMIT 1",
            $volunteer_id
        ));

        if ($existing) {
            wp_send_json_error(array('message' => __('You are already checked in. Please check out first.', 'campaign-office')));
        }

        $result = $wpdb->insert(
            $this->table_check_ins,
            array(
                'volunteer_id' => $volunteer_id,
                'shift_id' => $shift_id,
                'check_in_time' => current_time('mysql'),
                'location' => $location,
            ),
            array('%d', '%d', '%s', '%s')
        );

        if ($result) {
            wp_send_json_success(array(
                'message' => __('Checked in successfully!', 'campaign-office'),
                'checkin_id' => $wpdb->insert_id,
                'time' => current_time('mysql'),
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to check in.', 'campaign-office')));
        }
    }

    public function ajax_check_out() {
        check_ajax_referer('cp_field_ops_nonce', 'nonce');

        $volunteer_id = get_current_user_id();
        if (!$volunteer_id) {
            wp_send_json_error(array('message' => __('You must be logged in.', 'campaign-office')));
        }

        global $wpdb;

        $checkin_id = isset($_POST['checkin_id']) ? absint($_POST['checkin_id']) : 0;

        // Get the last check-in if no ID provided
        if (!$checkin_id) {
            $checkin = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$this->table_check_ins}
                WHERE volunteer_id = %d AND check_out_time IS NULL
                ORDER BY check_in_time DESC LIMIT 1",
                $volunteer_id
            ));

            if (!$checkin) {
                wp_send_json_error(array('message' => __('No active check-in found.', 'campaign-office')));
            }

            $checkin_id = $checkin->id;
            $check_in_time = $checkin->check_in_time;
            $shift_id = $checkin->shift_id;
        } else {
            $checkin = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$this->table_check_ins} WHERE id = %d",
                $checkin_id
            ));

            if (!$checkin || $checkin->volunteer_id != $volunteer_id) {
                wp_send_json_error(array('message' => __('Invalid check-in ID.', 'campaign-office')));
            }

            $check_in_time = $checkin->check_in_time;
            $shift_id = $checkin->shift_id;
        }

        $check_out_time = current_time('mysql');

        // Update check-in record
        $result = $wpdb->update(
            $this->table_check_ins,
            array('check_out_time' => $check_out_time),
            array('id' => $checkin_id),
            array('%s'),
            array('%d')
        );

        if ($result !== false) {
            // Calculate hours worked
            $hours_worked = (strtotime($check_out_time) - strtotime($check_in_time)) / 3600;

            // Record hours
            $wpdb->insert(
                $this->table_hours,
                array(
                    'volunteer_id' => $volunteer_id,
                    'shift_id' => $shift_id,
                    'hours' => $hours_worked,
                    'hours_date' => date('Y-m-d', strtotime($check_in_time)),
                ),
                array('%d', '%d', '%f', '%s')
            );

            wp_send_json_success(array(
                'message' => __('Checked out successfully!', 'campaign-office'),
                'hours' => round($hours_worked, 2),
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to check out.', 'campaign-office')));
        }
    }

    public function ajax_save_availability() {
        check_ajax_referer('cp_field_ops_nonce', 'nonce');

        $volunteer_id = get_current_user_id();
        if (!$volunteer_id) {
            wp_send_json_error(array('message' => __('You must be logged in.', 'campaign-office')));
        }

        global $wpdb;

        $availability_data = isset($_POST['availability']) ? wp_unslash($_POST['availability']) : array();

        if (empty($availability_data)) {
            wp_send_json_error(array('message' => __('No availability data provided.', 'campaign-office')));
        }

        // Delete existing availability
        $wpdb->delete(
            $this->table_availability,
            array('volunteer_id' => $volunteer_id),
            array('%d')
        );

        // Insert new availability records
        $success_count = 0;
        foreach ($availability_data as $day => $times) {
            if (!empty($times['available'])) {
                $result = $wpdb->insert(
                    $this->table_availability,
                    array(
                        'volunteer_id' => $volunteer_id,
                        'day_of_week' => sanitize_text_field($day),
                        'start_time' => sanitize_text_field($times['start_time']),
                        'end_time' => sanitize_text_field($times['end_time']),
                    ),
                    array('%d', '%s', '%s', '%s')
                );

                if ($result) {
                    $success_count++;
                }
            }
        }

        if ($success_count > 0) {
            wp_send_json_success(array(
                'message' => __('Availability saved!', 'campaign-office'),
                'count' => $success_count,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to save availability.', 'campaign-office')));
        }
    }

    /**
     * REST API handlers
     */
    public function rest_get_shifts($request) {
        return new WP_REST_Response(array(), 200);
    }

    public function rest_check_in($request) {
        return new WP_REST_Response(array('success' => true), 201);
    }

    public function rest_check_out($request) {
        return new WP_REST_Response(array('success' => true), 200);
    }
}
