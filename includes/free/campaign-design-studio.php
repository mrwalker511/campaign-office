<?php
/**
 * Campaign Design Studio
 *
 * Custom visual design tool built specifically for political campaigns.
 * No third-party page builders - 100% custom implementation with
 * drag-and-drop interface, live preview, and campaign-specific components.
 *
 * Features:
 * - Drag-and-drop section builder
 * - Campaign-specific design blocks
 * - Live preview with device switcher
 * - Component library (hero, CTA, stats, testimonials)
 * - Template presets
 * - Custom CSS editor
 * - Responsive design controls
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Campaign_Design_Studio
 *
 * Custom visual design tool for campaign websites
 */
class CP_Campaign_Design_Studio {

    /**
     * Database table for saved designs
     *
     * @var string
     */
    private $designs_table;

    /**
     * Available design components
     *
     * @var array
     */
    private $components = array();

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->designs_table = $wpdb->prefix . 'cp_page_designs';

        // Initialize components
        $this->init_components();

        // Database setup
        add_action('after_setup_theme', array($this, 'create_designs_table'));

        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Add design studio to post editor
        add_action('add_meta_boxes', array($this, 'add_design_meta_box'));

        // Save design data
        add_action('save_post', array($this, 'save_design_data'));

        // AJAX handlers
        add_action('wp_ajax_cp_save_design', array($this, 'ajax_save_design'));
        add_action('wp_ajax_cp_load_design', array($this, 'ajax_load_design'));
        add_action('wp_ajax_cp_get_component_html', array($this, 'ajax_get_component_html'));
        add_action('wp_ajax_cp_preview_design', array($this, 'ajax_preview_design'));
        add_action('wp_ajax_cp_apply_template', array($this, 'ajax_apply_template'));

        // Enqueue studio assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_studio_assets'));

