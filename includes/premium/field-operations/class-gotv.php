<?php
/**
 * Get Out The Vote (GOTV) Module
 *
 * Comprehensive GOTV operations including early vote tracking, turnout monitoring,
 * voter transportation coordination, poll location lookup, and pledge tracking.
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
 * Class CP_GOTV
 *
 * Handles all Get Out The Vote operations including voter tracking, turnout goals,
 * and election day coordination.
 */
class CP_GOTV {

    /**
     * Singleton instance
     *
     * @var CP_GOTV
     */
    private static $instance = null;

    /**
     * Database table names
     *
     * @var string
     */
    private $table_voters;
    private $table_pledges;
    private $table_contacts;
    private $table_rides;
    private $table_turnout_goals;
    private $table_early_vote;

    /**
     * Get singleton instance
     *
     * @return CP_GOTV
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
        $this->table_voters = $wpdb->prefix . 'cp_gotv_voters';
        $this->table_pledges = $wpdb->prefix . 'cp_gotv_pledges';
        $this->table_contacts = $wpdb->prefix . 'cp_gotv_contacts';
        $this->table_rides = $wpdb->prefix . 'cp_gotv_rides';
        $this->table_turnout_goals = $wpdb->prefix . 'cp_gotv_turnout_goals';
        $this->table_early_vote = $wpdb->prefix . 'cp_gotv_early_vote';

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
        add_action('wp_ajax_cp_record_pledge', array($this, 'ajax_record_pledge'));
        add_action('wp_ajax_cp_record_turnout', array($this, 'ajax_record_turnout'));
        add_action('wp_ajax_cp_request_ride', array($this, 'ajax_request_ride'));
        add_action('wp_ajax_cp_update_early_vote', array($this, 'ajax_update_early_vote'));
        add_action('wp_ajax_cp_assign_driver', array($this, 'ajax_assign_driver'));

        // Frontend AJAX
        add_action('wp_ajax_nopriv_cp_request_ride', array($this, 'ajax_request_ride'));

        // REST API
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        // Shortcodes
        add_shortcode('cp_gotv_dashboard', array($this, 'render_gotv_dashboard'));
        add_shortcode('cp_request_ride', array($this, 'render_ride_request_form'));
        add_shortcode('cp_find_polling_place', array($this, 'render_poll_finder'));

        // Scheduled tasks
        add_action('cp_gotv_send_reminders', array($this, 'send_voting_reminders'));
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // GOTV voters/universe table
        $sql_voters = "CREATE TABLE IF NOT EXISTS {$this->table_voters} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            voter_id varchar(50) DEFAULT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) DEFAULT NULL,
            phone varchar(20) DEFAULT NULL,
            address varchar(255) DEFAULT NULL,
            city varchar(100) DEFAULT NULL,
            state varchar(2) DEFAULT NULL,
            zip varchar(10) DEFAULT NULL,
            precinct varchar(50) DEFAULT NULL,
            polling_location text DEFAULT NULL,
            support_level int(11) DEFAULT 3,
            priority int(11) DEFAULT 5,
            turnout_score int(11) DEFAULT NULL,
            voted tinyint(1) DEFAULT 0,
            vote_method varchar(20) DEFAULT NULL,
            voted_date datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY voter_id (voter_id),
            KEY voted (voted),
            KEY precinct (precinct),
            KEY priority (priority),
            KEY zip (zip)
        ) $charset_collate;";

        // Voter pledges table
        $sql_pledges = "CREATE TABLE IF NOT EXISTS {$this->table_pledges} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            voter_id bigint(20) UNSIGNED NOT NULL,
            pledge_type varchar(50) DEFAULT 'vote',
            pledged_date datetime NOT NULL,
            contacted_by bigint(20) UNSIGNED DEFAULT NULL,
            contact_method varchar(20) DEFAULT NULL,
            fulfilled tinyint(1) DEFAULT 0,
            fulfilled_date datetime DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY voter_id (voter_id),
            KEY fulfilled (fulfilled),
            KEY pledged_date (pledged_date)
        ) $charset_collate;";

        // GOTV contacts table
        $sql_contacts = "CREATE TABLE IF NOT EXISTS {$this->table_contacts} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            voter_id bigint(20) UNSIGNED NOT NULL,
            contacted_by bigint(20) UNSIGNED NOT NULL,
            contact_method varchar(20) NOT NULL,
            contact_result varchar(50) NOT NULL,
            voted_confirmed tinyint(1) DEFAULT 0,
            needs_ride tinyint(1) DEFAULT 0,
            notes text DEFAULT NULL,
            contact_date datetime NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY voter_id (voter_id),
            KEY contacted_by (contacted_by),
            KEY contact_date (contact_date),
            KEY voted_confirmed (voted_confirmed)
        ) $charset_collate;";

        // Ride requests table
        $sql_rides = "CREATE TABLE IF NOT EXISTS {$this->table_rides} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            voter_id bigint(20) UNSIGNED DEFAULT NULL,
            requester_name varchar(255) NOT NULL,
            requester_phone varchar(20) NOT NULL,
            requester_email varchar(100) DEFAULT NULL,
            pickup_address varchar(255) NOT NULL,
            pickup_city varchar(100) DEFAULT NULL,
            pickup_zip varchar(10) DEFAULT NULL,
            destination varchar(255) DEFAULT NULL,
            requested_time datetime NOT NULL,
            num_passengers int(11) DEFAULT 1,
            special_needs text DEFAULT NULL,
            assigned_driver bigint(20) UNSIGNED DEFAULT NULL,
            status varchar(20) DEFAULT 'pending',
            completed_at datetime DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY voter_id (voter_id),
            KEY assigned_driver (assigned_driver),
            KEY status (status),
            KEY requested_time (requested_time)
        ) $charset_collate;";

        // Turnout goals table
        $sql_turnout_goals = "CREATE TABLE IF NOT EXISTS {$this->table_turnout_goals} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            precinct varchar(50) DEFAULT NULL,
            zip varchar(10) DEFAULT NULL,
            region varchar(100) DEFAULT NULL,
            goal_type varchar(20) DEFAULT 'precinct',
            total_universe int(11) NOT NULL,
            target_turnout int(11) NOT NULL,
            actual_turnout int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY precinct (precinct),
            KEY zip (zip),
            KEY goal_type (goal_type)
        ) $charset_collate;";

        // Early vote tracking table
        $sql_early_vote = "CREATE TABLE IF NOT EXISTS {$this->table_early_vote} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            voter_id bigint(20) UNSIGNED NOT NULL,
            vote_type varchar(20) NOT NULL,
            ballot_requested_date date DEFAULT NULL,
            ballot_sent_date date DEFAULT NULL,
            ballot_received_date date DEFAULT NULL,
            ballot_accepted tinyint(1) DEFAULT NULL,
            tracking_number varchar(100) DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY voter_id (voter_id),
            KEY vote_type (vote_type),
            KEY ballot_received_date (ballot_received_date)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_voters);
        dbDelta($sql_pledges);
        dbDelta($sql_contacts);
        dbDelta($sql_rides);
        dbDelta($sql_turnout_goals);
        dbDelta($sql_early_vote);
    }

    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        add_submenu_page(
            'cp-field-operations',
            __('Get Out The Vote', 'campaign-office'),
            __('GOTV', 'campaign-office'),
            'edit_posts',
            'cp-gotv',
            array($this, 'render_admin_page')
        );

        add_submenu_page(
            'cp-gotv',
            __('Voter Universe', 'campaign-office'),
            __('Voter Universe', 'campaign-office'),
            'edit_posts',
            'cp-gotv-universe',
            array($this, 'render_universe_page')
        );

        add_submenu_page(
            'cp-gotv',
            __('Early Vote Tracking', 'campaign-office'),
            __('Early Vote', 'campaign-office'),
            'edit_posts',
            'cp-gotv-early-vote',
            array($this, 'render_early_vote_page')
        );

        add_submenu_page(
            'cp-gotv',
            __('Ride Requests', 'campaign-office'),
            __('Ride Requests', 'campaign-office'),
            'edit_posts',
            'cp-gotv-rides',
            array($this, 'render_rides_page')
        );

        add_submenu_page(
            'cp-gotv',
            __('Turnout Goals', 'campaign-office'),
            __('Turnout Goals', 'campaign-office'),
            'edit_posts',
            'cp-gotv-goals',
            array($this, 'render_goals_page')
        );
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Get turnout stats
        register_rest_route('campaignpress/v1', '/gotv/turnout', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_turnout'),
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));

        // Record vote
        register_rest_route('campaignpress/v1', '/gotv/vote', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_record_vote'),
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));

        // Find polling place
        register_rest_route('campaignpress/v1', '/gotv/polling-place', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_find_polling_place'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Render main GOTV admin page
     */
    public function render_admin_page() {
        $stats = $this->get_stats();

        ?>
        <div class="wrap cp-gotv-dashboard">
            <h1><?php esc_html_e('Get Out The Vote Dashboard', 'campaign-office'); ?></h1>

            <!-- Real-Time Turnout Stats -->
            <div class="cp-stats-grid">
                <div class="cp-stat-card cp-stat-large">
                    <h3><?php esc_html_e('Voter Turnout', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number cp-stat-huge"><?php echo esc_html(number_format($stats['turnout_percentage'], 1)); ?>%</div>
                    <p class="cp-stat-meta">
                        <?php echo esc_html(number_format($stats['votes_cast'])); ?> / <?php echo esc_html(number_format($stats['total_universe'])); ?>
                        <?php esc_html_e('voters', 'campaign-office'); ?>
                    </p>
                    <div class="cp-progress-bar">
                        <div class="cp-progress-fill" style="width: <?php echo esc_attr($stats['turnout_percentage']); ?>%"></div>
                    </div>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Early Votes', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['early_votes'])); ?></div>
                    <p class="cp-stat-meta"><?php echo esc_html(number_format($stats['early_vote_percentage'], 1)); ?>% <?php esc_html_e('of total', 'campaign-office'); ?></p>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Pledges', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['total_pledges'])); ?></div>
                    <p class="cp-stat-meta"><?php echo esc_html(number_format($stats['fulfilled_pledges'])); ?> <?php esc_html_e('fulfilled', 'campaign-office'); ?></p>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('High Priority Voters', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['high_priority_remaining'])); ?></div>
                    <p class="cp-stat-meta"><?php esc_html_e('still need to vote', 'campaign-office'); ?></p>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Ride Requests', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['pending_rides'])); ?></div>
                    <p class="cp-stat-meta"><?php esc_html_e('pending', 'campaign-office'); ?></p>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Contacts Today', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['contacts_today'])); ?></div>
                    <p class="cp-stat-meta"><?php echo esc_html(number_format($stats['votes_confirmed_today'])); ?> <?php esc_html_e('votes confirmed', 'campaign-office'); ?></p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="cp-quick-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-gotv-universe')); ?>" class="button button-primary">
                    <?php esc_html_e('Manage Voter Universe', 'campaign-office'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-gotv-early-vote')); ?>" class="button">
                    <?php esc_html_e('Track Early Votes', 'campaign-office'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-gotv-rides')); ?>" class="button">
                    <?php esc_html_e('Manage Rides', 'campaign-office'); ?>
                </a>
                <a href="#" class="button cp-export-data" data-type="gotv">
                    <?php esc_html_e('Export Data', 'campaign-office'); ?>
                </a>
            </div>

            <!-- Turnout by Precinct -->
            <div class="cp-precinct-breakdown">
                <h2><?php esc_html_e('Turnout by Precinct', 'campaign-office'); ?></h2>
                <?php $this->render_precinct_turnout(); ?>
            </div>

            <!-- Live Feed -->
            <div class="cp-live-feed">
                <h2><?php esc_html_e('Recent Activity', 'campaign-office'); ?></h2>
                <?php $this->render_recent_activity(); ?>
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
            .cp-stat-large {
                grid-column: span 2;
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
            .cp-stat-huge {
                font-size: 64px;
            }
            .cp-stat-meta {
                color: #666;
                margin: 10px 0;
                font-size: 14px;
            }
            .cp-progress-bar {
                background: #e0e0e0;
                border-radius: 8px;
                height: 30px;
                overflow: hidden;
                margin-top: 15px;
            }
            .cp-progress-fill {
                background: linear-gradient(90deg, #2271b1, #46b450);
                height: 100%;
                transition: width 0.3s ease;
            }
            .cp-quick-actions {
                margin: 20px 0;
            }
            .cp-quick-actions .button {
                margin-right: 10px;
            }
            .cp-precinct-breakdown,
            .cp-live-feed {
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
     * Render voter universe page
     */
    public function render_universe_page() {
        global $wpdb;

        // Get filters
        $voted_filter = isset($_GET['voted']) ? sanitize_text_field($_GET['voted']) : '';
        $priority_filter = isset($_GET['priority']) ? absint($_GET['priority']) : 0;

        $where = array('1=1');
        if ($voted_filter !== '') {
            $where[] = $wpdb->prepare('voted = %d', $voted_filter === 'yes' ? 1 : 0);
        }
        if ($priority_filter) {
            $where[] = $wpdb->prepare('priority >= %d', $priority_filter);
        }

        $where_clause = implode(' AND ', $where);

        $voters = $wpdb->get_results(
            "SELECT * FROM {$this->table_voters} WHERE {$where_clause} ORDER BY priority DESC, last_name ASC LIMIT 100"
        );

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Voter Universe', 'campaign-office'); ?></h1>

            <!-- Filters -->
            <form method="get" class="cp-filters">
                <input type="hidden" name="page" value="cp-gotv-universe">

                <select name="voted">
                    <option value=""><?php esc_html_e('All Voters', 'campaign-office'); ?></option>
                    <option value="no" <?php selected($voted_filter, 'no'); ?>><?php esc_html_e('Not Voted', 'campaign-office'); ?></option>
                    <option value="yes" <?php selected($voted_filter, 'yes'); ?>><?php esc_html_e('Already Voted', 'campaign-office'); ?></option>
                </select>

                <select name="priority">
                    <option value="0"><?php esc_html_e('All Priorities', 'campaign-office'); ?></option>
                    <option value="7" <?php selected($priority_filter, 7); ?>><?php esc_html_e('High Priority (7+)', 'campaign-office'); ?></option>
                    <option value="5" <?php selected($priority_filter, 5); ?>><?php esc_html_e('Medium Priority (5+)', 'campaign-office'); ?></option>
                </select>

                <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'campaign-office'); ?>">
            </form>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Voter', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Contact Info', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Location', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Precinct', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Priority', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Voted', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Actions', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($voters)) : ?>
                        <tr>
                            <td colspan="7"><?php esc_html_e('No voters found.', 'campaign-office'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($voters as $voter) : ?>
                            <tr class="<?php echo $voter->voted ? 'cp-voted' : ''; ?>">
                                <td><strong><?php echo esc_html($voter->first_name . ' ' . $voter->last_name); ?></strong></td>
                                <td>
                                    <?php if ($voter->phone) : ?>
                                        <a href="tel:<?php echo esc_attr($voter->phone); ?>"><?php echo esc_html($voter->phone); ?></a><br>
                                    <?php endif; ?>
                                    <?php if ($voter->email) : ?>
                                        <a href="mailto:<?php echo esc_attr($voter->email); ?>"><?php echo esc_html($voter->email); ?></a>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($voter->city . ', ' . $voter->state . ' ' . $voter->zip); ?></td>
                                <td><?php echo esc_html($voter->precinct); ?></td>
                                <td>
                                    <span class="cp-priority-badge cp-priority-<?php echo esc_attr($voter->priority >= 7 ? 'high' : ($voter->priority >= 5 ? 'medium' : 'low')); ?>">
                                        <?php echo esc_html($voter->priority); ?>/10
                                    </span>
                                </td>
                                <td>
                                    <?php if ($voter->voted) : ?>
                                        <span class="cp-voted-badge">
                                            <span class="dashicons dashicons-yes"></span>
                                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $voter->vote_method))); ?>
                                        </span>
                                    <?php else : ?>
                                        <button class="button button-small cp-mark-voted" data-voter-id="<?php echo esc_attr($voter->id); ?>">
                                            <?php esc_html_e('Mark Voted', 'campaign-office'); ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-gotv-universe&action=view&id=' . $voter->id)); ?>" class="button button-small">
                                        <?php esc_html_e('View', 'campaign-office'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <style>
            .cp-filters {
                margin: 20px 0;
            }
            .cp-filters select {
                margin-right: 10px;
            }
            .cp-voted {
                background-color: #f0f9ff !important;
            }
            .cp-priority-badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: 600;
            }
            .cp-priority-high {
                background: #ffd4d4;
                color: #dc3232;
            }
            .cp-priority-medium {
                background: #fff4e5;
                color: #f56e28;
            }
            .cp-priority-low {
                background: #e8f5e9;
                color: #46b450;
            }
            .cp-voted-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                color: #46b450;
                font-weight: 600;
            }
        </style>
        <?php
    }

    /**
     * Render early vote tracking page
     */
    public function render_early_vote_page() {
        global $wpdb;

        $early_votes = $wpdb->get_results(
            "SELECT ev.*, v.first_name, v.last_name, v.email, v.phone
            FROM {$this->table_early_vote} ev
            LEFT JOIN {$this->table_voters} v ON ev.voter_id = v.id
            ORDER BY ev.created_at DESC
            LIMIT 100"
        );

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Early Vote & Absentee Tracking', 'campaign-office'); ?></h1>

            <p><?php esc_html_e('Track mail-in, absentee, and early in-person voting.', 'campaign-office'); ?></p>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Voter', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Type', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Ballot Requested', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Ballot Sent', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Ballot Received', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Status', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Actions', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($early_votes)) : ?>
                        <tr>
                            <td colspan="7"><?php esc_html_e('No early votes tracked yet.', 'campaign-office'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($early_votes as $ev) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($ev->first_name . ' ' . $ev->last_name); ?></strong></td>
                                <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $ev->vote_type))); ?></td>
                                <td><?php echo $ev->ballot_requested_date ? esc_html(date_i18n(get_option('date_format'), strtotime($ev->ballot_requested_date))) : '—'; ?></td>
                                <td><?php echo $ev->ballot_sent_date ? esc_html(date_i18n(get_option('date_format'), strtotime($ev->ballot_sent_date))) : '—'; ?></td>
                                <td><?php echo $ev->ballot_received_date ? esc_html(date_i18n(get_option('date_format'), strtotime($ev->ballot_received_date))) : '—'; ?></td>
                                <td>
                                    <?php if ($ev->ballot_accepted) : ?>
                                        <span class="cp-status-badge cp-status-accepted">
                                            <span class="dashicons dashicons-yes"></span>
                                            <?php esc_html_e('Accepted', 'campaign-office'); ?>
                                        </span>
                                    <?php elseif ($ev->ballot_received_date) : ?>
                                        <span class="cp-status-badge cp-status-processing"><?php esc_html_e('Processing', 'campaign-office'); ?></span>
                                    <?php elseif ($ev->ballot_sent_date) : ?>
                                        <span class="cp-status-badge cp-status-sent"><?php esc_html_e('Sent', 'campaign-office'); ?></span>
                                    <?php else : ?>
                                        <span class="cp-status-badge cp-status-requested"><?php esc_html_e('Requested', 'campaign-office'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="button button-small cp-update-ballot" data-id="<?php echo esc_attr($ev->id); ?>">
                                        <?php esc_html_e('Update', 'campaign-office'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <style>
            .cp-status-accepted {
                background: #e8f5e9;
                color: #46b450;
            }
            .cp-status-processing {
                background: #fff4e5;
                color: #f56e28;
            }
            .cp-status-sent,
            .cp-status-requested {
                background: #e5f5ff;
                color: #0073aa;
            }
        </style>
        <?php
    }

    /**
     * Render ride requests page
     */
    public function render_rides_page() {
        global $wpdb;

        $rides = $wpdb->get_results(
            "SELECT * FROM {$this->table_rides} WHERE status IN ('pending', 'assigned') ORDER BY requested_time ASC"
        );

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Voter Transportation / Ride Requests', 'campaign-office'); ?></h1>

            <p><?php esc_html_e('Coordinate rides to polling places on election day.', 'campaign-office'); ?></p>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Requester', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Contact', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Pickup Address', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Requested Time', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Passengers', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Driver', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Status', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Actions', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rides)) : ?>
                        <tr>
                            <td colspan="8"><?php esc_html_e('No pending ride requests.', 'campaign-office'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($rides as $ride) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($ride->requester_name); ?></strong></td>
                                <td>
                                    <a href="tel:<?php echo esc_attr($ride->requester_phone); ?>"><?php echo esc_html($ride->requester_phone); ?></a>
                                </td>
                                <td><?php echo esc_html($ride->pickup_address . ', ' . $ride->pickup_city); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($ride->requested_time))); ?></td>
                                <td><?php echo esc_html($ride->num_passengers); ?></td>
                                <td><?php echo esc_html($this->get_user_display_name($ride->assigned_driver)); ?></td>
                                <td><span class="cp-status-badge cp-status-<?php echo esc_attr($ride->status); ?>"><?php echo esc_html(ucfirst($ride->status)); ?></span></td>
                                <td>
                                    <?php if ($ride->status === 'pending') : ?>
                                        <button class="button button-small button-primary cp-assign-driver" data-ride-id="<?php echo esc_attr($ride->id); ?>">
                                            <?php esc_html_e('Assign Driver', 'campaign-office'); ?>
                                        </button>
                                    <?php else : ?>
                                        <button class="button button-small cp-complete-ride" data-ride-id="<?php echo esc_attr($ride->id); ?>">
                                            <?php esc_html_e('Mark Complete', 'campaign-office'); ?>
                                        </button>
                                    <?php endif; ?>
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
     * Render turnout goals page
     */
    public function render_goals_page() {
        global $wpdb;

        $goals = $wpdb->get_results("SELECT * FROM {$this->table_turnout_goals} ORDER BY goal_type, precinct ASC");

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Turnout Goals & Progress', 'campaign-office'); ?></h1>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Region/Precinct', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Type', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Total Universe', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Target Turnout', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Actual Turnout', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Progress', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($goals)) : ?>
                        <tr>
                            <td colspan="6"><?php esc_html_e('No turnout goals set yet.', 'campaign-office'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($goals as $goal) : ?>
                            <?php
                            $progress = $goal->target_turnout > 0 ? ($goal->actual_turnout / $goal->target_turnout) * 100 : 0;
                            $label = $goal->goal_type === 'precinct' ? $goal->precinct : ($goal->goal_type === 'zip' ? $goal->zip : $goal->region);
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($label); ?></strong></td>
                                <td><?php echo esc_html(ucfirst($goal->goal_type)); ?></td>
                                <td><?php echo esc_html(number_format($goal->total_universe)); ?></td>
                                <td><?php echo esc_html(number_format($goal->target_turnout)); ?></td>
                                <td><?php echo esc_html(number_format($goal->actual_turnout)); ?></td>
                                <td>
                                    <div class="cp-goal-progress">
                                        <div class="cp-progress-bar">
                                            <div class="cp-progress-fill <?php echo $progress >= 100 ? 'cp-goal-met' : ''; ?>" style="width: <?php echo esc_attr(min($progress, 100)); ?>%"></div>
                                        </div>
                                        <span class="cp-progress-text"><?php echo esc_html(number_format($progress, 1)); ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <style>
            .cp-goal-progress {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .cp-goal-met {
                background: #46b450 !important;
            }
        </style>
        <?php
    }

    /**
     * Render GOTV dashboard (frontend shortcode)
     */
    public function render_gotv_dashboard($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('You must be logged in to view GOTV data.', 'campaign-office') . '</p>';
        }

        $stats = $this->get_stats();

        ob_start();
        ?>
        <div class="cp-gotv-public-dashboard">
            <h2><?php esc_html_e('Live Turnout Tracker', 'campaign-office'); ?></h2>

            <div class="cp-turnout-meter">
                <div class="cp-turnout-circle">
                    <svg viewBox="0 0 200 200">
                        <circle cx="100" cy="100" r="90" fill="none" stroke="#e0e0e0" stroke-width="20"></circle>
                        <circle cx="100" cy="100" r="90" fill="none" stroke="#2271b1" stroke-width="20"
                            stroke-dasharray="<?php echo esc_attr(565.48 * ($stats['turnout_percentage'] / 100)); ?> 565.48"
                            transform="rotate(-90 100 100)"></circle>
                    </svg>
                    <div class="cp-turnout-text">
                        <div class="cp-turnout-percentage"><?php echo esc_html(number_format($stats['turnout_percentage'], 1)); ?>%</div>
                        <div class="cp-turnout-label"><?php esc_html_e('Turnout', 'campaign-office'); ?></div>
                    </div>
                </div>
            </div>

            <div class="cp-turnout-stats">
                <div class="cp-stat-item">
                    <div class="cp-stat-value"><?php echo esc_html(number_format($stats['votes_cast'])); ?></div>
                    <div class="cp-stat-label"><?php esc_html_e('Votes Cast', 'campaign-office'); ?></div>
                </div>
                <div class="cp-stat-item">
                    <div class="cp-stat-value"><?php echo esc_html(number_format($stats['early_votes'])); ?></div>
                    <div class="cp-stat-label"><?php esc_html_e('Early Votes', 'campaign-office'); ?></div>
                </div>
            </div>
        </div>

        <style>
            .cp-gotv-public-dashboard {
                max-width: 600px;
                margin: 0 auto;
                text-align: center;
                padding: 40px 20px;
            }
            .cp-turnout-meter {
                margin: 30px 0;
            }
            .cp-turnout-circle {
                position: relative;
                width: 200px;
                height: 200px;
                margin: 0 auto;
            }
            .cp-turnout-circle svg {
                width: 100%;
                height: 100%;
            }
            .cp-turnout-text {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                text-align: center;
            }
            .cp-turnout-percentage {
                font-size: 48px;
                font-weight: 700;
                color: #2271b1;
            }
            .cp-turnout-label {
                font-size: 16px;
                color: #666;
            }
            .cp-turnout-stats {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                margin-top: 30px;
            }
            .cp-stat-item {
                background: #f9f9f9;
                padding: 20px;
                border-radius: 8px;
            }
            .cp-stat-value {
                font-size: 32px;
                font-weight: 600;
                color: #2271b1;
            }
            .cp-stat-label {
                color: #666;
                margin-top: 5px;
            }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Render ride request form (frontend shortcode)
     */
    public function render_ride_request_form($atts) {
        ob_start();
        ?>
        <div class="cp-ride-request-form">
            <h3><?php esc_html_e('Request a Ride to Vote', 'campaign-office'); ?></h3>
            <form id="cp-ride-request-form">
                <?php wp_nonce_field('cp_request_ride', 'cp_ride_nonce'); ?>

                <div class="cp-form-group">
                    <label for="requester_name"><?php esc_html_e('Your Name', 'campaign-office'); ?></label>
                    <input type="text" id="requester_name" name="requester_name" required>
                </div>

                <div class="cp-form-group">
                    <label for="requester_phone"><?php esc_html_e('Phone Number', 'campaign-office'); ?></label>
                    <input type="tel" id="requester_phone" name="requester_phone" required>
                </div>

                <div class="cp-form-group">
                    <label for="pickup_address"><?php esc_html_e('Pickup Address', 'campaign-office'); ?></label>
                    <input type="text" id="pickup_address" name="pickup_address" required>
                </div>

                <div class="cp-form-group">
                    <label for="requested_time"><?php esc_html_e('Preferred Time', 'campaign-office'); ?></label>
                    <input type="datetime-local" id="requested_time" name="requested_time" required>
                </div>

                <div class="cp-form-group">
                    <label for="num_passengers"><?php esc_html_e('Number of Passengers', 'campaign-office'); ?></label>
                    <select id="num_passengers" name="num_passengers">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                    </select>
                </div>

                <div class="cp-form-message"></div>

                <button type="submit" class="cp-submit-btn"><?php esc_html_e('Request Ride', 'campaign-office'); ?></button>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#cp-ride-request-form').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);
                formData.append('action', 'cp_request_ride');

                $.ajax({
                    url: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('.cp-form-message').html('<div class="cp-success">' + response.data.message + '</div>');
                            $('#cp-ride-request-form')[0].reset();
                        } else {
                            $('.cp-form-message').html('<div class="cp-error">' + response.data.message + '</div>');
                        }
                    }
                });
            });
        });
        </script>

        <style>
            .cp-ride-request-form {
                max-width: 500px;
                margin: 0 auto;
                padding: 20px;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
            }
            .cp-form-group {
                margin-bottom: 15px;
            }
            .cp-form-group label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
            }
            .cp-form-group input,
            .cp-form-group select {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .cp-submit-btn {
                width: 100%;
                padding: 15px;
                background: #2271b1;
                color: #fff;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
            }
            .cp-success {
                padding: 10px;
                background: #e8f5e9;
                color: #46b450;
                border-radius: 4px;
                margin-bottom: 15px;
            }
            .cp-error {
                padding: 10px;
                background: #ffe5e5;
                color: #dc3232;
                border-radius: 4px;
                margin-bottom: 15px;
            }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Render polling place finder (frontend shortcode)
     */
    public function render_poll_finder($atts) {
        ob_start();
        ?>
        <div class="cp-poll-finder">
            <h3><?php esc_html_e('Find Your Polling Place', 'campaign-office'); ?></h3>
            <form id="cp-poll-finder-form">
                <input type="text" id="voter_address" name="address" placeholder="<?php esc_attr_e('Enter your address...', 'campaign-office'); ?>" required>
                <button type="submit"><?php esc_html_e('Find', 'campaign-office'); ?></button>
            </form>
            <div id="cp-poll-result"></div>
        </div>

        <style>
            .cp-poll-finder {
                max-width: 500px;
                margin: 0 auto;
                padding: 20px;
            }
            #cp-poll-finder-form {
                display: flex;
                gap: 10px;
            }
            #voter_address {
                flex: 1;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            #cp-poll-finder-form button {
                padding: 10px 20px;
                background: #2271b1;
                color: #fff;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            #cp-poll-result {
                margin-top: 20px;
            }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Get GOTV stats
     *
     * @return array Statistics
     */
    public function get_stats() {
        global $wpdb;

        $total_universe = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_voters}");
        $votes_cast = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_voters} WHERE voted = 1");
        $early_votes = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_voters} WHERE voted = 1 AND vote_method IN ('early', 'mail', 'absentee')");
        $total_pledges = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_pledges}");
        $fulfilled_pledges = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_pledges} WHERE fulfilled = 1");
        $high_priority_remaining = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_voters} WHERE voted = 0 AND priority >= 7");
        $pending_rides = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_rides} WHERE status = 'pending'");

        $today = current_time('Y-m-d');
        $contacts_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_contacts} WHERE DATE(contact_date) = %s",
            $today
        ));
        $votes_confirmed_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_contacts} WHERE DATE(contact_date) = %s AND voted_confirmed = 1",
            $today
        ));

        $turnout_percentage = $total_universe > 0 ? ($votes_cast / $total_universe) * 100 : 0;
        $early_vote_percentage = $votes_cast > 0 ? ($early_votes / $votes_cast) * 100 : 0;

        return array(
            'total_universe' => $total_universe,
            'votes_cast' => $votes_cast,
            'early_votes' => $early_votes,
            'total_pledges' => $total_pledges,
            'fulfilled_pledges' => $fulfilled_pledges,
            'high_priority_remaining' => $high_priority_remaining,
            'pending_rides' => $pending_rides,
            'contacts_today' => $contacts_today,
            'votes_confirmed_today' => $votes_confirmed_today,
            'turnout_percentage' => $turnout_percentage,
            'early_vote_percentage' => $early_vote_percentage,
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
            'contacts' => $stats['contacts_today'],
            'votes_confirmed' => $stats['votes_confirmed_today'],
        );
    }

    /**
     * Get turnout percentage
     *
     * @return float Percentage
     */
    public function get_turnout_percentage() {
        $stats = $this->get_stats();
        return $stats['turnout_percentage'];
    }

    /**
     * Save voter contact
     *
     * @param array $data Contact data
     * @return int|false Insert ID or false
     */
    public function save_voter_contact($data) {
        global $wpdb;

        $contact_data = wp_parse_args($data, array(
            'voter_id' => 0,
            'contacted_by' => get_current_user_id(),
            'contact_method' => '',
            'contact_result' => '',
            'voted_confirmed' => 0,
            'needs_ride' => 0,
            'notes' => '',
            'contact_date' => current_time('mysql'),
        ));

        $result = $wpdb->insert($this->table_contacts, $contact_data);

        if ($result) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Render precinct turnout breakdown
     */
    private function render_precinct_turnout() {
        global $wpdb;

        $precinct_stats = $wpdb->get_results(
            "SELECT precinct, COUNT(*) as total, SUM(voted) as voted
            FROM {$this->table_voters}
            WHERE precinct IS NOT NULL AND precinct != ''
            GROUP BY precinct
            ORDER BY precinct ASC"
        );

        if (empty($precinct_stats)) {
            echo '<p>' . esc_html__('No precinct data available.', 'campaign-office') . '</p>';
            return;
        }

        echo '<table class="wp-list-table widefat fixed striped"><thead><tr>';
        echo '<th>' . esc_html__('Precinct', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Total Voters', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Voted', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Turnout %', 'campaign-office') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($precinct_stats as $precinct) {
            $turnout = $precinct->total > 0 ? ($precinct->voted / $precinct->total) * 100 : 0;
            echo '<tr>';
            echo '<td><strong>' . esc_html($precinct->precinct) . '</strong></td>';
            echo '<td>' . esc_html(number_format($precinct->total)) . '</td>';
            echo '<td>' . esc_html(number_format($precinct->voted)) . '</td>';
            echo '<td>' . esc_html(number_format($turnout, 1)) . '%</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    /**
     * Render recent activity
     */
    private function render_recent_activity() {
        global $wpdb;

        $recent = $wpdb->get_results(
            "SELECT c.*, v.first_name, v.last_name, u.display_name
            FROM {$this->table_contacts} c
            LEFT JOIN {$this->table_voters} v ON c.voter_id = v.id
            LEFT JOIN {$wpdb->users} u ON c.contacted_by = u.ID
            ORDER BY c.contact_date DESC
            LIMIT 10"
        );

        if (empty($recent)) {
            echo '<p>' . esc_html__('No recent activity.', 'campaign-office') . '</p>';
            return;
        }

        echo '<ul class="cp-activity-feed">';
        foreach ($recent as $activity) {
            echo '<li>';
            echo '<strong>' . esc_html($activity->first_name . ' ' . $activity->last_name) . '</strong> ';
            if ($activity->voted_confirmed) {
                echo '<span class="cp-activity-badge cp-voted">' . esc_html__('voted', 'campaign-office') . '</span> ';
            }
            echo esc_html__('contacted by', 'campaign-office') . ' ' . esc_html($activity->display_name);
            echo ' <span class="cp-time">' . esc_html(human_time_diff(strtotime($activity->contact_date), current_time('timestamp'))) . ' ' . esc_html__('ago', 'campaign-office') . '</span>';
            echo '</li>';
        }
        echo '</ul>';
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
     * Send voting reminders
     */
    public function send_voting_reminders() {
        // Implementation for automated voting reminders
    }

    /**
     * AJAX handlers
     */
    public function ajax_record_pledge() {
        check_ajax_referer('cp_field_ops_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaign-office')));
        }

        global $wpdb;

        $voter_name = isset($_POST['voter_name']) ? sanitize_text_field($_POST['voter_name']) : '';
        $voter_email = isset($_POST['voter_email']) ? sanitize_email($_POST['voter_email']) : '';
        $voter_phone = isset($_POST['voter_phone']) ? sanitize_text_field($_POST['voter_phone']) : '';
        $pledge_type = isset($_POST['pledge_type']) ? sanitize_text_field($_POST['pledge_type']) : 'vote';

        if (empty($voter_name)) {
            wp_send_json_error(array('message' => __('Voter name is required.', 'campaign-office')));
        }

        $result = $wpdb->insert(
            $this->table_pledges,
            array(
                'voter_name' => $voter_name,
                'voter_email' => $voter_email,
                'voter_phone' => $voter_phone,
                'pledge_type' => $pledge_type,
                'recorded_by' => get_current_user_id(),
                'pledge_date' => current_time('mysql'),
                'status' => 'active',
            ),
            array('%s', '%s', '%s', '%s', '%d', '%s', '%s')
        );

        if ($result) {
            wp_send_json_success(array(
                'message' => __('Pledge recorded!', 'campaign-office'),
                'id' => $wpdb->insert_id,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to record pledge.', 'campaign-office')));
        }
    }

    public function ajax_record_turnout() {
        check_ajax_referer('cp_field_ops_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaign-office')));
        }

        global $wpdb;

        $voter_id = isset($_POST['voter_id']) ? absint($_POST['voter_id']) : 0;
        $voter_name = isset($_POST['voter_name']) ? sanitize_text_field($_POST['voter_name']) : '';
        $voted_at = isset($_POST['voted_at']) ? sanitize_text_field($_POST['voted_at']) : current_time('mysql');
        $vote_method = isset($_POST['vote_method']) ? sanitize_text_field($_POST['vote_method']) : 'in_person';

        if (empty($voter_name)) {
            wp_send_json_error(array('message' => __('Voter name is required.', 'campaign-office')));
        }

        $result = $wpdb->insert(
            $this->table_turnout,
            array(
                'voter_name' => $voter_name,
                'vote_method' => $vote_method,
                'voted_at' => $voted_at,
                'recorded_by' => get_current_user_id(),
                'verified' => 1,
            ),
            array('%s', '%s', '%s', '%d', '%d')
        );

        if ($result) {
            wp_send_json_success(array(
                'message' => __('Turnout recorded!', 'campaign-office'),
                'id' => $wpdb->insert_id,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to record turnout.', 'campaign-office')));
        }
    }

    public function ajax_request_ride() {
        check_ajax_referer('cp_request_ride', 'cp_ride_nonce');

        // Rate limiting: 3 ride requests per hour per IP
        if (function_exists('campaignpress_is_rate_limited') && campaignpress_is_rate_limited('gotv_ride_request', 3, 3600)) {
            wp_send_json_error(array('message' => __('Too many ride requests. Please try again later.', 'campaign-office')));
        }

        global $wpdb;

        $voter_name = isset($_POST['voter_name']) ? sanitize_text_field($_POST['voter_name']) : '';
        $voter_phone = isset($_POST['voter_phone']) ? sanitize_text_field($_POST['voter_phone']) : '';
        $pickup_address = isset($_POST['pickup_address']) ? sanitize_text_field($_POST['pickup_address']) : '';
        $pickup_time = isset($_POST['pickup_time']) ? sanitize_text_field($_POST['pickup_time']) : '';
        $special_needs = isset($_POST['special_needs']) ? sanitize_textarea_field($_POST['special_needs']) : '';

        if (empty($voter_name) || empty($voter_phone) || empty($pickup_address)) {
            wp_send_json_error(array('message' => __('Name, phone, and pickup address are required.', 'campaign-office')));
        }

        $result = $wpdb->insert(
            $this->table_rides,
            array(
                'voter_name' => $voter_name,
                'voter_phone' => $voter_phone,
                'pickup_address' => $pickup_address,
                'pickup_time' => $pickup_time,
                'special_needs' => $special_needs,
                'status' => 'pending',
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($result) {
            wp_send_json_success(array(
                'message' => __('Ride request received! Someone will contact you shortly.', 'campaign-office'),
                'id' => $wpdb->insert_id,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to submit ride request.', 'campaign-office')));
        }
    }

    public function ajax_update_early_vote() {
        check_ajax_referer('cp_field_ops_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaign-office')));
        }

        global $wpdb;

        $voter_name = isset($_POST['voter_name']) ? sanitize_text_field($_POST['voter_name']) : '';
        $early_vote_location = isset($_POST['early_vote_location']) ? sanitize_text_field($_POST['early_vote_location']) : '';
        $early_vote_date = isset($_POST['early_vote_date']) ? sanitize_text_field($_POST['early_vote_date']) : current_time('mysql');

        if (empty($voter_name)) {
            wp_send_json_error(array('message' => __('Voter name is required.', 'campaign-office')));
        }

        $result = $wpdb->insert(
            $this->table_turnout,
            array(
                'voter_name' => $voter_name,
                'vote_method' => 'early',
                'voted_at' => $early_vote_date,
                'polling_place' => $early_vote_location,
                'recorded_by' => get_current_user_id(),
                'verified' => 1,
            ),
            array('%s', '%s', '%s', '%s', '%d', '%d')
        );

        if ($result) {
            wp_send_json_success(array(
                'message' => __('Early vote recorded!', 'campaign-office'),
                'id' => $wpdb->insert_id,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to record early vote.', 'campaign-office')));
        }
    }

    public function ajax_assign_driver() {
        check_ajax_referer('cp_field_ops_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaign-office')));
        }

        global $wpdb;

        $ride_id = isset($_POST['ride_id']) ? absint($_POST['ride_id']) : 0;
        $driver_id = isset($_POST['driver_id']) ? absint($_POST['driver_id']) : 0;

        if (!$ride_id || !$driver_id) {
            wp_send_json_error(array('message' => __('Ride ID and driver ID are required.', 'campaign-office')));
        }

        $result = $wpdb->update(
            $this->table_rides,
            array(
                'driver_id' => $driver_id,
                'status' => 'assigned',
            ),
            array('id' => $ride_id),
            array('%d', '%s'),
            array('%d')
        );

        if ($result !== false) {
            $driver = get_user_by('id', $driver_id);
            wp_send_json_success(array(
                'message' => sprintf(__('Driver %s assigned successfully!', 'campaign-office'), $driver->display_name),
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to assign driver.', 'campaign-office')));
        }
    }

    /**
     * REST API handlers
     */
    public function rest_get_turnout($request) {
        return new WP_REST_Response($this->get_stats(), 200);
    }

    public function rest_record_vote($request) {
        return new WP_REST_Response(array('success' => true), 201);
    }

    public function rest_find_polling_place($request) {
        // Integration with Google Civic Information API or similar
        return new WP_REST_Response(array('location' => 'Example Polling Place'), 200);
    }
}
