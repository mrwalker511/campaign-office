<?php
/**
 * Layout Module: Testimonials
 * 
 * Testimonials section with left border accent styling.
 * New design system: background color, left red border, italic quotes.
 */
?>
<section class="cp-section cp-section--gray" aria-label="<?php esc_attr_e( 'Testimonials', 'campaignpress' ); ?>">
    <div class="cp-container">
        <!-- Section Header -->
        <div class="cp-text-center cp-mb-4">
            <span class="cp-section__label"><?php esc_html_e( 'What People Say', 'campaignpress' ); ?></span>
            <h2 class="cp-section__title"><?php esc_html_e( 'Endorsements', 'campaignpress' ); ?></h2>
            <div class="cp-divider"></div>
        </div>
        
        <!-- Testimonials Grid -->
        <div class="cp-grid cp-grid--2">
            <!-- Testimonial 1 -->
            <div class="cp-testimonial">
                <p class="cp-testimonial__quote">
                    "<?php esc_html_e( 'The clear choice for our future. Honest, hardworking, and dedicated to the values that make our community strong.', 'campaignpress' ); ?>"
                </p>
                <p class="cp-testimonial__author"><?php esc_html_e( 'Mayor Jane Doe', 'campaignpress' ); ?></p>
                <p class="cp-testimonial__role"><?php esc_html_e( 'Cityville', 'campaignpress' ); ?></p>
            </div>
            
            <!-- Testimonial 2 -->
            <div class="cp-testimonial">
                <p class="cp-testimonial__quote">
                    "<?php esc_html_e( 'A leader who listens. I\'m proud to support this campaign and the vision for our district.', 'campaignpress' ); ?>"
                </p>
                <p class="cp-testimonial__author"><?php esc_html_e( 'John Smith', 'campaignpress' ); ?></p>
                <p class="cp-testimonial__role"><?php esc_html_e( 'Teacher & Community Leader', 'campaignpress' ); ?></p>
            </div>
        </div>
    </div>
</section>
