<?php
/**
 * Hero Commander Block - Server-side Render
 *
 * @package CampaignPress
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extract attributes with defaults
$headline = $attributes['headline'] ?? __( 'Fighting for Our Future', 'campaignpress' );
$subheadline = $attributes['subheadline'] ?? __( 'Together, we can build a better tomorrow', 'campaignpress' );
$typewriter_enabled = $attributes['typewriterEnabled'] ?? false;
$typewriter_texts = $attributes['typewriterTexts'] ?? array( 'Fighting for Our Future', 'Building a Better Tomorrow', 'Working for Change' );
$background_type = $attributes['backgroundType'] ?? 'color';
$background_image = $attributes['backgroundImage'] ?? array( 'url' => '', 'id' => 0, 'alt' => '' );
$background_video = $attributes['backgroundVideo'] ?? array( 'url' => '', 'id' => 0 );
$background_color = $attributes['backgroundColor'] ?? '#14213d';
$overlay_enabled = $attributes['overlayEnabled'] ?? false;
$overlay_color = $attributes['overlayColor'] ?? 'rgba(0, 0, 0, 0.5)';
$overlay_opacity = $attributes['overlayOpacity'] ?? 0.5;
$min_height = $attributes['minHeight'] ?? '600px';
$text_align = $attributes['textAlign'] ?? 'center';
$text_color = $attributes['textColor'] ?? '#ffffff';
$headline_size = $attributes['headlineSize'] ?? 'clamp(2rem, 5vw, 4rem)';
$subheadline_size = $attributes['subheadlineSize'] ?? '1.25rem';
$content_max_width = $attributes['contentMaxWidth'] ?? '800px';
$primary_cta = $attributes['primaryCTA'] ?? array( 'enabled' => true, 'text' => 'Donate Now', 'url' => '' );
$secondary_cta = $attributes['secondaryCTA'] ?? array( 'enabled' => true, 'text' => 'Get Involved', 'url' => '' );
$parallax_enabled = $attributes['parallaxEnabled'] ?? false;
$animation_style = $attributes['animationStyle'] ?? 'none';

// Build background styles
$bg_styles = array();
if ( 'color' === $background_type ) {
    $bg_styles[] = 'background-color: ' . esc_attr( $background_color );
} elseif ( 'image' === $background_type && ! empty( $background_image['url'] ) ) {
    $bg_styles[] = 'background-image: url(' . esc_url( $background_image['url'] ) . ')';
    $bg_styles[] = 'background-size: cover';
    $bg_styles[] = 'background-position: center';
} elseif ( 'video' === $background_type && ! empty( $background_video['url'] ) ) {
    $bg_styles[] = 'background-color: #000000';
}
$bg_style_attr = ! empty( $bg_styles ) ? 'style="' . implode( '; ', $bg_styles ) . '"' : '';

// Build overlay style
$overlay_style = '';
if ( $overlay_enabled ) {
    $overlay_style = 'style="background-color: ' . esc_attr( $overlay_color ) . '; opacity: ' . esc_attr( $overlay_opacity ) . '"';
}

// Get wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'cp-hero-commander',
    'style' => 'min-height: ' . esc_attr( $min_height ) . ';',
) );

// Generate unique ID
$unique_id = 'cp-hero-' . wp_unique_id();
?>

<section <?php echo wp_kses_data( $wrapper_attributes ); ?> id="<?php echo esc_attr( $unique_id ); ?>">
    <?php if ( 'video' === $background_type && ! empty( $background_video['url'] ) ) : ?>
        <div class="cp-hero-video-background">
            <video autoplay muted loop playsinline>
                <source src="<?php echo esc_url( $background_video['url'] ); ?>" type="video/mp4">
            </video>
        </div>
    <?php endif; ?>

    <?php if ( $overlay_enabled ) : ?>
        <div class="cp-hero-overlay" <?php echo $overlay_style; ?>></div>
    <?php endif; ?>

    <div class="cp-hero-content" style="text-align: <?php echo esc_attr( $text_align ); ?>;">
        <div class="cp-hero-inner" style="max-width: <?php echo esc_attr( $content_max_width ); ?>; color: <?php echo esc_attr( $text_color ); ?>;">
            
            <?php if ( $headline ) : ?>
                <h1 class="cp-hero-headline" style="font-size: <?php echo esc_attr( $headline_size ); ?>; color: <?php echo esc_attr( $text_color ); ?>;">
                    <?php if ( $typewriter_enabled && ! empty( $typewriter_texts ) ) : ?>
                        <span class="cp-typewriter" data-texts="<?php echo esc_attr( wp_json_encode( $typewriter_texts ) ); ?>">
                            <?php echo esc_html( $typewriter_texts[0] ?? $headline ); ?>
                        </span>
                    <?php else : ?>
                        <?php echo esc_html( $headline ); ?>
                    <?php endif; ?>
                </h1>
            <?php endif; ?>

            <?php if ( $subheadline ) : ?>
                <p class="cp-hero-subheadline" style="font-size: <?php echo esc_attr( $subheadline_size ); ?>; color: <?php echo esc_attr( $text_color ); ?>;">
                    <?php echo esc_html( $subheadline ); ?>
                </p>
            <?php endif; ?>

            <?php if ( ( $primary_cta['enabled'] && ! empty( $primary_cta['url'] ) ) || ( $secondary_cta['enabled'] && ! empty( $secondary_cta['url'] ) ) ) : ?>
                <div class="cp-hero-ctas">
                    <?php if ( $primary_cta['enabled'] && ! empty( $primary_cta['url'] ) ) : ?>
                        <a href="<?php echo esc_url( $primary_cta['url'] ); ?>" class="cp-hero-cta cp-hero-cta-primary" target="_blank" rel="noopener">
                            <?php echo esc_html( $primary_cta['text'] ); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ( $secondary_cta['enabled'] && ! empty( $secondary_cta['url'] ) ) : ?>
                        <a href="<?php echo esc_url( $secondary_cta['url'] ); ?>" class="cp-hero-cta cp-hero-cta-secondary" target="_blank" rel="noopener">
                            <?php echo esc_html( $secondary_cta['text'] ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<style>
#<?php echo esc_attr( $unique_id ); ?> {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

#<?php echo esc_attr( $unique_id ); ?> .cp-hero-video-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -2;
}

#<?php echo esc_attr( $unique_id ); ?> .cp-hero-video-background video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

#<?php echo esc_attr( $unique_id ); ?> .cp-hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
}

#<?php echo esc_attr( $unique_id ); ?> .cp-hero-content {
    position: relative;
    z-index: 1;
    width: 100%;
    padding: 4rem 1rem;
}

#<?php echo esc_attr( $unique_id ); ?> .cp-hero-inner {
    margin: 0 auto;
}

#<?php echo esc_attr( $unique_id ); ?> .cp-hero-headline {
    margin: 0 0 1rem 0;
    font-weight: 700;
    line-height: 1.2;
}

#<?php echo esc_attr( $unique_id ); ?> .cp-hero-subheadline {
    margin: 0 0 2rem 0;
    font-weight: 400;
    line-height: 1.6;
    opacity: 0.9;
}

#<?php echo esc_attr( $unique_id ); ?> .cp-hero-ctas {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

#<?php echo esc_attr( $unique_id ); ?> .cp-hero-cta {
    display: inline-block;
    padding: 0.75rem 2rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

#<?php echo esc_attr( $unique_id ); ?> .cp-hero-cta-primary {
    background-color: #ff8800;
    color: #ffffff;
}

#<?php echo esc_attr( $unique_id ); ?> .cp-hero-cta-primary:hover {
    background-color: #cc6c00;
    transform: translateY(-2px);
}

#<?php echo esc_attr( $unique_id ); ?> .cp-hero-cta-secondary {
    background-color: transparent;
    color: #ffffff;
    border-color: #ffffff;
}

#<?php echo esc_attr( $unique_id ); ?> .cp-hero-cta-secondary:hover {
    background-color: #ffffff;
    color: #14213d;
    transform: translateY(-2px);
}

/* Typewriter animation */
#<?php echo esc_attr( $unique_id ); ?> .cp-typewriter {
    position: relative;
    display: inline-block;
}

