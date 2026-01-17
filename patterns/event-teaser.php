<?php
/**
 * Title: Event Teaser
 * Slug: campaignpress/event-teaser
 * Categories: campaignpress, events
 * Keywords: event, rally, town hall
 */
?>
<!-- wp:group {"style":{"border":{"width":"1px","radius":"var:preset|custom|borderRadius|lg"},"spacing":{"padding":{"top":"0","right":"0","bottom":"0","left":"0"}}},"borderColor":"neutral-300","backgroundColor":"white"} -->
<div class="wp-block-group has-border-color has-neutral-300-border-color has-white-background-color has-background" style="border-width:1px;border-radius:var(--wp--preset--custom--border-radius--lg);padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">
    <!-- wp:columns {"style":{"spacing":{"blockGap":"0"}}} -->
    <div class="wp-block-columns">
        <!-- wp:column {"width":"30%"} -->
        <div class="wp-block-column" style="flex-basis:30%">
            <!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"medium","linkDestination":"none","className":"is-style-default"} -->
            <figure class="wp-block-image size-medium is-style-default"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/event-placeholder.jpg' ); ?>" alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
            <!-- /wp:image -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"width":"70%","style":{"spacing":{"padding":{"top":"var:preset|spacing|6","right":"var:preset|spacing|6","bottom":"var:preset|spacing|6","left":"var:preset|spacing|6"}}}} -->
        <div class="wp-block-column" style="flex-basis:70%;padding-top:var(--wp--preset--spacing--6);padding-right:var(--wp--preset--spacing--6);padding-bottom:var(--wp--preset--spacing--6);padding-left:var(--wp--preset--spacing--6)">
            <!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|primary-700"},"typography":{"textTransform":"uppercase","fontSize":"xs","fontWeight":"700"}}} -->
            <p class="has-text-color has-xs-font-size" style="color:var(--wp--preset--color--primary-700);font-weight:700;text-transform:uppercase"><?php echo date_i18n( 'M j, Y' ); ?></p>
            <!-- /wp:paragraph -->

            <!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"top":"0.5rem","bottom":"0.5rem"}}}} -->
            <h3 class="wp-block-heading" style="margin-top:0.5rem;margin-bottom:0.5rem"><?php esc_html_e( 'Town Hall Meeting', 'campaignpress' ); ?></h3>
            <!-- /wp:heading -->

            <!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|neutral-600"}}} -->
            <p class="has-text-color" style="color:var(--wp--preset--color--neutral-600)"><?php esc_html_e( 'Join us for an open discussion about local issues.', 'campaignpress' ); ?></p>
            <!-- /wp:paragraph -->

            <!-- wp:buttons -->
            <div class="wp-block-buttons">
                <!-- wp:button {"className":"is-style-outline","style":{"spacing":{"padding":{"top":"0.5rem","bottom":"0.5rem","left":"1.5rem","right":"1.5rem"}}}} -->
                <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" style="padding-top:0.5rem;padding-right:1.5rem;padding-bottom:0.5rem;padding-left:1.5rem"><?php esc_html_e( 'RSVP', 'campaignpress' ); ?></a></div>
                <!-- /wp:button -->
            </div>
            <!-- /wp:buttons -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
