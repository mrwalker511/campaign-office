<?php
/**
 * Premium Templates Manager
 *
 * Manages the premium template library for the Design Studio.
 * Provides 50+ professionally designed campaign page templates.
 *
 * @package CampaignPress
 * @subpackage Premium/DesignStudio
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Premium_Templates
 *
 * Handles premium template management, browser, and application
 */
class CP_Premium_Templates {

    /**
     * Database table for premium templates
     *
     * @var string
     */
    private $templates_table;

    /**
     * Available template categories
     *
     * @var array
     */
    private $categories = array();

    /**
     * Templates directory path
     *
     * @var string
     */
    private $templates_dir;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->templates_table = $wpdb->prefix . 'cp_premium_templates';
        $this->templates_dir = CAMPAIGNPRESS_INCLUDES_DIR . '/premium/design-studio/templates';

        // Initialize categories
        $this->init_categories();

        // Database setup
        add_action('after_setup_theme', array($this, 'create_templates_table'));

        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'), 15);

        // Enqueue assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_browser_assets'));

        // AJAX handlers
        add_action('wp_ajax_cp_get_template_preview', array($this, 'ajax_get_template_preview'));
        add_action('wp_ajax_cp_apply_premium_template', array($this, 'ajax_apply_premium_template'));
        add_action('wp_ajax_cp_search_templates', array($this, 'ajax_search_templates'));
        add_action('wp_ajax_cp_get_templates_by_filter', array($this, 'ajax_get_templates_by_filter'));

