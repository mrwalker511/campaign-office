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
if (defined('CAMPAIGNPRESS_DEV_MODE') && CAMPAIGNPRESS_DEV_MODE) {
    // Development mode is enabled via wp-config.php
} else {
    define('CAMPAIGNPRESS_DEV_MODE', false);
}

/**
 * Define Constants
 */
define('CAMPAIGNPRESS_VERSION', '2.0.0');
define('CAMPAIGNPRESS_THEME_DIR', get_template_directory());
define('CAMPAIGNPRESS_THEME_URI', get_template_directory_uri());
define('CAMPAIGNPRESS_INCLUDES_DIR', CAMPAIGNPRESS_THEME_DIR . '/includes');
define('CAMPAIGNPRESS_ASSETS_URI', CAMPAIGNPRESS_THEME_URI . '/assets');

/**
 * Check for Campaign Office Core Plugin
 *
 * The theme works best with the Campaign Office Core plugin which provides
 * custom post types, volunteer management, and other campaign features.
 */
function campaignpress_check_core_plugin() {
    if (!class_exists('Campaign_Office_Core')) {
        add_action('admin_notices', 'campaignpress_core_plugin_notice');
    } else {
        // Plugin is active - add theme support
        add_theme_support('campaign-office-core');
    }
}
add_action('after_setup_theme', 'campaignpress_check_core_plugin');

/**
 * Admin notice if Campaign Office Core plugin is not active
 */
function campaignpress_core_plugin_notice() {
    // Only show to users who can install plugins
    if (!current_user_can('install_plugins')) {
        return;
    }

    // Check if notice has been dismissed
    $dismissed = get_user_meta(get_current_user_id(), 'campaignpress_dismiss_plugin_notice', true);
    if ($dismissed === 'yes') {
        return;
    }

    $plugin_slug = 'campaign-office-core';
    $plugin_file = $plugin_slug . '/' . $plugin_slug . '.php';

    // Check if plugin is installed but not activated
    $all_plugins = get_plugins();
    $is_installed = isset($all_plugins[$plugin_file]);

    if ($is_installed) {
        // Plugin is installed but not activated
        $activate_url = wp_nonce_url(
            admin_url('plugins.php?action=activate&plugin=' . urlencode($plugin_file)),
            'activate-plugin_' . $plugin_file
        );

        $message = sprintf(
            /* translators: %s: plugin name */
            __('Campaign Office theme works best with the %s plugin. The plugin is installed but not active.', 'campaign-office'),
            '<strong>Campaign Office Core</strong>'
        );

        $button = sprintf(
            '<a href="%s" class="button button-primary">%s</a>',
            esc_url($activate_url),
            __('Activate Plugin', 'campaign-office')
        );
    } else {
        // Plugin is not installed
        $install_url = wp_nonce_url(
            self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_slug),
            'install-plugin_' . $plugin_slug
        );

        $message = sprintf(
            /* translators: %s: plugin name */
            __('Campaign Office theme requires the %s plugin for full functionality.', 'campaign-office'),
            '<strong>Campaign Office Core</strong>'
        );

        $button = sprintf(
            '<a href="%s" class="button button-primary">%s</a>',
            esc_url($install_url),
            __('Install Plugin', 'campaign-office')
        );
    }

    $dismiss_url = add_query_arg(
        array(
            'campaignpress_dismiss_notice' => 'plugin',
            'nonce' => wp_create_nonce('campaignpress_dismiss_notice')
        ),
        admin_url()
    );

    ?>
    <div class="notice notice-warning is-dismissible" data-dismiss-url="<?php echo esc_url($dismiss_url); ?>">
        <p>
            <?php echo wp_kses_post($message); ?>
        </p>
        <p>
            <?php echo $button; ?>
            <a href="<?php echo esc_url($dismiss_url); ?>" class="button">
                <?php _e('Dismiss', 'campaign-office'); ?>
            </a>
        </p>
        <p>
            <small>
                <?php _e('Note: Without the plugin, some features like volunteer management, custom post types, and event RSVPs will not be available.', 'campaign-office'); ?>
            </small>
        </p>
    </div>
    <?php
}

/**
 * Handle notice dismissal
 */
