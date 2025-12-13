<?php
/**
 * Title: Political Hero Section
 * Slug: campaignpress/hero-political
 * Categories: campaign-office, featured
 * Keywords: hero, political, campaign, cover
 */
?>
<!-- wp:cover {"url":"<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-placeholder.jpg' ); ?>","dimRatio":50,"overlayColor":"primary-900","align":"full","className":"is-style-campaign-hero"} -->
<div class="wp-block-cover alignfull is-style-campaign-hero">
    <span aria-hidden="true" class="wp-block-cover__background has-primary-900-background-color has-background-dim"></span>
    <div class="wp-block-cover__inner-container">
        <!-- wp:group {"layout":{"type":"constrained"}} -->
        <div class="wp-block-group">
            <!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"var:preset|font-size|4-xl","fontWeight":"800"}}} -->
            <h1 class="wp-block-heading has-text-align-center" style="font-size:var(--wp--preset--font-size--4-xl);font-weight:800"><?php esc_html_e( 'Building a Brighter Future Together', 'campaign-office' ); ?></h1>
            <!-- /wp:heading -->

            <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"var:preset|font-size|2-xl"}}} -->
            <p class="has-text-align-center" style="font-size:var(--wp--preset--font-size--2-xl)"><?php esc_html_e( 'Vote for Integrity. Vote for Progress.', 'campaign-office' ); ?></p>
            <!-- /wp:paragraph -->

            <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
            <div class="wp-block-buttons">
                <!-- wp:button {"className":"is-style-fill"} -->
                <div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button"><?php esc_html_e( 'Donate Now', 'campaign-office' ); ?></a></div>
                <!-- /wp:button -->

                <!-- wp:button {"className":"is-style-outline"} -->
                <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button"><?php esc_html_e( 'Join the Team', 'campaign-office' ); ?></a></div>
                <!-- /wp:button -->
            </div>
            <!-- /wp:buttons -->
        </div>
        <!-- /wp:group -->
    </div>
</div>
<!-- /wp:cover -->
