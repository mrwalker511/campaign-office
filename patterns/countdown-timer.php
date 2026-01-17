<?php
/**
 * Title: Election Countdown
 * Slug: campaignpress/countdown-timer
 * Categories: campaign-office, featured
 * Keywords: countdown, election, day
 */
?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|8","bottom":"var:preset|spacing|8","left":"var:preset|spacing|8","right":"var:preset|spacing|8"}}},"backgroundColor":"primary","textColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-white-color has-primary-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--8);padding-right:var(--wp--preset--spacing--8);padding-bottom:var(--wp--preset--spacing--8);padding-left:var(--wp--preset--spacing--8)">
    <!-- wp:heading {"textAlign":"center","level":2} -->
    <h2 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'Election Day Countdown', 'campaignpress' ); ?></h2>
    <!-- /wp:heading -->

    <!-- wp:columns {"style":{"spacing":{"blockGap":"2rem"}}} -->
    <div class="wp-block-columns">
        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"3rem","fontWeight":"800","lineHeight":"1"},"color":{"text":"var:preset|color|accent"}}} -->
            <p class="has-text-align-center has-text-color" style="color:var(--wp--preset--color--accent);font-size:3rem;font-weight:800;line-height:1">146</p>
            <!-- /wp:paragraph -->
            <!-- wp:paragraph {"align":"center","style":{"typography":{"textTransform":"uppercase","fontSize":"sm"}}} -->
            <p class="has-text-align-center has-sm-font-size" style="text-transform:uppercase"><?php esc_html_e( 'Days', 'campaignpress' ); ?></p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"3rem","fontWeight":"800","lineHeight":"1"},"color":{"text":"var:preset|color|accent"}}} -->
            <p class="has-text-align-center has-text-color" style="color:var(--wp--preset--color--accent);font-size:3rem;font-weight:800;line-height:1">12</p>
            <!-- /wp:paragraph -->
             <!-- wp:paragraph {"align":"center","style":{"typography":{"textTransform":"uppercase","fontSize":"sm"}}} -->
            <p class="has-text-align-center has-sm-font-size" style="text-transform:uppercase"><?php esc_html_e( 'Hours', 'campaignpress' ); ?></p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"3rem","fontWeight":"800","lineHeight":"1"},"color":{"text":"var:preset|color|accent"}}} -->
            <p class="has-text-align-center has-text-color" style="color:var(--wp--preset--color--accent);font-size:3rem;font-weight:800;line-height:1">45</p>
            <!-- /wp:paragraph -->
             <!-- wp:paragraph {"align":"center","style":{"typography":{"textTransform":"uppercase","fontSize":"sm"}}} -->
            <p class="has-text-align-center has-sm-font-size" style="text-transform:uppercase"><?php esc_html_e( 'Minutes', 'campaignpress' ); ?></p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
