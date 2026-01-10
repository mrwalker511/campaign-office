<?php
/**
 * Title: Testimonials Carousel
 * Slug: campaignpress/testimonials
 * Categories: campaign-office, text
 * Keywords: quote, testimonial, endorsement
 */
?>
<!-- wp:group {"align":"full","className":"cp-section cp-section--gray","style":{"spacing":{"padding":{"top":"var:preset|spacing|16","bottom":"var:preset|spacing|16"}}},"backgroundColor":"neutral-50","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull cp-section cp-section--gray has-neutral-50-background-color has-background" style="padding-top:var(--wp--preset--spacing--16);padding-bottom:var(--wp--preset--spacing--16)">
    <!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|10"}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
    <div class="wp-block-group alignwide" style="margin-bottom:var(--wp--preset--spacing--10)">
        <!-- wp:paragraph {"align":"center","className":"cp-section__label","style":{"typography":{"fontSize":"0.75rem","fontWeight":"700","letterSpacing":"0.15em","textTransform":"uppercase"}},"textColor":"secondary"} -->
        <p class="has-text-align-center cp-section__label has-secondary-color has-text-color" style="font-size:0.75rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase"><?php esc_html_e( 'What People Say', 'campaign-office' ); ?></p>
        <!-- /wp:paragraph -->

        <!-- wp:heading {"textAlign":"center","level":2,"className":"cp-section__title","fontFamily":"display"} -->
        <h2 class="wp-block-heading has-text-align-center cp-section__title has-display-font-family"><?php esc_html_e( 'Endorsements', 'campaign-office' ); ?></h2>
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
            <!-- wp:group {"className":"cp-testimonial","style":{"spacing":{"padding":{"top":"1.5rem","right":"1.5rem","bottom":"1.5rem","left":"1.5rem"}},"border":{"left":{"color":"var:preset|color|secondary","width":"4px"}}},"backgroundColor":"white","layout":{"type":"default"}} -->
            <div class="wp-block-group cp-testimonial has-white-background-color has-background" style="border-left-color:var(--wp--preset--color--secondary);border-left-width:4px;padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem">
                <!-- wp:paragraph {"className":"cp-testimonial__quote","style":{"typography":{"fontStyle":"italic","lineHeight":"1.8"}}} -->
                <p class="cp-testimonial__quote" style="font-style:italic;line-height:1.8">"<?php esc_html_e( 'The clear choice for our future. Honest, hardworking, and dedicated to the values that make our community strong.', 'campaign-office' ); ?>"</p>
                <!-- /wp:paragraph -->
                
                <!-- wp:paragraph {"className":"cp-testimonial__author","style":{"typography":{"fontWeight":"700"},"spacing":{"margin":{"bottom":"0"}}},"textColor":"primary"} -->
                <p class="cp-testimonial__author has-primary-color has-text-color" style="margin-bottom:0;font-weight:700"><?php esc_html_e( 'Mayor Jane Doe', 'campaign-office' ); ?></p>
                <!-- /wp:paragraph -->
                
                <!-- wp:paragraph {"className":"cp-testimonial__role","style":{"typography":{"fontSize":"0.75rem","textTransform":"uppercase","letterSpacing":"0.1em"}},"textColor":"neutral-600"} -->
                <p class="cp-testimonial__role has-neutral-600-color has-text-color" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em"><?php esc_html_e( 'Cityville', 'campaign-office' ); ?></p>
                <!-- /wp:paragraph -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:group {"className":"cp-testimonial","style":{"spacing":{"padding":{"top":"1.5rem","right":"1.5rem","bottom":"1.5rem","left":"1.5rem"}},"border":{"left":{"color":"var:preset|color|secondary","width":"4px"}}},"backgroundColor":"white","layout":{"type":"default"}} -->
            <div class="wp-block-group cp-testimonial has-white-background-color has-background" style="border-left-color:var(--wp--preset--color--secondary);border-left-width:4px;padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem">
                <!-- wp:paragraph {"className":"cp-testimonial__quote","style":{"typography":{"fontStyle":"italic","lineHeight":"1.8"}}} -->
                <p class="cp-testimonial__quote" style="font-style:italic;line-height:1.8">"<?php esc_html_e( 'A leader who listens. I\'m proud to support this campaign and the vision for our district.', 'campaign-office' ); ?>"</p>
                <!-- /wp:paragraph -->
                
                <!-- wp:paragraph {"className":"cp-testimonial__author","style":{"typography":{"fontWeight":"700"},"spacing":{"margin":{"bottom":"0"}}},"textColor":"primary"} -->
                <p class="cp-testimonial__author has-primary-color has-text-color" style="margin-bottom:0;font-weight:700"><?php esc_html_e( 'John Smith', 'campaign-office' ); ?></p>
                <!-- /wp:paragraph -->
                
                <!-- wp:paragraph {"className":"cp-testimonial__role","style":{"typography":{"fontSize":"0.75rem","textTransform":"uppercase","letterSpacing":"0.1em"}},"textColor":"neutral-600"} -->
                <p class="cp-testimonial__role has-neutral-600-color has-text-color" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em"><?php esc_html_e( 'Teacher & Community Leader', 'campaign-office' ); ?></p>
                <!-- /wp:paragraph -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
