<?php
/**
 * Render for Section Wrapper Block
 */
$attributes = $attributes ?? [];
$breakpoint = $attributes['breakpoint'] ?? 'md';
$max_width = $attributes['maxWidth'] ?? '1200px';
$gap = $attributes['gap'] ?? '1rem';

// Sanitize CSS values for security
$breakpoint_safe = sanitize_html_class($breakpoint);
$max_width_safe = esc_attr($max_width);
$gap_safe = esc_attr($gap);

$wrapper_attributes = get_block_wrapper_attributes(array(
    'class' => 'cp-section-wrapper cp-break-' . $breakpoint_safe,
    'style' => 'max-width: ' . $max_width_safe . '; gap: ' . $gap_safe . ';'
));
?>
<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
    <?php echo wp_kses_post( $content ); ?>
</div>
