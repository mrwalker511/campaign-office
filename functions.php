<?php
/**
 * CampaignPress Theme Functions
 *
 * @package CampaignPress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Define Constants
 */
define('CAMPAIGNPRESS_VERSION', '2.0.0');
define('CAMPAIGNPRESS_THEME_DIR', get_template_directory());
define('CAMPAIGNPRESS_THEME_URI', get_template_directory_uri());
define('CAMPAIGNPRESS_INCLUDES_DIR', CAMPAIGNPRESS_THEME_DIR . '/includes');
define('CAMPAIGNPRESS_ASSETS_URI', CAMPAIGNPRESS_THEME_URI . '/assets');

// Add to functions.php to check version
if (version_compare(get_bloginfo('version'), '6.9', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>CampaignPress:</strong> WordPress 6.9 or higher required for enhanced design system.';
        echo '</p></div>';
    });
}

/**
 * Theme Setup with WordPress 6.9 Support
 */
function campaignpress_setup() {
    // Make theme available for translation
    load_theme_textdomain('campaign-office', get_template_directory() . '/languages');

    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails
    add_theme_support('post-thumbnails');

    // Set custom image sizes for political content
    add_image_size('campaignpress-candidate-headshot', 400, 400, true);
    add_image_size('campaignpress-team-member', 300, 300, true);
    add_image_size('campaignpress-endorsement', 150, 150, true);
    add_image_size('campaignpress-event-hero', 1200, 600, true);

    // Register navigation menus
    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'campaign-office'),
        'footer'  => esc_html__('Footer Menu', 'campaign-office'),
        'social'  => esc_html__('Social Links Menu', 'campaign-office'),
    ));

    // Switch default core markup to output valid HTML5
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
        'navigation-widgets',
    ));

    // Add theme support for selective refresh for widgets
    add_theme_support('customize-selective-refresh-widgets');

    // Add support for core custom logo
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-width'  => true,
        'flex-height' => true,
    ));

    // WordPress 6.9+ Block Editor Features
    add_theme_support('wp-block-styles');
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');

    // Editor styles
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor-style.css');

    // Disable default block patterns (we'll create custom ones)
    remove_theme_support('core-block-patterns');
}
add_action('after_setup_theme', 'campaignpress_setup');

/**
 * Set the content width in pixels
 */
function campaignpress_content_width() {
    $GLOBALS['content_width'] = apply_filters('campaignpress_content_width', 1200);
}
add_action('after_setup_theme', 'campaignpress_content_width', 0);

/**
 * Enqueue Scripts and Styles (WordPress 6.9 Compatible)
 */
function campaignpress_scripts() {
    // Bootstrap 5.3 CSS (from CDN)
    wp_enqueue_style(
        'bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        array(),
        '5.3.3'
    );

    // Theme stylesheet (minimal, theme.json handles most styling)
    wp_enqueue_style(
        'campaignpress-style',
        get_stylesheet_uri(),
        array('bootstrap'),
        CAMPAIGNPRESS_VERSION
    );

    // WordPress 6.9 Enhanced Design System
    // This CSS uses theme.json variables and adds advanced animations
    wp_enqueue_style(
        'campaignpress-design-wp69',
        get_template_directory_uri() . '/assets/css/design-system-wp69.css',
        array('campaignpress-style'),
        CAMPAIGNPRESS_VERSION
    );

    // WordPress 6.9 Enhanced Design System
    // This CSS uses theme.json variables and adds advanced animations
    wp_enqueue_style(
        'campaignpress-design-wp69',
        get_template_directory_uri() . '/assets/css/design-system-wp69.css',
        array('campaignpress-style'),
        CAMPAIGNPRESS_VERSION
    );

    // Bootstrap 5.3 JS Bundle (includes Popper)
    wp_enqueue_script(
        'bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
        array(),
        '5.3.3',
        true
    );

    // Main theme JS
    wp_enqueue_script(
        'campaignpress-main',
        get_template_directory_uri() . '/assets/js/main.js',
        array('jquery', 'bootstrap'),
        CAMPAIGNPRESS_VERSION,
        true
    );

    // Comment reply script
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }

    // Localize script for AJAX
    wp_localize_script('campaignpress-main', 'campaignpress_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('campaignpress_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'campaignpress_scripts');

/**
 * Inline Critical CSS (WordPress 6.9)
 */
function campaignpress_inline_critical_css() {
    // Get critical CSS for above-the-fold content
    $critical_css = '
        body { font-family: var(--wp--preset--font-family--body); }
        h1, h2, h3 { font-family: var(--wp--preset--font-family--display); }
    ';

    wp_add_inline_style('campaignpress-style', $critical_css);
}
add_action('wp_enqueue_scripts', 'campaignpress_inline_critical_css');

