<?php
/**
 * Layout Module: Hero
 * 
 * Full width hero section with headline, subhead, and dual CTAs.
 * New design system: left-aligned content, gradient overlay, badge accent.
 */
?>
<section class="cp-hero" aria-label="Hero">
    <!-- Background Image -->
    <div class="cp-hero__bg" style="background-image: url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-placeholder.jpg' ); ?>');"></div>
    
    <!-- Gradient Overlay -->
    <div class="cp-hero__overlay"></div>
    
    <!-- Content -->
    <div class="cp-container">
        <div class="cp-hero__content">
            <!-- Badge -->
            <span class="cp-hero__badge">
                <?php esc_html_e( 'Official Campaign Site', 'campaignpress' ); ?>
            </span>
            
            <!-- Headline -->
            <h1 class="cp-hero__title">
                <?php esc_html_e( 'Restoring Faith', 'campaignpress' ); ?><br>
                <span class="cp-hero__title-italic"><?php esc_html_e( 'In America', 'campaignpress' ); ?></span>
            </h1>
            
            <!-- Subtitle -->
            <p class="cp-hero__subtitle">
                <?php esc_html_e( 'Join the movement dedicated to protecting our constitutional rights, strengthening our economy, and securing a brighter future for our families.', 'campaignpress' ); ?>
            </p>
            
            <!-- CTA Group -->
            <div class="cp-hero__actions">
                <a href="#volunteer" class="cp-btn cp-btn--primary">
                    <?php esc_html_e( 'Join the Movement', 'campaignpress' ); ?>
                </a>
                <a href="#about" class="cp-btn cp-btn--outline">
                    <?php esc_html_e( 'Watch the Video', 'campaignpress' ); ?>
                </a>
            </div>
        </div>
    </div>
</section>
