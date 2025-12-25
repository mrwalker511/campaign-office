<?php
/**
 * CampaignPress CRM System Initialization
 *
 * Main initialization file for the CampaignPress political CRM system.
 * Handles class loading, database initialization, hooks, and integration
 * with the main CampaignPress plugin.
 *
 * This CRM system is designed for political campaigns and provides:
 * - Voter database management (50K+ contacts)
 * - Interaction tracking (calls, texts, door knocks, emails)
 * - Smart segmentation and tagging
 * - Engagement scoring algorithm
 * - CSV import/export for voter files (L2, TargetSmart, NGP VAN)
 * - Duplicate detection and merging
 * - Household grouping
 * - Custom field management
 * - Advanced search and filtering
 * - Bulk operations
 *
 * @package CampaignPress
 * @subpackage CRM
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CRM System Initialization Class
 *
 * @since 1.0.0
 */
class CampaignPress_CRM_Init {

    /**
     * CRM version
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * Instance of this class
     *
     * @var CampaignPress_CRM_Init
     */
    private static $instance = null;

    /**
     * CRM Database instance
     *
     * @var CampaignPress_CRM_Database
     */
    public $database;

    /**
     * CRM Contacts instance
     *
     * @var CampaignPress_CRM_Contacts
     */
    public $contacts;

    /**
     * CRM Interactions instance
     *
     * @var CampaignPress_CRM_Interactions
     */
    public $interactions;

    /**
     * CRM Segments instance
     *
     * @var CampaignPress_CRM_Segments
     */
    public $segments;

    /**
     * CRM Import/Export instance
     *
     * @var CampaignPress_CRM_Import_Export
     */
    public $import_export;

    /**
     * Get singleton instance
     *
     * @since 1.0.0
     * @return CampaignPress_CRM_Init
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->define_constants();
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Define CRM constants
     *
     * @since 1.0.0
     */
    private function define_constants() {
        if ( ! defined( 'CP_CRM_VERSION' ) ) {
            define( 'CP_CRM_VERSION', self::VERSION );
        }

        if ( ! defined( 'CP_CRM_PATH' ) ) {
            define( 'CP_CRM_PATH', dirname( __FILE__ ) . '/' );
        }
    }

    /**
     * Load required dependencies
     *
     * @since 1.0.0
     */
    private function load_dependencies() {
        // Load class files
        require_once CP_CRM_PATH . 'class-crm-database.php';
        require_once CP_CRM_PATH . 'class-crm-contacts.php';
        require_once CP_CRM_PATH . 'class-crm-interactions.php';
        require_once CP_CRM_PATH . 'class-crm-segments.php';
        require_once CP_CRM_PATH . 'class-crm-import-export.php';

        // Initialize core classes
        $this->database      = new CampaignPress_CRM_Database();
        $this->contacts      = new CampaignPress_CRM_Contacts();
        $this->interactions  = new CampaignPress_CRM_Interactions();
        $this->segments      = new CampaignPress_CRM_Segments();
        $this->import_export = new CampaignPress_CRM_Import_Export();
    }

