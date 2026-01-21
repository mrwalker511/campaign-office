<?php
/**
 * Volunteer CTA Block - Server-side Render
 *
 * @package CampaignPress
 * @since 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extract attributes with defaults
$title       = $attributes['title'] ?? __( 'Join Our Campaign', 'campaignpress' );
$description = $attributes['description'] ?? '';
$button_text = $attributes['buttonText'] ?? __( 'Sign Up to Volunteer', 'campaignpress' );
$button_url  = $attributes['buttonUrl'] ?? '';

// Validate URL format
if ( ! empty( $button_url ) && ! filter_var( $button_url, FILTER_VALIDATE_URL ) ) {
    $button_url = '';
}

// Get wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'cp-volunteer-cta',
) );
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
    <?php if ( $title ) : ?>
        <h3 class="cp-cta-title"><?php echo esc_html( $title ); ?></h3>
    <?php endif; ?>

    <?php if ( $description ) : ?>
        <p class="cp-cta-description"><?php echo esc_html( $description ); ?></p>
    <?php endif; ?>

    <?php if ( $button_url ) : ?>
        <a href="<?php echo esc_url( $button_url ); ?>" class="cp-button cp-button-primary">
            <?php echo esc_html( $button_text ); ?>
        </a>
    <?php endif; ?>
</div>
