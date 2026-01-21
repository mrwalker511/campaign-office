<?php
/**
 * Issue Card Block - Server-side Render
 *
 * @package CampaignPress
 * @since 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extract attributes with defaults
$title       = $attributes['issueTitle'] ?? '';
$description = $attributes['issueDescription'] ?? '';
$icon        = $attributes['iconName'] ?? 'megaphone';

// Sanitize icon name to prevent CSS injection (only alphanumeric and hyphens)
$icon = preg_replace( '/[^a-z0-9\-]/', '', strtolower( $icon ) );

// Get wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'cp-issue-card',
) );
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
    <?php if ( $icon ) : ?>
        <div class="cp-issue-icon">
            <span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
        </div>
    <?php endif; ?>

    <?php if ( $title ) : ?>
        <h3 class="cp-issue-title"><?php echo esc_html( $title ); ?></h3>
    <?php endif; ?>

    <?php if ( $description ) : ?>
        <div class="cp-issue-description"><?php echo wp_kses_post( $description ); ?></div>
    <?php endif; ?>
</div>
