<?php
/**
 * Render for Style Panel Block
 * Basically a Group block with enhanced attributes exposed via UI
 */
$attributes = $attributes ?? [];
$padding = $attributes['padding'] ?? '2rem';
$bg_color = $attributes['bgColor'] ?? 'transparent';
$radius = $attributes['borderRadius'] ?? '0px';

// Sanitize CSS values for security
$padding_safe = esc_attr($padding);
$bg_color_safe = esc_attr($bg_color);
$radius_safe = esc_attr($radius);

$style_string = "padding: {$padding_safe}; background-color: {$bg_color_safe}; border-radius: {$radius_safe};";

$wrapper_attributes = get_block_wrapper_attributes(array(
    'class' => 'cp-style-panel-wrapper',
    'style' => $style_string
));
?>
<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
    <?php echo wp_kses_post( $content ); ?>
</div>