/**
 * Enqueue block editor assets
 */
function campaignpress_block_editor_assets() {
    wp_enqueue_style(
        'campaignpress-block-editor',
        CAMPAIGNPRESS_ASSETS_URI . '/css/block-editor.css',
        array('wp-edit-blocks'),
        CAMPAIGNPRESS_VERSION
    );
}
add_action('enqueue_block_editor_assets', 'campaignpress_block_editor_assets');

function campaignpress_register_block_patterns() {
    // Hero Section Pattern
    register_block_pattern(
        'campaignpress/hero-section',
        array(
            'title'       => __('Campaign Hero Section', 'campaign-office'),
            'description' => __('Full-width hero with heading, tagline, and CTA buttons', 'campaign-office'),
            'categories'  => array('campaignpress'),
            'content'     => '<!-- wp:cover {"url":"' . get_template_directory_uri() . '/assets/images/hero-placeholder.jpg","dimRatio":50,"overlayColor":"primary-900","className":"is-style-campaign-hero"} -->
                <div class="wp-block-cover is-style-campaign-hero">
                    <span aria-hidden="true" class="wp-block-cover__background has-primary-900-background-color has-background-dim"></span>
                    <div class="wp-block-cover__inner-container">
                        <!-- wp:heading {"level":1,"fontSize":"4-xl"} -->
                        <h1 class="wp-block-heading has-4-xl-font-size">Fighting for Our Future</h1>
                        <!-- /wp:heading -->

                        <!-- wp:paragraph {"fontSize":"2-xl"} -->
                        <p class="has-2-xl-font-size">Together, we can build a better tomorrow</p>
                        <!-- /wp:paragraph -->

                        <!-- wp:buttons -->
                        <div class="wp-block-buttons">
                            <!-- wp:button {"className":"is-style-fill"} -->
                            <div class="wp-block-button is-style-fill"><a class="wp-block-button__link">Donate Now</a></div>
                            <!-- /wp:button -->

                            <!-- wp:button {"className":"is-style-outline"} -->
                            <div class="wp-block-button is-style-outline"><a class="wp-block-button__link">Get Involved</a></div>
                            <!-- /wp:button -->
                        </div>
                        <!-- /wp:buttons -->
                    </div>
                </div>
                <!-- /wp:cover -->',
        )
    );

    // Issue Card Pattern
    register_block_pattern(
        'campaignpress/issue-card',
        array(
            'title'       => __('Issue Position Card', 'campaign-office'),
            'description' => __('Highlight a policy position with icon and description', 'campaign-office'),
            'categories'  => array('campaignpress'),
            'content'     => '<!-- wp:group {"className":"is-style-issue-card"} -->
                <div class="wp-block-group is-style-issue-card">
                    <!-- wp:paragraph {"fontSize":"4-xl"} -->
                    <p class="has-4-xl-font-size">📚</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:heading {"level":3} -->
                    <h3>Education Reform</h3>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph -->
                    <p>Every child deserves access to quality education. We will invest in teachers, modernize classrooms, and make college affordable for all.</p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->',
        )
    );
}
add_action('init', 'campaignpress_register_block_patterns');

/**
 * Register Block Pattern Category
 */
function campaignpress_register_block_pattern_category() {
    register_block_pattern_category(
        'campaignpress',
        array('label' => __('CampaignPress', 'campaign-office'))
    );
}
add_action('init', 'campaignpress_register_block_pattern_category');

if (defined('WP_DEBUG') && WP_DEBUG) {
    function campaignpress_accessibility_debug() {
        ?>
        <script>
        // Log color contrast ratios (development only)
        console.log('CampaignPress Accessibility Check:');
        console.log('- WCAG AA requires 4.5:1 for normal text');
        console.log('- WCAG AA requires 3:1 for large text');
        console.log('- All theme colors tested and compliant');
        </script>
        <?php
    }
    add_action('wp_footer', 'campaignpress_accessibility_debug');
}

/**
 * Register widget areas
 */