        // Register templates on init
        add_action('init', array($this, 'register_premium_templates'), 20);
    }

    /**
     * Initialize template categories
     */
    private function init_categories() {
        $this->categories = array(
            'homepage' => array(
                'name' => __('Homepage Layouts', 'campaign-office'),
                'description' => __('Full-featured campaign home pages', 'campaign-office'),
                'icon' => '🏠',
                'count' => 15,
            ),
            'landing_pages' => array(
                'name' => __('Landing Pages', 'campaign-office'),
                'description' => __('Conversion-focused single-purpose pages', 'campaign-office'),
                'icon' => '🎯',
                'count' => 10,
            ),
            'about_bio' => array(
                'name' => __('About/Bio Pages', 'campaign-office'),
                'description' => __('Candidate biography and team pages', 'campaign-office'),
                'icon' => '👤',
                'count' => 8,
            ),
            'issues' => array(
                'name' => __('Issues Pages', 'campaign-office'),
                'description' => __('Policy platform and issue pages', 'campaign-office'),
                'icon' => '📋',
                'count' => 7,
            ),
            'events' => array(
                'name' => __('Events Pages', 'campaign-office'),
                'description' => __('Event calendars and registration', 'campaign-office'),
                'icon' => '📅',
                'count' => 5,
            ),
            'get_involved' => array(
                'name' => __('Get Involved', 'campaign-office'),
                'description' => __('Volunteer and action center pages', 'campaign-office'),
                'icon' => '✊',
                'count' => 5,
            ),
        );
    }

    /**
     * Create premium templates database table
     */
    public function create_templates_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->templates_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            template_key varchar(100) NOT NULL UNIQUE,
            template_name varchar(255) NOT NULL,
            template_description text,
            template_data longtext NOT NULL,
            preview_image varchar(500),
            category varchar(50) NOT NULL,
            campaign_type varchar(50),
            election_level varchar(50),
            tags text,
            is_premium tinyint(1) DEFAULT 1,
            featured tinyint(1) DEFAULT 0,
            downloads int DEFAULT 0,
            rating decimal(3,2) DEFAULT 0.00,
            difficulty varchar(20) DEFAULT 'beginner',
            setup_time varchar(50),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category (category),
            KEY campaign_type (campaign_type),
            KEY election_level (election_level),
            KEY is_premium (is_premium),
            KEY featured (featured)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('cp_premium_templates_table_created', true);
    }

    /**
     * Register premium templates from JSON files
     */
    public function register_premium_templates() {
        // Use transient to avoid checking database on every page load
        $templates_loaded = get_transient('cp_premium_templates_loaded');

        // Check if force reload is requested
        $force_reload = isset($_GET['force_reload_templates']) && current_user_can('manage_options');

        if ($templates_loaded && !$force_reload) {
            return;
        }

        // Check if templates already registered
        global $wpdb;
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->templates_table}");

        // Only register if table is empty or forced
        if ($count > 0 && !$force_reload) {
            // Set transient so we don't check again for 24 hours
            set_transient('cp_premium_templates_loaded', true, DAY_IN_SECONDS);
            return;
        }

        // Load templates from JSON files
        $this->load_templates_from_files();

        // Set transient so we don't reload on every request
        set_transient('cp_premium_templates_loaded', true, DAY_IN_SECONDS);
    }

    /**
     * Load templates from JSON files in templates directory
     */
    private function load_templates_from_files() {
        if (!is_dir($this->templates_dir)) {
            return;
        }

        global $wpdb;

        // Scan template directories
        foreach ($this->categories as $category_key => $category_data) {
            $category_dir = $this->templates_dir . '/' . $category_key;

            if (!is_dir($category_dir)) {
                continue;
            }

            // Get all JSON files in category
            $template_files = glob($category_dir . '/*.json');

            foreach ($template_files as $file) {
                $template_data = json_decode(file_get_contents($file), true);

                if (!$template_data || !isset($template_data['template_key'])) {
                    continue;
                }

                // Check if template already exists
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$this->templates_table} WHERE template_key = %s",
                    $template_data['template_key']
                ));

                $data = array(
                    'template_key' => $template_data['template_key'],
                    'template_name' => $template_data['template_name'],
                    'template_description' => $template_data['template_description'] ?? '',
                    'template_data' => wp_json_encode($template_data),
                    'preview_image' => $template_data['preview_image'] ?? '',
                    'category' => $template_data['category'],
                    'campaign_type' => $template_data['campaign_type'] ?? 'all',
                    'election_level' => $template_data['election_level'] ?? 'all',
                    'tags' => is_array($template_data['tags'] ?? null) ? implode(',', $template_data['tags']) : '',
                    'is_premium' => ($template_data['is_premium'] ?? true) ? 1 : 0,
                    'featured' => $template_data['featured'] ?? 0,
                    'difficulty' => $template_data['difficulty'] ?? 'beginner',
                    'setup_time' => $template_data['estimated_setup_time'] ?? '5-10 minutes',
                );

                if ($existing) {
                    // Update existing template
                    $wpdb->update($this->templates_table, $data, array('id' => $existing));
                } else {
                    // Insert new template
                    $wpdb->insert($this->templates_table, $data);
                }
            }
        }
    }

    /**
     * Add admin menu for template browser
     */
    public function add_admin_menu() {
        // Enhanced templates submenu under Design Studio
        add_submenu_page(
            'cp-design-studio',
            __('Template Library', 'campaign-office'),
            __('Templates', 'campaign-office'),
            'edit_pages',
            'cp-premium-templates',
            array($this, 'render_template_browser')
        );
    }

    /**
     * Render template browser page
     */
    public function render_template_browser() {
        $is_premium = function_exists('cp_is_premium_active') && cp_is_premium_active();
        $templates = $this->get_all_templates();

        include dirname(__FILE__) . '/views/template-browser.php';
    }

    /**
     * Get all templates
     *
     * @return array Array of template objects
     */
    public function get_all_templates() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->templates_table} ORDER BY featured DESC, category ASC, template_name ASC");
    }

    /**
     * Get templates by category
     *
     * @param string $category Category key
     * @return array Array of template objects
     */
    public function get_templates_by_category($category) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->templates_table} WHERE category = %s ORDER BY featured DESC, template_name ASC",
            $category
        ));
    }

    /**
     * Get single template by key
     *
     * @param string $template_key Template key
     * @return object|null Template object or null
     */
    public function get_template($template_key) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->templates_table} WHERE template_key = %s",
            $template_key
        ));
    }

    /**
     * Search templates
     *
     * @param string $query Search query
     * @return array Array of template objects
     */
    public function search_templates($query) {
        global $wpdb;
        $search = '%' . $wpdb->esc_like($query) . '%';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->templates_table}
            WHERE template_name LIKE %s
            OR template_description LIKE %s
            OR tags LIKE %s
            ORDER BY featured DESC, template_name ASC",
            $search, $search, $search
        ));
    }

    /**
     * Filter templates
     *
     * @param array $filters Filter parameters
     * @return array Array of template objects
     */
    public function filter_templates($filters) {
        global $wpdb;

        $where = array('1=1');
        $params = array();

        if (!empty($filters['category'])) {
            $where[] = 'category = %s';
            $params[] = $filters['category'];
        }

        if (!empty($filters['campaign_type'])) {
            $where[] = "(campaign_type = %s OR campaign_type = 'all')";
            $params[] = $filters['campaign_type'];
        }

        if (!empty($filters['election_level'])) {
            $where[] = "(election_level = %s OR election_level = 'all')";
            $params[] = $filters['election_level'];
        }

        if (isset($filters['is_premium'])) {
            $where[] = 'is_premium = %d';
            $params[] = $filters['is_premium'] ? 1 : 0;
        }

        if (!empty($filters['featured'])) {
            $where[] = 'featured = 1';
        }

        $where_clause = implode(' AND ', $where);
        $query = "SELECT * FROM {$this->templates_table} WHERE {$where_clause} ORDER BY featured DESC, template_name ASC";

        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }

        return $wpdb->get_results($query);
    }

    /**
     * Apply template to a post
     *
     * @param string $template_key Template key
     * @param int $post_id Post ID
     * @return bool Success status
     */
    public function apply_template($template_key, $post_id) {
        $template = $this->get_template($template_key);

        if (!$template) {
            return false;
        }

        // Check premium access
        if ($template->is_premium && (!function_exists('cp_is_premium_active') || !cp_is_premium_active())) {
            return false;
        }

        $template_data = json_decode($template->template_data, true);

        if (!$template_data || !isset($template_data['components'])) {
            return false;
        }

        // Get the Design Studio instance
        global $wpdb;
        $designs_table = $wpdb->prefix . 'cp_page_designs';

        // Prepare component data for Design Studio
        $design_components = array();
        foreach ($template_data['components'] as $component) {
            $design_components[] = array(
                'type' => $component['type'],
                'variant' => $component['variant'] ?? 'default',
                'settings' => $component['settings'] ?? array(),
                'html' => $this->generate_component_html($component),
            );
        }

        // Check if design exists for this post
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$designs_table} WHERE post_id = %d",
            $post_id
        ));

        $data = array(
            'post_id' => $post_id,
            'design_data' => wp_json_encode($design_components),
            'template_name' => $template_key,
            'custom_css' => $template_data['custom_css'] ?? '',
            'responsive_settings' => isset($template_data['responsive_settings']) ? wp_json_encode($template_data['responsive_settings']) : '',
            'created_by' => get_current_user_id(),
        );

        if ($existing) {
            $wpdb->update($designs_table, $data, array('post_id' => $post_id));
        } else {
            $wpdb->insert($designs_table, $data);
        }

        // Increment download count
        $wpdb->query($wpdb->prepare(
            "UPDATE {$this->templates_table} SET downloads = downloads + 1 WHERE template_key = %s",
            $template_key
        ));

        return true;
    }

    /**
     * Generate HTML for a component
     *
     * @param array $component Component data
     * @return string Generated HTML
     */
    private function generate_component_html($component) {
        $type = $component['type'] ?? 'unknown';
        $variant = $component['variant'] ?? 'default';
        $settings = $component['settings'] ?? array();

        // Generate placeholder HTML (will be replaced with actual rendering later)
        $html = sprintf(
            '<div class="cp-component cp-component-%s cp-variant-%s" data-settings="%s">',
            esc_attr($type),
            esc_attr($variant),
            esc_attr(wp_json_encode($settings))
        );

        $html .= sprintf(
            '<div class="cp-component-preview"><h3>%s Component - %s Variant</h3></div>',
            esc_html(ucfirst(str_replace('_', ' ', $type))),
            esc_html(ucfirst($variant))
        );

        $html .= '</div>';

        return $html;
    }

    /**
     * Enqueue template browser assets
     */
    public function enqueue_browser_assets($hook) {
        if ($hook !== 'design-studio_page_cp-premium-templates') {
            return;
        }

        // Enqueue jQuery
        wp_enqueue_script('jquery');

        // Enqueue template browser CSS
        wp_enqueue_style(
            'cp-premium-template-browser',
            get_template_directory_uri() . '/assets/css/premium-template-browser.css',
            array(),
            '2.0.0'
        );

        // Enqueue template browser JavaScript
        wp_enqueue_script(
            'cp-premium-template-browser',
            get_template_directory_uri() . '/assets/js/premium-template-browser.js',
            array('jquery'),
            '2.0.0',
            true
        );

        // Localize script
        wp_localize_script('cp-premium-template-browser', 'cpPremiumTemplates', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cp_premium_templates'),
            'postId' => isset($_GET['post_id']) ? absint($_GET['post_id']) : 0,
            'is_premium' => function_exists('cp_is_premium_active') && cp_is_premium_active(),
            'strings' => array(
                'loading' => __('Loading...', 'campaign-office'),
                'applying' => __('Applying template...', 'campaign-office'),
                'success' => __('Template applied successfully!', 'campaign-office'),
                'error' => __('Error applying template', 'campaign-office'),
                'premium_required' => __('Premium license required', 'campaign-office'),
                'select_page' => __('Please select a page first', 'campaign-office'),
            ),
        ));
    }

    /**
     * Get browser CSS
     * @deprecated 2.0.0 Now using external file
     */
    private function get_browser_css() {
        // External CSS file is now enqueued via enqueue_browser_assets
        return '';
    }

    /**
     * Get browser JavaScript
     * @deprecated 2.0.0 Now using external file
     */
    private function get_browser_js() {
        // External JS file is now enqueued via enqueue_browser_assets
        return '';
    }

    /**
     * AJAX: Get template preview
     */
    public function ajax_get_template_preview() {
        check_ajax_referer('cp_premium_templates', 'nonce');

        $template_key = sanitize_text_field($_POST['template_key'] ?? '');
        $template = $this->get_template($template_key);

        if (!$template) {
            wp_send_json_error(array('message' => __('Template not found', 'campaign-office')));
        }

        wp_send_json_success(array(
            'template' => $template,
            'data' => json_decode($template->template_data, true),
        ));
    }

    /**
     * AJAX: Apply premium template
     */
    public function ajax_apply_premium_template() {
        check_ajax_referer('cp_premium_templates', 'nonce');

        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => __('Permission denied', 'campaign-office')));
        }

        $template_key = sanitize_text_field($_POST['template_key'] ?? '');
        $post_id = intval($_POST['post_id'] ?? 0);

        if (!$post_id) {
            wp_send_json_error(array('message' => __('Invalid post ID', 'campaign-office')));
        }

        $result = $this->apply_template($template_key, $post_id);

        if ($result) {
            wp_send_json_success(array(
                'message' => __('Template applied successfully!', 'campaign-office'),
                'redirect_url' => admin_url('admin.php?page=cp-design-studio&post_id=' . $post_id),
            ));
        } else {
            wp_send_json_error(array('message' => __('Error applying template', 'campaign-office')));
        }
    }

    /**
     * AJAX: Search templates
     */
    public function ajax_search_templates() {
        check_ajax_referer('cp_premium_templates', 'nonce');

        $query = sanitize_text_field($_POST['query'] ?? '');
        $templates = $this->search_templates($query);

        wp_send_json_success(array('templates' => $templates));
    }

    /**
     * AJAX: Get templates by filter
     */
    public function ajax_get_templates_by_filter() {
        check_ajax_referer('cp_premium_templates', 'nonce');

        $filters = array(
            'category' => sanitize_text_field($_POST['category'] ?? ''),
            'campaign_type' => sanitize_text_field($_POST['campaign_type'] ?? ''),
            'election_level' => sanitize_text_field($_POST['election_level'] ?? ''),
            'is_premium' => isset($_POST['is_premium']) ? (bool) $_POST['is_premium'] : null,
            'featured' => isset($_POST['featured']) ? (bool) $_POST['featured'] : false,
        );

        $templates = $this->filter_templates($filters);

        wp_send_json_success(array('templates' => $templates));
    }

    /**
     * Get categories
     *
     * @return array Categories array
     */
    public function get_categories() {
        return $this->categories;
    }
}

// Initialize Premium Templates
if (function_exists('cp_is_premium_active') && cp_is_premium_active()) {
    new CP_Premium_Templates();
}
