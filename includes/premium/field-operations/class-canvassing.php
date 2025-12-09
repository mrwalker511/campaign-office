<?php
/**
 * Canvassing Module
 *
 * Comprehensive door-to-door canvassing system with walk lists, territory management,
 * mobile-responsive interface, survey tools, GPS tracking, and offline capability.
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
 * Class CP_Canvassing
 *
 * Handles all canvassing operations including walk lists, turf cutting, door knocking,
 * and survey data collection.
 */
class CP_Canvassing {

    /**
     * Singleton instance
     *
     * @var CP_Canvassing
     */
    private static $instance = null;

    /**
     * Database table names
     *
     * @var string
     */
    private $table_walk_lists;
    private $table_turfs;
    private $table_interactions;
    private $table_surveys;
    private $table_survey_responses;

    /**
     * Get singleton instance
     *
     * @return CP_Canvassing
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
        $this->table_walk_lists = $wpdb->prefix . 'cp_walk_lists';
        $this->table_turfs = $wpdb->prefix . 'cp_turfs';
        $this->table_interactions = $wpdb->prefix . 'cp_canvass_interactions';
        $this->table_surveys = $wpdb->prefix . 'cp_canvass_surveys';
        $this->table_survey_responses = $wpdb->prefix . 'cp_canvass_survey_responses';

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
        add_action('wp_ajax_cp_create_walk_list', array($this, 'ajax_create_walk_list'));
        add_action('wp_ajax_cp_cut_turf', array($this, 'ajax_cut_turf'));
        add_action('wp_ajax_cp_save_canvass_interaction', array($this, 'ajax_save_interaction'));
        add_action('wp_ajax_cp_get_walk_list', array($this, 'ajax_get_walk_list'));
        add_action('wp_ajax_cp_export_canvass_data', array($this, 'ajax_export_data'));

        // Frontend AJAX (for canvassers)
        add_action('wp_ajax_nopriv_cp_save_canvass_interaction', array($this, 'ajax_save_interaction'));
        add_action('wp_ajax_nopriv_cp_get_walk_list', array($this, 'ajax_get_walk_list'));

        // REST API
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        // Shortcodes
        add_shortcode('cp_canvassing_interface', array($this, 'render_canvassing_interface'));
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Walk lists table
        $sql_walk_lists = "CREATE TABLE IF NOT EXISTS {$this->table_walk_lists} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text DEFAULT NULL,
            turf_id bigint(20) UNSIGNED DEFAULT NULL,
            total_addresses int(11) DEFAULT 0,
            completed_addresses int(11) DEFAULT 0,
            status varchar(20) DEFAULT 'active',
            created_by bigint(20) UNSIGNED DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY turf_id (turf_id),
            KEY status (status),
            KEY created_by (created_by)
        ) $charset_collate;";

        // Turfs table (territory cutting)
        $sql_turfs = "CREATE TABLE IF NOT EXISTS {$this->table_turfs} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text DEFAULT NULL,
            assigned_to bigint(20) UNSIGNED DEFAULT NULL,
            boundary_data longtext DEFAULT NULL,
            zip_codes text DEFAULT NULL,
            precincts text DEFAULT NULL,
            total_addresses int(11) DEFAULT 0,
            priority int(11) DEFAULT 5,
            status varchar(20) DEFAULT 'unassigned',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY assigned_to (assigned_to),
            KEY status (status),
            KEY priority (priority)
        ) $charset_collate;";

        // Canvass interactions table
        $sql_interactions = "CREATE TABLE IF NOT EXISTS {$this->table_interactions} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            walk_list_id bigint(20) UNSIGNED NOT NULL,
            turf_id bigint(20) UNSIGNED DEFAULT NULL,
            canvasser_id bigint(20) UNSIGNED NOT NULL,
            address varchar(255) NOT NULL,
            city varchar(100) DEFAULT NULL,
            state varchar(2) DEFAULT NULL,
            zip varchar(10) DEFAULT NULL,
            latitude decimal(10, 8) DEFAULT NULL,
            longitude decimal(11, 8) DEFAULT NULL,
            result varchar(50) NOT NULL,
            voter_name varchar(255) DEFAULT NULL,
            voter_email varchar(100) DEFAULT NULL,
            voter_phone varchar(20) DEFAULT NULL,
            notes text DEFAULT NULL,
            survey_responses longtext DEFAULT NULL,
            duration int(11) DEFAULT NULL,
            interaction_date datetime NOT NULL,
            synced tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY walk_list_id (walk_list_id),
            KEY turf_id (turf_id),
            KEY canvasser_id (canvasser_id),
            KEY result (result),
            KEY interaction_date (interaction_date),
            KEY synced (synced)
        ) $charset_collate;";

        // Surveys table
        $sql_surveys = "CREATE TABLE IF NOT EXISTS {$this->table_surveys} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text DEFAULT NULL,
            questions longtext NOT NULL,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status)
        ) $charset_collate;";

        // Survey responses table
        $sql_survey_responses = "CREATE TABLE IF NOT EXISTS {$this->table_survey_responses} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            survey_id bigint(20) UNSIGNED NOT NULL,
            interaction_id bigint(20) UNSIGNED NOT NULL,
            responses longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY survey_id (survey_id),
            KEY interaction_id (interaction_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_walk_lists);
        dbDelta($sql_turfs);
        dbDelta($sql_interactions);
        dbDelta($sql_surveys);
        dbDelta($sql_survey_responses);
    }

    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        add_submenu_page(
            'cp-field-operations',
            __('Canvassing', 'campaign-office'),
            __('Canvassing', 'campaign-office'),
            'edit_posts',
            'cp-canvassing',
            array($this, 'render_admin_page')
        );

        add_submenu_page(
            'cp-canvassing',
            __('Walk Lists', 'campaign-office'),
            __('Walk Lists', 'campaign-office'),
            'edit_posts',
            'cp-walk-lists',
            array($this, 'render_walk_lists_page')
        );

        add_submenu_page(
            'cp-canvassing',
            __('Territory Management', 'campaign-office'),
            __('Territories', 'campaign-office'),
            'edit_posts',
            'cp-turfs',
            array($this, 'render_turfs_page')
        );

        add_submenu_page(
            'cp-canvassing',
            __('Survey Builder', 'campaign-office'),
            __('Surveys', 'campaign-office'),
            'edit_posts',
            'cp-canvass-surveys',
            array($this, 'render_surveys_page')
        );
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Get walk list
        register_rest_route('campaignpress/v1', '/canvassing/walk-list/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_walk_list'),
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));

        // Save interaction
        register_rest_route('campaignpress/v1', '/canvassing/interaction', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_save_interaction'),
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));

        // Get stats
        register_rest_route('campaignpress/v1', '/canvassing/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_stats'),
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));
    }

    /**
     * Render main admin page
     */
    public function render_admin_page() {
        global $wpdb;

        // Get today's stats
        $today = current_time('Y-m-d');
        $stats = $this->get_stats_for_date($today);

        ?>
        <div class="wrap cp-canvassing-dashboard">
            <h1><?php esc_html_e('Canvassing Dashboard', 'campaign-office'); ?></h1>

            <!-- Stats Cards -->
            <div class="cp-stats-grid">
                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Doors Knocked Today', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['doors_knocked'])); ?></div>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Conversations', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['conversations'])); ?></div>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Active Canvassers', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['active_canvassers'])); ?></div>
                </div>

                <div class="cp-stat-card">
                    <h3><?php esc_html_e('Completion Rate', 'campaign-office'); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html(number_format($stats['completion_rate'], 1)); ?>%</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="cp-quick-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-walk-lists&action=new')); ?>" class="button button-primary">
                    <?php esc_html_e('Create Walk List', 'campaign-office'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-turfs&action=new')); ?>" class="button">
                    <?php esc_html_e('Cut New Turf', 'campaign-office'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-canvass-surveys&action=new')); ?>" class="button">
                    <?php esc_html_e('Create Survey', 'campaign-office'); ?>
                </a>
                <a href="#" class="button cp-export-data" data-type="canvassing">
                    <?php esc_html_e('Export Data', 'campaign-office'); ?>
                </a>
            </div>

            <!-- Recent Activity -->
            <div class="cp-recent-activity">
                <h2><?php esc_html_e('Recent Door Knocks', 'campaign-office'); ?></h2>
                <?php $this->render_recent_interactions(); ?>
            </div>

            <!-- Leaderboard -->
            <div class="cp-leaderboard">
                <h2><?php esc_html_e('Top Canvassers This Week', 'campaign-office'); ?></h2>
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
            .cp-recent-activity,
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
     * Render walk lists page
     */
    public function render_walk_lists_page() {
        global $wpdb;

        // Handle new walk list creation
        if (isset($_GET['action']) && $_GET['action'] === 'new') {
            $this->render_new_walk_list_form();
            return;
        }

        // Get all walk lists
        $walk_lists = $wpdb->get_results(
            "SELECT * FROM {$this->table_walk_lists} ORDER BY created_at DESC"
        );

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Walk Lists', 'campaign-office'); ?></h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=cp-walk-lists&action=new')); ?>" class="page-title-action">
                <?php esc_html_e('Create Walk List', 'campaign-office'); ?>
            </a>

            <hr class="wp-header-end">

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Name', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Turf', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Addresses', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Completed', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Progress', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Status', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Actions', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($walk_lists)) : ?>
                        <tr>
                            <td colspan="7"><?php esc_html_e('No walk lists found. Create your first walk list!', 'campaign-office'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($walk_lists as $list) : ?>
                            <?php
                            $progress = $list->total_addresses > 0 ? ($list->completed_addresses / $list->total_addresses) * 100 : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($list->name); ?></strong></td>
                                <td><?php echo esc_html($this->get_turf_name($list->turf_id)); ?></td>
                                <td><?php echo esc_html(number_format($list->total_addresses)); ?></td>
                                <td><?php echo esc_html(number_format($list->completed_addresses)); ?></td>
                                <td>
                                    <div class="cp-progress-bar">
                                        <div class="cp-progress-fill" style="width: <?php echo esc_attr($progress); ?>%"></div>
                                    </div>
                                    <span class="cp-progress-text"><?php echo esc_html(number_format($progress, 1)); ?>%</span>
                                </td>
                                <td><span class="cp-status-badge cp-status-<?php echo esc_attr($list->status); ?>"><?php echo esc_html(ucfirst($list->status)); ?></span></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-walk-lists&action=view&id=' . $list->id)); ?>" class="button button-small">
                                        <?php esc_html_e('View', 'campaign-office'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-walk-lists&action=export&id=' . $list->id)); ?>" class="button button-small">
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
     * Render new walk list form
     */
    private function render_new_walk_list_form() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Create Walk List', 'campaign-office'); ?></h1>

            <form id="cp-new-walk-list-form" class="cp-canvass-form">
                <?php wp_nonce_field('cp_create_walk_list', 'cp_walk_list_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="walk_list_name"><?php esc_html_e('Walk List Name', 'campaign-office'); ?></label></th>
                        <td><input type="text" id="walk_list_name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="walk_list_description"><?php esc_html_e('Description', 'campaign-office'); ?></label></th>
                        <td><textarea id="walk_list_description" name="description" rows="3" class="large-text"></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="turf_id"><?php esc_html_e('Assign to Turf', 'campaign-office'); ?></label></th>
                        <td>
                            <select id="turf_id" name="turf_id">
                                <option value=""><?php esc_html_e('Select Turf...', 'campaign-office'); ?></option>
                                <?php echo $this->get_turfs_dropdown(); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Target Criteria', 'campaign-office'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="criteria[]" value="likely_supporters">
                                    <?php esc_html_e('Likely Supporters', 'campaign-office'); ?>
                                </label><br>
                                <label>
                                    <input type="checkbox" name="criteria[]" value="persuadable">
                                    <?php esc_html_e('Persuadable Voters', 'campaign-office'); ?>
                                </label><br>
                                <label>
                                    <input type="checkbox" name="criteria[]" value="new_registrants">
                                    <?php esc_html_e('New Registrants', 'campaign-office'); ?>
                                </label><br>
                                <label>
                                    <input type="checkbox" name="criteria[]" value="high_turnout">
                                    <?php esc_html_e('High Turnout Voters', 'campaign-office'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="zip_codes"><?php esc_html_e('ZIP Codes', 'campaign-office'); ?></label></th>
                        <td>
                            <input type="text" id="zip_codes" name="zip_codes" class="regular-text" placeholder="<?php esc_attr_e('Enter ZIP codes separated by commas', 'campaign-office'); ?>">
                            <p class="description"><?php esc_html_e('Leave blank to use all ZIP codes in turf', 'campaign-office'); ?></p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Generate Walk List', 'campaign-office'); ?></button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-walk-lists')); ?>" class="button"><?php esc_html_e('Cancel', 'campaign-office'); ?></a>
                </p>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#cp-new-walk-list-form').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);
                formData.append('action', 'cp_create_walk_list');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            window.location.href = '<?php echo esc_js(admin_url('admin.php?page=cp-walk-lists')); ?>';
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
     * Render territory management page
     */
    public function render_turfs_page() {
        global $wpdb;

        $turfs = $wpdb->get_results("SELECT * FROM {$this->table_turfs} ORDER BY priority DESC, name ASC");

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Territory Management', 'campaign-office'); ?></h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=cp-turfs&action=new')); ?>" class="page-title-action">
                <?php esc_html_e('Create Territory', 'campaign-office'); ?>
            </a>

            <hr class="wp-header-end">

            <p><?php esc_html_e('Divide your campaign area into manageable territories (turfs) for canvassing teams.', 'campaign-office'); ?></p>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Territory Name', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Assigned To', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Addresses', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Priority', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Status', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Actions', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($turfs)) : ?>
                        <tr>
                            <td colspan="6"><?php esc_html_e('No territories defined yet. Create your first territory!', 'campaign-office'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($turfs as $turf) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($turf->name); ?></strong></td>
                                <td><?php echo esc_html($this->get_user_display_name($turf->assigned_to)); ?></td>
                                <td><?php echo esc_html(number_format($turf->total_addresses)); ?></td>
                                <td>
                                    <?php
                                    $priority_labels = array(
                                        1 => __('Very Low', 'campaign-office'),
                                        3 => __('Low', 'campaign-office'),
                                        5 => __('Medium', 'campaign-office'),
                                        7 => __('High', 'campaign-office'),
                                        10 => __('Critical', 'campaign-office'),
                                    );
                                    echo esc_html($priority_labels[$turf->priority] ?? __('Medium', 'campaign-office'));
                                    ?>
                                </td>
                                <td><span class="cp-status-badge cp-status-<?php echo esc_attr($turf->status); ?>"><?php echo esc_html(ucfirst($turf->status)); ?></span></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-turfs&action=edit&id=' . $turf->id)); ?>" class="button button-small">
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
     * Render surveys page
     */
    public function render_surveys_page() {
        global $wpdb;

        $surveys = $wpdb->get_results("SELECT * FROM {$this->table_surveys} ORDER BY created_at DESC");

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Survey Builder', 'campaign-office'); ?></h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=cp-canvass-surveys&action=new')); ?>" class="page-title-action">
                <?php esc_html_e('Create Survey', 'campaign-office'); ?>
            </a>

            <hr class="wp-header-end">

            <p><?php esc_html_e('Create custom surveys to collect voter opinions and feedback during canvassing.', 'campaign-office'); ?></p>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Survey Name', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Questions', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Responses', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Status', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Actions', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($surveys)) : ?>
                        <tr>
                            <td colspan="5"><?php esc_html_e('No surveys created yet.', 'campaign-office'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($surveys as $survey) : ?>
                            <?php
                            $questions = json_decode($survey->questions, true);
                            $question_count = is_array($questions) ? count($questions) : 0;
                            $response_count = $wpdb->get_var($wpdb->prepare(
                                "SELECT COUNT(*) FROM {$this->table_survey_responses} WHERE survey_id = %d",
                                $survey->id
                            ));
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($survey->name); ?></strong></td>
                                <td><?php echo esc_html($question_count); ?></td>
                                <td><?php echo esc_html(number_format($response_count)); ?></td>
                                <td><span class="cp-status-badge cp-status-<?php echo esc_attr($survey->status); ?>"><?php echo esc_html(ucfirst($survey->status)); ?></span></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-canvass-surveys&action=edit&id=' . $survey->id)); ?>" class="button button-small">
                                        <?php esc_html_e('Edit', 'campaign-office'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-canvass-surveys&action=responses&id=' . $survey->id)); ?>" class="button button-small">
                                        <?php esc_html_e('View Responses', 'campaign-office'); ?>
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
     * Render canvassing interface (frontend shortcode)
     */
    public function render_canvassing_interface($atts) {
        $atts = shortcode_atts(array(
            'walk_list_id' => '',
        ), $atts);

        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('You must be logged in to access the canvassing interface.', 'campaign-office') . '</p>';
        }

        ob_start();
        ?>
        <div class="cp-canvassing-interface" data-walk-list-id="<?php echo esc_attr($atts['walk_list_id']); ?>">
            <!-- Connection Status -->
            <div class="cp-connection-status">
                <span class="cp-status-indicator cp-online"></span>
                <span class="cp-status-text"><?php esc_html_e('Connected', 'campaign-office'); ?></span>
            </div>

            <!-- Current Address Card -->
            <div class="cp-address-card">
                <div class="cp-address-header">
                    <h2 class="cp-address-text" id="cp-current-address"><?php esc_html_e('Loading...', 'campaign-office'); ?></h2>
                    <button class="cp-gps-btn" id="cp-track-location" title="<?php esc_attr_e('Track my location', 'campaign-office'); ?>">
                        <span class="dashicons dashicons-location"></span>
                    </button>
                </div>
                <div class="cp-address-details" id="cp-address-details"></div>
            </div>

            <!-- Quick Results -->
            <div class="cp-quick-results">
                <h3><?php esc_html_e('Door Knock Result', 'campaign-office'); ?></h3>
                <div class="cp-result-buttons">
                    <button class="cp-result-btn cp-result-answered" data-result="answered">
                        <span class="dashicons dashicons-yes"></span>
                        <?php esc_html_e('Answered', 'campaign-office'); ?>
                    </button>
                    <button class="cp-result-btn cp-result-not-home" data-result="not_home">
                        <span class="dashicons dashicons-minus"></span>
                        <?php esc_html_e('Not Home', 'campaign-office'); ?>
                    </button>
                    <button class="cp-result-btn cp-result-refused" data-result="refused">
                        <span class="dashicons dashicons-no"></span>
                        <?php esc_html_e('Refused', 'campaign-office'); ?>
                    </button>
                    <button class="cp-result-btn cp-result-moved" data-result="moved">
                        <span class="dashicons dashicons-migrate"></span>
                        <?php esc_html_e('Moved', 'campaign-office'); ?>
                    </button>
                    <button class="cp-result-btn cp-result-invalid" data-result="invalid_address">
                        <span class="dashicons dashicons-warning"></span>
                        <?php esc_html_e('Invalid', 'campaign-office'); ?>
                    </button>
                </div>
            </div>

            <!-- Conversation Form (shown when "Answered" is clicked) -->
            <div class="cp-conversation-form" id="cp-conversation-form" style="display: none;">
                <h3><?php esc_html_e('Conversation Details', 'campaign-office'); ?></h3>

                <div class="cp-form-group">
                    <label for="cp-voter-name"><?php esc_html_e('Voter Name', 'campaign-office'); ?></label>
                    <input type="text" id="cp-voter-name" class="cp-form-control">
                </div>

                <div class="cp-form-group">
                    <label for="cp-voter-email"><?php esc_html_e('Email (optional)', 'campaign-office'); ?></label>
                    <input type="email" id="cp-voter-email" class="cp-form-control">
                </div>

                <div class="cp-form-group">
                    <label for="cp-voter-phone"><?php esc_html_e('Phone (optional)', 'campaign-office'); ?></label>
                    <input type="tel" id="cp-voter-phone" class="cp-form-control">
                </div>

                <!-- Survey Questions (dynamically loaded) -->
                <div id="cp-survey-questions"></div>

                <div class="cp-form-group">
                    <label for="cp-notes"><?php esc_html_e('Notes', 'campaign-office'); ?></label>
                    <textarea id="cp-notes" class="cp-form-control" rows="4"></textarea>
                </div>

                <button class="cp-save-btn" id="cp-save-interaction">
                    <?php esc_html_e('Save & Next Door', 'campaign-office'); ?>
                </button>
            </div>

            <!-- Progress Bar -->
            <div class="cp-progress-container">
                <div class="cp-progress-bar">
                    <div class="cp-progress-fill" id="cp-progress-fill" style="width: 0%"></div>
                </div>
                <p class="cp-progress-text">
                    <span id="cp-completed-count">0</span> / <span id="cp-total-count">0</span>
                    <?php esc_html_e('addresses completed', 'campaign-office'); ?>
                </p>
            </div>

            <!-- Navigation -->
            <div class="cp-navigation">
                <button class="cp-nav-btn" id="cp-prev-address">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                    <?php esc_html_e('Previous', 'campaign-office'); ?>
                </button>
                <button class="cp-nav-btn" id="cp-skip-address">
                    <?php esc_html_e('Skip', 'campaign-office'); ?>
                </button>
                <button class="cp-nav-btn cp-primary" id="cp-next-address">
                    <?php esc_html_e('Next', 'campaign-office'); ?>
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>

            <!-- Offline Queue Indicator -->
            <div class="cp-offline-queue" id="cp-offline-queue" style="display: none;">
                <span class="dashicons dashicons-cloud"></span>
                <span id="cp-queue-count">0</span> <?php esc_html_e('items waiting to sync', 'campaign-office'); ?>
            </div>
        </div>

        <style>
            .cp-canvassing-interface {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
            .cp-address-card {
                background: #fff;
                border: 2px solid #2271b1;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .cp-address-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .cp-address-text {
                margin: 0;
                font-size: 24px;
                color: #2271b1;
            }
            .cp-gps-btn {
                background: #2271b1;
                color: #fff;
                border: none;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .cp-quick-results {
                margin-bottom: 20px;
            }
            .cp-result-buttons {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
                gap: 10px;
                margin-top: 10px;
            }
            .cp-result-btn {
                padding: 15px 10px;
                border: 2px solid #ddd;
                background: #fff;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 600;
                text-align: center;
                transition: all 0.2s;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 5px;
            }
            .cp-result-btn:hover {
                border-color: #2271b1;
                background: #f0f6fc;
            }
            .cp-result-btn.active {
                border-color: #2271b1;
                background: #2271b1;
                color: #fff;
            }
            .cp-result-answered { border-color: #46b450; }
            .cp-result-answered:hover,
            .cp-result-answered.active { background: #46b450; border-color: #46b450; color: #fff; }
            .cp-result-not-home { border-color: #ffb900; }
            .cp-result-not-home:hover,
            .cp-result-not-home.active { background: #ffb900; border-color: #ffb900; color: #fff; }
            .cp-result-refused { border-color: #dc3232; }
            .cp-result-refused:hover,
            .cp-result-refused.active { background: #dc3232; border-color: #dc3232; color: #fff; }
            .cp-conversation-form {
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
            .cp-save-btn {
                width: 100%;
                padding: 15px;
                background: #2271b1;
                color: #fff;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
            }
            .cp-save-btn:hover {
                background: #135e96;
            }
            .cp-progress-container {
                margin-bottom: 20px;
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
            .cp-navigation {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                gap: 10px;
            }
            .cp-nav-btn {
                padding: 15px;
                border: 2px solid #2271b1;
                background: #fff;
                color: #2271b1;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 5px;
            }
            .cp-nav-btn.cp-primary {
                background: #2271b1;
                color: #fff;
            }
            .cp-offline-queue {
                margin-top: 20px;
                padding: 10px;
                background: #fff3cd;
                border: 1px solid #ffc107;
                border-radius: 4px;
                text-align: center;
            }
            @media (max-width: 600px) {
                .cp-result-buttons {
                    grid-template-columns: repeat(2, 1fr);
                }
                .cp-navigation {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <script>
        // Canvassing interface JavaScript will be loaded from external file
        // This is a placeholder showing the structure
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX: Create walk list
     */
    public function ajax_create_walk_list() {
        check_ajax_referer('cp_create_walk_list', 'cp_walk_list_nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaign-office')));
        }

        global $wpdb;

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $turf_id = isset($_POST['turf_id']) ? absint($_POST['turf_id']) : null;

        if (empty($name)) {
            wp_send_json_error(array('message' => __('Walk list name is required.', 'campaign-office')));
        }

        // Insert walk list
        $result = $wpdb->insert(
            $this->table_walk_lists,
            array(
                'name' => $name,
                'description' => $description,
                'turf_id' => $turf_id,
                'created_by' => get_current_user_id(),
                'status' => 'active',
            ),
            array('%s', '%s', '%d', '%d', '%s')
        );

        if ($result) {
            $walk_list_id = $wpdb->insert_id;

            // Generate addresses based on criteria
            // This would integrate with voter database or external API
            // For now, we'll just set a placeholder count
            $wpdb->update(
                $this->table_walk_lists,
                array('total_addresses' => 100), // Placeholder
                array('id' => $walk_list_id),
                array('%d'),
                array('%d')
            );

            wp_send_json_success(array(
                'message' => __('Walk list created successfully!', 'campaign-office'),
                'walk_list_id' => $walk_list_id,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to create walk list.', 'campaign-office')));
        }
    }

    /**
     * AJAX: Save canvass interaction
     */
    public function ajax_save_interaction() {
        check_ajax_referer('cp_field_ops_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campaign-office')));
        }

        $interaction_data = $this->sanitize_interaction_data($_POST);
        $result = $this->save_interaction($interaction_data);

        if ($result) {
            wp_send_json_success(array(
                'message' => __('Interaction saved successfully!', 'campaign-office'),
                'interaction_id' => $result,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to save interaction.', 'campaign-office')));
        }
    }

    /**
     * Save canvass interaction
     *
     * @param array $data Interaction data
     * @return int|false Insert ID or false on failure
     */
    public function save_interaction($data) {
        global $wpdb;

        $interaction_data = wp_parse_args($data, array(
            'walk_list_id' => 0,
            'turf_id' => null,
            'canvasser_id' => get_current_user_id(),
            'address' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'latitude' => null,
            'longitude' => null,
            'result' => '',
            'voter_name' => '',
            'voter_email' => '',
            'voter_phone' => '',
            'notes' => '',
            'survey_responses' => '',
            'duration' => null,
            'interaction_date' => current_time('mysql'),
            'synced' => 1,
        ));

        $result = $wpdb->insert(
            $this->table_interactions,
            $interaction_data,
            array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d')
        );

        if ($result) {
            // Update walk list completion count
            if ($interaction_data['walk_list_id']) {
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$this->table_walk_lists}
                    SET completed_addresses = completed_addresses + 1
                    WHERE id = %d",
                    $interaction_data['walk_list_id']
                ));
            }

            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Sanitize interaction data
     *
     * @param array $data Raw data
     * @return array Sanitized data
     */
    private function sanitize_interaction_data($data) {
        return array(
            'walk_list_id' => isset($data['walk_list_id']) ? absint($data['walk_list_id']) : 0,
            'turf_id' => isset($data['turf_id']) ? absint($data['turf_id']) : null,
            'canvasser_id' => isset($data['canvasser_id']) ? absint($data['canvasser_id']) : get_current_user_id(),
            'address' => isset($data['address']) ? sanitize_text_field($data['address']) : '',
            'city' => isset($data['city']) ? sanitize_text_field($data['city']) : '',
            'state' => isset($data['state']) ? sanitize_text_field($data['state']) : '',
            'zip' => isset($data['zip']) ? sanitize_text_field($data['zip']) : '',
            'latitude' => isset($data['latitude']) ? floatval($data['latitude']) : null,
            'longitude' => isset($data['longitude']) ? floatval($data['longitude']) : null,
            'result' => isset($data['result']) ? sanitize_text_field($data['result']) : '',
            'voter_name' => isset($data['voter_name']) ? sanitize_text_field($data['voter_name']) : '',
            'voter_email' => isset($data['voter_email']) ? sanitize_email($data['voter_email']) : '',
            'voter_phone' => isset($data['voter_phone']) ? sanitize_text_field($data['voter_phone']) : '',
            'notes' => isset($data['notes']) ? sanitize_textarea_field($data['notes']) : '',
            'survey_responses' => isset($data['survey_responses']) ? wp_json_encode($data['survey_responses']) : '',
            'duration' => isset($data['duration']) ? absint($data['duration']) : null,
            'interaction_date' => isset($data['interaction_date']) ? sanitize_text_field($data['interaction_date']) : current_time('mysql'),
            'synced' => isset($data['synced']) ? absint($data['synced']) : 1,
        );
    }

    /**
     * Get stats for a specific date
     *
     * @param string $date Date in Y-m-d format
     * @return array Statistics
     */
    public function get_stats_for_date($date) {
        global $wpdb;

        $doors_knocked = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_interactions}
            WHERE DATE(interaction_date) = %s",
            $date
        ));

        $conversations = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_interactions}
            WHERE DATE(interaction_date) = %s AND result = 'answered'",
            $date
        ));

        $active_canvassers = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT canvasser_id) FROM {$this->table_interactions}
            WHERE DATE(interaction_date) = %s",
            $date
        ));

        $completion_rate = $doors_knocked > 0 ? ($conversations / $doors_knocked) * 100 : 0;

        return array(
            'doors_knocked' => $doors_knocked,
            'conversations' => $conversations,
            'active_canvassers' => $active_canvassers,
            'completion_rate' => $completion_rate,
        );
    }

    /**
     * Get overall stats
     *
     * @return array Statistics
     */
    public function get_stats() {
        return $this->get_stats_for_date(current_time('Y-m-d'));
    }

    /**
     * Get daily stats
     *
     * @return array Statistics
     */
    public function get_daily_stats() {
        return $this->get_stats();
    }

    /**
     * Get total doors knocked today
     *
     * @return int Count
     */
    public function get_total_doors_knocked() {
        $stats = $this->get_stats();
        return $stats['doors_knocked'];
    }

    /**
     * Render recent interactions
     */
    private function render_recent_interactions() {
        global $wpdb;

        $interactions = $wpdb->get_results(
            "SELECT i.*, u.display_name as canvasser_name
            FROM {$this->table_interactions} i
            LEFT JOIN {$wpdb->users} u ON i.canvasser_id = u.ID
            ORDER BY i.interaction_date DESC
            LIMIT 10"
        );

        if (empty($interactions)) {
            echo '<p>' . esc_html__('No recent activity.', 'campaign-office') . '</p>';
            return;
        }

        echo '<table class="wp-list-table widefat fixed striped"><thead><tr>';
        echo '<th>' . esc_html__('Time', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Address', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Canvasser', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Result', 'campaign-office') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($interactions as $interaction) {
            echo '<tr>';
            echo '<td>' . esc_html(human_time_diff(strtotime($interaction->interaction_date), current_time('timestamp')) . ' ago') . '</td>';
            echo '<td>' . esc_html($interaction->address) . '</td>';
            echo '<td>' . esc_html($interaction->canvasser_name) . '</td>';
            echo '<td><span class="cp-result-badge cp-result-' . esc_attr($interaction->result) . '">' . esc_html(ucfirst(str_replace('_', ' ', $interaction->result))) . '</span></td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    /**
     * Render leaderboard
     */
    private function render_leaderboard() {
        global $wpdb;

        $week_start = date('Y-m-d', strtotime('monday this week'));

        $leaderboard = $wpdb->get_results($wpdb->prepare(
            "SELECT u.display_name, COUNT(*) as doors_knocked,
            SUM(CASE WHEN result = 'answered' THEN 1 ELSE 0 END) as conversations
            FROM {$this->table_interactions} i
            LEFT JOIN {$wpdb->users} u ON i.canvasser_id = u.ID
            WHERE DATE(interaction_date) >= %s
            GROUP BY canvasser_id
            ORDER BY doors_knocked DESC
            LIMIT 10",
            $week_start
        ));

        if (empty($leaderboard)) {
            echo '<p>' . esc_html__('No canvassing activity this week yet.', 'campaign-office') . '</p>';
            return;
        }

        echo '<table class="wp-list-table widefat fixed striped"><thead><tr>';
        echo '<th>' . esc_html__('Rank', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Canvasser', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Doors Knocked', 'campaign-office') . '</th>';
        echo '<th>' . esc_html__('Conversations', 'campaign-office') . '</th>';
        echo '</tr></thead><tbody>';

        $rank = 1;
        foreach ($leaderboard as $entry) {
            echo '<tr>';
            echo '<td><strong>' . esc_html($rank) . '</strong></td>';
            echo '<td>' . esc_html($entry->display_name) . '</td>';
            echo '<td>' . esc_html(number_format($entry->doors_knocked)) . '</td>';
            echo '<td>' . esc_html(number_format($entry->conversations)) . '</td>';
            echo '</tr>';
            $rank++;
        }

        echo '</tbody></table>';
    }

    /**
     * Get turf name by ID
     *
     * @param int $turf_id Turf ID
     * @return string Turf name
     */
    private function get_turf_name($turf_id) {
        if (!$turf_id) {
            return '—';
        }

        global $wpdb;
        $name = $wpdb->get_var($wpdb->prepare(
            "SELECT name FROM {$this->table_turfs} WHERE id = %d",
            $turf_id
        ));

        return $name ? $name : '—';
    }

    /**
     * Get user display name by ID
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
     * Get turfs dropdown options
     *
     * @return string HTML options
     */
    private function get_turfs_dropdown() {
        global $wpdb;
        $turfs = $wpdb->get_results("SELECT id, name FROM {$this->table_turfs} ORDER BY name ASC");

        $options = '';
        foreach ($turfs as $turf) {
            $options .= sprintf(
                '<option value="%d">%s</option>',
                esc_attr($turf->id),
                esc_html($turf->name)
            );
        }

        return $options;
    }

    /**
     * REST API: Get walk list
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function rest_get_walk_list($request) {
        $walk_list_id = $request->get_param('id');
        // Implementation would return walk list data
        return new WP_REST_Response(array('id' => $walk_list_id), 200);
    }

    /**
     * REST API: Save interaction
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function rest_save_interaction($request) {
        $data = $request->get_json_params();
        $result = $this->save_interaction($data);

        if ($result) {
            return new WP_REST_Response(array('id' => $result), 201);
        }

        return new WP_REST_Response(array('error' => 'Failed to save'), 500);
    }

    /**
     * REST API: Get stats
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function rest_get_stats($request) {
        return new WP_REST_Response($this->get_stats(), 200);
    }

    /**
     * AJAX: Cut turf
     */
    public function ajax_cut_turf() {
        // Implementation for territory cutting
        wp_send_json_success(array('message' => __('Turf created successfully!', 'campaign-office')));
    }

    /**
     * AJAX: Get walk list
     */
    public function ajax_get_walk_list() {
        // Implementation for retrieving walk list data
        wp_send_json_success(array());
    }

    /**
     * AJAX: Export data
     */
    public function ajax_export_data() {
        // Implementation for data export
        wp_send_json_success(array('message' => __('Export complete!', 'campaign-office')));
    }
}
