<?php
/**
 * Title: Petition Form
 * Slug: campaignpress/petition-form
 * Categories: campaignpress, call-to-action
 * Keywords: petition, form, signature
 */
?>
<!-- wp:group {"align":"wide","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide">
    <!-- wp:columns -->
    <div class="wp-block-columns">
        <!-- wp:column {"width":"60%"} -->
        <div class="wp-block-column" style="flex-basis:60%">
            <!-- wp:heading {"level":2} -->
            <h2 class="wp-block-heading"><?php esc_html_e( 'Sign the Petition', 'campaignpress' ); ?></h2>
            <!-- /wp:heading -->

            <!-- wp:paragraph {"fontSize":"lg"} -->
            <p class="has-lg-font-size"><?php esc_html_e( 'We need 5,000 signatures to get this initiative on the ballot.', 'campaignpress' ); ?></p>
            <!-- /wp:paragraph -->

            <!-- wp:paragraph -->
            <p><?php esc_html_e( 'Join your neighbors in demanding change. By signing this petition, you are sending a clear message that we deserve better infrastructure and safer streets.', 'campaignpress' ); ?></p>
            <!-- /wp:paragraph -->

            <!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|6","right":"var:preset|spacing|6","bottom":"var:preset|spacing|6","left":"var:preset|spacing|6"}}},"backgroundColor":"neutral-100"} -->
            <div class="wp-block-group has-neutral-100-background-color has-background" style="padding-top:var(--wp--preset--spacing--6);padding-right:var(--wp--preset--spacing--6);padding-bottom:var(--wp--preset--spacing--6);padding-left:var(--wp--preset--spacing--6)">
                 <!-- wp:paragraph -->
                <p><em>[Petition Form Shortcode Placeholder]</em></p>
                <!-- /wp:paragraph -->
                 <!-- wp:button {"className":"is-style-fill"} -->
                <div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button"><?php esc_html_e( 'Sign Petition', 'campaignpress' ); ?></a></div>
                <!-- /wp:button -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"width":"40%"} -->
        <div class="wp-block-column" style="flex-basis:40%">
            <!-- wp:image {"aspectRatio":"3/4","scale":"cover","sizeSlug":"large","linkDestination":"none"} -->
            <figure class="wp-block-image size-large"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/team-placeholder.jpg' ); ?>" alt="" style="aspect-ratio:3/4;object-fit:cover"/></figure>
            <!-- /wp:image -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