function campaignpress_handle_notice_dismissal() {
    if (isset($_GET['campaignpress_dismiss_notice']) && isset($_GET['nonce'])) {
        if (wp_verify_nonce($_GET['nonce'], 'campaignpress_dismiss_notice')) {
            update_user_meta(get_current_user_id(), 'campaignpress_dismiss_plugin_notice', 'yes');
            wp_safe_redirect(remove_query_arg(array('campaignpress_dismiss_notice', 'nonce')));
            exit;
        }
    }
}
add_action('admin_init', 'campaignpress_handle_notice_dismissal');

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
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'campaign-office'),
        'footer'  => __('Footer Menu', 'campaign-office'),
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
 * Content width is controlled by theme.json settings.contentSize and settings.layout.wideSize
 * No need for legacy $content_width global in block themes
 */

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

    // Heroicons CSS
    wp_enqueue_style(
        'campaignpress-heroicons',
        get_template_directory_uri() . '/assets/css/heroicons.css',
        array('campaignpress-style'),
        CAMPAIGNPRESS_VERSION
    );

    // Bootstrap 5 CSS (self-hosted by default, filterable for CDN)
    // To use CDN, add to functions.php: add_filter('campaignpress_bootstrap_css_url', function() { return 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'; });
    $bootstrap_css_url = apply_filters(
        'campaignpress_bootstrap_css_url',
        get_template_directory_uri() . '/assets/vendor/bootstrap/bootstrap.min.css'
    );
    wp_enqueue_style(
        'bootstrap',
        $bootstrap_css_url,
        array(),
        '5.3.0'
    );

    // Bootstrap 5 JS Bundle (self-hosted by default, filterable for CDN)
    // To use CDN, add to functions.php: add_filter('campaignpress_bootstrap_js_url', function() { return 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'; });
    $bootstrap_js_url = apply_filters(
        'campaignpress_bootstrap_js_url',
        get_template_directory_uri() . '/assets/vendor/bootstrap/bootstrap.bundle.min.js'
    );
    wp_enqueue_script(
        'bootstrap-bundle',
        $bootstrap_js_url,
        array(),
        '5.3.0',
        true
    );

    // Main theme JS (jQuery dependency auto-enqueued by WordPress)
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
        'ajax_url'         => admin_url('admin-ajax.php'),
        'nonce'            => wp_create_nonce('campaignpress_nonce'),
        'countdown_ended'  => __('Event has ended', 'campaign-office'),
        'day_singular'     => __('Day', 'campaign-office'),
        'day_plural'       => __('Days', 'campaign-office'),
        'debug'            => defined('WP_DEBUG') && WP_DEBUG,
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
 * Add lazy loading to images
 *
 * Loads images lazily to improve initial page load time.
 * Skips lazy loading for above-the-fold images.
 *
 * @param array $attr Image attributes.
 * @param WP_Post $attachment Image attachment post.
 * @param string|array $size Requested image size.
 * @return array Modified attributes.
 */
function campaignpress_add_lazy_loading($attr, $attachment, $size) {
    // Skip lazy loading for above-the-fold images
    $lazy_skip_sizes = apply_filters('campaignpress_lazy_skip_sizes', array(
        'hero',
        'full',
        'campaignpress-candidate-headshot',
        'campaignpress-event-hero'
    ));

    if (in_array($size, $lazy_skip_sizes, true)) {
        return $attr;
    }

    // Skip if in admin
    if (is_admin()) {
        return $attr;
    }

    // Skip if feed
    if (is_feed()) {
        return $attr;
    }

    // Add lazy loading attribute
    $attr['loading'] = 'lazy';

    // Add decoding attribute for better performance
    $attr['decoding'] = 'async';

    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'campaignpress_add_lazy_loading', 10, 3);

/**
 * Clear homepage transients when issues or events are updated
 */
