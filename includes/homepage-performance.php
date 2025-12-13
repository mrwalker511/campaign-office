<?php
/**
 * Additional Performance Optimizations for Homepage
 * Adds resource hints and preloading for critical assets
 * 
 * @package CampaignPress
 * @since 2.0.0
 */

/**
 * Add resource hints for homepage performance
 */
function campaignpress_homepage_resource_hints() {
    if (!is_front_page()) {
        return;
    }
    
    // Preload hero video poster if exists
    if (has_post_thumbnail()) {
        $poster_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
        if ($poster_url) {
            echo '<link rel="preload" as="image" href="' . esc_url($poster_url) . '" fetchpriority="high">' . "\n";
        }
    }
    
    // Preload critical CSS for homepage
    $critical_css = get_template_directory() . '/assets/css/critical/home.css';
    if (file_exists($critical_css)) {
        echo '<link rel="preload" as="style" href="' . get_template_directory_uri() . '/assets/css/critical/home.css">' . "\n";
    }
}
add_action('wp_head', 'campaignpress_homepage_resource_hints', 1);

/**
 * Optimize video loading on homepage
 */
function campaignpress_optimize_homepage_videos() {
    if (!is_front_page()) {
        return;
    }
    
    ?>
    <script>
    // Optimize video loading
    document.addEventListener('DOMContentLoaded', function() {
        const heroVideo = document.querySelector('.campaign-hero video');
        if (heroVideo) {
            // Only load video on fast connections
            if ('connection' in navigator) {
                const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                if (connection && connection.effectiveType === '4g') {
                    heroVideo.load();
                } else {
                    // On slow connections, just show poster
                    heroVideo.removeAttribute('autoplay');
                }
            }
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'campaignpress_optimize_homepage_videos', 1);

/**
 * Add preconnect for external resources
 */
function campaignpress_external_resource_hints() {
    // Only add if actually using external resources
    $donation_url = get_option('campaignpress_donation_url');
    if ($donation_url && strpos($donation_url, 'http') === 0) {
        $parsed = parse_url($donation_url);
        if (isset($parsed['host'])) {
            echo '<link rel="dns-prefetch" href="//' . esc_attr($parsed['host']) . '">' . "\n";
        }
    }
}
add_action('wp_head', 'campaignpress_external_resource_hints', 1);

/**
 * Lazy load below-the-fold images
 */
function campaignpress_lazy_load_content_images($content) {
    if (!is_front_page()) {
        return $content;
    }
    
    // Add loading=lazy to all images except the first one (hero)
    static $first_image = true;
    
    if ($first_image) {
        $first_image = false;
        return $content;
    }
    
    // Add lazy loading to subsequent images
    $content = preg_replace('/<img(.*?)>/i', '<img$1 loading="lazy">', $content);
    
    return $content;
}
add_filter('the_content', 'campaignpress_lazy_load_content_images', 20);
