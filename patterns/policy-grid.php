<?php
/**
 * Title: Policy Grid
 * Slug: campaignpress/policy-grid
 * Categories: campaignpress, text
 * Keywords: policy, issues, grid, principles
 */
?>
<!-- wp:group {"align":"full","className":"cp-section cp-section--white","style":{"spacing":{"padding":{"top":"var:preset|spacing|16","bottom":"var:preset|spacing|16"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull cp-section cp-section--white has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--16);padding-bottom:var(--wp--preset--spacing--16)">
    <!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|10"}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
    <div class="wp-block-group alignwide" style="margin-bottom:var(--wp--preset--spacing--10)">
        <!-- wp:paragraph {"align":"center","className":"cp-section__label","style":{"typography":{"fontSize":"0.75rem","fontWeight":"700","letterSpacing":"0.15em","textTransform":"uppercase"}},"textColor":"secondary"} -->
        <p class="has-text-align-center cp-section__label has-secondary-color has-text-color" style="font-size:0.75rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase"><?php esc_html_e( 'Our Core Principles', 'campaignpress' ); ?></p>
        <!-- /wp:paragraph -->

        <!-- wp:heading {"textAlign":"center","level":2,"className":"cp-section__title","fontFamily":"display"} -->
        <h2 class="wp-block-heading has-text-align-center cp-section__title has-display-font-family"><?php esc_html_e( 'Values That Define Us', 'campaignpress' ); ?></h2>
        <!-- /wp:heading -->

        <!-- wp:group {"className":"cp-divider","style":{"spacing":{"margin":{"top":"1.5rem"}},"layout":{"selfStretch":"fixed","flexSize":"5rem"},"dimensions":{"minHeight":"4px"}},"backgroundColor":"gold","layout":{"type":"default"}} -->
        <div class="wp-block-group cp-divider has-gold-background-color has-background" style="margin-top:1.5rem;min-height:4px"></div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:group -->

    <!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"2rem"}}}} -->
    <div class="wp-block-columns alignwide">
        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:group {"className":"cp-card","style":{"spacing":{"padding":{"top":"2.5rem","right":"2.5rem","bottom":"2.5rem","left":"2.5rem"}},"border":{"top":{"color":"var:preset|color|primary","width":"4px"},"right":{},"bottom":{},"left":{}}},"backgroundColor":"neutral-50","layout":{"type":"default"}} -->
            <div class="wp-block-group cp-card has-neutral-50-background-color has-background" style="border-top-color:var(--wp--preset--color--primary);border-top-width:4px;padding-top:2.5rem;padding-right:2.5rem;padding-bottom:2.5rem;padding-left:2.5rem">
                <!-- wp:heading {"level":3,"className":"cp-card__title","fontFamily":"display"} -->
                <h3 class="wp-block-heading cp-card__title has-display-font-family"><?php esc_html_e( 'Individual Liberty', 'campaignpress' ); ?></h3>
                <!-- /wp:heading -->
                <!-- wp:paragraph {"className":"cp-card__text","textColor":"neutral-600"} -->
                <p class="cp-card__text has-neutral-600-color has-text-color"><?php esc_html_e( 'Defending our constitutional rights against government overreach and preserving freedom for future generations.', 'campaignpress' ); ?></p>
                <!-- /wp:paragraph -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:group {"className":"cp-card","style":{"spacing":{"padding":{"top":"2.5rem","right":"2.5rem","bottom":"2.5rem","left":"2.5rem"}},"border":{"top":{"color":"var:preset|color|primary","width":"4px"},"right":{},"bottom":{},"left":{}}},"backgroundColor":"neutral-50","layout":{"type":"default"}} -->
            <div class="wp-block-group cp-card has-neutral-50-background-color has-background" style="border-top-color:var(--wp--preset--color--primary);border-top-width:4px;padding-top:2.5rem;padding-right:2.5rem;padding-bottom:2.5rem;padding-left:2.5rem">
                <!-- wp:heading {"level":3,"className":"cp-card__title","fontFamily":"display"} -->
                <h3 class="wp-block-heading cp-card__title has-display-font-family"><?php esc_html_e( 'Economic Opportunity', 'campaignpress' ); ?></h3>
                <!-- /wp:heading -->
                <!-- wp:paragraph {"className":"cp-card__text","textColor":"neutral-600"} -->
                <p class="cp-card__text has-neutral-600-color has-text-color"><?php esc_html_e( 'Cutting red tape, lowering taxes, and empowering small businesses to create jobs and drive innovation.', 'campaignpress' ); ?></p>
                <!-- /wp:paragraph -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:group {"className":"cp-card","style":{"spacing":{"padding":{"top":"2.5rem","right":"2.5rem","bottom":"2.5rem","left":"2.5rem"}},"border":{"top":{"color":"var:preset|color|primary","width":"4px"},"right":{},"bottom":{},"left":{}}},"backgroundColor":"neutral-50","layout":{"type":"default"}} -->
            <div class="wp-block-group cp-card has-neutral-50-background-color has-background" style="border-top-color:var(--wp--preset--color--primary);border-top-width:4px;padding-top:2.5rem;padding-right:2.5rem;padding-bottom:2.5rem;padding-left:2.5rem">
                <!-- wp:heading {"level":3,"className":"cp-card__title","fontFamily":"display"} -->
                <h3 class="wp-block-heading cp-card__title has-display-font-family"><?php esc_html_e( 'Strong Families', 'campaignpress' ); ?></h3>
                <!-- /wp:heading -->
                <!-- wp:paragraph {"className":"cp-card__text","textColor":"neutral-600"} -->
                <p class="cp-card__text has-neutral-600-color has-text-color"><?php esc_html_e( 'Protecting parental rights and ensuring our education system focuses on excellence, not indoctrination.', 'campaignpress' ); ?></p>
                <!-- /wp:paragraph -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