#<?php echo esc_attr( $unique_id ); ?> .cp-typewriter::after {
    content: '|';
    animation: blink 1s infinite;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }
}

/* Parallax effect */
#<?php echo esc_attr( $unique_id ); ?>[data-parallax="true"] {
    background-attachment: fixed;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
}

@media (max-width: 768px) {
    #<?php echo esc_attr( $unique_id ); ?> .cp-hero-ctas {
        flex-direction: column;
        align-items: center;
    }
    
    #<?php echo esc_attr( $unique_id ); ?> .cp-hero-cta {
        width: 100%;
        max-width: 300px;
        text-align: center;
    }
}
</style>

<?php if ( $parallax_enabled ) : ?>
<script>
(function() {
    const hero = document.getElementById('<?php echo esc_js( $unique_id ); ?>');
    if (hero) {
        hero.setAttribute('data-parallax', 'true');
        
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            hero.style.backgroundPosition = `center ${rate}px`;
        });
    }
})();
</script>
<?php endif; ?>

<?php if ( $typewriter_enabled && ! empty( $typewriter_texts ) ) : ?>
<script>
(function() {
    const typewriter = document.querySelector('#<?php echo esc_js( $unique_id ); ?> .cp-typewriter');
    if (typewriter && typewriter.dataset.texts) {
        const texts = JSON.parse(typewriter.dataset.texts);
        let index = 0;
        let charIndex = 0;
        let isDeleting = false;
        let isWaiting = false;
        
        function type() {
            const currentText = texts[index];
            
            if (isWaiting) {
                setTimeout(type, 2000);
                isWaiting = false;
                return;
            }
            
            if (isDeleting) {
                typewriter.textContent = currentText.substring(0, charIndex - 1);
                charIndex--;
            } else {
                typewriter.textContent = currentText.substring(0, charIndex + 1);
                charIndex++;
            }
            
            let typeSpeed = <?php echo esc_js( $attributes['typewriterSpeed'] ?? 100 ); ?>;
            
            if (!isDeleting && charIndex === currentText.length) {
                typeSpeed = 2000;
                isDeleting = true;
                isWaiting = true;
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                index = (index + 1) % texts.length;
                typeSpeed = 500;
            }
            
            setTimeout(type, typeSpeed);
        }
        
        type();
    }
})();
</script>
<?php endif; ?>
