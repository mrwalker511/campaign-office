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
     * Page hooks
     *
     * @var string
     */
    private $studio_page_hook;
    private $templates_page_hook;
    private $styles_page_hook;

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
        add_action('wp_ajax_cp_get_component_properties', array($this, 'ajax_get_component_properties'));
        add_action('wp_ajax_cp_save_page_settings', array($this, 'ajax_save_page_settings'));
        add_action('wp_ajax_cp_save_style_settings', array($this, 'ajax_save_style_settings'));

        // Enqueue studio assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_studio_assets'));

        // Front-end rendering
        add_filter('the_content', array($this, 'render_custom_design'));

        // Output design system styles
        add_action('wp_head', array($this, 'output_design_system_styles'));
    }

    /**
     * Output design system styles to head
     */
    public function output_design_system_styles() {
        if (!is_singular()) {
            return;
        }

        $post_id = get_the_ID();

        // Get page settings
        $page_settings = get_post_meta($post_id, '_cp_page_settings', true);
        if (!$page_settings) {
            return;
        }

        // Get style settings
        $style_settings = get_post_meta($post_id, '_cp_style_settings', true);
        if (!$style_settings) {
            return;
        }

        // Map values
        $bg_color = $page_settings['bg_color'] ?? '#ffffff';
        $container_width = $page_settings['container_width'] ?? '1200';
        $border_radius = $page_settings['border_radius'] ?? '4';

        $base_font_size = $style_settings['base_font_size'] ?? '16';
        $heading_weight = $style_settings['heading_weight'] ?? '600';
        $line_height = $style_settings['line_height'] ?? '1.5';
        $primary_color = $style_settings['primary_color'] ?? '#0073aa';
        $secondary_color = $style_settings['secondary_color'] ?? '#005a87';
        $accent_color = $style_settings['accent_color'] ?? '#d63638';
        $text_color = $style_settings['text_color'] ?? '#333333';
        $section_padding = $style_settings['section_padding'] ?? 'standard';
        $element_spacing = $style_settings['element_spacing'] ?? 'standard';

        // Map padding values to rem
        $padding_map = array(
            'compact' => '3rem',
            'standard' => '4rem',
            'spacious' => '6rem',
            'extra-spacious' => '8rem',
        );
        $section_padding_rem = $padding_map[$section_padding] ?? '4rem';

        // Map spacing values to rem
        $spacing_map = array(
            'tight' => '0.5rem',
            'standard' => '1rem',
            'relaxed' => '1.5rem',
        );
        $element_spacing_rem = $spacing_map[$element_spacing] ?? '1rem';

        // Output styles
        ?>
        <style id="cp-design-system-styles">
            :root {
                --cp-page-bg: <?php echo esc_attr($bg_color); ?>;
                --cp-container-width: <?php echo esc_attr($container_width); ?>px;
                --cp-border-radius: <?php echo esc_attr($border_radius); ?>px;

                --cp-base-font-size: <?php echo esc_attr($base_font_size); ?>px;
                --cp-heading-weight: <?php echo esc_attr($heading_weight); ?>;
                --cp-line-height: <?php echo esc_attr($line_height); ?>;

                --cp-primary-color: <?php echo esc_attr($primary_color); ?>;
                --cp-secondary-color: <?php echo esc_attr($secondary_color); ?>;
                --cp-accent-color: <?php echo esc_attr($accent_color); ?>;
                --cp-text-color: <?php echo esc_attr($text_color); ?>;

                --cp-section-padding: <?php echo esc_attr($section_padding_rem); ?>;
                --cp-element-spacing: <?php echo esc_attr($element_spacing_rem); ?>;
            }

            body {
                background-color: var(--cp-page-bg);
                font-size: var(--cp-base-font-size);
                line-height: var(--cp-line-height);
                color: var(--cp-text-color);
            }

            h1, h2, h3, h4, h5, h6 {
                font-weight: var(--cp-heading-weight);
                line-height: var(--cp-line-height);
            }

            .site-container,
            .container,
            .wp-block-group__inner-container {
                max-width: var(--cp-container-width);
                margin-left: auto;
                margin-right: auto;
            }

            * {
                border-radius: var(--cp-border-radius);
            }

            .wp-block-button__link,
            .button,
            .btn-primary {
                background-color: var(--cp-primary-color);
                border-color: var(--cp-primary-color);
                border-radius: var(--cp-border-radius);
            }

            .wp-block-button__link:hover,
            .button:hover,
            .btn-primary:hover {
                background-color: var(--cp-secondary-color);
                border-color: var(--cp-secondary-color);
            }

            .cp-section {
                padding: var(--cp-section-padding) 1rem;
            }

            .cp-section > * + * {
                margin-top: var(--cp-element-spacing);
            }

            /* Hero section height variations */
            <?php if (isset($page_settings['hero_height'])) : ?>
                <?php $hero_height = $page_settings['hero_height']; ?>
                <?php if ($hero_height === 'short') : ?>
                    .hero-section,
                    .site-header {
                        min-height: 45vh;
                    }
                <?php elseif ($hero_height === 'standard') : ?>
                    .hero-section,
                    .site-header {
                        min-height: 60vh;
                    }
                <?php elseif ($hero_height === 'tall') : ?>
                    .hero-section,
                    .site-header {
                        min-height: 75vh;
                    }
                <?php elseif ($hero_height === 'full') : ?>
                    .hero-section,
                    .site-header {
                        min-height: 100vh;
                    }
                <?php endif; ?>
            <?php endif; ?>
        </style>
        <?php
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
                'icon' => 'dashicons-star-filled',
                'category' => 'headers',
                'variants' => array('centered', 'split', 'video', 'minimal'),
                'settings' => array(
                    'heading' => array('type' => 'text', 'label' => __('Heading', 'campaign-office'), 'default' => __('Join Our Movement', 'campaign-office')),
                    'subheading' => array('type' => 'textarea', 'label' => __('Subheading', 'campaign-office'), 'default' => __('Building a better future for every citizen in our district.', 'campaign-office')),
                    'button_text' => array('type' => 'text', 'label' => __('Button Text', 'campaign-office'), 'default' => __('Volunteer Now', 'campaign-office')),
                    'bg_color' => array('type' => 'color', 'label' => __('Background Color', 'campaign-office'), 'default' => '#0073aa'),
                    'text_color' => array('type' => 'color', 'label' => __('Text Color', 'campaign-office'), 'default' => '#ffffff'),
                ),
            ),
            'cta' => array(
                'name' => __('Call-to-Action', 'campaign-office'),
                'icon' => 'dashicons-megaphone',
                'category' => 'conversion',
                'variants' => array('banner', 'card', 'modal', 'inline'),
                'settings' => array(
                    'title' => array('type' => 'text', 'label' => __('Title', 'campaign-office'), 'default' => __('Ready to Make a Difference?', 'campaign-office')),
                    'button_text' => array('type' => 'text', 'label' => __('Button Text', 'campaign-office'), 'default' => __('Donate Today', 'campaign-office')),
                    'style' => array('type' => 'select', 'label' => __('Style', 'campaign-office'), 'options' => array('primary', 'outline', 'dark'), 'default' => 'primary'),
                ),
            ),
            'stats' => array(
                'name' => __('Campaign Stats', 'campaign-office'),
                'icon' => 'dashicons-chart-bar',
                'category' => 'data',
                'variants' => array('counters', 'progress-bars', 'charts', 'badges'),
                'settings' => array(
                    'stat_1_label' => array('type' => 'text', 'label' => __('Stat 1 Label', 'campaign-office'), 'default' => __('Volunteers', 'campaign-office')),
                    'stat_1_value' => array('type' => 'text', 'label' => __('Stat 1 Value', 'campaign-office'), 'default' => '1,200+'),
                    'stat_2_label' => array('type' => 'text', 'label' => __('Stat 2 Label', 'campaign-office'), 'default' => __('Donors', 'campaign-office')),
                    'stat_2_value' => array('type' => 'text', 'label' => __('Stat 2 Value', 'campaign-office'), 'default' => '5,000+'),
                ),
            ),
            'testimonials' => array(
                'name' => __('Testimonials', 'campaign-office'),
                'icon' => 'dashicons-format-quote',
                'category' => 'social-proof',
                'variants' => array('carousel', 'grid', 'masonry', 'featured'),
                'settings' => array(
                    'quote' => array('type' => 'textarea', 'label' => __('Featured Quote', 'campaign-office'), 'default' => __('"The most passionate candidate I have ever supported!"', 'campaign-office')),
                    'author' => array('type' => 'text', 'label' => __('Author', 'campaign-office'), 'default' => __('Jane Doe, Local Teacher', 'campaign-office')),
                ),
            ),
            'donation' => array(
                'name' => __('Donation Form', 'campaign-office'),
                'icon' => 'dashicons-money-alt',
                'category' => 'conversion',
                'variants' => array('tiers', 'thermometer', 'quick-donate', 'recurring'),
                'settings' => array(
                    'goal_amount' => array('type' => 'text', 'label' => __('Goal Amount', 'campaign-office'), 'default' => '50000'),
                    'current_amount' => array('type' => 'text', 'label' => __('Current Amount', 'campaign-office'), 'default' => '25000'),
                    'form_title' => array('type' => 'text', 'label' => __('Form Title', 'campaign-office'), 'default' => __('Help Us Reach Our Goal', 'campaign-office')),
                ),
            ),
            'volunteer' => array(
                'name' => __('Volunteer Signup', 'campaign-office'),
                'icon' => 'dashicons-groups',
                'category' => 'conversion',
                'variants' => array('form', 'opportunities', 'impact', 'leaderboard'),
                'settings' => array(
                    'form_title' => array('type' => 'text', 'label' => __('Form Title', 'campaign-office'), 'default' => __('Join Our Campaign', 'campaign-office')),
                    'button_text' => array('type' => 'text', 'label' => __('Sign Up Text', 'campaign-office'), 'default' => __('Sign Up To Help', 'campaign-office')),
                ),
            ),
            'events' => array(
                'name' => __('Events Calendar', 'campaign-office'),
                'icon' => 'dashicons-calendar-alt',
                'category' => 'content',
                'variants' => array('list', 'grid', 'calendar', 'featured'),
                'settings' => array(
                    'max_events' => array('type' => 'number', 'label' => __('Max Events to Show', 'campaign-office'), 'default' => 3),
                    'show_map' => array('type' => 'checkbox', 'label' => __('Show Event Maps', 'campaign-office'), 'default' => true),
                ),
            ),
            'issues' => array(
                'name' => __('Policy Issues', 'campaign-office'),
                'icon' => 'dashicons-clipboard',
                'category' => 'content',
                'variants' => array('grid', 'accordion', 'tabs', 'timeline'),
                'settings' => array(
                    'num_issues' => array('type' => 'number', 'label' => __('Number of Issues', 'campaign-office'), 'default' => 4),
                    'columns' => array('type' => 'select', 'label' => __('Columns', 'campaign-office'), 'options' => array('2' => '2 Columns', '3' => '3 Columns', '4' => '4 Columns'), 'default' => '3'),
                ),
            ),
            'team' => array(
                'name' => __('Team Members', 'campaign-office'),
                'icon' => 'dashicons-businessperson',
                'category' => 'about',
                'variants' => array('grid', 'carousel', 'org-chart', 'cards'),
                'settings' => array(
                    'show_titles' => array('type' => 'checkbox', 'label' => __('Show Staff Titles', 'campaign-office'), 'default' => true),
                    'image_style' => array('type' => 'select', 'label' => __('Image Style', 'campaign-office'), 'options' => array('square' => 'Square', 'rounded' => 'Rounded', 'circle' => 'Circle'), 'default' => 'circle'),
                ),
            ),
            'timeline' => array(
                'name' => __('Campaign Timeline', 'campaign-office'),
                'icon' => 'dashicons-backup',
                'category' => 'content',
                'variants' => array('vertical', 'horizontal', 'roadmap', 'milestones'),
                'settings' => array(
                    'start_year' => array('type' => 'text', 'label' => __('Start Year', 'campaign-office'), 'default' => '2024'),
                ),
            ),
            'countdown' => array(
                'name' => __('Election Countdown', 'campaign-office'),
                'icon' => 'dashicons-clock',
                'category' => 'urgency',
                'variants' => array('minimal', 'cards', 'inline', 'bar'),
                'settings' => array(
                    'election_date' => array('type' => 'text', 'label' => __('Election Date', 'campaign-office'), 'default' => '2024-11-05'),
                    'label' => array('type' => 'text', 'label' => __('Label', 'campaign-office'), 'default' => __('Days Until Election', 'campaign-office')),
                ),
            ),
            'social-feed' => array(
                'name' => __('Social Media', 'campaign-office'),
                'icon' => 'dashicons-share',
                'category' => 'social',
                'variants' => array('instagram', 'twitter', 'facebook', 'combined'),
                'settings' => array(
                    'username' => array('type' => 'text', 'label' => __('Username', 'campaign-office'), 'default' => '@candidate'),
                    'count' => array('type' => 'number', 'label' => __('Post Count', 'campaign-office'), 'default' => 4),
                    'columns' => array('type' => 'select', 'label' => __('Columns', 'campaign-office'), 'options' => array('1' => '1 Column', '2' => '2 Columns', '3' => '3 Columns', '4' => '4 Columns'), 'default' => '4'),
                ),
            ),
            'communications' => array(
                'name' => __('Communications', 'campaign-office'),
                'icon' => 'dashicons-email-alt',
                'category' => 'forms',
                'variants' => array('subscribe', 'unsubscribe'),
                'settings' => array(
                    'title' => array('type' => 'text', 'label' => __('Title', 'campaign-office'), 'default' => __('Join Our Movement', 'campaign-office')),
                    'type' => array('type' => 'select', 'label' => __('Input Type', 'campaign-office'), 'options' => array('both' => 'Email & SMS', 'email' => 'Email Only', 'sms' => 'SMS Only'), 'default' => 'both'),
                    'zip_field' => array('type' => 'checkbox', 'label' => __('Show ZIP Field', 'campaign-office'), 'default' => true),
                ),
            ),
        );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        $this->studio_page_hook = add_menu_page(
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

        $this->templates_page_hook = add_submenu_page(
            'cp-design-studio',
            __('Templates', 'campaign-office'),
            __('Templates', 'campaign-office'),
            'edit_pages',
            'cp-design-templates',
            array($this, 'render_templates_page')
        );

        $this->styles_page_hook = add_submenu_page(
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

        // Get saved page settings
        $page_settings = $post_id ? get_post_meta($post_id, '_cp_page_settings', true) : array();
        $style_settings = $post_id ? get_post_meta($post_id, '_cp_style_settings', true) : array();

        // Set defaults
        $bg_color = $page_settings['bg_color'] ?? '#ffffff';
        $hero_height = $page_settings['hero_height'] ?? 'standard';
        $container_width = $page_settings['container_width'] ?? '1200';
        $border_radius = $page_settings['border_radius'] ?? '4';
        $custom_css = $design_data ? $design_data->custom_css : '';

        $base_font_size = $style_settings['base_font_size'] ?? '16';
        $heading_weight = $style_settings['heading_weight'] ?? '600';
        $line_height = $style_settings['line_height'] ?? '1.5';
        $primary_color = $style_settings['primary_color'] ?? '#0073aa';
        $secondary_color = $style_settings['secondary_color'] ?? '#005a87';
        $accent_color = $style_settings['accent_color'] ?? '#d63638';
        $text_color = $style_settings['text_color'] ?? '#333333';
        $section_padding = $style_settings['section_padding'] ?? 'standard';
        $element_spacing = $style_settings['element_spacing'] ?? 'standard';
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

            <script>
            jQuery(document).ready(function($) {
                // Load saved settings into inputs
                $('#cp-page-bg-color').val('<?php echo esc_js($bg_color); ?>').wpColorPicker('color', '<?php echo esc_js($bg_color); ?>');
                $('#cp-hero-height').val('<?php echo esc_js($hero_height); ?>');
                $('#cp-container-width').val('<?php echo esc_js($container_width); ?>');
                $('#cp-border-radius').val('<?php echo esc_js($border_radius); ?>');

                $('#cp-base-font-size').val('<?php echo esc_js($base_font_size); ?>');
                $('#cp-heading-weight').val('<?php echo esc_js($heading_weight); ?>');
                $('#cp-line-height').val('<?php echo esc_js($line_height); ?>');
                $('#cp-primary-color').val('<?php echo esc_js($primary_color); ?>').wpColorPicker('color', '<?php echo esc_js($primary_color); ?>');
                $('#cp-secondary-color').val('<?php echo esc_js($secondary_color); ?>').wpColorPicker('color', '<?php echo esc_js($secondary_color); ?>');
                $('#cp-accent-color').val('<?php echo esc_js($accent_color); ?>').wpColorPicker('color', '<?php echo esc_js($accent_color); ?>');
                $('#cp-text-color').val('<?php echo esc_js($text_color); ?>').wpColorPicker('color', '<?php echo esc_js($text_color); ?>');
                $('#cp-section-padding').val('<?php echo esc_js($section_padding); ?>');
                $('#cp-element-spacing').val('<?php echo esc_js($element_spacing); ?>');
            });
            </script>

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
                                        <div class="cp-component-icon"><span class="dashicons <?php echo esc_attr($component['icon']); ?>"></span></div>
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
                            <h3><?php esc_html_e('Typography Settings', 'campaign-office'); ?></h3>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Base Font Size', 'campaign-office'); ?></label>
                                <select class="cp-input" id="cp-base-font-size">
                                    <option value="14"><?php esc_html_e('Small (14px)', 'campaign-office'); ?></option>
                                    <option value="16" selected><?php esc_html_e('Standard (16px)', 'campaign-office'); ?></option>
                                    <option value="18"><?php esc_html_e('Large (18px)', 'campaign-office'); ?></option>
                                    <option value="20"><?php esc_html_e('Extra Large (20px)', 'campaign-office'); ?></option>
                                </select>
                            </div>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Heading Font Weight', 'campaign-office'); ?></label>
                                <select class="cp-input" id="cp-heading-weight">
                                    <option value="400"><?php esc_html_e('Normal (400)', 'campaign-office'); ?></option>
                                    <option value="500"><?php esc_html_e('Medium (500)', 'campaign-office'); ?></option>
                                    <option value="600" selected><?php esc_html_e('Semibold (600)', 'campaign-office'); ?></option>
                                    <option value="700"><?php esc_html_e('Bold (700)', 'campaign-office'); ?></option>
                                    <option value="800"><?php esc_html_e('Extra Bold (800)', 'campaign-office'); ?></option>
                                </select>
                            </div>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Line Height', 'campaign-office'); ?></label>
                                <select class="cp-input" id="cp-line-height">
                                    <option value="1.3"><?php esc_html_e('Compact (1.3)', 'campaign-office'); ?></option>
                                    <option value="1.5" selected><?php esc_html_e('Standard (1.5)', 'campaign-office'); ?></option>
                                    <option value="1.7"><?php esc_html_e('Relaxed (1.7)', 'campaign-office'); ?></option>
                                    <option value="1.9"><?php esc_html_e('Loose (1.9)', 'campaign-office'); ?></option>
                                </select>
                            </div>

                            <h3><?php esc_html_e('Color Settings', 'campaign-office'); ?></h3>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Primary Color', 'campaign-office'); ?></label>
                                <input type="text" class="cp-input cp-color-picker" id="cp-primary-color" value="#0073aa">
                            </div>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Secondary Color', 'campaign-office'); ?></label>
                                <input type="text" class="cp-input cp-color-picker" id="cp-secondary-color" value="#005a87">
                            </div>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Accent Color', 'campaign-office'); ?></label>
                                <input type="text" class="cp-input cp-color-picker" id="cp-accent-color" value="#d63638">
                            </div>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Text Color', 'campaign-office'); ?></label>
                                <input type="text" class="cp-input cp-color-picker" id="cp-text-color" value="#333333">
                            </div>

                            <h3><?php esc_html_e('Spacing Settings', 'campaign-office'); ?></h3>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Section Padding', 'campaign-office'); ?></label>
                                <select class="cp-input" id="cp-section-padding">
                                    <option value="compact"><?php esc_html_e('Compact (3rem)', 'campaign-office'); ?></option>
                                    <option value="standard" selected><?php esc_html_e('Standard (4rem)', 'campaign-office'); ?></option>
                                    <option value="spacious"><?php esc_html_e('Spacious (6rem)', 'campaign-office'); ?></option>
                                    <option value="extra-spacious"><?php esc_html_e('Extra Spacious (8rem)', 'campaign-office'); ?></option>
                                </select>
                            </div>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Element Spacing', 'campaign-office'); ?></label>
                                <select class="cp-input" id="cp-element-spacing">
                                    <option value="tight"><?php esc_html_e('Tight (0.5rem)', 'campaign-office'); ?></option>
                                    <option value="standard" selected><?php esc_html_e('Standard (1rem)', 'campaign-office'); ?></option>
                                    <option value="relaxed"><?php esc_html_e('Relaxed (1.5rem)', 'campaign-office'); ?></option>
                                </select>
                            </div>

                            <button class="button button-primary" id="cp-save-style-settings">
                                <span class="dashicons dashicons-saved"></span>
                                <?php esc_html_e('Save Style Settings', 'campaign-office'); ?>
                            </button>
                        </div>

                        <!-- Settings Tab -->
                        <div class="cp-tab-content" data-tab-content="settings">
                            <h3><?php esc_html_e('Page Settings', 'campaign-office'); ?></h3>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Page Background Color', 'campaign-office'); ?></label>
                                <input type="text" class="cp-input cp-color-picker" id="cp-page-bg-color" value="#ffffff">
                            </div>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Hero Section Height', 'campaign-office'); ?></label>
                                <select class="cp-input" id="cp-hero-height">
                                    <option value="standard"><?php esc_html_e('Standard (60vh)', 'campaign-office'); ?></option>
                                    <option value="tall"><?php esc_html_e('Tall (75vh)', 'campaign-office'); ?></option>
                                    <option value="short"><?php esc_html_e('Short (45vh)', 'campaign-office'); ?></option>
                                    <option value="full"><?php esc_html_e('Full Screen (100vh)', 'campaign-office'); ?></option>
                                </select>
                            </div>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Container Width', 'campaign-office'); ?></label>
                                <select class="cp-input" id="cp-container-width">
                                    <option value="1140"><?php esc_html_e('Narrow (1140px)', 'campaign-office'); ?></option>
                                    <option value="1200" selected><?php esc_html_e('Standard (1200px)', 'campaign-office'); ?></option>
                                    <option value="1320"><?php esc_html_e('Wide (1320px)', 'campaign-office'); ?></option>
                                    <option value="1440"><?php esc_html_e('Extra Wide (1440px)', 'campaign-office'); ?></option>
                                </select>
                            </div>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Border Radius', 'campaign-office'); ?></label>
                                <select class="cp-input" id="cp-border-radius">
                                    <option value="0"><?php esc_html_e('Sharp (0px)', 'campaign-office'); ?></option>
                                    <option value="4" selected><?php esc_html_e('Rounded (4px)', 'campaign-office'); ?></option>
                                    <option value="8"><?php esc_html_e('More Rounded (8px)', 'campaign-office'); ?></option>
                                    <option value="12"><?php esc_html_e('Very Rounded (12px)', 'campaign-office'); ?></option>
                                </select>
                            </div>
                            <div class="cp-setting-group">
                                <label><?php esc_html_e('Custom CSS', 'campaign-office'); ?></label>
                                <textarea class="cp-input" id="cp-custom-css" rows="10" placeholder="/* Add custom CSS here */"></textarea>
                                <p class="description"><?php esc_html_e('Custom CSS will be applied to this page only.', 'campaign-office'); ?></p>
                            </div>
                            <button class="button button-primary" id="cp-save-page-settings">
                                <span class="dashicons dashicons-saved"></span>
                                <?php esc_html_e('Save Page Settings', 'campaign-office'); ?>
                            </button>
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
                        <p class="description"><?php esc_html_e('Drag components from the sidebar to start designing your page', 'campaign-office'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        </div>
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
        $settings = $_POST['settings'] ?? array();

        // Generate component HTML based on type and variant
        $html = $this->generate_component_html($component_type, $variant, $settings);

        wp_send_json_success(array(
            'html' => $html,
            'component' => $component_type,
            'variant' => $variant,
        ));
    }

    public function ajax_get_component_properties() {
        check_ajax_referer('cp_get_component');

        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => __('Permission denied', 'campaign-office')));
        }

        $component_type = sanitize_text_field($_POST['component_type'] ?? '');

        if (!isset($this->components[$component_type])) {
            wp_send_json_error(array('message' => __('Invalid component type', 'campaign-office')));
        }

        wp_send_json_success(array(
            'settings' => $this->components[$component_type]['settings'] ?? array(),
            'variants' => $this->components[$component_type]['variants'] ?? array(),
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
    private function generate_component_html($type, $variant, $settings = array()) {
        $component_info = $this->components[$type] ?? null;

        if (!$component_info) {
            return '';
        }

        // Merge defaults if settings is empty
        if (empty($settings) && isset($component_info['settings'])) {
            foreach ($component_info['settings'] as $key => $schema) {
                $settings[$key] = $schema['default'];
            }
        }

        // Specific rendering logic for Hero component (as an example)
        if ($type === 'hero') {
            $heading = $settings['heading'] ?? '';
            $subheading = $settings['subheading'] ?? '';
            $bg_color = $settings['bg_color'] ?? '#0073aa';
            $text_color = $settings['text_color'] ?? '#ffffff';
            $btn_text = $settings['button_text'] ?? '';

            return sprintf(
                '<div class="cp-component cp-component-hero cp-variant-%s" style="background-color: %s; color: %s; padding: 4rem 2rem; text-align: center;">
                    <h1 style="font-size: 3rem; margin-bottom: 1rem;">%s</h1>
                    <p style="font-size: 1.25rem; margin-bottom: 2rem;">%s</p>
                    <button class="button button-primary button-large">%s</button>
                </div>',
                esc_attr($variant),
                esc_attr($bg_color),
                esc_attr($text_color),
                esc_html($heading),
                esc_html($subheading),
                esc_html($btn_text)
            );
        }

        // Communications component logic
        if ($type === 'communications') {
            $title = $settings['title'] ?? __('Join Our Movement', 'campaign-office');
            $input_type = $settings['type'] ?? 'both';
            $show_zip = $settings['zip_field'] ?? true;

            if ($variant === 'unsubscribe') {
                return sprintf(
                    '<div class="cp-component cp-communications cp-variant-unsubscribe">
                        <div class="cp-unsubscribe-wrapper">
                            <h3>%s</h3>
                            <p>%s</p>
                            <form class="cp-unsubscribe-form">
                                <input type="email" name="email" placeholder="%s" required class="cp-input">
                                <button type="submit" class="cp-button">%s</button>
                            </form>
                        </div>
                    </div>',
                    esc_html($title),
                    esc_html__('To unsubscribe from our communications, please enter your email address below.', 'campaign-office'),
                    esc_attr__('Email Address', 'campaign-office'),
                    esc_html__('Unsubscribe', 'campaign-office')
                );
            }

            // Default: subscribe
            $zip_html = $show_zip ? sprintf('<div class="form-field"><input type="text" name="zip" placeholder="%s" class="cp-input"></div>', esc_attr__('ZIP Code', 'campaign-office')) : '';
            $email_html = in_array($input_type, array('both', 'email')) ? sprintf('<div class="form-field"><input type="email" name="email" placeholder="%s" required class="cp-input"></div>', esc_attr__('Email Address', 'campaign-office')) : '';
            $phone_html = in_array($input_type, array('both', 'sms')) ? sprintf('<div class="form-field"><input type="tel" name="phone" placeholder="%s" class="cp-input"></div>', esc_attr__('Phone Number', 'campaign-office')) : '';

            return sprintf(
                '<div class="cp-component cp-communications cp-variant-subscribe">
                    <div class="cp-subscribe-form-wrapper">
                        <h3>%s</h3>
                        <form class="cp-subscribe-form">
                            %s
                            <div class="form-row">
                                <input type="text" name="first_name" placeholder="%s" required class="cp-input">
                                <input type="text" name="last_name" placeholder="%s" required class="cp-input">
                            </div>
                            %s
                            %s
                            %s
                            <div class="form-actions">
                                <button type="submit" class="cp-button cp-button-primary">%s</button>
                            </div>
                            <div class="cp-form-message" style="display:none;"></div>
                        </form>
                    </div>
                </div>',
                esc_html($title),
                wp_nonce_field('cp_subscribe', 'cp_subscribe_nonce', false, false),
                esc_attr__('First Name', 'campaign-office'),
                esc_attr__('Last Name', 'campaign-office'),
                $email_html,
                $phone_html,
                $zip_html,
                esc_html__('Subscribe', 'campaign-office')
            );
        }

        // Generic placeholder
        $html = sprintf(
            '<div class="cp-component cp-component-%s cp-variant-%s" style="padding: 2rem; border: 1px dashed #ccc; background: #fafafa; border-radius: 8px;">
                <div class="cp-component-icon" style="font-size: 2rem; margin-bottom: 1rem;">%s</div>
                <h3 class="cp-component-title">%s</h3>
                <p class="cp-component-description">%s variant</p>
                <div class="cp-component-content">
                    <ul style="list-style: disc; padding-left: 1.5rem;">',
            esc_attr($type),
            esc_attr($variant),
            $component_info['icon'],
            esc_html($component_info['name']),
            esc_html(ucfirst($variant))
        );

        foreach ($settings as $key => $value) {
            $html .= sprintf('<li><strong>%s:</strong> %s</li>', esc_html(ucfirst(str_replace('_', ' ', $key))), esc_html($value));
        }

        $html .= '    </ul>
                </div>
            </div>';

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
     * AJAX handler for saving page settings
     */
    public function ajax_save_page_settings() {
        check_ajax_referer('cp_save_design');

        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => __('Permission denied', 'campaign-office')));
        }

        $post_id = intval($_POST['post_id']);

        if (!$post_id) {
            wp_send_json_error(array('message' => __('Invalid page ID', 'campaign-office')));
        }

        $settings = array(
            'bg_color' => sanitize_hex_color($_POST['bg_color'] ?? '#ffffff'),
            'hero_height' => sanitize_text_field($_POST['hero_height'] ?? 'standard'),
            'container_width' => sanitize_text_field($_POST['container_width'] ?? '1200'),
            'border_radius' => sanitize_text_field($_POST['border_radius'] ?? '4'),
        );

        update_post_meta($post_id, '_cp_page_settings', $settings);

        wp_send_json_success(array('message' => __('Page settings saved successfully', 'campaign-office')));
    }

    /**
     * AJAX handler for saving style settings
     */
    public function ajax_save_style_settings() {
        check_ajax_referer('cp_save_design');

        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => __('Permission denied', 'campaign-office')));
        }

        $post_id = intval($_POST['post_id']);

        if (!$post_id) {
            wp_send_json_error(array('message' => __('Invalid page ID', 'campaign-office')));
        }

        $settings = array(
            'base_font_size' => sanitize_text_field($_POST['base_font_size'] ?? '16'),
            'heading_weight' => sanitize_text_field($_POST['heading_weight'] ?? '600'),
            'line_height' => sanitize_text_field($_POST['line_height'] ?? '1.5'),
            'primary_color' => sanitize_hex_color($_POST['primary_color'] ?? '#0073aa'),
            'secondary_color' => sanitize_hex_color($_POST['secondary_color'] ?? '#005a87'),
            'accent_color' => sanitize_hex_color($_POST['accent_color'] ?? '#d63638'),
            'text_color' => sanitize_hex_color($_POST['text_color'] ?? '#333333'),
            'section_padding' => sanitize_text_field($_POST['section_padding'] ?? 'standard'),
            'element_spacing' => sanitize_text_field($_POST['element_spacing'] ?? 'standard'),
        );

        update_post_meta($post_id, '_cp_style_settings', $settings);

        wp_send_json_success(array('message' => __('Style settings saved successfully', 'campaign-office')));
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
        $allowed_hooks = array(
             $this->studio_page_hook,
             $this->templates_page_hook,
             $this->styles_page_hook,
             'toplevel_page_cp-design-studio',
             'cp-design-studio_page_cp-design-templates',
             'cp-design-studio_page_cp-global-styles'
        );
        
        // Filter out false values
        $allowed_hooks = array_filter($allowed_hooks);

        if (!in_array($hook, $allowed_hooks) && strpos($hook, 'cp-design-studio') === false) {
            return;
        }

        // Enqueue jQuery (required for all Design Studio functionality)
        wp_enqueue_script('jquery');

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        // Enqueue Design Studio assets
        wp_enqueue_style(
            'cp-design-studio',
            CAMPAIGNPRESS_ASSETS_URI . '/css/design-studio.css',
            array('wp-color-picker'),
            CAMPAIGNPRESS_VERSION
        );

        wp_enqueue_script(
            'cp-design-studio',
            CAMPAIGNPRESS_ASSETS_URI . '/js/design-studio.js',
            array('jquery', 'wp-color-picker'),
            CAMPAIGNPRESS_VERSION,
            true
        );

        // Localize data
        wp_localize_script('cp-design-studio', 'cpDesignStudio', array(
            'nonces' => array(
                'save_design' => wp_create_nonce('cp_save_design'),
                'load_design' => wp_create_nonce('cp_load_design'),
                'apply_template' => wp_create_nonce('cp_apply_template'),
                'get_component' => wp_create_nonce('cp_get_component'),
            ),
            'i18n' => array(
                'saving' => __('Saving...', 'campaign-office'),
                'saved' => __('Saved!', 'campaign-office'),
                'save_design' => __('Save Design', 'campaign-office'),
                'delete_confirm' => __('Delete this component?', 'campaign-office'),
                'clear_confirm' => __('Clear all components?', 'campaign-office'),
                'start_building' => __('Start Building', 'campaign-office'),
                'drag_desc' => __('Drag components from the sidebar to start designing your page', 'campaign-office'),
                'select_page_first' => __('Please select a page first', 'campaign-office'),
                'applying' => __('Applying...', 'campaign-office'),
                'template_applied' => __('Template applied successfully! Open the Design Studio to customize it.', 'campaign-office'),
                'error_applying' => __('Error applying template', 'campaign-office'),
                'error_saving' => __('Error saving design', 'campaign-office'),
            )
        ));
    }
}

// Initialize Design Studio
new CP_Campaign_Design_Studio();
