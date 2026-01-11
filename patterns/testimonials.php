<?php
/**
 * Title: Testimonials Carousel
 * Slug: campaignpress/testimonials
 * Categories: campaign-office, text
 * Keywords: quote, testimonial, endorsement
 */
?>
<!-- wp:group {"align":"full","className":"cp-section cp-section--white","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull cp-section cp-section--white has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20)">
    <!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|12"}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
    <div class="wp-block-group alignwide" style="margin-bottom:var(--wp--preset--spacing--12)">
        <!-- wp:paragraph {"align":"center","className":"cp-section__label","style":{"typography":{"fontSize":"0.75rem","fontWeight":"700","letterSpacing":"0.15em","textTransform":"uppercase"}},"textColor":"secondary"} -->
        <p class="has-text-align-center cp-section__label has-secondary-color has-text-color" style="font-size:0.75rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase"><?php esc_html_e( 'COMMUNITY VOICES', 'campaign-office' ); ?></p>
        <!-- /wp:paragraph -->

        <!-- wp:heading {"textAlign":"center","level":2,"className":"cp-section__title","fontFamily":"display","style":{"typography":{"fontSize":"2.5rem","fontWeight":"700"}}} -->
        <h2 class="wp-block-heading has-text-align-center cp-section__title has-display-font-family" style="font-size:2.5rem;font-weight:700"><?php esc_html_e( 'Why We Support Thomas', 'campaign-office' ); ?></h2>
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
            <!-- wp:group {"className":"cp-testimonial","style":{"spacing":{"padding":{"top":"2.5rem","right":"2.5rem","bottom":"2.5rem","left":"2.5rem"}},"border":{"left":{"color":"var:preset|color|secondary","width":"4px"}}},"backgroundColor":"neutral-50","layout":{"type":"default"}} -->
            <div class="wp-block-group cp-testimonial has-neutral-50-background-color has-background" style="border-left-color:var(--wp--preset--color--secondary);border-left-width:4px;padding-top:2.5rem;padding-right:2.5rem;padding-bottom:2.5rem;padding-left:2.5rem">
                <!-- wp:paragraph {"className":"cp-testimonial__quote","style":{"typography":{"fontStyle":"italic","lineHeight":"1.8"}},"textColor":"neutral-700"} -->
                <p class="cp-testimonial__quote has-neutral-700-color has-text-color" style="font-style:italic;line-height:1.8"><?php esc_html_e( 'Thomas actually understands what it’s like to meet a payroll. He’s the first candidate I’ve seen who prioritizes our local economy.', 'campaign-office' ); ?></p>
                <!-- /wp:paragraph -->
                
                <!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
                <div class="wp-block-group">
                    <!-- wp:image {"width":48,"height":48,"linkDestination":"none","className":"is-style-rounded"} -->
                    <figure class="wp-block-image is-resized is-style-rounded"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/avatar-placeholder.png' ); ?>" alt="" width="48" height="48"/></figure>
                    <!-- /wp:image -->
                    <!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained"}} -->
                    <div class="wp-block-group">
                        <!-- wp:paragraph {"className":"cp-testimonial__author","style":{"typography":{"fontWeight":"700"},"spacing":{"margin":{"bottom":"0"}}},"textColor":"primary"} -->
                        <p class="cp-testimonial__author has-primary-color has-text-color" style="margin-bottom:0;font-weight:700"><?php esc_html_e( 'Sarah Jenkins', 'campaign-office' ); ?></p>
                        <!-- /wp:paragraph -->
                        <!-- wp:paragraph {"className":"cp-testimonial__role","style":{"typography":{"fontSize":"0.75rem","textTransform":"uppercase","letterSpacing":"0.1em"}},"textColor":"neutral-500"} -->
                        <p class="cp-testimonial__role has-neutral-500-color has-text-color" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em"><?php esc_html_e( 'Small Business Owner', 'campaign-office' ); ?></p>
                        <!-- /wp:paragraph -->
                    </div>
                    <!-- /wp:group -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:group {"className":"cp-testimonial","style":{"spacing":{"padding":{"top":"2.5rem","right":"2.5rem","bottom":"2.5rem","left":"2.5rem"}},"border":{"left":{"color":"var:preset|color|secondary","width":"4px"}}},"backgroundColor":"neutral-50","layout":{"type":"default"}} -->
            <div class="wp-block-group cp-testimonial has-neutral-50-background-color has-background" style="border-left-color:var(--wp--preset--color--secondary);border-left-width:4px;padding-top:2.5rem;padding-right:2.5rem;padding-bottom:2.5rem;padding-left:2.5rem">
                <!-- wp:paragraph {"className":"cp-testimonial__quote","style":{"typography":{"fontStyle":"italic","lineHeight":"1.8"}},"textColor":"neutral-700"} -->
                <p class="cp-testimonial__quote has-neutral-700-color has-text-color" style="font-style:italic;line-height:1.8"><?php esc_html_e( 'As a veteran, I look for leaders with integrity. Thomas has shown time and again that he stands by his word and his country.', 'campaign-office' ); ?></p>
                <!-- /wp:paragraph -->
                
                <!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
                <div class="wp-block-group">
                    <!-- wp:image {"width":48,"height":48,"linkDestination":"none","className":"is-style-rounded"} -->
                    <figure class="wp-block-image is-resized is-style-rounded"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/avatar-placeholder.png' ); ?>" alt="" width="48" height="48"/></figure>
                    <!-- /wp:image -->
                    <!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained"}} -->
                    <div class="wp-block-group">
                        <!-- wp:paragraph {"className":"cp-testimonial__author","style":{"typography":{"fontWeight":"700"},"spacing":{"margin":{"bottom":"0"}}},"textColor":"primary"} -->
                        <p class="cp-testimonial__author has-primary-color has-text-color" style="margin-bottom:0;font-weight:700"><?php esc_html_e( 'Michael Rodriguez', 'campaign-office' ); ?></p>
                        <!-- /wp:paragraph -->
                        <!-- wp:paragraph {"className":"cp-testimonial__role","style":{"typography":{"fontSize":"0.75rem","textTransform":"uppercase","letterSpacing":"0.1em"}},"textColor":"neutral-500"} -->
                        <p class="cp-testimonial__role has-neutral-500-color has-text-color" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em"><?php esc_html_e( 'Army Veteran', 'campaign-office' ); ?></p>
                        <!-- /wp:paragraph -->
                    </div>
                    <!-- /wp:group -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:group {"className":"cp-testimonial","style":{"spacing":{"padding":{"top":"2.5rem","right":"2.5rem","bottom":"2.5rem","left":"2.5rem"}},"border":{"left":{"color":"var:preset|color|secondary","width":"4px"}}},"backgroundColor":"neutral-50","layout":{"type":"default"}} -->
            <div class="wp-block-group cp-testimonial has-neutral-50-background-color has-background" style="border-left-color:var(--wp--preset--color--secondary);border-left-width:4px;padding-top:2.5rem;padding-right:2.5rem;padding-bottom:2.5rem;padding-left:2.5rem">
                <!-- wp:paragraph {"className":"cp-testimonial__quote","style":{"typography":{"fontStyle":"italic","lineHeight":"1.8"}},"textColor":"neutral-700"} -->
                <p class="cp-testimonial__quote has-neutral-700-color has-text-color" style="font-style:italic;line-height:1.8"><?php esc_html_e( 'His commitment to education and parental rights is exactly what our school district needs. He’s a voice for common sense.', 'campaign-office' ); ?></p>
                <!-- /wp:paragraph -->
                
                <!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
                <div class="wp-block-group">
                    <!-- wp:image {"width":48,"height":48,"linkDestination":"none","className":"is-style-rounded"} -->
                    <figure class="wp-block-image is-resized is-style-rounded"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/avatar-placeholder.png' ); ?>" alt="" width="48" height="48"/></figure>
                    <!-- /wp:image -->
                    <!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained"}} -->
                    <div class="wp-block-group">
                        <!-- wp:paragraph {"className":"cp-testimonial__author","style":{"typography":{"fontWeight":"700"},"spacing":{"margin":{"bottom":"0"}}},"textColor":"primary"} -->
                        <p class="cp-testimonial__author has-primary-color has-text-color" style="margin-bottom:0;font-weight:700"><?php esc_html_e( 'Diane Weber', 'campaign-office' ); ?></p>
                        <!-- /wp:paragraph -->
                        <!-- wp:paragraph {"className":"cp-testimonial__role","style":{"typography":{"fontSize":"0.75rem","textTransform":"uppercase","letterSpacing":"0.1em"}},"textColor":"neutral-500"} -->
                        <p class="cp-testimonial__role has-neutral-500-color has-text-color" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em"><?php esc_html_e( 'Teacher & Parent', 'campaign-office' ); ?></p>
                        <!-- /wp:paragraph -->
                    </div>
                    <!-- /wp:group -->
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
