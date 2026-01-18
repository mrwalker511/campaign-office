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
define('CAMPAIGNPRESS_VERSION', '2.1.0');
define('CAMPAIGNPRESS_THEME_DIR', get_template_directory());
define('CAMPAIGNPRESS_THEME_URI', get_template_directory_uri());
define('CAMPAIGNPRESS_INCLUDES_DIR', CAMPAIGNPRESS_THEME_DIR . '/includes');
define('CAMPAIGNPRESS_ASSETS_URI', CAMPAIGNPRESS_THEME_URI . '/assets');

/**
 * Text Domain Handling
 * WordPress uses the theme directory name as the default text domain.
 * We need to detect this and use it consistently.
 */
define('CAMPAIGNPRESS_TEXT_DOMAIN', basename(CAMPAIGNPRESS_THEME_DIR));

/**
 * Legacy Name Compatibility
 * Support both old (campaign-office) and new (campaignpress) naming
 */
define('CAMPAIGNPRESS_LEGACY_TEXT_DOMAIN', 'campaignpress');

/**
 * Development License Helper (Auto-loaded for Testing)
 *
 * If dev-license-helper.php exists in the theme directory, it will be auto-loaded.
 * This provides a mock license server for testing without needing a real license server.
 *
 * The dev-license-helper.php file:
 * - Is NOT included in production builds (excluded by build scripts)
 * - Provides test license keys for all license tiers
 * - Enables the mock license server filter
 *
 * Test License Keys (when dev-license-helper.php is present):
 *   Professional: CP-DEV-PROFESSIONAL-2024-X1Y2Z3W4V5U6
 *   Free: CP-DEV-FREE-2024-F1R2E3E4EEEE
 *   Email: dev@campaignpress.test
 */
