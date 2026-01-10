<?php
/**
 * Layout Module: Hero - Center Aligned Variant
 * 
 * Large H1, subheadline, two buttons (primary/secondary), background image overlay.
 * New design system: navy background, centered content, serif headings.
 */
?>
<section class="cp-hero" style="justify-content: center;" aria-label="Hero Center">
    <!-- Background Image -->
    <div class="cp-hero__bg" style="background-image: url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-placeholder.jpg' ); ?>');"></div>
    
    <!-- Gradient Overlay (center fade) -->
    <div class="cp-hero__overlay" style="background: linear-gradient(to bottom, rgba(20,33,61,0.8) 0%, rgba(20,33,61,0.7) 50%, rgba(20,33,61,0.9) 100%);"></div>
    
    <!-- Content -->
    <div class="cp-container cp-text-center" style="max-width: 900px;">
        <div class="cp-hero__content" style="max-width: 100%; text-align: center;">
            <!-- Badge -->
            <span class="cp-hero__badge" style="margin-left: auto; margin-right: auto;">
                <?php esc_html_e( 'Campaign 2026', 'campaign-office' ); ?>
            </span>
            
            <!-- Headline -->
            <h1 class="cp-hero__title" style="text-align: center;">
                <?php esc_html_e( 'Leadership That Puts', 'campaign-office' ); ?><br>
                <span class="cp-hero__title-italic"><?php esc_html_e( 'People First', 'campaign-office' ); ?></span>
            </h1>
            
            <!-- Subtitle -->
            <p class="cp-hero__subtitle" style="text-align: center; max-width: 700px; margin-left: auto; margin-right: auto;">
                <?php esc_html_e( 'Building stronger communities through transparency, accountability, and bold action for our district.', 'campaign-office' ); ?>
            </p>
            
            <!-- CTA Group -->
            <div class="cp-hero__actions" style="justify-content: center;">
                <a href="#join" class="cp-btn cp-btn--primary">
                    <?php esc_html_e( 'Join the Movement', 'campaign-office' ); ?>
                </a>
                <a href="#platform" class="cp-btn cp-btn--outline">
                    <?php esc_html_e( 'Our Platform', 'campaign-office' ); ?>
                </a>
            </div>
        </div>
    </div>
</section>
