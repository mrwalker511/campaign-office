<?php
/**
 * Title: Political Hero Section
 * Slug: campaignpress/hero-political
 * Categories: campaign-office, featured
 * Keywords: hero, political, campaign, cover
 */
?>
<!-- wp:group {"align":"full","className":"cp-hero","style":{"spacing":{"padding":{"top":"0","bottom":"0"}}},"backgroundColor":"primary","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull cp-hero has-primary-background-color has-background" style="padding-top:0;padding-bottom:0">
    <!-- wp:cover {"url":"<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-placeholder.jpg' ); ?>","dimRatio":60,"overlayColor":"primary","minHeight":90,"minHeightUnit":"vh","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|16"}}}} -->
    <div class="wp-block-cover alignfull" style="padding-top:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--16);min-height:90vh">
        <span aria-hidden="true" class="wp-block-cover__background has-primary-background-color has-background-dim-60 has-background-dim"></span>
        <img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-placeholder.jpg' ); ?>" data-object-fit="cover"/>
        <div class="wp-block-cover__inner-container">
            <!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained","contentSize":"700px","justifyContent":"left"}} -->
            <div class="wp-block-group alignwide">
                <!-- wp:paragraph {"className":"cp-hero__badge","style":{"typography":{"fontSize":"0.75rem","fontWeight":"700","letterSpacing":"0.15em","textTransform":"uppercase"},"spacing":{"padding":{"top":"0.25rem","right":"1rem","bottom":"0.25rem","left":"1rem"},"margin":{"bottom":"1.5rem"}},"border":{"left":{"color":"var:preset|color|white","width":"4px"}}},"backgroundColor":"secondary","textColor":"white"} -->
                <p class="cp-hero__badge has-white-color has-secondary-background-color has-text-color has-background" style="border-left-color:var(--wp--preset--color--white);border-left-width:4px;margin-bottom:1.5rem;padding-top:0.25rem;padding-right:1rem;padding-bottom:0.25rem;padding-left:1rem;font-size:0.75rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase"><?php esc_html_e( 'OFFICIAL CAMPAIGN SITE', 'campaign-office' ); ?></p>
                <!-- /wp:paragraph -->

                <!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"clamp(2.5rem, 6vw, 5rem)","fontWeight":"700","lineHeight":"1.1"},"spacing":{"margin":{"bottom":"1.5rem"}}},"textColor":"white","fontFamily":"display"} -->
                <h1 class="wp-block-heading has-white-color has-text-color has-display-font-family" style="margin-bottom:1.5rem;font-size:clamp(2.5rem, 6vw, 5rem);font-weight:700;line-height:1.1"><?php esc_html_e( 'Restoring Faith', 'campaign-office' ); ?><br><em><?php esc_html_e( 'In America', 'campaign-office' ); ?></em></h1>
                <!-- /wp:heading -->

                <!-- wp:paragraph {"style":{"typography":{"fontSize":"1.25rem","fontWeight":"400","lineHeight":"1.6"},"spacing":{"margin":{"bottom":"2.5rem"}},"color":{"text":"rgba(255,255,255,0.9)"}}} -->
                <p style="color:rgba(255,255,255,0.9);margin-bottom:2.5rem;font-size:1.25rem;font-weight:400;line-height:1.6"><?php esc_html_e( 'Thomas Harrison is fighting to protect our constitutional rights, strengthen our economy, and secure a brighter future for our families.', 'campaign-office' ); ?></p>
                <!-- /wp:paragraph -->

                <!-- wp:buttons {"layout":{"type":"flex","flexWrap":"wrap"}} -->
                <div class="wp-block-buttons">
                    <!-- wp:button {"backgroundColor":"secondary","style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.05em","fontWeight":"700"},"spacing":{"padding":{"top":"1.25rem","right":"2.5rem","bottom":"1.25rem","left":"2.5rem"}}}} -->
                    <div class="wp-block-button"><a class="wp-block-button__link has-secondary-background-color has-background wp-element-button" style="padding-top:1.25rem;padding-right:2.5rem;padding-bottom:1.25rem;padding-left:2.5rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700"><?php esc_html_e( 'Join the Movement', 'campaign-office' ); ?></a></div>
                    <!-- /wp:button -->

                    <!-- wp:button {"className":"is-style-outline","style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.05em","fontWeight":"700"},"spacing":{"padding":{"top":"1.25rem","right":"2.5rem","bottom":"1.25rem","left":"2.5rem"}},"border":{"width":"2px"}}} -->
                    <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" style="border-width:2px;padding-top:1.25rem;padding-right:2.5rem;padding-bottom:1.25rem;padding-left:2.5rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700"><?php esc_html_e( 'Watch the Video', 'campaign-office' ); ?></a></div>
                    <!-- /wp:button -->
                </div>
                <!-- /wp:buttons -->
            </div>
            <!-- /wp:group -->
        </div>
    </div>
    <!-- /wp:cover -->
</div>
<!-- /wp:group -->
