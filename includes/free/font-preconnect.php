/**
 * Preconnect to Google Fonts for performance
 * Loaded early in <head> for optimal performance
 */
function campaignpress_preconnect_fonts() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
}
add_action('wp_head', 'campaignpress_preconnect_fonts', 1);
