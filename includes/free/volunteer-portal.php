<?php
/**
 * Advanced Volunteer Portal
 *
 * Self-service portal for volunteers to manage their profile, view assignments,
 * track volunteer hours, sign up for shifts, and communicate with campaign staff.
 *
 * Features:
 * - Volunteer login and dashboard
 * - Profile management
 * - Shift signup and calendar
 * - Volunteer hours tracking
 * - Assignment notifications
 * - Leaderboards and gamification
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Volunteer_Portal
 *
 * Manages the volunteer self-service portal
 */
class CP_Volunteer_Portal {

    /**
     * Database table names
     *
     * @var string
     */
    private $shifts_table;
    private $hours_table;
    private $assignments_table;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->shifts_table = $wpdb->prefix . 'cp_volunteer_shifts';
        $this->hours_table = $wpdb->prefix . 'cp_volunteer_hours';
        $this->assignments_table = $wpdb->prefix . 'cp_volunteer_assignments';

        // Database setup
        add_action('after_setup_theme', array($this, 'create_portal_tables'));

        // Shortcodes
        add_shortcode('cp_volunteer_portal', array($this, 'render_volunteer_portal'));
        add_shortcode('cp_volunteer_login', array($this, 'render_volunteer_login'));
        add_shortcode('cp_volunteer_dashboard', array($this, 'render_volunteer_dashboard'));
        add_shortcode('cp_volunteer_leaderboard', array($this, 'render_volunteer_leaderboard'));

