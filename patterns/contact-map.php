<?php
/**
 * Title: Contact Map
 * Slug: campaignpress/contact-map
 * Keywords: contact, map, locations
 */
?>
<!-- wp:group {"align":"full","className":"cp-map-placeholder","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull cp-map-placeholder">
    <!-- wp:group {"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
    <div class="wp-block-group">
        <!-- wp:paragraph {"align":"center","className":"icon","style":{"typography":{"fontSize":"4rem"}}} -->
        <p class="has-text-align-center icon" style="font-size:4rem">📍</p>
        <!-- /wp:paragraph -->
        <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"1.5rem","fontWeight":"600"}}} -->
        <p class="has-text-align-center" style="font-size:1.5rem;font-weight:600"><?php esc_html_e( 'Interactive Map Would Display Here', 'campaign-office' ); ?></p>
        <!-- /wp:paragraph -->
        <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"0.875rem"}},"textColor":"neutral-500"} -->
        <p class="has-text-align-center has-neutral-500-color has-text-color" style="font-size:0.875rem"><?php esc_html_e( 'Showing office locations across the district', 'campaign-office' ); ?></p>
        <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->