        // Front-end rendering
        add_filter('the_content', array($this, 'render_custom_design'));
    }

    /**
     * Create designs database table
     */
    public function create_designs_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->designs_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id bigint(20) UNSIGNED NOT NULL,
            design_data longtext NOT NULL,
            design_type varchar(50) DEFAULT 'page',
            template_name varchar(255) DEFAULT NULL,
            custom_css text DEFAULT NULL,
            responsive_settings text DEFAULT NULL,
            created_by bigint(20) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY design_type (design_type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('cp_design_studio_table_created', true);
    }

    /**
     * Initialize design components
     */
    private function init_components() {
        $this->components = array(
            'hero' => array(
                'name' => __('Hero Section', 'campaign-office'),
                'icon' => '🎯',
                'category' => 'headers',
                'variants' => array('centered', 'split', 'video', 'minimal'),
            ),
            'cta' => array(
                'name' => __('Call-to-Action', 'campaign-office'),
                'icon' => '📢',
                'category' => 'conversion',
                'variants' => array('banner', 'card', 'modal', 'inline'),
            ),
            'stats' => array(
                'name' => __('Campaign Stats', 'campaign-office'),
                'icon' => '📊',
                'category' => 'data',
                'variants' => array('counters', 'progress-bars', 'charts', 'badges'),
            ),
            'testimonials' => array(
                'name' => __('Testimonials', 'campaign-office'),
                'icon' => '💬',
                'category' => 'social-proof',
                'variants' => array('carousel', 'grid', 'masonry', 'featured'),
            ),
            'donation' => array(
                'name' => __('Donation Form', 'campaign-office'),
                'icon' => '💰',
                'category' => 'conversion',
                'variants' => array('tiers', 'thermometer', 'quick-donate', 'recurring'),
            ),
            'volunteer' => array(
                'name' => __('Volunteer Signup', 'campaign-office'),
                'icon' => '👥',
                'category' => 'conversion',
                'variants' => array('form', 'opportunities', 'impact', 'leaderboard'),
            ),
            'events' => array(
                'name' => __('Events Calendar', 'campaign-office'),
                'icon' => '📅',
                'category' => 'content',
                'variants' => array('list', 'grid', 'calendar', 'featured'),
            ),
            'issues' => array(
                'name' => __('Policy Issues', 'campaign-office'),
                'icon' => '📋',
                'category' => 'content',
                'variants' => array('grid', 'accordion', 'tabs', 'timeline'),
            ),
            'team' => array(
                'name' => __('Team Members', 'campaign-office'),
                'icon' => '👔',
                'category' => 'about',
                'variants' => array('grid', 'carousel', 'org-chart', 'cards'),
            ),
            'timeline' => array(
                'name' => __('Campaign Timeline', 'campaign-office'),
                'icon' => '⏳',
                'category' => 'content',
                'variants' => array('vertical', 'horizontal', 'roadmap', 'milestones'),
            ),
            'countdown' => array(
                'name' => __('Election Countdown', 'campaign-office'),
                'icon' => '⏰',
                'category' => 'urgency',
                'variants' => array('flip-clock', 'simple', 'circular', 'banner'),
            ),
            'social-feed' => array(
                'name' => __('Social Media', 'campaign-office'),
                'icon' => '📱',
                'category' => 'social',
                'variants' => array('instagram', 'twitter', 'facebook', 'combined'),
            ),
        );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Design Studio', 'campaign-office'),
            __('Design Studio', 'campaign-office'),
            'edit_pages',
            'cp-design-studio',
            array($this, 'render_studio_page'),
            'dashicons-layout',
            27
        );

        add_submenu_page(
            'cp-design-studio',
            __('Page Builder', 'campaign-office'),
            __('Page Builder', 'campaign-office'),
            'edit_pages',
            'cp-design-studio',
            array($this, 'render_studio_page')
        );

        add_submenu_page(
            'cp-design-studio',
            __('Templates', 'campaign-office'),
            __('Templates', 'campaign-office'),
            'edit_pages',
            'cp-design-templates',
            array($this, 'render_templates_page')
        );

        add_submenu_page(
            'cp-design-studio',
            __('Global Styles', 'campaign-office'),
            __('Global Styles', 'campaign-office'),
            'edit_pages',
            'cp-global-styles',
            array($this, 'render_global_styles_page')
        );
    }

    /**
     * Render design studio page
     */
    public function render_studio_page() {
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        $design_data = $this->get_design_data($post_id);
        ?>
        <div class="wrap cp-design-studio-wrap">
            <div class="cp-studio-header">
                <div class="cp-studio-title">
                    <h1><?php esc_html_e('Campaign Design Studio', 'campaign-office'); ?></h1>
                    <span class="cp-beta-badge">BETA</span>
                </div>
                <div class="cp-studio-actions">
                    <select id="cp-page-selector" class="cp-select">
                        <option value=""><?php esc_html_e('Select a page to edit...', 'campaign-office'); ?></option>
                        <?php
                        $pages = get_pages();
                        foreach ($pages as $page) {
                            printf(
                                '<option value="%d" %s>%s</option>',
                                $page->ID,
                                selected($post_id, $page->ID, false),
                                esc_html($page->post_title)
                            );
                        }
                        ?>
                    </select>
                    <button class="button" id="cp-device-desktop" title="<?php esc_attr_e('Desktop View', 'campaign-office'); ?>">
                        <span class="dashicons dashicons-desktop"></span>
                    </button>
                    <button class="button" id="cp-device-tablet" title="<?php esc_attr_e('Tablet View', 'campaign-office'); ?>">
                        <span class="dashicons dashicons-tablet"></span>
                    </button>
                    <button class="button" id="cp-device-mobile" title="<?php esc_attr_e('Mobile View', 'campaign-office'); ?>">
                        <span class="dashicons dashicons-smartphone"></span>
                    </button>
                    <button class="button button-primary" id="cp-save-design-btn">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e('Save Design', 'campaign-office'); ?>
                    </button>
                </div>
            </div>

            <div class="cp-studio-body">
                <!-- Component Sidebar -->
                <div class="cp-studio-sidebar">
                    <div class="cp-sidebar-tabs">
                        <button class="cp-tab active" data-tab="components">
                            <span class="dashicons dashicons-admin-page"></span>
                            <?php esc_html_e('Components', 'campaign-office'); ?>
                        </button>
                        <button class="cp-tab" data-tab="styles">
                            <span class="dashicons dashicons-admin-customizer"></span>
                            <?php esc_html_e('Styles', 'campaign-office'); ?>
                        </button>
                        <button class="cp-tab" data-tab="settings">
                            <span class="dashicons dashicons-admin-settings"></span>
                            <?php esc_html_e('Settings', 'campaign-office'); ?>
                        </button>
                    </div>

                    <div class="cp-sidebar-content">
                        <!-- Components Tab -->
                        <div class="cp-tab-content active" data-tab-content="components">
                            <div class="cp-component-search">
                                <input type="text" id="cp-search-components" placeholder="<?php esc_attr_e('Search components...', 'campaign-office'); ?>" class="cp-input">
                            </div>

                            <div class="cp-components-list">
                                <?php foreach ($this->components as $key => $component) : ?>
                                    <div class="cp-component-card" data-component="<?php echo esc_attr($key); ?>" draggable="true">
                                        <div class="cp-component-icon"><?php echo $component['icon']; ?></div>
                                        <div class="cp-component-info">
                                            <div class="cp-component-name"><?php echo esc_html($component['name']); ?></div>
                                            <div class="cp-component-category"><?php echo esc_html(ucfirst($component['category'])); ?></div>
                                        </div>
                                        <div class="cp-component-variants">
                                            <?php echo count($component['variants']); ?> variants
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Styles Tab -->
                        <div class="cp-tab-content" data-tab-content="styles">
                            <h3><?php esc_html_e('Component Styles', 'campaign-office'); ?></h3>
                            <div id="cp-style-controls">
                                <p class="description"><?php esc_html_e('Select a component to edit its styles', 'campaign-office'); ?></p>
                            </div>
                        </div>

                        <!-- Settings Tab -->
                        <div class="cp-tab-content" data-tab-content="settings">
                            <h3><?php esc_html_e('Page Settings', 'campaign-office'); ?></h3>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Page Template', 'campaign-office'); ?></label>
                                <select class="cp-input">
                                    <option><?php esc_html_e('Default', 'campaign-office'); ?></option>
                                    <option><?php esc_html_e('Full Width', 'campaign-office'); ?></option>
                                    <option><?php esc_html_e('Landing Page', 'campaign-office'); ?></option>
                                </select>
                            </div>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Custom CSS', 'campaign-office'); ?></label>
                                <textarea class="cp-input" id="cp-custom-css" rows="10" placeholder="/* Add custom CSS here */"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Canvas/Preview Area -->
                <div class="cp-studio-canvas">
                    <div class="cp-canvas-header">
                        <span class="cp-canvas-breadcrumb">
                            <?php esc_html_e('Canvas', 'campaign-office'); ?> /
                            <span id="cp-selected-component-name"><?php esc_html_e('No component selected', 'campaign-office'); ?></span>
                        </span>
                        <button class="button button-small" id="cp-clear-canvas">
                            <span class="dashicons dashicons-trash"></span>
                            <?php esc_html_e('Clear All', 'campaign-office'); ?>
                        </button>
                    </div>

                    <div class="cp-canvas-viewport" id="cp-canvas-viewport" data-device="desktop">
                        <div class="cp-canvas-drop-zone" id="cp-canvas">
                            <div class="cp-canvas-empty-state">
                                <div class="cp-empty-icon">
                                    <span class="dashicons dashicons-layout"></span>
                                </div>
                                <h3><?php esc_html_e('Start Building', 'campaign-office'); ?></h3>
                                <p><?php esc_html_e('Drag components from the sidebar to start designing your page', 'campaign-office'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Properties Panel -->
                <div class="cp-studio-properties">
                    <h3><?php esc_html_e('Component Properties', 'campaign-office'); ?></h3>
                    <div id="cp-properties-content">
                        <p class="description"><?php esc_html_e('Select a component to edit its properties', 'campaign-office'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .cp-design-studio-wrap {
            margin: 0 -20px 0 -2px;
            background: #f0f0f1;
        }
        .cp-studio-header {
            background: #fff;
            border-bottom: 1px solid #ddd;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cp-studio-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .cp-studio-title h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        .cp-beta-badge {
            background: #d63638;
            color: #fff;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.625rem;
            font-weight: 700;
        }
        .cp-studio-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .cp-select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 0.25rem;
        }
        .cp-studio-body {
            display: grid;
            grid-template-columns: 300px 1fr 320px;
            height: calc(100vh - 140px);
        }
        .cp-studio-sidebar {
            background: #fff;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
        }
        .cp-sidebar-tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
        }
        .cp-tab {
            flex: 1;
            padding: 1rem;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.75rem;
        }
        .cp-tab.active {
            border-bottom-color: #2271b1;
            color: #2271b1;
        }
        .cp-sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }
        .cp-tab-content {
            display: none;
        }
        .cp-tab-content.active {
            display: block;
        }
        .cp-component-search {
            margin-bottom: 1rem;
        }
        .cp-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 0.25rem;
        }
        .cp-components-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .cp-component-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            padding: 1rem;
            cursor: grab;
            transition: all 0.3s;
        }
        .cp-component-card:hover {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .cp-component-card:active {
            cursor: grabbing;
        }
        .cp-component-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .cp-component-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .cp-component-category {
            font-size: 0.75rem;
            color: #666;
            text-transform: uppercase;
        }
        .cp-component-variants {
            font-size: 0.75rem;
            color: #0073aa;
            margin-top: 0.5rem;
        }
        .cp-studio-canvas {
            background: #e5e5e5;
            display: flex;
            flex-direction: column;
            overflow: auto;
        }
        .cp-canvas-header {
            background: #fff;
            border-bottom: 1px solid #ddd;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cp-canvas-breadcrumb {
            font-size: 0.875rem;
            color: #666;
        }
        .cp-canvas-viewport {
            flex: 1;
            padding: 2rem;
            display: flex;
            justify-content: center;
        }
        .cp-canvas-viewport[data-device="desktop"] #cp-canvas {
            width: 100%;
            max-width: 1200px;
        }
        .cp-canvas-viewport[data-device="tablet"] #cp-canvas {
            width: 768px;
        }
        .cp-canvas-viewport[data-device="mobile"] #cp-canvas {
            width: 375px;
        }
        #cp-canvas {
            background: #fff;
            min-height: 600px;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: relative;
        }
        .cp-canvas-empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #999;
        }
        .cp-empty-icon .dashicons {
            font-size: 4rem;
            width: 4rem;
            height: 4rem;
        }
        .cp-studio-properties {
            background: #fff;
            border-left: 1px solid #ddd;
            padding: 1.5rem;
            overflow-y: auto;
        }
        .cp-setting-group {
            margin-bottom: 1.5rem;
        }
        .cp-setting-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .cp-dropped-component {
            border: 2px dashed #2271b1;
            padding: 2rem;
            margin: 1rem;
            border-radius: 0.5rem;
            position: relative;
            transition: all 0.3s;
        }
        .cp-dropped-component:hover {
            background: #f9f9f9;
        }
        .cp-component-controls {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            display: flex;
            gap: 0.25rem;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .cp-dropped-component:hover .cp-component-controls {
            opacity: 1;
        }
        .cp-control-btn {
            background: #2271b1;
            color: #fff;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 0.25rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cp-control-btn:hover {
            background: #135e96;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Tab switching
            $('.cp-tab').click(function() {
                var tab = $(this).data('tab');
                $('.cp-tab').removeClass('active');
                $(this).addClass('active');
                $('.cp-tab-content').removeClass('active');
                $('[data-tab-content="' + tab + '"]').addClass('active');
            });

            // Device switcher
            $('#cp-device-desktop').click(function() {
                $('.cp-canvas-viewport').attr('data-device', 'desktop');
                $('#cp-device-desktop, #cp-device-tablet, #cp-device-mobile').removeClass('button-primary');
                $(this).addClass('button-primary');
            });
            $('#cp-device-tablet').click(function() {
                $('.cp-canvas-viewport').attr('data-device', 'tablet');
                $('#cp-device-desktop, #cp-device-tablet, #cp-device-mobile').removeClass('button-primary');
                $(this).addClass('button-primary');
            });
            $('#cp-device-mobile').click(function() {
                $('.cp-canvas-viewport').attr('data-device', 'mobile');
                $('#cp-device-desktop, #cp-device-tablet, #cp-device-mobile').removeClass('button-primary');
                $(this).addClass('button-primary');
            });

            // Drag and drop
            var draggedComponent = null;

            $('.cp-component-card').on('dragstart', function(e) {
                draggedComponent = $(this).data('component');
                $(this).css('opacity', '0.5');
            });

            $('.cp-component-card').on('dragend', function(e) {
                $(this).css('opacity', '1');
            });

            $('#cp-canvas').on('dragover', function(e) {
                e.preventDefault();
                $(this).css('background', '#f0f8ff');
            });

            $('#cp-canvas').on('dragleave', function(e) {
                $(this).css('background', '#fff');
            });

            $('#cp-canvas').on('drop', function(e) {
                e.preventDefault();
                $(this).css('background', '#fff');

                if (draggedComponent) {
                    $('.cp-canvas-empty-state').remove();

                    var componentHTML = '<div class="cp-dropped-component" data-component="' + draggedComponent + '">' +
                        '<div class="cp-component-controls">' +
                        '<button class="cp-control-btn cp-edit-component" title="Edit"><span class="dashicons dashicons-edit"></span></button>' +
                        '<button class="cp-control-btn cp-delete-component" title="Delete"><span class="dashicons dashicons-trash"></span></button>' +
                        '</div>' +
                        '<div class="cp-component-content">' +
                        '<h3>🎯 ' + draggedComponent.replace('-', ' ').toUpperCase() + '</h3>' +
                        '<p>Component preview will appear here</p>' +
                        '</div>' +
                        '</div>';

                    $(this).append(componentHTML);
                    draggedComponent = null;
                }
            });

            // Component controls
            $(document).on('click', '.cp-delete-component', function() {
                if (confirm('<?php esc_js_e('Delete this component?', 'campaign-office'); ?>')) {
                    $(this).closest('.cp-dropped-component').fadeOut(300, function() {
                        $(this).remove();
                        if ($('#cp-canvas .cp-dropped-component').length === 0) {
                            $('#cp-canvas').html('<div class="cp-canvas-empty-state"><div class="cp-empty-icon"><span class="dashicons dashicons-layout"></span></div><h3><?php esc_js_e('Start Building', 'campaign-office'); ?></h3><p><?php esc_js_e('Drag components from the sidebar to start designing your page', 'campaign-office'); ?></p></div>');
                        }
                    });
                }
            });

            // Clear canvas
            $('#cp-clear-canvas').click(function() {
                if (confirm('<?php esc_js_e('Clear all components?', 'campaign-office'); ?>')) {
                    $('#cp-canvas').html('<div class="cp-canvas-empty-state"><div class="cp-empty-icon"><span class="dashicons dashicons-layout"></span></div><h3><?php esc_js_e('Start Building', 'campaign-office'); ?></h3><p><?php esc_js_e('Drag components from the sidebar to start designing your page', 'campaign-office'); ?></p></div>');
                }
            });

            // Save design
            $('#cp-save-design-btn').click(function() {
                var components = [];
                $('.cp-dropped-component').each(function() {
                    components.push({
                        type: $(this).data('component'),
                        html: $(this).html()
                    });
                });

                $(this).prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> <?php esc_js_e('Saving...', 'campaign-office'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cp_save_design',
                        post_id: $('#cp-page-selector').val(),
                        design_data: JSON.stringify(components),
                        custom_css: $('#cp-custom-css').val(),
                        _wpnonce: '<?php echo wp_create_nonce('cp_save_design'); ?>'
                    },
                    success: function(response) {
                        $('#cp-save-design-btn').prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> <?php esc_js_e('Saved!', 'campaign-office'); ?>');
                        setTimeout(function() {
                            $('#cp-save-design-btn').html('<span class="dashicons dashicons-saved"></span> <?php esc_js_e('Save Design', 'campaign-office'); ?>');
                        }, 2000);
                    }
                });
            });

            // Page selector
            $('#cp-page-selector').change(function() {
                var postId = $(this).val();
                if (postId) {
                    window.location.href = '?page=cp-design-studio&post_id=' + postId;
                }
            });

            // Load existing design if post_id is set
            var currentPostId = $('#cp-page-selector').val();
            if (currentPostId) {
                loadExistingDesign(currentPostId);
            }

            // Function to load existing design
            function loadExistingDesign(postId) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cp_load_design',
                        post_id: postId,
                        _wpnonce: '<?php echo wp_create_nonce('cp_load_design'); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data.design_data && response.data.design_data.length > 0) {
                            // Clear canvas
                            $('.cp-canvas-empty-state').remove();

                            // Add each component to canvas
                            response.data.design_data.forEach(function(component) {
                                var componentHTML = '<div class="cp-dropped-component" data-component="' + component.type + '">' +
                                    component.html +
                                    '</div>';
                                $('#cp-canvas').append(componentHTML);
                            });

                            // Load custom CSS if exists
                            if (response.data.custom_css) {
                                $('#cp-custom-css').val(response.data.custom_css);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading design:', error);
                    }
                });
            }
        });
        </script>
        <?php
    }

    /**
     * Render templates page
     */
    public function render_templates_page() {
        $templates = $this->get_template_data();
        $gradients = array(
            'classic_campaign' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'grassroots_movement' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            'issues_first' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        );
        $icons = array(
            'classic_campaign' => '🎯',
            'grassroots_movement' => '📱',
            'issues_first' => '📋',
        );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Design Templates', 'campaign-office'); ?></h1>
            <p class="description">
                <?php esc_html_e('Choose a template, select a page, and apply it instantly. Each template includes pre-configured components optimized for campaign websites.', 'campaign-office'); ?>
            </p>

            <div style="background: #fff; padding: 1.5rem; margin: 2rem 0 1rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <label for="cp-template-page-selector" style="font-weight: 600; margin-right: 1rem;">
                    <?php esc_html_e('Apply template to:', 'campaign-office'); ?>
                </label>
                <select id="cp-template-page-selector" style="min-width: 300px;">
                    <option value=""><?php esc_html_e('Select a page...', 'campaign-office'); ?></option>
                    <?php
                    $pages = get_pages();
                    foreach ($pages as $page) {
                        printf(
                            '<option value="%d">%s</option>',
                            $page->ID,
                            esc_html($page->post_title)
                        );
                    }
                    ?>
                </select>
            </div>

            <div class="cp-templates-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 2rem;">

                <?php foreach ($templates as $key => $template) : ?>
                <div class="cp-template-card" style="background: #fff; border-radius: 0.5rem; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div class="cp-template-preview" style="background: <?php echo esc_attr($gradients[$key] ?? '#667eea'); ?>; height: 200px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 3rem;">
                        <?php echo $icons[$key] ?? '📄'; ?>
                    </div>
                    <div class="cp-template-info" style="padding: 1.5rem;">
                        <h3 style="margin: 0 0 0.5rem 0;"><?php echo esc_html($template['name']); ?></h3>
                        <p style="color: #666; font-size: 0.875rem; margin: 0 0 1rem 0;">
                            <?php echo esc_html($template['description']); ?>
                        </p>
                        <div style="margin-bottom: 1rem; padding: 0.75rem; background: #f5f5f5; border-radius: 0.25rem;">
                            <strong style="font-size: 0.75rem; color: #666; text-transform: uppercase;">
                                <?php esc_html_e('Includes:', 'campaign-office'); ?>
                            </strong>
                            <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 0.75rem; color: #666;">
                                <?php foreach ($template['components'] as $component) : ?>
                                    <li><?php echo esc_html(ucfirst(str_replace('_', ' ', $component['type']))); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <button class="button button-primary cp-use-template" data-template="<?php echo esc_attr($key); ?>" style="width: 100%;">
                            <?php esc_html_e('Use This Template', 'campaign-office'); ?>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.cp-use-template').click(function() {
                var templateKey = $(this).data('template');
                var postId = $('#cp-template-page-selector').val();
                var $btn = $(this);

                if (!postId) {
                    alert('<?php esc_js_e('Please select a page first', 'campaign-office'); ?>');
                    return;
                }

                $btn.prop('disabled', true).text('<?php esc_js_e('Applying...', 'campaign-office'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cp_apply_template',
                        template: templateKey,
                        post_id: postId,
                        _wpnonce: '<?php echo wp_create_nonce('cp_apply_template'); ?>'
                    },
                    success: function(response) {
                        $btn.prop('disabled', false).text('<?php esc_js_e('Use This Template', 'campaign-office'); ?>');
                        if (response.success) {
                            alert('<?php esc_js_e('Template applied successfully! Open the Design Studio to customize it.', 'campaign-office'); ?>');
                            window.location.href = '?page=cp-design-studio&post_id=' + postId;
                        } else {
                            alert(response.data.message || '<?php esc_js_e('Error applying template', 'campaign-office'); ?>');
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).text('<?php esc_js_e('Use This Template', 'campaign-office'); ?>');
                        alert('<?php esc_js_e('Error applying template', 'campaign-office'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Render global styles page
     */
    public function render_global_styles_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Global Styles', 'campaign-office'); ?></h1>
            <p class="description"><?php esc_html_e('Define global design settings that apply across your entire website.', 'campaign-office'); ?></p>

            <div class="card" style="max-width: 800px; margin-top: 2rem;">
                <h2><?php esc_html_e('Typography', 'campaign-office'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('Heading Font', 'campaign-office'); ?></th>
                        <td>
                            <select class="regular-text">
                                <option>Inter</option>
                                <option>Roboto</option>
                                <option>Poppins</option>
                                <option>Montserrat</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Body Font', 'campaign-office'); ?></th>
                        <td>
                            <select class="regular-text">
                                <option>Inter</option>
                                <option>Open Sans</option>
                                <option>Lato</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="card" style="max-width: 800px; margin-top: 2rem;">
                <h2><?php esc_html_e('Color Palette', 'campaign-office'); ?></h2>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <div>
                        <label><?php esc_html_e('Primary Color', 'campaign-office'); ?></label>
                        <input type="color" value="#0073aa" style="width: 100%; height: 50px; border: none; border-radius: 0.25rem; cursor: pointer;">
                    </div>
                    <div>
                        <label><?php esc_html_e('Secondary Color', 'campaign-office'); ?></label>
                        <input type="color" value="#00a0d2" style="width: 100%; height: 50px; border: none; border-radius: 0.25rem; cursor: pointer;">
                    </div>
                    <div>
                        <label><?php esc_html_e('Accent Color', 'campaign-office'); ?></label>
                        <input type="color" value="#d63638" style="width: 100%; height: 50px; border: none; border-radius: 0.25rem; cursor: pointer;">
                    </div>
                </div>
            </div>

            <p class="submit">
                <button class="button button-primary"><?php esc_html_e('Save Global Styles', 'campaign-office'); ?></button>
            </p>
        </div>
        <?php
    }

    /**
     * Add design meta box to post editor
     */
    public function add_design_meta_box() {
        $post_types = array('page', 'post', 'cp_event');
        foreach ($post_types as $post_type) {
            add_meta_box(
                'cp_design_studio',
                __('Design Studio', 'campaign-office'),
                array($this, 'render_design_meta_box'),
                $post_type,
                'side',
                'high'
            );
        }
    }

    /**
     * Render design meta box
     */
    public function render_design_meta_box($post) {
        $design_exists = $this->get_design_data($post->ID);
        ?>
        <div class="cp-design-metabox">
            <?php if ($design_exists) : ?>
                <p>
                    <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                    <?php esc_html_e('Custom design active', 'campaign-office'); ?>
                </p>
            <?php else : ?>
                <p><?php esc_html_e('No custom design yet', 'campaign-office'); ?></p>
            <?php endif; ?>

            <a href="<?php echo admin_url('admin.php?page=cp-design-studio&post_id=' . $post->ID); ?>" class="button button-primary button-large" style="width: 100%;">
                <span class="dashicons dashicons-layout"></span>
                <?php esc_html_e('Open Design Studio', 'campaign-office'); ?>
            </a>
        </div>
        <?php
    }

    /**
     * Helper methods
     */

    private function get_design_data($post_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->designs_table} WHERE post_id = %d", $post_id));
    }

    public function save_design_data($post_id) {
        // Hook for saving from WordPress editor
    }

    /**
     * AJAX handlers
     */

    public function ajax_save_design() {
        check_ajax_referer('cp_save_design');

        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => __('Permission denied', 'campaign-office')));
        }

        $post_id = intval($_POST['post_id']);
        $design_data = wp_unslash($_POST['design_data']);
        $custom_css = sanitize_textarea_field($_POST['custom_css'] ?? '');

        global $wpdb;

        // Check if design exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->designs_table} WHERE post_id = %d", $post_id));

        if ($existing) {
            $wpdb->update(
                $this->designs_table,
                array(
                    'design_data' => $design_data,
                    'custom_css' => $custom_css,
                ),
                array('post_id' => $post_id)
            );
        } else {
            $wpdb->insert(
                $this->designs_table,
                array(
                    'post_id' => $post_id,
                    'design_data' => $design_data,
                    'custom_css' => $custom_css,
                    'created_by' => get_current_user_id(),
                )
            );
        }

        wp_send_json_success(array('message' => __('Design saved successfully', 'campaign-office')));
    }

    public function ajax_load_design() {
        check_ajax_referer('cp_load_design');

        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => __('Permission denied', 'campaign-office')));
        }

        $post_id = intval($_POST['post_id']);
        $design = $this->get_design_data($post_id);

        if ($design) {
            wp_send_json_success(array(
                'design_data' => json_decode($design->design_data, true),
                'custom_css' => $design->custom_css ?? '',
                'template_name' => $design->template_name ?? '',
            ));
        } else {
            wp_send_json_success(array(
                'design_data' => array(),
                'custom_css' => '',
                'template_name' => '',
            ));
        }
    }

    public function ajax_get_component_html() {
        check_ajax_referer('cp_get_component');

        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => __('Permission denied', 'campaign-office')));
        }

        $component_type = sanitize_text_field($_POST['component_type'] ?? '');
        $variant = sanitize_text_field($_POST['variant'] ?? 'default');

        if (!isset($this->components[$component_type])) {
            wp_send_json_error(array('message' => __('Invalid component type', 'campaign-office')));
        }

        $component = $this->components[$component_type];

        // Generate component HTML based on type and variant
        $html = $this->generate_component_html($component_type, $variant);

        wp_send_json_success(array(
            'html' => $html,
            'component' => $component_type,
            'variant' => $variant,
        ));
    }

    public function ajax_preview_design() {
        check_ajax_referer('cp_preview_design');

        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => __('Permission denied', 'campaign-office')));
        }

        $design_data = wp_unslash($_POST['design_data'] ?? '');
        $custom_css = sanitize_textarea_field($_POST['custom_css'] ?? '');

        // Return preview URL or HTML
        wp_send_json_success(array(
            'preview' => true,
            'message' => __('Preview ready', 'campaign-office'),
        ));
    }

    /**
     * Generate component HTML
     */
    private function generate_component_html($type, $variant) {
        $component_info = $this->components[$type] ?? null;

        if (!$component_info) {
            return '';
        }

        // Generate placeholder HTML for the component
        $html = sprintf(
            '<div class="cp-component cp-component-%s cp-variant-%s">
                <div class="cp-component-icon">%s</div>
                <h3 class="cp-component-title">%s</h3>
                <p class="cp-component-description">%s variant preview</p>
                <div class="cp-component-content">
                    <!-- Component content will be rendered here -->
                    <p style="text-align: center; padding: 2rem; background: #f5f5f5; border-radius: 0.5rem;">
                        <strong>%s Component</strong><br>
                        <span style="color: #666;">Variant: %s</span><br>
                        <small>Customize this component in the properties panel</small>
                    </p>
                </div>
            </div>',
            esc_attr($type),
            esc_attr($variant),
            $component_info['icon'],
            esc_html($component_info['name']),
            esc_html(ucfirst($variant)),
            esc_html($component_info['name']),
            esc_html(ucfirst($variant))
        );

        return $html;
    }

    public function ajax_apply_template() {
        check_ajax_referer('cp_apply_template');

        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => __('Permission denied', 'campaign-office')));
        }

        $post_id = intval($_POST['post_id']);
        $template_key = sanitize_text_field($_POST['template']);

        $templates = $this->get_template_data();

        if (!isset($templates[$template_key])) {
            wp_send_json_error(array('message' => __('Template not found', 'campaign-office')));
        }

        $template = $templates[$template_key];

        global $wpdb;

        // Check if design exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->designs_table} WHERE post_id = %d", $post_id));

        if ($existing) {
            $wpdb->update(
                $this->designs_table,
                array(
                    'design_data' => wp_json_encode($template['components']),
                    'template_name' => $template_key,
                ),
                array('post_id' => $post_id)
            );
        } else {
            $wpdb->insert(
                $this->designs_table,
                array(
                    'post_id' => $post_id,
                    'design_data' => wp_json_encode($template['components']),
                    'template_name' => $template_key,
                    'created_by' => get_current_user_id(),
                )
            );
        }

        wp_send_json_success(array('message' => __('Template applied successfully', 'campaign-office')));
    }

    /**
     * Get template data
     */
    private function get_template_data() {
        return array(
            'classic_campaign' => array(
                'name' => __('Classic Campaign', 'campaign-office'),
                'description' => __('Traditional political campaign layout with hero, issues, and donation sections.', 'campaign-office'),
                'components' => array(
                    array('type' => 'hero', 'variant' => 'centered'),
                    array('type' => 'stats', 'variant' => 'counters'),
                    array('type' => 'issues', 'variant' => 'grid'),
                    array('type' => 'donation', 'variant' => 'tiers'),
                    array('type' => 'events', 'variant' => 'featured'),
                ),
            ),
            'grassroots_movement' => array(
                'name' => __('Grassroots Movement', 'campaign-office'),
                'description' => __('Volunteer-focused design emphasizing community engagement and action.', 'campaign-office'),
                'components' => array(
                    array('type' => 'hero', 'variant' => 'video'),
                    array('type' => 'volunteer', 'variant' => 'opportunities'),
                    array('type' => 'testimonials', 'variant' => 'carousel'),
                    array('type' => 'events', 'variant' => 'list'),
                    array('type' => 'cta', 'variant' => 'banner'),
                ),
            ),
            'issues_first' => array(
                'name' => __('Issues-First', 'campaign-office'),
                'description' => __('Policy-driven layout that leads with detailed issue positions.', 'campaign-office'),
                'components' => array(
                    array('type' => 'hero', 'variant' => 'minimal'),
                    array('type' => 'issues', 'variant' => 'accordion'),
                    array('type' => 'timeline', 'variant' => 'vertical'),
                    array('type' => 'team', 'variant' => 'grid'),
                    array('type' => 'cta', 'variant' => 'card'),
                ),
            ),
        );
    }

    /**
     * Front-end rendering
     */
    public function render_custom_design($content) {
        if (!is_singular()) {
            return $content;
        }

        $design = $this->get_design_data(get_the_ID());

        if (!$design) {
            return $content;
        }

        // Render custom design
        $custom_content = '<div class="cp-custom-design">';
        $design_array = json_decode($design->design_data, true);

        if ($design_array) {
            foreach ($design_array as $component) {
                $custom_content .= '<div class="cp-design-component">' . wp_kses_post($component['html']) . '</div>';
            }
        }

        $custom_content .= '</div>';

        if ($design->custom_css) {
            $custom_content .= '<style>' . wp_strip_all_tags($design->custom_css) . '</style>';
        }

        return $custom_content . $content;
    }

    /**
     * Enqueue studio assets
     */
    public function enqueue_studio_assets($hook) {
        if ($hook !== 'toplevel_page_cp-design-studio' && $hook !== 'cp-design-studio_page_cp-design-templates' && $hook !== 'cp-design-studio_page_cp-global-styles') {
            return;
        }

        // Enqueue jQuery (required for all Design Studio functionality)
        wp_enqueue_script('jquery');

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }
}

// Initialize Design Studio
new CP_Campaign_Design_Studio();
