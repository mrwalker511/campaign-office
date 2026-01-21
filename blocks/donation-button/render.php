<?php
/**
 * Donation Button Block - Server-side Render
 *
 * @package CampaignPress
 * @since 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extract attributes with defaults
$button_text = $attributes['buttonText'] ?? __( 'Donate Now', 'campaignpress' );
$donation_url = $attributes['donationUrl'] ?? '';
$button_style = $attributes['buttonStyle'] ?? 'primary';
$alignment   = $attributes['alignment'] ?? 'left';

// Validate URL format
if ( ! empty( $donation_url ) && ! filter_var( $donation_url, FILTER_VALIDATE_URL ) ) {
    $donation_url = '';
}

// Whitelist validation for button style
$valid_styles = array( 'primary', 'secondary', 'outline' );
if ( ! in_array( $button_style, $valid_styles, true ) ) {
    $button_style = 'primary';
}

// Whitelist validation for alignment
$valid_alignments = array( 'left', 'center', 'right' );
if ( ! in_array( $alignment, $valid_alignments, true ) ) {
    $alignment = 'left';
}

// Get wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'cp-donation-button-wrapper align-' . $alignment,
) );

if ( empty( $donation_url ) ) {
    if ( current_user_can( 'edit_posts' ) ) {
        return sprintf(
            '<div %s><div class="cp-block-notice">%s</div></div>',
            wp_kses_data( $wrapper_attributes ),
            esc_html__( 'Please set a donation URL in the block settings or theme customizer.', 'campaignpress' )
        );
    }
    return '';
}
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
    <a href="<?php echo esc_url( $donation_url ); ?>" 
       class="cp-donation-button cp-button-<?php echo esc_attr( $button_style ); ?>" 
       target="_blank" 
       rel="noopener">
        <?php echo esc_html( $button_text ); ?>
    </a>
</div>
