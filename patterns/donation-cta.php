<?php
/**
 * Title: Donation CTA Bar
 * Slug: campaignpress/donation-cta
 * Categories: campaignpress, call-to-action
 * Keywords: donate, fundraising, money, bar
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|16","bottom":"var:preset|spacing|16"}},"border":{"top":{"color":"rgba(0,0,0,0.1)","width":"1px"}}},"backgroundColor":"secondary","textColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-white-color has-secondary-background-color has-text-color has-background" style="border-top-color:rgba(0,0,0,0.1);border-top-width:1px;padding-top:var(--wp--preset--spacing--16);padding-bottom:var(--wp--preset--spacing--16)">
    <!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"1.5rem"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
    <div class="wp-block-group alignwide">
        <!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"2.5rem","fontWeight":"700"}},"fontFamily":"display"} -->
        <h2 class="wp-block-heading has-text-align-center has-display-font-family" style="font-size:2.5rem;font-weight:700"><?php esc_html_e( 'America is Counting on You', 'campaignpress' ); ?></h2>
        <!-- /wp:heading -->

        <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"1.25rem","fontWeight":"400"}}} -->
        <p class="has-text-align-center" style="font-size:1.25rem;font-weight:400;max-width:800px;margin-left:auto;margin-right:auto"><?php esc_html_e( 'We can't do this alone. It takes a movement of dedicated citizens to bring real change. Join us today.', 'campaignpress' ); ?></p>
        <!-- /wp:paragraph -->

        <!-- wp:group {"style":{"spacing":{"margin":{"top":"1rem"}}},"layout":{"type":"flex","justifyContent":"center","flexWrap":"wrap"}} -->
        <div class="wp-block-group" style="margin-top:1rem">
            <!-- wp:group {"style":{"spacing":{"padding":{"top":"1rem","right":"1.5rem","bottom":"1rem","left":"1.5rem"}},"border":{"radius":"0"}},"backgroundColor":"white","layout":{"type":"flex","flexWrap":"nowrap"}} -->
            <div class="wp-block-group has-white-background-color has-background" style="border-radius:0;padding-top:1rem;padding-right:1.5rem;padding-bottom:1rem;padding-left:1.5rem">
                <!-- wp:paragraph {"style":{"typography":{"fontSize":"1.25rem","fontWeight":"700"}},"textColor":"neutral-400"} -->
                <p class="has-neutral-400-color has-text-color" style="font-size:1.25rem;font-weight:700">$</p>
                <!-- /wp:paragraph -->
                <!-- wp:paragraph {"style":{"typography":{"fontSize":"1.25rem","fontWeight":"700"}},"textColor":"neutral-800"} -->
                <p class="has-neutral-800-color has-text-color" style="font-size:1.25rem;font-weight:700"><?php esc_html_e( '25', 'campaignpress' ); ?></p>
                <!-- /wp:paragraph -->
            </div>
            <!-- /wp:group -->

            <!-- wp:button {"backgroundColor":"primary","style":{"spacing":{"padding":{"top":"1.25rem","right":"2.5rem","bottom":"1.25rem","left":"2.5rem"}},"border":{"radius":"0"}}} -->
            <div class="wp-block-button"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button" style="border-radius:0;padding-top:1.25rem;padding-right:2.5rem;padding-bottom:1.25rem;padding-left:2.5rem;font-weight:700"><?php esc_html_e( 'Donate Now', 'campaignpress' ); ?></a></div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:group -->

        <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"0.875rem"}},"textColor":"white"} -->
        <p class="has-text-align-center has-white-color has-text-color" style="font-size:0.875rem"><a href="#" style="color:inherit"><?php esc_html_e( 'Donate $50', 'campaignpress' ); ?></a> | <a href="#" style="color:inherit"><?php esc_html_e( 'Donate $100', 'campaignpress' ); ?></a> | <a href="#" style="color:inherit"><?php esc_html_e( 'Donate $250', 'campaignpress' ); ?></a></p>
        <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->
