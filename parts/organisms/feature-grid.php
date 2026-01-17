<?php
/**
 * Layout Module: Feature Grid
 * 
 * 3-Column grid for displaying key policy points or features.
 * New design system: cards with navy top border, hover effects.
 */
?>
<section class="cp-section cp-section--gray" aria-label="<?php esc_attr_e( 'Key Priorities', 'campaignpress' ); ?>">
    <div class="cp-container">
        <!-- Section Header -->
        <div class="cp-text-center cp-mb-4">
            <span class="cp-section__label"><?php esc_html_e( 'Our Focus', 'campaignpress' ); ?></span>
            <h2 class="cp-section__title"><?php esc_html_e( 'Key Priorities', 'campaignpress' ); ?></h2>
            <div class="cp-divider"></div>
            <p style="color: var(--ds-text-muted); max-width: 600px; margin: 0 auto;">
                <?php esc_html_e( 'We are focused on the issues that matter most to our community. Here is where we stand.', 'campaignpress' ); ?>
            </p>
        </div>

        <!-- Grid -->
        <div class="cp-grid cp-grid--3">
            <!-- Feature 1 -->
            <article class="cp-card">
                <h3 class="cp-card__title"><?php esc_html_e( 'Education First', 'campaignpress' ); ?></h3>
                <p class="cp-card__text">
                    <?php esc_html_e( 'Investing in our schools and teachers to ensure every child has access to quality education.', 'campaignpress' ); ?>
                </p>
                <a href="#" style="color: var(--ds-secondary); font-weight: 600; text-decoration: none;">
                    <?php esc_html_e( 'Read Policy →', 'campaignpress' ); ?>
                </a>
            </article>

            <!-- Feature 2 -->
            <article class="cp-card">
                <h3 class="cp-card__title"><?php esc_html_e( 'Sustainable Growth', 'campaignpress' ); ?></h3>
                <p class="cp-card__text">
                    <?php esc_html_e( 'Promoting economic development that respects our environment and local community values.', 'campaignpress' ); ?>
                </p>
                <a href="#" style="color: var(--ds-secondary); font-weight: 600; text-decoration: none;">
                    <?php esc_html_e( 'Read Policy →', 'campaignpress' ); ?>
                </a>
            </article>

            <!-- Feature 3 -->
            <article class="cp-card">
                <h3 class="cp-card__title"><?php esc_html_e( 'Community Safety', 'campaignpress' ); ?></h3>
                <p class="cp-card__text">
                    <?php esc_html_e( 'Working together with law enforcement and community leaders to keep our neighborhoods safe.', 'campaignpress' ); ?>
                </p>
                <a href="#" style="color: var(--ds-secondary); font-weight: 600; text-decoration: none;">
                    <?php esc_html_e( 'Read Policy →', 'campaignpress' ); ?>
                </a>
            </article>
        </div>
    </div>
</section>