$dev_helper_path = CAMPAIGNPRESS_THEME_DIR . '/dev-license-helper.php';
if (file_exists($dev_helper_path)) {
    require_once $dev_helper_path;
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
if (!defined('CAMPAIGNPRESS_DEV_MODE')) {
    define('CAMPAIGNPRESS_DEV_MODE', false);
}

/**
 * Set up theme text domain
 *
 * WordPress 6.7+ requires textdomain to be loaded at 'init' or later.
 * Translation functions (__(), _e(), etc.) must NOT be used before 'init'.
 * We handle dynamic directory names to avoid errors when the directory
 * doesn't match the hardcoded text domain.
 */
function campaignpress_setup_textdomain() {
    $theme_dir = basename(get_template_directory());
    $text_domain = 'campaignpress';

    // If the directory name doesn't match our expected text domain,
    // we need to load translations with the directory name as the domain
    if ($theme_dir !== $text_domain) {
        load_theme_textdomain($theme_dir, CAMPAIGNPRESS_THEME_DIR . '/languages');
    }

    // Always load with our standard text domain for backward compatibility
    load_theme_textdomain($text_domain, CAMPAIGNPRESS_THEME_DIR . '/languages');
}
add_action('init', 'campaignpress_setup_textdomain', 1);

/**
 * Check for CampaignPress Core Plugin
 *
 * The theme works best with the CampaignPress Core plugin which provides
 * custom post types, volunteer management, and other campaign features.
 * Supports both old and new plugin class names for backward compatibility.
 */
function campaignpress_check_core_plugin() {
    // Check for new CampaignPress Core plugin (prefer this)
    if (class_exists('CampaignPress_Core')) {
        add_theme_support('campaignpress-core');
        return;
    }

    // Check for legacy Campaign Office Core plugin
    if (class_exists('Campaign_Office_Core')) {
        add_theme_support('campaignpress-core'); // Use new theme support name
        add_theme_support('campaign-office-core'); // Legacy support
        return;
    }

    // No core plugin active
    add_action('admin_notices', 'campaignpress_core_plugin_notice');
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

    $plugin_slug = 'campaignpress-core'; // New plugin name
    $plugin_file = $plugin_slug . '/' . $plugin_slug . '.php';

    // Also check for legacy plugin
    $legacy_plugin_file = 'campaign-office-core/campaign-office-core.php';

    // Check if plugin is installed but not activated
    $all_plugins = get_plugins();
    $is_installed = isset($all_plugins[$plugin_file]) || isset($all_plugins[$legacy_plugin_file]);

    if ($is_installed) {
        // Plugin is installed but not activated
        $activate_url = wp_nonce_url(
            admin_url('plugins.php?action=activate&plugin=' . urlencode($plugin_file)),
            'activate-plugin_' . $plugin_file
        );

        $message = sprintf(
            /* translators: %s: plugin name */
            esc_html__('CampaignPress theme works best with the %s plugin. The plugin is installed but not active.', CAMPAIGNPRESS_TEXT_DOMAIN),
            '<strong>CampaignPress Core</strong>'
        );

         $button = sprintf(
            '<a href="%s" class="button button-primary">%s</a>',
            esc_url($activate_url),
            esc_html__('Activate Plugin', CAMPAIGNPRESS_TEXT_DOMAIN)
        );
    } else {
        // Plugin is not installed
        $install_url = wp_nonce_url(
            self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_slug),
            'install-plugin_' . $plugin_slug
        );

        $message = sprintf(
            /* translators: %s: plugin name */
            esc_html__('CampaignPress theme requires the %s plugin for full functionality.', CAMPAIGNPRESS_TEXT_DOMAIN),
            '<strong>CampaignPress Core</strong>'
        );

        $button = sprintf(
            '<a href="%s" class="button button-primary">%s</a>',
            esc_url($install_url),
            __('Install Plugin', 'campaignpress')
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
            <?php echo wp_kses_post($button); ?>
            <a href="<?php echo esc_url($dismiss_url); ?>" class="button">
                <?php esc_html_e('Dismiss', 'campaignpress'); ?>
            </a>
        </p>
        <p>
            <small>
                <?php _e('Note: Without the plugin, some features like volunteer management, custom post types, and event RSVPs will not be available.', 'campaignpress'); ?>
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
        $nonce = sanitize_text_field(wp_unslash($_GET['nonce']));
        if (wp_verify_nonce($nonce, 'campaignpress_dismiss_notice')) {
            update_user_meta(get_current_user_id(), 'campaignpress_dismiss_plugin_notice', 'yes');
            wp_safe_redirect(remove_query_arg(array('campaignpress_dismiss_notice', 'nonce')));
            exit;
        }
    }
}
add_action('admin_init', 'campaignpress_handle_notice_dismissal');

/**
 * Check minimum dependencies (WordPress & PHP versions)
 *
 * @since 2.1.0
 */
function campaignpress_check_dependencies() {
    global $wp_version;

    $min_wp_version = '6.4';
    $min_php_version = '8.0';
    $errors = array();

    // Check WordPress version
    if (version_compare($wp_version, $min_wp_version, '<')) {
        $errors[] = sprintf(
            __('CampaignPress requires WordPress %s or higher. You are running version %s. Please upgrade WordPress.', 'campaignpress'),
            $min_wp_version,
            $wp_version
        );
    }

    // Check PHP version
    if (version_compare(PHP_VERSION, $min_php_version, '<')) {
        $errors[] = sprintf(
            __('CampaignPress requires PHP %s or higher. You are running version %s. Please contact your hosting provider to upgrade PHP.', 'campaignpress'),
            $min_php_version,
            PHP_VERSION
        );
    }

    // Display admin notices if there are errors
    if (!empty($errors)) {
        add_action('admin_notices', function() use ($errors) {
            foreach ($errors as $error) {
                echo '<div class="notice notice-error"><p><strong>' . esc_html__('CampaignPress Error:', 'campaignpress') . '</strong> ' . esc_html($error) . '</p></div>';
            }
        });

        // Log errors
        foreach ($errors as $error) {
            error_log('CampaignPress Dependency Error: ' . $error);
        }

        // Return false to indicate dependency check failed
        return false;
    }

    return true;
}

// Check dependencies on theme activation
add_action('after_switch_theme', 'campaignpress_check_dependencies', 1);
add_action('admin_init', 'campaignpress_check_dependencies', 1);

/**
 * Theme Setup
 */
function campaignpress_setup() {
    // Note: Textdomain is loaded at 'init' priority 1 per WordPress 6.7+ requirements.
    // Do NOT use __() or _e() in this function - use plain strings instead.

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
    // Note: Using plain strings here because WordPress 6.7+ requires textdomain
    // to be loaded at 'init' or later. These strings are still translatable via .pot files.
    register_nav_menus(array(
        'primary' => 'Primary Menu',
        'footer'  => 'Footer Menu',
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
    // Prefer compiled assets when available; fall back to source editor styles.
    $editor_style = '';
    if (file_exists(CAMPAIGNPRESS_THEME_DIR . '/assets/dist/css/editor.css')) {
        $editor_style = 'assets/dist/css/editor.css';
    } elseif (file_exists(CAMPAIGNPRESS_THEME_DIR . '/assets/css/editor.css')) {
        $editor_style = 'assets/css/editor.css';
    }

    if (!empty($editor_style)) {
        add_editor_style($editor_style);
    }

    // Disable default block patterns (we'll create custom ones)
    remove_theme_support('core-block-patterns');

    // Add support for Starter Content
    // Note: Using plain strings because WordPress 6.7+ requires textdomain at 'init' or later.
    // These strings are still translatable via .pot files for i18n tools.
    add_theme_support('starter-content', array(
        'widgets' => array(
            'main-sidebar' => array(
                'text_about' => array(
                    'text',
                    array(
                        'title' => 'About the Campaign',
                        'text'  => 'Fighting for a better future for our community. Join our movement today.',
                    ),
                ),
            ),
        ),
        'posts' => array(
            'home' => array(
                'post_type' => 'page',
                'post_title' => 'Home',
                'post_content' => '<!-- wp:pattern {"slug":"campaignpress/hero-section"} /--> <!-- wp:pattern {"slug":"campaignpress/issue-card"} /--> <!-- wp:pattern {"slug":"campaignpress/donation-cta"} /-->',
                'template' => 'front-page.html',
            ),
            'about' => array(
                'post_type' => 'page',
                'post_title' => 'About Marcus',
                'post_content' => '<!-- wp:heading --><h2>Meet the Candidate</h2><!-- /wp:heading --><!-- wp:paragraph --><p>I was born and raised in this community...</p><!-- /wp:paragraph -->',
            ),
            'contact' => array(
                'post_type' => 'page',
                'post_title' => 'Contact',
                'post_content' => '<!-- wp:heading --><h2>Get in Touch</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Email: info@campaign.test</p><!-- /wp:paragraph -->',
            ),
            'volunteer' => array(
                'post_type' => 'page',
                'post_title' => 'Volunteer',
                'post_content' => '<!-- wp:heading --><h2>Join Our Team</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Sign up to volunteer today.</p><!-- /wp:paragraph -->',
            ),
            'donate' => array(
                'post_type' => 'page',
                'post_title' => 'Donate',
                'post_content' => '<!-- wp:heading {"textAlign":"center"} --><h2 class="has-text-align-center">Fuel Our Movement</h2><!-- /wp:heading --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">Your contribution helps us reach more voters and spread our message of hope.</p><!-- /wp:paragraph --> [cp_donation_button processor="actblue" text="Donate via ActBlue" style="primary" size="large"]',
            ),
            'volunteer-dashboard' => array(
                'post_type' => 'page',
                'post_title' => 'Volunteer Portal',
                'post_content' => '[cp_volunteer_portal]',
            ),
            'events' => array(
                'post_type' => 'page',
                'post_title' => 'Events',
                'post_content' => '<!-- wp:heading --><h2>Join Us at an Upcoming Event</h2><!-- /wp:heading --><!-- wp:query {"query":{"postType":"cp_event"}} --><div class="wp-block-query"><!-- wp:post-template --> <!-- wp:post-title {"isLink":true} /--> <!-- wp:post-excerpt /--> <!-- /wp:post-template --></div><!-- /wp:query -->',
            ),
            'press-releases' => array(
                'post_type' => 'page',
                'post_title' => 'News',
            ),
        ),
        'options' => array(
            'show_on_front' => 'page',
            'page_on_front' => '{{home}}',
            'page_for_posts' => '{{press-releases}}',
        ),
        'nav_menus' => array(
            'primary' => array(
                'name' => __('Primary Menu', 'campaignpress'),
                'items' => array(
                    'link_home',
                    'page_about',
                    'page_events',
                    'page_volunteer',
                    'page_volunteer-dashboard',
                    'page_donate',
                    'page_contact',
                ),
            ),
        ),
    ));
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
    // Fonts are defined in theme.json using system font stacks
    // No external font requests for privacy compliance

    // Theme stylesheet (minimal, theme.json handles most styling)
    wp_enqueue_style(
        'campaignpress-style',
        get_stylesheet_uri(),
        array(),
        CAMPAIGNPRESS_VERSION
    );

    // Heroicons CSS
    wp_enqueue_style(
        'campaignpress-heroicons',
        get_template_directory_uri() . '/assets/css/heroicons.css',
        array('campaignpress-style'),
        CAMPAIGNPRESS_VERSION
    );

    // Tailwind CSS (Combined Design System)
    // Only enqueue compiled Tailwind output. Source Tailwind files contain @apply/@layer
    // directives and will cause CSS parse errors if served directly.
    $tailwind_css_url = '';
    if (file_exists(CAMPAIGNPRESS_THEME_DIR . '/assets/dist/css/tailwind.css')) {
        $tailwind_css_url = get_template_directory_uri() . '/assets/dist/css/tailwind.css';
    } elseif (file_exists(CAMPAIGNPRESS_THEME_DIR . '/assets/css/dist/tailwind.css')) {
        $tailwind_css_url = get_template_directory_uri() . '/assets/css/dist/tailwind.css';
    }

    // Default dependency chain (if Tailwind isn't present)
    $design_tokens_deps = array('campaignpress-style');

    if (!empty($tailwind_css_url)) {
        wp_enqueue_style(
            'campaignpress-tailwind',
            $tailwind_css_url,
            array('campaignpress-style'),
            CAMPAIGNPRESS_VERSION
        );
        $design_tokens_deps = array('campaignpress-tailwind');
    } elseif (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('CampaignPress: Tailwind build not found. Skipping compiled Tailwind enqueue.');
    }

    // Design Tokens CSS - Bridges theme.json tokens to custom CSS variables
    // Provides consistent --cp-* variables across the theme
    $design_tokens_css = get_template_directory_uri() . '/assets/css/design-tokens.css';
    if (file_exists(CAMPAIGNPRESS_THEME_DIR . '/assets/dist/css/design-tokens.css')) {
        $design_tokens_css = get_template_directory_uri() . '/assets/dist/css/design-tokens.css';
    }

    wp_enqueue_style(
        'campaignpress-design-tokens',
        $design_tokens_css,
        $design_tokens_deps,
        CAMPAIGNPRESS_VERSION
    );

    // Animation System CSS - Orchestrated animations with token-based timing
    $animations_css = get_template_directory_uri() . '/assets/css/animations.css';
    if (file_exists(CAMPAIGNPRESS_THEME_DIR . '/assets/dist/css/animations.css')) {
        $animations_css = get_template_directory_uri() . '/assets/dist/css/animations.css';
    }

    wp_enqueue_style(
        'campaignpress-animations',
        $animations_css,
        array('campaignpress-design-tokens'),
        CAMPAIGNPRESS_VERSION
    );

    // Bootstrap 5 CSS (bundled locally for WordPress.org compliance)
    // Use filter to override with CDN if needed for development:
    // add_filter('campaignpress_bootstrap_css_url', function() { return 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'; });
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

    // Bootstrap 5 JS Bundle (bundled locally for WordPress.org compliance)
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
    $main_js = get_template_directory_uri() . '/assets/js/main.js';
    if (file_exists(CAMPAIGNPRESS_THEME_DIR . '/assets/dist/js/main.js')) {
        $main_js = get_template_directory_uri() . '/assets/dist/js/main.js';
    }

    wp_enqueue_script(
        'campaignpress-main',
        $main_js,
        array('jquery'), // Depends on jQuery
        CAMPAIGNPRESS_VERSION,
        true
    );

    // Classic Statesman Homepage React App
    // Only enqueue the compiled build (do not enqueue JSX sources).
    $classic_statesman_js = '';
    if (file_exists(CAMPAIGNPRESS_THEME_DIR . '/assets/dist/js/classic-statesman.js')) {
        $classic_statesman_js = get_template_directory_uri() . '/assets/dist/js/classic-statesman.js';
    }

    /**
     * Filter: Override the Classic Statesman script URL.
     *
     * This can be used in development to point at a Vite dev server, or to
     * provide an alternate build path.
     *
     * Return an empty string to disable.
     */
    $classic_statesman_js = apply_filters('campaignpress_classic_statesman_script_url', $classic_statesman_js);

    // Load on front page or classic statesman template
    if ((is_front_page() || is_page_template('home-classic-statesman.html')) && !empty($classic_statesman_js)) {
        wp_enqueue_script(
            'campaignpress-classic-statesman',
            $classic_statesman_js,
            array(), // React is bundled
            CAMPAIGNPRESS_VERSION,
            true
        );
    } elseif ((is_front_page() || is_page_template('home-classic-statesman.html')) && defined('WP_DEBUG') && WP_DEBUG) {
        error_log('CampaignPress: Classic Statesman build not found. Skipping enqueue.');
    }

    // Comment reply script
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }

    // Localize script for AJAX
    wp_localize_script('campaignpress-main', 'campaignpress_vars', array(
        'ajax_url'         => admin_url('admin-ajax.php'),
        'nonce'            => wp_create_nonce('campaignpress_nonce'),
        'countdown_ended'  => __('Event has ended', 'campaignpress'),
        'day_singular'     => __('Day', 'campaignpress'),
        'day_plural'       => __('Days', 'campaignpress'),
        // Only expose debug mode to administrators
        'debug'            => (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')),
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
    // Get user identifier (IP address) with proper sanitization
    $user_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';

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
        'campaignpress',
        array('label' => __('CampaignPress', 'campaignpress'))
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
 * Add body class for homepage layout
 */
function campaignpress_homepage_layout_body_class($classes) {
    if (is_front_page()) {
        $homepage_layout = get_theme_mod('campaignpress_homepage_layout', 'modern');
        
        if (!in_array($homepage_layout, array('classic', 'modern', 'traditional'), true)) {
            $homepage_layout = 'modern';
        }
        
        $classes[] = 'homepage-layout-' . sanitize_html_class($homepage_layout);
    }
    
    return $classes;
}
add_filter('body_class', 'campaignpress_homepage_layout_body_class');

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function campaignpress_widgets_init() {
    register_sidebar(array(
        'name'          => __('Main Sidebar', 'campaignpress'),
        'id'            => 'primary-sidebar',
        'description'   => __('Widgets added here will appear in the sidebar of classic-compatible pages.', 'campaignpress'),
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
 * Block Templates for CPTs
 */
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/block-templates.php';

/**
 * Heroicons browser admin page
 */
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/icons-browser.php';

/**
 * Custom icons integration
 */
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/custom-icons.php';
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/free/custom-icons-block.php';

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

/**
 * Fix corrupted update transients on theme load
 *
 * If the update transients are corrupted (missing required structure),
 * delete them so WordPress can regenerate them properly.
 * This runs early before WordPress tries to use the transients.
 *
 * @since 2.1.0
 */
function campaignpress_fix_corrupted_transients() {
    // Only run once per page load
    static $fixed = false;
    if ($fixed) {
        return;
    }
    $fixed = true;

    // Check and fix plugin update transient
    $plugins_transient = get_site_transient('update_plugins');
    if ($plugins_transient !== false && (!is_object($plugins_transient) || !isset($plugins_transient->response))) {
        delete_site_transient('update_plugins');
    }

    // Check and fix theme update transient
    $themes_transient = get_site_transient('update_themes');
    if ($themes_transient !== false && (!is_object($themes_transient) || !isset($themes_transient->response))) {
        delete_site_transient('update_themes');
    }
}
// Run very early, before most WordPress operations
add_action('muplugins_loaded', 'campaignpress_fix_corrupted_transients', 1);
add_action('plugins_loaded', 'campaignpress_fix_corrupted_transients', 1);

/**
 * Fix WordPress update transient structure issues
 *
 * WordPress 6.7+ can throw "Undefined array key" warnings and fatal errors
 * when update transients are missing expected properties. This ensures the
 * required structure exists both when setting AND reading the transient.
 *
 * @since 2.1.0
 */
function campaignpress_fix_update_transient_plugins($transient) {
    if (empty($transient) || !is_object($transient)) {
        $transient = new stdClass();
    }
    if (!isset($transient->response) || !is_array($transient->response)) {
        $transient->response = array();
    }
    if (!isset($transient->no_update) || !is_array($transient->no_update)) {
        $transient->no_update = array();
    }
    if (!isset($transient->translations) || !is_array($transient->translations)) {
        $transient->translations = array();
    }
    if (!isset($transient->checked) || !is_array($transient->checked)) {
        $transient->checked = array();
    }
    return $transient;
}
// Fix when setting the transient
add_filter('pre_set_site_transient_update_plugins', 'campaignpress_fix_update_transient_plugins', 1);
// Fix when reading the transient (handles corrupted DB data)
add_filter('site_transient_update_plugins', 'campaignpress_fix_update_transient_plugins', 1);

function campaignpress_fix_update_transient_themes($transient) {
    if (empty($transient) || !is_object($transient)) {
        $transient = new stdClass();
    }
    if (!isset($transient->response) || !is_array($transient->response)) {
        $transient->response = array();
    }
    if (!isset($transient->no_update) || !is_array($transient->no_update)) {
        $transient->no_update = array();
    }
    if (!isset($transient->translations) || !is_array($transient->translations)) {
        $transient->translations = array();
    }
    if (!isset($transient->checked) || !is_array($transient->checked)) {
        $transient->checked = array();
    }
    return $transient;
}
// Fix when setting the transient
add_filter('pre_set_site_transient_update_themes', 'campaignpress_fix_update_transient_themes', 1);
// Fix when reading the transient (handles corrupted DB data)
add_filter('site_transient_update_themes', 'campaignpress_fix_update_transient_themes', 1);
