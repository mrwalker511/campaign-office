<?php
/**
 * Core Performance Optimizations
 *
 * Consolidates performance patches and homepage optimizations into a single class.
 *
 * @package CampaignPress
 * @subpackage Core
 * @since 2.0.0
 */

namespace CampaignPress\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Performance {

    /**
     * Initialize the class and set its properties.
     */
    public static function init() {
        // Asset Optimization
        add_action('wp_enqueue_scripts', [__CLASS__, 'load_optimized_assets'], 20);
        add_action('wp_head', [__CLASS__, 'inline_critical_css'], 1);
        add_filter('style_loader_tag', [__CLASS__, 'defer_css'], 10, 2);

        // Bloat Removal
        add_action('init', [__CLASS__, 'remove_bloat']);

        // Resource Hints
        add_action('wp_head', [__CLASS__, 'preload_resources'], 1);
        add_action('wp_head', [__CLASS__, 'homepage_resource_hints'], 1);
        add_action('wp_head', [__CLASS__, 'external_resource_hints'], 1);

        // Script Deferral
        if (!is_admin()) {
            add_filter('script_loader_tag', [__CLASS__, 'defer_scripts'], 10, 2);
        }

        // Image Optimization
        add_filter('wp_get_attachment_image_src', [__CLASS__, 'serve_modern_image_formats'], 10, 3);

        // Video Optimization
        add_action('wp_footer', [__CLASS__, 'optimize_homepage_videos'], 1);

        // Monitoring
        add_action('wp_footer', [__CLASS__, 'performance_monitor'], 999);
    }

    /**
     * Load minified assets if they exist.
     * Note: Minified CSS exists, but minified JS does not.
     * Regular assets are loaded in functions.php with cache busting via version.
     */
    public static function load_optimized_assets() {
        // Minified CSS and JS are loaded via functions.php
        // This method is kept for backwards compatibility
        // Future: Implement build process to generate minified assets
    }

    /**
     * Inline Critical CSS for specific pages.
     */
    public static function inline_critical_css() {
        $critical_css_file = '';

        if (is_front_page()) {
            $critical_css_file = get_template_directory() . '/assets/css/critical/home.css';
        } elseif (is_post_type_archive('cp_event') || is_singular('cp_event')) {
            $critical_css_file = get_template_directory() . '/assets/css/critical/events.css';
        } elseif (is_page('donate')) {
            $critical_css_file = get_template_directory() . '/assets/css/critical/donate.css';
        } elseif (is_page('volunteer')) {
            $critical_css_file = get_template_directory() . '/assets/css/critical/volunteer.css';
        }

        if ($critical_css_file && file_exists($critical_css_file)) {
            $critical_css = file_get_contents($critical_css_file);
            if ($critical_css !== false) {
                echo '<style id="critical-css">' . $critical_css . '</style>' . "\n";
            } else {
                // Log error in debug mode only
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('CampaignPress: Failed to load critical CSS: ' . $critical_css_file);
                }
            }
        }
    }

    /**
     * Defer non-critical CSS loading.
     */
    public static function defer_css($html, $handle) {
        if ($handle === 'campaignpress-style-min' || $handle === 'campaignpress-design-wp69') {
            $html = str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $html);
            $html .= '<noscript><link rel="stylesheet" href="' . esc_url(wp_styles()->registered[$handle]->src) . '"></noscript>';
        }
        return $html;
    }

    /**
     * Remove standard WordPress bloat.
     */
    public static function remove_bloat() {
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

        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
    }

    /**
     * Preload resources (fonts, DNS prefetch).
     */
    public static function preload_resources() {
        $font_path = get_template_directory() . '/assets/fonts/PlusJakartaSans-Variable.woff2';
        $using_google_fonts = wp_style_is('campaignpress-fonts', 'enqueued') || wp_style_is('campaignpress-fonts', 'registered');

        if (!$using_google_fonts && file_exists($font_path)) {
            echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/fonts/PlusJakartaSans-Variable.woff2" as="font" type="font/woff2" crossorigin>' . "\n";
        }

        echo '<link rel="dns-prefetch" href="//maps.googleapis.com">' . "\n";
    }

    /**
     * Homepage specific resource hints (LCP optimization).
     */
    public static function homepage_resource_hints() {
        if (!is_front_page()) {
            return;
        }

        if (has_post_thumbnail()) {
            $poster_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
            if ($poster_url) {
                echo '<link rel="preload" as="image" href="' . esc_url($poster_url) . '" fetchpriority="high">' . "\n";
            }
        }
    }

    /**
     * DNS Prefetch for external donation links.
     */
    public static function external_resource_hints() {
        $donation_url = get_option('campaignpress_donation_url');
        if ($donation_url && strpos($donation_url, 'http') === 0) {
            $parsed = parse_url($donation_url);
            if (isset($parsed['host'])) {
                echo '<link rel="dns-prefetch" href="//' . esc_attr($parsed['host']) . '">' . "\n";
            }
        }
    }

    /**
     * Defer JS execution (except jQuery).
     */
    public static function defer_scripts($tag, $handle) {
        // Scripts that should NOT be deferred for better interactivity/reliability
        $exclude = array(
            'jquery',
            'jquery-core',
            'jquery-migrate',
            'campaignpress-main', // Core theme interactions
            'campaignpress-scripts', // Critical logic
            'bootstrap-bundle', // Required for UI components immediately
        );

        if (in_array($handle, $exclude, true)) {
            return $tag;
        }

        if (strpos($tag, 'defer') === false) {
            $tag = str_replace(' src', ' defer src', $tag);
        }
        return $tag;
    }

    /**
     * Serve AVIF/WebP images if available on disk.
     */
    public static function serve_modern_image_formats($image, $attachment_id, $size) {
        $upload_dir = wp_upload_dir();
        $image_path = get_attached_file($attachment_id);

        if (!$image_path) {
            return $image;
        }

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

    /**
     * Optimize Homepage Video Loading.
     */
    public static function optimize_homepage_videos() {
        if (!is_front_page()) {
            return;
        }
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const heroVideo = document.querySelector('.campaign-hero video');
            if (heroVideo) {
                if ('connection' in navigator) {
                    const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                    if (connection && connection.effectiveType === '4g') {
                        heroVideo.load();
                    } else {
                        heroVideo.removeAttribute('autoplay');
                    }
                }
            }
        });
        </script>
        <?php
    }

    /**
     * Debugging: Performance Monitor.
     */
    public static function performance_monitor() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $time = timer_stop(0, 3);
            $queries = get_num_queries();
            echo "\n<!-- Page generated in {$time} seconds with {$queries} database queries -->\n";
        }
    }
}