function campaignpress_clear_homepage_cache($post_id) {
    $post_type = get_post_type($post_id);

    if ('cp_issue' === $post_type) {
        delete_transient('campaignpress_homepage_issues');
    }

    if ('cp_event' === $post_type) {
        // Clear all event transients (one per day) using WordPress cache API
        global $wpdb;
        $transient_pattern = '_transient_campaignpress_homepage_events_';
        $timeout_pattern = '_transient_timeout_campaignpress_homepage_events_';

        // Get all matching transients
        $transients = $wpdb->get_col($wpdb->prepare(
            "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
            $wpdb->esc_like($transient_pattern) . '%'
        ));

        $timeouts = $wpdb->get_col($wpdb->prepare(
            "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
            $wpdb->esc_like($timeout_pattern) . '%'
        ));

        // Delete each transient individually for safety
        foreach ($transients as $transient) {
            $name = str_replace('_transient_', '', $transient);
            delete_transient($name);
        }

        foreach ($timeouts as $timeout) {
            $name = str_replace('_transient_timeout_', '', $timeout);
            delete_option($timeout); // Cleanup timeout option
        }
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
 * Rate limiting helper for public AJAX endpoints
 * Prevents spam and DDoS attacks on public forms
 *
 * @param string $action The AJAX action name
 * @param int $max_requests Maximum requests allowed per time window
 * @param int $time_window Time window in seconds (default: 3600 = 1 hour)
 * @return bool True if rate limit exceeded, false otherwise
 */
function campaignpress_is_rate_limited($action, $max_requests = 5, $time_window = 3600) {
    // Get user identifier (IP address)
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';

    // Create transient key based on action and IP
    $transient_key = 'cp_rate_limit_' . md5($action . $user_ip);

    // Get current request count
    $request_count = get_transient($transient_key);

    if (false === $request_count) {
        // First request in this time window
        set_transient($transient_key, 1, $time_window);
        return false;
    }

    if ($request_count >= $max_requests) {
        // Rate limit exceeded
        return true;
    }

    // Increment request count
    set_transient($transient_key, $request_count + 1, $time_window);
    return false;
}

/**
 * Encrypt sensitive data like API keys
 * Uses WordPress AUTH_KEY and AUTH_SALT for encryption
 *
 * @param string $data Data to encrypt
 * @return string Encrypted data (base64 encoded)
 */
function campaignpress_encrypt($data) {
    if (empty($data)) {
        return '';
    }

    // Use WordPress authentication constants for encryption key
    $key = defined('AUTH_KEY') ? AUTH_KEY : 'campaignpress-fallback-key';
    $salt = defined('AUTH_SALT') ? AUTH_SALT : 'campaignpress-fallback-salt';

    // Create encryption key from WordPress constants
    $encryption_key = hash('sha256', $key . $salt);

    // Generate initialization vector
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($iv_length);

    // Encrypt the data
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);

    // Combine IV and encrypted data, then base64 encode
    return base64_encode($iv . '::' . $encrypted);
}

/**
 * Decrypt sensitive data like API keys
 *
 * @param string $encrypted_data Encrypted data (base64 encoded)
 * @return string|false Decrypted data or false on failure
 */
function campaignpress_decrypt($encrypted_data) {
    if (empty($encrypted_data)) {
        return '';
    }

    // Decode base64
    $decoded = base64_decode($encrypted_data);
    if ($decoded === false) {
        return false;
    }

    // Split IV and encrypted data
    $parts = explode('::', $decoded, 2);
    if (count($parts) !== 2) {
        return false;
    }

    list($iv, $encrypted) = $parts;

    // Use WordPress authentication constants for decryption key
    $key = defined('AUTH_KEY') ? AUTH_KEY : 'campaignpress-fallback-key';
    $salt = defined('AUTH_SALT') ? AUTH_SALT : 'campaignpress-fallback-salt';

    // Create decryption key from WordPress constants
    $encryption_key = hash('sha256', $key . $salt);

    // Decrypt the data
    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $encryption_key, 0, $iv);

    return $decrypted;
}

/**
 * Get encrypted option value
 * Wrapper for get_option that automatically decrypts sensitive data
 *
 * @param string $option Option name
 * @param mixed $default Default value if option doesn't exist
 * @return mixed Decrypted option value
 */
function campaignpress_get_encrypted_option($option, $default = false) {
    $encrypted_value = get_option($option, $default);

    // If it's the default value or empty, return as-is
    if ($encrypted_value === $default || empty($encrypted_value)) {
        return $encrypted_value;
    }

    // Try to decrypt - if it fails, it might be plain text (backward compatibility)
    $decrypted = campaignpress_decrypt($encrypted_value);
    if ($decrypted === false || $decrypted === '') {
        // Might be plain text from old installation
        return $encrypted_value;
    }

    return $decrypted;
}

/**
 * Update encrypted option value
 * Wrapper for update_option that automatically encrypts sensitive data
 *
 * @param string $option Option name
 * @param mixed $value Value to encrypt and store
 * @return bool Whether the option was updated
 */
function campaignpress_update_encrypted_option($option, $value) {
    if (empty($value)) {
        return update_option($option, '');
    }

    $encrypted = campaignpress_encrypt($value);
    return update_option($option, $encrypted);
}

/**
 * Enqueue block editor assets
 */
function campaignpress_block_editor_assets() {
    // Standard Editor Styles
    wp_enqueue_style(
        'campaignpress-block-editor',
        CAMPAIGNPRESS_ASSETS_URI . '/css/editor.css',
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

function campaignpress_primary_menu_layout_body_class($classes) {
    $layout = get_theme_mod('campaignpress_primary_menu_layout', 'inline');

    if (!in_array($layout, array('inline', 'vertical'), true)) {
        $layout = 'inline';
    }

    $classes[] = 'cp-primary-menu-' . $layout;

    return $classes;
}
add_filter('body_class', 'campaignpress_primary_menu_layout_body_class');

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function campaignpress_widgets_init() {
    register_sidebar(array(
        'name'          => __('Main Sidebar', 'campaign-office'),
        'id'            => 'main-sidebar',
        'description'   => __('Widgets added here will appear in the sidebar of classic-compatible pages.', 'campaign-office'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'campaignpress_widgets_init');

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
 * Heroicons helper functions
 */
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/heroicons.php';

/**
 * Heroicons browser admin page
 */
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/icons-browser.php';

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
 * Runs once when theme is activated to ensure custom post type permalinks work
 */
function campaignpress_activation() {
    // Flush rewrite rules (CPTs are already registered via init hook)
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'campaignpress_activation');

/**
 * Clean up on theme deactivation
 */
function campaignpress_deactivation() {
    flush_rewrite_rules();
}
add_action('switch_theme', 'campaignpress_deactivation');
