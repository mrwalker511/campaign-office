<?php
/**
 * Hero Commander Block - Server-Side Rendering
 *
 * @package CampaignPress
 * @var array $attributes Block attributes
 * @var string $content Block content
 * @var WP_Block $block Block instance
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extract attributes with defaults
$headline = $attributes['headline'] ?? 'Leading with Integrity';
$subheadline = $attributes['subheadline'] ?? 'Building a Better Future Together';
$typewriter_enabled = $attributes['typewriterEnabled'] ?? true;
$typewriter_texts = $attributes['typewriterTexts'] ?? ['Vision', 'Courage', 'Values', 'Leadership'];
$typewriter_speed = $attributes['typewriterSpeed'] ?? 100;

$background_type = $attributes['backgroundType'] ?? 'image';
$background_image = $attributes['backgroundImage'] ?? ['url' => '', 'id' => 0, 'alt' => ''];
$background_video = $attributes['backgroundVideo'] ?? ['url' => '', 'id' => 0];
$background_color = $attributes['backgroundColor'] ?? '#0053c3';
$gradient_colors = $attributes['gradientColors'] ?? ['start' => '#0053c3', 'end' => '#003275'];

$overlay_enabled = $attributes['overlayEnabled'] ?? true;
$overlay_color = $attributes['overlayColor'] ?? '#000000';
$overlay_opacity = $attributes['overlayOpacity'] ?? 0.5;

$min_height = $attributes['minHeight'] ?? '100vh';
$text_align = $attributes['textAlign'] ?? 'center';
$text_color = $attributes['textColor'] ?? '#ffffff';
$headline_size = $attributes['headlineSize'] ?? 'clamp(2.5rem, 5vw, 5rem)';
$subheadline_size = $attributes['subheadlineSize'] ?? 'clamp(1.25rem, 2.5vw, 2rem)';
$content_max_width = $attributes['contentMaxWidth'] ?? '900px';

$primary_cta = $attributes['primaryCTA'] ?? ['text' => 'Donate Now', 'url' => '#donate', 'enabled' => true];
$secondary_cta = $attributes['secondaryCTA'] ?? ['text' => 'Get Involved', 'url' => '#volunteer', 'enabled' => true];

$parallax_enabled = $attributes['parallaxEnabled'] ?? false;
$animation_style = $attributes['animationStyle'] ?? 'fade-up';

// Generate unique ID for this block instance
$block_id = 'hero-commander-' . wp_unique_id();

// Build background style
$background_style = '';
switch ($background_type) {
    case 'image':
        if (!empty($background_image['url'])) {
            $background_style = sprintf(
                'background-image: url(%s); background-size: cover; background-position: center;',
                esc_url($background_image['url'])
            );
        }
        break;
    case 'video':
        $background_style = sprintf('background-color: %s;', esc_attr($background_color));
        break;
    case 'gradient':
        $background_style = sprintf(
            'background-image: linear-gradient(135deg, %s, %s);',
            esc_attr($gradient_colors['start']),
            esc_attr($gradient_colors['end'])
        );
        break;
    case 'color':
        $background_style = sprintf('background-color: %s;', esc_attr($background_color));
        break;
}

// Build wrapper classes
$wrapper_classes = ['hero-commander', 'alignfull'];
if ($parallax_enabled) {
    $wrapper_classes[] = 'has-parallax';
}
if ($animation_style !== 'none') {
    $wrapper_classes[] = 'has-animation';
    $wrapper_classes[] = 'animation-' . esc_attr($animation_style);
}

// Data attributes for JavaScript
$data_attrs = '';
$data_attrs .= sprintf(' data-typewriter-enabled="%s"', $typewriter_enabled ? 'true' : 'false');
$data_attrs .= sprintf(' data-typewriter-texts="%s"', esc_attr(wp_json_encode($typewriter_texts)));
$data_attrs .= sprintf(' data-typewriter-speed="%s"', esc_attr($typewriter_speed));
$data_attrs .= sprintf(' data-parallax="%s"', $parallax_enabled ? 'true' : 'false');

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => implode(' ', $wrapper_classes),
    'style' => sprintf('min-height: %s;', esc_attr($min_height)),
    'id' => $block_id
]);
?>

<section <?php echo $wrapper_attributes; ?> <?php echo $data_attrs; ?>>
    <!-- Background Layer -->
    <div class="hero-commander__background" style="<?php echo esc_attr($background_style); ?>">
        <?php if ($background_type === 'video' && !empty($background_video['url'])): ?>
            <video
                class="hero-commander__video"
                autoplay
                muted
                loop
                playsinline
                <?php if (!empty($background_image['url'])): ?>
                    poster="<?php echo esc_url($background_image['url']); ?>"
                <?php endif; ?>
            >
                <source src="<?php echo esc_url($background_video['url']); ?>" type="video/mp4">
                <?php esc_html_e('Your browser does not support the video tag.', 'campaign-office'); ?>
            </video>
        <?php endif; ?>

        <?php if ($overlay_enabled): ?>
            <div
                class="hero-commander__overlay"
                style="background-color: <?php echo esc_attr($overlay_color); ?>; opacity: <?php echo esc_attr($overlay_opacity); ?>;"
            ></div>
        <?php endif; ?>
    </div>

    <!-- Content Layer -->
    <div
        class="hero-commander__content"
        style="
            max-width: <?php echo esc_attr($content_max_width); ?>;
            text-align: <?php echo esc_attr($text_align); ?>;
            color: <?php echo esc_attr($text_color); ?>;
        "
    >
        <div class="hero-commander__inner">
            <?php if (!empty($headline)): ?>
                <h1
                    class="hero-commander__headline"
                    style="
                        font-size: <?php echo esc_attr($headline_size); ?>;
                        color: <?php echo esc_attr($text_color); ?>;
                    "
                >
                    <?php echo wp_kses_post($headline); ?>
                </h1>
            <?php endif; ?>

            <?php if ($typewriter_enabled && !empty($typewriter_texts)): ?>
                <p
                    class="hero-commander__typewriter"
                    data-typewriter-container
                    style="
                        font-size: <?php echo esc_attr($headline_size); ?>;
                        color: <?php echo esc_attr($text_color); ?>;
                    "
                >
                    <span class="typewriter-text"></span>
                    <span class="typewriter-cursor">|</span>
                </p>
            <?php endif; ?>

            <?php if (!empty($subheadline)): ?>
                <p
                    class="hero-commander__subheadline"
                    style="
                        font-size: <?php echo esc_attr($subheadline_size); ?>;
                        color: <?php echo esc_attr($text_color); ?>;
                    "
                >
                    <?php echo wp_kses_post($subheadline); ?>
                </p>
            <?php endif; ?>

            <?php if (($primary_cta['enabled'] ?? true) || ($secondary_cta['enabled'] ?? true)): ?>
                <div class="hero-commander__cta">
                    <?php if ($primary_cta['enabled'] ?? true): ?>
                        <a
                            href="<?php echo esc_url($primary_cta['url'] ?? '#'); ?>"
                            class="hero-commander__cta-primary button"
                        >
                            <?php echo esc_html($primary_cta['text'] ?? 'Donate Now'); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($secondary_cta['enabled'] ?? true): ?>
                        <a
                            href="<?php echo esc_url($secondary_cta['url'] ?? '#'); ?>"
                            class="hero-commander__cta-secondary button button-outline"
                        >
                            <?php echo esc_html($secondary_cta['text'] ?? 'Get Involved'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="hero-commander__scroll-indicator">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 14l-7 7m0 0l-7-7m7 7V3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
</section>
