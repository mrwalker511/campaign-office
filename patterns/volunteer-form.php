<?php
/**
 * Title: Volunteer Form
 * Slug: campaignpress/volunteer-form
 * Categories: campaign-office, call-to-action
 * Keywords: volunteer, form, signup
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|12","bottom":"var:preset|spacing|12"}}},"backgroundColor":"primary","textColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-white-color has-primary-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--12);padding-bottom:var(--wp--preset--spacing--12)">
    <!-- wp:columns {"verticalAlignment":"center"} -->
    <div class="wp-block-columns are-vertically-aligned-center">
        <!-- wp:column {"width":"50%"} -->
        <div class="wp-block-column" style="flex-basis:50%">
            <!-- wp:heading {"level":2} -->
            <h2 class="wp-block-heading"><?php esc_html_e( 'Join the Movement', 'campaign-office' ); ?></h2>
            <!-- /wp:heading -->

            <!-- wp:paragraph {"fontSize":"lg"} -->
            <p class="has-lg-font-size"><?php esc_html_e( 'We need your help to win. Whether you can knock on doors, make calls, or host an event, there is a place for you on our team.', 'campaign-office' ); ?></p>
            <!-- /wp:paragraph -->

            <!-- wp:list {"className":"is-style-check"} -->
            <ul class="is-style-check">
                <!-- wp:list-item -->
                <li><?php esc_html_e( 'Block Walking', 'campaign-office' ); ?></li>
                <!-- /wp:list-item -->
                <!-- wp:list-item -->
                <li><?php esc_html_e( 'Phone Banking', 'campaign-office' ); ?></li>
                <!-- /wp:list-item -->
                <!-- wp:list-item -->
                <li><?php esc_html_e( 'Data Entry', 'campaign-office' ); ?></li>
                <!-- /wp:list-item -->
                <!-- wp:list-item -->
                <li><?php esc_html_e( 'Event Hosting', 'campaign-office' ); ?></li>
                <!-- /wp:list-item -->
            </ul>
            <!-- /wp:list -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"width":"50%","style":{"spacing":{"padding":{"top":"var:preset|spacing|8","right":"var:preset|spacing|8","bottom":"var:preset|spacing|8","left":"var:preset|spacing|8"}}},"backgroundColor":"white","textColor":"neutral-900"} -->
        <div class="wp-block-column has-neutral-900-color has-white-background-color has-text-color has-background" style="flex-basis:50%;padding-top:var(--wp--preset--spacing--8);padding-right:var(--wp--preset--spacing--8);padding-bottom:var(--wp--preset--spacing--8);padding-left:var(--wp--preset--spacing--8)">
            <!-- wp:heading {"level":3} -->
            <h3 class="wp-block-heading"><?php esc_html_e( 'Sign Up Today', 'campaign-office' ); ?></h3>
            <!-- /wp:heading -->

            <!-- wp:paragraph -->
            <p><?php echo do_shortcode('[cp_volunteer_form title="Join Our Team"]'); ?></p>
            <!-- /wp:paragraph -->

            <!-- wp:button {"width":100,"className":"is-style-fill"} -->
            <div class="wp-block-button has-custom-width wp-block-button__width-100 is-style-fill"><a class="wp-block-button__link wp-element-button"><?php esc_html_e( 'I\'m In!', 'campaign-office' ); ?></a></div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
