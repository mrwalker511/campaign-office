<?php
/**
 * Layout Module: CTA (Call to Action)
 * 
 * High impact band for conversions (Volunteer, Donate, Join).
 * New design system: secondary (red) background, white/navy buttons.
 */
?>
<section class="cp-section cp-section--dark" style="background-color: var(--ds-secondary);" aria-label="Call to Action">
    <div class="cp-container cp-text-center">
        <h2 class="cp-section__title" style="color: var(--wp--preset--color--white);">
            <?php esc_html_e( 'Ready to Make a Difference?', 'campaign-office' ); ?>
        </h2>
        <p class="cp-hero__subtitle" style="text-align: center; max-width: 600px; margin-left: auto; margin-right: auto;">
            <?php esc_html_e( 'Our campaign is powered by people like you. Join us in building a community that works for everyone.', 'campaign-office' ); ?>
        </p>
        
        <div class="cp-hero__actions" style="justify-content: center;">
            <a href="#volunteer" class="cp-btn cp-btn--outline">
                <?php esc_html_e( 'Volunteer With Us', 'campaign-office' ); ?>
            </a>
            <a href="#donate" class="cp-btn cp-btn--dark">
                <?php esc_html_e( 'Donate Now', 'campaign-office' ); ?>
            </a>
        </div>
        
        <p style="font-size: 0.875rem; color: rgba(255,255,255,0.7); margin-top: 1.5rem;">
            <?php esc_html_e( 'Join 1,200+ supporters in our district.', 'campaign-office' ); ?>
        </p>
    </div>
</section>
