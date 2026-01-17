<?php
/**
 * Title: Press Kit Grid
 * Slug: campaignpress/press-kit
 * Categories: campaignpress, text
 * Keywords: press, media, news
 */
?>
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
    <!-- wp:heading {"textAlign":"center","level":2} -->
    <h2 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'Press & Media', 'campaignpress' ); ?></h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"align":"center"} -->
    <p class="has-text-align-center"><?php esc_html_e( 'Latest press releases and media resources.', 'campaignpress' ); ?></p>
    <!-- /wp:paragraph -->

    <!-- wp:columns {"style":{"spacing":{"blockGap":"2rem"}}} -->
    <div class="wp-block-columns">
        <!-- wp:column {"width":"66%"} -->
        <div class="wp-block-column" style="flex-basis:66%">
            <!-- wp:heading {"level":3} -->
            <h3 class="wp-block-heading"><?php esc_html_e( 'Latest Releases', 'campaignpress' ); ?></h3>
            <!-- /wp:heading -->

            <!-- wp:query {"queryId":1,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"displayLayout":{"type":"list"}} -->
            <div class="wp-block-query">
                <!-- wp:post-template -->
                <!-- wp:group {"style":{"spacing":{"padding":{"top":"1rem","bottom":"1rem"},"margin":{"top":"0","bottom":"1rem"}},"border":{"bottom":{"color":"var:preset|color|neutral-300","width":"1px"}}}} -->
                <div class="wp-block-group" style="border-bottom-color:var(--wp--preset--color--neutral-300);border-bottom-width:1px;margin-top:0;margin-bottom:1rem;padding-top:1rem;padding-bottom:1rem">
                    <!-- wp:post-title {"isLink":true} /-->
                    <!-- wp:post-date /-->
                    <!-- wp:post-excerpt /-->
                </div>
                <!-- /wp:group -->
                <!-- /wp:post-template -->
            </div>
            <!-- /wp:query -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"width":"33%","style":{"border":{"width":"1px"},"spacing":{"padding":{"top":"var:preset|spacing|6","right":"var:preset|spacing|6","bottom":"var:preset|spacing|6","left":"var:preset|spacing|6"}}},"borderColor":"neutral-300"} -->
        <div class="wp-block-column has-border-color has-neutral-300-border-color" style="border-width:1px;flex-basis:33%;padding-top:var(--wp--preset--spacing--6);padding-right:var(--wp--preset--spacing--6);padding-bottom:var(--wp--preset--spacing--6);padding-left:var(--wp--preset--spacing--6)">
            <!-- wp:heading {"level":3} -->
            <h3 class="wp-block-heading"><?php esc_html_e( 'Media Kit', 'campaignpress' ); ?></h3>
            <!-- /wp:heading -->

            <!-- wp:file {"showDownloadButton":true,"downloadButtonText":"Download Bio"} -->
            <div class="wp-block-file"><a href="#"><?php esc_html_e( 'Candidate Bio (PDF)', 'campaignpress' ); ?></a><a href="#" class="wp-block-file__button wp-element-button" download><?php esc_html_e( 'Download Bio', 'campaignpress' ); ?></a></div>
            <!-- /wp:file -->

            <!-- wp:file {"showDownloadButton":true,"downloadButtonText":"Download Headshot"} -->
            <div class="wp-block-file"><a href="#"><?php esc_html_e( 'Official Headshot (JPG)', 'campaignpress' ); ?></a><a href="#" class="wp-block-file__button wp-element-button" download><?php esc_html_e( 'Download Headshot', 'campaignpress' ); ?></a></div>
            <!-- /wp:file -->

            <!-- wp:paragraph {"fontSize":"sm","style":{"spacing":{"margin":{"top":"2rem"}}}} -->
            <p class="has-sm-font-size" style="margin-top:2rem"><strong><?php esc_html_e( 'Press Contact:', 'campaignpress' ); ?></strong><br>press@campaign.com<br>555-0123</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
