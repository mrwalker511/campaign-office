<?php
/**
 * Title: Progress Tracker
 * Slug: campaignpress/progress-tracker
 * Categories: campaignpress, featured
 * Keywords: progress, goal, tracking
 */
?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|8","bottom":"var:preset|spacing|8","left":"var:preset|spacing|8","right":"var:preset|spacing|8"}},"border":{"radius":"var:preset|custom|borderRadius|xl"}},"background":{"backgroundImage":{"url":"<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-placeholder.jpg' ); ?>","source":"file"},"dimRatio":80,"overlayColor":"primary-900"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-primary-900-background-color has-background" style="border-radius:var(--wp--preset--custom--border-radius--xl);padding-top:var(--wp--preset--spacing--8);padding-right:var(--wp--preset--spacing--8);padding-bottom:var(--wp--preset--spacing--8);padding-left:var(--wp--preset--spacing--8)">
    <span aria-hidden="true" class="wp-block-group__background has-background-dim-80 has-background-dim"></span>
    <!-- wp:heading {"textAlign":"center","style":{"color":{"text":"var:preset|color|white"}}} -->
    <h2 class="wp-block-heading has-text-align-center has-text-color" style="color:var(--wp--preset--color--white)"><?php esc_html_e( 'Campaign Goals', 'campaignpress' ); ?></h2>
    <!-- /wp:heading -->

    <!-- wp:columns {"style":{"spacing":{"blockGap":"2rem"}}} -->
    <div class="wp-block-columns">
        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"4rem","fontWeight":"800","lineHeight":"1"},"color":{"text":"var:preset|color|accent"}}} -->
            <p class="has-text-align-center has-text-color" style="color:var(--wp--preset--color--accent);font-size:4rem;font-weight:800;line-height:1">15K</p>
            <!-- /wp:paragraph -->
            <!-- wp:paragraph {"align":"center","style":{"color":{"text":"var:preset|color|white"}}} -->
            <p class="has-text-align-center has-text-color" style="color:var(--wp--preset--color--white)"><?php esc_html_e( 'Doors Knocked', 'campaignpress' ); ?></p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"4rem","fontWeight":"800","lineHeight":"1"},"color":{"text":"var:preset|color|accent"}}} -->
            <p class="has-text-align-center has-text-color" style="color:var(--wp--preset--color--accent);font-size:4rem;font-weight:800;line-height:1">5K</p>
            <!-- /wp:paragraph -->
            <!-- wp:paragraph {"align":"center","style":{"color":{"text":"var:preset|color|white"}}} -->
            <p class="has-text-align-center has-text-color" style="color:var(--wp--preset--color--white)"><?php esc_html_e( 'Calls Made', 'campaignpress' ); ?></p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"4rem","fontWeight":"800","lineHeight":"1"},"color":{"text":"var:preset|color|accent"}}} -->
            <p class="has-text-align-center has-text-color" style="color:var(--wp--preset--color--accent);font-size:4rem;font-weight:800;line-height:1">100%</p>
            <!-- /wp:paragraph -->
            <!-- wp:paragraph {"align":"center","style":{"color":{"text":"var:preset|color|white"}}} -->
            <p class="has-text-align-center has-text-color" style="color:var(--wp--preset--color--white)"><?php esc_html_e( 'Committed', 'campaignpress' ); ?></p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