    /**
     * Initialize WordPress hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Activation hook
        add_action( 'after_switch_theme', array( $this, 'activate' ) );

        // Deactivation hook
        add_action( 'switch_theme', array( $this, 'deactivate' ) );

        // Initialize CRM on theme setup
        add_action( 'after_setup_theme', array( $this, 'init' ) );

        // Admin initialization
        add_action( 'admin_init', array( $this, 'admin_init' ) );

        // Admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

        // AJAX hooks
        add_action( 'wp_ajax_cp_crm_search_contacts', array( $this, 'ajax_search_contacts' ) );
        add_action( 'wp_ajax_cp_crm_import_csv', array( $this, 'ajax_import_csv' ) );
        add_action( 'wp_ajax_cp_crm_export_csv', array( $this, 'ajax_export_csv' ) );

        // REST API hooks
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

        // Scheduled tasks
        add_action( 'cp_crm_daily_maintenance', array( $this, 'daily_maintenance' ) );
        add_action( 'cp_crm_recalculate_engagement_scores', array( $this, 'recalculate_engagement_scores' ) );

        // Integration hooks
        do_action( 'cp_crm_loaded', $this );
    }

    /**
     * Initialize CRM system
     *
     * @since 1.0.0
     */
    public function init() {
        // Check if database tables exist
        if ( ! $this->database->tables_exist() ) {
            // Create tables if they don't exist
            $this->database->create_tables();

            // Create default tags
            $this->create_default_tags();
        }

        // Load text domain for translations
        load_plugin_textdomain( 'campaign-office', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

        // Allow customization after initialization
        do_action( 'cp_crm_init', $this );
    }

    /**
     * Admin initialization
     *
     * @since 1.0.0
     */
    public function admin_init() {
        // Check for database updates
        $db_version = get_option( 'cp_crm_db_version', '0.0.0' );
        if ( version_compare( $db_version, CampaignPress_CRM_Database::DB_VERSION, '<' ) ) {
            $this->database->create_tables();
        }

        // Register settings
        $this->register_settings();
    }

    /**
     * Add CRM admin menu
     *
     * @since 1.0.0
     */
    public function add_admin_menu() {
        // Main CRM menu
        add_menu_page(
            __( 'CRM', 'campaign-office' ),
            __( 'CRM', 'campaign-office' ),
            'edit_posts',
            'cp-crm',
            array( $this, 'render_dashboard_page' ),
            'dashicons-groups',
            30
        );

        // Dashboard submenu
        add_submenu_page(
            'cp-crm',
            __( 'CRM Dashboard', 'campaign-office' ),
            __( 'Dashboard', 'campaign-office' ),
            'edit_posts',
            'cp-crm',
            array( $this, 'render_dashboard_page' )
        );

        // Contacts submenu
        add_submenu_page(
            'cp-crm',
            __( 'Contacts', 'campaign-office' ),
            __( 'Contacts', 'campaign-office' ),
            'edit_posts',
            'cp-crm-contacts',
            array( $this, 'render_contacts_page' )
        );

        // Segments submenu
        add_submenu_page(
            'cp-crm',
            __( 'Segments', 'campaign-office' ),
            __( 'Segments', 'campaign-office' ),
            'edit_posts',
            'cp-crm-segments',
            array( $this, 'render_segments_page' )
        );

        // Import/Export submenu
        add_submenu_page(
            'cp-crm',
            __( 'Import/Export', 'campaign-office' ),
            __( 'Import/Export', 'campaign-office' ),
            'manage_options',
            'cp-crm-import-export',
            array( $this, 'render_import_export_page' )
        );

        // Interactions submenu
        add_submenu_page(
            'cp-crm',
            __( 'Interactions', 'campaign-office' ),
            __( 'Interactions', 'campaign-office' ),
            'edit_posts',
            'cp-crm-interactions',
            array( $this, 'render_interactions_page' )
        );
    }

    /**
     * Render dashboard page
     *
     * @since 1.0.0
     */
    public function render_dashboard_page() {
        $stats = array(
            'total_contacts' => $this->contacts->get_contact_count(),
            'total_interactions' => $this->interactions->get_interaction_count(),
            'total_segments' => $this->segments->get_segment_count(),
            'recent_contacts' => $this->contacts->get_contacts( array( 'per_page' => 5, 'orderby' => 'created_at', 'order' => 'DESC' ) ),
        );
        ?>
        <div class="wrap cp-crm-dashboard">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <div class="cp-crm-stats-grid">
                <div class="cp-crm-stat-card">
                    <h3><?php esc_html_e( 'Total Contacts', 'campaign-office' ); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html( number_format( $stats['total_contacts'] ) ); ?></div>
                </div>
                <div class="cp-crm-stat-card">
                    <h3><?php esc_html_e( 'Total Interactions', 'campaign-office' ); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html( number_format( $stats['total_interactions'] ) ); ?></div>
                </div>
                <div class="cp-crm-stat-card">
                    <h3><?php esc_html_e( 'Active Segments', 'campaign-office' ); ?></h3>
                    <div class="cp-stat-number"><?php echo esc_html( number_format( $stats['total_segments'] ) ); ?></div>
                </div>
            </div>

            <div class="cp-crm-section">
                <h2><?php esc_html_e( 'Recent Contacts', 'campaign-office' ); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Name', 'campaign-office' ); ?></th>
                            <th><?php esc_html_e( 'Email', 'campaign-office' ); ?></th>
                            <th><?php esc_html_e( 'Phone', 'campaign-office' ); ?></th>
                            <th><?php esc_html_e( 'Created', 'campaign-office' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $stats['recent_contacts']['contacts'] ) ) : ?>
                            <?php foreach ( $stats['recent_contacts']['contacts'] as $contact ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $contact->first_name . ' ' . $contact->last_name ); ?></td>
                                    <td><?php echo esc_html( $contact->email ); ?></td>
                                    <td><?php echo esc_html( $contact->phone ?? '-' ); ?></td>
                                    <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $contact->created_at ) ) ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4"><?php esc_html_e( 'No contacts found.', 'campaign-office' ); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Render contacts page
     *
     * @since 1.0.0
     */
    public function render_contacts_page() {
        ?>
        <div class="wrap cp-crm-contacts">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <div class="cp-crm-toolbar">
                <form method="get" action="">
                    <input type="hidden" name="page" value="cp-crm-contacts">
                    <input type="text" name="s" placeholder="<?php esc_attr_e( 'Search contacts...', 'campaign-office' ); ?>" class="regular-text">
                    <button type="submit" class="button"><?php esc_html_e( 'Search', 'campaign-office' ); ?></button>
                </form>
                <a href="#" class="button button-primary"><?php esc_html_e( 'Add New Contact', 'campaign-office' ); ?></a>
            </div>

            <p><?php esc_html_e( 'Use the search above to find and manage contacts in the CRM.', 'campaign-office' ); ?></p>
        </div>
        <?php
    }

    /**
     * Render segments page
     *
     * @since 1.0.0
     */
    public function render_segments_page() {
        ?>
        <div class="wrap cp-crm-segments">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <p><?php esc_html_e( 'Manage contact segments and smart lists for targeted communication.', 'campaign-office' ); ?></p>

            <a href="#" class="button button-primary"><?php esc_html_e( 'Create New Segment', 'campaign-office' ); ?></a>
        </div>
        <?php
    }

    /**
     * Render import/export page
     *
     * @since 1.0.0
     */
    public function render_import_export_page() {
        ?>
        <div class="wrap cp-crm-import-export">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <div class="cp-crm-import-section">
                <h2><?php esc_html_e( 'Import Contacts', 'campaign-office' ); ?></h2>
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field( 'cp_crm_import', 'cp_crm_import_nonce' ); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'CSV File', 'campaign-office' ); ?></th>
                            <td><input type="file" name="import_file" accept=".csv" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Format', 'campaign-office' ); ?></th>
                            <td>
                                <select name="format">
                                    <option value="generic"><?php esc_html_e( 'Generic CSV', 'campaign-office' ); ?></option>
                                    <option value="l2"><?php esc_html_e( 'L2 Political', 'campaign-office' ); ?></option>
                                    <option value="targetsmart"><?php esc_html_e( 'TargetSmart', 'campaign-office' ); ?></option>
                                    <option value="ngpvan"><?php esc_html_e( 'NGP VAN', 'campaign-office' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button( __( 'Import Contacts', 'campaign-office' ) ); ?>
                </form>
            </div>

            <div class="cp-crm-export-section">
                <h2><?php esc_html_e( 'Export Contacts', 'campaign-office' ); ?></h2>
                <form method="post" action="">
                    <?php wp_nonce_field( 'cp_crm_export', 'cp_crm_export_nonce' ); ?>
                    <p><?php esc_html_e( 'Export contacts to CSV format for use in other systems.', 'campaign-office' ); ?></p>
                    <button type="submit" name="cp_crm_export" value="1" class="button button-secondary"><?php esc_html_e( 'Export All Contacts', 'campaign-office' ); ?></button>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Render interactions page
     *
     * @since 1.0.0
     */
    public function render_interactions_page() {
        ?>
        <div class="wrap cp-crm-interactions">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <p><?php esc_html_e( 'View and manage all contact interactions including calls, emails, door knocks, and more.', 'campaign-office' ); ?></p>
        </div>
        <?php
    }

    /**
     * Plugin activation
     *
     * @since 1.0.0
     */
    public function activate() {
        // Create database tables
        $this->database->create_tables();

        // Create default tags
        $this->create_default_tags();

        // Schedule cron jobs
        if ( ! wp_next_scheduled( 'cp_crm_daily_maintenance' ) ) {
            wp_schedule_event( time(), 'daily', 'cp_crm_daily_maintenance' );
        }

        if ( ! wp_next_scheduled( 'cp_crm_recalculate_engagement_scores' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'cp_crm_recalculate_engagement_scores' );
        }

        // Set activation flag
        update_option( 'cp_crm_activated', time() );

        // Log activation
        error_log( 'CampaignPress CRM activated at ' . date( 'Y-m-d H:i:s' ) );
    }

    /**
     * Plugin deactivation
     *
     * @since 1.0.0
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook( 'cp_crm_daily_maintenance' );
        wp_clear_scheduled_hook( 'cp_crm_recalculate_engagement_scores' );

        // Log deactivation
        error_log( 'CampaignPress CRM deactivated at ' . date( 'Y-m-d H:i:s' ) );
    }

    /**
     * Create default tags
     *
     * @since 1.0.0
     */
    private function create_default_tags() {
        $default_tags = array(
            array(
                'name'      => 'Strong Support',
                'slug'      => 'strong-support',
                'tag_type'  => 'support_level',
                'color'     => '#27ae60',
                'is_system' => 1,
            ),
            array(
                'name'      => 'Support',
                'slug'      => 'support',
                'tag_type'  => 'support_level',
                'color'     => '#2ecc71',
                'is_system' => 1,
            ),
            array(
                'name'      => 'Undecided',
                'slug'      => 'undecided',
                'tag_type'  => 'support_level',
                'color'     => '#f39c12',
                'is_system' => 1,
            ),
            array(
                'name'      => 'Volunteer',
                'slug'      => 'volunteer',
                'tag_type'  => 'role',
                'color'     => '#3498db',
                'is_system' => 1,
            ),
            array(
                'name'      => 'Donor',
                'slug'      => 'donor',
                'tag_type'  => 'role',
                'color'     => '#9b59b6',
                'is_system' => 1,
            ),
            array(
                'name'      => 'VIP',
                'slug'      => 'vip',
                'tag_type'  => 'role',
                'color'     => '#e74c3c',
                'is_system' => 1,
            ),
            array(
                'name'      => 'Contacted',
                'slug'      => 'contacted',
                'tag_type'  => 'status',
                'color'     => '#1abc9c',
                'is_system' => 1,
            ),
            array(
                'name'      => 'Needs Follow-up',
                'slug'      => 'needs-follow-up',
                'tag_type'  => 'status',
                'color'     => '#e67e22',
                'is_system' => 1,
            ),
        );

        foreach ( $default_tags as $tag_data ) {
            // Check if tag already exists
            $existing = $this->segments->get_tags( array( 'tag_type' => $tag_data['tag_type'] ) );
            $exists = false;
            foreach ( $existing as $existing_tag ) {
                if ( $existing_tag->slug === $tag_data['slug'] ) {
                    $exists = true;
                    break;
                }
            }

            // Create tag if doesn't exist
            if ( ! $exists ) {
                $this->segments->create_tag( $tag_data );
            }
        }
    }

    /**
     * Register plugin settings
     *
     * @since 1.0.0
     */
    private function register_settings() {
        // CRM settings
        register_setting( 'cp_crm_settings', 'cp_crm_enable_duplicate_detection' );
        register_setting( 'cp_crm_settings', 'cp_crm_enable_household_grouping' );
        register_setting( 'cp_crm_settings', 'cp_crm_engagement_score_algorithm' );
        register_setting( 'cp_crm_settings', 'cp_crm_import_batch_size' );
        register_setting( 'cp_crm_settings', 'cp_crm_enable_geocoding' );
        register_setting( 'cp_crm_settings', 'cp_crm_default_import_format' );
    }

    /**
     * Daily maintenance task
     *
     * Runs optimization, cleanup, and maintenance tasks
     *
     * @since 1.0.0
     */
    public function daily_maintenance() {
        // Optimize database tables
        $this->database->optimize_tables();

        // Clean up old export files (older than 7 days)
        $this->cleanup_export_files();

        // Recalculate dynamic segments
        $this->recalculate_dynamic_segments();

        // Log maintenance
        error_log( 'CampaignPress CRM daily maintenance completed at ' . date( 'Y-m-d H:i:s' ) );
    }

    /**
     * Recalculate engagement scores
     *
     * Recalculates engagement scores for all contacts
     *
     * @since 1.0.0
     */
    public function recalculate_engagement_scores() {
        $batch_size = 100;
        $page = 1;

        do {
            $result = $this->contacts->get_contacts( array(
                'page'     => $page,
                'per_page' => $batch_size,
            ) );

            foreach ( $result['contacts'] as $contact ) {
                $this->contacts->update_engagement_score( $contact->id );
            }

            $page++;
        } while ( $page <= $result['pages'] );

        // Log completion
        error_log( 'CampaignPress CRM engagement scores recalculated at ' . date( 'Y-m-d H:i:s' ) );
    }

    /**
     * Recalculate dynamic segments
     *
     * @since 1.0.0
     */
    private function recalculate_dynamic_segments() {
        $segments = $this->segments->get_segments( array(
            'segment_type' => 'dynamic',
            'is_active'    => 1,
        ) );

        foreach ( $segments as $segment ) {
            $this->segments->recalculate_segment( $segment->id );
        }
    }

    /**
     * Cleanup old export files
     *
     * @since 1.0.0
     */
    private function cleanup_export_files() {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/crm-exports/';

        if ( ! file_exists( $export_dir ) ) {
            return;
        }

        $files = glob( $export_dir . '*.csv' );
        $cutoff = time() - ( 7 * DAY_IN_SECONDS );

        foreach ( $files as $file ) {
            if ( filemtime( $file ) < $cutoff ) {
                wp_delete_file( $file );
            }
        }
    }

    /**
     * AJAX: Search contacts
     *
     * @since 1.0.0
     */
    public function ajax_search_contacts() {
        // Check nonce
        check_ajax_referer( 'cp_crm_search', 'nonce' );

        // Check capabilities
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'campaign-office' ) ) );
        }

        $query = isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : '';
        $limit = isset( $_GET['limit'] ) ? absint( $_GET['limit'] ) : 20;

        if ( empty( $query ) ) {
            wp_send_json_error( array( 'message' => __( 'Search query required.', 'campaign-office' ) ) );
        }

        $contacts = $this->contacts->search_contacts( $query, $limit );

        wp_send_json_success( array( 'contacts' => $contacts ) );
    }

    /**
     * AJAX: Import CSV
     *
     * @since 1.0.0
     */
    public function ajax_import_csv() {
        // Check nonce
        check_ajax_referer( 'cp_crm_import', 'nonce' );

        // Check capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'campaign-office' ) ) );
        }

        // Handle file upload
        if ( empty( $_FILES['file'] ) ) {
            wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'campaign-office' ) ) );
        }

        $file = $_FILES['file'];

        // Validate file
        $validation = $this->import_export->validate_import_file( $file['tmp_name'] );
        if ( is_wp_error( $validation ) ) {
            wp_send_json_error( array( 'message' => $validation->get_error_message() ) );
        }

        // Get import settings
        $format = isset( $_POST['format'] ) ? sanitize_text_field( $_POST['format'] ) : 'generic';
        $update_existing = isset( $_POST['update_existing'] ) ? (bool) $_POST['update_existing'] : false;

        // Import file
        $result = $this->import_export->import_csv( $file['tmp_name'], array(
            'format'          => $format,
            'update_existing' => $update_existing,
        ) );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * AJAX: Export CSV
     *
     * @since 1.0.0
     */
    public function ajax_export_csv() {
        // Check nonce
        check_ajax_referer( 'cp_crm_export', 'nonce' );

        // Check capabilities
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'campaign-office' ) ) );
        }

        // Get export parameters
        $segment_id = isset( $_POST['segment_id'] ) ? absint( $_POST['segment_id'] ) : null;
        $tag_id = isset( $_POST['tag_id'] ) ? absint( $_POST['tag_id'] ) : null;

        // Export contacts
        $result = $this->import_export->export_csv( array(
            'segment_id' => $segment_id,
            'tag_id'     => $tag_id,
        ) );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        // Return download URL
        $upload_dir = wp_upload_dir();
        $file_url = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $result );

        wp_send_json_success( array(
            'file_path' => $result,
            'file_url'  => $file_url,
        ) );
    }

    /**
     * Register REST API routes
     *
     * @since 1.0.0
     */
    public function register_rest_routes() {
        $namespace = 'campaignpress/v1';

        // Contacts endpoints
        register_rest_route( $namespace, '/crm/contacts', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_contacts' ),
            'permission_callback' => array( $this, 'rest_permission_check' ),
        ) );

        register_rest_route( $namespace, '/crm/contacts/(?P<id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_contact' ),
            'permission_callback' => array( $this, 'rest_permission_check' ),
        ) );

        register_rest_route( $namespace, '/crm/contacts', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_create_contact' ),
            'permission_callback' => array( $this, 'rest_permission_check' ),
        ) );

        register_rest_route( $namespace, '/crm/contacts/(?P<id>\d+)', array(
            'methods'             => 'PUT',
            'callback'            => array( $this, 'rest_update_contact' ),
            'permission_callback' => array( $this, 'rest_permission_check' ),
        ) );

        // Interactions endpoints
        register_rest_route( $namespace, '/crm/interactions', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_log_interaction' ),
            'permission_callback' => array( $this, 'rest_permission_check' ),
        ) );

        // Segments endpoints
        register_rest_route( $namespace, '/crm/segments', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_segments' ),
            'permission_callback' => array( $this, 'rest_permission_check' ),
        ) );

        // Tags endpoints
        register_rest_route( $namespace, '/crm/tags', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_tags' ),
            'permission_callback' => array( $this, 'rest_permission_check' ),
        ) );

        // Statistics endpoint
        register_rest_route( $namespace, '/crm/statistics', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_statistics' ),
            'permission_callback' => array( $this, 'rest_permission_check' ),
        ) );
    }

    /**
     * REST API permission check
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object
     * @return bool True if user has permission
     */
    public function rest_permission_check( $request ) {
        return current_user_can( 'edit_posts' );
    }

    /**
     * REST: Get contacts
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function rest_get_contacts( $request ) {
        $args = array(
            'page'     => $request->get_param( 'page' ) ?: 1,
            'per_page' => $request->get_param( 'per_page' ) ?: 50,
            'search'   => $request->get_param( 'search' ) ?: '',
        );

        $result = $this->contacts->get_contacts( $args );

        return new WP_REST_Response( $result, 200 );
    }

    /**
     * REST: Get single contact
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function rest_get_contact( $request ) {
        $contact = $this->contacts->get_contact( $request['id'] );

        if ( ! $contact ) {
            return new WP_Error( 'not_found', __( 'Contact not found.', 'campaign-office' ), array( 'status' => 404 ) );
        }

        return new WP_REST_Response( $contact, 200 );
    }

    /**
     * REST: Create contact
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function rest_create_contact( $request ) {
        $result = $this->contacts->create_contact( $request->get_json_params() );

        if ( is_wp_error( $result ) ) {
            return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 400 );
        }

        return new WP_REST_Response( array( 'id' => $result ), 201 );
    }

    /**
     * REST: Update contact
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function rest_update_contact( $request ) {
        $result = $this->contacts->update_contact( $request['id'], $request->get_json_params() );

        if ( is_wp_error( $result ) ) {
            return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 400 );
        }

        return new WP_REST_Response( array( 'success' => true ), 200 );
    }

    /**
     * REST: Log interaction
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function rest_log_interaction( $request ) {
        $result = $this->interactions->log_interaction( $request->get_json_params() );

        if ( is_wp_error( $result ) ) {
            return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 400 );
        }

        return new WP_REST_Response( array( 'id' => $result ), 201 );
    }

    /**
     * REST: Get segments
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function rest_get_segments( $request ) {
        $segments = $this->segments->get_segments();
        return new WP_REST_Response( $segments, 200 );
    }

    /**
     * REST: Get tags
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function rest_get_tags( $request ) {
        $tags = $this->segments->get_tags();
        return new WP_REST_Response( $tags, 200 );
    }

    /**
     * REST: Get statistics
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function rest_get_statistics( $request ) {
        $stats = $this->database->get_statistics();
        return new WP_REST_Response( $stats, 200 );
    }

    /**
     * Get CRM version
     *
     * @since 1.0.0
     * @return string CRM version
     */
    public function get_version() {
        return self::VERSION;
    }
}

/**
 * Initialize the CRM system
 *
 * @since 1.0.0
 * @return CampaignPress_CRM_Init
 */
function cp_crm() {
    return CampaignPress_CRM_Init::get_instance();
}

// Initialize CRM
cp_crm();