        // AJAX handlers
        add_action('wp_ajax_cp_volunteer_login', array($this, 'ajax_volunteer_login'));
        add_action('wp_ajax_nopriv_cp_volunteer_login', array($this, 'ajax_volunteer_login'));
        add_action('wp_ajax_cp_volunteer_signup_shift', array($this, 'ajax_signup_shift'));
        add_action('wp_ajax_cp_volunteer_log_hours', array($this, 'ajax_log_hours'));
        add_action('wp_ajax_cp_get_volunteer_shifts', array($this, 'ajax_get_shifts'));
        add_action('wp_ajax_cp_update_volunteer_profile', array($this, 'ajax_update_profile'));

        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Enqueue assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_portal_assets'));
    }

    /**
     * Create portal database tables
     */
    public function create_portal_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Volunteer shifts table
        $sql_shifts = "CREATE TABLE IF NOT EXISTS {$this->shifts_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text DEFAULT NULL,
            shift_date date NOT NULL,
            start_time time NOT NULL,
            end_time time NOT NULL,
            location varchar(255) DEFAULT NULL,
            capacity int(11) DEFAULT 10,
            filled int(11) DEFAULT 0,
            shift_type varchar(50) DEFAULT 'general',
            coordinator_id bigint(20) DEFAULT NULL,
            status varchar(20) DEFAULT 'open',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY shift_date (shift_date),
            KEY status (status)
        ) $charset_collate;";

        // Volunteer hours table
        $sql_hours = "CREATE TABLE IF NOT EXISTS {$this->hours_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            volunteer_id bigint(20) UNSIGNED NOT NULL,
            shift_id bigint(20) UNSIGNED DEFAULT NULL,
            activity varchar(255) NOT NULL,
            hours decimal(5,2) NOT NULL,
            activity_date date NOT NULL,
            notes text DEFAULT NULL,
            verified tinyint(1) DEFAULT 0,
            verified_by bigint(20) DEFAULT NULL,
            verified_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY volunteer_id (volunteer_id),
            KEY activity_date (activity_date),
            KEY verified (verified)
        ) $charset_collate;";

        // Volunteer shift assignments table
        $sql_assignments = "CREATE TABLE IF NOT EXISTS {$this->assignments_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            volunteer_id bigint(20) UNSIGNED NOT NULL,
            shift_id bigint(20) UNSIGNED NOT NULL,
            status varchar(20) DEFAULT 'confirmed',
            checked_in tinyint(1) DEFAULT 0,
            checked_in_at datetime DEFAULT NULL,
            checked_out_at datetime DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY volunteer_id (volunteer_id),
            KEY shift_id (shift_id),
            KEY status (status),
            UNIQUE KEY volunteer_shift (volunteer_id, shift_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_shifts);
        dbDelta($sql_hours);
        dbDelta($sql_assignments);

        update_option('cp_volunteer_portal_tables_created', true);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=cp_volunteer',
            __('Volunteer Portal', 'campaignpress'),
            __('Portal', 'campaignpress'),
            'edit_posts',
            'cp-volunteer-portal',
            array($this, 'render_admin_portal_page')
        );

        add_submenu_page(
            'edit.php?post_type=cp_volunteer',
            __('Manage Shifts', 'campaignpress'),
            __('Shifts', 'campaignpress'),
            'edit_posts',
            'cp-volunteer-shifts',
            array($this, 'render_admin_shifts_page')
        );

        add_submenu_page(
            'edit.php?post_type=cp_volunteer',
            __('Volunteer Hours', 'campaignpress'),
            __('Hours', 'campaignpress'),
            'edit_posts',
            'cp-volunteer-hours',
            array($this, 'render_admin_hours_page')
        );
    }

    /**
     * Render volunteer portal shortcode
     */
    public function render_volunteer_portal($atts) {
        $atts = shortcode_atts(array(
            'auto_login' => 'false',
        ), $atts, 'cp_volunteer_portal');

        $volunteer_id = $this->get_current_volunteer_id();

        if (!$volunteer_id) {
            return $this->render_volunteer_login(array());
        }

        return $this->render_volunteer_dashboard(array());
    }

    /**
     * Render volunteer login form
     */
    public function render_volunteer_login($atts) {
        ob_start();
        ?>
        <div class="cp-volunteer-login-wrapper">
            <div class="cp-volunteer-login-box">
                <h2><?php esc_html_e('Volunteer Login', 'campaignpress'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Enter your email address to access your volunteer portal.', 'campaignpress'); ?>
                </p>
                <form class="cp-volunteer-login-form" id="cp-volunteer-login-form">
                    <?php wp_nonce_field('cp_volunteer_login', 'cp_volunteer_login_nonce'); ?>
                    <div class="form-field">
                        <label for="volunteer_email"><?php esc_html_e('Email Address', 'campaignpress'); ?></label>
                        <input type="email" id="volunteer_email" name="volunteer_email" required class="cp-input">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="cp-button cp-button-primary">
                            <?php esc_html_e('Access Portal', 'campaignpress'); ?>
                        </button>
                    </div>
                    <div class="cp-login-message" style="display:none;"></div>
                </form>
                <p class="cp-login-note">
                    <?php esc_html_e('New volunteer?', 'campaignpress'); ?>
                    <a href="#signup"><?php esc_html_e('Sign up here', 'campaignpress'); ?></a>
                </p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render volunteer dashboard
     */
    public function render_volunteer_dashboard($atts) {
        $volunteer_id = $this->get_current_volunteer_id();

        if (!$volunteer_id) {
            return '<p>' . esc_html__('Please log in to access your volunteer dashboard.', 'campaignpress') . '</p>';
        }

        $volunteer = $this->get_volunteer($volunteer_id);
        $stats = $this->get_volunteer_stats($volunteer_id);
        $upcoming_shifts = $this->get_volunteer_upcoming_shifts($volunteer_id);

        ob_start();
        ?>
        <div class="cp-volunteer-dashboard">
            <!-- Header -->
            <div class="cp-dashboard-header">
                <div class="cp-volunteer-info">
                    <h2><?php printf(esc_html__('Welcome, %s!', 'campaignpress'), esc_html($volunteer->first_name)); ?></h2>
                    <p class="cp-volunteer-role"><?php esc_html_e('Active Volunteer', 'campaignpress'); ?></p>
                </div>
                <div class="cp-dashboard-actions">
                    <button class="cp-button cp-button-secondary" onclick="location.reload()">
                        <?php esc_html_e('Refresh', 'campaignpress'); ?>
                    </button>
                    <button class="cp-button cp-button-ghost" onclick="cpVolunteerLogout()">
                        <?php esc_html_e('Logout', 'campaignpress'); ?>
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="cp-stats-grid">
                <div class="cp-stat-card">
                    <div class="cp-stat-icon">⏰</div>
                    <div class="cp-stat-content">
                        <div class="cp-stat-value"><?php echo esc_html(number_format($stats['total_hours'], 1)); ?></div>
                        <div class="cp-stat-label"><?php esc_html_e('Hours Logged', 'campaignpress'); ?></div>
                    </div>
                </div>
                <div class="cp-stat-card">
                    <div class="cp-stat-icon">📅</div>
                    <div class="cp-stat-content">
                        <div class="cp-stat-value"><?php echo esc_html($stats['shifts_completed']); ?></div>
                        <div class="cp-stat-label"><?php esc_html_e('Shifts Completed', 'campaignpress'); ?></div>
                    </div>
                </div>
                <div class="cp-stat-card">
                    <div class="cp-stat-icon">🎯</div>
                    <div class="cp-stat-content">
                        <div class="cp-stat-value"><?php echo esc_html($stats['upcoming_shifts']); ?></div>
                        <div class="cp-stat-label"><?php esc_html_e('Upcoming Shifts', 'campaignpress'); ?></div>
                    </div>
                </div>
                <div class="cp-stat-card">
                    <div class="cp-stat-icon">🏆</div>
                    <div class="cp-stat-content">
                        <div class="cp-stat-value">#<?php echo esc_html($stats['rank']); ?></div>
                        <div class="cp-stat-label"><?php esc_html_e('Volunteer Rank', 'campaignpress'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="cp-dashboard-tabs">
                <button class="cp-tab-button active" data-tab="upcoming">
                    <?php esc_html_e('Upcoming Shifts', 'campaignpress'); ?>
                </button>
                <button class="cp-tab-button" data-tab="available">
                    <?php esc_html_e('Available Shifts', 'campaignpress'); ?>
                </button>
                <button class="cp-tab-button" data-tab="hours">
                    <?php esc_html_e('Log Hours', 'campaignpress'); ?>
                </button>
                <button class="cp-tab-button" data-tab="profile">
                    <?php esc_html_e('Profile', 'campaignpress'); ?>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="cp-tab-content active" data-tab-content="upcoming">
                <h3><?php esc_html_e('Your Upcoming Shifts', 'campaignpress'); ?></h3>
                <?php if (empty($upcoming_shifts)) : ?>
                    <p class="cp-empty-state">
                        <?php esc_html_e('You have no upcoming shifts. Browse available shifts to sign up!', 'campaignpress'); ?>
                    </p>
                <?php else : ?>
                    <div class="cp-shifts-list">
                        <?php foreach ($upcoming_shifts as $shift) : ?>
                            <div class="cp-shift-card">
                                <div class="cp-shift-date">
                                    <div class="cp-date-day"><?php echo date('j', strtotime($shift->shift_date)); ?></div>
                                    <div class="cp-date-month"><?php echo date('M', strtotime($shift->shift_date)); ?></div>
                                </div>
                                <div class="cp-shift-details">
                                    <h4><?php echo esc_html($shift->title); ?></h4>
                                    <p class="cp-shift-time">
                                        <?php echo date('g:i A', strtotime($shift->start_time)); ?> -
                                        <?php echo date('g:i A', strtotime($shift->end_time)); ?>
                                    </p>
                                    <p class="cp-shift-location">
                                        <span class="dashicons dashicons-location"></span>
                                        <?php echo esc_html($shift->location); ?>
                                    </p>
                                </div>
                                <div class="cp-shift-actions">
                                    <button class="cp-button cp-button-small cp-button-secondary">
                                        <?php esc_html_e('Details', 'campaignpress'); ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="cp-tab-content" data-tab-content="available">
                <h3><?php esc_html_e('Available Volunteer Shifts', 'campaignpress'); ?></h3>
                <div id="cp-available-shifts-container">
                    <?php echo $this->render_available_shifts(); ?>
                </div>
            </div>

            <div class="cp-tab-content" data-tab-content="hours">
                <h3><?php esc_html_e('Log Volunteer Hours', 'campaignpress'); ?></h3>
                <?php echo $this->render_log_hours_form($volunteer_id); ?>
            </div>

            <div class="cp-tab-content" data-tab-content="profile">
                <h3><?php esc_html_e('Volunteer Profile', 'campaignpress'); ?></h3>
                <?php echo $this->render_profile_form($volunteer); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render available shifts
     */
    private function render_available_shifts() {
        global $wpdb;

        $shifts = $wpdb->get_results("
            SELECT * FROM {$this->shifts_table}
            WHERE status = 'open'
            AND shift_date >= CURDATE()
            AND filled < capacity
            ORDER BY shift_date ASC, start_time ASC
            LIMIT 20
        ");

        if (empty($shifts)) {
            return '<p class="cp-empty-state">' . esc_html__('No available shifts at this time. Check back soon!', 'campaignpress') . '</p>';
        }

        ob_start();
        ?>
        <div class="cp-shifts-grid">
            <?php foreach ($shifts as $shift) : ?>
                <div class="cp-shift-card cp-shift-available">
                    <div class="cp-shift-header">
                        <h4><?php echo esc_html($shift->title); ?></h4>
                        <span class="cp-shift-badge"><?php echo esc_html(ucfirst($shift->shift_type)); ?></span>
                    </div>
                    <div class="cp-shift-body">
                        <p class="cp-shift-date-time">
                            <strong><?php echo date_i18n(get_option('date_format'), strtotime($shift->shift_date)); ?></strong><br>
                            <?php echo date('g:i A', strtotime($shift->start_time)); ?> -
                            <?php echo date('g:i A', strtotime($shift->end_time)); ?>
                        </p>
                        <p class="cp-shift-location">
                            <span class="dashicons dashicons-location"></span>
                            <?php echo esc_html($shift->location); ?>
                        </p>
                        <?php if ($shift->description) : ?>
                            <p class="cp-shift-description"><?php echo esc_html(wp_trim_words($shift->description, 15)); ?></p>
                        <?php endif; ?>
                        <p class="cp-shift-capacity">
                            <span class="dashicons dashicons-groups"></span>
                            <?php printf(esc_html__('%d / %d spots filled', 'campaignpress'), $shift->filled, $shift->capacity); ?>
                        </p>
                    </div>
                    <div class="cp-shift-footer">
                        <button class="cp-button cp-button-primary cp-signup-shift-btn" data-shift-id="<?php echo esc_attr($shift->id); ?>">
                            <?php esc_html_e('Sign Up', 'campaignpress'); ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render log hours form
     */
    private function render_log_hours_form($volunteer_id) {
        ob_start();
        ?>
        <form class="cp-log-hours-form" id="cp-log-hours-form">
            <?php wp_nonce_field('cp_log_hours', 'cp_log_hours_nonce'); ?>
            <input type="hidden" name="volunteer_id" value="<?php echo esc_attr($volunteer_id); ?>">

            <div class="form-row">
                <div class="form-field">
                    <label for="activity"><?php esc_html_e('Activity', 'campaignpress'); ?></label>
                    <select id="activity" name="activity" required class="cp-input">
                        <option value=""><?php esc_html_e('Select activity...', 'campaignpress'); ?></option>
                        <option value="Canvassing"><?php esc_html_e('Canvassing', 'campaignpress'); ?></option>
                        <option value="Phone Banking"><?php esc_html_e('Phone Banking', 'campaignpress'); ?></option>
                        <option value="Event Support"><?php esc_html_e('Event Support', 'campaignpress'); ?></option>
                        <option value="Data Entry"><?php esc_html_e('Data Entry', 'campaignpress'); ?></option>
                        <option value="Social Media"><?php esc_html_e('Social Media', 'campaignpress'); ?></option>
                        <option value="Other"><?php esc_html_e('Other', 'campaignpress'); ?></option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="activity_date"><?php esc_html_e('Date', 'campaignpress'); ?></label>
                    <input type="date" id="activity_date" name="activity_date" required class="cp-input" max="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-field">
                    <label for="hours"><?php esc_html_e('Hours', 'campaignpress'); ?></label>
                    <input type="number" id="hours" name="hours" step="0.5" min="0.5" max="24" required class="cp-input">
                </div>
            </div>
            <div class="form-field">
                <label for="notes"><?php esc_html_e('Notes (optional)', 'campaignpress'); ?></label>
                <textarea id="notes" name="notes" rows="3" class="cp-input"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="cp-button cp-button-primary">
                    <?php esc_html_e('Log Hours', 'campaignpress'); ?>
                </button>
            </div>
            <div class="cp-form-message" style="display:none;"></div>
        </form>

        <div class="cp-recent-hours" style="margin-top: 2rem;">
            <h4><?php esc_html_e('Recent Activity', 'campaignpress'); ?></h4>
            <?php echo $this->render_volunteer_hours_list($volunteer_id); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render volunteer hours list
     */
    private function render_volunteer_hours_list($volunteer_id, $limit = 10) {
        global $wpdb;

        $hours = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$this->hours_table}
            WHERE volunteer_id = %d
            ORDER BY activity_date DESC, created_at DESC
            LIMIT %d
        ", $volunteer_id, $limit));

        if (empty($hours)) {
            return '<p class="cp-empty-state">' . esc_html__('No hours logged yet.', 'campaignpress') . '</p>';
        }

        ob_start();
        ?>
        <table class="cp-hours-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Date', 'campaignpress'); ?></th>
                    <th><?php esc_html_e('Activity', 'campaignpress'); ?></th>
                    <th><?php esc_html_e('Hours', 'campaignpress'); ?></th>
                    <th><?php esc_html_e('Status', 'campaignpress'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hours as $hour) : ?>
                    <tr>
                        <td><?php echo date_i18n(get_option('date_format'), strtotime($hour->activity_date)); ?></td>
                        <td><?php echo esc_html($hour->activity); ?></td>
                        <td><?php echo esc_html(number_format($hour->hours, 1)); ?></td>
                        <td>
                            <?php if ($hour->verified) : ?>
                                <span class="cp-badge cp-badge-success"><?php esc_html_e('Verified', 'campaignpress'); ?></span>
                            <?php else : ?>
                                <span class="cp-badge cp-badge-pending"><?php esc_html_e('Pending', 'campaignpress'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    /**
     * Render profile form
     */
    private function render_profile_form($volunteer) {
        ob_start();
        ?>
        <form class="cp-profile-form" id="cp-profile-form">
            <?php wp_nonce_field('cp_update_profile', 'cp_profile_nonce'); ?>
            <input type="hidden" name="volunteer_id" value="<?php echo esc_attr($volunteer->id); ?>">

            <div class="form-row">
                <div class="form-field">
                    <label for="first_name"><?php esc_html_e('First Name', 'campaignpress'); ?></label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($volunteer->first_name); ?>" class="cp-input">
                </div>
                <div class="form-field">
                    <label for="last_name"><?php esc_html_e('Last Name', 'campaignpress'); ?></label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($volunteer->last_name); ?>" class="cp-input">
                </div>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label for="email"><?php esc_html_e('Email', 'campaignpress'); ?></label>
                    <input type="email" id="email" name="email" value="<?php echo esc_attr($volunteer->email); ?>" class="cp-input">
                </div>
                <div class="form-field">
                    <label for="phone"><?php esc_html_e('Phone', 'campaignpress'); ?></label>
                    <input type="tel" id="phone" name="phone" value="<?php echo esc_attr($volunteer->phone); ?>" class="cp-input">
                </div>
            </div>
            <div class="form-field">
                <label for="skills"><?php esc_html_e('Skills & Interests', 'campaignpress'); ?></label>
                <textarea id="skills" name="skills" rows="3" class="cp-input"><?php echo esc_textarea($volunteer->skills); ?></textarea>
                <p class="description"><?php esc_html_e('What skills or interests do you have that could help the campaign?', 'campaignpress'); ?></p>
            </div>
            <div class="form-field">
                <label for="availability"><?php esc_html_e('Availability', 'campaignpress'); ?></label>
                <textarea id="availability" name="availability" rows="2" class="cp-input"><?php echo esc_textarea($volunteer->availability); ?></textarea>
                <p class="description"><?php esc_html_e('When are you generally available to volunteer?', 'campaignpress'); ?></p>
            </div>
            <div class="form-actions">
                <button type="submit" class="cp-button cp-button-primary">
                    <?php esc_html_e('Update Profile', 'campaignpress'); ?>
                </button>
            </div>
            <div class="cp-form-message" style="display:none;"></div>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Render volunteer leaderboard
     */
    public function render_volunteer_leaderboard($atts) {
        $atts = shortcode_atts(array(
            'limit' => 10,
            'period' => 'all',
        ), $atts, 'cp_volunteer_leaderboard');

        global $wpdb;
        $volunteers_table = $wpdb->prefix . 'cp_volunteers';

        $where = '';
        if ($atts['period'] === 'month') {
            $where = 'AND h.activity_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)';
        } elseif ($atts['period'] === 'week') {
            $where = 'AND h.activity_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)';
        }

        $leaderboard = $wpdb->get_results($wpdb->prepare("
            SELECT v.id, v.first_name, v.last_name,
                   COALESCE(SUM(h.hours), 0) as total_hours,
                   COUNT(DISTINCT h.id) as activities_count
            FROM {$volunteers_table} v
            LEFT JOIN {$this->hours_table} h ON v.id = h.volunteer_id {$where}
            WHERE v.status = 'active'
            GROUP BY v.id
            HAVING total_hours > 0
            ORDER BY total_hours DESC
            LIMIT %d
        ", $atts['limit']));

        if (empty($leaderboard)) {
            return '<p class="cp-empty-state">' . esc_html__('No volunteer activity yet.', 'campaignpress') . '</p>';
        }

        ob_start();
        ?>
        <div class="cp-volunteer-leaderboard">
            <h3><?php esc_html_e('Top Volunteers', 'campaignpress'); ?></h3>
            <table class="cp-leaderboard-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Rank', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Volunteer', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Hours', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Activities', 'campaignpress'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rank = 1;
                    foreach ($leaderboard as $vol) :
                        $medal = '';
                        if ($rank === 1) $medal = '🥇';
                        elseif ($rank === 2) $medal = '🥈';
                        elseif ($rank === 3) $medal = '🥉';
                    ?>
                        <tr>
                            <td class="cp-rank"><?php echo $medal; ?> #<?php echo $rank; ?></td>
                            <td><?php echo esc_html($vol->first_name . ' ' . substr($vol->last_name, 0, 1) . '.'); ?></td>
                            <td><?php echo esc_html(number_format($vol->total_hours, 1)); ?></td>
                            <td><?php echo esc_html($vol->activities_count); ?></td>
                        </tr>
                    <?php
                        $rank++;
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Helper functions
     */

    private function get_current_volunteer_id() {
        // Check session/cookie for logged-in volunteer
        if (isset($_COOKIE['cp_volunteer_id'])) {
            return intval($_COOKIE['cp_volunteer_id']);
        }
        return null;
    }

    private function get_volunteer($id) {
        global $wpdb;
        $volunteers_table = $wpdb->prefix . 'cp_volunteers';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$volunteers_table} WHERE id = %d", $id));
    }

    private function get_volunteer_stats($volunteer_id) {
        global $wpdb;

        $total_hours = $wpdb->get_var($wpdb->prepare("
            SELECT COALESCE(SUM(hours), 0) FROM {$this->hours_table} WHERE volunteer_id = %d
        ", $volunteer_id));

        $shifts_completed = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$this->assignments_table}
            WHERE volunteer_id = %d AND checked_out_at IS NOT NULL
        ", $volunteer_id));

        $upcoming_shifts = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$this->assignments_table} a
            JOIN {$this->shifts_table} s ON a.shift_id = s.id
            WHERE a.volunteer_id = %d AND s.shift_date >= CURDATE()
        ", $volunteer_id));

        $rank = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) + 1 FROM (
                SELECT volunteer_id, SUM(hours) as total
                FROM {$this->hours_table}
                GROUP BY volunteer_id
                HAVING total > (SELECT SUM(hours) FROM {$this->hours_table} WHERE volunteer_id = %d)
            ) as rankings
        ", $volunteer_id));

        return array(
            'total_hours' => floatval($total_hours),
            'shifts_completed' => intval($shifts_completed),
            'upcoming_shifts' => intval($upcoming_shifts),
            'rank' => intval($rank),
        );
    }

    private function get_volunteer_upcoming_shifts($volunteer_id) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT s.*, a.status, a.checked_in
            FROM {$this->shifts_table} s
            JOIN {$this->assignments_table} a ON s.id = a.shift_id
            WHERE a.volunteer_id = %d
            AND s.shift_date >= CURDATE()
            ORDER BY s.shift_date ASC, s.start_time ASC
            LIMIT 10
        ", $volunteer_id));
    }

    /**
     * AJAX Handlers
     */

    public function ajax_volunteer_login() {
        check_ajax_referer('cp_volunteer_login', 'cp_volunteer_login_nonce');

        $email = sanitize_email($_POST['volunteer_email']);

        global $wpdb;
        $volunteers_table = $wpdb->prefix . 'cp_volunteers';
        $volunteer = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$volunteers_table} WHERE email = %s", $email));

        if (!$volunteer) {
            wp_send_json_error(array('message' => __('Email not found. Please check your email or sign up as a new volunteer.', 'campaignpress')));
        }

        // Set cookie for 30 days
        setcookie('cp_volunteer_id', $volunteer->id, time() + (30 * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN);

        wp_send_json_success(array(
            'message' => __('Login successful! Redirecting...', 'campaignpress'),
            'volunteer_id' => $volunteer->id,
        ));
    }

    public function ajax_signup_shift() {
        check_ajax_referer('campaignpress_volunteer_portal');

        $volunteer_id = $this->get_current_volunteer_id();
        $shift_id = intval($_POST['shift_id']);

        if (!$volunteer_id) {
            wp_send_json_error(array('message' => __('Please log in first.', 'campaignpress')));
        }

        global $wpdb;

        // Check if already signed up
        $existing = $wpdb->get_var($wpdb->prepare("
            SELECT id FROM {$this->assignments_table}
            WHERE volunteer_id = %d AND shift_id = %d
        ", $volunteer_id, $shift_id));

        if ($existing) {
            wp_send_json_error(array('message' => __('You are already signed up for this shift.', 'campaignpress')));
        }

        // Insert assignment
        $wpdb->insert($this->assignments_table, array(
            'volunteer_id' => $volunteer_id,
            'shift_id' => $shift_id,
            'status' => 'confirmed',
        ));

        // Update shift filled count
        $wpdb->query($wpdb->prepare("UPDATE {$this->shifts_table} SET filled = filled + 1 WHERE id = %d", $shift_id));

        wp_send_json_success(array('message' => __('Successfully signed up for shift!', 'campaignpress')));
    }

    public function ajax_log_hours() {
        check_ajax_referer('cp_log_hours', 'cp_log_hours_nonce');

        $volunteer_id = intval($_POST['volunteer_id']);
        $activity = sanitize_text_field($_POST['activity']);
        $hours = floatval($_POST['hours']);
        $activity_date = sanitize_text_field($_POST['activity_date']);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');

        global $wpdb;
        $wpdb->insert($this->hours_table, array(
            'volunteer_id' => $volunteer_id,
            'activity' => $activity,
            'hours' => $hours,
            'activity_date' => $activity_date,
            'notes' => $notes,
        ));

        wp_send_json_success(array('message' => __('Hours logged successfully!', 'campaignpress')));
    }

    public function ajax_update_profile() {
        check_ajax_referer('cp_update_profile', 'cp_profile_nonce');

        $volunteer_id = intval($_POST['volunteer_id']);

        global $wpdb;
        $volunteers_table = $wpdb->prefix . 'cp_volunteers';

        $wpdb->update(
            $volunteers_table,
            array(
                'first_name' => sanitize_text_field($_POST['first_name']),
                'last_name' => sanitize_text_field($_POST['last_name']),
                'email' => sanitize_email($_POST['email']),
                'phone' => sanitize_text_field($_POST['phone']),
                'skills' => sanitize_textarea_field($_POST['skills']),
                'availability' => sanitize_textarea_field($_POST['availability']),
            ),
            array('id' => $volunteer_id)
        );

        wp_send_json_success(array('message' => __('Profile updated successfully!', 'campaignpress')));
    }

    public function ajax_get_shifts() {
        $shifts = $this->render_available_shifts();
        wp_send_json_success(array('html' => $shifts));
    }

    /**
     * Admin page placeholders
     */
    public function render_admin_portal_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Volunteer Portal Settings', 'campaignpress'); ?></h1>
            <p><?php esc_html_e('Configure volunteer portal settings and view portal analytics.', 'campaignpress'); ?></p>
            <p><strong><?php esc_html_e('Portal URL:', 'campaignpress'); ?></strong> Add [cp_volunteer_portal] shortcode to any page</p>
        </div>
        <?php
    }

    public function render_admin_shifts_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Manage Volunteer Shifts', 'campaignpress'); ?></h1>
            <p><?php esc_html_e('Create and manage volunteer shift opportunities.', 'campaignpress'); ?></p>
        </div>
        <?php
    }

    public function render_admin_hours_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Volunteer Hours', 'campaignpress'); ?></h1>
            <p><?php esc_html_e('Review and verify volunteer hours.', 'campaignpress'); ?></p>
        </div>
        <?php
    }

    /**
     * Enqueue portal assets
     */
    public function enqueue_portal_assets() {
        // Only load on singular pages (posts/pages)
        if (!is_singular()) {
            return;
        }

        // Check if current post contains volunteer portal shortcode
        $post = get_post();
        if (!$post || !has_shortcode($post->post_content, 'cp_volunteer_portal')) {
            return;
        }

        // Enqueue volunteer portal CSS
        $css_file = CAMPAIGNPRESS_THEME_DIR . '/assets/css/volunteer-portal.css';
        if (file_exists($css_file)) {
            wp_enqueue_style('cp-volunteer-portal', CAMPAIGNPRESS_ASSETS_URI . '/css/volunteer-portal.css', array('campaignpress-style'), CAMPAIGNPRESS_VERSION);
        } else {
            // Fallback to inline styles if file doesn't exist
            wp_add_inline_style('campaignpress-style', $this->get_portal_styles());
        }

        // Enqueue volunteer portal JS
        $js_file = CAMPAIGNPRESS_THEME_DIR . '/assets/js/volunteer-portal.js';
        if (file_exists($js_file)) {
            wp_enqueue_script('cp-volunteer-portal', CAMPAIGNPRESS_ASSETS_URI . '/js/volunteer-portal.js', array('jquery'), CAMPAIGNPRESS_VERSION, true);
            
            // Localize script with translated strings and AJAX URL
            wp_localize_script('cp-volunteer-portal', 'campaignpress_vars', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('campaignpress_volunteer_portal'),
                'login_error' => __('An error occurred. Please try again.', 'campaignpress'),
                'hours_error' => __('An error occurred. Please try again.', 'campaignpress'),
                'profile_error' => __('An error occurred. Please try again.', 'campaignpress'),
            ));
        } else {
            // Fallback to inline scripts if file doesn't exist
            wp_add_inline_script('campaignpress-main', $this->get_portal_scripts());
        }
    }

    private function get_portal_styles() {
        return '
        .cp-volunteer-dashboard { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .cp-dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid #e5e5e5; }
        .cp-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .cp-stat-card { background: #f9f9f9; padding: 1.5rem; border-radius: 0.5rem; text-align: center; }
        .cp-stat-icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .cp-stat-value { font-size: 2.5rem; font-weight: 700; color: var(--wp--preset--color--primary, #0073aa); }
        .cp-stat-label { font-size: 0.875rem; color: #666; margin-top: 0.25rem; }
        .cp-dashboard-tabs { display: flex; gap: 0.5rem; margin-bottom: 2rem; border-bottom: 2px solid #e5e5e5; }
        .cp-tab-button { padding: 1rem 1.5rem; background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; font-weight: 600; transition: all 0.3s; }
        .cp-tab-button.active { border-bottom-color: var(--wp--preset--color--primary, #0073aa); color: var(--wp--preset--color--primary, #0073aa); }
        .cp-tab-content { display: none; }
        .cp-tab-content.active { display: block; }
        .cp-shifts-list, .cp-shifts-grid { display: grid; gap: 1rem; margin-top: 1.5rem; }
        .cp-shifts-grid { grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
        .cp-shift-card { background: #fff; border: 1px solid #e5e5e5; border-radius: 0.5rem; padding: 1.5rem; display: flex; gap: 1rem; }
        .cp-shift-date { background: var(--wp--preset--color--primary, #0073aa); color: #fff; padding: 1rem; border-radius: 0.5rem; text-align: center; min-width: 70px; }
        .cp-date-day { font-size: 2rem; font-weight: 700; line-height: 1; }
        .cp-date-month { font-size: 0.875rem; text-transform: uppercase; margin-top: 0.25rem; }
        .cp-empty-state { text-align: center; padding: 3rem; color: #999; font-style: italic; }
        .cp-volunteer-login-box { max-width: 400px; margin: 3rem auto; padding: 2rem; background: #f9f9f9; border-radius: 0.5rem; }
        .cp-button { padding: 0.75rem 1.5rem; border: none; border-radius: 0.25rem; cursor: pointer; font-weight: 600; transition: all 0.3s; }
        .cp-button-primary { background: var(--wp--preset--color--primary, #0073aa); color: #fff; }
        .cp-button-secondary { background: #e5e5e5; color: #333; }
        .cp-input { width: 100%; padding: 0.75rem; border: 1px solid #e5e5e5; border-radius: 0.25rem; }
        .form-field { margin-bottom: 1rem; }
        .form-field label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        .cp-badge { padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600; }
        .cp-badge-success { background: #d4edda; color: #155724; }
        .cp-badge-pending { background: #fff3cd; color: #856404; }
        ';
    }

    private function get_portal_scripts() {
        return '
        jQuery(document).ready(function($) {
            $(".cp-tab-button").click(function() {
                var tab = $(this).data("tab");
                $(".cp-tab-button").removeClass("active");
                $(this).addClass("active");
                $(".cp-tab-content").removeClass("active");
                $("[data-tab-content=" + tab + "]").addClass("active");
            });

            $("#cp-volunteer-login-form").submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: campaignpress_vars.ajax_url,
                    type: "POST",
                    data: $(this).serialize() + "&action=cp_volunteer_login",
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            $(".cp-login-message").html("<p class=\"error\">" + response.data.message + "</p>").show();
                        }
                    }
                });
            });

            $(document).on("click", ".cp-signup-shift-btn", function() {
                var shiftId = $(this).data("shift-id");
                $.post(campaignpress_vars.ajax_url, {
                    action: "cp_volunteer_signup_shift",
                    shift_id: shiftId,
                    _wpnonce: campaignpress_vars.nonce
                }, function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                });
            });
        });

        function cpVolunteerLogout() {
            document.cookie = "cp_volunteer_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            location.reload();
        }
        ';
    }
}

// Initialize volunteer portal
new CP_Volunteer_Portal();