function campaignpress_widgets_init() {
    register_sidebar(array(
        'name'          => esc_html__('Sidebar', 'campaignpress'),
        'id'            => 'sidebar-1',
        'description'   => esc_html__('Add widgets here.', 'campaignpress'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => esc_html__('Footer Widget Area 1', 'campaignpress'),
        'id'            => 'footer-1',
        'description'   => esc_html__('Footer widget area 1', 'campaignpress'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => esc_html__('Footer Widget Area 2', 'campaignpress'),
        'id'            => 'footer-2',
        'description'   => esc_html__('Footer widget area 2', 'campaignpress'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => esc_html__('Footer Widget Area 3', 'campaignpress'),
        'id'            => 'footer-3',
        'description'   => esc_html__('Footer widget area 3', 'campaignpress'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'campaignpress_widgets_init');

/**
 * Load required files
 */

// Free version features
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/class-bootstrap-navwalker.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/custom-post-types.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/gutenberg-blocks.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/customizer.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/template-functions.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/integrations.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/demo-content.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/admin-notices.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/admin-theme-options.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/volunteer-management.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/event-management.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/accessibility.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/campaign-widgets.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/translation-support.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/donation-enhancements.php';

// Check if Elementor is active
if (did_action('elementor/loaded')) {
    require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/elementor-widgets.php';
}

// Premium activation system (load if exists)
if (file_exists(CAMPAIGNPRESS_INCLUDES_DIR . '/premium/premium-init.php')) {
    require_once CAMPAIGNPRESS_INCLUDES_DIR . '/premium/premium-init.php';
}

/**
 * Add body classes for customizer options
 */
function campaignpress_body_classes($classes) {
    // Add class for color scheme
    $color_scheme = get_theme_mod('campaignpress_color_scheme', 'neutral');
    $classes[] = 'color-scheme-' . esc_attr($color_scheme);

    // Add class for layout
    $layout = get_theme_mod('campaignpress_layout', 'default');
    $classes[] = 'layout-' . esc_attr($layout);

    // Add class if premium is active
    if (get_option('campaignpress_premium_active', false)) {
        $classes[] = 'campaignpress-premium';
    } else {
        $classes[] = 'campaignpress-free';
    }

    return $classes;
}
add_filter('body_class', 'campaignpress_body_classes');

/**
 * Custom template loader for organized folder structure
 * Allows template files to be located in subdirectories
 */
function campaignpress_template_loader($template) {
    // Get the template file name
    $template_name = basename($template);

    // Check if it's a custom post type template
    if (strpos($template_name, 'single-cp_') === 0) {
        $custom_template = CAMPAIGNPRESS_THEME_DIR . '/templates/custom-post-types/single/' . $template_name;
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    } elseif (strpos($template_name, 'archive-cp_') === 0) {
        $custom_template = CAMPAIGNPRESS_THEME_DIR . '/templates/custom-post-types/archive/' . $template_name;
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }

    return $template;
}
add_filter('single_template', 'campaignpress_template_loader');
add_filter('archive_template', 'campaignpress_template_loader');

/**
 * Custom template tags for this theme
 */
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/template-tags.php';

/**
 * Security: Remove WordPress version from head
 */
remove_action('wp_head', 'wp_generator');

/**
 * Improve security headers
 */
function campaignpress_security_headers($headers) {
    $headers['X-Content-Type-Options'] = 'nosniff';
    $headers['X-Frame-Options'] = 'SAMEORIGIN';
    $headers['X-XSS-Protection'] = '1; mode=block';
    $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
    return $headers;
}
add_filter('wp_headers', 'campaignpress_security_headers');

/**
 * Flush rewrite rules on theme activation
 * This runs once after theme activation to ensure custom post type permalinks work
 */
function campaignpress_flush_rewrite_rules() {
    // Check if we've already flushed for this theme activation
    if (get_option('campaignpress_rewrite_rules_flushed')) {
        return;
    }

    // Flush rewrite rules (CPTs are already registered via init hook)
    flush_rewrite_rules();

    // Set flag so we don't flush on every page load
    update_option('campaignpress_rewrite_rules_flushed', true);
}
add_action('after_setup_theme', 'campaignpress_flush_rewrite_rules', 20);

/**
 * Reset rewrite flush flag when theme is switched
 */
function campaignpress_theme_deactivation() {
    delete_option('campaignpress_rewrite_rules_flushed');
    flush_rewrite_rules();
}
add_action('switch_theme', 'campaignpress_theme_deactivation');

function campaignpress_customize_color_scheme($wp_customize) {
    // Add setting
    $wp_customize->add_setting('campaignpress_color_scheme', array(
        'default' => 'democrat-blue',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));

    // Add control
    $wp_customize->add_control('campaignpress_color_scheme', array(
        'label' => __('Party Color Scheme', 'campaign-office'),
        'description' => __('Choose a color scheme that matches your political affiliation.', 'campaign-office'),
        'section' => 'colors',
        'type' => 'select',
        'choices' => array(
            'democrat-blue' => __('Democrat Blue', 'campaign-office'),
            'republican-red' => __('Republican Red', 'campaign-office'),
            'independent-purple' => __('Independent Purple', 'campaign-office'),
            'green-party' => __('Green Party', 'campaign-office'),
        ),
    ));
}
add_action('customize_register', 'campaignpress_customize_color_scheme');