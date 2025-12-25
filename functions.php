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
 * Development Mode (Optional)
 *
 * To enable premium features during development without a license key,
 * add this to your wp-config.php:
 *
 *   define('CAMPAIGNPRESS_DEV_MODE', true);
 *
 * NEVER set this to true in production or distributed versions.
 */

/**
 * Define Constants
 */
define('CAMPAIGNPRESS_VERSION', '2.0.0');
define('CAMPAIGNPRESS_THEME_DIR', get_template_directory());
define('CAMPAIGNPRESS_THEME_URI', get_template_directory_uri());
define('CAMPAIGNPRESS_INCLUDES_DIR', CAMPAIGNPRESS_THEME_DIR . '/includes');
define('CAMPAIGNPRESS_ASSETS_URI', CAMPAIGNPRESS_THEME_URI . '/assets');

/**
 * Theme Setup
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

    // Navigation is handled by the Navigation block in block themes
    // Menus can be created in Appearance > Menus and assigned via the Navigation block

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

    // WordPress 6.9+ Block Theme Features
    // All block styles, wide alignment, responsive embeds, and editor styles
    // are now controlled via theme.json - no add_theme_support() calls needed

    // Editor stylesheet for Site Editor
    add_editor_style('assets/css/design-system-wp69.css');

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
 * Enqueue scripts and styles
 */
function campaignpress_scripts() {

    // Self-hosted fonts (GDPR compliant)
    // Fonts are preloaded in wp_head via includes/free/font-preconnect.php
    // @font-face declarations are in theme.json

    // Theme stylesheet (minimal, theme.json handles most styling)
    wp_enqueue_style(
        'campaignpress-style',
        get_stylesheet_uri(),
        array(),
        CAMPAIGNPRESS_VERSION
    );

    // WordPress 6.9+ Enhanced Design System
    // This CSS uses theme.json variables and adds advanced animations
    // Future-proof for WordPress 6.9 and beyond
    wp_enqueue_style(
        'campaignpress-design-wp69',
        get_template_directory_uri() . '/assets/css/design-system-wp69.css',
        array('campaignpress-style'),
        CAMPAIGNPRESS_VERSION
    );

    // Bootstrap 5 CSS (self-hosted for performance and reliability)
    wp_enqueue_style(
        'bootstrap',
        get_template_directory_uri() . '/assets/vendor/bootstrap/bootstrap.min.css',
        array(),
        '5.3.0'
    );

    // Bootstrap 5 JS Bundle (self-hosted, includes Popper)
    wp_enqueue_script(
        'bootstrap-bundle',
        get_template_directory_uri() . '/assets/vendor/bootstrap/bootstrap.bundle.min.js',
        array(),
        '5.3.0',
        true
    );

    // Ensure jQuery is loaded (WordPress core)
    wp_enqueue_script('jquery');
    
    // Main theme JS
    wp_enqueue_script(
        'campaignpress-main',
        get_template_directory_uri() . '/assets/js/main.js',
        array('jquery'), // Depends on jQuery
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
 * Disable Dashicons on frontend for non-admin users
 * Saves 45KB on every page load
 */
function campaignpress_disable_dashicons() {
    if (!is_admin() && !is_user_logged_in()) {
        wp_deregister_style('dashicons');
    }
}
add_action('wp_enqueue_scripts', 'campaignpress_disable_dashicons');

/**
 * Clear homepage transients when issues or events are updated
 */
function campaignpress_clear_homepage_cache($post_id) {
    $post_type = get_post_type($post_id);

    if ('cp_issue' === $post_type) {
        delete_transient('campaignpress_homepage_issues');
    }

    if ('cp_event' === $post_type) {
        // Clear all event transients (one per day)
        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_campaignpress_homepage_events_%'");
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_campaignpress_homepage_events_%'");
    }
}
add_action('save_post', 'campaignpress_clear_homepage_cache');
add_action('delete_post', 'campaignpress_clear_homepage_cache');

/**
 * Font loading is handled via theme.json
 * For privacy compliance (GDPR), fonts should be self-hosted
 * See PATCHES.md for instructions on localizing Google Fonts
 */

/**
 * Enqueue block editor assets
 */
function campaignpress_block_editor_assets() {
    // Standard Editor Styles
    wp_enqueue_style(
        'campaignpress-block-editor',
        CAMPAIGNPRESS_ASSETS_URI . '/css/block-editor.css',
        array('wp-edit-blocks'),
        CAMPAIGNPRESS_VERSION
    );

    // Political Studio UX Overrides (Elementor-like)
    wp_enqueue_style(
        'campaignpress-editor-ux',
        CAMPAIGNPRESS_ASSETS_URI . '/css/editor-overrides.css',
        array('wp-edit-blocks'),
        CAMPAIGNPRESS_VERSION
    );

    wp_enqueue_script(
        'campaignpress-editor-ux',
        CAMPAIGNPRESS_ASSETS_URI . '/js/editor-overrides.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-plugins', 'wp-edit-post'),
        CAMPAIGNPRESS_VERSION,
        true
    );
}
add_action('enqueue_block_editor_assets', 'campaignpress_block_editor_assets');

/**
 * Register Block Patterns
 */
require_once get_template_directory() . '/includes/block-patterns.php';

/**
 * Register Block Pattern Category
 */
function campaignpress_register_block_pattern_category() {
    register_block_pattern_category(
        'campaign-office',
        array('label' => __('CampaignPress', 'campaign-office'))
    );
}
add_action('init', 'campaignpress_register_block_pattern_category');



/**
 * Block themes use template parts instead of widget areas
 * Widgets can be added to template parts using the Site Editor
 */

/**
 * Load Core System
 */
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/core/loader.php';

/**
 * Add body classes for customizer options
 */
function campaignpress_color_scheme_body_class($classes) {
    $color_scheme = get_theme_mod('campaignpress_color_scheme', 'democrat-blue');
    $classes[] = 'color-scheme-' . sanitize_html_class($color_scheme);
    return $classes;
}
add_filter('body_class', 'campaignpress_color_scheme_body_class');

/**
 * Block theme template hierarchy
 * WordPress automatically loads templates from /templates/ directory
 * Custom post type templates (single-cp_*.html, archive-cp_*.html) are now in /templates/
 */

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



function campaignpress_customize_disclaimer($wp_customize) {
    $wp_customize->add_setting('campaignpress_disclaimer_text', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));

    $wp_customize->add_control('campaignpress_disclaimer_text', array(
        'label' => __('"Paid for by" Disclaimer', 'campaign-office'),
        'description' => __('e.g. Paid for by Friends of Candidate', 'campaign-office'),
        'section' => 'title_tagline',
        'type' => 'text',
    ));
}
add_action('customize_register', 'campaignpress_customize_disclaimer');