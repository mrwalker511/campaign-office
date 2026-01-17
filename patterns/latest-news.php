<?php
/**
 * Title: Latest News Grid
 * Slug: campaignpress/latest-news
 * Categories: campaignpress, query
 * Keywords: news, blog, trail, press
 */
?>
<!-- wp:group {"align":"full","className":"cp-section cp-section--white","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull cp-section cp-section--white has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20)">
    <!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|12"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
    <div class="wp-block-group alignwide" style="margin-bottom:var(--wp--preset--spacing--12)">
        <!-- wp:group {"style":{"spacing":{"blockGap":"0.5rem"}},"layout":{"type":"constrained"}} -->
        <div class="wp-block-group">
            <!-- wp:paragraph {"className":"cp-section__label","style":{"typography":{"fontSize":"0.75rem","fontWeight":"700","letterSpacing":"0.15em","textTransform":"uppercase"}},"textColor":"secondary"} -->
            <p class="cp-section__label has-secondary-color has-text-color" style="font-size:0.75rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase"><?php esc_html_e( 'ON THE TRAIL', 'campaignpress' ); ?></p>
            <!-- /wp:paragraph -->

            <!-- wp:heading {"level":2,"className":"cp-section__title","fontFamily":"display","style":{"typography":{"fontSize":"2.5rem","fontWeight":"700"},"spacing":{"margin":{"bottom":"0"}}}} -->
            <h2 class="wp-block-heading cp-section__title has-display-font-family" style="margin-bottom:0;font-size:2.5rem;font-weight:700"><?php esc_html_e( 'Latest News', 'campaignpress' ); ?></h2>
            <!-- /wp:heading -->
        </div>
        <!-- /wp:group -->

        <!-- wp:paragraph {"className":"cp-link-with-icon","style":{"typography":{"fontSize":"0.875rem","fontWeight":"700","textTransform":"uppercase","letterSpacing":"0.05em"}}} -->
        <p class="cp-link-with-icon" style="font-size:0.875rem;font-weight:700;letter-spacing:0.05em;text-transform:uppercase"><a href="#"><?php esc_html_e( 'VIEW ALL NEWS', 'campaignpress' ); ?> <span class="icon">→</span></a></p>
        <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->

    <!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"2rem"}}}} -->
    <div class="wp-block-columns alignwide">
        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:group {"className":"cp-news-card","style":{"spacing":{"blockGap":"1.5rem"}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group cp-news-card">
                <!-- wp:group {"style":{"dimensions":{"minHeight":"250px"}},"backgroundColor":"neutral-200","layout":{"type":"constrained"}} -->
                <div class="wp-block-group has-neutral-200-background-color has-background" style="min-height:250px">
                    <!-- wp:paragraph {"style":{"spacing":{"padding":{"top":"1rem","left":"1rem"}},"typography":{"fontSize":"0.75rem","fontWeight":"700","textTransform":"uppercase","letterSpacing":"0.1em"}},"backgroundColor":"white","textColor":"primary"} -->
                    <p class="has-primary-color has-white-background-color has-text-color has-background" style="padding-top:1rem;padding-left:1rem;font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;max-width:max-content"><?php esc_html_e( 'PRESS RELEASE', 'campaignpress' ); ?></p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->

                <!-- wp:group {"style":{"spacing":{"blockGap":"0.75rem"}},"layout":{"type":"constrained"}} -->
                <div class="wp-block-group">
                    <!-- wp:paragraph {"style":{"typography":{"fontSize":"0.875rem","textTransform":"uppercase","letterSpacing":"0.05em"}},"textColor":"neutral-500"} -->
                    <p class="has-neutral-500-color has-text-color" style="font-size:0.875rem;letter-spacing:0.05em;text-transform:uppercase"><?php esc_html_e( 'OCT 12, 2025', 'campaignpress' ); ?></p>
                    <!-- /wp:paragraph -->

                    <!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.5rem","fontWeight":"700"}},"fontFamily":"display"} -->
                    <h3 class="wp-block-heading has-display-font-family" style="font-size:1.5rem;font-weight:700"><?php esc_html_e( 'Harrison Announces Plan for Energy Independence', 'campaignpress' ); ?></h3>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.6"}},"textColor":"neutral-600"} -->
                    <p class="has-neutral-600-color has-text-color" style="line-height:1.6"><?php esc_html_e( 'A bold new strategy to lower costs for families and secure our nation's energy future.', 'campaignpress' ); ?></p>
                    <!-- /wp:paragraph -->

                    <!-- wp:paragraph {"style":{"typography":{"fontSize":"0.875rem","fontWeight":"700","textTransform":"uppercase","letterSpacing":"0.05em"}},"textColor":"secondary"} -->
                    <p class="has-secondary-color has-text-color" style="font-size:0.875rem;font-weight:700;letter-spacing:0.05em;text-transform:uppercase"><a href="#" style="color:inherit;text-decoration:none"><?php esc_html_e( 'READ MORE', 'campaignpress' ); ?> →</a></p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:group {"className":"cp-news-card","style":{"spacing":{"blockGap":"1.5rem"}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group cp-news-card">
                <!-- wp:group {"style":{"dimensions":{"minHeight":"250px"}},"backgroundColor":"neutral-200","layout":{"type":"constrained"}} -->
                <div class="wp-block-group has-neutral-200-background-color has-background" style="min-height:250px">
                    <!-- wp:paragraph {"style":{"spacing":{"padding":{"top":"1rem","left":"1rem"}},"typography":{"fontSize":"0.75rem","fontWeight":"700","textTransform":"uppercase","letterSpacing":"0.1em"}},"backgroundColor":"white","textColor":"primary"} -->
                    <p class="has-primary-color has-white-background-color has-text-color has-background" style="padding-top:1rem;padding-left:1rem;font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;max-width:max-content"><?php esc_html_e( 'PRESS RELEASE', 'campaignpress' ); ?></p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->

                <!-- wp:group {"style":{"spacing":{"blockGap":"0.75rem"}},"layout":{"type":"constrained"}} -->
                <div class="wp-block-group">
                    <!-- wp:paragraph {"style":{"typography":{"fontSize":"0.875rem","textTransform":"uppercase","letterSpacing":"0.05em"}},"textColor":"neutral-500"} -->
                    <p class="has-neutral-500-color has-text-color" style="font-size:0.875rem;letter-spacing:0.05em;text-transform:uppercase"><?php esc_html_e( 'OCT 08, 2025', 'campaignpress' ); ?></p>
                    <!-- /wp:paragraph -->

                    <!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.5rem","fontWeight":"700"}},"fontFamily":"display"} -->
                    <h3 class="wp-block-heading has-display-font-family" style="font-size:1.5rem;font-weight:700"><?php esc_html_e( 'Endorsed by Small Business Alliance', 'campaignpress' ); ?></h3>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.6"}},"textColor":"neutral-600"} -->
                    <p class="has-neutral-600-color has-text-color" style="line-height:1.6"><?php esc_html_e( 'Local business leaders rally behind Harrison's economic growth agenda.', 'campaignpress' ); ?></p>
                    <!-- /wp:paragraph -->

                    <!-- wp:paragraph {"style":{"typography":{"fontSize":"0.875rem","fontWeight":"700","textTransform":"uppercase","letterSpacing":"0.05em"}},"textColor":"secondary"} -->
                    <p class="has-secondary-color has-text-color" style="font-size:0.875rem;font-weight:700;letter-spacing:0.05em;text-transform:uppercase"><a href="#" style="color:inherit;text-decoration:none"><?php esc_html_e( 'READ MORE', 'campaignpress' ); ?> →</a></p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:group {"className":"cp-news-card","style":{"spacing":{"blockGap":"1.5rem"}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group cp-news-card">
                <!-- wp:group {"style":{"dimensions":{"minHeight":"250px"}},"backgroundColor":"neutral-200","layout":{"type":"constrained"}} -->
                <div class="wp-block-group has-neutral-200-background-color has-background" style="min-height:250px">
                    <!-- wp:paragraph {"style":{"spacing":{"padding":{"top":"1rem","left":"1rem"}},"typography":{"fontSize":"0.75rem","fontWeight":"700","textTransform":"uppercase","letterSpacing":"0.1em"}},"backgroundColor":"white","textColor":"primary"} -->
                    <p class="has-primary-color has-white-background-color has-text-color has-background" style="padding-top:1rem;padding-left:1rem;font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;max-width:max-content"><?php esc_html_e( 'PRESS RELEASE', 'campaignpress' ); ?></p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->

                <!-- wp:group {"style":{"spacing":{"blockGap":"0.75rem"}},"layout":{"type":"constrained"}} -->
                <div class="wp-block-group">
                    <!-- wp:paragraph {"style":{"typography":{"fontSize":"0.875rem","textTransform":"uppercase","letterSpacing":"0.05em"}},"textColor":"neutral-500"} -->
                    <p class="has-neutral-500-color has-text-color" style="font-size:0.875rem;letter-spacing:0.05em;text-transform:uppercase"><?php esc_html_e( 'OCT 05, 2025', 'campaignpress' ); ?></p>
                    <!-- /wp:paragraph -->

                    <!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.5rem","fontWeight":"700"}},"fontFamily":"display"} -->
                    <h3 class="wp-block-heading has-display-font-family" style="font-size:1.5rem;font-weight:700"><?php esc_html_e( 'Rally in the Park Draws Record Crowds', 'campaignpress' ); ?></h3>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.6"}},"textColor":"neutral-600"} -->
                    <p class="has-neutral-600-color has-text-color" style="line-height:1.6"><?php esc_html_e( 'Thousands gathered this weekend to hear Thomas speak about the importance of community.', 'campaignpress' ); ?></p>
                    <!-- /wp:paragraph -->

                    <!-- wp:paragraph {"style":{"typography":{"fontSize":"0.875rem","fontWeight":"700","textTransform":"uppercase","letterSpacing":"0.05em"}},"textColor":"secondary"} -->
                    <p class="has-secondary-color has-text-color" style="font-size:0.875rem;font-weight:700;letter-spacing:0.05em;text-transform:uppercase"><a href="#" style="color:inherit;text-decoration:none"><?php esc_html_e( 'READ MORE', 'campaignpress' ); ?> →</a></p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
