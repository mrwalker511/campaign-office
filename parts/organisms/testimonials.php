<?php
/**
 * Layout Module: Testimonials
 * 
 * Testimonials section with left border accent styling.
 * New design system: background color, left red border, italic quotes.
 */
?>
<section class="cp-section cp-section--gray" aria-label="<?php esc_attr_e( 'Testimonials', 'campaign-office' ); ?>">
    <div class="cp-container">
        <!-- Section Header -->
        <div class="cp-text-center cp-mb-4">
            <span class="cp-section__label"><?php esc_html_e( 'What People Say', 'campaign-office' ); ?></span>
            <h2 class="cp-section__title"><?php esc_html_e( 'Endorsements', 'campaign-office' ); ?></h2>
            <div class="cp-divider"></div>
        </div>
        
        <!-- Testimonials Grid -->
        <div class="cp-grid cp-grid--2">
            <!-- Testimonial 1 -->
            <div class="cp-testimonial">
                <p class="cp-testimonial__quote">
                    "<?php esc_html_e( 'The clear choice for our future. Honest, hardworking, and dedicated to the values that make our community strong.', 'campaign-office' ); ?>"
                </p>
                <p class="cp-testimonial__author"><?php esc_html_e( 'Mayor Jane Doe', 'campaign-office' ); ?></p>
                <p class="cp-testimonial__role"><?php esc_html_e( 'Cityville', 'campaign-office' ); ?></p>
            </div>
            
            <!-- Testimonial 2 -->
            <div class="cp-testimonial">
                <p class="cp-testimonial__quote">
                    "<?php esc_html_e( 'A leader who listens. I\'m proud to support this campaign and the vision for our district.', 'campaign-office' ); ?>"
                </p>
                <p class="cp-testimonial__author"><?php esc_html_e( 'John Smith', 'campaign-office' ); ?></p>
                <p class="cp-testimonial__role"><?php esc_html_e( 'Teacher & Community Leader', 'campaign-office' ); ?></p>
            </div>
        </div>
    </div>
</section>
