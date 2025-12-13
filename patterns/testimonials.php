<?php
/**
 * Title: Testimonials Carousel
 * Slug: campaignpress/testimonials
 * Categories: campaign-office, text
 * Keywords: quote, testimonial, endorsement
 */
?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|12","bottom":"var:preset|spacing|12"}}},"backgroundColor":"neutral-50","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-neutral-50-background-color has-background" style="padding-top:var(--wp--preset--spacing--12);padding-bottom:var(--wp--preset--spacing--12)">
    <!-- wp:heading {"textAlign":"center","level":2} -->
    <h2 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'Endorsements', 'campaign-office' ); ?></h2>
    <!-- /wp:heading -->

    <!-- wp:columns {"style":{"spacing":{"blockGap":"2rem"}}} -->
    <div class="wp-block-columns">
        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:group {"style":{"border":{"width":"1px","radius":"var:preset|custom|borderRadius|lg"},"spacing":{"padding":{"top":"var:preset|spacing|6","right":"var:preset|spacing|6","bottom":"var:preset|spacing|6","left":"var:preset|spacing|6"}}},"borderColor":"neutral-200","backgroundColor":"white"} -->
            <div class="wp-block-group has-border-color has-neutral-200-border-color has-white-background-color has-background" style="border-width:1px;border-radius:var(--wp--preset--custom--border-radius--lg);padding-top:var(--wp--preset--spacing--6);padding-right:var(--wp--preset--spacing--6);padding-bottom:var(--wp--preset--spacing--6);padding-left:var(--wp--preset--spacing--6)">
                <!-- wp:quote {"className":"is-style-plain"} -->
                <blockquote class="wp-block-quote is-style-plain">
                    <!-- wp:paragraph -->
                    <p>"The clear choice for our future. Honest, hardworking, and dedicated."</p>
                    <!-- /wp:paragraph -->
                    <!-- wp:separator {"className":"is-style-wide"} -->
                    <hr class="wp-block-separator has-alpha-channel-opacity is-style-wide"/>
                    <!-- /wp:separator -->
                    <!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
                    <div class="wp-block-group">
                         <!-- wp:image {"width":50,"height":50,"scale":"cover","sizeSlug":"thumbnail","linkDestination":"none","className":"is-style-rounded"} -->
                        <figure class="wp-block-image size-thumbnail is-style-rounded is-resized"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/team-placeholder.jpg' ); ?>" alt="" style="object-fit:cover;width:50px;height:50px" width="50" height="50"/></figure>
                        <!-- /wp:image -->
                        <!-- wp:paragraph {"fontSize":"sm"} -->
                        <p class="has-sm-font-size"><strong>Mayor Jane Doe</strong><br>Cityville</p>
                        <!-- /wp:paragraph -->
                    </div>
                    <!-- /wp:group -->
                </blockquote>
                <!-- /wp:quote -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

         <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:group {"style":{"border":{"width":"1px","radius":"var:preset|custom|borderRadius|lg"},"spacing":{"padding":{"top":"var:preset|spacing|6","right":"var:preset|spacing|6","bottom":"var:preset|spacing|6","left":"var:preset|spacing|6"}}},"borderColor":"neutral-200","backgroundColor":"white"} -->
            <div class="wp-block-group has-border-color has-neutral-200-border-color has-white-background-color has-background" style="border-width:1px;border-radius:var(--wp--preset--custom--border-radius--lg);padding-top:var(--wp--preset--spacing--6);padding-right:var(--wp--preset--spacing--6);padding-bottom:var(--wp--preset--spacing--6);padding-left:var(--wp--preset--spacing--6)">
                <!-- wp:quote {"className":"is-style-plain"} -->
                <blockquote class="wp-block-quote is-style-plain">
                    <!-- wp:paragraph -->
                    <p>"A leader who listens. I'm proud to support this campaign."</p>
                    <!-- /wp:paragraph -->
                    <!-- wp:separator {"className":"is-style-wide"} -->
                    <hr class="wp-block-separator has-alpha-channel-opacity is-style-wide"/>
                    <!-- /wp:separator -->
                    <!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
                    <div class="wp-block-group">
                         <!-- wp:image {"width":50,"height":50,"scale":"cover","sizeSlug":"thumbnail","linkDestination":"none","className":"is-style-rounded"} -->
                        <figure class="wp-block-image size-thumbnail is-style-rounded is-resized"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/team-placeholder.jpg' ); ?>" alt="" style="object-fit:cover;width:50px;height:50px" width="50" height="50"/></figure>
                        <!-- /wp:image -->
                        <!-- wp:paragraph {"fontSize":"sm"} -->
                        <p class="has-sm-font-size"><strong>John Smith</strong><br>Teacher</p>
                        <!-- /wp:paragraph -->
                    </div>
                    <!-- /wp:group -->
                </blockquote>
                <!-- /wp:quote -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
