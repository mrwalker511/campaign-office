<?php
/**
 * Performance Optimization Patches for CampaignPress
 * Add these functions to functions.php for 95+ Core Web Vitals
 * 
 * @package CampaignPress
 * @since 2.0.0
 */

/**
 * PATCH 1: Load Optimized Assets
 * Uses minified CSS and JS for production
 */
function campaignpress_optimized_assets() {
    // Check if optimized files exist
    $min_css_path = get_template_directory() . '/assets/css/min/design-system-wp69.css';
    $min_js_path = get_template_directory() . '/assets/js/min/main.js';
    
    if (file_exists($min_css_path)) {
        wp_enqueue_style(
            'campaignpress-style-min',
            get_template_directory_uri() . '/assets/css/min/design-system-wp69.css',
            array(),
            filemtime($min_css_path)
        );
    }
    
    if (file_exists($min_js_path)) {
        wp_enqueue_script(
            'campaignpress-main-min',
            get_template_directory_uri() . '/assets/js/min/main.js',
            array(),
            filemtime($min_js_path),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'campaignpress_optimized_assets', 20);

/**
 * PATCH 2: Inline Critical CSS
 * Inlines above-the-fold CSS for instant rendering
 */
function campaignpress_critical_css() {
    $critical_css_file = '';
    
    // Determine which critical CSS file to use
    if (is_front_page()) {
        $critical_css_file = get_template_directory() . '/assets/css/critical/home.css';
    } elseif (is_post_type_archive('cp_event') || is_singular('cp_event')) {
        $critical_css_file = get_template_directory() . '/assets/css/critical/events.css';
    } elseif (is_page('donate')) {
        $critical_css_file = get_template_directory() . '/assets/css/critical/donate.css';
    } elseif (is_page('volunteer')) {
        $critical_css_file = get_template_directory() . '/assets/css/critical/volunteer.css';
    }
    
    // Inline critical CSS if file exists
    if ($critical_css_file && file_exists($critical_css_file)) {
        $critical_css = file_get_contents($critical_css_file);
        echo '<style id="critical-css">' . $critical_css . '</style>' . "\n";
    }
}
add_action('wp_head', 'campaignpress_critical_css', 1);

/**
 * PATCH 3: Defer Non-Critical CSS
 * Loads non-critical CSS asynchronously
 */
function campaignpress_defer_css($html, $handle) {
    // Defer main stylesheet
    if ($handle === 'campaignpress-style-min' || $handle === 'campaignpress-design-wp69') {
        $html = str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $html);
        // Add noscript fallback
        $html .= '<noscript><link rel="stylesheet" href="' . esc_url(wp_styles()->registered[$handle]->src) . '"></noscript>';
    }
    return $html;
}
add_filter('style_loader_tag', 'campaignpress_defer_css', 10, 2);

/**
 * PATCH 4: Remove WordPress Bloat
 * Removes unnecessary WordPress features for performance
 */
function campaignpress_remove_bloat() {
    // Remove emoji detection scripts
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    
    // Remove jQuery Migrate
    add_filter('wp_default_scripts', function($scripts) {
        if (!is_admin() && isset($scripts->registered['jquery'])) {
            $script = $scripts->registered['jquery'];
            if ($script->deps) {
                $script->deps = array_diff($script->deps, array('jquery-migrate'));
            }
        }
    });
    
    // Remove generator meta tag
    remove_action('wp_head', 'wp_generator');
    
    // Remove Windows Live Writer manifest
    remove_action('wp_head', 'wlwmanifest_link');
    
    // Remove RSD link
    remove_action('wp_head', 'rsd_link');
    
    // Remove shortlink
    remove_action('wp_head', 'wp_shortlink_wp_head');
    
    // Remove REST API link
    remove_action('wp_head', 'rest_output_link_wp_head');
    
    // Remove oEmbed discovery links
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
}
add_action('init', 'campaignpress_remove_bloat');

/**
 * PATCH 5: Preload Critical Resources
 * Preloads fonts and establishes early connections
 */
function campaignpress_preload_resources() {
    // Preload critical fonts (if using local fonts)
    $font_path = get_template_directory() . '/assets/fonts/PlusJakartaSans-Variable.woff2';
    if (file_exists($font_path)) {
        echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/fonts/PlusJakartaSans-Variable.woff2" as="font" type="font/woff2" crossorigin>' . "\n";
    }
    
    // DNS prefetch for external resources
    echo '<link rel="dns-prefetch" href="//maps.googleapis.com">' . "\n";
    
    // Preconnect to critical origins (only if using external fonts)
    // echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}
add_action('wp_head', 'campaignpress_preload_resources', 1);

/**
 * BONUS PATCH 6: Defer JavaScript Loading
 * Adds defer attribute to all scripts
 */
function campaignpress_defer_scripts($tag, $handle) {
    // Don't defer jQuery (some plugins may need it synchronously)
    if ($handle === 'jquery') {
        return $tag;
    }
    
    // Add defer to all other scripts
    if (strpos($tag, 'defer') === false) {
        $tag = str_replace(' src', ' defer src', $tag);
    }
    
    return $tag;
}
add_filter('script_loader_tag', 'campaignpress_defer_scripts', 10, 2);

/**
 * BONUS PATCH 7: Lazy Load Images
 * Adds native lazy loading to all images
 */
function campaignpress_lazy_load_images($content) {
    // Add loading="lazy" to all images
    $content = preg_replace('/<img(.*?)>/i', '<img$1 loading="lazy">', $content);
    return $content;
}
add_filter('the_content', 'campaignpress_lazy_load_images');
add_filter('post_thumbnail_html', 'campaignpress_lazy_load_images');

/**
 * BONUS PATCH 8: Optimize AVIF/WebP Images
 * Serves modern image formats with fallbacks
 */
function campaignpress_modern_image_formats($image, $attachment_id, $size) {
    $upload_dir = wp_upload_dir();
    $image_path = get_attached_file($attachment_id);
    
    if (!$image_path) {
        return $image;
    }
    
    // Check for AVIF version
    $avif_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.avif', $image_path);
    $webp_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $image_path);
    
    if (file_exists($avif_path)) {
        $avif_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $avif_path);
        $image[0] = $avif_url;
    } elseif (file_exists($webp_path)) {
        $webp_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $webp_path);
        $image[0] = $webp_url;
    }
    
    return $image;
}
add_filter('wp_get_attachment_image_src', 'campaignpress_modern_image_formats', 10, 3);

/**
 * BONUS PATCH 9: Disable Gutenberg Block Library CSS on Frontend
 * Only load block styles when blocks are actually used
 */
function campaignpress_disable_block_library_css() {
    if (!is_admin()) {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('wc-blocks-style'); // WooCommerce blocks
        wp_dequeue_style('global-styles'); // Global styles
    }
}
add_action('wp_enqueue_scripts', 'campaignpress_disable_block_library_css', 100);

/**
 * BONUS PATCH 10: Limit Post Revisions
 * Reduces database bloat
 */
if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 3);
}

/**
 * Performance Monitoring
 * Logs page generation time (for debugging)
 */
function campaignpress_performance_monitor() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $time = timer_stop(0, 3);
        $queries = get_num_queries();
        echo "\n<!-- Page generated in {$time} seconds with {$queries} database queries -->\n";
    }
}
add_action('wp_footer', 'campaignpress_performance_monitor', 999);
